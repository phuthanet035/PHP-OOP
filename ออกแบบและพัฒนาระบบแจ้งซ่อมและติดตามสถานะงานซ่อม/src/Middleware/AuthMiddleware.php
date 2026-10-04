<?php

namespace App\Middleware;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use Closure;

/**
 * Ensures user is authenticated before accessing route
 */
class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (!Auth::check()) {
            if ($request->isAjax() || $request->expectsJson()) {
                Response::json(['error' => 'Unauthenticated', 'message' => 'กรุณาเข้าสู่ระบบก่อนทำรายการ'], 401);
            }
            Response::redirect('/login?redirect=' . urlencode($request->getUri()));
        }

        return $next($request);
    }
}
