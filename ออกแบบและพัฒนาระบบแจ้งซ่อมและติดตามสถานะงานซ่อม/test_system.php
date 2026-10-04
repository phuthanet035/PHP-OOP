<?php

declare(strict_types=1);

/**
 * Automated System Test Script
 * Tests:
 * 1. Database Connection & Singleton
 * 2. Authentication & RBAC
 * 3. Ticket Creation & File Upload
 * 4. State Machine Transitions Matrix (Open -> Assigned -> InProgress -> Resolved -> Closed)
 * 5. Rejection flow (Resolved -> InProgress)
 * 6. Observer Pattern & LINE Notification Dispatch
 * 7. Admin Dashboard Aggregates
 */

require_once __DIR__ . '/src/Core/Autoloader.php';
require_once __DIR__ . '/src/Core/helpers.php';
use App\Core\Autoloader;

Autoloader::register();
Autoloader::addNamespace('App', __DIR__ . '/src');

// Load .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$name, $value] = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value, " \t\n\r\0\x0B\"'");
        }
    }
}

use App\Core\Database;
use App\Core\Auth;
use App\Core\EventDispatcher;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use App\Repositories\TicketRepository;
use App\Repositories\UserRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\CommentRepository;
use App\Repositories\StatusLogRepository;
use App\Repositories\RatingRepository;
use App\Services\TicketService;
use App\Services\TicketStatusService;
use App\Services\DashboardService;
use App\Observers\TicketObserver;
use App\Notifications\LineMessagingService;

echo "=== STARTING COMPREHENSIVE SYSTEM TESTS ===\n\n";

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $testName) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$testName}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$testName}\n";
        $failed++;
    }
}

// 1. Hook Observer to EventDispatcher
$events = EventDispatcher::getInstance();
$events->listen('ticket.created', [TicketObserver::class, 'handleCreated']);
$events->listen('ticket.status_changed', [TicketObserver::class, 'handleStatusChanged']);

// Test 1: Database Singleton
$db1 = Database::getInstance();
$db2 = Database::getInstance();
assertTest($db1 === $db2, "Database::getInstance() returns singleton instance");

// Test 2: Users in DB
$userRepo = new UserRepository();
$adminUser = $userRepo->findByEmail('admin@helpdesk.com');
$techUser = $userRepo->findByEmail('tech@helpdesk.com');
$normalUser = $userRepo->findByEmail('user@helpdesk.com');
assertTest(!empty($adminUser) && $adminUser['role'] === 'admin', "Admin user loaded with role 'admin'");
assertTest(!empty($techUser) && $techUser['role'] === 'technician', "Technician user loaded with role 'technician'");
assertTest(!empty($normalUser) && $normalUser['role'] === 'user', "User loaded with role 'user'");

// Test 3: Password verification
$authCheck = Auth::login('admin@helpdesk.com', 'password123');
assertTest($authCheck === true, "Auth::login() validates password_hash correctly");
assertTest(Auth::isAdmin() === true, "Auth::isAdmin() returns true for admin");

// Test 4: Ticket Creation through TicketService
$ticketService = new TicketService();
$ticketRepo = new TicketRepository();
$ticketId = $ticketService->createTicket([
    'title'       => 'เครื่องคอมพิวเตอร์ห้อง 402 เปิดเครื่องไม่ติด พัดลมไม่หมุน',
    'category_id' => 1,
    'priority'    => 'Urgent',
    'description' => 'ทดสอบเปิดสวิตช์แล้วเงียบสนิท ไม่มีเสียงพัดลมหรือไฟสถานะติด มีงานด่วนต้องใช้เอกสาร',
], null, $normalUser);

assertTest($ticketId > 0, "TicketService::createTicket() inserted new ticket #{$ticketId}");
$newTicket = $ticketRepo->findWithDetails($ticketId);
assertTest($newTicket['status'] === TicketStatus::Open->value, "New ticket has initial status 'Open'");
assertTest($newTicket['priority'] === TicketPriority::Urgent->value, "New ticket has priority 'Urgent'");

// Test 5: State Machine Transition (Open -> Assigned by Admin)
$statusService = new TicketStatusService();
$assignResult = $statusService->transition($newTicket, TicketStatus::Assigned->value, $adminUser, [
    'technician_id' => (int) $techUser['id'],
    'note'          => 'แอดมินมอบหมายงานให้ช่างสมศักดิ์เข้าดำเนินการ',
]);
assertTest($assignResult === true, "Transition Open -> Assigned by Admin successful");

$assignedTicket = $ticketRepo->findWithDetails($ticketId);
assertTest($assignedTicket['status'] === TicketStatus::Assigned->value, "Ticket status is now 'Assigned'");
assertTest((int)$assignedTicket['technician_id'] === (int)$techUser['id'], "Ticket assigned to technician #{$techUser['id']}");

// Test 6: State Machine Transition (Assigned -> InProgress by Technician)
$inProgressResult = $statusService->transition($assignedTicket, TicketStatus::InProgress->value, $techUser, [
    'comment' => 'ช่างรับงานและกำลังเดินทางไปตรวจสอบที่ห้อง 402',
]);
assertTest($inProgressResult === true, "Transition Assigned -> InProgress by Assigned Tech successful");

$inProgressTicket = $ticketRepo->findWithDetails($ticketId);
assertTest($inProgressTicket['status'] === TicketStatus::InProgress->value, "Ticket status is now 'InProgress'");

// Test 7: State Machine Transition (InProgress -> Resolved by Technician with Photo)
$resolveResult = $statusService->transition($inProgressTicket, TicketStatus::Resolved->value, $techUser, [
    'comment'    => 'ตรวจสอบพบสายไฟ AC ขั้วหลวม ได้ทำการเปลี่ยนสายไฟเส้นใหม่และทดสอบเปิดติดเรียบร้อย',
    'image_path' => 'sample_repair_ram.jpg',
]);
assertTest($resolveResult === true, "Transition InProgress -> Resolved with required photo successful");

$resolvedTicket = $ticketRepo->findWithDetails($ticketId);
assertTest($resolvedTicket['status'] === TicketStatus::Resolved->value, "Ticket status is now 'Resolved'");
assertTest(!empty($resolvedTicket['resolved_at']), "resolved_at timestamp set properly");

// Test 8: State Machine Transition (Resolved -> Closed by Ticket Owner with 5-star Rating)
$closeResult = $statusService->transition($resolvedTicket, TicketStatus::Closed->value, $normalUser, [
    'rating_score'    => 5,
    'rating_feedback' => 'ช่างบริการรวดเร็วมาก เปลี่ยนสายไฟใหม่ใช้งานได้ทันเวลาสอน ประทับใจมากครับ',
]);
assertTest($closeResult === true, "Transition Resolved -> Closed by Ticket Owner with rating successful");

$closedTicket = $ticketRepo->findWithDetails($ticketId);
assertTest($closedTicket['status'] === TicketStatus::Closed->value, "Ticket status is now 'Closed'");
assertTest(!empty($closedTicket['closed_at']), "closed_at timestamp set properly");

$ratingRepo = new RatingRepository();
$savedRating = $ratingRepo->findByTicket($ticketId);
assertTest(!empty($savedRating) && (int)$savedRating['score'] === 5, "Rating record saved with 5 stars");

// Test 9: Rejection Transition Test (Resolved -> InProgress by Owner)
// Create another ticket to test rejection flow
$rejTicketId = $ticketService->createTicket([
    'title'       => 'ทดสอบระบบการปฏิเสธงานซ่อม (Rejection Flow Test)',
    'category_id' => 3,
    'priority'    => 'Low',
    'description' => 'สร้างตั๋วงานเพื่อทดสอบกรณีผู้ใช้งานปฏิเสธผลงานซ่อมและส่งแก้งาน',
], null, $normalUser);
$rejTicket = $ticketRepo->findWithDetails($rejTicketId);
$statusService->transition($rejTicket, 'Assigned', $adminUser, ['technician_id' => (int) $techUser['id']]);
$rejTicket = $ticketRepo->findWithDetails($rejTicketId);
$statusService->transition($rejTicket, 'InProgress', $techUser, ['comment' => 'เริ่มซ่อม']);
$rejTicket = $ticketRepo->findWithDetails($rejTicketId);
$statusService->transition($rejTicket, 'Resolved', $techUser, ['image_path' => 'sample_repair_ram.jpg']);
$rejTicket = $ticketRepo->findWithDetails($rejTicketId);

// User rejects resolution with reason:
$statusService->transition($rejTicket, 'InProgress', $normalUser, [
    'comment' => 'ทดสอบพิมพ์แล้วยังมีคราบหมึกดำติดที่ขอบกระดาษ ขอให้ช่างตรวจสอบดรัมหมึกอีกรอบครับ'
]);
$revertedTicket = $ticketRepo->findWithDetails($rejTicketId);
assertTest($revertedTicket['status'] === TicketStatus::InProgress->value, "Resolved ticket successfully reverted to 'InProgress' on rejection");
assertTest($revertedTicket['resolved_at'] === null, "resolved_at cleared on rejection");

// Test 10: State Machine Illegal Transition Prevention
$illegalCaught = false;
try {
    // Cannot jump Open -> Resolved directly!
    $illegalTicket = $ticketService->createTicket([
        'title' => 'Illegal Jump Test',
        'category_id' => 1,
        'description' => 'Test invalid transition',
    ], null, $normalUser);
    $illegalObj = $ticketRepo->findWithDetails($illegalTicket);
    $statusService->transition($illegalObj, 'Resolved', $adminUser);
} catch (\DomainException $e) {
    $illegalCaught = true;
}
assertTest($illegalCaught === true, "State Machine blocks illegal transition (Open -> Resolved)");

// Test 11: Audit Trail Logging (status_logs)
$logRepo = new StatusLogRepository();
$logs = $logRepo->findByTicket($ticketId);
assertTest(count($logs) >= 4, "Status audit logs recorded all transitions (Count: " . count($logs) . ")");

// Test 12: LINE Notification Dispatch via Observer
$lineLogs = LineMessagingService::getRecentLogs(5);
assertTest(!empty($lineLogs), "LINE notifications recorded in log file via TicketObserver");

// Test 13: Dashboard Statistics
$dashService = new DashboardService();
$stats = $dashService->getStatistics();
assertTest($stats['totalTickets'] >= 5, "Dashboard reflects accurate total tickets ({$stats['totalTickets']})");
assertTest(isset($stats['statusCounts']['Open']), "Dashboard counts tickets by status");
assertTest($stats['avgRating'] > 0, "Dashboard computes average satisfaction rating ({$stats['avgRating']})");

echo "\n============================================\n";
echo " TEST SUMMARY: {$passed} PASSED, {$failed} FAILED\n";
echo "============================================\n";

exit($failed > 0 ? 1 : 0);
