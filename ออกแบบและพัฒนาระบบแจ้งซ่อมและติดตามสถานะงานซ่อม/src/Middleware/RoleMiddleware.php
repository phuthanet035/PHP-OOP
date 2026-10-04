<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use Closure;

/**
 * Role-Based Access Control (RBAC) Middleware
 * Enforces role restrictions according to Diagram 4 Use Case Model
 */
class RoleMiddleware implements MiddlewareInterface
{
    private array $allowedRoles;

    public function __construct(string|array $roles)
    {
        $this->allowedRoles = is_array($roles) ? $roles : [$roles];
    }

    public function handle(Request $request, Closure $next): mixed
    {
        if (!Auth::check()) {
            Response::redirect('/login');
        }

        $userRole = Auth::role();

        if (!in_array($userRole, $this->allowedRoles, true)) {
            if ($request->isAjax() || $request->expectsJson()) {
                Response::json([
                    'error' => 'Forbidden',
                    'message' => 'คุณไม่มีสิทธิ์ในการเข้าถึงส่วนนี้ (Permission Denied)'
                ], 403);
            }

            View::setFlash('error', 'คุณไม่มีสิทธิ์ในการเข้าถึงหน้านี้');
            View::render('errors/403', ['role' => $userRole], null);
            exit;
        }

        return $next($request);
    }
}
