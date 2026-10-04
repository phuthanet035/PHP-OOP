<?php

namespace App\Core;

/**
 * View Template Renderer
 */
class View
{
    private static string $viewsPath = '';

    public static function init(string $path): void
    {
        self::$viewsPath = rtrim($path, '/\\') . '/';
    }

    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        Auth::init();

        if (empty(self::$viewsPath)) {
            self::$viewsPath = dirname(__DIR__, 2) . '/views/';
        }

        $viewFile = self::$viewsPath . ltrim($view, '/') . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: {$viewFile}");
        }

        // Shared global variables
        $globalData = [
            'currentUser' => Auth::user(),
            'currentUserId' => Auth::id(),
            'currentUserRole' => Auth::role(),
            'csrfToken' => Csrf::token(),
            'csrfField' => Csrf::field(),
            'flashSuccess' => self::getFlash('success'),
            'flashError' => self::getFlash('error'),
            'flashWarning' => self::getFlash('warning'),
            'flashInfo' => self::getFlash('info'),
            'appName' => $_ENV['APP_NAME'] ?? 'Smart IT Helpdesk',
        ];

        $viewData = array_merge($globalData, $data);
        extract($viewData);

        // Capture view content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render inside layout if specified
        if ($layout !== null) {
            $layoutFile = self::$viewsPath . ltrim($layout, '/') . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
                return;
            }
        }

        echo $content;
    }

    public static function setFlash(string $type, string $message): void
    {
        Auth::init();
        $_SESSION['flash'][$type] = $message;
    }

    public static function getFlash(string $type): ?string
    {
        Auth::init();
        if (isset($_SESSION['flash'][$type])) {
            $msg = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $msg;
        }
        return null;
    }
}

// Global sanitization helper function
if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
