<?php
/**
 * คลาส: FoodItem (อาหารทั่วไป)
 * -------------------------------------------------------------
 * ทำหน้าที่:
 * 1. เป็น "คลาสแม่ (Base Class)" พื้นฐานสำหรับอาหารทุกชนิดในตู้เย็น
 * 2. จัดเก็บข้อมูลพื้นฐาน เช่น รหัส, ชื่ออาหาร, วันหมดอายุ, หมวดหมู่ และสถานะว่าทานแล้วหรือยัง
 * 3. [Encapsulation] ควบคุมการคำนวณจำนวนวันคงเหลือ (daysLeft) และตรวจสอบว่าหมดอายุหรือยัง (isExpired)
 * 4. [Polymorphism] กำหนดเมธอด getStatusText() ให้คลาสลูกสามารถนำไปปรับแต่งตามประเภทอาหารได้
 */

class FoodItem {
    // กำหนดเป็น protected เพื่อให้คลาสลูก (PerishableFood, FrozenFood) สามารถเข้าถึงได้
    protected string $id;
    protected string $name;
    protected DateTime $expireDate;
    protected bool $isConsumed;
    protected string $category;

    /**
     * Constructor: กำหนดค่าเริ่มต้นของอาหาร
     */
    public function __construct(string $id, string $name, DateTime $expireDate, string $category = 'ทั่วไป') {
        $this->id = $id;
        $this->name = $name;
        $this->expireDate = $expireDate;
        $this->isConsumed = false; // เริ่มต้นยังไม่ได้ทาน
        $this->category = $category;
    }

    // --- Getter Methods (อ่านข้อมูล) ---
    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getCategory(): string { return $this->category; }
    public function getExpireDate(): DateTime { return $this->expireDate; }
    public function isConsumed(): bool { return $this->isConsumed; }

    /**
     * เปลี่ยนสถานะเป็น "ทานแล้ว" เมื่อนำอาหารไปประกอบอาหารหรือรับประทาน
     */
    public function markAsConsumed(): void {
        $this->isConsumed = true;
    }

    /**
     * [OOP: Encapsulation]
     * คำนวณจำนวนวันที่เหลือก่อนหมดอายุ เทียบกับวันปัจจุบัน
     * - ค่าบวก = เหลือเวลาอีกกี่วัน
     * - ค่า 0 = หมดอายุวันนี้
     * - ค่าติดลบ = หมดอายุเลยมาแล้วกี่วัน
     */
    public function daysLeft(DateTime $currentDate): int {
        $today = new DateTime($currentDate->format('Y-m-d'));
        $target = new DateTime($this->expireDate->format('Y-m-d'));
        $diff = $today->diff($target);
        return $diff->invert ? -$diff->days : $diff->days;
    }

    /**
     * ตรวจสอบว่าอาหารหมดอายุแล้วหรือยัง
     */
    public function isExpired(DateTime $currentDate): bool {
        return $this->daysLeft($currentDate) < 0;
    }

    /**
     * ตรวจสอบว่าเป็นอาหารที่ต้อง "รีบกินด่วน" หรือไม่
     * สำหรับอาหารทั่วไป: ถ้าเหลือเวลา <= 3 วัน และยังไม่หมดอายุ ถือว่าต้องรีบกิน
     */
    public function isUrgent(DateTime $currentDate): bool {
        $days = $this->daysLeft($currentDate);
        return (!$this->isExpired($currentDate) && $days <= 3);
    }

    /**
     * [OOP: Polymorphism]
     * แสดงข้อความสถานะความสดใหม่ของอาหาร
     * คลาสลูกสามารถ Override เมธอดนี้เพื่อปรับปรุงข้อความเตือนเฉพาะเจาะจงได้
     */
    public function getStatusText(DateTime $currentDate): string {
        $days = $this->daysLeft($currentDate);

        if ($days < 0) {
            $abs = abs($days);
            return "❌ หมดอายุแล้ว {$abs} วัน (แนะนำให้ทิ้ง)";
        } elseif ($days === 0) {
            return "🚨 หมดอายุวันนี้! (ต้องทานทันที)";
        } elseif ($days <= 3) {
            return "⚠️ ใกล้หมดอายุ (เหลืออีก {$days} วัน)";
        } else {
            return "✅ ยังสดใหม่ (เหลืออีก {$days} วัน)";
        }
    }
}
