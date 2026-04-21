<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="soft-panel p-4 p-lg-5">
            <h1 class="h3 mb-3">Restablecer contraseña</h1>
            <form method="post" action="<?= e(base_url('reset-password')) ?>" class="vstack gap-3">
                <?= csrf_field() ?>
                <input type="hidden" name="email" value="<?= e((string) old('email', $email ?? '')) ?>">
                <input type="hidden" name="token" value="<?= e((string) old('token', $token ?? '')) ?>">

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

                <div class="d-flex justify-content-between gap-3">
                    <a class="btn btn-outline-secondary" href="<?= e(base_url('login')) ?>">Volver</a>
                    <button class="btn btn-primary" type="submit">Actualizar contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>
