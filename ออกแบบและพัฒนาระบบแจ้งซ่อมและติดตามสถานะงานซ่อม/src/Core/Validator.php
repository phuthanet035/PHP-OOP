<?php

namespace App\Core;

/**
 * Input and File Validator
 */
class Validator
{
    private array $data;
    private array $files;
    private array $errors = [];

    public function __construct(array $data = [], array $files = [])
    {
        $this->data = $data;
        $this->files = $files;
    }

    public static function make(array $data, array $rules, array $files = []): self
    {
        $validator = new self($data, $files);
        $validator->validate($rules);
        return $validator;
    }

    public function validate(array $rules): void
    {
        foreach ($rules as $field => $fieldRules) {
            if (is_string($fieldRules)) {
                $fieldRules = explode('|', $fieldRules);
            }

            $value = $this->data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $this->applyRule($field, $value, $rule, $params);
            }
        }
    }

    private function applyRule(string $field, mixed $value, string $rule, array $params): void
    {
        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $this->addError($field, "กรุณากรอกข้อมูลในช่อง {$field}");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "รูปแบบอีเมลไม่ถูกต้อง");
                }
                break;

            case 'min':
                $min = (int) ($params[0] ?? 0);
                if (!empty($value) && mb_strlen((string) $value) < $min) {
                    $this->addError($field, "ข้อมูลต้องมีความยาวอย่างน้อย {$min} ตัวอักษร");
                }
                break;

            case 'max':
                $max = (int) ($params[0] ?? 255);
                if (!empty($value) && mb_strlen((string) $value) > $max) {
                    $this->addError($field, "ข้อมูลต้องมีความยาวไม่เกิน {$max} ตัวอักษร");
                }
                break;

            case 'in':
                if (!empty($value) && !in_array($value, $params, true)) {
                    $this->addError($field, "ค่าที่เลือกไม่ถูกต้อง");
                }
                break;

            case 'integer':
                if (!empty($value) && filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, "ข้อมูลต้องเป็นตัวเลขจำนวนเต็ม");
                }
                break;

            case 'between':
                $min = (int) ($params[0] ?? 0);
                $max = (int) ($params[1] ?? 100);
                $intVal = (int) $value;
                if ($intVal < $min || $intVal > $max) {
                    $this->addError($field, "ค่าต้องอยู่ระหว่าง {$min} ถึง {$max}");
                }
                break;

            case 'file_required':
                $file = $this->files[$field] ?? null;
                if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                    $this->addError($field, "กรุณาแนบไฟล์รูปภาพ");
                }
                break;

            case 'file_mimes':
                $file = $this->files[$field] ?? null;
                if ($file && $file['error'] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $file['tmp_name']);
                    finfo_close($finfo);

                    $allowedMimes = [
                        'jpg' => 'image/jpeg',
                        'jpeg' => 'image/jpeg',
                        'png' => 'image/png',
                        'webp' => 'image/webp'
                    ];

                    $valid = false;
                    foreach ($params as $allowedExt) {
                        if ($ext === $allowedExt && isset($allowedMimes[$allowedExt]) && $mime === $allowedMimes[$allowedExt]) {
                            $valid = true;
                            break;
                        }
                    }

                    if (!$valid) {
                        $this->addError($field, "ไฟล์ต้องเป็นรูปภาพประเภท: " . implode(', ', $params));
                    }
                }
                break;

            case 'file_max_kb':
                $maxKb = (int) ($params[0] ?? 5120);
                $file = $this->files[$field] ?? null;
                if ($file && $file['error'] === UPLOAD_ERR_OK) {
                    if ($file['size'] > ($maxKb * 1024)) {
                        $this->addError($field, "ขนาดไฟล์ต้องไม่เกิน " . round($maxKb / 1024, 1) . " MB");
                    }
                }
                break;
        }
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }
}
