<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Core\View;

/**
 * Base Abstract Controller
 */
abstract class BaseController
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function json(mixed $data, int $status = 200): Response
    {
        return Response::json($data, $status);
    }

    protected function redirect(string $url, int $status = 302): void
    {
        Response::redirect($url, $status);
    }

    protected function currentUser(): ?array
    {
        return Auth::user();
    }

    protected function currentUserId(): ?int
    {
        return Auth::id();
    }

    protected function currentRole(): ?string
    {
        return Auth::role();
    }
}
