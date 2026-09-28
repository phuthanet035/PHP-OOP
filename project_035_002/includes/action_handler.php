<?php
/**
 * ไฟล์: includes/action_handler.php
 * -------------------------------------------------------------
 * ทำหน้าที่:
 * 1. รับและประมวลผลคำสั่ง (Action) ที่ส่งมาจากฟอร์มหน้าเว็บผ่าน HTTP POST
 * 2. สั่งการไปยังออบเจกต์ $fridge เพื่อดำเนินงานตามคำสั่ง:
 *    - action = 'consume' : บันทึกว่าทานอาหารแล้ว ($fridge->consumeItem)
 *    - action = 'add'     : สร้าง Object อาหารชิ้นใหม่ตามคลาสที่เลือก แล้วใส่เข้าตู้เย็น ($fridge->addItem)
 *    - action = 'reset'   : ล้าง Session คืนค่ากลับสู่ตัวอย่างเริ่มต้น
 * 3. ส่งคืนข้อความแจ้งเตือน $alertMsg เพื่อแสดงผลบนหน้าจอ
 */

$alertMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. กรณีผู้ใช้กดปุ่ม "😋 ทานแล้ว"
    if ($action === 'consume') {
        $id = $_POST['food_id'] ?? '';
        if ($fridge->consumeItem($id)) {
            if (!isset($_SESSION['consumed_items'])) {
                $_SESSION['consumed_items'] = [];
            }
            if (!in_array($id, $_SESSION['consumed_items'], true)) {
                $_SESSION['consumed_items'][] = $id;
            }
            $alertMsg = "😋 บันทึกการรับประทานเรียบร้อยแล้ว! ขอบคุณที่ช่วยลดปัญหาขยะอาหาร (Food Waste)";
        }
    } 
    // 2. กรณีผู้ใช้กรอกฟอร์มเพิ่มอาหารใหม่
    elseif ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $dateStr = $_POST['expire_date'] ?? '';
        $type = $_POST['type'] ?? 'general';
        $tip = trim($_POST['tip'] ?? 'เก็บในตู้เย็น');

        if ($name && $dateStr) {
            $newId = 'CUSTOM_' . time() . '_' . rand(100, 999);
            $newExp = new DateTime($dateStr);

            // สร้าง Object ตามคลาส OOP ที่ผู้ใช้เลือก
            if ($type === 'perishable') {
                $newItem = new PerishableFood($newId, $name, $newExp, $tip);
            } elseif ($type === 'frozen') {
                $newItem = new FrozenFood($newId, $name, $newExp);
            } else {
                $newItem = new FoodItem($newId, $name, $newExp, 'ทั่วไป');
            }

            // เพิ่มเข้าตู้เย็น
            $fridge->addItem($newItem);

            // บันทึกลง Session เพื่อจำลองฐานข้อมูล
            if (!isset($_SESSION['custom_items'])) {
                $_SESSION['custom_items'] = [];
            }
            $_SESSION['custom_items'][] = [
                'id' => $newId,
                'name' => $name,
                'expire' => $dateStr,
                'type' => $type,
                'tip' => $tip,
                'category' => 'ทั่วไป',
                'consumed' => false
            ];

            $alertMsg = "📥 เพิ่ม '{$name}' เข้าตู้เย็นเรียบร้อยแล้ว!";
        }
    } 
    // 3. กรณีรีเซ็ตข้อมูลทั้งหมด
    elseif ($action === 'reset') {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            $_SESSION = [];
        }
        header("Location: index.php");
        exit;
    }
}
