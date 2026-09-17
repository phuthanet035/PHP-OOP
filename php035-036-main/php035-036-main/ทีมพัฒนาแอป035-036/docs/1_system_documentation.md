# เอกสารการทำงานระบบ (System & Architecture Documentation)
## โครงการ: ระบบจองห้องประชุมและพื้นที่ทำงานอัจฉริยะ (SpaceHub Booking System)
**รหัสโครงการ:** 035-036  
**รูปแบบสถาปัตยกรรม:** Object-Oriented Analysis and Design (OOAD) & Object-Oriented Programming (OOP) — 3 Main Classes  
**เทคโนโลยีหลัก:** PHP 8.4 (Strict Types), Vanilla JavaScript (ES6+), Vanilla CSS (Glassmorphism), JSON Persistence Database

---

## 1. บทนำและวัตถุประสงค์ของระบบ (System Overview & Objectives)

### 1.1 ความเป็นมาและปัญหาเดิม
ในองค์กร Co-working Space หรือสถาบันการศึกษา การจองห้องประชุมมักประสบปัญหาการจองซ้ำซ้อน (Booking Collisions), ความยุ่งยากในการใช้งานเนื่องจากต้องผ่านขั้นตอนการลงทะเบียนและเข้าสู่ระบบ (Login Friction), และการคำนวณราคาที่ไม่โปร่งใสเมื่อมีการใช้อุปกรณ์เสริมหรือบริการจัดเลี้ยง (Catering)

### 1.2 วัตถุประสงค์ของระบบ SpaceHub
1. **นำไปใช้งานได้จริง (Production-Ready)**: ระบบสามารถให้บริการจอง ตรวจสอบสถานะ คำนวณราคา และออกใบยืนยันการจองดิจิทัล (Digital Voucher) ได้ทันที
2. **ออกแบบตามหลัก OOAD และ OOP อย่างเคร่งครัด**: โครงสร้างหลักประกอบด้วย 3 คลาสที่มีหน้าที่ชัดเจน (Single Responsibility Principle)
3. **ลดอุปสรรคการใช้งาน (No Login Barrier)**: ผู้ใช้สามารถทำรายการจองโดยตรงผ่านรหัสอ้างอิง (Booking Reference Code) ซึ่งใช้ค้นหา แก้ไข หรือยกเลิกการจองได้เอง
4. **ป้องกันการจองเวลาชนกัน 100% (Collision Prevention)**: ใช้อัลกอริทึมตรวจสอบช่วงเวลาว่างแบบคณิตศาสตร์ช่วง (Interval Overlap Algorithm)

---

## 2. การวิเคราะห์และออกแบบเชิงวัตถุ (OOAD: Object-Oriented Analysis & Design)

ระบบได้รับการออกแบบโดยแบ่งความรับผิดชอบออกเป็น 3 คลาสหลักตามบทบาท (Roles & Responsibilities):

```mermaid
classDiagram
    direction TB
    
    class Room {
        -string id
        -string name
        -string type
        -int capacity
        -float hourlyRate
        -array amenities
        -string imageUrl
        -string description
        -int minHours
        +__construct(...)
        +getId(): string
        +getName(): string
        +getType(): string
        +getCapacity(): int
        +getHourlyRate(): float
        +getAmenities(): array
        +getImageUrl(): string
        +getDescription(): string
        +getMinHours(): int
        +calculateBasePrice(float hours): float
        +isSuitableFor(int attendees): bool
        +toArray(): array
        +fromArray(array data)$ Room
    }

    class Booking {
        -string bookingCode
        -string customerName
        -string customerPhone
        -string customerEmail
        -string organization
        -string purpose
        -Room room
        -string bookingDate
        -string startTime
        -string endTime
        -int attendeeCount
        -array addOnServices
        -string status
        -string createdAt
        -string notes
        -float durationHours
        -float baseAmount
        -float addOnsAmount
        -float taxAmount
        -float totalAmount
        +__construct(...)
        +generateCode()$ string
        +calculateDurationHours(): float
        +recalculateFinancials(): void
        +validateTimeSlot(): array
        +confirmBooking(): void
        +cancelBooking(): void
        +getBookingCode(): string
        +getCustomerName(): string
        +getCustomerPhone(): string
        +getCustomerEmail(): string
        +getRoom(): Room
        +getBookingDate(): string
        +getStartTime(): string
        +getEndTime(): string
        +getAttendeeCount(): int
        +getAddOnServices(): array
        +getStatus(): string
        +getTotalAmount(): float
        +toArray(): array
        +fromArray(array data, Room room)$ Booking
    }

    class BookingManager {
        -array~string, Room~ rooms
        -array~string, Booking~ bookings
        -string dataDir
        -string roomsFile
        -string bookingsFile
        +__construct(string dataDir)
        -initializeStorage(): void
        +loadData(): void
        +saveBookings(): bool
        +hasTimeConflict(string roomId, string date, string start, string end, ?string excludeCode): bool
        +getAvailableRooms(string date, string start, string end, int attendees): array
        +createBooking(Booking booking): array
        +cancelBooking(string bookingCode): array
        +getBookingByCode(string bookingCode): ?Booking
        +searchBookings(string keyword): array
        +getAllBookings(array filters): array
        +getAllRooms(): array
        +getRoomById(string roomId): ?Room
        +getStatistics(): array
    }

    BookingManager "1" o-- "*" Room : manages & seeds
    BookingManager "1" *-- "*" Booking : orchestrates & persists
    Booking "1" --> "1" Room : references room resource
```

### 2.1 รายละเอียดและบทบาทของ 3 คลาส

| ชื่อคลาส | บทบาทตาม OOAD | หลักการ OOP ที่นำมาใช้ | ความรับผิดชอบหลัก |
| :--- | :--- | :--- | :--- |
| **`Room`** | **Entity / Resource Model** | Encapsulation, Information Hiding, Factory Method | จัดเก็บคุณลักษณะของห้องประชุม, ความจุ, อัตราค่าเช่ารายชั่วโมง, และคำนวณราคาขั้นต่ำตามชั่วโมงใช้งาน |
| **`Booking`** | **Entity / Transaction Model** | Encapsulation, Dependency Injection, State Pattern | จัดเก็บข้อมูลธุรกรรมการจอง, ข้อมูลผู้จอง, บริการเสริม, ตรวจสอบความถูกต้องของเวลา และคำนวณราคาสุทธิ (ค่าห้อง + บริการเสริม + VAT 7%) |
| **`BookingManager`** | **Service / Controller / Repository** | High Cohesion, Low Coupling, Data Persistence | บริหารจัดการคลังห้องและรายการจองทั้งหมด, ตรวจจับการจองซ้ำซ้อน (Conflict Detection), บันทึกและดึงข้อมูลจากไฟล์ JSON, และคำนวณสถิติภาพรวม |

---

## 3. ผังลำดับการทำงาน (Sequence Diagrams)

### 3.1 ขั้นตอนการตรวจสอบและทำการจอง (Booking Creation & Conflict Prevention Flow)

```mermaid
sequenceDiagram
    autonumber
    actor User as ผู้ใช้งาน (Guest / User)
    participant UI as หน้าเว็บ (index.php / app.js)
    participant API as เกตเวย์ (api.php)
    participant BM as BookingManager
    participant BK as Booking
    participant RM as Room
    participant DB as JSON Storage (bookings.json)

    User->>UI: ระบุวันที่, เวลา (09:00 - 12:00), จำนวนคน
    UI->>API: GET api.php?action=check_availability&date=...&start=...
    API->>BM: getAvailableRooms(date, start, end, attendees)
    loop วนตรวจสอบแต่ละห้องในระบบ
        BM->>RM: isSuitableFor(attendees)
        BM->>BM: hasTimeConflict(roomId, date, start, end)
    end
    BM-->>API: รายชื่อห้องที่ว่างจริง
    API-->>UI: แสดงผลเฉพาะห้องที่พร้อมจอง (Active Green Badge)

    User->>UI: กด "จองห้องนี้ทันที" และกรอกข้อมูลผู้จอง + บริการเสริม
    User->>UI: กดยืนยันการจอง (Confirm Booking)
    UI->>API: POST api.php?action=create_booking (JSON Payload)
    API->>BM: getRoomById(roomId)
    API->>BK: new Booking(..., Room)
    API->>BM: createBooking(Booking)
    
    BM->>BK: validateTimeSlot()
    alt ช่วงเวลาไม่ถูกต้อง
        BK-->>BM: Return Validation Errors
        BM-->>API: success: false, message
        API-->>UI: แสดงข้อผิดพลาดทาง Toast Alert
    else ผ่านการตรวจสอบเบื้องต้น
        BM->>BM: hasTimeConflict(...) [Double Checking]
        alt พบการจองซ้ำซ้อน
            BM-->>API: success: false, ห้องถูกจองแล้วในช่วงเวลานี้
            API-->>UI: แจ้งเตือนการชนของเวลา
        else เวลาว่างแน่นอน
            BM->>BK: confirmBooking()
            BM->>DB: saveBookings() (บันทึกลง bookings.json)
            BM-->>API: success: true, booking data + code
            API-->>UI: success: true
            UI->>UI: แสดงใบยืนยันการจองดิจิทัล (Digital Voucher Modal)
        end
    end
```

### 3.2 ขั้นตอนการค้นหาและขอยกเลิกการจอง (Booking Lookup & Cancellation Flow)

```mermaid
sequenceDiagram
    autonumber
    actor User as ผู้ใช้งาน
    participant UI as แท็บค้นหา (Lookup Tab)
    participant API as api.php
    participant BM as BookingManager
    participant BK as Booking
    participant DB as bookings.json

    User->>UI: กรอกรหัสการจอง เช่น "BK-2026-89B27"
    UI->>API: GET api.php?action=lookup_booking&code=BK-2026-89B27
    API->>BM: getBookingByCode("BK-2026-89B27")
    BM-->>API: Booking Data (Room, Time, Financials, Status)
    API-->>UI: แสดงการ์ดรายละเอียดการจอง
    
    opt ผู้ใช้งานกดยกเลิกการจอง
        User->>UI: กดยืนยัน "ยกเลิกการจองนี้"
        UI->>API: POST api.php?action=cancel_booking { bookingCode }
        API->>BM: cancelBooking("BK-2026-89B27")
        BM->>BK: cancelBooking() (เปลี่ยนสถานะเป็น CANCELLED)
        BM->>DB: saveBookings()
        BM-->>API: success: true
        API-->>UI: แจ้งเตือนยกเลิกสำเร็จ & คืนสิทธิ์ห้องว่างให้ระบบทันที
    end
```

---

## 4. โครงสร้างการจัดเก็บข้อมูล (Data Schema & Storage)

ระบบใช้ไฟล์ JSON เพื่อความสะดวกรวดเร็วในการติดตั้งและใช้งานจริงโดยไม่ต้องพึ่งพา RDBMS ภายนอก:

### 4.1 ไฟล์ `data/rooms.json` (Room Resource Schema)
```json
[
  {
    "id": "R101",
    "name": "Boardroom Alpha",
    "type": "Executive Boardroom",
    "capacity": 16,
    "hourlyRate": 650.0,
    "amenities": [
      "4K Ultra-HD Display (75\")",
      "Polycom Video Conference Bar",
      "Glass Whiteboard",
      "Wireless Presentation",
      "High-Speed Wi-Fi 6",
      "Air Purifier"
    ],
    "imageUrl": "https://images.unsplash.com/photo-1517502884422-41eaead166d4?auto=format&fit=crop&w=800&q=80",
    "description": "ห้องประชุมระดับผู้บริหาร บรรยากาศพรีเมียม เงียบสงบ พร้อมระบบวิดีโอคอนเฟอเรนซ์ระดับมืออาชีพ",
    "minHours": 1
  }
]
```

### 4.2 ไฟล์ `data/bookings.json` (Booking Transaction Schema)
```json
[
  {
    "bookingCode": "BK-2026-89B27",
    "customerName": "Khun Ekkarat Charoensuk",
    "customerPhone": "085-999-1122",
    "customerEmail": "ekkarat.c@innovation.com",
    "organization": "Creative Hub Thailand",
    "purpose": "AI Platform Project Planning Meeting",
    "roomId": "R102",
    "roomName": "Innovation Pod B",
    "roomType": "Team Collaboration",
    "roomRate": 420.0,
    "bookingDate": "2026-09-14",
    "startTime": "09:00",
    "endTime": "12:00",
    "durationHours": 3.0,
    "attendeeCount": 1,
    "addOnServices": [
      {
        "id": "projector_4k",
        "name": "ระบบเชื่อมต่อไร้สาย 4K Wireless ClickShare",
        "price": 350.0,
        "quantity": 1
      },
      {
        "id": "sound_eng",
        "name": "เจ้าหน้าที่เทคนิคประจำห้องคุมระบบภาพ-เสียง",
        "price": 600.0,
        "quantity": 1
      }
    ],
    "status": "CONFIRMED",
    "createdAt": "2026-09-14 10:55:00",
    "notes": "",
    "baseAmount": 1260.0,
    "addOnsAmount": 950.0,
    "taxAmount": 154.7,
    "totalAmount": 2364.7
  }
]
```

---

## 5. คู่มือการติดตั้งและใช้งานระบบ (Installation & Run Guide)

### 5.1 ความต้องการของระบบ (System Requirements)
- PHP เวอร์ชัน 8.0 ขึ้นไป (ทดสอบบน PHP 8.4.24 Strict Types)
- เว็บเบราว์เซอร์มาตรฐาน (Chrome, Edge, Firefox, Safari)

### 5.2 ขั้นตอนการเปิดใช้งานระบบ (Step-by-Step Launch)
1. เปิด Command Prompt หรือ PowerShell ในโฟลเดอร์โปรเจกต์ `c:\Users\camer\035-036\`
2. รันคำสั่งเปิดเครื่องบริการ PHP Built-in Server:
   ```bash
   php -S localhost:8000
   ```
3. เปิดเว็บเบราว์เซอร์แล้วเข้าสู่ URL:
   ```
   http://localhost:8000/index.php
   ```
4. ระบบพร้อมใช้งานได้ทันทีโดยไม่ต้องตั้งค่าฐานข้อมูลหรือลงทะเบียนผู้ใช้
