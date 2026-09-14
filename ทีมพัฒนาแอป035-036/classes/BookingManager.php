<?php
declare(strict_types=1);

require_once __DIR__ . '/Room.php';
require_once __DIR__ . '/Booking.php';

/**
 * Class BookingManager
 * Orchestrator, Repository, and Business Logic Service for Room Reservations.
 * Demonstrates OOAD Controller & High Cohesion Pattern, Conflict Resolution, and Data Persistence.
 */
class BookingManager
{
    /** @var array<string, Room> */
    private array $rooms = [];

    /** @var array<string, Booking> */
    private array $bookings = [];

    private string $dataDir;
    private string $roomsFile;
    private string $bookingsFile;

    public function __construct(string $dataDir = '')
    {
        $this->dataDir = $dataDir ?: dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
        $this->roomsFile = $this->dataDir . DIRECTORY_SEPARATOR . 'rooms.json';
        $this->bookingsFile = $this->dataDir . DIRECTORY_SEPARATOR . 'bookings.json';

        $this->initializeStorage();
        $this->loadData();
    }

    /**
     * Ensure data directory exists and seed defaults if empty.
     */
    private function initializeStorage(): void
    {
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
        }

        // Seed initial rooms if file doesn't exist
        if (!file_exists($this->roomsFile)) {
            $defaultRooms = [
                [
                    'id' => 'R101',
                    'name' => 'Boardroom Alpha',
                    'type' => 'Executive Boardroom',
                    'capacity' => 16,
                    'hourlyRate' => 650.00,
                    'amenities' => ['4K Ultra-HD Display (75")', 'Polycom Video Conference Bar', 'Glass Whiteboard', 'Wireless Presentation', 'High-Speed Wi-Fi 6', 'Air Purifier'],
                    'imageUrl' => 'https://images.unsplash.com/photo-1517502884422-41eaead166d4?auto=format&fit=crop&w=800&q=80',
                    'description' => 'ห้องประชุมระดับผู้บริหาร บรรยากาศพรีเมียม เงียบสงบ พร้อมระบบวิดีโอคอนเฟอเรนซ์ระดับมืออาชีพ',
                    'minHours' => 1,
                ],
                [
                    'id' => 'R102',
                    'name' => 'Innovation Pod B',
                    'type' => 'Team Collaboration',
                    'capacity' => 8,
                    'hourlyRate' => 420.00,
                    'amenities' => ['Interactive Touch Screen (55")', 'Mobile Whiteboard', 'HDMI / Type-C Ports', 'Ergonomic Chairs', 'Power Stations'],
                    'imageUrl' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
                    'description' => 'ห้องทำงานกลุ่มขนาดกลาง เหมาะสำหรับ Brainstorming, Workshop ย่อย และการประชุมทีมประจำสัปดาห์',
                    'minHours' => 1,
                ],
                [
                    'id' => 'R103',
                    'name' => 'Podcast & Studio Suite',
                    'type' => 'Audio / Media Studio',
                    'capacity' => 4,
                    'hourlyRate' => 550.00,
                    'amenities' => ['Acoustic Soundproofing Foam', 'Shure SM7B Microphones (x4)', 'RodeCaster Pro Audio Board', 'Elgato Key Lights', '4K Streaming Cam'],
                    'imageUrl' => 'https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?auto=format&fit=crop&w=800&q=80',
                    'description' => 'ห้องสตูดิโอบันทึกเสียงและพอดแคสต์แบบเก็บเสียง 100% พร้อมอุปกรณ์สตรีมมิงและบันทึกระดับ Broadcast',
                    'minHours' => 2,
                ],
                [
                    'id' => 'R104',
                    'name' => 'Creative Workshop Hall',
                    'type' => 'Seminar & Workshop',
                    'capacity' => 30,
                    'hourlyRate' => 1200.00,
                    'amenities' => ['Dual Laser Projectors', 'Wireless Lavalier & Handheld Mics', 'Flexible Modular Desks', 'Stage Podium', 'Surround Sound System'],
                    'imageUrl' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=800&q=80',
                    'description' => 'ห้องสัมมนาและเวิร์กชอปขนาดใหญ่ สามารถปรับเปลี่ยนการจัดโต๊ะได้หลากหลายรูปแบบตามสไตล์งาน',
                    'minHours' => 2,
                ],
                [
                    'id' => 'R105',
                    'name' => 'Private Focus Pod',
                    'type' => 'Single / Duo Focus',
                    'capacity' => 2,
                    'hourlyRate' => 180.00,
                    'amenities' => ['Active Noise Cancelling Shell', 'Adjustable Standing Desk', 'Webcam Light', 'USB-C Fast Charging', 'Ergonomic Stool'],
                    'imageUrl' => 'https://images.unsplash.com/photo-1527192491265-7e15c55b1ed2?auto=format&fit=crop&w=800&q=80',
                    'description' => 'ตู้ทำงานส่วนบุคคลสำหรับการสัมภาษณ์งานออนไลน์ โทรศัพท์คุยธุรกิจ หรือทำงานที่ต้องการสมาธิสูงสุด',
                    'minHours' => 1,
                ]
            ];
            file_put_contents($this->roomsFile, json_encode($defaultRooms, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        // Seed initial bookings if file doesn't exist
        if (!file_exists($this->bookingsFile)) {
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));

            $sampleBookings = [
                [
                    'bookingCode' => 'BK-2026-A101',
                    'customerName' => 'ดร. นภัสสร วงศ์พิพิธ',
                    'customerPhone' => '081-987-6543',
                    'customerEmail' => 'napassorn.w@techpulse.co.th',
                    'organization' => 'TechPulse Innovations Ltd.',
                    'purpose' => 'การประชุมวางแผนกลยุทธ์ประจำไตรมาส Q4',
                    'roomId' => 'R101',
                    'roomName' => 'Boardroom Alpha',
                    'roomType' => 'Executive Boardroom',
                    'roomRate' => 650.00,
                    'bookingDate' => $today,
                    'startTime' => '09:00',
                    'endTime' => '12:00',
                    'durationHours' => 3.0,
                    'attendeeCount' => 12,
                    'addOnServices' => [
                        ['id' => 'coffee', 'name' => 'ชุดกาแฟสดพรีเมียมและเบเกอรี่', 'price' => 120.0, 'quantity' => 12],
                        ['id' => 'tech_support', 'name' => 'เจ้าหน้าที่เทคนิคประจำห้องประชุม', 'price' => 500.0, 'quantity' => 1]
                    ],
                    'status' => 'CONFIRMED',
                    'createdAt' => date('Y-m-d H:i:s', strtotime('-1 day')),
                    'notes' => 'ต้องการไมค์ไร้สายเพิ่ม 2 ตัว และชาเขียวร้อน',
                    'baseAmount' => 1950.0,
                    'addOnsAmount' => 1940.0,
                    'taxAmount' => 272.3,
                    'totalAmount' => 4162.3
                ],
                [
                    'bookingCode' => 'BK-2026-B205',
                    'customerName' => 'คุณพงศกร สิทธิชัย',
                    'customerPhone' => '089-123-4567',
                    'customerEmail' => 'pongsakorn.dev@studio.io',
                    'organization' => 'Creator Space Thailand',
                    'purpose' => 'บันทึกรายการ Podcast สัมภาษณ์พิเศษตอนที่ 15',
                    'roomId' => 'R103',
                    'roomName' => 'Podcast & Studio Suite',
                    'roomType' => 'Audio / Media Studio',
                    'roomRate' => 550.00,
                    'bookingDate' => $today,
                    'startTime' => '13:30',
                    'endTime' => '16:30',
                    'durationHours' => 3.0,
                    'attendeeCount' => 3,
                    'addOnServices' => [
                        ['id' => 'audio_engineer', 'name' => 'Sound Engineer คุมการบันทึกเสียง', 'price' => 800.0, 'quantity' => 1]
                    ],
                    'status' => 'CONFIRMED',
                    'createdAt' => date('Y-m-d H:i:s', strtotime('-2 days')),
                    'notes' => 'บันทึกแบบ 4 Tracks แยกไฟล์ WAV',
                    'baseAmount' => 1650.0,
                    'addOnsAmount' => 800.0,
                    'taxAmount' => 171.5,
                    'totalAmount' => 2621.5
                ],
                [
                    'bookingCode' => 'BK-2026-C309',
                    'customerName' => 'คุณกานต์พิชชา รัตนเวทย์',
                    'customerPhone' => '062-555-8910',
                    'customerEmail' => 'karnpitcha@designlab.com',
                    'organization' => 'DesignLab Academy',
                    'purpose' => 'อบรม Design Thinking สำหรับสตาร์ทอัพ',
                    'roomId' => 'R104',
                    'roomName' => 'Creative Workshop Hall',
                    'roomType' => 'Seminar & Workshop',
                    'roomRate' => 1200.00,
                    'bookingDate' => $tomorrow,
                    'startTime' => '10:00',
                    'endTime' => '15:00',
                    'durationHours' => 5.0,
                    'attendeeCount' => 25,
                    'addOnServices' => [
                        ['id' => 'coffee', 'name' => 'ชุดกาแฟสดพรีเมียมและเบเกอรี่', 'price' => 120.0, 'quantity' => 25],
                        ['id' => 'whiteboard_kit', 'name' => 'อุปกรณ์ Workshop & Post-it ชุดใหญ่', 'price' => 450.0, 'quantity' => 1]
                    ],
                    'status' => 'CONFIRMED',
                    'createdAt' => date('Y-m-d H:i:s', strtotime('-3 days')),
                    'notes' => 'จัดโต๊ะแบบกลุ่มย่อย 5 กลุ่ม กลุ่มละ 5 คน',
                    'baseAmount' => 6000.0,
                    'addOnsAmount' => 3450.0,
                    'taxAmount' => 661.5,
                    'totalAmount' => 10111.5
                ]
            ];
            file_put_contents($this->bookingsFile, json_encode($sampleBookings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Load data from JSON storage into OOP collections.
     */
    public function loadData(): void
    {
        $this->rooms = [];
        $this->bookings = [];

        // Load Rooms
        if (file_exists($this->roomsFile)) {
            $rawRooms = json_decode(file_get_contents($this->roomsFile) ?: '[]', true) ?: [];
            foreach ($rawRooms as $item) {
                $room = Room::fromArray($item);
                $this->rooms[$room->getId()] = $room;
            }
        }

        // Load Bookings
        if (file_exists($this->bookingsFile)) {
            $rawBookings = json_decode(file_get_contents($this->bookingsFile) ?: '[]', true) ?: [];
            foreach ($rawBookings as $bData) {
                $roomId = $bData['roomId'] ?? '';
                if (isset($this->rooms[$roomId])) {
                    $room = $this->rooms[$roomId];
                    $booking = Booking::fromArray($bData, $room);
                    $this->bookings[$booking->getBookingCode()] = $booking;
                }
            }
        }
    }

    /**
     * Persist current bookings collection to JSON file.
     */
    public function saveBookings(): bool
    {
        $data = [];
        foreach ($this->bookings as $booking) {
            $data[] = $booking->toArray();
        }

        return file_put_contents(
            $this->bookingsFile,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        ) !== false;
    }

    /**
     * Core Algorithm: Check if a time slot has collisions with existing confirmed bookings.
     * Overlap condition: NOT (NewEnd <= ExistStart OR NewStart >= ExistEnd)
     * Equivalently: (NewStart < ExistEnd) AND (NewEnd > ExistStart)
     */
    public function hasTimeConflict(
        string $roomId,
        string $date,
        string $startTime,
        string $endTime,
        ?string $excludeBookingCode = null
    ): bool {
        $reqStart = strtotime($date . ' ' . $startTime);
        $reqEnd = strtotime($date . ' ' . $endTime);

        if (!$reqStart || !$reqEnd || $reqEnd <= $reqStart) {
            return true; // Invalid time range treated as conflict
        }

        foreach ($this->bookings as $booking) {
            // Ignore if different room, different date, cancelled status, or self
            if ($booking->getRoom()->getId() !== $roomId) continue;
            if ($booking->getBookingDate() !== $date) continue;
            if ($booking->getStatus() === 'CANCELLED') continue;
            if ($excludeBookingCode && $booking->getBookingCode() === $excludeBookingCode) continue;

            $existStart = strtotime($booking->getBookingDate() . ' ' . $booking->getStartTime());
            $existEnd = strtotime($booking->getBookingDate() . ' ' . $booking->getEndTime());

            // Mathematical Overlap condition
            if ($reqStart < $existEnd && $reqEnd > $existStart) {
                return true; // Collision found!
            }
        }

        return false;
    }

    /**
     * Get list of rooms that are strictly available for the requested slot & attendees.
     * @return Room[]
     */
    public function getAvailableRooms(
        string $date,
        string $startTime,
        string $endTime,
        int $attendees = 1
    ): array {
        $available = [];

        foreach ($this->rooms as $room) {
            if (!$room->isSuitableFor($attendees)) {
                continue;
            }

            if (!$this->hasTimeConflict($room->getId(), $date, $startTime, $endTime)) {
                $available[] = $room;
            }
        }

        return $available;
    }

    /**
     * Create and validate a new booking reservation.
     * @return array [ 'success' => bool, 'message' => string, 'booking' => ?array ]
     */
    public function createBooking(Booking $booking): array
    {
        // 1. Validate booking fields & business rules
        $validation = $booking->validateTimeSlot();
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => implode(' | ', $validation['errors']),
                'booking' => null,
            ];
        }

        // 2. Validate customer information
        if (empty($booking->getCustomerName())) {
            return [
                'success' => false,
                'message' => 'กรุณากรอกชื่อ-นามสกุลของผู้จอง',
                'booking' => null,
            ];
        }

        if (empty($booking->getCustomerPhone()) && empty($booking->getCustomerEmail())) {
            return [
                'success' => false,
                'message' => 'กรุณาระบุเบอร์โทรศัพท์หรืออีเมลสำหรับติดต่อกลับ',
                'booking' => null,
            ];
        }

        // 3. Collision / Conflict Check
        $roomId = $booking->getRoom()->getId();
        if ($this->hasTimeConflict(
            $roomId,
            $booking->getBookingDate(),
            $booking->getStartTime(),
            $booking->getEndTime()
        )) {
            return [
                'success' => false,
                'message' => "ห้อง {$booking->getRoom()->getName()} ถูกจองแล้วในช่วงเวลาดังกล่าว กรุณาเลือกช่วงเวลาอื่นหรือเลือกห้องใหม่",
                'booking' => null,
            ];
        }

        // 4. Save and index
        $this->bookings[$booking->getBookingCode()] = $booking;
        $saved = $this->saveBookings();

        if (!$saved) {
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง',
                'booking' => null,
            ];
        }

        return [
            'success' => true,
            'message' => 'ยืนยันการจองเรียบร้อยแล้ว!',
            'booking' => $booking->toArray(),
        ];
    }

    /**
     * Cancel an existing booking by booking code.
     */
    public function cancelBooking(string $bookingCode): array
    {
        $code = strtoupper(trim($bookingCode));
        if (!isset($this->bookings[$code])) {
            return [
                'success' => false,
                'message' => "ไม่พบรหัสการจอง {$code} ในระบบ",
            ];
        }

        $booking = $this->bookings[$code];
        if ($booking->getStatus() === 'CANCELLED') {
            return [
                'success' => false,
                'message' => "รายการจอง {$code} ถูกยกเลิกไปก่อนหน้านี้แล้ว",
            ];
        }

        $booking->cancelBooking();
        $this->saveBookings();

        return [
            'success' => true,
            'message' => "ยกเลิกรายการจอง {$code} สำเร็จ",
            'booking' => $booking->toArray(),
        ];
    }

    /**
     * Lookup a booking by its reference code.
     */
    public function getBookingByCode(string $bookingCode): ?Booking
    {
        $code = strtoupper(trim($bookingCode));
        return $this->bookings[$code] ?? null;
    }

    /**
     * Search bookings by customer name, phone, email, or booking code.
     * @return Booking[]
     */
    public function searchBookings(string $keyword): array
    {
        $keyword = mb_strtolower(trim($keyword));
        if ($keyword === '') return [];

        $results = [];
        foreach ($this->bookings as $booking) {
            $match = (
                str_contains(mb_strtolower($booking->getBookingCode()), $keyword) ||
                str_contains(mb_strtolower($booking->getCustomerName()), $keyword) ||
                str_contains(mb_strtolower($booking->getCustomerPhone()), $keyword) ||
                str_contains(mb_strtolower($booking->getCustomerEmail()), $keyword) ||
                str_contains(mb_strtolower($booking->getOrganization()), $keyword)
            );

            if ($match) {
                $results[] = $booking;
            }
        }

        return $results;
    }

    /**
     * Retrieve all bookings with optional filters.
     * @return array
     */
    public function getAllBookings(array $filters = []): array
    {
        $list = [];
        $filterDate = $filters['date'] ?? null;
        $filterStatus = isset($filters['status']) ? strtoupper($filters['status']) : null;
        $filterRoomId = $filters['roomId'] ?? null;

        foreach ($this->bookings as $booking) {
            if ($filterDate && $booking->getBookingDate() !== $filterDate) continue;
            if ($filterStatus && $booking->getStatus() !== $filterStatus) continue;
            if ($filterRoomId && $booking->getRoom()->getId() !== $filterRoomId) continue;

            $list[] = $booking->toArray();
        }

        // Sort by bookingDate DESC, startTime ASC
        usort($list, function ($a, $b) {
            $cmpDate = strcmp($b['bookingDate'], $a['bookingDate']);
            if ($cmpDate !== 0) return $cmpDate;
            return strcmp($a['startTime'], $b['startTime']);
        });

        return $list;
    }

    /**
     * Get all rooms.
     * @return array
     */
    public function getAllRooms(): array
    {
        $list = [];
        foreach ($this->rooms as $room) {
            $list[] = $room->toArray();
        }
        return $list;
    }

    public function getRoomById(string $roomId): ?Room
    {
        return $this->rooms[$roomId] ?? null;
    }

    /**
     * Calculate analytical statistics for dashboard overview.
     */
    public function getStatistics(): array
    {
        $totalBookings = count($this->bookings);
        $confirmedCount = 0;
        $cancelledCount = 0;
        $totalRevenue = 0.0;
        $todayBookings = 0;
        $today = date('Y-m-d');
        $roomBookingsCount = [];

        foreach ($this->bookings as $booking) {
            $roomId = $booking->getRoom()->getId();
            $roomName = $booking->getRoom()->getName();

            if (!isset($roomBookingsCount[$roomName])) {
                $roomBookingsCount[$roomName] = 0;
            }

            if ($booking->getStatus() === 'CONFIRMED') {
                $confirmedCount++;
                $totalRevenue += $booking->getTotalAmount();
                $roomBookingsCount[$roomName]++;

                if ($booking->getBookingDate() === $today) {
                    $todayBookings++;
                }
            } elseif ($booking->getStatus() === 'CANCELLED') {
                $cancelledCount++;
            }
        }

        // Find most popular room
        arsort($roomBookingsCount);
        $popularRoom = !empty($roomBookingsCount) ? array_key_first($roomBookingsCount) : 'ยังไม่มีข้อมูล';

        return [
            'totalBookings' => $totalBookings,
            'confirmedBookings' => $confirmedCount,
            'cancelledBookings' => $cancelledCount,
            'todayBookings' => $todayBookings,
            'totalRevenue' => round($totalRevenue, 2),
            'totalRooms' => count($this->rooms),
            'popularRoom' => $popularRoom,
            'roomUsage' => $roomBookingsCount,
        ];
    }
}
