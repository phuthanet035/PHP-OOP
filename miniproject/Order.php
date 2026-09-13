<?php
require_once 'OrderItem.php';

class Order {
    public string $customerName;
    public string $phone;
    /** @var OrderItem[] */
    private array $items = [];

    public function __construct(string $customerName, string $phone) {
        $this->customerName = $customerName;
        $this->phone = $phone;
    }

    public function addItem(OrderItem $item): void {
        $this->items[] = $item;
    }

    public function getTotal(): float {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += $item->getSubtotal();
        }
        return $total;
    }

    /**
     * @return OrderItem[]
     */
    public function getItems(): array {
        return $this->items;
    }
}
