<?php

declare(strict_types=1);

namespace App\Controllers;

final class HomeController
{
    public function index(): void
    {
        if (is_logged_in()) {
            redirect(base_url('admin'));
        }

        redirect(base_url('login'));
    }
}
