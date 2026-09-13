<?php
require_once 'PaperSize.php';

class Paper {
    public PaperSize $size;
    public bool $isCardstock;

    public function __construct(PaperSize $size, bool $isCardstock) {
        $this->size = $size;
        $this->isCardstock = $isCardstock;
    }

    public function getPrice(): float {
        $price = 0.0;
        if ($this->size === PaperSize::A4) {
            $price = 0.5; // Base price for A4
        } elseif ($this->size === PaperSize::A3) {
            $price = 1.5; // Base price for A3
        }
        
        if ($this->isCardstock) {
            $price += 2.0; // Additional cost for cardstock
        }
        
        return $price;
    }
}
