<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="soft-panel p-4 p-lg-5">
            <h1 class="h3 mb-3">Cambiar contraseña</h1>
            <p class="text-secondary">Actualiza la contraseña de la cuenta <strong><?= e((string) ($user['email'] ?? '')) ?></strong>.</p>
            <div class="alert alert-light border small mb-3"><?= e($passwordPolicyText) ?></div>

            <form method="post" action="<?= e(base_url('change-password')) ?>" class="vstack gap-3">
                <?= csrf_field() ?>

                <div>
                    <label class="form-label" for="current_password">Contraseña actual</label>
                    <input class="form-control <?= error_for('current_password') ? 'is-invalid' : '' ?>" id="current_password" type="password" name="current_password" required autofocus>
                    <?php if (error_for('current_password')): ?>
                        <div class="invalid-feedback d-block"><?= e((string) error_for('current_password')) ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="form-label" for="password">Nueva contraseña</label>
                    <input class="form-control <?= error_for('password') ? 'is-invalid' : '' ?>" id="password" type="password" name="password" required>
                    <?php if (error_for('password')): ?>
                        <div class="invalid-feedback d-block"><?= e((string) error_for('password')) ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                    <input class="form-control <?= error_for('password_confirmation') ? 'is-invalid' : '' ?>" id="password_confirmation" type="password" name="password_confirmation" required>
                    <?php if (error_for('password_confirmation')): ?>
                        <div class="invalid-feedback d-block"><?= e((string) error_for('password_confirmation')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <?php if (!(bool) ($user['must_change_password'] ?? false)): ?>
                        <a class="btn btn-outline-secondary" href="<?= e(base_url('admin')) ?>">Cancelar</a>
                    <?php endif; ?>
                    <button class="btn btn-primary" type="submit">Actualizar contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>
