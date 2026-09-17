<?php
declare(strict_types=1);

require_once __DIR__ . '/Room.php';

/**
 * ==============================================================================
 * คลาส Booking (คลาสรายการธุรกรรมการจองและการคำนวณการเงิน - Booking Transaction Class)
 * ==============================================================================
 * คลาสที่ 2: รับผิดชอบการพัฒนาโดย สมาชิกคนที่ 2 (รหัส 036 นายศุภนัฐ จันทร์เปรม)
 * คำอธิบาย: คลาสนี้ทำหน้าที่เป็น Transaction Domain Model สำหรับจัดการธุรกรรมการจองห้อง
 * การคำนวณระยะเวลาใช้งาน บริการเสริม ภาษีมูลค่าเพิ่ม (VAT 7%) และการออกใบเสร็จรับเงิน
 * ==============================================================================
 */
class Booking
{
    // --- 1. คุณลักษณะประจำธุรกรรมการจอง (Private Encapsulated Properties) ---
    // พัฒนาและออกแบบโดย: รหัส 036 นายศุภนัฐ จันทร์เปรม

    private string $bookingCode;    // 1.1 รหัสธุรกรรมการจองที่เป็นเอกลักษณ์ (เช่น BK-2026-A89B)
    private string $customerName;   // 1.2 ชื่อ-นามสกุล ของผู้ทำการจอง
    private string $customerPhone;  // 1.3 เบอร์โทรศัพท์ติดต่อของผู้จอง
    private string $customerEmail;  // 1.4 อีเมลติดต่อของผู้จอง
    private string $organization;   // 1.5 ชื่อองค์กร/บริษัท/หน่วยงาน (ถ้ามี)
    private string $purpose;        // 1.6 วัตถุประสงค์ในการเข้าใช้งานห้องประชุม
    private Room $room;             // 1.7 วัตถุ Room ของห้องประชุมที่เลือกจอง (Aggregation/Association)
    private string $bookingDate;    // 1.8 วันที่ต้องการเข้าใช้งาน (รูปแบบ: YYYY-MM-DD)
    private string $startTime;      // 1.9 เวลาเริ่มต้นการจอง (รูปแบบ: HH:MM)
    private string $endTime;        // 1.10 เวลาสิ้นสุดการจอง (รูปแบบ: HH:MM)
    private int $attendeeCount;     // 1.11 จำนวนผู้เข้าร่วมประชุมจริง (คน)
    private array $addOnServices;   // 1.12 อาร์เรย์รายการบริการเสริมที่เลือก (Add-on Services)
    private string $status;         // 1.13 สถานะการจอง ('CONFIRMED', 'CANCELLED', 'COMPLETED')
    private string $createdAt;      // 1.14 วันเวลาที่ทำรายการจองบันทึกลงระบบ
    private string $notes;          // 1.15 หมายเหตุเพิ่มเติมจากผู้ใช้

    // --- 2. ตัวแปรสำหรับการคำนวณตัวเลขทางการเงิน (Financial Breakdown Properties) ---
    // คำนวณและประมวลผลโดย: รหัส 036 นายศุภนัฐ จันทร์เปรม

    private float $durationHours = 0.0; // 2.1 จำนวนชั่วโมงรวมที่คำนวณได้
    private float $baseAmount = 0.0;    // 2.2 ยอดเงินค่าเช่าห้องพื้นฐาน (บาท)
    private float $addOnsAmount = 0.0;  // 2.3 ยอดเงินค่าบริการเสริมรวม (บาท)
    private float $taxAmount = 0.0;     // 2.4 ยอดภาษีมูลค่าเพิ่ม VAT 7% (บาท)
    private float $totalAmount = 0.0;   // 2.5 ยอดเงินสุทธิรวมทั้งสิ้น (บาท)

    /**
     * --- 3. คอนสตรัคเตอร์ (Constructor Method) ---
     * รับผิดชอบโดย: รหัส 036 นายศุภนัฐ จันทร์เปรม
     * กำหนดค่าเริ่มต้นให้กับวัตถุการจอง พร้อมเรียกใช้ระบบคำนวณทางการเงินอัตโนมัติ
     */
    public function __construct(
        string $bookingCode,
        string $customerName,
        string $customerPhone,
        string $customerEmail,
        string $organization,
        string $purpose,
        Room $room,
        string $bookingDate,
        string $startTime,
        string $endTime,
        int $attendeeCount = 1,
        array $addOnServices = [],
        string $status = 'CONFIRMED',
        string $createdAt = '',
        string $notes = ''
    ) {
        $this->bookingCode = $bookingCode ?: self::generateCode();                        // สร้างรหัสจองหากไม่มีการระบุ
        $this->customerName = trim($customerName);                                        // กำหนดชื่อลูกค้า
        $this->customerPhone = trim($customerPhone);                                      // กำหนดเบอร์โทร
        $this->customerEmail = trim($customerEmail);                                      // กำหนดอีเมล
        $this->organization = trim($organization) ?: 'บุคคลทั่วไป (General Guest)';         // กำหนดชื่อองค์กร
        $this->purpose = trim($purpose) ?: 'การประชุม / ทำงานกลุ่ม (Meeting & Work)';      // กำหนดวัตถุประสงค์
        $this->room = $room;                                                              // กำหนดวัตถุห้องประชุม
        $this->bookingDate = $bookingDate;                                                // กำหนดวันที่จอง
        $this->startTime = $startTime;                                                    // กำหนดเวลาเริ่ม
        $this->endTime = $endTime;                                                        // กำหนดเวลาจบ
        $this->attendeeCount = max(1, $attendeeCount);                                    // กำหนดจำนวนคนเข้าประชุม
        $this->addOnServices = $addOnServices;                                            // กำหนดบริการเสริม
        $this->status = in_array(strtoupper($status), ['CONFIRMED', 'CANCELLED', 'COMPLETED', 'PENDING']) 
            ? strtoupper($status) 
            : 'CONFIRMED';                                                               // กำหนดสถานะการจอง (รองรับ PENDING ด้วย)
        $this->createdAt = $createdAt ?: date('Y-m-d H:i:s');                            // กำหนดวันเวลาสร้าง
        $this->notes = trim($notes);                                                      // กำหนดหมายเหตุ

        // คำนวณสรุปยอดเงินทางการเงินทันทีเมื่อสร้างวัตถุ (Calculated Automatically)
        $this->recalculateFinancials();
    }

    /**
     * --- 4. ฟังก์ชันสุ่มสร้างรหัสการจองที่เป็นเอกลักษณ์ (Unique Code Generator) ---
     * ออกแบบโดย: รหัส 036 นายศุภนัฐ จันทร์เปรม
     * 
     * @return string รหัสการจอง เช่น BK-2026-9F2A
     */
    public static function generateCode(): string
    {
        // ใช้ฟังก์ชัน bin2hex และ random_bytes สร้างรหัสผ่านแบบสุ่มที่ไม่ซ้ำกัน
        return 'BK-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    }

    /**
     * --- 5. ฟังก์ชันคำนวณระยะเวลาการใช้งานเป็นชั่วโมง (Duration Calculation) ---
     * ออกแบบโดย: รหัส 036 นายศุภนัฐ จันทร์เปรม
     * 
     * @return float จำนวนชั่วโมงจริง (เช่น 2.5 ชั่วโมง)
     */
    public function calculateDurationHours(): float
    {
        // แปลงข้อความวันเวลาเริ่มต้นและสิ้นสุดเป็น Timestamp สากล
        $start = strtotime($this->bookingDate . ' ' . $this->startTime);
        $end = strtotime($this->bookingDate . ' ' . $this->endTime);

        // หากรูปแบบเวลาไม่ถูกต้อง หรือเวลาจบอยู่ก่อนเวลาเริ่ม ให้คืนค่า 0 ชั่วโมง
        if (!$start || !$end || $end <= $start) {
            return 0.0;
        }

        // คำนวณผลต่างนาที แล้วหารด้วย 60 เพื่อแปลงเป็นชั่วโมง ปัดเศษ 2 ตำแหน่ง
        $diffMinutes = ($end - $start) / 60;
        return round($diffMinutes / 60, 2);
    }

    /**
     * --- 6. ฟังก์ชันคำนวณตัวเลขทางการเงินและภาษี (Financial Business Logic) ---
     * พัฒนาโดย: รหัส 036 นายศุภนัฐ จันทร์เปรม
     * คำนวณค่าเช่าห้อง + บริการเสริม + ภาษีมูลค่าเพิ่ม VAT 7%
     */
    public function recalculateFinancials(): void
    {
        // 6.1 คำนวณชั่วโมงการใช้งาน
        $this->durationHours = $this->calculateDurationHours();

        // 6.2 คำนวณค่าเช่าห้องพื้นฐานจากวัตถุ Room
        $this->baseAmount = $this->room->calculateBasePrice($this->durationHours);

        // 6.3 คำนวณค่าบริการเสริม (Add-on Services)
        $addOnRates = [
            'coffee' => 120.0,
            'projector_4k' => 350.0,
            'sound_eng' => 600.0,
            'whiteboard_kit' => 250.0
        ];

        $this->addOnsAmount = 0.0;
        foreach ($this->addOnServices as $service) {
            if (is_string($service)) {
                $id = $service;
                $unitPrice = $addOnRates[$id] ?? 0.0;
                $qty = ($id === 'coffee') ? max(1, $this->attendeeCount) : 1;
                $this->addOnsAmount += ($unitPrice * $qty);
            } elseif (is_array($service)) {
                $id = $service['id'] ?? '';
                $unitPrice = (float)($service['price'] ?? ($addOnRates[$id] ?? 0.0));
                $qty = (int)($service['quantity'] ?? (($id === 'coffee') ? max(1, $this->attendeeCount) : 1));
                $this->addOnsAmount += ($unitPrice * $qty);
            }
        }
        $this->addOnsAmount = round($this->addOnsAmount, 2);

        // 6.4 คำนวณภาษีมูลค่าเพิ่ม VAT 7% (7% ของ ยอดห้อง + ยอดบริการเสริม)
        $subtotal = $this->baseAmount + $this->addOnsAmount;
        $this->taxAmount = round($subtotal * 0.07, 2);

        // 6.5 คำนวณราคารวมสุทธิทั้งสิ้น (Total Amount)
        $this->totalAmount = round($subtotal + $this->taxAmount, 2);
    }

    // --- 7. Encapsulation Getter Methods (ฟังก์ชันเข้าถึงค่าตัวแปร) ---
    // รับผิดชอบโดย: รหัส 036 นายศุภนัฐ จันทร์เปรม

    public function getBookingCode(): string { return $this->bookingCode; }              // รหัสการจอง
    public function getCustomerName(): string { return $this->customerName; }            // ชื่อลูกค้า
    public function getCustomerPhone(): string { return $this->customerPhone; }          // เบอร์โทร
    public function getCustomerEmail(): string { return $this->customerEmail; }          // อีเมล
    public function getOrganization(): string { return $this->organization; }            // องค์กร
    public function getPurpose(): string { return $this->purpose; }                      // วัตถุประสงค์
    public function getRoom(): Room { return $this->room; }                              // วัตถุห้องประชุม
    public function getBookingDate(): string { return $this->bookingDate; }              // วันที่จอง
    public function getStartTime(): string { return $this->startTime; }                  // เวลาเริ่ม
    public function getEndTime(): string { return $this->endTime; }                      // เวลาจบ
    public function getAttendeeCount(): int { return $this->attendeeCount; }              // จำนวนคน
    public function getAddOnServices(): array { return $this->addOnServices; }            // บริการเสริม
    public function getStatus(): string { return $this->status; }                        // สถานะ
    public function getCreatedAt(): string { return $this->createdAt; }                  // วันเวลาสร้าง
    public function getNotes(): string { return $this->notes; }                          // หมายเหตุ

    public function getDurationHours(): float { return $this->durationHours; }           // จำนวนชั่วโมง
    public function getBaseAmount(): float { return $this->baseAmount; }                 // ค่าห้องพื้นฐาน
    public function getAddOnsAmount(): float { return $this->addOnsAmount; }             // ค่าบริการเสริม
    public function getTaxAmount(): float { return $this->taxAmount; }                   // ภาษี VAT 7%
    public function getTotalAmount(): float { return $this->totalAmount; }               // ราคารวมสุทธิ

    /**
     * เปลี่ยนสถานะธุรกรรมการจอง ('CONFIRMED', 'CANCELLED', 'COMPLETED')
     */
    public function setStatus(string $newStatus): void
    {
        $upper = strtoupper(trim($newStatus));
        // เพิ่ม PENDING เพื่อให้แอดมินสามารถตั้งสถานะรออนุมัติได้
        if (in_array($upper, ['CONFIRMED', 'CANCELLED', 'COMPLETED', 'PENDING'])) {
            $this->status = $upper;
        }
    }

    /**
     * --- 8. แปลงวัตถุ Booking เป็น อาร์เรย์สำหรับ JSON API ---
     * พัฒนาโดย: รหัส 036 นายศุภนัฐ จันทร์เปรม
     */
    public function toArray(): array
    {
        return [
            'bookingCode' => $this->bookingCode,                                         // รหัสการจอง
            'customerName' => $this->customerName,                                       // ชื่อลูกค้า
            'customerPhone' => $this->customerPhone,                                     // เบอร์โทร
            'customerEmail' => $this->customerEmail,                                     // อีเมล
            'organization' => $this->organization,                                       // องค์กร
            'purpose' => $this->purpose,                                                 // วัตถุประสงค์
            'room' => $this->room->toArray(),                                            // ข้อมูลห้องพัก (Array)
            'bookingDate' => $this->bookingDate,                                         // วันที่จอง
            'startTime' => $this->startTime,                                             // เวลาเริ่ม
            'endTime' => $this->endTime,                                                 // เวลาจบ
            'attendeeCount' => $this->attendeeCount,                                     // จำนวนผู้เข้าร่วม
            'addOnServices' => $this->addOnServices,                                     // บริการเสริม
            'status' => $this->status,                                                   // สถานะ
            'createdAt' => $this->createdAt,                                             // วันเวลาสร้าง
            'notes' => $this->notes,                                                     // หมายเหตุ
            'financials' => [
                'durationHours' => $this->durationHours,                                 // จำนวนชั่วโมง
                'baseAmount' => $this->baseAmount,                                       // ยอดค่าห้อง
                'addOnsAmount' => $this->addOnsAmount,                                   // ยอดบริการเสริม
                'taxAmount' => $this->taxAmount,                                         // ยอด VAT 7%
                'totalAmount' => $this->totalAmount,                                     // ยอดสุทธิ
            ]
        ];
    }
}
