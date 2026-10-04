<?php

namespace App\Middleware;

use App\Core\Request;
use Closure;

interface MiddlewareInterface
{
    public function handle(Request $request, Closure $next): mixed;
}
