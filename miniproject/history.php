<?php
$ordersFile = 'orders.json';
$orders = [];
if (file_exists($ordersFile)) {
    $orders = json_decode(file_get_contents($ordersFile), true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติใบเสร็จรับเงิน</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 800px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="margin-bottom: 0;">ประวัติใบเสร็จรับเงิน</h1>
            <a href="index.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">&larr; กลับไปหน้าสั่งงาน</a>
        </div>

        <?php if (empty($orders)): ?>
            <p style="text-align: center; color: var(--text-muted);">ยังไม่มีประวัติการสั่งงาน</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="receipt" style="margin-top: 0; margin-bottom: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline;">
                        <h2>รหัสออเดอร์: <?= htmlspecialchars($order['id']) ?></h2>
                        <span style="color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($order['date']) ?></span>
                    </div>
                    <div class="item">
                        <span>ลูกค้า:</span>
                        <span><?= htmlspecialchars($order['customerName']) ?> (<?= htmlspecialchars($order['phone']) ?>)</span>
                    </div>
                    <hr style="border: 0; border-top: 1px solid var(--border); margin: 10px 0;">
                    
                    <?php foreach ($order['items'] as $item): ?>
                    <div class="item">
                        <span>บริการ:</span>
                        <span><?= htmlspecialchars($item['serviceName']) ?></span>
                    </div>
                    <div class="item">
                        <span>กระดาษ:</span>
                        <span><?= htmlspecialchars($item['paperName']) ?></span>
                    </div>
                    <div class="item">
                        <span>รายละเอียด:</span>
                        <span><?= htmlspecialchars($item['details']) ?></span>
                    </div>
                    <div class="item">
                        <span>ราคา:</span>
                        <span><?= number_format($item['subtotal'], 2) ?> บาท</span>
                    </div>
                    <hr style="border: 0; border-top: 1px dashed var(--border); margin: 10px 0;">
                    <?php endforeach; ?>
                    
                    <div class="total">
                        <span>ยอดรวมทั้งสิ้น:</span>
                        <span><?= number_format($order['total'], 2) ?> บาท</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
