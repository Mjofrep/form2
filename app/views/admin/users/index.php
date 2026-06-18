<section class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
    <div>
        <h1 class="h2 mb-2">Usuarios</h1>
        <p class="text-secondary mb-0">Administra accesos al panel y controla quién puede crear campañas o gestionar usuarios.</p>
    </div>
    <a href="<?= e(base_url('admin/users/create')) ?>" class="btn btn-primary">Nuevo usuario</a>
</section>

<div class="soft-panel p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Clave</th>
                    <th>Último acceso</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= e((string) $user['name']) ?></td>
                            <td><?= e((string) $user['email']) ?></td>
                            <td><?= e($roles[$user['role']] ?? (string) $user['role']) ?></td>
                            <td>
                                <span class="badge <?= (int) $user['is_active'] === 1 ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                    <?= (int) $user['is_active'] === 1 ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= (int) $user['must_change_password'] === 1 ? 'text-bg-warning' : 'text-bg-light border' ?>">
                                    <?= (int) $user['must_change_password'] === 1 ? 'Cambio pendiente' : 'Vigente' ?>
                                </span>
                            </td>
                            <td><?= e((string) ($user['last_login_at'] ?: 'Sin registro')) ?></td>
                            <td class="text-end">
                                <a href="<?= e(base_url('admin/users/' . $user['id'] . '/edit')) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">No hay usuarios registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
