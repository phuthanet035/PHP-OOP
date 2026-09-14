<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/classes/Room.php';
require_once __DIR__ . '/classes/Booking.php';
require_once __DIR__ . '/classes/BookingManager.php';

try {
    $manager = new BookingManager();
    $action = $_GET['action'] ?? '';

    // Handle JSON body for POST requests
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
        case 'get_rooms':
            echo json_encode([
                'success' => true,
                'data' => $manager->getAllRooms()
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'check_availability':
            $date = $_GET['date'] ?? date('Y-m-d');
            $start = $_GET['start'] ?? '09:00';
            $end = $_GET['end'] ?? '11:00';
            $attendees = (int)($_GET['attendees'] ?? 1);

            $availableRooms = $manager->getAvailableRooms($date, $start, $end, $attendees);
            $availableIds = array_map(fn($r) => $r->getId(), $availableRooms);

            echo json_encode([
                'success' => true,
                'availableRoomIds' => $availableIds,
                'availableRooms' => array_map(fn($r) => $r->toArray(), $availableRooms)
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'create_booking':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
                exit;
            }

            $roomId = $inputData['roomId'] ?? '';
            $room = $manager->getRoomById($roomId);
            if (!$room) {
                echo json_encode(['success' => false, 'message' => 'ไม่พบห้องที่ระบุ']);
                exit;
            }

            $booking = new Booking(
                '',
                $inputData['customerName'] ?? '',
                $inputData['customerPhone'] ?? '',
                $inputData['customerEmail'] ?? '',
                $inputData['organization'] ?? '',
                $inputData['purpose'] ?? '',
                $room,
                $inputData['bookingDate'] ?? date('Y-m-d'),
                $inputData['startTime'] ?? '09:00',
                $inputData['endTime'] ?? '10:00',
                (int)($inputData['attendeeCount'] ?? 1),
                (array)($inputData['addOnServices'] ?? []),
                'CONFIRMED',
                date('Y-m-d H:i:s'),
                $inputData['notes'] ?? ''
            );

            $result = $manager->createBooking($booking);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        case 'get_bookings':
            $filters = [
                'date' => $_GET['date'] ?? null,
                'status' => $_GET['status'] ?? null,
                'roomId' => $_GET['roomId'] ?? null,
            ];
            // Filter out empty values
            $filters = array_filter($filters, fn($v) => !empty($v));

            $bookings = $manager->getAllBookings($filters);
            echo json_encode([
                'success' => true,
                'data' => $bookings
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'lookup_booking':
            $code = $_GET['code'] ?? '';
            if (empty($code)) {
                echo json_encode(['success' => false, 'message' => 'กรุณาระบุรหัสการจอง']);
                exit;
            }

            $booking = $manager->getBookingByCode($code);
            if (!$booking) {
                // Try searching by phone/contact
                $matches = $manager->searchBookings($code);
                if (!empty($matches)) {
                    echo json_encode([
                        'success' => true,
                        'data' => array_map(fn($b) => $b->toArray(), $matches),
                        'isList' => true
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                echo json_encode(['success' => false, 'message' => "ไม่พบข้อมูลการจองสำหรับ '{$code}'"]);
                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => $booking->toArray(),
                'isList' => false
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'cancel_booking':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
                exit;
            }

            $bookingCode = $inputData['bookingCode'] ?? '';
            $result = $manager->cancelBooking($bookingCode);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            break;

        case 'get_stats':
            $stats = $manager->getStatistics();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ], JSON_UNESCAPED_UNICODE);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action specified'
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
