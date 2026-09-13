<?php
require_once 'Order.php';
require_once 'PrintService.php';
require_once 'FinishingService.php';
require_once 'Paper.php';
require_once 'PaperSize.php';

$order = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = $_POST['customerName'] ?? 'Guest';
    $phone = $_POST['phone'] ?? '-';
    $paperSizeStr = $_POST['paperSize'] ?? 'A4';
    $isCardstock = isset($_POST['isCardstock']);
    $pages = (int)($_POST['pages'] ?? 1);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $serviceType = $_POST['serviceType'] ?? 'print';
    $isColor = isset($_POST['isColor']);

    $order = new Order($customerName, $phone);

    $paperSize = $paperSizeStr === 'A3' ? PaperSize::A3 : PaperSize::A4;
    $paper = new Paper($paperSize, $isCardstock);

    if ($serviceType === 'print') {
        $service = new PrintService("Document Printing", $isColor);
    } else {
        $service = new FinishingService("Binding/Finishing", 5.0); // Fixed unit price for example
    }

    $orderItem = new OrderItem($service, $paper, $pages, $quantity);
    $order->addItem($orderItem);

    // Save order to history
    $orderData = [
        'id' => uniqid(),
        'date' => date('Y-m-d H:i:s'),
        'customerName' => $order->customerName,
        'phone' => $order->phone,
        'items' => [],
        'total' => $order->getTotal()
    ];
    
    foreach ($order->getItems() as $item) {
        $orderData['items'][] = [
            'serviceName' => $item->service->getName(),
            'paperName' => $item->paper->size->value . ($item->paper->isCardstock ? ' (แข็ง)' : ' (ธรรมดา)'),
            'details' => $item->pages . ' หน้า x ' . $item->quantity . ' ชุด',
            'subtotal' => $item->getSubtotal()
        ];
    }
    
    $ordersFile = 'orders.json';
    $existingOrders = [];
    if (file_exists($ordersFile)) {
        $existingOrders = json_decode(file_get_contents($ordersFile), true) ?? [];
    }
    array_unshift($existingOrders, $orderData);
    file_put_contents($ordersFile, json_encode($existingOrders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printing Service Order</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1>ระบบสั่งพิมพ์งาน</h1>
            <a href="history.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">ประวัติใบเสร็จ</a>
        </div>
        <form method="POST">
            <div class="form-group">
                <label for="customerName">ชื่อลูกค้า</label>
                <input type="text" id="customerName" name="customerName" required>
            </div>
            <div class="form-group">
                <label for="phone">เบอร์โทรศัพท์</label>
                <input type="text" id="phone" name="phone" required>
            </div>
            
            <div class="form-group">
                <label for="serviceType">ประเภทบริการ</label>
                <select id="serviceType" name="serviceType">
                    <option value="print">งานปริ้น</option>
                    <option value="finish">เข้าเล่ม</option>
                </select>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="isColor" name="isColor">
                <label for="isColor" style="margin-bottom:0;">พิมพ์สี</label>
            </div>

            <div class="form-group">
                <label for="paperSize">ขนาดกระดาษ</label>
                <select id="paperSize" name="paperSize">
                    <option value="A4">A4</option>
                    <option value="A3">A3</option>
                </select>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" id="isCardstock" name="isCardstock">
                <label for="isCardstock" style="margin-bottom:0;">กระดาษแข็ง (Cardstock)</label>
            </div>

            <div class="form-group">
                <label for="pages">จำนวนหน้า (ต่อ 1 ชุด)</label>
                <input type="number" id="pages" name="pages" min="1" value="1" required>
            </div>

            <div class="form-group">
                <label for="quantity">จำนวนชุด</label>
                <input type="number" id="quantity" name="quantity" min="1" value="1" required>
            </div>

            <button type="submit">คำนวณราคาและสั่งงาน</button>
        </form>

        <?php if ($order): ?>
        <div class="receipt">
            <h2>ใบเสร็จรับเงิน</h2>
            <div class="item">
                <span>ลูกค้า:</span>
                <span><?= htmlspecialchars($order->customerName) ?> (<?= htmlspecialchars($order->phone) ?>)</span>
            </div>
            <hr style="border: 0; border-top: 1px solid var(--border); margin: 10px 0;">
            
            <?php foreach ($order->getItems() as $item): ?>
            <div class="item">
                <span>บริการ:</span>
                <span><?= htmlspecialchars($item->service->getName()) ?></span>
            </div>
            <div class="item">
                <span>กระดาษ:</span>
                <span><?= $item->paper->size->value ?> <?= $item->paper->isCardstock ? '(แข็ง)' : '(ธรรมดา)' ?></span>
            </div>
            <div class="item">
                <span>รายละเอียด:</span>
                <span><?= $item->pages ?> หน้า x <?= $item->quantity ?> ชุด</span>
            </div>
            <div class="item">
                <span>ราคา:</span>
                <span><?= number_format($item->getSubtotal(), 2) ?> บาท</span>
            </div>
            <?php endforeach; ?>
            
            <div class="total">
                <span>ยอดรวมทั้งสิ้น:</span>
                <span><?= number_format($order->getTotal(), 2) ?> บาท</span>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
