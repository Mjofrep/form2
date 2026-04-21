<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? config('app.name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(asset_url('assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<?php $currentUser = auth_user(); ?>
<?php $showUserMenu = $showUserMenu ?? true; ?>
<?php $logoClickable = $logoClickable ?? true; ?>
<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container">
        <?php if ($logoClickable): ?>
            <a class="navbar-brand fw-semibold d-flex align-items-center gap-3" href="<?= e(base_url(is_logged_in() ? 'admin' : 'login')) ?>">
                <img src="<?= e(asset_url('assets/logo.png')) ?>" alt="Enel" style="height: 32px; width: auto; display: block;">
                <span class="text-dark">Centro de Excelencia Operacional</span>
            </a>
        <?php else: ?>
            <div class="navbar-brand fw-semibold mb-0 d-flex align-items-center gap-3">
                <img src="<?= e(asset_url('assets/logo.png')) ?>" alt="Enel" style="height: 32px; width: auto; display: block;">
                <span class="text-dark">Centro de Excelencia Operacional</span>
            </div>
        <?php endif; ?>
        <div class="d-flex align-items-center gap-2 ms-auto">
            <?php if ($showUserMenu && $currentUser): ?>
                <span class="text-secondary small"><?= e($currentUser['email']) ?></span>
                <form method="post" action="<?= e(base_url('logout')) ?>" class="m-0">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-secondary btn-sm" type="submit">Salir</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="py-4">
    <div class="container">
        <?php if ($success = get_flash('success')): ?>
            <div class="alert alert-success"><?= e((string) $success) ?></div>
        <?php endif; ?>
        <?php if ($error = get_flash('error')): ?>
            <div class="alert alert-danger"><?= e((string) $error) ?></div>
        <?php endif; ?>
        <?php if ($info = get_flash('info')): ?>
            <div class="alert alert-info"><?= e((string) $info) ?></div>
        <?php endif; ?>
        <?= $content ?>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script src="<?= e(asset_url('assets/js/app.js')) ?>"></script>
</body>
</html>
