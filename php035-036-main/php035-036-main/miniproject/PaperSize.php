<?php
class PaperSize {
    public const A4 = 'A4';
    public const A3 = 'A3';

    public string $value;

    public function __construct(string $value) {
        $this->value = $value;
    }
}

