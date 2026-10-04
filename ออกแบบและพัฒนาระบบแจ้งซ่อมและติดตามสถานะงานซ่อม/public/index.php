<?php

declare(strict_types=1);

/**
 * Smart IT Helpdesk & Notification System
 * Front Controller Entry Point
 * 
 * Conforms to Architecture Diagram 1, Class Diagram 5, and Component Diagram 8
 */

// Set Timezone
date_default_timezone_set('Asia/Bangkok');

// Define Base Paths
define('BASE_PATH', dirname(__DIR__));
define('STORAGE_PATH', BASE_PATH . '/storage');
define('VIEWS_PATH', BASE_PATH . '/views');

// 1. PSR-4 Autoloader & Helpers
require_once BASE_PATH . '/src/Core/Autoloader.php';
require_once BASE_PATH . '/src/Core/helpers.php';
use App\Core\Autoloader;

Autoloader::register();
Autoloader::addNamespace('App', BASE_PATH . '/src');

// 2. Load Environment (.env) Variables
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }
    }
}

// 3. Error Reporting Configuration
$debug = filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN);
if ($debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// 4. Initialize Core Components
use App\Core\Auth;
use App\Core\EventDispatcher;
use App\Core\Router;
use App\Core\View;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Middleware\RoleMiddleware;
use App\Observers\TicketObserver;

// Controllers
use App\Controllers\AuthController;
use App\Controllers\TicketController;
use App\Controllers\DashboardController;
use App\Controllers\AdminController;
use App\Controllers\FileController;

// Initialize Session & Views
Auth::init();
View::init(VIEWS_PATH);

// 5. Observer Pattern Registration (Diagram 5 & Sequence 6.1)
$events = EventDispatcher::getInstance();
$events->listen('ticket.created', [TicketObserver::class, 'handleCreated']);
$events->listen('ticket.status_changed', [TicketObserver::class, 'handleStatusChanged']);

// 6. Router & Routes Definition
$router = new Router();

// --- Authentication & Demo Routes ---
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/demo-login/{role}', [AuthController::class, 'demoLogin']);

// --- Tickets Routes (Protected by AuthMiddleware) ---
$router->get('/', [TicketController::class, 'index'], [AuthMiddleware::class]);
$router->get('/tickets', [TicketController::class, 'index'], [AuthMiddleware::class]);
$router->get('/tickets/create', [TicketController::class, 'create'], [AuthMiddleware::class]);
$router->post('/tickets', [TicketController::class, 'store'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/tickets/{id}', [TicketController::class, 'show'], [AuthMiddleware::class]);

// State Machine transitions & actions (Sequence 6.2)
$router->post('/tickets/{id}/status', [TicketController::class, 'updateStatus'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->patch('/tickets/{id}/status', [TicketController::class, 'updateStatus'], [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/tickets/{id}/assign', [TicketController::class, 'assignTechnician'], [AuthMiddleware::class, new RoleMiddleware(['admin']), CsrfMiddleware::class]);
$router->post('/tickets/{id}/comments', [TicketController::class, 'addComment'], [AuthMiddleware::class, CsrfMiddleware::class]);

// --- Admin Analytics & Management Routes (Diagram 6.3) ---
$router->get('/admin/dashboard', [DashboardController::class, 'index'], [AuthMiddleware::class, new RoleMiddleware(['admin', 'technician'])]);
$router->get('/admin/users', [AdminController::class, 'users'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->post('/admin/users', [AdminController::class, 'createUser'], [AuthMiddleware::class, new RoleMiddleware(['admin']), CsrfMiddleware::class]);
$router->get('/admin/categories', [AdminController::class, 'categories'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->post('/admin/categories', [AdminController::class, 'createCategory'], [AuthMiddleware::class, new RoleMiddleware(['admin']), CsrfMiddleware::class]);
$router->post('/admin/categories/{id}/delete', [AdminController::class, 'deleteCategory'], [AuthMiddleware::class, new RoleMiddleware(['admin']), CsrfMiddleware::class]);
$router->get('/admin/line-logs', [AdminController::class, 'lineLogs'], [AuthMiddleware::class, new RoleMiddleware(['admin'])]);
$router->post('/admin/line-logs/test', [AdminController::class, 'testLineNotification'], [AuthMiddleware::class, new RoleMiddleware(['admin']), CsrfMiddleware::class]);

// --- Protected Static File Serving Route ---
$router->get('/uploads/{filename}', [FileController::class, 'serve']);

// 7. Dispatch HTTP Request
$router->dispatch();
