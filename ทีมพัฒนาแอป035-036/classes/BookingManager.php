<?php
declare(strict_types=1);

require_once __DIR__ . '/Room.php';
require_once __DIR__ . '/Booking.php';
require_once __DIR__ . '/Database.php';

/**
 * ==============================================================================
 * คลาส BookingManager (คลาสระบบบริหารจัดการ การจองและการตรวจสอบเวลาชนกัน)
 * ==============================================================================
 * คลาสที่ 3: คลาสใช้งานร่วมกัน (Shared Controller Class โดย รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม)
 * คำอธิบาย: คลาสนี้เป็นศูนย์กลางในการควบคุมลอจิกการจอง การตรวจสอบเวลาซ้ำซ้อน (Conflict Detection)
 * การบันทึกข้อมูลลง MySQL / JSON และการคำนวณสถิติภาพรวมของระบบทั้งหมด
 * ==============================================================================
 */
class BookingManager
{
    private array $rooms = [];          // อาร์เรย์เก็บรายการวัตถุ Room ทั้งหมดในระบบ (รหัส 035)
    private array $bookings = [];       // อาร์เรย์เก็บรายการวัตถุ Booking ทั้งหมดในระบบ (รหัส 036 ศุภนัฐ จันทร์เปรม)
    private string $roomsFilePath;      // พาธไฟล์ JSON สำรองสำหรับห้อง
    private string $bookingsFilePath;   // พาธไฟล์ JSON สำรองสำหรับรายการจอง

    /**
     * --- 1. คอนสตรัคเตอร์ (Constructor Method) ---
     * พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
     * โหลดข้อมูลเริ่มต้นจาก MySQL หรือ JSON Fallback ทันทีเมื่อเรียกใช้งาน
     */
    public function __construct(
        string $roomsFilePath = __DIR__ . '/../data/rooms.json',
        string $bookingsFilePath = __DIR__ . '/../data/bookings.json'
    ) {
        $this->roomsFilePath = $roomsFilePath;
        $this->bookingsFilePath = $bookingsFilePath;

        // ดึงข้อมูลห้องประชุมและรายการจองทั้งหมดโหลดเข้าหน่วยความจำ
        $this->loadRooms();
        $this->loadBookings();
    }

    /**
     * --- 2. ฟังก์ชันโหลดข้อมูลห้องประชุม (Load Rooms Data) ---
     * พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม (เน้นโครงสร้างห้องโดย รหัส 035)
     * ดึงข้อมูลจากฐานข้อมูล MySQL PDO หรือ JSON File
     */
    public function loadRooms(): void
    {
        $this->rooms = [];
        $pdo = Database::getConnection();

        if ($pdo !== null) {
            try {
                // ดึงข้อมูลห้องประชุมทั้งหมดจากตาราง rooms ใน MySQL
                $stmt = $pdo->query("SELECT * FROM rooms ORDER BY id ASC");
                $dbRooms = $stmt->fetchAll();

                foreach ($dbRooms as $rData) {
                    $roomObj = Room::fromArray([
                        'id' => $rData['id'],
                        'name' => $rData['name'],
                        'type' => $rData['type'],
                        'capacity' => (int)$rData['capacity'],
                        'hourlyRate' => (float)$rData['hourly_rate'],
                        'amenities' => json_decode($rData['amenities'] ?? '[]', true) ?? [],
                        'imageUrl' => $rData['image_url'],
                        'description' => $rData['description'],
                        'minHours' => (int)$rData['min_hours']
                    ]);
                    $this->rooms[$roomObj->getId()] = $roomObj;
                }
                return;
            } catch (PDOException $e) {
                // หากดึง MySQL ล้มเหลว ให้ย้ายไปใช้ JSON Fallback
            }
        }

        // Fallback JSON Loading
        if (file_exists($this->roomsFilePath)) {
            $jsonContent = file_get_contents($this->roomsFilePath);
            $roomsData = json_decode($jsonContent, true) ?? [];
            foreach ($roomsData as $rData) {
                $roomObj = Room::fromArray($rData);
                $this->rooms[$roomObj->getId()] = $roomObj;
            }
        }
    }

    /**
     * --- 3. ฟังก์ชันโหลดรายการจองทั้งหมด (Load Bookings Data) ---
     * พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม (เน้นการจองโดย รหัส 036 ศุภนัฐ จันทร์เปรม)
     */
    public function loadBookings(): void
    {
        $this->bookings = [];
        $pdo = Database::getConnection();

        if ($pdo !== null) {
            try {
                // อัปเดตรายการจองทดสอบให้ตรงกับวันที่ปัจจุบันและรหัสห้องที่ถูกต้อง เพื่อให้ระบบแสดงห้องไม่ว่างอย่างสมจริง
                $todayDate = date('Y-m-d');
                try {
                    $pdo->exec("UPDATE bookings SET booking_date = '{$todayDate}', room_id = 'RM-101' WHERE booking_code = 'BK-2026-001'");
                    $pdo->exec("UPDATE bookings SET booking_date = '{$todayDate}', room_id = 'RM-102' WHERE booking_code = 'BK-2026-002'");
                    $pdo->exec("UPDATE bookings SET booking_date = '{$todayDate}', room_id = 'RM-201' WHERE booking_code = 'BK-2026-003'");
                    $pdo->exec("UPDATE bookings SET booking_date = '{$todayDate}', room_id = 'RM-301' WHERE booking_code = 'BK-2026-004'");
                    $pdo->exec("UPDATE bookings SET booking_date = '{$todayDate}', room_id = 'RM-501' WHERE booking_code = 'BK-2026-005'");
                } catch (Throwable $e) {
                    // Ignore minor update warnings
                }

                // ดึงรายการจองทั้งหมดจากตาราง bookings ใน MySQL
                $stmt = $pdo->query("SELECT * FROM bookings ORDER BY created_at DESC");
                $dbBookings = $stmt->fetchAll();

                foreach ($dbBookings as $bData) {
                    $roomId = $bData['room_id'];
                    if (strpos($roomId, 'RM-') !== 0 && strpos($roomId, 'R') === 0) {
                        $roomId = 'RM-' . substr($roomId, 1);
                    }
                    $roomObj = $this->getRoomById($roomId) ?? array_values($this->rooms)[0] ?? null;

                    if ($roomObj !== null) {
                        $addOns = json_decode($bData['add_ons_json'] ?? '[]', true) ?? [];
                        $bookingObj = new Booking(
                            $bData['booking_code'],
                            $bData['customer_name'],
                            $bData['customer_phone'],
                            $bData['customer_email'],
                            $bData['organization'] ?? '',
                            $bData['purpose'] ?? '',
                            $roomObj,
                            $bData['booking_date'],
                            $bData['start_time'],
                            $bData['end_time'],
                            (int)$bData['attendee_count'],
                            $addOns,
                            $bData['status'],
                            $bData['created_at'],
                            $bData['notes'] ?? ''
                        );
                        $this->bookings[$bookingObj->getBookingCode()] = $bookingObj;
                    }
                }
                return;
            } catch (PDOException $e) {
                // หากดึง MySQL ล้มเหลว ให้ย้ายไปใช้ JSON Fallback
            }
        }

        // Fallback JSON Loading
        if (file_exists($this->bookingsFilePath)) {
            $jsonContent = file_get_contents($this->bookingsFilePath);
            $bookingsData = json_decode($jsonContent, true) ?? [];
            foreach ($bookingsData as $bData) {
                $roomId = $bData['room']['id'] ?? '';
                $roomObj = $this->getRoomById($roomId) ?? Room::fromArray($bData['room'] ?? []);

                $bookingObj = new Booking(
                    $bData['bookingCode'] ?? '',
                    $bData['customerName'] ?? '',
                    $bData['customerPhone'] ?? '',
                    $bData['customerEmail'] ?? '',
                    $bData['organization'] ?? '',
                    $bData['purpose'] ?? '',
                    $roomObj,
                    $bData['bookingDate'] ?? '',
                    $bData['startTime'] ?? '',
                    $bData['endTime'] ?? '',
                    (int)($bData['attendeeCount'] ?? 1),
                    (array)($bData['addOnServices'] ?? []),
                    $bData['status'] ?? 'CONFIRMED',
                    $bData['createdAt'] ?? '',
                    $bData['notes'] ?? ''
                );
                $this->bookings[$bookingObj->getBookingCode()] = $bookingObj;
            }
        }
    }

    /**
     * --- 4. ฟังก์ชันตรวจสอบเวลาซ้ำซ้อนอย่างแม่นยำ (Time Overlap Validation Logic) ---
     * พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
     * 
     * @param string $roomId รหัสห้องประชุมที่ต้องการจอง
     * @param string $date วันที่จอง (YYYY-MM-DD)
     * @param string $startTime เวลาเริ่ม (HH:MM)
     * @param string $endTime เวลาจบ (HH:MM)
     * @param string|null $excludeCode รหัสการจองที่ต้องการยกเว้น (กรณีแก้ไข)
     * @return bool คืนค่า true หากเวลาชนกัน (Conflict) หรือ false หากเวลาว่าง
     */
    public function isTimeSlotConflicting(
        string $roomId,
        string $date,
        string $startTime,
        string $endTime,
        ?string $excludeCode = null
    ): bool {
        // แปลงเวลาเริ่มต้นและเวลาจบที่ผู้ใช้เลือกเป็น Timestamp
        $newStart = strtotime($date . ' ' . $startTime);
        $newEnd = strtotime($date . ' ' . $endTime);

        if (!$newStart || !$newEnd || $newEnd <= $newStart) {
            return true; // คืนค่า true ป้องกันหากรูปแบบเวลาผิดพลาด
        }

        // วนลูปตรวจสอบรายการจองที่มีอยู่ทั้งหมดในระบบ
        foreach ($this->bookings as $booking) {
            // ข้ามรายการที่ยกเลิกแล้ว (CANCELLED) หรือข้ามรายการเดิมกรณีแก้ไข
            if ($booking->getStatus() === 'CANCELLED') {
                continue;
            }
            if ($excludeCode !== null && $booking->getBookingCode() === $excludeCode) {
                continue;
            }

            // ตรวจสอบเฉพาะห้องและวันที่ตรงกันเท่านั้น (แปลงรูปแบบวันที่เป็น YYYY-MM-DD เพื่อความแม่นยำ 100%)
            $bDateStr = date('Y-m-d', strtotime($booking->getBookingDate()));
            $tDateStr = date('Y-m-d', strtotime($date));

            if ($booking->getRoom()->getId() === $roomId && $bDateStr === $tDateStr) {
                $existingStart = strtotime($tDateStr . ' ' . $booking->getStartTime());
                $existingEnd = strtotime($tDateStr . ' ' . $booking->getEndTime());

                // สูตรคณิตศาสตร์การชนกันของช่วงเวลา (Interval Overlap Condition):
                // ช่วงเวลา A และ B ซ้ำกันก็ต่อเมื่อ (StartA < EndB) AND (EndA > StartB)
                if ($newStart < $existingEnd && $newEnd > $existingStart) {
                    return true; // พบเวลาซ้ำซ้อนกัน!
                }
            }
        }

        return false; // เวลาว่าง สามารถจองได้
    }

    /**
     * --- 5. ฟังก์ชันเพิ่มรายการจองใหม่เข้าสู่ระบบ (Create Booking Transaction) ---
     * พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
     * 
     * @param array $data ข้อมูลที่ส่งมาจากฟอร์มการจอง
     * @return array [success => bool, message => string, booking => Booking|null]
     */
    public function createBooking(array $data): array
    {
        $roomId = trim($data['roomId'] ?? '');
        $customerName = trim($data['customerName'] ?? '');
        $customerPhone = trim($data['customerPhone'] ?? '');
        $customerEmail = trim($data['customerEmail'] ?? '');
        $bookingDate = trim($data['bookingDate'] ?? '');
        $startTime = trim($data['startTime'] ?? '');
        $endTime = trim($data['endTime'] ?? '');
        $attendeeCount = (int)($data['attendeeCount'] ?? 1);
        $organization = trim($data['organization'] ?? '');
        $purpose = trim($data['purpose'] ?? '');
        $addOnServices = (array)($data['addOnServices'] ?? []);
        $notes = trim($data['notes'] ?? '');

        // 5.1 ตรวจสอบข้อมูลบังคับให้ครบถ้วน (Validation)
        if (empty($roomId) || empty($customerName) || empty($customerPhone) || empty($bookingDate) || empty($startTime) || empty($endTime)) {
            return ['success' => false, 'message' => 'กรุณากรอกข้อมูลสำคัญให้ครบถ้วน'];
        }

        // 5.2 ตรวจสอบว่าพบห้องประชุมในระบบหรือไม่
        $roomObj = $this->getRoomById($roomId);
        if ($roomObj === null) {
            return ['success' => false, 'message' => 'ไม่พบห้องประชุมที่ระบุในระบบ'];
        }

        // 5.3 ตรวจสอบความจุห้องประชุม
        if (!$roomObj->isSuitableFor($attendeeCount)) {
            return ['success' => false, 'message' => "จำนวนผู้เข้าร่วม ({$attendeeCount} คน) เกินความจุสูงสุดของห้อง ({$roomObj->getCapacity()} คน)"];
        }

        // 5.4 ตรวจสอบเวลาซ้ำซ้อน
        if ($this->isTimeSlotConflicting($roomId, $bookingDate, $startTime, $endTime)) {
            return ['success' => false, 'message' => 'ช่วงเวลาและห้องประชุมที่เลือกถูกจองแล้ว กรุณาเลือกช่วงเวลาอื่น'];
        }

        // 5.5 สร้างวัตถุ Booking ใหม่ (พัฒนาโดย รหัส 036 ศุภนัฐ จันทร์เปรม)
        $newCode = Booking::generateCode();
        $bookingObj = new Booking(
            $newCode,
            $customerName,
            $customerPhone,
            $customerEmail,
            $organization,
            $purpose,
            $roomObj,
            $bookingDate,
            $startTime,
            $endTime,
            $attendeeCount,
            $addOnServices,
            'CONFIRMED',
            date('Y-m-d H:i:s'),
            $notes
        );

        // 5.6 บันทึกลง MySQL หรือ JSON Fallback
        $this->bookings[$newCode] = $bookingObj;
        $this->persistBookingToStorage($bookingObj);

        return [
            'success' => true,
            'message' => 'ทำรายการจองสำเร็จเรียบร้อยแล้ว',
            'booking' => $bookingObj
        ];
    }

    /**
     * --- 6. ฟังก์ชันบันทึกรายการจองลง MySQL หรือ JSON Fallback ---
     * พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
     */
    private function persistBookingToStorage(Booking $booking): void
    {
        $pdo = Database::getConnection();

        if ($pdo !== null) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (
                        booking_code, customer_name, customer_phone, customer_email,
                        organization, purpose, room_id, booking_date, start_time, end_time,
                        attendee_count, add_ons_json, status, duration_hours, base_amount,
                        add_ons_amount, tax_amount, total_amount, created_at, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
                ");
                $stmt->execute([
                    $booking->getBookingCode(),
                    $booking->getCustomerName(),
                    $booking->getCustomerPhone(),
                    $booking->getCustomerEmail(),
                    $booking->getOrganization(),
                    $booking->getPurpose(),
                    $booking->getRoom()->getId(),
                    $booking->getBookingDate(),
                    $booking->getStartTime(),
                    $booking->getEndTime(),
                    $booking->getAttendeeCount(),
                    json_encode($booking->getAddOnServices(), JSON_UNESCAPED_UNICODE),
                    $booking->getStatus(),
                    $booking->getDurationHours(),
                    $booking->getBaseAmount(),
                    $booking->getAddOnsAmount(),
                    $booking->getTaxAmount(),
                    $booking->getTotalAmount(),
                    $booking->getNotes()
                ]);
            } catch (PDOException $e) {
                // บันทึกลง JSON แทนหาก MySQL เกิดข้อผิดพลาด
                $this->saveBookingsToJson();
            }
        } else {
            $this->saveBookingsToJson();
        }
    }

    /**
     * บันทึกข้อมูลรายการจองทั้งหมดลงไฟล์ JSON
     */
    private function saveBookingsToJson(): void
    {
        $data = [];
        foreach ($this->bookings as $b) {
            $data[] = $b->toArray();
        }
        @file_put_contents($this->bookingsFilePath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * --- 7. ฟังก์ชันยกเลิกรายการจองในระบบ (Cancel Booking) ---
     * พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
     */
    public function cancelBooking(string $bookingCode): bool
    {
        return $this->updateBookingStatus($bookingCode, 'CANCELLED');
    }

    /**
     * --- 8. ฟังก์ชันอัปเดตสถานะการจอง (สำหรับแอดมินหลังบ้าน Admin Panel) ---
     * พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
     * 
     * @param string $bookingCode รหัสการจอง
     * @param string $newStatus สถานะใหม่ ('CONFIRMED', 'CANCELLED', 'COMPLETED', 'PENDING')
     * @return bool คืนค่า true หากอัปเดตสำเร็จ
     */
    public function updateBookingStatus(string $bookingCode, string $newStatus): bool
    {
        $validStatuses = ['CONFIRMED', 'CANCELLED', 'COMPLETED', 'PENDING'];
        $newStatus = strtoupper($newStatus);

        if (!in_array($newStatus, $validStatuses)) {
            return false;
        }

        if (isset($this->bookings[$bookingCode])) {
            $this->bookings[$bookingCode]->setStatus($newStatus);

            $pdo = Database::getConnection();
            if ($pdo !== null) {
                try {
                    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE booking_code = ?");
                    $stmt->execute([$newStatus, $bookingCode]);
                } catch (PDOException $e) {
                    $this->saveBookingsToJson();
                }
            } else {
                $this->saveBookingsToJson();
            }
            return true;
        }
        return false;
    }

    /**
     * --- 9. ฟังก์ชันลบรายการจองออกจากระบบ (Admin Delete Booking) ---
     * พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
     */
    public function deleteBooking(string $bookingCode): bool
    {
        if (isset($this->bookings[$bookingCode])) {
            unset($this->bookings[$bookingCode]);

            $pdo = Database::getConnection();
            if ($pdo !== null) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM bookings WHERE booking_code = ?");
                    $stmt->execute([$bookingCode]);
                } catch (PDOException $e) {
                    $this->saveBookingsToJson();
                }
            } else {
                $this->saveBookingsToJson();
            }
            return true;
        }
        return false;
    }

    // --- Getter Utility Methods ---
    // รับผิดชอบโดย รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม

    public function getRoomById(string $id): ?Room
    {
        return $this->rooms[$id] ?? null;
    }

    public function getAllRooms(): array
    {
        return array_values($this->rooms);
    }

    /**
     * ค้นหาห้องประชุมที่ว่างสำหรับช่วงเวลา และรองรับจำนวนผู้เข้าร่วมที่กำหนด
     */
    public function getAvailableRooms(string $date, string $startTime, string $endTime, int $attendees = 1): array
    {
        $available = [];
        foreach ($this->rooms as $room) {
            if ($room->isSuitableFor($attendees) && !$this->isTimeSlotConflicting($room->getId(), $date, $startTime, $endTime)) {
                $available[] = $room;
            }
        }
        return $available;
    }

    public function getAllBookings(): array
    {
        return array_values($this->bookings);
    }

    public function getBookingByCode(string $code): ?Booking
    {
        return $this->bookings[$code] ?? null;
    }

    /**
     * --- 10. ฟังก์ชันคำนวณสถิติภาพรวมระบบ (System Dashboard Statistics) ---
     * พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
     */
    public function getOverviewStatistics(): array
    {
        $totalBookings = count($this->bookings);
        $totalRevenue = 0.0;
        $confirmedCount = 0;
        $cancelledCount = 0;
        $pendingCount = 0;
        $completedCount = 0;

        foreach ($this->bookings as $b) {
            $status = $b->getStatus();
            if ($status === 'CONFIRMED' || $status === 'COMPLETED') {
                if ($status === 'CONFIRMED') $confirmedCount++;
                if ($status === 'COMPLETED') $completedCount++;
                $totalRevenue += $b->getTotalAmount();
            } elseif ($status === 'CANCELLED') {
                $cancelledCount++;
            } elseif ($status === 'PENDING') {
                $pendingCount++;
            }
        }

        return [
            'totalRooms' => count($this->rooms),
            'totalBookings' => $totalBookings,
            'confirmedBookings' => $confirmedCount,
            'pendingBookings' => $pendingCount,
            'completedBookings' => $completedCount,
            'cancelledBookings' => $cancelledCount,
            'totalRevenue' => round($totalRevenue, 2),
            'isDatabaseConnected' => Database::isConnected()
        ];
    }
}
