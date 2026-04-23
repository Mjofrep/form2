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
        $user = $email !== '' ? (new UserModel())->findByEmail($email) : null;

        set_old_input(['email' => $email]);

        if ($email === '') {
            set_validation_errors(['email' => 'Debes ingresar un correo válido.']);
            flash('error', 'Completa las credenciales para continuar.');
            redirect(base_url('login'));
        }

        if ($user && !(new UserModel())->hasUsablePassword($user)) {
            $_SESSION['pending_password_email'] = $email;
            flash('info', 'Debes crear una contraseña para tu primer acceso.');
            redirect(base_url('set-password'));
        }

        if ($password === '') {
            set_validation_errors(['password' => 'Debes ingresar tu contraseña.']);
            flash('error', 'Debes ingresar la contraseña para continuar.');
            redirect(base_url('login'));
        }

        if (!Auth::attempt($email, $password)) {
            set_validation_errors(['email' => 'Las credenciales no son válidas.']);
            flash('error', 'No fue posible iniciar sesión.');
            redirect(base_url('login'));
        }

        clear_old_input();
        flash('success', 'Sesión iniciada correctamente.');
        redirect(base_url('admin'));
    }

    public function showSetPassword(): void
    {
        require_guest();

        $email = (string) ($_SESSION['pending_password_email'] ?? '');

        if ($email === '') {
            flash('error', 'No hay una solicitud de primer acceso pendiente.');
            redirect(base_url('login'));
        }

        render('auth/set_password', [
            'title' => 'Crear contraseña',
            'email' => $email,
            'passwordPolicyText' => password_policy_text(),
        ]);
    }

    public function setPassword(): void
    {
        require_guest();
        verify_csrf();

        $email = (string) ($_SESSION['pending_password_email'] ?? '');

        if ($email === '') {
            flash('error', 'No hay una solicitud de primer acceso pendiente.');
            redirect(base_url('login'));
        }

        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);
        $errors = [];

        if (!$user || $userModel->hasUsablePassword($user)) {
            unset($_SESSION['pending_password_email']);
            flash('error', 'La cuenta ya no requiere creación de contraseña.');
            redirect(base_url('login'));
        }

        foreach (password_policy_errors($password) as $error) {
            $errors['password'] = $error;
            break;
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'La confirmación no coincide.';
        }

        if ($errors !== []) {
            set_validation_errors($errors);
            flash('error', 'No fue posible crear la contraseña.');
            redirect(base_url('set-password'));
        }

        $userModel->updatePasswordByEmail($email, password_hash($password, PASSWORD_DEFAULT));
        unset($_SESSION['pending_password_email']);
        flash('success', 'Contraseña creada correctamente. Ya puedes iniciar sesión.');
        redirect(base_url('login'));
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
