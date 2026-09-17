<?php
declare(strict_types=1);

/**
 * ==============================================================================
 * คลาส Room (คลาสทรัพยากรห้องประชุม / พื้นที่ใช้งาน - Room Entity Class)
 * ==============================================================================
 * คลาสที่ 1: รับผิดชอบการพัฒนาโดย สมาชิกคนที่ 1 (รหัส 035)
 * คำอธิบาย: คลาสนี้ทำหน้าที่เป็น Domain Model ตัวแทนของ "ห้องประชุม/พื้นที่ทำงาน"
 * ในระบบ ตามหลักการสืบทอดและแคปซูลข้อมูล (OOP Encapsulation & Domain Modeling)
 * ==============================================================================
 */
class Room
{
    // --- 1. การกำหนดคุณลักษณะประจำวัตถุ (Private Encapsulated Properties) ---
    private string $id;           // 1.1 รหัสประจำห้องประชุม (เช่น RM-101)
    private string $name;         // 1.2 ชื่อของห้องประชุม (เช่น Starlight Innovation Lab)
    private string $type;         // 1.3 ประเภทห้อง (เช่น Meeting Room, Boardroom, Focus Pod)
    private int $capacity;        // 1.4 ความจุจำนวนผู้เข้าร่วมสูงสุด (คน)
    private float $hourlyRate;    // 1.5 อัตราค่าเช่าบริการต่อหนึ่งชั่วโมง (บาท)
    private array $amenities;     // 1.6 รายการสิ่งอำนวยความสะดวก (เช่น โปรเจคเตอร์, Wi-Fi 6)
    private string $imageUrl;     // 1.7 ที่อยู่อินเทอร์เน็ตของรูปภาพประกอบห้องประชุม
    private string $description;  // 1.8 คำอธิบายรายละเอียดเพิ่มเติมของห้อง
    private int $minHours;        // 1.9 จำนวนชั่วโมงการจองขั้นต่ำ (ขั้นต่ำเริ่มต้น 1 ชั่วโมง)

    /**
     * --- 2. คอนสตรัคเตอร์ (Constructor Method) ---
     * ฟังก์ชันพิเศษที่ถูกเรียกใช้อัตโนมัติเมื่อมีการสร้างวัตถุ (new Room)
     * ทำหน้าที่กำหนดค่าเริ่มต้นให้แก่คุณลักษณะทั้งหมดของห้องประชุม
     */
    public function __construct(
        string $id,
        string $name,
        string $type,
        int $capacity,
        float $hourlyRate,
        array $amenities = [],
        string $imageUrl = '',
        string $description = '',
        int $minHours = 1
    ) {
        $this->id = $id;                                                                 // กำหนดรหัสห้อง
        $this->name = trim($name);                                                       // กำหนดชื่อห้อง ตัดช่องว่าง
        $this->type = trim($type);                                                       // กำหนดประเภทห้อง
        $this->capacity = max(1, $capacity);                                             // กำหนดความจุขั้นต่ำ 1 คน
        $this->hourlyRate = max(0.0, $hourlyRate);                                       // กำหนดอัตราค่าบริการต่อชั่วโมง
        $this->amenities = $amenities;                                                   // กำหนดสิ่งอำนวยความสะดวก
        $this->imageUrl = $imageUrl ?: 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=600'; // รูปภาพ
        $this->description = trim($description);                                         // กำหนดคำอธิบายห้อง
        $this->minHours = max(1, $minHours);                                             // กำหนดชั่วโมงจองขั้นต่ำ
    }

    // --- 3. ฟังก์ชันการเข้าถึงข้อมูล (Encapsulation Getter Methods) ---
    // รับผิดชอบโดย รหัส 035: ป้องกันไม่ให้โค้ดภายนอกแก้ไขค่าตัวแปรโดยตรง

    public function getId(): string
    {
        return $this->id;                                                                // คืนค่ารหัสประจำห้อง
    }

    public function getName(): string
    {
        return $this->name;                                                              // คืนค่าชื่อห้องประชุม
    }

    public function getType(): string
    {
        return $this->type;                                                              // คืนค่าประเภทของห้อง
    }

    public function getCapacity(): int
    {
        return $this->capacity;                                                          // คืนค่าความจุสูงสุด (คน)
    }

    public function getHourlyRate(): float
    {
        return $this->hourlyRate;                                                        // คืนค่าราคาเช่าต่อชั่วโมง (บาท)
    }

    public function getAmenities(): array
    {
        return $this->amenities;                                                         // คืนค่ารายการอุปกรณ์สิ่งอำนวยความสะดวก
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;                                                          // คืนค่า URL รูปภาพประจำห้อง
    }

    public function getDescription(): string
    {
        return $this->description;                                                       // คืนค่าคำอธิบายของห้อง
    }

    public function getMinHours(): int
    {
        return $this->minHours;                                                          // คืนค่าจำนวนชั่วโมงจองขั้นต่ำ
    }

    // --- 4. ฟังก์ชันการคำนวณทางธุรกิจประจำคลาส (Business Logic Methods) ---
    // รับผิดชอบโดย รหัส 035

    /**
     * ฟังก์ชันคำนวณค่าเช่าห้องพื้นฐานตามจำนวนชั่วโมงการใช้งาน
     * 
     * @param float $hours จำนวนชั่วโมงที่ลูกค้าต้องการจอง
     * @return float ราคารวมค่าเช่าห้องพื้นฐาน (บาท)
     */
    public function calculateBasePrice(float $hours): float
    {
        // ตรวจสอบว่าชั่วโมงที่จองไม่น้อยกว่าจำนวนชั่วโมงขั้นต่ำของห้อง
        $billableHours = max((float)$this->minHours, $hours);
        
        // คำนวณราคา = จำนวนชั่วโมง x อัตราค่าบริการต่อชั่วโมง ปัดเศษ 2 ตำแหน่ง
        return round($billableHours * $this->hourlyRate, 2);
    }

    /**
     * ฟังก์ชันตรวจสอบว่าห้องประชุมนี้รองรับจำนวนผู้เข้าร่วมได้หรือไม่
     * 
     * @param int $attendees จำนวนผู้เข้าร่วมประชุมที่ต้องการจอง
     * @return bool คืนค่า true หากจำนวนผู้เข้าร่วมไม่เกินความจุของห้อง
     */
    public function isSuitableFor(int $attendees): bool
    {
        // ต้องมีผู้เข้าร่วมมากกว่า 0 คน และไม่เกินความจุห้อง (capacity)
        return $attendees > 0 && $attendees <= $this->capacity;
    }

    /**
     * ฟังก์ชันแปลงวัตถุ Room เป็น Array แบบ Associative สำหรับส่งผ่าน JSON API
     * 
     * @return array ข้อมูลห้องประชุมในรูปแบบ Array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,                                                           // รหัสห้อง
            'name' => $this->name,                                                       // ชื่อห้อง
            'type' => $this->type,                                                       // ประเภทห้อง
            'capacity' => $this->capacity,                                               // ความจุ
            'hourlyRate' => $this->hourlyRate,                                           // ราคาต่อชั่วโมง
            'amenities' => $this->amenities,                                             // อุปกรณ์
            'imageUrl' => $this->imageUrl,                                               // รูปภาพ
            'description' => $this->description,                                         // คำอธิบาย
            'minHours' => $this->minHours,                                               // ชั่วโมงขั้นต่ำ
        ];
    }

    /**
     * ฟังก์ชันแบบ Static Factory Method สำหรับสร้างวัตถุ Room จากข้อมูล Array
     * 
     * @param array $data ข้อมูลห้องในรูปแบบ Array
     * @return Room วัตถุ Room ใหม่ที่สร้างสำเร็จ
     */
    public static function fromArray(array $data): Room
    {
        return new self(
            $data['id'] ?? '',                                                           // ดึงค่า id
            $data['name'] ?? '',                                                         // ดึงค่า name
            $data['type'] ?? 'Standard Room',                                            // ดึงค่า type
            (int)($data['capacity'] ?? 1),                                               // ดึงค่า capacity
            (float)($data['hourlyRate'] ?? ($data['hourly_rate'] ?? 0.0)),              // ดึงค่า hourlyRate (รองรับทั้ง camelCase และ snake_case จาก MySQL)
            is_array($data['amenities'] ?? null) 
                ? $data['amenities'] 
                : (json_decode($data['amenities'] ?? '[]', true) ?? []),                 // ดึงค่า amenities
            $data['imageUrl'] ?? ($data['image_url'] ?? ''),                             // ดึงค่า imageUrl
            $data['description'] ?? '',                                                  // ดึงค่า description
            (int)($data['minHours'] ?? ($data['min_hours'] ?? 1))                        // ดึงค่า minHours
        );
    }
}
