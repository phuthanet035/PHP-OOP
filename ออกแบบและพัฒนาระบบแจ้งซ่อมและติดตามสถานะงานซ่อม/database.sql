-- Smart IT Helpdesk & Notification System
-- Database Schema & Initial Data
-- Conforms strictly to ER Diagram (Diagram 2) and Ticket State Machine (Diagram 3)

CREATE DATABASE IF NOT EXISTS `smart_it_helpdesk_oop` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smart_it_helpdesk_oop`;

-- Disable Foreign Key checks during recreation
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `ratings`;
DROP TABLE IF EXISTS `status_logs`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `tickets`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- 1. USERS TABLE
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'technician', 'admin') NOT NULL DEFAULT 'user',
  `line_user_id` VARCHAR(100) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. CATEGORIES TABLE
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. TICKETS TABLE
CREATE TABLE `tickets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `technician_id` INT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `status` ENUM('Open', 'Assigned', 'InProgress', 'Resolved', 'Closed') NOT NULL DEFAULT 'Open',
  `priority` ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `resolved_at` DATETIME NULL,
  `closed_at` DATETIME NULL,
  CONSTRAINT `fk_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tickets_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_tickets_technician` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. COMMENTS TABLE
CREATE TABLE `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `body` TEXT NOT NULL,
  `image_path` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_comments_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. STATUS_LOGS TABLE
CREATE TABLE `status_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL,
  `changed_by` INT NOT NULL,
  `from_status` ENUM('Open', 'Assigned', 'InProgress', 'Resolved', 'Closed') NOT NULL,
  `to_status` ENUM('Open', 'Assigned', 'InProgress', 'Resolved', 'Closed') NOT NULL,
  `note` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_status_logs_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_status_logs_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. RATINGS TABLE
CREATE TABLE `ratings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ticket_id` INT NOT NULL UNIQUE,
  `score` INT NOT NULL CHECK (`score` BETWEEN 1 AND 5),
  `feedback` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ratings_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ==========================================================
-- SEED DATA (Default Accounts, Categories, Sample Tickets)
-- ==========================================================
-- Passwords: password123 (hashed using PASSWORD_BCRYPT)
-- $2y$10$wT0vR3tK3jO9bF7eU1ZzOu3V4uC8sE0wM6yK9bT4rG1hA2sD5fE6m -> generated securely in PHP

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `line_user_id`, `created_at`) VALUES
(1, 'ผู้ดูแลระบบ (Admin IT)', 'admin@helpdesk.com', '$2y$10$Z3U60WlR9W3m5sO1r6sAfeN984Bup6zL519pfx34M8iEtiOQo9g1O', 'admin', 'Uadmin1234567890', NOW()),
(2, 'ช่างเทคนิค สมศักดิ์ (Senior Tech)', 'tech@helpdesk.com', '$2y$10$Z3U60WlR9W3m5sO1r6sAfeN984Bup6zL519pfx34M8iEtiOQo9g1O', 'technician', 'Utech1234567890', NOW()),
(3, 'ช่างเทคนิค สุรชัย (Network Tech)', 'surachai@helpdesk.com', '$2y$10$Z3U60WlR9W3m5sO1r6sAfeN984Bup6zL519pfx34M8iEtiOQo9g1O', 'technician', 'Usurachai123456', NOW()),
(4, 'อาจารย์กิตติศักดิ์ (User)', 'user@helpdesk.com', '$2y$10$Z3U60WlR9W3m5sO1r6sAfeN984Bup6zL519pfx34M8iEtiOQo9g1O', 'user', 'Uuser1234567890', NOW()),
(5, 'เจ้าหน้าที่ นภาพร (User Staff)', 'naphaporn@helpdesk.com', '$2y$10$Z3U60WlR9W3m5sO1r6sAfeN984Bup6zL519pfx34M8iEtiOQo9g1O', 'user', NULL, NOW());

INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'คอมพิวเตอร์และจอภาพ (Hardware)', 'เครื่อง PC, All-in-One, เปิดไม่ติด, จอฟ้า, พัดลมเสียงดัง, แหล่งจ่ายไฟมีปัญหา'),
(2, 'ระบบเครือข่ายและอินเทอร์เน็ต (Network)', 'เชื่อมต่อ Wi-Fi ไม่ได้, สาย LAN หลุด/ชำรุด, อินเทอร์เน็ตช้า, ระบบ VPN ขัดข้อง'),
(3, 'เครื่องพิมพ์และสแกนเนอร์ (Printer/Scanner)', 'เครื่องปริ้นกระดาษติด, หมึกหมด, สแกนเอกสารไม่ได้, เชื่อมต่อเครื่องพิมพ์เครือข่ายไม่ผ่าน'),
(4, 'ซอฟต์แวร์และโปรแกรมระบบ (Software/OS)', 'Windows อัปเดตล้มเหลว, MS Office ใช้งานไม่ได้, โปรแกรมติดไวรัส, ลงโปรแกรมเพิ่มเติม'),
(5, 'อุปกรณ์ห้องเรียนและห้องประชุม (AV Equipment)', 'โปรเจกเตอร์ภาพไม่ขึ้น, ไมโครโฟนไม่มีเสียง, ลำโพงเสียงแตก, กล้องประชุมทางไกลขัดข้อง');

-- Sample Tickets in Various State Machine Stages
-- Ticket 1: Closed (Open -> Assigned -> InProgress -> Resolved -> Closed) with Rating
INSERT INTO `tickets` (`id`, `user_id`, `category_id`, `technician_id`, `title`, `description`, `status`, `priority`, `created_at`, `updated_at`, `resolved_at`, `closed_at`) VALUES
(1, 4, 1, 2, 'คอมพิวเตอร์ห้องปฏิบัติการ 301 เปิดไม่ติด มีเสียงบี๊บยาว', 'กดสวิตช์เปิดเครื่องแล้วไฟหน้าเคสไม่สว่าง มีเสียงบี๊บยาวต่อเนื่อง 3 ครั้ง พัดลม CPU หมุนเบาๆ ต้องการใช้ในการสอนพรุ่งนี้เช้า', 'Closed', 'High', DATE_SUB(NOW(), INTERVAL 3 DAY), NOW(), DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO `comments` (`id`, `ticket_id`, `user_id`, `body`, `image_path`, `created_at`) VALUES
(1, 1, 4, 'แนบรูปภาพตัวเครื่องด้านหลัง สายไฟเสียบแน่นแล้วครับ', NULL, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 1, 2, 'รับเรื่องแล้วครับ ตรวจสอบพบ RAM หลวมและมีฝุ่น ได้ทำการถอดทำความสะอาดและใส่กลับ ใช้งานได้ปกติเรียบร้อยครับ', 'sample_repair_ram.jpg', DATE_SUB(NOW(), INTERVAL 2 DAY));

INSERT INTO `status_logs` (`id`, `ticket_id`, `changed_by`, `from_status`, `to_status`, `note`, `created_at`) VALUES
(1, 1, 1, 'Open', 'Assigned', 'มอบหมายงานให้ช่างสมศักดิ์', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 1, 2, 'Assigned', 'InProgress', 'ช่างรับงานและกำลังเดินทางไปตรวจสอบที่ห้อง 301', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 1, 2, 'InProgress', 'Resolved', 'ซ่อมแซมเสร็จสิ้น ตรวจสอบเปิดติดเข้าสู่วินโดวส์ได้ตามปกติ', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4, 1, 4, 'Resolved', 'Closed', 'ผู้แจ้งตรวจสอบผลงานแล้วเรียบร้อย พึงพอใจมาก', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO `ratings` (`id`, `ticket_id`, `score`, `feedback`, `created_at`) VALUES
(1, 1, 5, 'ช่างมาไวมาก แก้ปัญหาตรงจุด สุภาพเรียบร้อย ยอดเยี่ยมครับ', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- Ticket 2: InProgress (Assigned -> InProgress)
INSERT INTO `tickets` (`id`, `user_id`, `category_id`, `technician_id`, `title`, `description`, `status`, `priority`, `created_at`, `updated_at`, `resolved_at`, `closed_at`) VALUES
(2, 5, 2, 3, 'สัญญาณ Wi-Fi ชั้น 4 อาคาร 15 หลุดบ่อยมาก', 'ช่วงบ่ายสัญญาณ Wi-Fi ขาดหายต่อเนื่อง โน้ตบุ๊กต่อแล้วขึ้น No Internet ทำงานส่งเอกสารไม่ได้', 'InProgress', 'Urgent', DATE_SUB(NOW(), INTERVAL 6 HOUR), NOW(), NULL, NULL);

INSERT INTO `comments` (`id`, `ticket_id`, `user_id`, `body`, `image_path`, `created_at`) VALUES
(3, 2, 3, 'กำลังตรวจสอบ Access Point ตัวที่ AP-402 และเช็คสายสัญญาณ Switch ชั้น 4 ครับ', NULL, DATE_SUB(NOW(), INTERVAL 3 HOUR));

INSERT INTO `status_logs` (`id`, `ticket_id`, `changed_by`, `from_status`, `to_status`, `note`, `created_at`) VALUES
(5, 2, 1, 'Open', 'Assigned', 'มอบหมายช่างสุรชัย (Network)', DATE_SUB(NOW(), INTERVAL 5 HOUR)),
(6, 2, 3, 'Assigned', 'InProgress', 'ช่างสุรชัยรับงาน กำลังเข้าตรวจสอบตู้ Rack ชั้น 4', DATE_SUB(NOW(), INTERVAL 3 HOUR));

-- Ticket 3: Assigned (Admin assigned tech, tech waiting to accept)
INSERT INTO `tickets` (`id`, `user_id`, `category_id`, `technician_id`, `title`, `description`, `status`, `priority`, `created_at`, `updated_at`, `resolved_at`, `closed_at`) VALUES
(3, 4, 3, 2, 'เครื่องพิมพ์ HP LaserJet ห้องธุรการ กระดาษติดด้านในดึงไม่ออก', 'มีกระดาษ A4 ติดอยู่ใต้ชุดลูกกลิ้งความร้อนด้านใน มีไฟสีส้มกะพริบเตือน ไม่กล้าดึงแรงกลัวอะไหล่หัก', 'Assigned', 'Medium', DATE_SUB(NOW(), INTERVAL 2 HOUR), NOW(), NULL, NULL);

INSERT INTO `status_logs` (`id`, `ticket_id`, `changed_by`, `from_status`, `to_status`, `note`, `created_at`) VALUES
(7, 3, 1, 'Open', 'Assigned', 'แอดมินมอบหมายงานให้ช่างสมศักดิ์เข้าดำเนินการ', DATE_SUB(NOW(), INTERVAL 1 HOUR));

-- Ticket 4: Open (Newly created by user, waiting for admin assignment)
INSERT INTO `tickets` (`id`, `user_id`, `category_id`, `technician_id`, `title`, `description`, `status`, `priority`, `created_at`, `updated_at`, `resolved_at`, `closed_at`) VALUES
(4, 5, 5, NULL, 'โปรเจกเตอร์ห้องประชุม 2 ภาพเป็นสีชมพูและกะพริบ', 'ต่อสาย HDMI แล้วภาพฉายขึ้นจอเพดานเป็นสีเพี้ยนออกชมพู กะพริบเป็นระยะ สลับสายแล้วยังเป็นเหมือนเดิม', 'Open', 'Medium', DATE_SUB(NOW(), INTERVAL 30 MINUTE), NOW(), NULL, NULL);

INSERT INTO `status_logs` (`id`, `ticket_id`, `changed_by`, `from_status`, `to_status`, `note`, `created_at`) VALUES
(8, 4, 5, 'Open', 'Open', 'ผู้ใช้สร้างคำขอแจ้งซ่อมใหม่ในระบบ', DATE_SUB(NOW(), INTERVAL 30 MINUTE));
