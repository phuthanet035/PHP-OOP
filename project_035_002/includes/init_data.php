<?php
/**
 * ไฟล์: includes/init_data.php
 * -------------------------------------------------------------
 * ทำหน้าที่:
 * 1. จัดเตรียม "ข้อมูลอาหารจำลองเริ่มต้น (Seed Data)" ใส่ในตู้เย็น
 * 2. จัดการเรื่อง "Session State" โหลดข้อมูลที่ผู้ใช้เคยกดทาน หรือเพิ่มขึ้นมาใหม่
 * 3. ส่งคืนออบเจกต์ $fridge และ $today เพื่อนำไปใช้งานต่อในระบบ
 */

$today = new DateTime('2026-09-28'); // วันจำลองปัจจุบัน

/**
 * ฟังก์ชันสร้างตู้เย็นและใส่อาหารตัวอย่างประเภทต่างๆ
 */
function createDefaultFridge(DateTime $today): SmartFridge {
    $fridge = new SmartFridge("ครอบครัวสุขสันต์");

    // 1. นมสด (PerishableFood: เหลืออีก 2 วัน -> วิกฤต ต้องรีบดื่ม)
    $fridge->addItem(new PerishableFood(
        'F01',
        'นมสดพาสเจอร์ไรส์',
        (clone $today)->modify('+2 days'),
        'แช่ฝาประตูตู้เย็น'
    ));

    // 2. อกไก่สด (PerishableFood: หมดอายุไปแล้ว 1 วัน -> เน่าเสีย ห้ามกิน)
    $fridge->addItem(new PerishableFood(
        'F02',
        'อกไก่สด',
        (clone $today)->modify('-1 day'),
        'ควรปรุงสุกทันที'
    ));

    // 3. ผักสลัดกรีนโอ๊ค (PerishableFood: เหลืออีก 3 วัน -> ใกล้หมดอายุ)
    $fridge->addItem(new PerishableFood(
        'F03',
        'ผักสลัดกรีนโอ๊ค',
        (clone $today)->modify('+3 days'),
        'ใส่กล่องปิดฝาก่อนแช่'
    ));

    // 4. ไข่ไก่ (FoodItem: เหลืออีก 10 วัน -> สดใหม่)
    $fridge->addItem(new FoodItem(
        'F04',
        'ไข่ไก่ 1 แผง',
        (clone $today)->modify('+10 days'),
        'อาหารทั่วไป'
    ));

    // 5. โยเกิร์ตรสธรรมชาติ (FoodItem: หมดอายุวันนี้พอดี!)
    $fridge->addItem(new FoodItem(
        'F05',
        'โยเกิร์ตพร้อมดื่ม',
        clone $today,
        'ผลิตภัณฑ์นม'
    ));

    // 6. นักเก็ตไก่แช่แข็ง (FrozenFood: เหลืออีก 60 วัน)
    $fridge->addItem(new FrozenFood(
        'F06',
        'นักเก็ตไก่แช่แข็ง',
        (clone $today)->modify('+60 days'),
        -18
    ));

    return $fridge;
}

// สร้างตู้เย็นเริ่มต้น
$fridge = createDefaultFridge($today);

// โหลดรายการอาหารที่เคยทานแล้วจาก Session
if (isset($_SESSION['consumed_items']) && is_array($_SESSION['consumed_items'])) {
    foreach ($_SESSION['consumed_items'] as $id) {
        $fridge->consumeItem($id);
    }
}

// โหลดอาหารที่ผู้ใช้เพิ่มใหม่จากหน้าเว็บ
if (isset($_SESSION['custom_items']) && is_array($_SESSION['custom_items'])) {
    foreach ($_SESSION['custom_items'] as $raw) {
        $exp = new DateTime($raw['expire']);
        if ($raw['type'] === 'perishable') {
            $newItem = new PerishableFood($raw['id'], $raw['name'], $exp, $raw['tip'] ?? '');
        } elseif ($raw['type'] === 'frozen') {
            $newItem = new FrozenFood($raw['id'], $raw['name'], $exp);
        } else {
            $newItem = new FoodItem($raw['id'], $raw['name'], $exp, $raw['category']);
        }

        $fridge->addItem($newItem);

        if (!empty($raw['consumed'])) {
            $fridge->consumeItem($raw['id']);
        }
    }
}
