<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CampaignModel;

final class AdminCampaignController
{
    private CampaignModel $campaigns;

    public function __construct()
    {
        $this->campaigns = new CampaignModel();
    }

    public function index(): void
    {
        require_auth();

        render('admin/campaigns/index', [
            'title' => 'Administrador de campañas',
            'campaigns' => $this->campaigns->allWithSubmissionCount(),
            'statuses' => CampaignModel::statuses(),
            'types' => CampaignModel::campaignTypes(),
        ]);
    }

    public function create(): void
    {
        require_auth();

        $campaign = [
            'id' => null,
            'name' => '',
            'type' => CampaignModel::TYPE_FORM,
            'status' => CampaignModel::STATUS_DRAFT,
            'title' => '',
            'description' => '',
            'thank_you_message' => 'Gracias por responder. Tu información fue registrada correctamente.',
            'starts_at' => null,
            'ends_at' => null,
            'token' => '',
            'blocks' => [],
            'questions' => [],
        ];

        render('admin/campaigns/form', [
            'title' => 'Nueva campaña',
            'campaign' => $campaign,
            'isEditing' => false,
            'statuses' => CampaignModel::statuses(),
            'types' => CampaignModel::campaignTypes(),
            'questionTypes' => CampaignModel::questionTypes(),
            'supportTypes' => CampaignModel::supportTypes(),
            'blocks' => old('blocks', []),
            'questions' => old('questions', []),
        ]);
    }

    public function store(): void
    {
        require_auth();
        verify_csrf();

        [$campaignData, $blocks, $questions, $errors] = $this->validateCampaignInput();

        if ($errors !== []) {
            set_old_input($_POST);
            set_validation_errors($errors);
            flash('error', 'Corrige los errores del formulario.');
            redirect(base_url('admin/campaigns/create'));
        }

        $id = $this->campaigns->create($campaignData, $blocks, $questions, $this->normalizeBlockFiles());

        flash('success', 'Campaña creada.');
        redirect(base_url('admin/campaigns/' . $id . '/edit'));
    }

    public function edit(string $id): void
    {
        require_auth();

        $campaign = $this->campaigns->find((int) $id);

        if (!$campaign) {
            http_response_code(404);
            render('home/404', ['title' => '404']);
            return;
        }

        $formBlocks = array_map(static fn (array $block): array => [
            'type' => $block['type'],
            'title' => $block['title'],
            'content' => $block['content'],
            'existing_path' => $block['media_path'],
        ], $campaign['blocks']);

        $formQuestions = array_map(static fn (array $question): array => [
            'label' => $question['label'],
            'type' => $question['type'],
            'support_type' => $question['support_type'] ?? 'text',
            'support_content' => $question['support_content'] ?? ($question['help_text'] ?? ''),
            'placeholder' => $question['placeholder'],
            'required' => (int) $question['is_required'],
            'options_text' => implode(PHP_EOL, array_map(static fn (array $option): string => $option['label'], $question['options'])),
        ], $campaign['questions']);

        render('admin/campaigns/form', [
            'title' => 'Editar campaña',
            'campaign' => $campaign,
            'isEditing' => true,
            'statuses' => CampaignModel::statuses(),
            'types' => CampaignModel::campaignTypes(),
            'questionTypes' => CampaignModel::questionTypes(),
            'supportTypes' => CampaignModel::supportTypes(),
            'blocks' => old('blocks', $formBlocks),
            'questions' => old('questions', $formQuestions),
        ]);
    }

    public function update(string $id): void
    {
        require_auth();
        verify_csrf();

        [$campaignData, $blocks, $questions, $errors] = $this->validateCampaignInput();

        if ($errors !== []) {
            set_old_input($_POST);
            set_validation_errors($errors);
            flash('error', 'Corrige los errores del formulario.');
            redirect(base_url('admin/campaigns/' . (int) $id . '/edit'));
        }

        $this->campaigns->update((int) $id, $campaignData, $blocks, $questions, $this->normalizeBlockFiles());

        flash('success', 'Campaña actualizada.');
        redirect(base_url('admin/campaigns/' . (int) $id . '/edit'));
    }

    public function results(string $id): void
    {
        require_auth();

        $campaign = $this->campaigns->find((int) $id);

        if (!$campaign) {
            http_response_code(404);
            render('home/404', ['title' => '404']);
            return;
        }

        [$tableColumns, $tableRows] = $this->campaigns->resultsTable($campaign);

        render('admin/campaigns/results', [
            'title' => 'Resultados de campaña',
            'campaign' => $campaign,
            'tableColumns' => $tableColumns,
            'tableRows' => $tableRows,
        ]);
    }

    public function export(string $id): void
    {
        require_auth();

        $campaign = $this->campaigns->find((int) $id);

        if (!$campaign) {
            http_response_code(404);
            render('home/404', ['title' => '404']);
            return;
        }

        [$columns, $rows] = $this->campaigns->resultsTable($campaign);

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="campaign-' . (int) $campaign['id'] . '-results.xls"');

        echo '<table border="1"><tr>';
        foreach ($columns as $column) {
            echo '<th>' . e($column) . '</th>';
        }
        echo '</tr>';

        foreach ($rows as $row) {
            echo '<tr>';
            foreach ($columns as $column) {
                echo '<td>' . e((string) ($row[$column] ?? '')) . '</td>';
            }
            echo '</tr>';
        }

        echo '</table>';
        exit;
    }

    private function validateCampaignInput(): array
    {
        $campaignData = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'type' => (string) ($_POST['type'] ?? ''),
            'status' => (string) ($_POST['status'] ?? ''),
            'title' => trim((string) ($_POST['title'] ?? '')),
            'description' => trim((string) ($_POST['description'] ?? '')),
            'thank_you_message' => trim((string) ($_POST['thank_you_message'] ?? '')),
            'starts_at' => (string) ($_POST['starts_at'] ?? ''),
            'ends_at' => (string) ($_POST['ends_at'] ?? ''),
        ];

        $blocks = is_array($_POST['blocks'] ?? null) ? $_POST['blocks'] : [];
        $questions = is_array($_POST['questions'] ?? null) ? $_POST['questions'] : [];
        $errors = [];

        if ($campaignData['name'] === '') {
            $errors['name'] = 'El nombre interno es obligatorio.';
        }

        if ($campaignData['title'] === '') {
            $errors['title'] = 'El título público es obligatorio.';
        }

        if (!array_key_exists($campaignData['type'], CampaignModel::campaignTypes())) {
            $errors['type'] = 'Tipo de campaña inválido.';
        }

        if (!array_key_exists($campaignData['status'], CampaignModel::statuses())) {
            $errors['status'] = 'Estado inválido.';
        }

        if ($campaignData['starts_at'] !== '' && strtotime($campaignData['starts_at']) === false) {
            $errors['starts_at'] = 'Fecha de inicio inválida.';
        }

        if ($campaignData['ends_at'] !== '' && strtotime($campaignData['ends_at']) === false) {
            $errors['ends_at'] = 'Fecha de cierre inválida.';
        }

        if ($campaignData['starts_at'] !== '' && $campaignData['ends_at'] !== '' && strtotime($campaignData['ends_at']) < strtotime($campaignData['starts_at'])) {
            $errors['ends_at'] = 'La fecha de cierre no puede ser anterior al inicio.';
        }

        foreach ($questions as $index => $question) {
            $label = trim((string) ($question['label'] ?? ''));
            $type = (string) ($question['type'] ?? '');
            $supportType = (string) ($question['support_type'] ?? 'text');
            $supportContent = trim((string) ($question['support_content'] ?? ''));

            if ($label === '' && $type === '') {
                continue;
            }

            if ($label === '') {
                $errors['questions.' . $index . '.label'] = 'Cada pregunta debe tener enunciado.';
            }

            if (!array_key_exists($type, CampaignModel::questionTypes())) {
                $errors['questions.' . $index . '.type'] = 'Tipo de pregunta inválido.';
            }

            if (!array_key_exists($supportType, CampaignModel::supportTypes())) {
                $errors['questions.' . $index . '.support_type'] = 'Tipo de apoyo inválido.';
            }

            if (in_array($supportType, ['image', 'video'], true) && $supportContent !== '' && !filter_var($supportContent, FILTER_VALIDATE_URL)) {
                $errors['questions.' . $index . '.support_content'] = 'La URL del apoyo debe ser válida.';
            }

            if (in_array($type, [CampaignModel::QUESTION_SINGLE_CHOICE, CampaignModel::QUESTION_MULTIPLE_CHOICE], true) && normalize_lines($question['options_text'] ?? '') === []) {
                $errors['questions.' . $index . '.options_text'] = 'Debes definir opciones para las preguntas de selección.';
            }
        }

        return [$campaignData, $blocks, $questions, $errors];
    }

    private function normalizeBlockFiles(): array
    {
        $files = $_FILES['blocks'] ?? null;

        if (!$files || !is_array($files['name'] ?? null)) {
            return [];
        }

        $normalized = [];

        foreach ($files['name'] as $index => $fileSet) {
            if (!isset($fileSet['image'])) {
                continue;
            }

            $normalized[$index] = [
                'name' => $files['name'][$index]['image'],
                'type' => $files['type'][$index]['image'],
                'tmp_name' => $files['tmp_name'][$index]['image'],
                'error' => $files['error'][$index]['image'],
                'size' => $files['size'][$index]['image'],
            ];
        }

        return $normalized;
    }
}
