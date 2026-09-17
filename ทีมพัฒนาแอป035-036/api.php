<?php
declare(strict_types=1);

/**
 * ==============================================================================
 * ไฟล์ API Gateway (SpaceHub RESTful Backend Endpoint Gateway)
 * ==============================================================================
 * พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
 * คำอธิบาย: ทำหน้าที่เป็นตัวกลางในการรับ Request จาก Front-end (Single Page App)
 * ประมวลผลลอจิก OOP ผ่าน BookingManager, User, Room, Booking แล้วส่งกลับเป็น JSON UTF-8
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/classes/Room.php';
require_once __DIR__ . '/classes/Booking.php';
require_once __DIR__ . '/classes/BookingManager.php';
require_once __DIR__ . '/classes/User.php';

try {
    $manager = new BookingManager();
    $action = $_GET['action'] ?? '';

    // อ่านข้อมูล JSON Request Payload เมื่อมีการส่งแบบ POST
    $inputData = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rawInput = file_get_contents('php://input');
        if ($rawInput) {
            $inputData = json_decode($rawInput, true) ?: [];
        } else {
            $inputData = $_POST;
        }
    }

    switch ($action) {
        // ==========================================================================
        // 1. ระบบยืนยันตัวตนสมาชิก และผู้ดูแลระบบ (User Authentication & Session API)
        // พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
        // ==========================================================================
        
        // 1.1 ล็อกอินเข้าสู่ระบบ (POST)
        case 'login':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
                exit;
            }
            $username = $inputData['username'] ?? '';
            $password = $inputData['password'] ?? '';

            $result = User::login($username, $password);
            if ($result['success'] && isset($result['user'])) {
                $_SESSION['user'] = $result['user']->toArray();
            }
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['message'],
                'user' => $result['success'] ? $_SESSION['user'] : null
            ], JSON_UNESCAPED_UNICODE);
            break;

        // 1.2 สมัครสมาชิกใหม่ (POST)
        case 'register':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
                exit;
            }
            $username = $inputData['username'] ?? '';
            $password = $inputData['password'] ?? '';
            $fullname = $inputData['fullname'] ?? '';
            $email = $inputData['email'] ?? '';
            $phone = $inputData['phone'] ?? '';

            $result = User::register($username, $password, $fullname, $email, $phone);
            if ($result['success'] && isset($result['user'])) {
                $_SESSION['user'] = $result['user']->toArray();
            }
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['message'],
                'user' => $result['success'] ? $_SESSION['user'] : null
            ], JSON_UNESCAPED_UNICODE);
            break;

        // 1.3 ออกจากระบบ (GET/POST)
        case 'logout':
            unset($_SESSION['user']);
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'ออกจากระบบเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
            break;

        // 1.4 ดึงข้อมูลผู้ใช้ปัจจุบันที่เข้าสู่ระบบอยู่ (GET)
        case 'get_current_user':
            $currentUser = $_SESSION['user'] ?? null;
            echo json_encode([
                'success' => true,
                'user' => $currentUser
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ==========================================================================
        // 2. ระบบจัดการข้อมูลห้องประชุมและตรวจสอบเวลาว่าง (Room & Availability API)
        // พัฒนาโดย: รหัส 035 (เน้นการดึงข้อมูลและตรวจสอบห้องประชุม)
        // ==========================================================================

        // 2.1 ดึงรายการห้องประชุมทั้งหมด (GET)
        case 'get_rooms':
            $rooms = array_map(fn($r) => $r->toArray(), $manager->getAllRooms());
            echo json_encode([
                'success' => true,
                'data' => $rooms
            ], JSON_UNESCAPED_UNICODE);
            break;

        // 2.2 ตรวจสอบรหัสห้องประชุมที่ว่างในช่วงเวลาที่กำหนด (GET)
        case 'check_availability':
            $date = $_GET['date'] ?? date('Y-m-d');
            $start = $_GET['start'] ?? '09:00';
            $end = $_GET['end'] ?? '12:00';
            $attendees = (int)($_GET['attendees'] ?? 1);

            $availableRooms = $manager->getAvailableRooms($date, $start, $end, $attendees);
            $availableRoomIds = array_map(fn($r) => $r->getId(), $availableRooms);

            echo json_encode([
                'success' => true,
                'availableRoomIds' => $availableRoomIds
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ==========================================================================
        // 3. ระบบธุรกรรมการจอง ค้นหา และยกเลิกการจอง (Booking Transaction API)
        // พัฒนาโดย: รหัส 036 ศุภนัฐ จันทร์เปรม (เน้นธุรกรรมและการคำนวณการเงิน)
        // ==========================================================================

        // 3.1 สร้างรายการจองใหม่ (POST)
        case 'create_booking':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
                exit;
            }
            $result = $manager->createBooking($inputData);
            if ($result['success'] && isset($result['booking'])) {
                $result['data'] = $result['booking']->toArray();
                unset($result['booking']);
            }
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        // 3.2 ดึงรายการจองทั้งหมดในระบบ (GET)
        case 'get_bookings':
            $bookings = array_map(fn($b) => $b->toArray(), $manager->getAllBookings());
            echo json_encode([
                'success' => true,
                'data' => $bookings
            ], JSON_UNESCAPED_UNICODE);
            break;

        // 3.3 ค้นหาข้อมูลการจองตามรหัสการจอง (GET)
        case 'lookup_booking':
            $code = $_GET['code'] ?? '';
            $booking = $manager->getBookingByCode($code);
            if (!$booking) {
                echo json_encode(['success' => false, 'message' => "ไม่พบข้อมูลการจองสำหรับ '{$code}'"]);
                exit;
            }
            echo json_encode([
                'success' => true,
                'data' => $booking->toArray()
            ], JSON_UNESCAPED_UNICODE);
            break;

        // 3.4 ยกเลิกการจอง (POST)
        case 'cancel_booking':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
                exit;
            }
            $bookingCode = $inputData['bookingCode'] ?? '';
            $success = $manager->cancelBooking($bookingCode);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'ยกเลิกการจองเรียบร้อยแล้ว' : 'ไม่พบรหัสการจองดังกล่าว'
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ==========================================================================
        // 4. ระบบจัดการหลังบ้านสำหรับแอดมิน (Admin Back-office API)
        // พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
        // ==========================================================================

        // 4.1 เปลี่ยนสถานะการจอง (POST) - สำหรับ Admin Management เท่านั้น
        case 'update_booking_status':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
                exit;
            }
            if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'ADMIN') {
                echo json_encode(['success' => false, 'message' => 'เข้าถึงถูกปฏิเสธ: เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถเปลี่ยนสถานะได้']);
                exit;
            }
            $code = $inputData['bookingCode'] ?? '';
            $newStatus = $inputData['status'] ?? '';
            $success = $manager->updateBookingStatus($code, $newStatus);
            echo json_encode([
                'success' => $success,
                'message' => $success ? "อัปเดตสถานะการจอง '{$code}' เป็น {$newStatus} สำเร็จ" : 'ไม่สามารถเปลี่ยนสถานะได้'
            ], JSON_UNESCAPED_UNICODE);
            break;

        // 4.2 ลบรายการจอง (POST) - สำหรับ Admin Delete เท่านั้น
        case 'delete_booking':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
                exit;
            }
            if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'ADMIN') {
                echo json_encode(['success' => false, 'message' => 'เข้าถึงถูกปฏิเสธ: เฉพาะผู้ดูแลระบบ (Admin) เท่านั้นที่สามารถลบรายการจองได้']);
                exit;
            }
            $code = $inputData['bookingCode'] ?? '';
            $success = $manager->deleteBooking($code);
            echo json_encode([
                'success' => $success,
                'message' => $success ? "ลบรายการจอง '{$code}' ออกจากระบบเรียบร้อยแล้ว" : 'ไม่พบรายการจอง'
            ], JSON_UNESCAPED_UNICODE);
            break;

        // ==========================================================================
        // 5. ดึงสถิติภาพรวมระบบ (Overview Dashboard Statistics API)
        // พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
        // ==========================================================================
        case 'get_stats':
            $stats = $manager->getOverviewStatistics();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ], JSON_UNESCAPED_UNICODE);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action requested'
            ]);
            break;
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server Error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
