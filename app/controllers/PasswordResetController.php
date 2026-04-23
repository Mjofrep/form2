<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Mailer;
use App\Models\PasswordResetModel;
use App\Models\UserModel;

final class PasswordResetController
{
    public function showRequestForm(): void
    {
        require_guest();

        render('auth/forgot_password', [
            'title' => 'Recuperar acceso',
        ]);
    }

    public function sendResetLink(): void
    {
        require_guest();
        verify_csrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        set_old_input(['email' => $email]);

        if ($email === '') {
            set_validation_errors(['email' => 'Debes indicar un correo.']);
            redirect(base_url('forgot-password'));
        }

        $user = (new UserModel())->findByEmail($email);

        if (!$user) {
            flash('info', 'Si el correo existe, se generó un enlace de recuperación.');
            redirect(base_url('forgot-password'));
        }

        $plainToken = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + ((int) config('app.password_reset_expires_minutes', 60) * 60));

        (new PasswordResetModel())->create($email, $plainToken, $expiresAt);

        $resetUrl = full_url('reset-password?email=' . rawurlencode($email) . '&token=' . rawurlencode($plainToken));

        if ((bool) config('mail.enabled', false)) {
            $sent = (new Mailer())->sendHtml(
                $email,
                (string) ($user['name'] ?? $email),
                'Recuperación de contraseña',
                $this->resetEmailHtml((string) ($user['name'] ?? 'usuario'), $resetUrl),
                "Hemos recibido una solicitud para restablecer tu contraseña. Usa este enlace: {$resetUrl}"
            );

            if ($sent) {
                flash('success', 'Si el correo existe, enviamos un enlace de recuperación.');
            } else {
                flash('error', 'No fue posible enviar el correo de recuperación en este momento.');
            }
        } else {
            flash('success', 'Enlace de recuperación generado.');
            flash('info', 'Modo local: usa este enlace para continuar: ' . $resetUrl);
        }

        redirect(base_url('forgot-password'));
    }

    public function showResetForm(): void
    {
        require_guest();

        render('auth/reset_password', [
            'title' => 'Restablecer contraseña',
            'email' => (string) ($_GET['email'] ?? ''),
            'token' => (string) ($_GET['token'] ?? ''),
        ]);
    }

    public function resetPassword(): void
    {
        require_guest();
        verify_csrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        $token = (string) ($_POST['token'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

        set_old_input(['email' => $email, 'token' => $token]);

        $errors = [];

        if ($email === '' || $token === '') {
            $errors['email'] = 'Solicitud inválida.';
        }

        foreach (password_policy_errors($password) as $error) {
            $errors['password'] = $error;
            break;
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'La confirmación no coincide.';
        }

        $user = (new UserModel())->findByEmail($email);
        $reset = (new PasswordResetModel())->findValidByEmail($email);

        if (!$user || !$reset || !password_verify($token, $reset['token'])) {
            $errors['email'] = 'El enlace de recuperación no es válido o expiró.';
        }

        if ($errors !== []) {
            set_validation_errors($errors);
            flash('error', 'No fue posible restablecer la contraseña.');
            redirect(base_url('reset-password?email=' . rawurlencode($email) . '&token=' . rawurlencode($token)));
        }

        (new UserModel())->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        (new PasswordResetModel())->deleteByEmail($email);

        clear_old_input();
        flash('success', 'Contraseña actualizada. Ya puedes iniciar sesión.');
        redirect(base_url('login'));
    }

    private function resetEmailHtml(string $name, string $resetUrl): string
    {
        $safeName = e($name);
        $safeUrl = e($resetUrl);

        return <<<HTML
<div style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.6;">
    <h2 style="margin-bottom: 16px;">Recuperación de contraseña</h2>
    <p>Hola {$safeName},</p>
    <p>Hemos recibido una solicitud para restablecer tu contraseña en el sistema Centro de Excelencia Operacional.</p>
    <p>
        <a href="{$safeUrl}" style="display: inline-block; padding: 12px 20px; background: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 6px;">Restablecer contraseña</a>
    </p>
    <p>Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
    <p><a href="{$safeUrl}">{$safeUrl}</a></p>
    <p>Si no solicitaste este cambio, puedes ignorar este mensaje.</p>
</div>
HTML;
    }
}
