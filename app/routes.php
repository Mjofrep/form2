<?php

declare(strict_types=1);

use App\Controllers\AdminCampaignController;
use App\Controllers\AdminUserController;
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\PasswordResetController;
use App\Controllers\PublicCampaignController;

$router->get('/', [HomeController::class, 'index']);

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/change-password', [AuthController::class, 'showChangePassword']);
$router->post('/change-password', [AuthController::class, 'changePassword']);
$router->post('/logout', [AuthController::class, 'logout']);

$router->get('/forgot-password', [PasswordResetController::class, 'showRequestForm']);
$router->post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
$router->get('/reset-password', [PasswordResetController::class, 'showResetForm']);
$router->post('/reset-password', [PasswordResetController::class, 'resetPassword']);

$router->get('/admin', [AdminCampaignController::class, 'index']);
$router->get('/admin/campaigns/create', [AdminCampaignController::class, 'create']);
$router->post('/admin/campaigns', [AdminCampaignController::class, 'store']);
$router->get('/admin/campaigns/{id}/edit', [AdminCampaignController::class, 'edit']);
$router->post('/admin/campaigns/{id}/update', [AdminCampaignController::class, 'update']);
$router->get('/admin/campaigns/{id}/results', [AdminCampaignController::class, 'results']);
$router->get('/admin/campaigns/{id}/export', [AdminCampaignController::class, 'export']);

$router->get('/admin/users', [AdminUserController::class, 'index']);
$router->get('/admin/users/create', [AdminUserController::class, 'create']);
$router->post('/admin/users', [AdminUserController::class, 'store']);
$router->get('/admin/users/{id}/edit', [AdminUserController::class, 'edit']);
$router->post('/admin/users/{id}/update', [AdminUserController::class, 'update']);

$router->get('/c/{token}', [PublicCampaignController::class, 'show']);
$router->post('/c/{token}', [PublicCampaignController::class, 'submit']);
