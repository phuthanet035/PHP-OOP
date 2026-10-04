<?php

namespace App\Services;

/**
 * File Uploader Service
 * Handles secure file uploads with MIME validation and size limits
 */
class FileUploader
{
    private string $uploadDir;
    private int $maxSize;
    private array $allowedMimes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
    ];

    public function __construct(?string $uploadDir = null, ?int $maxSize = null)
    {
        $this->uploadDir = $uploadDir ?? (dirname(__DIR__, 2) . '/storage/uploads/');
        $this->maxSize = $maxSize ?? (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880);

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function upload(array $file, string $prefix = 'file'): ?string
    {
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file['size'] > $this->maxSize) {
            throw new \InvalidArgumentException("ขนาดไฟล์เกินกำหนด (สูงสุด " . round($this->maxSize / 1048576, 1) . "MB)");
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!isset($this->allowedMimes[$ext])) {
            throw new \InvalidArgumentException("ไม่อนุญาตให้นามสกุลไฟล์นี้ ({$ext}) ให้อนุญาตเฉพาะ JPG, PNG, WEBP เท่านั้น");
        }

        // Verify MIME type using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mime !== $this->allowedMimes[$ext]) {
            throw new \InvalidArgumentException("รูปแบบไฟล์ไม่ถูกต้องตามที่กำหนด");
        }

        $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
        $targetPath = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \RuntimeException("เกิดข้อผิดพลาดในการบันทึกไฟล์");
        }

        return $filename;
    }
}
