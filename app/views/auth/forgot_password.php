<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="soft-panel p-4 p-lg-5">
            <h1 class="h3 mb-3">Recuperar acceso</h1>
            <p class="text-secondary">Ingresa tu correo y generaremos un enlace para restablecer tu contraseña.</p>

            <form method="post" action="<?= e(base_url('forgot-password')) ?>" class="vstack gap-3">
                <?= csrf_field() ?>
                <div>
                    <label class="form-label" for="email">Correo</label>
                    <input class="form-control <?= error_for('email') ? 'is-invalid' : '' ?>" id="email" type="email" name="email" value="<?= e((string) old('email', '')) ?>" required>
                    <?php if (error_for('email')): ?>
                        <div class="invalid-feedback d-block"><?= e((string) error_for('email')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between gap-3">
                    <a class="btn btn-outline-secondary" href="<?= e(base_url('login')) ?>">Volver</a>
                    <button class="btn btn-primary" type="submit">Generar enlace</button>
                </div>
            </form>
        </div>
    </div>
</div>
