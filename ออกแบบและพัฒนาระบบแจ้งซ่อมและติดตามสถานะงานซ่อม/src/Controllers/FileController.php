<?php

namespace App\Controllers;

use App\Core\Request;

/**
 * File Controller for serving protected attachments outside Document Root
 * Matching Diagram 9 (storage/uploads outside docroot)
 */
class FileController extends BaseController
{
    public function serve(Request $request): void
    {
        $filename = basename($request->param('filename', ''));
        $filePath = dirname(__DIR__, 2) . '/storage/uploads/' . $filename;

        if (empty($filename) || !file_exists($filePath)) {
            http_response_code(404);
            header('Content-Type: text/plain');
            echo "404 - File Not Found";
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        header("Content-Type: {$mime}");
        header("Content-Length: " . filesize($filePath));
        header("Cache-Control: public, max-age=86400");
        readfile($filePath);
        exit;
    }
}
