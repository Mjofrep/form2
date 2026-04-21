<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;

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

        if ($email === '' || $password === '') {
            set_validation_errors(['email' => 'Debes ingresar correo y contraseña.']);
            flash('error', 'Completa las credenciales para continuar.');
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

    public function logout(): void
    {
        verify_csrf();
        Auth::logout();
        session_start();
        flash('success', 'Sesión cerrada.');
        redirect(base_url('login'));
    }
}
