<?php

namespace App\Middleware;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use Closure;

/**
 * CSRF Verification Middleware
 */
class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, Closure $next): mixed
    {
        $method = $request->getMethod();

        if (in_array($method, ['POST', 'PATCH', 'PUT', 'DELETE'], true)) {
            $token = $request->post('_token') ?? $request->header('x-csrf-token');

            if (!Csrf::validate($token)) {
                if ($request->isAjax() || $request->expectsJson()) {
                    Response::json([
                        'error' => 'CSRF Token Mismatch',
                        'message' => 'เซสชันของคุณหมดอายุ กรุณารีเฟรชหน้าเว็บและลองใหม่อีกครั้ง'
                    ], 419);
                }

                http_response_code(419);
                echo "<h1>419 - CSRF Token Mismatch</h1><p>เซสชันของคุณหมดอายุ กรุณากด Back แล้วรีเฟรชหน้าเว็บ</p>";
                exit;
            }
        }

        return $next($request);
    }
}
