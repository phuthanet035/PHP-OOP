-- ============================================================
-- SpaceHub Database Schema v3.1 - COMPLETE ALL-IN-ONE UTF8MB4 SAFE
-- พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
-- ปรับปรุง: อัปเดตโครงสร้าง DDL และข้อมูลการจองแบบ Dynamic CURDATE()
-- รองรับการ Import ผ่าน phpMyAdmin / MySQL CLI 100%
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET character_set_connection = utf8mb4;
SET character_set_results = utf8mb4;
SET character_set_client = utf8mb4;

-- STEP 0: สร้างฐานข้อมูล spacehub_db อย่างปลอดภัย และเลือกใช้งาน
CREATE DATABASE IF NOT EXISTS `spacehub_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `spacehub_db`;

SET NAMES utf8mb4;

-- ปิด Foreign Key ชั่วคราวเพื่อลบตารางเก่าและสร้างใหม่อย่างสะอาด
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `add_ons`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- STEP 1: สร้างตารางทั้งหมด (DDL)
-- ============================================================

-- 1.1 ตารางผู้ใช้งาน (Users) - ออกแบบร่วมกัน (035 & 036)
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT           AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(50)   NOT NULL UNIQUE,
  `password`   VARCHAR(255)  NOT NULL,
  `fullname`   VARCHAR(100)  NOT NULL,
  `email`      VARCHAR(100)  NOT NULL UNIQUE,
  `phone`      VARCHAR(20)   DEFAULT '',
  `role`       ENUM('ADMIN','MEMBER') DEFAULT 'MEMBER',
  `avatar`     VARCHAR(255)  DEFAULT 'default_avatar.png',
  `created_at` DATETIME      DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.2 ตารางห้องประชุม (Rooms) - รับผิดชอบโดย รหัส 035
CREATE TABLE IF NOT EXISTS `rooms` (
  `id`          VARCHAR(50)    PRIMARY KEY,
  `name`        VARCHAR(100)   NOT NULL,
  `type`        VARCHAR(50)    NOT NULL,
  `capacity`    INT            NOT NULL DEFAULT 1,
  `hourly_rate` DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
  `amenities`   TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_url`   VARCHAR(500)   DEFAULT '',
  `description` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `min_hours`   INT            DEFAULT 1,
  `created_at`  DATETIME       DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.3 ตารางบริการเสริม (Add-ons) - รับผิดชอบโดย รหัส 036 (ศุภนัฐ จันทร์เปรม)
CREATE TABLE IF NOT EXISTS `add_ons` (
  `id`    VARCHAR(50)   PRIMARY KEY,
  `name`  VARCHAR(200)  NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `unit`  VARCHAR(50)   DEFAULT 'รายการ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.4 ตารางการจอง (Bookings) - รับผิดชอบโดย รหัส 036 (ศุภนัฐ จันทร์เปรม)
CREATE TABLE IF NOT EXISTS `bookings` (
  `id`             INT            AUTO_INCREMENT PRIMARY KEY,
  `booking_code`   VARCHAR(50)    NOT NULL UNIQUE,
  `customer_name`  VARCHAR(100)   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` VARCHAR(20)    NOT NULL,
  `customer_email` VARCHAR(100)   NOT NULL,
  `organization`   VARCHAR(200)   CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `purpose`        TEXT           CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `room_id`        VARCHAR(50)    NOT NULL,
  `booking_date`   DATE           NOT NULL,
  `start_time`     TIME           NOT NULL,
  `end_time`       TIME           NOT NULL,
  `attendee_count` INT            NOT NULL DEFAULT 1,
  `add_ons_json`   TEXT,
  `status`         ENUM('CONFIRMED','CANCELLED','COMPLETED','PENDING') DEFAULT 'CONFIRMED',
  `duration_hours` DECIMAL(5,2)   DEFAULT 0.00,
  `base_amount`    DECIMAL(10,2)  DEFAULT 0.00,
  `add_ons_amount` DECIMAL(10,2)  DEFAULT 0.00,
  `tax_amount`     DECIMAL(10,2)  DEFAULT 0.00,
  `total_amount`   DECIMAL(10,2)  DEFAULT 0.00,
  `created_at`     DATETIME       DEFAULT CURRENT_TIMESTAMP,
  `notes`          TEXT           CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- STEP 2: เพิ่มข้อมูลเริ่มต้น (Seed Data)
-- ============================================================

-- 2.1 ผู้ใช้งาน: admin=admin123, suppanat036=1234, student035=1234
INSERT INTO `users` (`id`,`username`,`password`,`fullname`,`email`,`phone`,`role`,`avatar`) VALUES
(1,'admin','$2y$10$HFIFR5/3RVpZxmeQMdT6JuiAPapaQhXg1KPaFAdVxVR/Hxv.tAPd.','ผู้ดูแลระบบ SpaceHub','admin@spacehub.com','081-234-5678','ADMIN','https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150'),
(2,'suppanat036','$2y$10$CFm3Fgw.4WGLkajUsG.eHupnAbKszLwSs8rmUfgZ2wV20SgVmaeW2','ศุภนัฐ จันทร์เปรม','suppanat036@spacehub.com','081-234-5678','MEMBER','https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150'),
(3,'student035','$2y$10$CFm3Fgw.4WGLkajUsG.eHupnAbKszLwSs8rmUfgZ2wV20SgVmaeW2','สมาชิกทีม 035','student035@spacehub.com','089-876-5432','MEMBER','https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150')
ON DUPLICATE KEY UPDATE `fullname`=VALUES(`fullname`);

-- 2.2 ห้องประชุม 20 ห้อง พร้อมรูปภาพจาก Unsplash (รับผิดชอบโดย รหัส 035)
INSERT INTO `rooms` (`id`,`name`,`type`,`capacity`,`hourly_rate`,`amenities`,`image_url`,`description`,`min_hours`) VALUES
('RM-101','Starlight Innovation Lab','Meeting Room',12,450.00,'["โปรเจคเตอร์ 4K","Smartboard อัจฉริยะ","ระบบ Video Conference","เครื่องชงกาแฟอัตโนมัติ","Wi-Fi 6"]','https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80','ห้องประชุมนวัตกรรมพร้อมเทคโนโลยีการประชุมระดับพรีเมียม รองรับการประชุมทั้งออนไซต์และไฮบริด',1),
('RM-102','Aurora Collaboration Hub','Meeting Room',16,550.00,'["จอ LED 75 นิ้ว","Wireless Presentation System","เก้าอี้ Ergonomic","ระบบ Air Purifier"]','https://images.unsplash.com/photo-1551434678-e076c223a692?auto=format&fit=crop&w=800&q=80','ห้องประชุมสมัยใหม่พร้อมระบบนำเสนอไร้สาย เหมาะสำหรับทีมงาน 10-16 คน',1),
('RM-103','Meridian Strategy Room','Meeting Room',10,400.00,'["โปรเจคเตอร์ Full HD","กระดานไวท์บอร์ดใหญ่","ระบบเสียง Bose","พอร์ต HDMI USB-C"]','https://images.unsplash.com/photo-1517502884422-41eaead166d4?auto=format&fit=crop&w=800&q=80','ห้องประชุมวางแผนกลยุทธ์ บรรยากาศเงียบสงบ เน้นประสิทธิภาพทำงานกลุ่ม',1),
('RM-104','Zenith Team Space','Meeting Room',8,350.00,'["Apple TV AirPlay","กระดาน Glass Board","ไฟ Adjustable Lighting","ระบบ Booking Display"]','https://images.unsplash.com/photo-1503387762-592deb58ef4e?auto=format&fit=crop&w=800&q=80','ห้องประชุมขนาดกลาง เหมาะสำหรับ Stand-up Meeting สไตล์มินิมอล',1),
('RM-201','Nexus Executive Boardroom','Boardroom',20,850.00,'["จอ OLED 85 นิ้ว 2 จอ","ระบบเสียงรอบทิศทาง Shure","โต๊ะประชุมไม้สักแท้","บริการผู้ช่วยส่วนตัว"]','https://images.unsplash.com/photo-1497215728101-856f4ea42174?auto=format&fit=crop&w=800&q=80','ห้องประชุมผู้บริหารระดับสูง ดีไซน์หรูหรา สำหรับการตัดสินใจระดับยุทธศาสตร์',2),
('RM-202','Pinnacle Director Suite','Boardroom',24,1200.00,'["Video Wall 4K","ระบบ Conference Polycom","โต๊ะ U-Shape พรีเมียม","มินิบาร์และบริการอาหาร"]','https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=800&q=80','บอร์ดรูมระดับ C-Suite ที่ใหญ่ที่สุด พร้อมระบบประชุมทางไกลระดับมืออาชีพ',2),
('RM-203','Summit Leadership Chamber','Boardroom',18,950.00,'["Interactive Display 80 นิ้ว","Cisco Webex Room Kit","เก้าอี้หนังนำเข้าอิตาลี","ระบบแสงธรรมชาติ"]','https://images.unsplash.com/photo-1541746972996-4e0b0f43e02a?auto=format&fit=crop&w=800&q=80','ห้องประชุมสำหรับผู้นำองค์กร ตกแต่งวัสดุพรีเมียม วิว Panoramic',2),
('RM-301','Spectrum Creative Studio','Creative Studio',15,650.00,'["Wacom Drawing Tablet x4","Mac Pro x6","กล้อง Sony A7IV","ระบบ Lighting Studio"]','https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=800&q=80','สตูดิโอสร้างสรรค์ทีม Design Media Production อุปกรณ์ครบครัน',2),
('RM-302','Prism Design Workshop','Creative Studio',12,580.00,'["จอ Color-Calibrated 4K x4","Pantone Color Library","3D Printer Bambu Lab","Material Sample Wall"]','https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80','ห้อง Workshop ออกแบบผลิตภัณฑ์ เครื่องพิมพ์ 3D คลังวัสดุตัวอย่าง 500 ชิ้น',2),
('RM-401','Solstice Focus Pod A','Focus Pod',4,200.00,'["จอภาพสำรอง 27 นิ้ว","เก้าอี้ Ergonomic Herman Miller","ปลั๊กไฟ USB-C Fast Charge"]','https://images.unsplash.com/photo-1527192491265-7e15c55b1ed2?auto=format&fit=crop&w=800&q=80','ห้องทำงานกลุ่มเล็ก เหมาะ Brainstorming งานต้องการสมาธิสูง',1),
('RM-402','Equinox Focus Pod B','Focus Pod',3,180.00,'["Standing Desk ปรับระดับ","จอ Dual Monitor Setup","Noise Cancelling Speaker","Wi-Fi 6E Priority"]','https://images.unsplash.com/photo-1517502884422-41eaead166d4?auto=format&fit=crop&w=800&q=80','ห้องทำงานส่วนตัว Deep Work Standing Desk ปรับระดับได้',1),
('RM-403','Aeon Private Office','Focus Pod',2,250.00,'["โต๊ะทำงาน L-Shape","ล็อคเกอร์ส่วนตัว","เครื่อง Espresso ส่วนตัว","โทรศัพท์ Direct Line"]','https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=800&q=80','ออฟฟิศส่วนตัวขนาดกะทัดรัด สำหรับ Executive 1-2 คน ความเป็นส่วนตัวสูง',1),
('RM-501','Grand Horizon Seminar Hall','Seminar Hall',80,2500.00,'["เวทีนำเสนอสูง 1 เมตร","ระบบ PA 5000W","จอ LED 10x6 เมตร","ระบบแสงอัจฉริยะ Philips Hue"]','https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=800&q=80','ห้องสัมมนาขนาดใหญ่ Corporate Event งานเปิดตัวผลิตภัณฑ์',4),
('RM-502','Apex Training Center','Seminar Hall',40,1200.00,'["โต๊ะ Classroom Style","โปรเจคเตอร์ Laser 5000 ANSI","ระบบ Live Streaming","กล้อง PTZ Auto-tracking"]','https://images.unsplash.com/photo-1560523159-4a9692d222f8?auto=format&fit=crop&w=800&q=80','ศูนย์ฝึกอบรม Live Streaming ในตัว Corporate Training',3),
('RM-503','Vantage Conference Theatre','Seminar Hall',60,1800.00,'["ที่นั่ง Theatre U-Shape","ระบบแปลภาษา 4 ภาษา","Breakout Rooms x3","Live Streaming 4K"]','https://images.unsplash.com/photo-1431540015161-0bf868a2d407?auto=format&fit=crop&w=800&q=80','ห้องประชุมนานาชาติ ระบบแปลภาษาพร้อมกัน Breakout Room',3),
('RM-601','Nova Co-Working Flex Hub','Open Space Zone',40,120.00,'["High-speed Wi-Fi 6","ตู้กดน้ำของว่างฟรี","โซนพักผ่อนเกม","ล็อคเกอร์ฟรี 4 ชม."]','https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=800&q=80','พื้นที่ทำงานรวม Freelancer Startup ต้องการความยืดหยุ่น',1),
('RM-602','Pulse Innovation Campus','Open Space Zone',60,100.00,'["Hot Desk 60 ที่","Phone Booth กันเสียง x4","Cafe Corner","Event Space ในตัว"]','https://images.unsplash.com/photo-1497366412874-3415097a27e7?auto=format&fit=crop&w=800&q=80','Co-Working Space ใหญ่ที่สุด บรรยากาศ Silicon Valley',1),
('RM-603','Orbit Startup Lounge','Open Space Zone',25,150.00,'["Dedicated Gigabit Internet","3D Printer Laser Cutter","Mini Kitchen","Pitch Deck Display"]','https://images.unsplash.com/photo-1556761175-5973dc0f32e7?auto=format&fit=crop&w=800&q=80','พื้นที่ Startup เครื่องมือ Prototype ทำ MVP',1),
('RM-701','Quantum Hybrid Studio','Meeting Room',14,750.00,'["Camera Array 360 องศา","Microsoft Teams Room System","Sound Proof Wall","AR VR Meeting Kit"]','https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=800&q=80','ห้องประชุมไฮบริดรุ่นล่าสุด กล้อง 360 ชุด AR VR Virtual Meeting',1),
('RM-702','Solaris Rooftop Terrace','Open Space Zone',30,900.00,'["วิวพาโนรามา 360 องศา","Wi-Fi กลางแจ้ง Outdoor Grade","โต๊ะ Outdoor Premium","ระบบไฟ Ambient LED"]','https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=800&q=80','พื้นที่ประชุมกลางแจ้งบนดาดฟ้า วิว 360 Team Building',1)
ON DUPLICATE KEY UPDATE `image_url`=VALUES(`image_url`), `description`=VALUES(`description`), `amenities`=VALUES(`amenities`);

-- 2.3 บริการเสริม (รับผิดชอบโดย รหัส 036 ศุภนัฐ จันทร์เปรม)
INSERT INTO `add_ons` (`id`,`name`,`price`,`unit`) VALUES
('coffee','ชุดกาแฟสดพรีเมียม เบเกอรี่ฝรั่งเศส',120.00,'ต่อคน'),
('projector_4k','ระบบเชื่อมต่อไร้สาย 4K Wireless ClickShare',350.00,'ต่อรอบ'),
('sound_eng','เจ้าหน้าที่เทคนิคคุมระบบภาพเสียง',600.00,'ต่อรอบ'),
('whiteboard_kit','ชุดอุปกรณ์ Post-it ปากกา Brainstorming',250.00,'ต่อชุด')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `price`=VALUES(`price`);

-- 2.4 รายการจองทดสอบ 20 รายการ
-- ปรับปรุง: ใช้วันที่ปัจจุบัน CURDATE() และวันถัดไปอัตโนมัติ เพื่อให้เมื่อนำไป Import ใน MySQL จะมีข้อมูลทดสอบสดใหม่อยู่เสมอ
-- สลับกัน: คี่ = รหัส 036 (ศุภนัฐ จันทร์เปรม), คู่ = รหัส 035
INSERT INTO `bookings`
(`id`,`booking_code`,`customer_name`,`customer_phone`,`customer_email`,`organization`,`purpose`,`room_id`,`booking_date`,`start_time`,`end_time`,`attendee_count`,`add_ons_json`,`status`,`duration_hours`,`base_amount`,`add_ons_amount`,`tax_amount`,`total_amount`,`created_at`,`notes`)
VALUES
(1,'BK-2026-001','ศุภนัฐ จันทร์เปรม (รหัส 036)','081-234-5678','suppanat036@spacehub.com','ทีมพัฒนาแอป 036','ประชุมวางแผนโปรเจกต์ทีม 036','RM-101',CURDATE(),'09:00:00','12:00:00',4,'["coffee","projector_4k"]','CONFIRMED',3.00,1350.00,830.00,152.60,2332.60,NOW(),'ขอปลั๊กไฟเพิ่ม 2 จุด (งาน 036)'),
(2,'BK-2026-002','สมาชิกทีมพัฒนา (รหัส 035)','089-876-5432','student035@spacehub.com','ทีมพัฒนาแอป 035','สัมมนาการเขียนโปรแกรม OOP 035','RM-102',CURDATE(),'09:00:00','12:00:00',8,'["whiteboard_kit"]','CONFIRMED',3.00,1650.00,250.00,133.00,2033.00,NOW(),'ขอไมโครโฟนไร้สาย (งาน 035)'),
(3,'BK-2026-003','ศุภนัฐ จันทร์เปรม (รหัส 036)','081-234-5678','suppanat036@spacehub.com','ทีมพัฒนาแอป 036','เวิร์กชอป Mobile App Dev 036','RM-201',CURDATE(),'09:00:00','12:00:00',15,'["coffee","sound_eng"]','CONFIRMED',3.00,2550.00,2400.00,346.50,5296.50,NOW(),'เตรียมเครื่องดื่มต้อนรับ (งาน 036)'),
(4,'BK-2026-004','สมาชิกทีมพัฒนา (รหัส 035)','089-876-5432','student035@spacehub.com','ทีมพัฒนาแอป 035','สอบสัมภาษณ์นักเรียนรหัส 035','RM-301',CURDATE(),'09:00:00','12:00:00',5,'["sound_eng"]','CONFIRMED',3.00,1950.00,600.00,178.50,2728.50,NOW(),'เสร็จสิ้นเรียบร้อย (งาน 035)'),
(5,'BK-2026-005','ศุภนัฐ จันทร์เปรม (รหัส 036)','081-234-5678','suppanat036@spacehub.com','ทีมพัฒนาแอป 036','ถ่ายทำคลิปวิดีโอ 036 Studio','RM-501',CURDATE(),'09:00:00','12:00:00',20,'["sound_eng"]','CONFIRMED',3.00,10000.00,600.00,742.00,11342.00,NOW(),'ต้องการไฟสปอร์ตไลท์เพิ่ม (งาน 036)'),
(6,'BK-2026-006','สมาชิกทีมพัฒนา (รหัส 035)','089-876-5432','student035@spacehub.com','ทีมพัฒนาแอป 035','อบรมการใช้งาน Database SQL 035','RM-302',DATE_ADD(CURDATE(), INTERVAL 1 DAY),'10:00:00','12:00:00',10,'["projector_4k"]','CONFIRMED',2.00,1160.00,350.00,105.70,1615.70,NOW(),'ขอสาย LAN 10 เส้น (งาน 035)'),
(7,'BK-2026-007','ศุภนัฐ จันทร์เปรม (รหัส 036)','081-234-5678','suppanat036@spacehub.com','ทีมพัฒนาแอป 036','Hackathon ทีม 036 Coding','RM-401',DATE_ADD(CURDATE(), INTERVAL 1 DAY),'08:00:00','17:00:00',4,'["coffee","projector_4k","sound_eng"]','PENDING',9.00,1800.00,1430.00,226.10,3456.10,NOW(),'รออนุมัติงบประมาณ (งาน 036)'),
(8,'BK-2026-008','สมาชิกทีมพัฒนา (รหัส 035)','089-876-5432','student035@spacehub.com','ทีมพัฒนาแอป 035','ติวข้อสอบวิชา Web Dev 035','RM-402',DATE_ADD(CURDATE(), INTERVAL 1 DAY),'13:00:00','17:00:00',3,'["whiteboard_kit"]','CONFIRMED',4.00,720.00,250.00,67.90,1037.90,NOW(),'เน้นแอร์เย็น (งาน 035)'),
(9,'BK-2026-009','ศุภนัฐ จันทร์เปรม (รหัส 036)','081-234-5678','suppanat036@spacehub.com','ทีมพัฒนาแอป 036','ประชุมบอร์ดบริหารโปรเจกต์ 036','RM-501',DATE_ADD(CURDATE(), INTERVAL 2 DAY),'11:00:00','15:00:00',10,'["coffee","sound_eng"]','CONFIRMED',4.00,10000.00,1800.00,826.00,12626.00,NOW(),'จัดชุดกาแฟ Coffee Break (งาน 036)'),
(10,'BK-2026-010','สมาชิกทีมพัฒนา (รหัส 035)','089-876-5432','student035@spacehub.com','ทีมพัฒนาแอป 035','ทดสอบระบบการจองห้อง 035','RM-502',DATE_ADD(CURDATE(), INTERVAL 2 DAY),'15:00:00','18:00:00',4,'[]','CANCELLED',3.00,3600.00,0.00,252.00,3852.00,NOW(),'ยกเลิกเนื่องจากติดภารกิจ (งาน 035)'),
(11,'BK-2026-011','ศุภนัฐ จันทร์เปรม (รหัส 036)','081-234-5678','suppanat036@spacehub.com','ทีมพัฒนาแอป 036','การอบรม UX UI Design 036','RM-601',DATE_ADD(CURDATE(), INTERVAL 3 DAY),'09:00:00','16:00:00',12,'["projector_4k","whiteboard_kit","coffee"]','CONFIRMED',7.00,840.00,2040.00,201.60,3081.60,NOW(),'ขอ Whiteboard ขนาดใหญ่ (งาน 036)'),
(12,'BK-2026-012','สมาชิกทีมพัฒนา (รหัส 035)','089-876-5432','student035@spacehub.com','ทีมพัฒนาแอป 035','สรุปผลงานประจำเดือน 035','RM-602',DATE_ADD(CURDATE(), INTERVAL 3 DAY),'13:00:00','15:00:00',5,'["projector_4k"]','CONFIRMED',2.00,200.00,350.00,38.50,588.50,NOW(),'ขอรีโมทนำเสนอ (งาน 035)'),
(13,'BK-2026-013','ศุภนัฐ จันทร์เปรม (รหัส 036)','081-234-5678','suppanat036@spacehub.com','ทีมพัฒนาแอป 036','บันทึกเสียง Podcast ทีม 036','RM-701',DATE_ADD(CURDATE(), INTERVAL 4 DAY),'10:00:00','13:00:00',3,'["sound_eng"]','CONFIRMED',3.00,2250.00,600.00,199.50,3049.50,NOW(),'ห้องเงียบเสียงพิเศษ (งาน 036)'),
(14,'BK-2026-014','สมาชิกทีมพัฒนา (รหัส 035)','089-876-5432','student035@spacehub.com','ทีมพัฒนาแอป 035','สาธิตซอฟต์แวร์ให้ลูกค้า 035','RM-702',DATE_ADD(CURDATE(), INTERVAL 4 DAY),'14:00:00','17:00:00',8,'["projector_4k"]','PENDING',3.00,2700.00,350.00,213.50,3263.50,NOW(),'รอการยืนยันจากลูกค้า (งาน 035)'),
(15,'BK-2026-015','ศุภนัฐ จันทร์เปรม (รหัส 036)','081-234-5678','suppanat036@spacehub.com','ทีมพัฒนาแอป 036','สัมมนา Cyber Security 036','RM-101',DATE_ADD(CURDATE(), INTERVAL 5 DAY),'09:00:00','12:00:00',12,'["coffee","sound_eng"]','CONFIRMED',3.00,1350.00,2040.00,237.30,3627.30,NOW(),'ขอเก้าอี้เสริม 5 ตัว (งาน 036)'),
(16,'BK-2026-016','สมาชิกทีมพัฒนา (รหัส 035)','089-876-5432','student035@spacehub.com','ทีมพัฒนาแอป 035','เวิร์กชอป Data Science 035','RM-102',DATE_ADD(CURDATE(), INTERVAL 5 DAY),'13:00:00','16:00:00',14,'["projector_4k"]','CONFIRMED',3.00,1650.00,350.00,140.00,2140.00,NOW(),'ปลั๊กพ่วงสำหรับโน้ตบุ๊ก (งาน 035)'),
(17,'BK-2026-017','ศุภนัฐ จันทร์เปรม (รหัส 036)','081-234-5678','suppanat036@spacehub.com','ทีมพัฒนาแอป 036','ระดมความคิดไอเดียธุรกิจ 036','RM-201',DATE_ADD(CURDATE(), INTERVAL 6 DAY),'10:00:00','12:00:00',6,'["whiteboard_kit"]','CONFIRMED',2.00,1700.00,250.00,136.50,2086.50,NOW(),'ปากกาเคมี กระดาษ Post-it (งาน 036)'),
(18,'BK-2026-018','สมาชิกทีมพัฒนา (รหัส 035)','089-876-5432','student035@spacehub.com','ทีมพัฒนาแอป 035','อบรม AI Machine Learning 035','RM-202',DATE_ADD(CURDATE(), INTERVAL 6 DAY),'14:00:00','18:00:00',20,'["projector_4k","sound_eng","coffee"]','CONFIRMED',4.00,4800.00,3350.00,570.50,8720.50,NOW(),'โปรเจคเตอร์ความละเอียดสูง 4K (งาน 035)'),
(19,'BK-2026-019','ศุภนัฐ จันทร์เปรม (รหัส 036)','081-234-5678','suppanat036@spacehub.com','ทีมพัฒนาแอป 036','การแข่งขัน E-Sports ทีม 036','RM-301',DATE_ADD(CURDATE(), INTERVAL 7 DAY),'09:00:00','17:00:00',15,'["sound_eng"]','CONFIRMED',8.00,5200.00,600.00,406.00,6206.00,NOW(),'อินเทอร์เน็ตความเร็วสูง gigabit (งาน 036)'),
(20,'BK-2026-020','สมาชิกทีมพัฒนา (รหัส 035)','089-876-5432','student035@spacehub.com','ทีมพัฒนาแอป 035','ฉลองความสำเร็จปิดโครงการ 035','RM-302',DATE_ADD(CURDATE(), INTERVAL 7 DAY),'11:00:00','14:00:00',10,'["coffee","sound_eng"]','CONFIRMED',3.00,1740.00,1800.00,247.80,3787.80,NOW(),'จัดเลี้ยงอาหารว่าง (งาน 035)')
ON DUPLICATE KEY UPDATE `booking_code`=VALUES(`booking_code`), `status`=VALUES(`status`);
