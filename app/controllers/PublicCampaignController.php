<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CampaignModel;

final class PublicCampaignController
{
    private CampaignModel $campaigns;

    public function __construct()
    {
        $this->campaigns = new CampaignModel();
    }

    public function show(string $token): void
    {
        $campaign = $this->campaigns->findByToken($token);

        $canPreviewDraft = is_logged_in();

        if (!$campaign || ($campaign['status'] === CampaignModel::STATUS_DRAFT && !$canPreviewDraft)) {
            http_response_code(404);
            render('home/404', ['title' => '404']);
            return;
        }

        render('public/show', [
            'title' => $campaign['title'],
            'campaign' => $campaign,
            'questionTypes' => CampaignModel::questionTypes(),
            'isPreview' => $campaign['status'] === CampaignModel::STATUS_DRAFT,
            'showUserMenu' => false,
            'logoClickable' => false,
        ]);
    }

    public function submit(string $token): void
    {
        verify_csrf();

        $campaign = $this->campaigns->findByToken($token);

        if (!$campaign || $campaign['status'] !== CampaignModel::STATUS_PUBLISHED || $campaign['type'] !== CampaignModel::TYPE_FORM) {
            http_response_code(404);
            render('home/404', ['title' => '404']);
            return;
        }

        [$errors, $answers, $fileAnswers] = $this->validateSubmission($campaign);

        if ($errors !== []) {
            set_old_input(['answers' => $answers]);
            set_validation_errors($errors);
            flash('error', 'Hay respuestas inválidas.');
            redirect(base_url('c/' . rawurlencode($token)));
        }

        $this->campaigns->createSubmission($campaign, $answers, $fileAnswers);
        flash('success', $campaign['thank_you_message'] ?: 'Respuesta enviada correctamente.');
        redirect(base_url('c/' . rawurlencode($token)));
    }

    private function validateSubmission(array $campaign): array
    {
        $answers = is_array($_POST['answers'] ?? null) ? $_POST['answers'] : [];
        $files = $this->normalizeAnswerFiles();
        $errors = [];

        foreach ($campaign['questions'] as $question) {
            $id = (int) $question['id'];
            $label = $question['label'];
            $value = $answers[$id] ?? null;
            $required = (int) $question['is_required'] === 1;
            $allowedOptions = array_map(static fn (array $option): string => $option['value'], $question['options']);

            switch ($question['type']) {
                case CampaignModel::QUESTION_TEXT:
                    if ($required && trim((string) $value) === '') {
                        $errors['answers.' . $id] = 'La pregunta ' . $label . ' es obligatoria.';
                    } elseif ($value !== null && strlen(trim((string) $value)) > 4000) {
                        $errors['answers.' . $id] = 'La pregunta ' . $label . ' supera el largo permitido.';
                    }
                    break;

                case CampaignModel::QUESTION_EMAIL:
                    if ($required && trim((string) $value) === '') {
                        $errors['answers.' . $id] = 'La pregunta ' . $label . ' es obligatoria.';
                    } elseif ($value !== null && trim((string) $value) !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors['answers.' . $id] = 'La pregunta ' . $label . ' debe contener un correo válido.';
                    }
                    break;

                case CampaignModel::QUESTION_NUMBER:
                    if ($required && trim((string) $value) === '') {
                        $errors['answers.' . $id] = 'La pregunta ' . $label . ' es obligatoria.';
                    } elseif ($value !== null && trim((string) $value) !== '' && !is_numeric($value)) {
                        $errors['answers.' . $id] = 'La pregunta ' . $label . ' debe contener un número válido.';
                    }
                    break;

                case CampaignModel::QUESTION_DATE:
                    if ($required && trim((string) $value) === '') {
                        $errors['answers.' . $id] = 'La pregunta ' . $label . ' es obligatoria.';
                    } elseif ($value !== null && trim((string) $value) !== '' && strtotime((string) $value) === false) {
                        $errors['answers.' . $id] = 'La pregunta ' . $label . ' debe contener una fecha válida.';
                    }
                    break;

                case CampaignModel::QUESTION_SINGLE_CHOICE:
                case CampaignModel::QUESTION_TRUE_FALSE:
                    if ($required && ($value === null || $value === '')) {
                        $errors['answers.' . $id] = 'La pregunta ' . $label . ' es obligatoria.';
                    } elseif ($value !== null && $value !== '' && !in_array((string) $value, $allowedOptions, true)) {
                        $errors['answers.' . $id] = 'La respuesta seleccionada para ' . $label . ' no es válida.';
                    }
                    break;

                case CampaignModel::QUESTION_MULTIPLE_CHOICE:
                    if ($required && empty($value)) {
                        $errors['answers.' . $id] = 'La pregunta ' . $label . ' requiere al menos una selección.';
                    }

                    if ($value !== null && $value !== []) {
                        if (!is_array($value)) {
                            $errors['answers.' . $id] = 'La respuesta seleccionada para ' . $label . ' no es válida.';
                            break;
                        }

                        foreach ($value as $selected) {
                            if (!in_array((string) $selected, $allowedOptions, true)) {
                                $errors['answers.' . $id] = 'La respuesta seleccionada para ' . $label . ' no es válida.';
                                break;
                            }
                        }
                    }
                    break;

                case CampaignModel::QUESTION_FILE:
                    $file = $files[$id] ?? null;
                    $hasUpload = $file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

                    if ($required && !$hasUpload) {
                        $errors['answers.' . $id] = 'La pregunta ' . $label . ' es obligatoria.';
                        break;
                    }

                    if ($hasUpload) {
                        $allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
                        $extension = strtolower((string) pathinfo((string) $file['name'], PATHINFO_EXTENSION));

                        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                            $errors['answers.' . $id] = 'La pregunta ' . $label . ' debe contener un archivo válido.';
                        } elseif (!in_array($extension, $allowedExtensions, true)) {
                            $errors['answers.' . $id] = 'La pregunta ' . $label . ' solo acepta PDF, Word, Excel o imágenes.';
                        } elseif (($file['size'] ?? 0) > 10 * 1024 * 1024) {
                            $errors['answers.' . $id] = 'La pregunta ' . $label . ' supera el tamaño permitido.';
                        }
                    }
                    break;
            }
        }

        return [$errors, $answers, $files];
    }

    private function normalizeAnswerFiles(): array
    {
        $files = $_FILES['answers'] ?? null;

        if (!$files || !is_array($files['name'] ?? null)) {
            return [];
        }

        $normalized = [];

        foreach ($files['name'] as $id => $name) {
            $normalized[(int) $id] = [
                'name' => $name,
                'type' => $files['type'][$id],
                'tmp_name' => $files['tmp_name'][$id],
                'error' => $files['error'][$id],
                'size' => $files['size'][$id],
            ];
        }

        return $normalized;
    }
}
