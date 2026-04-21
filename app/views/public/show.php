<?php $oldAnswers = old('answers', []); ?>

<section class="soft-panel p-4 p-lg-5 mb-4">
    <div class="row g-4 align-items-start">
        <div class="col-lg-8">
            <div class="text-secondary text-uppercase small mb-2"><?= e(\App\Models\CampaignModel::campaignTypes()[$campaign['type']] ?? $campaign['type']) ?></div>
            <h1 class="display-6 mb-3"><?= e($campaign['title']) ?></h1>
            <p class="text-secondary mb-0"><?= e((string) ($campaign['description'] ?? '')) ?></p>

            <?php if (!empty($isPreview)): ?>
                <div class="alert alert-info mt-4 mb-0">Vista previa de administrador. Esta campaña sigue en borrador y no es visible para el público.</div>
            <?php endif; ?>

            <?php if ($campaign['status'] === \App\Models\CampaignModel::STATUS_CLOSED): ?>
                <div class="alert alert-warning mt-4 mb-0">Esta campaña está cerrada. El contenido sigue disponible, pero ya no acepta nuevas respuestas.</div>
            <?php endif; ?>
        </div>
        <div class="col-lg-4">
            <div class="border rounded-4 p-3 bg-light-subtle">
                <div class="small text-secondary text-uppercase mb-3">Compartir</div>
                <div class="bg-white rounded-4 d-flex justify-content-center p-3 mb-3">
                    <div data-qr-url="<?= e(full_url('c/' . $campaign['token'])) ?>"></div>
                </div>
                <div class="small text-break"><?= e(full_url('c/' . $campaign['token'])) ?></div>
            </div>
        </div>
    </div>
</section>

<?php if ($campaign['blocks']): ?>
    <section class="row g-4 mb-4">
        <?php foreach ($campaign['blocks'] as $block): ?>
            <div class="col-lg-6">
                <article class="soft-panel h-100 overflow-hidden <?= $block['type'] === 'image' ? 'p-0' : 'p-4' ?>">
                    <?php if ($block['type'] === 'image' && !empty($block['media_path'])): ?>
                        <img src="<?= e(storage_url($block['media_path'])) ?>" alt="<?= e((string) ($block['title'] ?? '')) ?>" class="public-block-image">
                        <div class="p-4">
                            <?php if (!empty($block['title'])): ?><h2 class="h4"><?= e($block['title']) ?></h2><?php endif; ?>
                            <?php if (!empty($block['content'])): ?><p class="text-secondary mb-0"><?= e($block['content']) ?></p><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($block['title'])): ?><h2 class="h4"><?= e($block['title']) ?></h2><?php endif; ?>
                        <?php if (!empty($block['content'])): ?><p class="text-secondary mb-0"><?= e($block['content']) ?></p><?php endif; ?>
                    <?php endif; ?>
                </article>
            </div>
        <?php endforeach; ?>
    </section>
<?php endif; ?>

<?php if ($campaign['type'] === \App\Models\CampaignModel::TYPE_FORM): ?>
    <section class="soft-panel p-4">
        <h2 class="h3 mb-4">Responder campaña</h2>
        <form action="<?= e(base_url('c/' . $campaign['token'])) ?>" method="post" enctype="multipart/form-data" class="vstack gap-4">
            <?= csrf_field() ?>
            <?php foreach ($campaign['questions'] as $question): ?>
                <?php $questionId = (int) $question['id']; ?>
                <div class="builder-card p-3">
                    <label class="form-label fw-semibold">
                        <?= e($question['label']) ?>
                        <?php if ((int) $question['is_required'] === 1): ?><span class="text-danger">*</span><?php endif; ?>
                    </label>

                    <?php if (($question['support_type'] ?? 'text') === 'text' && !empty($question['support_content'])): ?>
                        <div class="small text-secondary mb-3"><?= e($question['support_content']) ?></div>
                    <?php elseif (($question['support_type'] ?? '') === 'image' && !empty($question['support_content'])): ?>
                        <div class="mb-3">
                            <img src="<?= e($question['support_content']) ?>" alt="Apoyo de la pregunta" class="img-fluid rounded-3 border">
                        </div>
                    <?php elseif (($question['support_type'] ?? '') === 'video' && !empty($question['support_content'])): ?>
                        <div class="ratio ratio-16x9 mb-3">
                            <iframe src="<?= e(video_embed_url($question['support_content'])) ?>" title="Video de apoyo" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        </div>
                    <?php endif; ?>

                    <?php if ($question['type'] === \App\Models\CampaignModel::QUESTION_TEXT): ?>
                        <textarea class="form-control" name="answers[<?= $questionId ?>]" rows="4" placeholder="<?= e((string) ($question['placeholder'] ?? '')) ?>"><?= e((string) ($oldAnswers[$questionId] ?? '')) ?></textarea>
                    <?php elseif ($question['type'] === \App\Models\CampaignModel::QUESTION_EMAIL): ?>
                        <input class="form-control" type="email" name="answers[<?= $questionId ?>]" value="<?= e((string) ($oldAnswers[$questionId] ?? '')) ?>" placeholder="<?= e((string) ($question['placeholder'] ?? '')) ?>">
                    <?php elseif ($question['type'] === \App\Models\CampaignModel::QUESTION_NUMBER): ?>
                        <input class="form-control" type="number" step="any" name="answers[<?= $questionId ?>]" value="<?= e((string) ($oldAnswers[$questionId] ?? '')) ?>" placeholder="<?= e((string) ($question['placeholder'] ?? '')) ?>">
                    <?php elseif ($question['type'] === \App\Models\CampaignModel::QUESTION_DATE): ?>
                        <input class="form-control" type="date" name="answers[<?= $questionId ?>]" value="<?= e((string) ($oldAnswers[$questionId] ?? '')) ?>">
                    <?php elseif ($question['type'] === \App\Models\CampaignModel::QUESTION_FILE): ?>
                        <input class="form-control" type="file" name="answers[<?= $questionId ?>]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                        <div class="form-text">Máximo 10 MB. Tipos permitidos: PDF, Word, Excel e imágenes.</div>
                    <?php elseif ($question['type'] === \App\Models\CampaignModel::QUESTION_MULTIPLE_CHOICE): ?>
                        <div class="vstack gap-2">
                            <?php foreach ($question['options'] as $option): ?>
                                <?php $selectedValues = is_array($oldAnswers[$questionId] ?? null) ? $oldAnswers[$questionId] : []; ?>
                                <label class="form-check border rounded-3 p-3 bg-white">
                                    <input class="form-check-input me-2" type="checkbox" name="answers[<?= $questionId ?>][]" value="<?= e($option['value']) ?>" <?= in_array($option['value'], $selectedValues, true) ? 'checked' : '' ?>>
                                    <span class="form-check-label"><?= e($option['label']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="vstack gap-2">
                            <?php foreach ($question['options'] as $option): ?>
                                <label class="form-check border rounded-3 p-3 bg-white">
                                    <input class="form-check-input me-2" type="radio" name="answers[<?= $questionId ?>]" value="<?= e($option['value']) ?>" <?= (($oldAnswers[$questionId] ?? null) === $option['value']) ? 'checked' : '' ?>>
                                    <span class="form-check-label"><?= e($option['label']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (error_for('answers.' . $questionId)): ?>
                        <div class="text-danger small mt-2"><?= e((string) error_for('answers.' . $questionId)) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if ($campaign['status'] === \App\Models\CampaignModel::STATUS_PUBLISHED): ?>
                <button class="btn btn-primary" type="submit">Enviar respuestas</button>
            <?php elseif (!empty($isPreview)): ?>
                <div class="alert alert-secondary mb-0">La campaña está en borrador. Puedes revisar el contenido, pero no enviar respuestas hasta publicarla.</div>
            <?php endif; ?>
        </form>
    </section>
<?php endif; ?>
