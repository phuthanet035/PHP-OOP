<?php
require_once 'Paper.php';

interface ServiceInterface {
    public function calculatePrice(Paper $paper, int $pages, int $quantity): float;
    public function getName(): string;
}
