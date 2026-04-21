<?php
$formAction = $isEditing
    ? base_url('admin/campaigns/' . $campaign['id'] . '/update')
    : base_url('admin/campaigns');
?>

<form method="post" action="<?= e($formAction) ?>" enctype="multipart/form-data" class="vstack gap-4">
    <?= csrf_field() ?>

    <section class="soft-panel p-4 p-lg-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
            <div>
                <h1 class="h2 mb-2"><?= $isEditing ? 'Editar campaña' : 'Nueva campaña' ?></h1>
                <p class="text-secondary mb-0">Define el tipo de campaña, configura su contenido público y arma el cuestionario en la misma pantalla.</p>
            </div>
            <?php if ($isEditing): ?>
                <div class="border rounded-4 p-3 bg-light-subtle">
                    <div class="small text-secondary">Token</div>
                    <div class="fw-semibold"><?= e($campaign['token']) ?></div>
                    <div class="small text-break mt-2"><?= e(full_url('c/' . $campaign['token'])) ?></div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="row g-4 align-items-start">
        <div class="col-xl-8">
            <div class="vstack gap-4">
                <div class="soft-panel p-4">
                    <h2 class="h4 mb-4">Datos base</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Nombre interno</label>
                            <input class="form-control <?= error_for('name') ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= e((string) old('name', $campaign['name'])) ?>" required>
                            <?php if (error_for('name')): ?><div class="invalid-feedback d-block"><?= e((string) error_for('name')) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="title">Título público</label>
                            <input class="form-control <?= error_for('title') ? 'is-invalid' : '' ?>" id="title" name="title" value="<?= e((string) old('title', $campaign['title'])) ?>" required>
                            <?php if (error_for('title')): ?><div class="invalid-feedback d-block"><?= e((string) error_for('title')) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="type">Tipo</label>
                            <select class="form-select <?= error_for('type') ? 'is-invalid' : '' ?>" id="type" name="type">
                                <?php foreach ($types as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= old('type', $campaign['type']) === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (error_for('type')): ?><div class="invalid-feedback d-block"><?= e((string) error_for('type')) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="status">Estado</label>
                            <select class="form-select <?= error_for('status') ? 'is-invalid' : '' ?>" id="status" name="status">
                                <?php foreach ($statuses as $value => $label): ?>
                                    <option value="<?= e($value) ?>" <?= old('status', $campaign['status']) === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (error_for('status')): ?><div class="invalid-feedback d-block"><?= e((string) error_for('status')) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="starts_at">Inicio</label>
                            <input class="form-control <?= error_for('starts_at') ? 'is-invalid' : '' ?>" id="starts_at" type="datetime-local" name="starts_at" value="<?= e((string) old('starts_at', to_datetime_local($campaign['starts_at'] ?? null))) ?>">
                            <?php if (error_for('starts_at')): ?><div class="invalid-feedback d-block"><?= e((string) error_for('starts_at')) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="ends_at">Cierre</label>
                            <input class="form-control <?= error_for('ends_at') ? 'is-invalid' : '' ?>" id="ends_at" type="datetime-local" name="ends_at" value="<?= e((string) old('ends_at', to_datetime_local($campaign['ends_at'] ?? null))) ?>">
                            <?php if (error_for('ends_at')): ?><div class="invalid-feedback d-block"><?= e((string) error_for('ends_at')) ?></div><?php endif; ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description">Descripción pública</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?= e((string) old('description', $campaign['description'])) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="thank_you_message">Mensaje de confirmación</label>
                            <textarea class="form-control" id="thank_you_message" name="thank_you_message" rows="3"><?= e((string) old('thank_you_message', $campaign['thank_you_message'])) ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="soft-panel p-4" data-repeater>
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <h2 class="h4 mb-1">Bloques públicos</h2>
                            <p class="text-secondary mb-0">Sirven tanto para campañas informativas como para enriquecer formularios.</p>
                        </div>
                        <button class="btn btn-outline-secondary" type="button" data-repeater-add>Añadir bloque</button>
                    </div>

                    <div class="vstack gap-3" data-repeater-list>
                        <?php foreach ($blocks as $index => $block): ?>
                            <div class="builder-card p-3" data-repeater-item>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h3 class="h6 mb-0">Bloque</h3>
                                    <button class="btn btn-sm btn-outline-danger" type="button" data-repeater-remove>Eliminar</button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tipo</label>
                                        <select class="form-select" data-block-type data-template-name="blocks[__INDEX__][type]" data-name="blocks[<?= e((string) $index) ?>][type]">
                                            <option value="text" <?= ($block['type'] ?? 'text') === 'text' ? 'selected' : '' ?>>Texto</option>
                                            <option value="image" <?= ($block['type'] ?? '') === 'image' ? 'selected' : '' ?>>Imagen</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Título</label>
                                        <input class="form-control" data-template-name="blocks[__INDEX__][title]" data-name="blocks[<?= e((string) $index) ?>][title]" value="<?= e((string) ($block['title'] ?? '')) ?>">
                                    </div>
                                    <input type="hidden" data-template-name="blocks[__INDEX__][existing_path]" data-name="blocks[<?= e((string) $index) ?>][existing_path]" value="<?= e((string) ($block['existing_path'] ?? '')) ?>">
                                    <div class="col-12 block-text-box">
                                        <label class="form-label">Contenido</label>
                                        <textarea class="form-control" rows="4" data-template-name="blocks[__INDEX__][content]" data-name="blocks[<?= e((string) $index) ?>][content]"><?= e((string) ($block['content'] ?? '')) ?></textarea>
                                    </div>
                                    <div class="col-12 block-image-box d-none">
                                        <label class="form-label">Imagen</label>
                                        <input class="form-control" type="file" accept="image/*" data-template-name="blocks[__INDEX__][image]" data-name="blocks[<?= e((string) $index) ?>][image]">
                                        <?php if (!empty($block['existing_path'])): ?>
                                            <div class="form-text">Imagen actual: <?= e($block['existing_path']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <template>
                        <div class="builder-card p-3" data-repeater-item>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h3 class="h6 mb-0">Bloque</h3>
                                <button class="btn btn-sm btn-outline-danger" type="button" data-repeater-remove>Eliminar</button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tipo</label>
                                    <select class="form-select" data-block-type data-template-name="blocks[__INDEX__][type]">
                                        <option value="text">Texto</option>
                                        <option value="image">Imagen</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Título</label>
                                    <input class="form-control" data-template-name="blocks[__INDEX__][title]">
                                </div>
                                <input type="hidden" data-template-name="blocks[__INDEX__][existing_path]" value="">
                                <div class="col-12 block-text-box">
                                    <label class="form-label">Contenido</label>
                                    <textarea class="form-control" rows="4" data-template-name="blocks[__INDEX__][content]"></textarea>
                                </div>
                                <div class="col-12 block-image-box d-none">
                                    <label class="form-label">Imagen</label>
                                    <input class="form-control" type="file" accept="image/*" data-template-name="blocks[__INDEX__][image]">
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="soft-panel p-4" data-repeater>
                    <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <h2 class="h4 mb-1">Preguntas</h2>
                            <p class="text-secondary mb-0">Disponible para campañas tipo cuestionario.</p>
                        </div>
                        <button class="btn btn-outline-secondary" type="button" data-repeater-add>Añadir pregunta</button>
                    </div>

                    <div class="vstack gap-3" data-repeater-list>
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="builder-card p-3" data-repeater-item>
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h3 class="h6 mb-0">Pregunta</h3>
                                    <button class="btn btn-sm btn-outline-danger" type="button" data-repeater-remove>Eliminar</button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Enunciado</label>
                                        <input class="form-control" data-template-name="questions[__INDEX__][label]" data-name="questions[<?= e((string) $index) ?>][label]" value="<?= e((string) ($question['label'] ?? '')) ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tipo</label>
                                        <select class="form-select" data-question-type data-template-name="questions[__INDEX__][type]" data-name="questions[<?= e((string) $index) ?>][type]">
                                            <?php foreach ($questionTypes as $value => $label): ?>
                                                <option value="<?= e($value) ?>" <?= ($question['type'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tipo de apoyo</label>
                                        <select class="form-select" data-support-type data-template-name="questions[__INDEX__][support_type]" data-name="questions[<?= e((string) $index) ?>][support_type]">
                                            <?php foreach ($supportTypes as $value => $label): ?>
                                                <option value="<?= e($value) ?>" <?= (($question['support_type'] ?? 'text') === $value) ? 'selected' : '' ?>><?= e($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-12 support-text-box">
                                        <label class="form-label">Texto de apoyo</label>
                                        <textarea class="form-control" rows="3" data-template-name="questions[__INDEX__][support_content]" data-name="questions[<?= e((string) $index) ?>][support_content]"><?= e((string) ($question['support_content'] ?? '')) ?></textarea>
                                    </div>
                                    <div class="col-12 support-url-box d-none">
                                        <label class="form-label">URL de imagen o video</label>
                                        <input class="form-control" type="url" placeholder="https://..." data-template-name="questions[__INDEX__][support_content]" data-name="questions[<?= e((string) $index) ?>][support_content]" value="<?= e((string) ($question['support_content'] ?? '')) ?>">
                                        <div class="form-text">Para video usa una URL pública, idealmente de YouTube o Vimeo.</div>
                                    </div>
                                    <div class="col-12 placeholder-box">
                                        <label class="form-label">Placeholder</label>
                                        <input class="form-control" data-template-name="questions[__INDEX__][placeholder]" data-name="questions[<?= e((string) $index) ?>][placeholder]" value="<?= e((string) ($question['placeholder'] ?? '')) ?>">
                                    </div>
                                    <div class="col-12 option-box d-none">
                                        <label class="form-label">Opciones</label>
                                        <textarea class="form-control" rows="4" data-template-name="questions[__INDEX__][options_text]" data-name="questions[<?= e((string) $index) ?>][options_text]"><?= e((string) ($question['options_text'] ?? '')) ?></textarea>
                                        <div class="form-text">Una opción por línea.</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input type="hidden" data-template-name="questions[__INDEX__][required]" data-name="questions[<?= e((string) $index) ?>][required]" value="0">
                                            <input class="form-check-input" id="required_<?= e((string) $index) ?>" type="checkbox" data-template-name="questions[__INDEX__][required]" data-name="questions[<?= e((string) $index) ?>][required]" value="1" <?= !empty($question['required']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="required_<?= e((string) $index) ?>">Respuesta obligatoria</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <template>
                        <div class="builder-card p-3" data-repeater-item>
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h3 class="h6 mb-0">Pregunta</h3>
                                <button class="btn btn-sm btn-outline-danger" type="button" data-repeater-remove>Eliminar</button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Enunciado</label>
                                    <input class="form-control" data-template-name="questions[__INDEX__][label]">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipo</label>
                                    <select class="form-select" data-question-type data-template-name="questions[__INDEX__][type]">
                                        <?php foreach ($questionTypes as $value => $label): ?>
                                            <option value="<?= e($value) ?>"><?= e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tipo de apoyo</label>
                                    <select class="form-select" data-support-type data-template-name="questions[__INDEX__][support_type]">
                                        <?php foreach ($supportTypes as $value => $label): ?>
                                            <option value="<?= e($value) ?>"><?= e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 support-text-box">
                                    <label class="form-label">Texto de apoyo</label>
                                    <textarea class="form-control" rows="3" data-template-name="questions[__INDEX__][support_content]"></textarea>
                                </div>
                                <div class="col-12 support-url-box d-none">
                                    <label class="form-label">URL de imagen o video</label>
                                    <input class="form-control" type="url" placeholder="https://..." data-template-name="questions[__INDEX__][support_content]">
                                    <div class="form-text">Para video usa una URL pública, idealmente de YouTube o Vimeo.</div>
                                </div>
                                <div class="col-12 placeholder-box">
                                    <label class="form-label">Placeholder</label>
                                    <input class="form-control" data-template-name="questions[__INDEX__][placeholder]">
                                </div>
                                <div class="col-12 option-box d-none">
                                    <label class="form-label">Opciones</label>
                                    <textarea class="form-control" rows="4" data-template-name="questions[__INDEX__][options_text]"></textarea>
                                    <div class="form-text">Una opción por línea.</div>
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input type="hidden" data-template-name="questions[__INDEX__][required]" value="0">
                                        <input class="form-check-input" type="checkbox" data-template-name="questions[__INDEX__][required]" value="1">
                                        <label class="form-check-label">Respuesta obligatoria</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="soft-panel p-4 position-sticky" style="top: 1rem;">
                <h2 class="h4 mb-3">Acciones</h2>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" type="submit"><?= $isEditing ? 'Guardar cambios' : 'Crear campaña' ?></button>
                    <?php if ($isEditing): ?>
                        <a class="btn btn-outline-secondary" href="<?= e(base_url('c/' . $campaign['token'])) ?>" target="_blank">Abrir pública</a>
                        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/campaigns/' . $campaign['id'] . '/results')) ?>">Ver resultados</a>
                    <?php endif; ?>
                    <a class="btn btn-outline-secondary" href="<?= e(base_url('admin')) ?>">Volver al panel</a>
                </div>

                <div class="mt-4 border rounded-4 p-3 bg-light-subtle">
                    <div class="fw-semibold mb-2">Incluido en esta versión</div>
                    <ul class="mb-0 ps-3 small text-secondary">
                        <li>Token único por campaña</li>
                        <li>QR en la vista pública</li>
                        <li>Bloques de texto e imagen</li>
                        <li>Adjuntos y validaciones por tipo</li>
                        <li>Exportación Excel compatible</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</form>
