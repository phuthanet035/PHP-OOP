# Smart IT Helpdesk & Notification System 🛠️
> **ระบบแจ้งซ่อมและติดตามสถานะงานซ่อมอุปกรณ์ไอทีอัจฉริยะ พร้อมระบบแจ้งเตือน**  
> สถาปัตยกรรม: **Custom OOP PHP 8.2+ MVC (No Framework)**  
> รายวิชา: **การออกแบบและพัฒนาเว็บขั้นสูง** — สาขาวิชาเทคโนโลยีสารสนเทศ

---

## 📌 สรุปการออกแบบตาม 9 System Diagrams

ระบบนี้ได้รับการออกแบบและพัฒนาให้ตรงตามข้อกำหนดทางวิศวกรรมซอฟต์แวร์ 9 Diagrams อย่างสมบูรณ์:

1. **System Architecture (Diagram 1):**  
   - แยกเลเยอร์ชัดเจนตามหลัก **Separation of Concerns**:
     - `Client Layer`: HTML5 + Tailwind CSS + Vanilla JavaScript (Fetch API)
     - `Server Layer`: Custom PHP 8.2+ MVC (Router, Middleware Pipeline, Controllers, Services, Repositories, Event Dispatcher)
     - `Data Layer`: MySQL 8.0 / MariaDB ผ่าน PDO Connection (Singleton Pattern) + File Storage (`storage/uploads/`) + Audit Logs (`status_logs`)
     - `External Services`: LINE Messaging API ผ่าน `LineMessagingService` (Observer Pattern)

2. **Entity Relationship Diagram (Diagram 2):**  
   - ฐานข้อมูลสัมพันธ์ 6 ตารางหลักพร้อม Foreign Keys:
     - `users` (id, name, email, password_hash, role, line_user_id, created_at)
     - `categories` (id, name, description)
     - `tickets` (id, user_id, category_id, technician_id, title, description, status, priority, created_at, updated_at, resolved_at, closed_at)
     - `comments` (id, ticket_id, user_id, body, image_path, created_at)
     - `status_logs` (id, ticket_id, changed_by, from_status, to_status, note, created_at)
     - `ratings` (id, ticket_id, score, feedback, created_at)

3. **Ticket State Machine (Diagram 3):**  
   - กฎการเปลี่ยนสถานะ 5 ขั้นตอน (Open ➔ Assigned ➔ InProgress ➔ Resolved ➔ Closed / Revert InProgress)
   - ควบคุมการเปลี่ยนสถานะด้วย `TicketStatusService` และ PHP 8.1+ Backed Enum:
     - `Open ➔ Assigned`: เฉพาะ **Admin** (ต้องเลือก `technician_id`)
     - `Assigned ➔ InProgress`: เฉพาะ **ช่างเทคนิคที่ได้รับมอบหมาย** (พร้อมบันทึกรับงาน)
     - `InProgress ➔ Resolved`: เฉพาะ **ช่างเทคนิค** (ต้องแนบรูปภาพผลการซ่อม `repair_photo` ตามระเบียบ)
     - `Resolved ➔ Closed`: เฉพาะ **ผู้แจ้งซ่อม (Owner)** (ต้องให้คะแนนความพึงพอใจ 1-5 ดาว)
     - `Resolved ➔ InProgress`: เฉพาะ **ผู้แจ้งซ่อม** (ปฏิเสธผลงานพร้อมระบุเหตุผลเพื่อส่งช่างแก้ไขต่อ)

4. **Use Case Diagram & RBAC (Diagram 4):**  
   - บทบาทผู้ใช้ 3 กลุ่ม:
     - **General User:** สร้างคำขอแจ้งซ่อม, ดูและติดตามสถานะงานของตนเอง, ส่งข้อความซักถาม, ยืนยันผลงานและให้คะแนน 1-5 ดาว หรือส่งกลับไปซ่อมต่อ
     - **Technician:** ดูงานที่ได้รับมอบหมาย/งานทั้งหมด, กดรับงาน (InProgress), บันทึกผลการซ่อมเสร็จสิ้น (Resolved) พร้อมแนบรูปถ่าย
     - **Admin IT:** มอบหมายช่างเทคนิค, จัดการผู้ใช้งาน, จัดการหมวดหมู่, ดูแดชบอร์ดสถิติ & ประสิทธิภาพ, ตรวจสอบ Audit Log และ LINE Notification Log
   - กลไกความปลอดภัย: CSRF Protection (`CsrfMiddleware`), Authentication (`AuthMiddleware`), Role Check (`RoleMiddleware`), MIME & File Size Whitelist (`FileUploader`).

5. **Class Diagram & OOP Patterns (Diagram 5):**  
   - **Singleton Pattern:** `Database::getInstance()`
   - **Repository Pattern:** `TicketRepository`, `UserRepository`, `CategoryRepository`, `CommentRepository`, `StatusLogRepository`, `RatingRepository`
   - **Service Layer Pattern:** `TicketStatusService`, `TicketService`, `DashboardService`, `FileUploader`
   - **Observer Pattern:** `EventDispatcher` ดักฟังเหตุการณ์ `ticket.created` และ `ticket.status_changed` แล้วเรียก `TicketObserver`
   - **Strategy Pattern:** `NotificationChannelInterface` ➔ `LineMessagingService`
   - **Dependency Injection:** คลาสรับ Dependencies ผ่าน Constructor

6. **Sequence Diagrams (Diagram 6):**  
   - 6.1 Create Ticket Flow ➔ File Validation ➔ Insert DB ➔ Event Dispatch ➔ LINE Push
   - 6.2 Technician Update Status Flow (AJAX/Fetch API + DB Transaction UPDATE/INSERT)
   - 6.3 Admin View Dashboard Flow (Aggregated Queries: Status breakdown, SLA hours, Top Techs, Monthly Ratings)

7. **Activity Diagram (Diagram 7):**  
   - Lifecycle ครบวงจรตั้งแต่ User แจ้งซ่อม ➔ ตรวจสอบไฟล์ ➔ Admin จ่ายงาน ➔ ช่างซ่อม ➔ User ตรวจรับและปิดงาน

8. **Component Diagram & Folder Structure (Diagram 8):**  
   - โครงสร้างโฟลเดอร์ตามมาตรฐาน PSR-4 Autoloading:
     ```
     ├── composer.json (PSR-4 autoloading definition)
     ├── .env / .env.example
     ├── .htaccess
     ├── database.sql
     ├── init_db.php (Database Auto-installer & Seeder)
     ├── run_server.bat (1-click Server Launcher)
     ├── test_system.php (Automated Unit & Integration Test Suite)
     ├── public/
     │   ├── index.php (Front Controller Entry Point)
     │   └── .htaccess
     ├── src/
     │   ├── Core/ (Database, Router, Request, Response, Auth, Csrf, Validator, EventDispatcher, View)
     │   ├── Enums/ (TicketStatus, TicketPriority, UserRole)
     │   ├── Middleware/ (AuthMiddleware, RoleMiddleware, CsrfMiddleware)
     │   ├── Models/
     │   ├── Repositories/ (RepositoryInterface, BaseRepository, TicketRepository, ...)
     │   ├── Services/ (TicketStatusService, TicketService, DashboardService, FileUploader)
     │   ├── Observers/ (TicketObserver)
     │   └── Notifications/ (NotificationChannelInterface, LineMessagingService)
     │   └── Controllers/ (AuthController, TicketController, DashboardController, AdminController, FileController)
     ├── storage/
     │   ├── uploads/ (เก็บรูปภาพที่อัปโหลด อยู่นอก Document Root)
     │   └── logs/ (app.log, line_notifications.log)
     └── views/
         ├── layouts/ (main.php, auth.php)
         ├── auth/ (login.php, register.php)
         ├── tickets/ (index.php, create.php, show.php)
         ├── admin/ (dashboard.php, users.php, categories.php, line_logs.php)
         └── errors/ (403.php, 404.php)
     ```

9. **Deployment Architecture (Diagram 9):**  
   - Document Root ชี้ไปที่ `/public/` เพื่อความปลอดภัย
   - โฟลเดอร์ `storage/uploads/` และไฟล์ `.env` อยู่นอก Document Root
   - รหัสผ่านเข้ารหัสด้วย `password_hash()` BCRYPT

---

## 🔑 บัญชีผู้ใช้งานเริ่มต้นสำหรับทดสอบ (Demo Accounts)

ทุกบัญชีใช้รหัสผ่าน: `password123`

| บทบาท (Role) | อีเมล (Email) | รหัสผ่าน | สิทธิ์และการทดสอบ |
| :--- | :--- | :--- | :--- |
| 👑 **ผู้ดูแลระบบ (Admin IT)** | `admin@helpdesk.com` | `password123` | ดูแดชบอร์ดสถิติ, มอบหมายงานให้ช่าง, จัดการผู้ใช้/หมวดหมู่ |
| 🔧 **ช่างเทคนิค (Senior Tech)** | `tech@helpdesk.com` | `password123` | ดูงานที่มอบหมาย, กดรับงาน, แนบรูปผลการซ่อมเสร็จสิ้น |
| 🔧 **ช่างเครือข่าย (Network Tech)** | `surachai@helpdesk.com` | `password123` | ดูงานระบบเน็ตเวิร์ก, ดำเนินการซ่อม |
| 👤 **ผู้แจ้งซ่อม (General User)** | `user@helpdesk.com` | `password123` | สร้างคำขอแจ้งซ่อม, ติดตามสถานะ, ยืนยันปิดงาน & ให้คะแนน |
| 👤 **เจ้าหน้าที่ (User Staff)** | `naphaporn@helpdesk.com` | `password123` | สร้างคำขอแจ้งซ่อม, ซักถามผ่านคอมเมนต์ |

> **ทางลัดพิเศษ:** ด้านบนแถบเมนูมีปุ่ม **"สลับบทบาททดสอบ (Quick Switcher)"** ให้คลิกสลับบทบาทระหว่าง Admin, Technician, User ได้ทันทีโดยไม่ต้องพิมพ์ล็อกอินใหม่

---

## 🚀 วิธีการติดตั้งและรันใช้งานระบบ

### วิธีที่ 1: รันผ่าน PHP Development Server (แนะนำและสะดวกรวดเร็วที่สุด)
1. เปิด **XAMPP Control Panel** และตรวจสอบให้แน่ใจว่า **MySQL** กำลังทำงาน (Start MySQL)
2. ดับเบิ้ลคลิกไฟล์ `run_server.bat` ในโฟลเดอร์โปรเจกต์
3. เปิดเว็บเบราว์เซอร์แล้วเข้าใช้งานที่:
   ```
   http://localhost:8080
   ```

### วิธีที่ 2: รันผ่าน XAMPP Apache
1. คัดลอกโฟลเดอร์นี้ไปไว้ที่ `C:\xampp\htdocs\smart_it_helpdesk`
2. Start Apache และ MySQL ใน XAMPP Control Panel
3. เปิดเบราว์เซอร์แล้วไปที่:
   ```
   http://localhost/smart_it_helpdesk/public/
   ```

### การตั้งค่าฐานข้อมูลใหม่ / รีเซ็ตข้อมูลเริ่มต้น (Reset Database)
สามารถรันคำสั่ง CLI นี้ได้ทุกเมื่อ:
```bash
php init_db.php
```

### การรันชุดทดสอบอัตโนมัติ (Automated Test Suite)
รันเพื่อทดสอบการทำงานของ State Machine, Repository, Observer, และ Database:
```bash
php test_system.php
```
*(ผ่านการทดสอบ 29/29 Test Cases สำเร็จสมบูรณ์)*

---

## 💬 การตั้งค่า LINE Messaging API

- ค่าเริ่มต้นระบบจะทำงานในโหมด **Simulation / Mock Log** โดยอัตโนมัติ: ข้อความแจ้งเตือนทั้งหมดจะถูกบันทึกอย่างสวยงามลงใน `storage/logs/line_notifications.log` และสามารถตรวจสอบประวัติได้ที่เมนู **"LINE Logs"** ในแถบ Admin
- หากต้องการใช้งานจริงกับ LINE Official Account:
  1. นำ **Channel Access Token (Long-Lived)** จาก [LINE Developers Console](https://developers.line.biz/)
  2. ใส่ในไฟล์ `.env`:
     ```env
     LINE_CHANNEL_ACCESS_TOKEN="YOUR_CHANNEL_ACCESS_TOKEN"
     LINE_NOTIFICATION_ENABLED=true
     ```
  3. ระบบจะทำการยิงข้อความ Push Message ผ่าน cURL ไปยัง LINE ผู้ใช้ทันที
