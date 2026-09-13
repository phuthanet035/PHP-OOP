<?php
require_once 'ServiceInterface.php';
require_once 'Paper.php';

class PrintService implements ServiceInterface {
    private string $type;
    private bool $isColor;

    public function __construct(string $type, bool $isColor) {
        $this->type = $type;
        $this->isColor = $isColor;
    }

    public function calculatePrice(Paper $paper, int $pages, int $quantity): float {
        $basePrice = $this->isColor ? 3.0 : 1.0; 
        $paperPrice = $paper->getPrice();
        return ($basePrice + $paperPrice) * $pages * $quantity;
    }

    public function getName(): string {
        return $this->type . ($this->isColor ? " (Color)" : " (B&W)");
    }
}
