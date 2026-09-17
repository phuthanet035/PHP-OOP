<?php
require_once 'ServiceInterface.php';
require_once 'Paper.php';

class FinishingService implements ServiceInterface {
    private string $type;
    private float $unitPrice;

    public function __construct(string $type, float $unitPrice) {
        $this->type = $type;
        $this->unitPrice = $unitPrice;
    }

    public function calculatePrice(Paper $paper, int $pages, int $quantity): float {
        return $this->unitPrice * $quantity; 
    }

    public function getName(): string {
        return $this->type;
    }
}
