<section class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
    <div>
        <h1 class="h2 mb-2"><?= e($campaign['title']) ?></h1>
        <p class="text-secondary mb-0"><?= e((string) ($campaign['description'] ?? '')) ?></p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= e(base_url('admin/campaigns/' . $campaign['id'] . '/edit')) ?>" class="btn btn-outline-secondary">Editar</a>
        <a href="<?= e(base_url('admin/campaigns/' . $campaign['id'] . '/export')) ?>" class="btn btn-outline-secondary">Exportar Excel</a>
        <a href="<?= e(base_url('c/' . $campaign['token'])) ?>" class="btn btn-primary" target="_blank">Ver pública</a>
    </div>
</section>

<section class="soft-panel p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <div>
            <h2 class="h4 mb-1">Vista tabular</h2>
            <p class="text-secondary mb-0">Cada fila representa un envío y cada columna una pregunta.</p>
        </div>
        <span class="badge text-bg-light border"><?= e((string) count($tableRows)) ?> filas</span>
    </div>

    <div class="table-responsive">
        <?php if ($tableRows): ?>
            <table class="table table-bordered table-striped align-middle mb-0 bg-white">
                <thead class="table-light">
                <tr>
                    <?php foreach ($tableColumns as $column): ?>
                        <th><?= e($column) ?></th>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($tableRows as $row): ?>
                    <tr>
                        <?php foreach ($tableColumns as $column): ?>
                            <td><?= e((string) ($row[$column] ?? '')) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-secondary mb-0">Esta campaña aún no tiene respuestas para mostrar.</p>
        <?php endif; ?>
    </div>
</section>

<section class="soft-panel p-4">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
        <h2 class="h4 mb-0">Respuestas (<?= e((string) count($campaign['submissions'])) ?>)</h2>
        <span class="badge text-bg-light border"><?= e($campaign['token']) ?></span>
    </div>

    <div class="vstack gap-3">
        <?php if ($campaign['submissions']): ?>
            <?php foreach ($campaign['submissions'] as $submission): ?>
                <article class="builder-card p-3">
                    <div class="small text-secondary mb-3">
                        <?= e($submission['submitted_at']) ?>
                        <?php if (!empty($submission['ip_address'])): ?>
                            · IP: <?= e($submission['ip_address']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($submission['answers'] as $answer): ?>
                            <div class="col-md-6">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="small text-secondary text-uppercase mb-2"><?= e($answer['question_label']) ?></div>
                                    <?php if (!empty($answer['value_file_path'])): ?>
                                        <a href="<?= e(storage_url($answer['value_file_path'])) ?>" target="_blank"><?= e($answer['original_file_name'] ?: 'Descargar archivo') ?></a>
                                    <?php elseif (!empty($answer['value_json'])): ?>
                                        <div><?= e(implode(', ', json_decode((string) $answer['value_json'], true) ?: [])) ?></div>
                                    <?php else: ?>
                                        <div><?= e((string) $answer['value_text']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-secondary mb-0">Esta campaña aún no tiene respuestas.</p>
        <?php endif; ?>
    </div>
</section>
