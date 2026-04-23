<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'Forms Hub PHP',
        'base_url' => '/form2',
        'timezone' => 'America/Santiago',
        'admin_email_from' => 'no-reply@formshub.local',
        'password_reset_expires_minutes' => 60,
        'mailer_enabled' => true,
    ],
    'mail' => [
        'enabled' => true,
        'host' => 'mail.noetica.cl',
        'port' => 465,
        'username' => 'ceo@noetica.cl',
        'password' => 'Neotica_1964$',
        'encryption' => 'ssl',
        'timeout' => 20,
        'debug_level' => 0,
        'allow_self_signed' => false,
        'from_email' => 'ceo@noetica.cl',
        'from_name' => 'Sistema CEO',
    ],
    'db' => [
        'host' => 'localhost',
        'port' => '8889',
        'dbname' => 'form2',
        'charset' => 'utf8mb4',
        'username' => 'root',
        'password' => 'root',
    ],
];
