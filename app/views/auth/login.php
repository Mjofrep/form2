<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="soft-panel p-4 p-lg-5">
            <div class="mb-4 text-center">
                <h1 class="h3 mb-2">Ingreso administrador</h1>
                <p class="text-secondary mb-0">Solo usuarios autorizados pueden acceder al panel.</p>
            </div>

            <div class="alert alert-light border small mb-4">
                Si tu cuenta aún no tiene contraseña, ingresa solo tu correo y el sistema te solicitará crearla.
            </div>

            <form method="post" action="<?= e(base_url('login')) ?>" class="vstack gap-3">
                <?= csrf_field() ?>
                <div>
                    <label class="form-label" for="email">Correo</label>
                    <input class="form-control <?= error_for('email') ? 'is-invalid' : '' ?>" id="email" type="email" name="email" value="<?= e((string) old('email', '')) ?>" required autofocus>
                    <?php if (error_for('email')): ?>
                        <div class="invalid-feedback d-block"><?= e((string) error_for('email')) ?></div>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="form-label" for="password">Contraseña</label>
                    <input class="form-control <?= error_for('password') ? 'is-invalid' : '' ?>" id="password" type="password" name="password">
                    <?php if (error_for('password')): ?>
                        <div class="invalid-feedback d-block"><?= e((string) error_for('password')) ?></div>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-between align-items-center gap-3">
                    <a href="<?= e(base_url('forgot-password')) ?>" class="small">Recuperar acceso</a>
                    <button class="btn btn-primary" type="submit">Ingresar</button>
                </div>
            </form>
        </div>
    </div>
</div>
