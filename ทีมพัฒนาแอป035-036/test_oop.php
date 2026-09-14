<?php
declare(strict_types=1);

require_once __DIR__ . '/classes/Room.php';
require_once __DIR__ . '/classes/Booking.php';
require_once __DIR__ . '/classes/BookingManager.php';

echo "=== STARTING OOP CLASS VERIFICATION ===\n\n";

// 1. Test Room Class
echo "1. Testing Room Class:\n";
$room = new Room(
    'TEST1',
    'Test Conference',
    'Boardroom',
    10,
    500.0,
    ['WiFi', 'Display'],
    'test.jpg',
    'Test description',
    1
);
assert($room->getName() === 'Test Conference');
assert($room->calculateBasePrice(2.5) === 1250.0);
assert($room->isSuitableFor(8) === true);
assert($room->isSuitableFor(12) === false);
echo "  [PASS] Room instantiation, base price calculation, and suitability checks passed!\n";

// 2. Test Booking Class
echo "\n2. Testing Booking Class:\n";
$booking = new Booking(
    'BK-TEST-001',
    'Somchai Jaidee',
    '0811112222',
    'somchai@test.com',
    'Sample Org',
    'Sprint Review',
    $room,
    date('Y-m-d'),
    '10:00',
    '12:00',
    5,
    [['id' => 'coffee', 'name' => 'Coffee', 'price' => 100.0, 'quantity' => 5]],
    'CONFIRMED'
);
// Base: 2h * 500 = 1000. Add-ons: 5 * 100 = 500. Subtotal: 1500. Tax 7%: 105. Total: 1605.
echo "  Duration: {$booking->getDurationHours()} hrs\n";
echo "  Base: {$booking->getBaseAmount()}, AddOns: {$booking->getAddOnsAmount()}, Tax: {$booking->getTaxAmount()}, Total: {$booking->getTotalAmount()}\n";
assert($booking->getDurationHours() === 2.0);
assert($booking->getBaseAmount() === 1000.0);
assert($booking->getAddOnsAmount() === 500.0);
assert($booking->getTotalAmount() === 1605.0);
echo "  [PASS] Booking pricing math, VAT, and duration calculated accurately!\n";

// 3. Test BookingManager Class
echo "\n3. Testing BookingManager Class:\n";
$manager = new BookingManager(__DIR__ . '/data_test');

// Test availability & conflict
$roomsAvail = $manager->getAvailableRooms(date('Y-m-d'), '10:00', '12:00', 4);
echo "  Initial available rooms: " . count($roomsAvail) . "\n";

// Create booking
$testRoom = $roomsAvail[0];
$newBooking = new Booking(
    '',
    'Test Guest',
    '0899998888',
    'guest@test.com',
    'Guest Co',
    'Annual Meeting',
    $testRoom,
    date('Y-m-d', strtotime('+3 days')),
    '14:00',
    '16:00',
    4
);
$res = $manager->createBooking($newBooking);
assert($res['success'] === true);
echo "  Booking created with code: " . $res['booking']['bookingCode'] . "\n";

// Test conflict detection with overlapping time (15:00 - 17:00)
$overlapBooking = new Booking(
    '',
    'Conflict Person',
    '0877776666',
    'conflict@test.com',
    'Overlap Co',
    'Overlap Meeting',
    $testRoom,
    date('Y-m-d', strtotime('+3 days')),
    '15:00',
    '17:00',
    4
);
$resOverlap = $manager->createBooking($overlapBooking);
assert($resOverlap['success'] === false);
echo "  [PASS] Conflict detection successfully blocked overlapping reservation!\n";

// Test cancel booking
$cancelRes = $manager->cancelBooking($res['booking']['bookingCode']);
assert($cancelRes['success'] === true);
echo "  [PASS] Cancel booking successful!\n";

// Clean up test data folder
$files = glob(__DIR__ . '/data_test/*');
foreach ($files as $file) {
    if (is_file($file)) unlink($file);
}
rmdir(__DIR__ . '/data_test');

echo "\n=== ALL OOP UNIT TESTS PASSED SUCCESSFULLY! ===\n";
