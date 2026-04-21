<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;
use RuntimeException;

final class CampaignModel
{
    public const TYPE_FORM = 'form';
    public const TYPE_INFO = 'info';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CLOSED = 'closed';

    public const QUESTION_TEXT = 'text';
    public const QUESTION_SINGLE_CHOICE = 'single_choice';
    public const QUESTION_MULTIPLE_CHOICE = 'multiple_choice';
    public const QUESTION_TRUE_FALSE = 'true_false';
    public const QUESTION_DATE = 'date';
    public const QUESTION_EMAIL = 'email';
    public const QUESTION_NUMBER = 'number';
    public const QUESTION_FILE = 'file';

    public static function campaignTypes(): array
    {
        return [
            self::TYPE_FORM => 'Cuestionario',
            self::TYPE_INFO => 'Informativo',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_PUBLISHED => 'Publicada',
            self::STATUS_CLOSED => 'Cerrada',
        ];
    }

    public static function questionTypes(): array
    {
        return [
            self::QUESTION_TEXT => 'Texto libre',
            self::QUESTION_SINGLE_CHOICE => 'Alternativa',
            self::QUESTION_MULTIPLE_CHOICE => 'Selección múltiple',
            self::QUESTION_TRUE_FALSE => 'Verdadero / Falso',
            self::QUESTION_DATE => 'Fecha',
            self::QUESTION_EMAIL => 'Correo',
            self::QUESTION_NUMBER => 'Número',
            self::QUESTION_FILE => 'Archivo adjunto',
        ];
    }

    public static function supportTypes(): array
    {
        return [
            'text' => 'Texto',
            'image' => 'Imagen',
            'video' => 'Video',
        ];
    }

    public function allWithSubmissionCount(): array
    {
        $sql = 'SELECT c.*, COUNT(s.id) AS submissions_count
                FROM campaigns c
                LEFT JOIN submissions s ON s.campaign_id = c.id
                GROUP BY c.id
                ORDER BY c.id DESC';

        return DB::pdo()->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = DB::pdo()->prepare('SELECT * FROM campaigns WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $campaign = $stmt->fetch();

        if (!$campaign) {
            return null;
        }

        return $this->hydrateCampaign($campaign);
    }

    public function findByToken(string $token): ?array
    {
        $stmt = DB::pdo()->prepare('SELECT * FROM campaigns WHERE token = :token LIMIT 1');
        $stmt->execute(['token' => $token]);
        $campaign = $stmt->fetch();

        if (!$campaign) {
            return null;
        }

        return $this->hydrateCampaign($campaign);
    }

    public function create(array $campaignData, array $blocks, array $questions, array $blockFiles): int
    {
        $pdo = DB::pdo();
        $pdo->beginTransaction();

        try {
            $token = $this->generateUniqueToken();
            $publishedAt = $campaignData['status'] === self::STATUS_PUBLISHED ? now() : null;

            $stmt = $pdo->prepare('INSERT INTO campaigns (name, token, type, status, title, description, thank_you_message, starts_at, ends_at, published_at, created_at, updated_at)
                VALUES (:name, :token, :type, :status, :title, :description, :thank_you_message, :starts_at, :ends_at, :published_at, NOW(), NOW())');

            $stmt->execute([
                'name' => $campaignData['name'],
                'token' => $token,
                'type' => $campaignData['type'],
                'status' => $campaignData['status'],
                'title' => $campaignData['title'],
                'description' => $campaignData['description'] ?: null,
                'thank_you_message' => $campaignData['thank_you_message'] ?: null,
                'starts_at' => $campaignData['starts_at'] ?: null,
                'ends_at' => $campaignData['ends_at'] ?: null,
                'published_at' => $publishedAt,
            ]);

            $campaignId = (int) $pdo->lastInsertId();
            $this->syncBlocks($campaignId, $blocks, $blockFiles);
            $this->syncQuestions($campaignId, $questions);

            $pdo->commit();
            return $campaignId;
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function update(int $id, array $campaignData, array $blocks, array $questions, array $blockFiles): void
    {
        $pdo = DB::pdo();
        $pdo->beginTransaction();

        try {
            $existing = $this->find($id);

            if (!$existing) {
                throw new RuntimeException('Campaign not found.');
            }

            $publishedAt = null;

            if ($campaignData['status'] === self::STATUS_PUBLISHED) {
                $publishedAt = $existing['published_at'] ?: now();
            }

            $stmt = $pdo->prepare('UPDATE campaigns
                SET name = :name,
                    type = :type,
                    status = :status,
                    title = :title,
                    description = :description,
                    thank_you_message = :thank_you_message,
                    starts_at = :starts_at,
                    ends_at = :ends_at,
                    published_at = :published_at,
                    updated_at = NOW()
                WHERE id = :id');

            $stmt->execute([
                'id' => $id,
                'name' => $campaignData['name'],
                'type' => $campaignData['type'],
                'status' => $campaignData['status'],
                'title' => $campaignData['title'],
                'description' => $campaignData['description'] ?: null,
                'thank_you_message' => $campaignData['thank_you_message'] ?: null,
                'starts_at' => $campaignData['starts_at'] ?: null,
                'ends_at' => $campaignData['ends_at'] ?: null,
                'published_at' => $publishedAt,
            ]);

            $pdo->prepare('DELETE FROM campaign_blocks WHERE campaign_id = :id')->execute(['id' => $id]);
            $pdo->prepare('DELETE FROM questions WHERE campaign_id = :id')->execute(['id' => $id]);

            $this->syncBlocks($id, $blocks, $blockFiles);
            $this->syncQuestions($id, $questions);
            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function createSubmission(array $campaign, array $postAnswers, array $fileAnswers): void
    {
        $pdo = DB::pdo();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('INSERT INTO submissions (campaign_id, submitted_at, ip_address, user_agent) VALUES (:campaign_id, NOW(), :ip_address, :user_agent)');
            $stmt->execute([
                'campaign_id' => $campaign['id'],
                'ip_address' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45) ?: null,
                'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 1000) ?: null,
            ]);

            $submissionId = (int) $pdo->lastInsertId();

            foreach ($campaign['questions'] as $question) {
                $questionId = (int) $question['id'];
                $payload = [
                    'submission_id' => $submissionId,
                    'question_id' => $questionId,
                    'value_text' => null,
                    'value_json' => null,
                    'value_file_path' => null,
                    'original_file_name' => null,
                ];

                if ($question['type'] === self::QUESTION_FILE) {
                    if (empty($fileAnswers[$questionId]) || ($fileAnswers[$questionId]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                        continue;
                    }

                    $storedPath = $this->storeUpload($fileAnswers[$questionId], 'submissions');
                    $payload['value_file_path'] = $storedPath;
                    $payload['original_file_name'] = $fileAnswers[$questionId]['name'];
                } else {
                    $value = $postAnswers[$questionId] ?? null;

                    if ($value === null || $value === '' || $value === []) {
                        continue;
                    }

                    if ($question['type'] === self::QUESTION_MULTIPLE_CHOICE) {
                        $payload['value_text'] = implode(', ', $value);
                        $payload['value_json'] = json_encode(array_values($value), JSON_UNESCAPED_UNICODE);
                    } else {
                        $payload['value_text'] = (string) $value;
                    }
                }

                $insert = $pdo->prepare('INSERT INTO submission_answers (submission_id, question_id, value_text, value_json, value_file_path, original_file_name)
                    VALUES (:submission_id, :question_id, :value_text, :value_json, :value_file_path, :original_file_name)');
                $insert->execute($payload);
            }

            $pdo->commit();
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    public function resultsTable(array $campaign): array
    {
        $columns = ['ID envio', 'Fecha envio', 'IP'];

        foreach ($campaign['questions'] as $question) {
            $columns[] = $question['label'];
        }

        $rows = [];

        foreach ($campaign['submissions'] as $submission) {
            $row = [
                'ID envio' => $submission['id'],
                'Fecha envio' => $submission['submitted_at'],
                'IP' => $submission['ip_address'] ?? '',
            ];

            $answers = [];

            foreach ($submission['answers'] as $answer) {
                $answers[(int) $answer['question_id']] = $answer;
            }

            foreach ($campaign['questions'] as $question) {
                $answer = $answers[(int) $question['id']] ?? null;
                $value = '';

                if ($answer) {
                    if (!empty($answer['value_file_path'])) {
                        $value = $answer['original_file_name'] ?: $answer['value_file_path'];
                    } elseif (!empty($answer['value_json'])) {
                        $decoded = json_decode((string) $answer['value_json'], true);
                        $value = is_array($decoded) ? implode(', ', $decoded) : '';
                    } else {
                        $value = (string) ($answer['value_text'] ?? '');
                    }
                }

                $row[$question['label']] = $value;
            }

            $rows[] = $row;
        }

        return [$columns, $rows];
    }

    private function hydrateCampaign(array $campaign): array
    {
        $campaign['blocks'] = $this->blocks((int) $campaign['id']);
        $campaign['questions'] = $this->questions((int) $campaign['id']);
        $campaign['submissions'] = $this->submissions((int) $campaign['id']);
        return $campaign;
    }

    private function blocks(int $campaignId): array
    {
        $stmt = DB::pdo()->prepare('SELECT * FROM campaign_blocks WHERE campaign_id = :campaign_id ORDER BY position ASC, id ASC');
        $stmt->execute(['campaign_id' => $campaignId]);
        return $stmt->fetchAll();
    }

    private function questions(int $campaignId): array
    {
        $stmt = DB::pdo()->prepare('SELECT * FROM questions WHERE campaign_id = :campaign_id ORDER BY position ASC, id ASC');
        $stmt->execute(['campaign_id' => $campaignId]);
        $questions = $stmt->fetchAll();

        foreach ($questions as &$question) {
            $optionStmt = DB::pdo()->prepare('SELECT * FROM question_options WHERE question_id = :question_id ORDER BY position ASC, id ASC');
            $optionStmt->execute(['question_id' => $question['id']]);
            $question['options'] = $optionStmt->fetchAll();

            $settings = [];

            if (!empty($question['settings'])) {
                $decoded = json_decode((string) $question['settings'], true);
                $settings = is_array($decoded) ? $decoded : [];
            }

            $question['support_type'] = (string) ($settings['support_type'] ?? 'text');
            $question['support_content'] = (string) ($settings['support_content'] ?? ($question['help_text'] ?? ''));
        }

        return $questions;
    }

    private function submissions(int $campaignId): array
    {
        $stmt = DB::pdo()->prepare('SELECT * FROM submissions WHERE campaign_id = :campaign_id ORDER BY submitted_at DESC, id DESC');
        $stmt->execute(['campaign_id' => $campaignId]);
        $submissions = $stmt->fetchAll();

        foreach ($submissions as &$submission) {
            $answerStmt = DB::pdo()->prepare('SELECT sa.*, q.label AS question_label FROM submission_answers sa INNER JOIN questions q ON q.id = sa.question_id WHERE sa.submission_id = :submission_id ORDER BY sa.id ASC');
            $answerStmt->execute(['submission_id' => $submission['id']]);
            $submission['answers'] = $answerStmt->fetchAll();
        }

        return $submissions;
    }

    private function syncBlocks(int $campaignId, array $blocks, array $blockFiles): void
    {
        $stmt = DB::pdo()->prepare('INSERT INTO campaign_blocks (campaign_id, type, title, content, media_path, position, created_at, updated_at)
            VALUES (:campaign_id, :type, :title, :content, :media_path, :position, NOW(), NOW())');

        foreach ($blocks as $index => $block) {
            $type = $block['type'] ?? 'text';
            $title = trim((string) ($block['title'] ?? ''));
            $content = trim((string) ($block['content'] ?? ''));
            $existingPath = trim((string) ($block['existing_path'] ?? ''));
            $mediaPath = $existingPath !== '' ? $existingPath : null;

            if ($type === 'text' && $title === '' && $content === '') {
                continue;
            }

            if ($type === 'image' && !isset($blockFiles[$index]) && $mediaPath === null) {
                continue;
            }

            if (isset($blockFiles[$index]) && ($blockFiles[$index]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $mediaPath = $this->storeUpload($blockFiles[$index], 'blocks');
            }

            $stmt->execute([
                'campaign_id' => $campaignId,
                'type' => $type,
                'title' => $title !== '' ? $title : null,
                'content' => $content !== '' ? $content : null,
                'media_path' => $mediaPath,
                'position' => $index,
            ]);
        }
    }

    private function syncQuestions(int $campaignId, array $questions): void
    {
        $insertQuestion = DB::pdo()->prepare('INSERT INTO questions (campaign_id, label, help_text, type, is_required, placeholder, position, settings, created_at, updated_at)
            VALUES (:campaign_id, :label, :help_text, :type, :is_required, :placeholder, :position, :settings, NOW(), NOW())');

        $insertOption = DB::pdo()->prepare('INSERT INTO question_options (question_id, label, value, position, created_at, updated_at)
            VALUES (:question_id, :label, :value, :position, NOW(), NOW())');

        foreach ($questions as $index => $question) {
            $label = trim((string) ($question['label'] ?? ''));
            $type = (string) ($question['type'] ?? '');

            if ($label === '' || $type === '') {
                continue;
            }

            $settingsPayload = [];

            if ($type === self::QUESTION_FILE) {
                $settingsPayload['max_size_kb'] = 10240;
            }

            $supportType = (string) ($question['support_type'] ?? 'text');
            $supportContent = trim((string) ($question['support_content'] ?? ''));

            if (array_key_exists($supportType, self::supportTypes()) && $supportContent !== '') {
                $settingsPayload['support_type'] = $supportType;
                $settingsPayload['support_content'] = $supportContent;
            }

            $settings = $settingsPayload !== []
                ? json_encode($settingsPayload, JSON_UNESCAPED_UNICODE)
                : null;

            $insertQuestion->execute([
                'campaign_id' => $campaignId,
                'label' => $label,
                'help_text' => $supportType === 'text' && $supportContent !== '' ? $supportContent : null,
                'type' => $type,
                'is_required' => !empty($question['required']) ? 1 : 0,
                'placeholder' => ($question['placeholder'] ?? '') !== '' ? $question['placeholder'] : null,
                'position' => $index,
                'settings' => $settings,
            ]);

            $questionId = (int) DB::pdo()->lastInsertId();

            $options = [];

            if ($type === self::QUESTION_TRUE_FALSE) {
                $options = ['Verdadero', 'Falso'];
            } elseif (in_array($type, [self::QUESTION_SINGLE_CHOICE, self::QUESTION_MULTIPLE_CHOICE], true)) {
                $options = normalize_lines($question['options_text'] ?? '');
            }

            foreach ($options as $optionIndex => $optionLabel) {
                $insertOption->execute([
                    'question_id' => $questionId,
                    'label' => $optionLabel,
                    'value' => $type === self::QUESTION_TRUE_FALSE
                        ? ($optionIndex === 0 ? 'true' : 'false')
                        : (slugify($optionLabel) ?: 'option_' . $optionIndex),
                    'position' => $optionIndex,
                ]);
            }
        }
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = strtoupper(substr(bin2hex(random_bytes(8)), 0, 10));
            $stmt = DB::pdo()->prepare('SELECT id FROM campaigns WHERE token = :token LIMIT 1');
            $stmt->execute(['token' => $token]);
        } while ($stmt->fetch());

        return $token;
    }

    private function storeUpload(array $file, string $directory): string
    {
        $extension = pathinfo((string) $file['name'], PATHINFO_EXTENSION);
        $filename = uniqid($directory . '_', true) . ($extension ? '.' . strtolower($extension) : '');
        $targetRelative = $directory . '/' . $filename;
        $targetAbsolute = dirname(__DIR__, 2) . '/public/uploads/' . $targetRelative;

        if (!move_uploaded_file((string) $file['tmp_name'], $targetAbsolute)) {
            throw new RuntimeException('No fue posible guardar el archivo.');
        }

        return $targetRelative;
    }
}
