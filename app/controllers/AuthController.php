<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\UserModel;

final class AuthController
{
    public function showLogin(): void
    {
        require_guest();

        render('auth/login', [
            'title' => 'Ingreso administrador',
        ]);
    }

    public function login(): void
    {
        require_guest();
        verify_csrf();

        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        set_old_input(['email' => $email]);

        if ($email === '') {
            set_validation_errors(['email' => 'Debes ingresar un correo válido.']);
            flash('error', 'Completa las credenciales para continuar.');
            redirect(base_url('login'));
        }

        if ($password === '') {
            set_validation_errors(['password' => 'Debes ingresar tu contraseña.']);
            flash('error', 'Debes ingresar la contraseña para continuar.');
            redirect(base_url('login'));
        }

        $status = Auth::attempt($email, $password);

        if ($status !== Auth::STATUS_SUCCESS) {
            if ($status === Auth::STATUS_LOCKED) {
                set_validation_errors(['email' => 'La cuenta se bloqueó temporalmente por múltiples intentos fallidos.']);
                flash('error', 'Tu cuenta fue bloqueada temporalmente. Intenta más tarde.');
            } elseif ($status === Auth::STATUS_INACTIVE) {
                set_validation_errors(['email' => 'La cuenta está inactiva.']);
                flash('error', 'Tu cuenta está inactiva. Contacta a un administrador.');
            } else {
                set_validation_errors(['email' => 'Las credenciales no son válidas.']);
                flash('error', 'No fue posible iniciar sesión.');
            }

            redirect(base_url('login'));
        }

        clear_old_input();
        flash('success', 'Sesión iniciada correctamente.');
        redirect(base_url(((bool) (auth_user()['must_change_password'] ?? false)) ? 'change-password' : 'admin'));
    }

    public function showChangePassword(): void
    {
        require_auth();

        render('auth/change_password', [
            'title' => 'Cambiar contraseña',
            'user' => auth_user(),
            'passwordPolicyText' => password_policy_text(),
        ]);
    }

    public function changePassword(): void
    {
        require_auth();
        verify_csrf();

        $user = auth_user();
        $userId = (int) ($user['id'] ?? 0);
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        $userModel = new UserModel();
        $errors = [];

        if (!$user || $userId <= 0) {
            Auth::logout();
            session_start();
            flash('error', 'Debes iniciar sesión nuevamente.');
            redirect(base_url('login'));
        }

        if (!password_verify($currentPassword, (string) ($user['password'] ?? ''))) {
            $errors['current_password'] = 'La contraseña actual no es correcta.';
        }

        foreach (password_policy_errors($password) as $error) {
            $errors['password'] = $error;
            break;
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'La confirmación no coincide.';
        }

        if ($currentPassword !== '' && $password !== '' && hash_equals($currentPassword, $password)) {
            $errors['password'] = 'La nueva contraseña debe ser distinta de la actual.';
        }

        if ($errors !== []) {
            set_validation_errors($errors);
            flash('error', 'No fue posible actualizar la contraseña.');
            redirect(base_url('change-password'));
        }

        $userModel->markPasswordChanged($userId, password_hash($password, PASSWORD_DEFAULT));
        $_SESSION['user'] = $userModel->find($userId);
        flash('success', 'Contraseña actualizada correctamente.');
        redirect(base_url('admin'));
    }

    public function logout(): void
    {
        verify_csrf();
        Auth::logout();
        session_start();
        flash('success', 'Sesión cerrada.');
        redirect(base_url('login'));
    }
}
