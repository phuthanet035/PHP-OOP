<?php
/**
 * คลาส: PerishableFood (ของสดเน่าเสียง่าย)
 * -------------------------------------------------------------
 * ทำหน้าที่:
 * 1. เป็น "คลาสลูก (Subclass)" ที่สืบทอดคุณสมบัติมาจากคลาสแม่ FoodItem
 * 2. ตัวแทนของ "ของสด" เช่น นมสด, เนื้อสัตว์, ผักสด ซึ่งมีความอ่อนไหวสูงมาก
 * 3. [Inheritance] สืบทอดตัวแปรและฟังก์ชันพื้นฐาน เช่น ชื่อ, วันหมดอายุ, daysLeft() จาก FoodItem
 * 4. เพิ่มคุณสมบัติเฉพาะตัว เช่น storageTip (คำแนะนำการเก็บ เช่น แช่ช่องธรรมดา/ปิดฝา)
 * 5. [Polymorphism] Override เมธอด getStatusText() ให้เข้มงวดกว่าเดิม
 *    - ถ้าเหลือเวลา <= 2 วัน จะเตือนเป็น "ของสดวิกฤต!" ทันที
 *    - ถ้าหมดอายุแล้ว จะเตือนว่า "เน่าเสียแล้ว ห้ามรับประทานเด็ดขาด"
 */

require_once __DIR__ . '/FoodItem.php';

class PerishableFood extends FoodItem {
    // ข้อมูลเฉพาะตัวของของสด: คำแนะนำการเก็บรักษา
    private string $storageTip;

    /**
     * Constructor: เรียก constructor ของคลาสแม่ (parent::__construct)
     * และกำหนดหมวดหมู่เป็น 'ของสดเน่าเสียง่าย' อัตโนมัติ
     */
    public function __construct(string $id, string $name, DateTime $expireDate, string $storageTip = 'แช่ช่องเย็นธรรมดา') {
        parent::__construct($id, $name, $expireDate, 'ของสดเน่าเสียง่าย');
        $this->storageTip = $storageTip;
    }

    public function getStorageTip(): string {
        return $this->storageTip;
    }

    /**
     * [OOP: Polymorphism]
     * Override เมธอด getStatusText() จากคลาสแม่ FoodItem
     * เพื่อแสดงการเตือนที่มีความเข้มงวดและปลอดภัยสูงกว่าอาหารทั่วไป
     */
    public function getStatusText(DateTime $currentDate): string {
        $days = $this->daysLeft($currentDate);

        if ($days < 0) {
            return "☣️ เน่าเสียแล้ว! (ห้ามรับประทานเด็ดขาด)";
        } elseif ($days <= 2) {
            return "⚡ ของสดวิกฤต! เหลือแค่ {$days} วัน ({$this->storageTip})";
        } else {
            return "🥗 ของสดพร้อมทาน (เหลืออีก {$days} วัน)";
        }
    }
}
