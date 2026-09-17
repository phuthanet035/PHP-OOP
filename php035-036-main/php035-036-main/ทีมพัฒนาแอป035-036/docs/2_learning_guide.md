# เอกสารการสอนและการเรียนรู้เชิงปฏิบัติ (Learning & Educational Guide)
## หัวข้อ: การประยุกต์ใช้การวิเคราะห์และออกแบบเชิงวัตถุ (OOAD) ร่วมกับ OOP ในการพัฒนาระบบจริง
**ระบบอ้างอิง:** SpaceHub - CoSpace Booking System  
**เหมาะสำหรับ:** นิสิต/นักศึกษา และผู้เรียนรู้การเขียนโปรแกรมเชิงวัตถุ (Object-Oriented Programming)

---

## 1. ปูพื้นฐาน: ทำไมระบบการจองต้องใช้ OOP?

ในการเขียนโปรแกรมแบบเดิม (Procedural Programming) เรามักสร้างไฟล์สคริปต์ยาวๆ รวมการดึงข้อมูล ตรวจสอบเงื่อนไข และคำนวณราคาไว้ในที่เดียวกัน ส่งผลให้:
- หากต้องการเปลี่ยนกฎการคิดราคา หรือเปลี่ยนเงื่อนไขเวลา ต้องตามแก้โค้ดหลายจุด
- เสี่ยงต่อการเกิดข้อผิดพลาด เช่น ข้อมูลห้องและข้อมูลการจองปะปนกัน
- โค้ดยากต่อการนำไปเขียน Automated Unit Test

**การเปลี่ยนวิธีคิดเป็นเชิงวัตถุ (Object-Oriented Approach):**  
เรามองระบบเป็น **"สิ่งของหรือบุคคล (Objects)"** ในโลกความจริงที่มี **"คุณลักษณะ (Attributes)"** และ **"ความสามารถ (Methods)"** แล้วสร้างแม่แบบ (Class) ขึ้นมาควบคุม

---

## 2. เสาหลักของ OOP ที่นำมาใช้จริงในโปรเจกต์นี้

```
               +-----------------------------+
               | 4 เสาหลักของ OOP ใน SpaceHub  |
               +-----------------------------+
                              |
     +-----------------+------+-----------------+-----------------+
     |                 |                        |                 |
[Encapsulation]   [Abstraction]          [Inheritance /     [Polymorphism &
 การห่อหุ้มข้อมูล  การคัดย่อคุณลักษณะ       Decoupling]        Cohesion]
 ซ่อนสถานะผ่าน     แปลงห้อง/การจอง         แยก 3 บทบาท        เมธอดคำนวณและแปลง
 private fields   เป็นโมเดลเชิงวัตถุ      ออกจากกัน          array แบบยืดหยุ่น
```

### 2.1 Encapsulation (การห่อหุ้มและการซ่อนข้อมูล)
สังเกตในไฟล์ `classes/Room.php` และ `classes/Booking.php`:
```php
class Room
{
    private string $id;
    private string $name;
    private float $hourlyRate;
    ...
    public function getHourlyRate(): float
    {
        return $this->hourlyRate;
    }
}
```
**ทำไมต้องเป็น `private`?**: เพื่อป้องกันไม่ให้โค้ดภายนอกแก้ไขค่าอัตราค่าบริการโดยตรง เช่น `$room->hourlyRate = -100;` ซึ่งจะทำให้ระบบเสียหาย การเข้าถึงต้องผ่าน Getter และหากมีการกำหนดค่าใหม่ก็สามารถตรวจสอบความถูกต้อง (Validation) ก่อนได้

### 2.2 Abstraction & Domain Modeling
เราคัดย่อสิ่งจำเป็นของ "ห้องประชุม" ออกมาเป็นคุณสมบัติในคลาส `Room`:
- `id`, `name`, `type`, `capacity`, `hourlyRate`, `amenities`
สิ่งที่ไม่จำเป็นในระบบนี้ เช่น สีของกำแพง ยี่ห้อของหลอดไฟ ถูกคัดทิ้งไป

### 2.3 Single Responsibility Principle (SRP)
- **`Room`** มีหน้าที่ดูแลเรื่องห้องและคำนวณราคาฐานของตัวมันเอง
- **`Booking`** มีหน้าที่รวมรวมรายการจอง คำนวณภาษีและบริการเสริมของตัวเอง
- **`BookingManager`** ไม่ได้เก็บเงินเอง แต่ทำหน้าที่เป็น "ผู้จัดการระบบ" ตรวจสอบว่ามีห้องไหนว่างบ้าง บันทึกไฟล์ และตรวจการชนของเวลา

---

## 3. เจาะลึกอัลกอริทึมการตรวจจับเวลาชนกัน (Conflict Detection Algorithm)

ปัญหาคลาสสิกของระบบจองคือ **"การป้องกันไม่ให้มี 2 รายการจองในห้องเดียวกันและเวลาทับซ้อนกัน"**

### 3.1 การพิสูจน์ทางคณิตศาสตร์
สมมุติว่ามีรายการจองเดิมที่มีอยู่แล้ว ช่วงเวลาคือ $[\text{ExistStart}, \text{ExistEnd}]$  
และมีผู้ใช้ต้องการจองใหม่ในช่วงเวลา $[\text{ReqStart}, \text{ReqEnd}]$

ช่วงเวลาทั้งสองจะ **"ไม่ชนกัน (No Conflict)"** เมื่อ:
1. การจองใหม่เสร็จสิ้นก่อนที่การจองเดิมจะเริ่ม: $\text{ReqEnd} \le \text{ExistStart}$
2. หรือการจองใหม่เริ่มขึ้นหลังจากการจองเดิมสิ้นสุดลงแล้ว: $\text{ReqStart} \ge \text{ExistEnd}$

ดังนั้น เงื่อนไขที่ทำให้เกิด **"การชนกัน (Conflict / Collision)"** คือนิเสธ (Negation) ของเงื่อนไขไม่ชนกัน:
$$\text{Conflict} = \neg (\text{ReqEnd} \le \text{ExistStart} \lor \text{ReqStart} \ge \text{ExistEnd})$$

เมื่อใช้กฎเดอมอร์แกน (De Morgan's Laws):
$$\text{Conflict} = (\text{ReqStart} < \text{ExistEnd}) \land (\text{ReqEnd} > \text{ExistStart})$$

### 3.2 การนำไปเขียนโค้ดใน `BookingManager.php`
```php
public function hasTimeConflict(string $roomId, string $date, string $startTime, string $endTime): bool 
{
    $reqStart = strtotime($date . ' ' . $startTime);
    $reqEnd   = strtotime($date . ' ' . $endTime);

    foreach ($this->bookings as $booking) {
        // กรองเฉพาะห้องเดียวกัน, วันเดียวกัน, และสถานะที่ยังไม่ถูกยกเลิก
        if ($booking->getRoom()->getId() !== $roomId) continue;
        if ($booking->getBookingDate() !== $date) continue;
        if ($booking->getStatus() === 'CANCELLED') continue;

        $existStart = strtotime($booking->getBookingDate() . ' ' . $booking->getStartTime());
        $existEnd   = strtotime($booking->getBookingDate() . ' ' . $booking->getEndTime());

        // สูตรคณิตศาสตร์ช่วงเวลาชนกัน:
        if ($reqStart < $existEnd && $reqEnd > $existStart) {
            return true; // พบเวลาทับซ้อนทันที!
        }
    }
    return false; // ว่าง ปลอดภัย สามารถจองได้
}
```

---

## 4. เจาะลึกการทำงานของโค้ดทีละคลาส

### 4.1 คลาส `Room.php`
```php
public function calculateBasePrice(float $hours): float
{
    // ใช้ max() เพื่อบังคับระยะเวลาเช่าขั้นต่ำ
    $billableHours = max($this->minHours, $hours);
    return round($billableHours * $this->hourlyRate, 2);
}

public function isSuitableFor(int $attendees): bool
{
    // ตรวจสอบว่าความจุรองรับผู้ใช้งานได้หรือไม่
    return $attendees > 0 && $attendees <= $this->capacity;
}
```
**จุดที่น่าสังเกต**: เมธอด `isSuitableFor` ช่วยให้ภายนอกไม่ต้องเขียนเงื่อนไข `$room->capacity >= $attendees` ซ้ำๆ ทุกที่ แต่ถามตัวออบเจกต์ห้องโดยตรงตามหลัก "Tell, Don't Ask"

### 4.2 คลาส `Booking.php`
```php
public function recalculateFinancials(): void
{
    $this->durationHours = $this->calculateDurationHours();
    $this->baseAmount = $this->room->calculateBasePrice($this->durationHours);

    $this->addOnsAmount = 0.0;
    foreach ($this->addOnServices as $service) {
        $price = (float)($service['price'] ?? 0);
        $qty   = (int)($service['quantity'] ?? 1);
        $this->addOnsAmount += ($price * $qty);
    }

    $subtotal = $this->baseAmount + $this->addOnsAmount;
    $this->taxAmount = round($subtotal * 0.07, 2);
    $this->totalAmount = round($subtotal + $this->taxAmount, 2);
}
```
**จุดที่น่าสังเกต**: สังเกตว่า `Booking` เรียกใช้ `$this->room->calculateBasePrice()` ซึ่งเป็นการนำหลัก **Dependency Injection** และการใช้บริการระหว่างออบเจกต์ (Message Passing) มาใช้

### 4.3 คลาส `BookingManager.php`
```php
public function createBooking(Booking $booking): array
{
    // ขั้นที่ 1: ตรวจสอบความถูกต้องของโมเดล
    $validation = $booking->validateTimeSlot();
    if (!$validation['valid']) {
        return ['success' => false, 'message' => implode(' | ', $validation['errors'])];
    }

    // ขั้นที่ 2: ตรวจจับเวลาชนกันในระบบ
    if ($this->hasTimeConflict(...)) {
        return ['success' => false, 'message' => 'ห้องถูกจองแล้วในช่วงเวลาดังกล่าว'];
    }

    // ขั้นที่ 3: จัดเก็บลงระบบ
    $this->bookings[$booking->getBookingCode()] = $booking;
    $this->saveBookings();

    return ['success' => true, 'booking' => $booking->toArray()];
}
```

---

## 5. แบบฝึกหัดและคำถามทดสอบความเข้าใจ (Exercises & Quiz)

### ข้อที่ 1 (แนวคิด Encapsulation)
**คำถาม**: หากเราเปลี่ยนตัวแปรในคลาส `Booking` จาก `private` เป็น `public` ทั้งหมด จะส่งผลเสียต่อการทำงานของระบบอย่างไร?  
**แนวทางคำตอบ**: จะทำให้โค้ดส่วนอื่นสามารถเข้าไปแก้ไขค่า `$totalAmount` หรือ `$durationHours` โดยตรงโดยไม่ผ่านการคำนวณที่ถูกต้อง ส่งผลให้ยอดเงินไม่ตรงกับค่าบริการจริง และสูญเสีย Data Integrity

### ข้อที่ 2 (การตรวจสอบเวลาซ้ำซ้อน)
**คำถาม**: หากมีผู้จองห้อง A เวลา 10:00 - 12:00 น. แล้วมีผู้ใช้อีกคนขอจองห้อง A ในเวลา 12:00 - 14:00 น. ในวันเดียวกัน ระบบจะถือว่าชนกันหรือไม่? เพราะเหตุใด?  
**แนวทางคำตอบ**: **ไม่ชนกัน** เพราะเมื่อแทนค่าในสูตร $(\text{ReqStart} < \text{ExistEnd}) \land (\text{ReqEnd} > \text{ExistStart})$  
จะได้ว่า $\text{ReqStart} = 12:00$ และ $\text{ExistEnd} = 12:00$ ซึ่งเงื่อนไข $12:00 < 12:00$ เป็น **เท็จ (False)** ระบบจึงอนุญาตให้จองต่อกันได้ทันที

### ข้อที่ 3 (แบบฝึกหัดพัฒนาต่อยอดเชิงปฏิบัติ)
**โจทย์ฝึกปฏิบัติ**: ให้นิสิต/ผู้เรียนลองเพิ่มฟีเจอร์ส่วนลดพิเศษ (Discount Code) เช่น โค้ด `PROMO10` ลดราคา 10%
- *คำถามชวนคิด*: ควรเพิ่มแอตทริบิวต์และเมธอดนี้ในคลาสใด ระหว่าง `Room`, `Booking`, หรือ `BookingManager`?
- *เฉลย*: ควรเพิ่มในคลาส **`Booking`** เพราะส่วนลดเป็นคุณลักษณะและข้อตกลงของธุรกรรมการจองแต่ละครั้ง ไม่ได้ผูกติดกับห้องทางกายภาพ
