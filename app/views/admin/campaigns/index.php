<section class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
    <div>
        <h1 class="h2 mb-2">Campañas</h1>
        <p class="text-secondary mb-0">Crea campañas tipo cuestionario o informativas, genera tokens únicos y revisa respuestas desde un solo panel.</p>
    </div>
    <a href="<?= e(base_url('admin/campaigns/create')) ?>" class="btn btn-primary">Nueva campaña</a>
</section>

<div class="vstack gap-3">
    <?php if ($campaigns): ?>
        <?php foreach ($campaigns as $campaign): ?>
            <article class="soft-panel p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
                    <div>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge text-bg-light border"><?= e($campaign['token']) ?></span>
                            <span class="badge text-bg-success"><?= e($statuses[$campaign['status']] ?? $campaign['status']) ?></span>
                            <span class="badge text-bg-secondary"><?= e($types[$campaign['type']] ?? $campaign['type']) ?></span>
                        </div>
                        <h2 class="h4 mb-2"><?= e($campaign['title']) ?></h2>
                        <p class="text-secondary mb-0"><?= e((string) ($campaign['description'] ?? '')) ?></p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-content-start">
                        <a href="<?= e(base_url('c/' . $campaign['token'])) ?>" class="btn btn-outline-secondary" target="_blank">Ver pública</a>
                        <a href="<?= e(base_url('admin/campaigns/' . $campaign['id'] . '/results')) ?>" class="btn btn-outline-secondary">Resultados (<?= e((string) $campaign['submissions_count']) ?>)</a>
                        <a href="<?= e(base_url('admin/campaigns/' . $campaign['id'] . '/edit')) ?>" class="btn btn-primary">Editar</a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="soft-panel p-4">
            <p class="text-secondary mb-0">No hay campañas creadas todavía.</p>
        </div>
    <?php endif; ?>
</div>
