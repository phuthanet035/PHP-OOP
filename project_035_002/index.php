<?php
/**
 * SmartFridge: ระบบแจ้งเตือนอาหารในตู้เย็นที่ใกล้จะหมดอายุ (Food Expiry Tracker)
 * -----------------------------------------------------------------------------
 * ไฟล์นี้คือ "Entry Point (จุดเริ่มต้นระบบ)" ทำหน้าที่เป็นตัวประสานงาน (Controller)
 * และแสดงผลหน้าจอ (View) โดยเชื่อมโยงส่วนประกอบที่ถูกแยกไว้อย่างเป็นระเบียบ:
 * 
 * 1. classes/                  : รวม Class Blueprints (FoodItem, PerishableFood, FrozenFood, SmartFridge)
 * 2. includes/init_data.php    : จัดเตรียมข้อมูลตัวอย่าง และโหลด Session
 * 3. includes/action_handler.php: จัดการคำสั่งเพิ่ม/กินอาหาร จากฟอร์ม
 * 4. style.css                 : ไฟล์ตกแต่งหน้าตาระบบทั้งหมด (โทนธรรมชาติ สบายตา ไม่ฉูดฉาด)
 */

// ให้ PHP Built-in Server ให้บริการไฟล์สถิต (เช่น style.css) ได้อย่างถูกต้อง
if (PHP_SAPI === 'cli-server') {
    $urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($urlPath !== '/' && $urlPath !== '/index.php' && file_exists(__DIR__ . $urlPath)) {
        return false;
    }
}

// เริ่มต้น Session สำหรับจำลองสถานะการใช้งาน
if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
    session_start();
}

// ==============================================================================
// 1. AUTOLOAD CLASSES (โหลดคลาสอัตโนมัติจาก classes/)
// ==============================================================================
spl_autoload_register(function ($className) {
    $file = __DIR__ . '/classes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// ==============================================================================
// 2. LOAD DATA & HANDLE ACTIONS (แยกไฟล์ logic ออกไปเรียบร้อย)
// ==============================================================================
// ดึงข้อมูลตู้เย็นและวันปัจจุบัน (ได้ตัวแปร $fridge, $today)
require_once __DIR__ . '/includes/init_data.php';

// กรณีรันผ่าน Terminal (CLI Mode)
if (PHP_SAPI === 'cli') {
    echo "========================================================\n";
    echo "  SmartFridge: ระบบแจ้งเตือนอาหารในตู้เย็น (PHP OOP + OOAD)\n";
    echo "========================================================\n\n";
    echo "❄️ ตู้เย็นของ: {$fridge->getOwnerName()} (วันที่ปัจจุบัน: {$today->format('Y-m-d')})\n\n";

    echo "--- 📋 รายการอาหารทั้งหมดในตู้เย็น ---\n";
    foreach ($fridge->getActiveItems() as $item) {
        $days = $item->daysLeft($today);
        $daysText = $days >= 0 ? "เหลือ {$days} วัน" : "เลยมา " . abs($days) . " วัน";
        printf("• %-24s [%s] -> %s\n", $item->getName(), $daysText, $item->getStatusText($today));
    }

    echo "\n--------------------------------------------------------\n";
    echo "🚨 เมนูแนะนำที่ 'ต้องรีบนำมาทำอาหารทันที' เพื่อไม่ให้เสียทิ้ง:\n";
    $urgents = $fridge->getUrgentItems($today);
    foreach ($urgents as $u) {
        echo "   👉 {$u->getName()} ({$u->daysLeft($today)} วันคงเหลือ)\n";
    }

    echo "\n>>> จำลองผู้ใช้หยิบ 'นมสดพาสเจอร์ไรส์' ไปดื่ม:\n";
    $fridge->consumeItem('F01');
    echo "😋 รับประทานเรียบร้อย! ขยะอาหารลดลง 1 ชิ้น\n\n";
    exit(0);
}

// ประมวลผล Action จากฟอร์มหน้าเว็บ (ได้ตัวแปร $alertMsg)
require_once __DIR__ . '/includes/action_handler.php';

// ดึงข้อมูลสรุปจากออบเจกต์ตู้เย็น
$activeItems = $fridge->getActiveItems();
$urgentItems = $fridge->getUrgentItems($today);
$expiredItems = $fridge->getExpiredItems($today);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartFridge - ระบบแจ้งเตือนอาหารในตู้เย็นใกล้หมดอายุ (PHP OOP & OOAD)</title>
    <!-- Google Fonts: Prompt & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- เชื่อมโยงไฟล์ style.css ภายนอก -->
    <link rel="stylesheet" href="style.css">
    <!-- ดึงสไตล์สำรองเพื่อรับประกันความสวยงาม 100% -->
    <style>
    <?php 
    if (file_exists(__DIR__ . '/style.css')) {
        include __DIR__ . '/style.css';
    }
    ?>
    </style>
</head>
<body>
    <div class="container">
        <!-- 1. Header & Navigation -->
        <header>
            <div class="brand">
                <div class="logo">🥗❄️</div>
                <div>
                    <h1>SmartFridge: ตู้เย็นอัจฉริยะเตือนอาหารใกล้หมดอายุ</h1>
                    <p class="subtitle">ระบบจัดการอาหารและลดขยะอาหาร (Food Waste) ด้วย <strong>PHP 8 OOP + OOAD</strong></p>
                </div>
            </div>
            <div class="header-tools">
                <div class="date-badge">📅 วันจำลอง: <?= $today->format('d/m/Y') ?></div>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="reset">
                    <button type="submit" class="btn btn-reset" title="คืนค่าอาหารตัวอย่างเดิมทั้งหมด">🔄 คืนค่าเริ่มต้น</button>
                </form>
            </div>
        </header>

        <!-- 2. แจ้งเตือน Notification Banner -->
        <?php if (!empty($alertMsg)): ?>
            <div class="alert-banner">
                <?= htmlspecialchars($alertMsg) ?>
            </div>
        <?php endif; ?>

        <!-- 3. แถบสถิติภาพรวม Quick Stats (โทนสีนุ่มนวล สบายตา) -->
        <section class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon total">📦</div>
                <div>
                    <div class="stat-val"><?= count($activeItems) ?></div>
                    <div class="stat-lbl">อาหารคงเหลือในตู้เย็น</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon urgent">⚠️</div>
                <div>
                    <div class="stat-val" style="color: var(--state-urgent-text);"><?= count($urgentItems) ?></div>
                    <div class="stat-lbl">ต้องรีบกินด่วน (ใน 3 วัน)</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon expired">❌</div>
                <div>
                    <div class="stat-val" style="color: var(--state-expired-text);"><?= count($expiredItems) ?></div>
                    <div class="stat-lbl">หมดอายุแล้ว (ควรทิ้ง)</div>
                </div>
            </div>
        </section>

        <!-- 4. ส่วนเนื้อหาหลัก Main Layout -->
        <div class="main-layout">
            <!-- ฝั่งซ้าย: แสดงรายการอาหารในตู้เย็น -->
            <main>
                <!-- กล่องเตือนเมนูเร่งด่วน โทนสีพีชอ่อนสบายตา -->
                <?php if (!empty($urgentItems)): ?>
                    <div class="urgent-highlight">
                        <h3>🚨 เมนูแนะนำที่ต้องรีบกินด่วนวันนี้/เร็วๆ นี้ เพื่อไม่ให้เสียทิ้ง:</h3>
                        <p>
                            <?= implode(', ', array_map(fn($item) => $item->getName(), $urgentItems)) ?>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="section-title-row">
                    <h2 class="section-title">📋 รายการของในตู้เย็น (<?= htmlspecialchars($fridge->getOwnerName()) ?>)</h2>
                    <span class="section-meta">อัปเดตอัตโนมัติ</span>
                </div>

                <div class="food-list">
                    <?php if (empty($activeItems)): ?>
                        <div class="empty-state">
                            ตู้เย็นว่างเปล่า ไม่มีของเหลืออยู่แล้ว
                            <div style="margin-top: 8px;">
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="action" value="reset">
                                    <button type="submit" class="btn btn-reset">กดที่นี่เพื่อโหลดอาหารตัวอย่างเริ่มต้น</button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activeItems as $item): 
                            $isUrgent = $item->isUrgent($today);
                            $isExpired = $item->isExpired($today);
                            $cardClass = $isExpired ? 'expired' : ($isUrgent ? 'urgent' : '');
                        ?>
                            <div class="food-card <?= $cardClass ?>">
                                <div>
                                    <div class="food-header">
                                        <span class="food-title"><?= htmlspecialchars($item->getName()) ?></span>
                                        <span class="food-cat"><?= htmlspecialchars($item->getCategory()) ?></span>
                                    </div>
                                    <div class="food-status">
                                        <?= htmlspecialchars($item->getStatusText($today)) ?>
                                    </div>
                                    <div class="food-date">
                                        วันหมดอายุ: <?= $item->getExpireDate()->format('d/m/Y') ?>
                                    </div>
                                </div>

                                <form method="post">
                                    <input type="hidden" name="action" value="consume">
                                    <input type="hidden" name="food_id" value="<?= htmlspecialchars($item->getId()) ?>">
                                    <button type="submit" class="btn btn-eat" title="กดเมื่อนำไปทำอาหารหรือรับประทานแล้ว">
                                        😋 ทานแล้ว
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>

            <!-- ฝั่งขวา: ฟอร์มเพิ่มของใหม่ และ ข้อมูลสถาปัตยกรรม OOP -->
            <aside>
                <!-- ฟอร์มเพิ่มของใหม่เข้าตู้เย็น -->
                <div class="sidebar-card">
                    <h3>➕ นำของใหม่เข้าตู้เย็น</h3>
                    <form method="post">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <label>ชื่ออาหาร/วัตถุดิบ</label>
                            <input type="text" name="name" required placeholder="เช่น ผักบุ้ง, นมเปรี้ยว">
                        </div>
                        <div class="form-group">
                            <label>ประเภท (แยกไฟล์ใน classes/)</label>
                            <select name="type">
                                <option value="perishable">ของสดเน่าเสียง่าย (PerishableFood.php)</option>
                                <option value="general">อาหารทั่วไป (FoodItem.php)</option>
                                <option value="frozen">อาหารแช่แข็ง (FrozenFood.php)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>วันหมดอายุ</label>
                            <input type="date" name="expire_date" required value="<?= (clone $today)->modify('+4 days')->format('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label>คำแนะนำการเก็บ (ถ้ามี)</label>
                            <input type="text" name="tip" placeholder="เช่น แช่ช่องฟรีซ, ห่อกระดาษ">
                        </div>
                        <button type="submit" class="btn btn-submit">บันทึกเข้าตู้เย็น</button>
                    </form>
                </div>

                <!-- กล่องสรุปการแยกไฟล์ของระบบ -->
                <div class="sidebar-card">
                    <h3>📁 โครงสร้างไฟล์ที่แยกการทำงาน</h3>
                    <div class="oop-note">
                        <strong>📄 classes/ (พิมพ์เขียว OOP)</strong>
                        แยกคลาส <code>FoodItem</code>, <code>PerishableFood</code>, <code>FrozenFood</code>, <code>SmartFridge</code>
                    </div>
                    <div class="oop-note">
                        <strong>⚙️ includes/init_data.php</strong>
                        เตรียมข้อมูลเริ่มต้นและจัดการ Session
                    </div>
                    <div class="oop-note">
                        <strong>🎯 includes/action_handler.php</strong>
                        รับคำสั่งจากฟอร์ม (กินอาหาร, เพิ่มอาหาร, รีเซ็ต)
                    </div>
                    <div class="oop-note">
                        <strong>🎨 style.css</strong>
                        โทนสีสบายตา นุ่มนวล ดูง่าย ไม่ฉูดฉาด
                    </div>
                </div>
            </aside>
        </div>

        <footer>
            <p><strong>SmartFridge</strong> &bull; ระบบจัดการอาหารในตู้เย็น พัฒนาด้วย <strong>PHP 8 OOP + OOAD</strong></p>
            <p style="font-size:0.8rem; margin-top:4px; opacity:0.8;">
                แยกโค้ดและหน้าที่การทำงานเป็นสัดส่วน สะอาดตา และสบายตาต่อการใช้งาน
            </p>
        </footer>
    </div>
</body>
</html>
