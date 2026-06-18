<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;

final class AdminUserController
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function index(): void
    {
        require_role(UserModel::ROLE_ADMIN);

        render('admin/users/index', [
            'title' => 'Usuarios',
            'users' => $this->users->all(),
            'roles' => UserModel::roles(),
        ]);
    }

    public function create(): void
    {
        require_role(UserModel::ROLE_ADMIN);

        render('admin/users/form', [
            'title' => 'Nuevo usuario',
            'user' => [
                'id' => null,
                'name' => '',
                'email' => '',
                'role' => UserModel::ROLE_USER,
                'is_active' => 1,
                'must_change_password' => 1,
            ],
            'roles' => UserModel::roles(),
            'isEditing' => false,
        ]);
    }

    public function store(): void
    {
        require_role(UserModel::ROLE_ADMIN);
        verify_csrf();

        [$data, $password, $errors] = $this->validateUserInput();

        if ($password === '') {
            $errors['password'] = 'Debes definir una contraseña temporal.';
        }

        if ($errors !== []) {
            set_old_input($_POST);
            set_validation_errors($errors);
            flash('error', 'Corrige los errores del formulario.');
            redirect(base_url('admin/users/create'));
        }

        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        $data['must_change_password'] = 1;
        $this->users->create($data);

        flash('success', 'Usuario creado correctamente.');
        redirect(base_url('admin/users'));
    }

    public function edit(string $id): void
    {
        require_role(UserModel::ROLE_ADMIN);

        $user = $this->users->find((int) $id);

        if (!$user) {
            http_response_code(404);
            render('home/404', ['title' => '404']);
            return;
        }

        render('admin/users/form', [
            'title' => 'Editar usuario',
            'user' => $user,
            'roles' => UserModel::roles(),
            'isEditing' => true,
        ]);
    }

    public function update(string $id): void
    {
        require_role(UserModel::ROLE_ADMIN);
        verify_csrf();

        $userId = (int) $id;
        $user = $this->users->find($userId);

        if (!$user) {
            http_response_code(404);
            render('home/404', ['title' => '404']);
            return;
        }

        [$data, $password, $errors] = $this->validateUserInput($userId, true);
        $currentUser = auth_user();

        if ((int) ($currentUser['id'] ?? 0) === $userId) {
            if ($data['role'] !== UserModel::ROLE_ADMIN) {
                $errors['role'] = 'No puedes quitar tu propio rol de administrador.';
            }

            if ($data['is_active'] !== 1) {
                $errors['is_active'] = 'No puedes desactivar tu propia cuenta.';
            }
        }

        if ($errors !== []) {
            set_old_input($_POST);
            set_validation_errors($errors);
            flash('error', 'Corrige los errores del formulario.');
            redirect(base_url('admin/users/' . $userId . '/edit'));
        }

        $this->users->update($userId, $data);

        if ($password !== '') {
            $this->users->updatePasswordAndRequireChange($userId, password_hash($password, PASSWORD_DEFAULT));
        }

        if ((int) ($currentUser['id'] ?? 0) === $userId) {
            $_SESSION['user'] = $this->users->find($userId);
        }

        flash('success', 'Usuario actualizado correctamente.');
        redirect(base_url('admin/users'));
    }

    private function validateUserInput(?int $userId = null, bool $isEditing = false): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $role = (string) ($_POST['role'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $mustChangePassword = isset($_POST['must_change_password']) ? 1 : 0;
        $errors = [];

        if ($name === '') {
            $errors['name'] = 'El nombre es obligatorio.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Debes ingresar un correo válido.';
        } elseif ($this->users->emailExists($email, $userId)) {
            $errors['email'] = 'Ya existe un usuario con ese correo.';
        }

        if (!array_key_exists($role, UserModel::roles())) {
            $errors['role'] = 'Rol inválido.';
        }

        if ($password !== '') {
            foreach (password_policy_errors($password) as $error) {
                $errors['password'] = $error;
                break;
            }

            if ($password !== $passwordConfirmation) {
                $errors['password_confirmation'] = 'La confirmación no coincide.';
            }
        } elseif (!$isEditing) {
            $errors['password'] = 'Debes definir una contraseña temporal.';
        }

        return [[
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'is_active' => $isActive,
            'must_change_password' => $password !== '' ? 1 : $mustChangePassword,
        ], $password, $errors];
    }
}
