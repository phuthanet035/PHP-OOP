<?php
/**
 * คลาส: FrozenFood (อาหารแช่แข็ง)
 * -------------------------------------------------------------
 * ทำหน้าที่:
 * 1. เป็น "คลาสลูก (Subclass)" ที่สืบทอดคุณสมบัติมาจาก FoodItem
 * 2. ตัวแทนของ "อาหารแช่แข็ง" เช่น นักเก็ต, เนื้อแช่ฟรีซ, ไอศกรีม
 * 3. [Inheritance] ได้รับโครงสร้างและฟังก์ชันพื้นฐานจากคลาสแม่ FoodItem
 * 4. เพิ่มคุณสมบัติเฉพาะตัว เช่น freezerTemp (อุณหภูมิช่องแช่แข็ง เช่น -18 องศาเซลเซียส)
 * 5. [Polymorphism] Override เมธอด getStatusText() โดยนำข้อความสถานะเดิมมาเสริมข้อมูลอุณหภูมิ
 */

require_once __DIR__ . '/FoodItem.php';

class FrozenFood extends FoodItem {
    // ข้อมูลเฉพาะตัวของอาหารแช่แข็ง: อุณหภูมิช่องฟรีซที่เหมาะสม
    private int $freezerTemp;

    /**
     * Constructor: กำหนดอุณหภูมิช่องฟรีซ และตั้งหมวดหมู่เป็น 'อาหารแช่แข็ง'
     */
    public function __construct(string $id, string $name, DateTime $expireDate, int $freezerTemp = -18) {
        parent::__construct($id, $name, $expireDate, 'อาหารแช่แข็ง');
        $this->freezerTemp = $freezerTemp;
    }

    public function getFreezerTemp(): int {
        return $this->freezerTemp;
    }

    /**
     * [OOP: Polymorphism]
     * Override เมธอด getStatusText() โดยเรียก parent::getStatusText() เพื่อนำข้อความเดิมมาต่อเติม
     */
    public function getStatusText(DateTime $currentDate): string {
        $baseStatus = parent::getStatusText($currentDate);
        return "❄️ [แช่แข็ง {$this->freezerTemp}°C] " . $baseStatus;
    }
}
