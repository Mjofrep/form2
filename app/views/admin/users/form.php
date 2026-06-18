<?php
$isEditing = $isEditing ?? false;
$targetUser = $user ?? [];
?>

<section class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
    <div>
        <h1 class="h2 mb-2"><?= $isEditing ? 'Editar usuario' : 'Nuevo usuario' ?></h1>
        <p class="text-secondary mb-0">Define el rol, el estado y la contraseña temporal para controlar el acceso al módulo.</p>
    </div>
    <a href="<?= e(base_url('admin/users')) ?>" class="btn btn-outline-secondary">Volver</a>
</section>

<div class="soft-panel p-4 p-lg-5">
    <form method="post" action="<?= e($isEditing ? base_url('admin/users/' . $targetUser['id'] . '/update') : base_url('admin/users')) ?>" class="vstack gap-4">
        <?= csrf_field() ?>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="name">Nombre</label>
                <input class="form-control <?= error_for('name') ? 'is-invalid' : '' ?>" id="name" type="text" name="name" value="<?= e((string) old('name', $targetUser['name'] ?? '')) ?>" required>
                <?php if (error_for('name')): ?>
                    <div class="invalid-feedback d-block"><?= e((string) error_for('name')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="email">Correo</label>
                <input class="form-control <?= error_for('email') ? 'is-invalid' : '' ?>" id="email" type="email" name="email" value="<?= e((string) old('email', $targetUser['email'] ?? '')) ?>" required>
                <?php if (error_for('email')): ?>
                    <div class="invalid-feedback d-block"><?= e((string) error_for('email')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="role">Rol</label>
                <select class="form-select <?= error_for('role') ? 'is-invalid' : '' ?>" id="role" name="role" required>
                    <?php $selectedRole = (string) old('role', $targetUser['role'] ?? \App\Models\UserModel::ROLE_USER); ?>
                    <?php foreach ($roles as $roleValue => $roleLabel): ?>
                        <option value="<?= e($roleValue) ?>" <?= $selectedRole === $roleValue ? 'selected' : '' ?>><?= e($roleLabel) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (error_for('role')): ?>
                    <div class="invalid-feedback d-block"><?= e((string) error_for('role')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="password"><?= $isEditing ? 'Nueva contraseña temporal' : 'Contraseña temporal' ?></label>
                <input class="form-control <?= error_for('password') ? 'is-invalid' : '' ?>" id="password" type="password" name="password" <?= $isEditing ? '' : 'required' ?>>
                <div class="form-text"><?= e(password_policy_text()) ?><?= $isEditing ? ' Déjalo vacío si no deseas cambiarla.' : '' ?></div>
                <?php if (error_for('password')): ?>
                    <div class="invalid-feedback d-block"><?= e((string) error_for('password')) ?></div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
                <input class="form-control <?= error_for('password_confirmation') ? 'is-invalid' : '' ?>" id="password_confirmation" type="password" name="password_confirmation" <?= $isEditing ? '' : 'required' ?>>
                <?php if (error_for('password_confirmation')): ?>
                    <div class="invalid-feedback d-block"><?= e((string) error_for('password_confirmation')) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex flex-column gap-2">
            <div class="form-check">
                <input class="form-check-input" id="is_active" type="checkbox" name="is_active" value="1" <?= (int) old('is_active', $targetUser['is_active'] ?? 1) === 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="is_active">Usuario activo</label>
                <?php if (error_for('is_active')): ?>
                    <div class="text-danger small mt-1"><?= e((string) error_for('is_active')) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-check">
                <input class="form-check-input" id="must_change_password" type="checkbox" name="must_change_password" value="1" <?= (int) old('must_change_password', $targetUser['must_change_password'] ?? 1) === 1 ? 'checked' : '' ?>>
                <label class="form-check-label" for="must_change_password">Exigir cambio de contraseña en el próximo ingreso</label>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-3">
            <a class="btn btn-outline-secondary" href="<?= e(base_url('admin/users')) ?>">Cancelar</a>
            <button class="btn btn-primary" type="submit"><?= $isEditing ? 'Guardar cambios' : 'Crear usuario' ?></button>
        </div>
    </form>
</div>
