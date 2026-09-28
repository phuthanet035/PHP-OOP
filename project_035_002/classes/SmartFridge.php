<?php
/**
 * คลาส: SmartFridge (ตู้เย็นอัจฉริยะ / ผู้จัดการระบบ)
 * -------------------------------------------------------------
 * ทำหน้าที่:
 * 1. เป็น "คลาสผู้จัดการ (Coordinator / Aggregation)" ที่จำลองการทำงานของตู้เย็น
 * 2. เป็นศูนย์รวมที่เก็บรวบรวมอาหารทุกชนิด (FoodItem, PerishableFood, FrozenFood)
 * 3. [Abstraction] ซ่อนความซับซ้อนของการค้นหาและคัดกรองอาหาร
 *    - มีเมธอด getUrgentItems() เพื่อคัดกรองอาหารที่ "ต้องรีบกินด่วน (ใน 3 วัน)" โดยที่ผู้ใช้ไม่ต้องมานั่งนับวันเอง
 *    - มีเมธอด getExpiredItems() คัดกรองของที่หมดอายุ เพื่อความปลอดภัย
 * 4. จัดการกระบวนการ "นำอาหารออกไปทาน (consumeItem)" ช่วยลดปัญหา Food Waste
 */

require_once __DIR__ . '/FoodItem.php';

class SmartFridge {
    private string $ownerName;

    /**
     * [OOP: Aggregation]
     * ตู้เย็นประกอบด้วยรายการของวัตถุ FoodItem หลายๆ ชิ้น
     * @var array<string, FoodItem>
     */
    private array $items = [];

    /**
     * Constructor: กำหนดชื่อเจ้าของตู้เย็น
     */
    public function __construct(string $ownerName) {
        $this->ownerName = $ownerName;
    }

    public function getOwnerName(): string {
        return $this->ownerName;
    }

    /**
     * นำอาหารใหม่บรรจุเข้าตู้เย็น
     */
    public function addItem(FoodItem $item): void {
        $this->items[$item->getId()] = $item;
    }

    /**
     * ค้นหาอาหารตามรหัส ID
     */
    public function getItem(string $id): ?FoodItem {
        return $this->items[$id] ?? null;
    }

    /**
     * ดึงรายการอาหารทั้งหมดที่ "ยังไม่ได้ทาน"
     * @return FoodItem[]
     */
    public function getActiveItems(): array {
        return array_filter($this->items, fn($item) => !$item->isConsumed());
    }

    /**
     * [หัวใจหลักในการแก้ปัญหาของมนุษย์]:
     * คัดกรองเฉพาะอาหารที่ต้อง "รีบนำมาทำอาหารทันที" (เหลือเวลา 0-3 วัน)
     * เพื่อป้องกันไม่ให้ของเน่าเสียคาตู้เย็น
     * @return FoodItem[]
     */
    public function getUrgentItems(DateTime $currentDate): array {
        return array_filter($this->getActiveItems(), fn($item) => $item->isUrgent($currentDate));
    }

    /**
     * คัดกรองอาหารที่ "หมดอายุแล้ว" เพื่อเตือนให้ทิ้งทันที ป้องกันอันตรายต่อสุขภาพ
     * @return FoodItem[]
     */
    public function getExpiredItems(DateTime $currentDate): array {
        return array_filter($this->getActiveItems(), fn($item) => $item->isExpired($currentDate));
    }

    /**
     * บันทึกว่าผู้ใช้ได้นำอาหารชิ้นนี้ไปรับประทานหรือทำอาหารแล้ว
     * ตัดออกจากรายการอาหารคงเหลือ
     */
    public function consumeItem(string $id): bool {
        $item = $this->getItem($id);
        if ($item && !$item->isConsumed()) {
            $item->markAsConsumed();
            return true;
        }
        return false;
    }
}
