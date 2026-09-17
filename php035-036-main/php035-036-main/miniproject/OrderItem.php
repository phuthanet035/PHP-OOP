<?php
require_once 'ServiceInterface.php';
require_once 'Paper.php';

class OrderItem {
    public ServiceInterface $service;
    public Paper $paper;
    public int $pages;
    public int $quantity;

    public function __construct(ServiceInterface $service, Paper $paper, int $pages, int $quantity) {
        $this->service = $service;
        $this->paper = $paper;
        $this->pages = $pages;
        $this->quantity = $quantity;
    }

    public function getSubtotal(): float {
        return $this->service->calculatePrice($this->paper, $this->pages, $this->quantity);
    }
}
