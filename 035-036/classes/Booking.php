<?php
declare(strict_types=1);

require_once __DIR__ . '/Room.php';

/**
 * Class Booking
 * Representing a customer reservation transaction.
 * Demonstrates OOP Encapsulation, State Management, and Financial Business Logic.
 */
class Booking
{
    private string $bookingCode;
    private string $customerName;
    private string $customerPhone;
    private string $customerEmail;
    private string $organization;
    private string $purpose;
    private Room $room;
    private string $bookingDate;     // Format: YYYY-MM-DD
    private string $startTime;       // Format: HH:MM
    private string $endTime;         // Format: HH:MM
    private int $attendeeCount;
    private array $addOnServices;    // Array of ['id', 'name', 'price', 'quantity']
    private string $status;          // 'CONFIRMED', 'CANCELLED', 'COMPLETED'
    private string $createdAt;
    private string $notes;

    // Financial breakdown
    private float $durationHours = 0.0;
    private float $baseAmount = 0.0;
    private float $addOnsAmount = 0.0;
    private float $taxAmount = 0.0;      // 7% VAT
    private float $totalAmount = 0.0;

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
        $this->bookingCode = $bookingCode ?: self::generateCode();
        $this->customerName = trim($customerName);
        $this->customerPhone = trim($customerPhone);
        $this->customerEmail = trim($customerEmail);
        $this->organization = trim($organization) ?: 'บุคคลทั่วไป (General Guest)';
        $this->purpose = trim($purpose) ?: 'การประชุม / ทำงานกลุ่ม (Meeting & Work)';
        $this->room = $room;
        $this->bookingDate = $bookingDate;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->attendeeCount = max(1, $attendeeCount);
        $this->addOnServices = $addOnServices;
        $this->status = in_array(strtoupper($status), ['CONFIRMED', 'CANCELLED', 'COMPLETED']) 
            ? strtoupper($status) 
            : 'CONFIRMED';
        $this->createdAt = $createdAt ?: date('Y-m-d H:i:s');
        $this->notes = $notes;

        $this->recalculateFinancials();
    }

    /**
     * Generate unique human-readable booking code e.g. BK-2026-8942
     */
    public static function generateCode(): string
    {
        return 'BK-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
    }

    /**
     * Calculate duration in hours between startTime and endTime.
     */
    public function calculateDurationHours(): float
    {
        $start = strtotime($this->bookingDate . ' ' . $this->startTime);
        $end = strtotime($this->bookingDate . ' ' . $this->endTime);

        if (!$start || !$end || $end <= $start) {
            return 0.0;
        }

        $diffMinutes = ($end - $start) / 60;
        return round($diffMinutes / 60, 2);
    }

    /**
     * Calculate financial totals (Base room charge + Add-ons + VAT).
     */
    public function recalculateFinancials(): void
    {
        $this->durationHours = $this->calculateDurationHours();
        
        // Base room cost
        $this->baseAmount = $this->room->calculateBasePrice($this->durationHours);

        // Add-ons cost
        $this->addOnsAmount = 0.0;
        foreach ($this->addOnServices as $service) {
            $price = (float)($service['price'] ?? 0);
            $qty = (int)($service['quantity'] ?? 1);
            $this->addOnsAmount += ($price * $qty);
        }

        // Subtotal + 7% Tax
        $subtotal = $this->baseAmount + $this->addOnsAmount;
        $this->taxAmount = round($subtotal * 0.07, 2);
        $this->totalAmount = round($subtotal + $this->taxAmount, 2);
    }

    /**
     * Validate booking time slot constraints.
     * @return array [ 'valid' => bool, 'errors' => string[] ]
     */
    public function validateTimeSlot(): array
    {
        $errors = [];

        // Check date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->bookingDate)) {
            $errors[] = 'รูปแบบวันที่ไม่ถูกต้อง (ต้องเป็น YYYY-MM-DD)';
        } elseif ($this->bookingDate < date('Y-m-d')) {
            $errors[] = 'ไม่สามารถจองวันในอดีตได้';
        }

        // Check time order
        if ($this->calculateDurationHours() <= 0) {
            $errors[] = 'เวลาสิ้นสุดต้องมากกว่าเวลาเริ่มต้นอย่างน้อย 30 นาที';
        }

        // Check operating hours (e.g. 08:00 - 21:00)
        $startStamp = strtotime($this->startTime);
        $endStamp = strtotime($this->endTime);
        $openStamp = strtotime('08:00');
        $closeStamp = strtotime('21:00');

        if ($startStamp < $openStamp || $endStamp > $closeStamp) {
            $errors[] = 'เวลาเปิดให้บริการของพื้นที่คือ 08:00 น. ถึง 21:00 น.';
        }

        // Check attendees vs room capacity
        if ($this->attendeeCount > $this->room->getCapacity()) {
            $errors[] = "จำนวนผู้เข้าร่วม ({$this->attendeeCount} คน) เกินความจุของห้อง {$this->room->getName()} (รองรับได้สูงสุด {$this->room->getCapacity()} คน)";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Cancel this booking reservation.
     */
    public function cancelBooking(): void
    {
        $this->status = 'CANCELLED';
    }

    /**
     * Confirm this booking reservation.
     */
    public function confirmBooking(): void
    {
        $this->status = 'CONFIRMED';
    }

    // --- Getters ---
    public function getBookingCode(): string { return $this->bookingCode; }
    public function getCustomerName(): string { return $this->customerName; }
    public function getCustomerPhone(): string { return $this->customerPhone; }
    public function getCustomerEmail(): string { return $this->customerEmail; }
    public function getOrganization(): string { return $this->organization; }
    public function getPurpose(): string { return $this->purpose; }
    public function getRoom(): Room { return $this->room; }
    public function getBookingDate(): string { return $this->bookingDate; }
    public function getStartTime(): string { return $this->startTime; }
    public function getEndTime(): string { return $this->endTime; }
    public function getAttendeeCount(): int { return $this->attendeeCount; }
    public function getAddOnServices(): array { return $this->addOnServices; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getNotes(): string { return $this->notes; }
    public function getDurationHours(): float { return $this->durationHours; }
    public function getBaseAmount(): float { return $this->baseAmount; }
    public function getAddOnsAmount(): float { return $this->addOnsAmount; }
    public function getTaxAmount(): float { return $this->taxAmount; }
    public function getTotalAmount(): float { return $this->totalAmount; }

    /**
     * Convert Booking object to array for JSON storage or API response.
     */
    public function toArray(): array
    {
        return [
            'bookingCode' => $this->bookingCode,
            'customerName' => $this->customerName,
            'customerPhone' => $this->customerPhone,
            'customerEmail' => $this->customerEmail,
            'organization' => $this->organization,
            'purpose' => $this->purpose,
            'roomId' => $this->room->getId(),
            'roomName' => $this->room->getName(),
            'roomType' => $this->room->getType(),
            'roomRate' => $this->room->getHourlyRate(),
            'bookingDate' => $this->bookingDate,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'durationHours' => $this->durationHours,
            'attendeeCount' => $this->attendeeCount,
            'addOnServices' => $this->addOnServices,
            'status' => $this->status,
            'createdAt' => $this->createdAt,
            'notes' => $this->notes,
            'baseAmount' => $this->baseAmount,
            'addOnsAmount' => $this->addOnsAmount,
            'taxAmount' => $this->taxAmount,
            'totalAmount' => $this->totalAmount,
        ];
    }

    /**
     * Factory method to reconstruct Booking from array with Room dependency injection.
     */
    public static function fromArray(array $data, Room $room): Booking
    {
        $booking = new self(
            $data['bookingCode'] ?? '',
            $data['customerName'] ?? '',
            $data['customerPhone'] ?? '',
            $data['customerEmail'] ?? '',
            $data['organization'] ?? '',
            $data['purpose'] ?? '',
            $room,
            $data['bookingDate'] ?? date('Y-m-d'),
            $data['startTime'] ?? '09:00',
            $data['endTime'] ?? '10:00',
            (int)($data['attendeeCount'] ?? 1),
            (array)($data['addOnServices'] ?? []),
            $data['status'] ?? 'CONFIRMED',
            $data['createdAt'] ?? '',
            $data['notes'] ?? ''
        );

        return $booking;
    }
}
