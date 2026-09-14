<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpaceHub - ระบบจองห้องประชุมและพื้นที่ทำงานอัจฉริยะ (CoSpace Booking)</title>
  <meta name="description" content="ระบบจองห้องประชุมและพื้นที่ทำงาน Co-working Space สถาปัตยกรรม OOP+OOAD 3 Class ใช้งานได้จริง ไม่มีหน้า Login พร้อมระบบป้องกันเวลาชนกันและใบเสร็จดิจิทัล">
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Custom Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Site Header -->
  <header class="site-header">
    <div class="container nav-wrapper">
      <a href="index.php" class="brand">
        <div class="brand-icon">
          <svg viewBox="0 0 24 24">
            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zM7 10h2v7H7zm4-3h2v10h-2zm4 6h2v4h-2z"/>
          </svg>
        </div>
        <div class="brand-text">
          <h1>SpaceHub</h1>
          <p>Smart Space & Meeting Room Booking</p>
        </div>
      </a>

      <!-- Navigation Tabs -->
      <nav class="nav-tabs" role="tablist">
        <button class="nav-btn active" data-tab="booking" id="tabBooking">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
          <span>จองห้องประชุม</span>
        </button>
        <button class="nav-btn" data-tab="schedule" id="tabSchedule">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="M3 9h18M9 21V9"></path></svg>
          <span>ตารางเวลาห้อง</span>
        </button>
        <button class="nav-btn" data-tab="lookup" id="tabLookup">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <span>ค้นหา / ยกเลิกการจอง</span>
        </button>
        <button class="nav-btn" data-tab="stats" id="tabStats">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
          <span>สถิติภาพรวม</span>
        </button>
      </nav>

      <!-- Operational Status Badge -->
      <div class="header-badge">
        <span class="pulse-dot"></span>
        <span>ระบบพร้อมใช้งาน (Direct Booking)</span>
      </div>
    </div>
  </header>

  <!-- Main Container -->
  <main class="container">

    <!-- VIEW 1: BOOKING VIEW (DEFAULT) -->
    <section id="booking-view" class="tab-view active">
      <!-- Hero Banner -->
      <div class="hero-banner">
        <h2 class="hero-title">พื้นที่สร้างสรรค์และห้องประชุมระดับพรีเมียม</h2>
        <p class="hero-subtitle">
          เลือกช่วงเวลาและห้องที่ตอบโจทย์การทำงานของคุณได้ทันที ไม่ต้องสมัครสมาชิกหรือล็อกอิน ระบบคำนวณราคาและป้องกันเวลาชนกันแบบอัตโนมัติ
        </p>
      </div>

      <!-- Filter / Time Selection Bar -->
      <div class="filter-card">
        <div class="filter-grid">
          <div class="form-group">
            <label class="form-label" for="filterDate">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
              วันที่ต้องการใช้งาน
            </label>
            <input type="date" id="filterDate" class="form-input" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="filterStartTime">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              เวลาเริ่มต้น
            </label>
            <select id="filterStartTime" class="form-select">
              <option value="08:00">08:00 น.</option>
              <option value="09:00" selected>09:00 น.</option>
              <option value="10:00">10:00 น.</option>
              <option value="11:00">11:00 น.</option>
              <option value="12:00">12:00 น.</option>
              <option value="13:00">13:00 น.</option>
              <option value="14:00">14:00 น.</option>
              <option value="15:00">15:00 น.</option>
              <option value="16:00">16:00 น.</option>
              <option value="17:00">17:00 น.</option>
              <option value="18:00">18:00 น.</option>
              <option value="19:00">19:00 น.</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="filterEndTime">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              เวลาสิ้นสุด
            </label>
            <select id="filterEndTime" class="form-select">
              <option value="09:00">09:00 น.</option>
              <option value="10:00">10:00 น.</option>
              <option value="11:00">11:00 น.</option>
              <option value="12:00" selected>12:00 น.</option>
              <option value="13:00">13:00 น.</option>
              <option value="14:00">14:00 น.</option>
              <option value="15:00">15:00 น.</option>
              <option value="16:00">16:00 น.</option>
              <option value="17:00">17:00 น.</option>
              <option value="18:00">18:00 น.</option>
              <option value="19:00">19:00 น.</option>
              <option value="20:00">20:00 น.</option>
              <option value="21:00">21:00 น.</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="filterAttendees">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
              ผู้เข้าร่วม (คน)
            </label>
            <input type="number" id="filterAttendees" class="form-input" value="4" min="1" max="50">
          </div>

          <button id="btnCheckAvailability" class="btn-primary" type="button">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            ตรวจห้องว่าง
          </button>
        </div>
      </div>

      <!-- Room Selection Section -->
      <div class="section-title">
        <span>รายการห้องประชุมและพื้นที่ทั้งหมด</span>
        <span id="roomCountBadge">ระบบแสดงสถานะห้องว่างแบบเรียลไทม์</span>
      </div>

      <div id="roomsContainer" class="rooms-grid">
        <!-- Room Cards will be injected dynamically by app.js -->
        <div style="grid-column: 1/-1; text-align:center; padding: 40px; color: var(--text-muted);">
          กำลังโหลดข้อมูลห้องประชุม...
        </div>
      </div>
    </section>

    <!-- VIEW 2: SCHEDULE TIMELINE VIEW -->
    <section id="schedule-view" class="tab-view">
      <div class="hero-banner">
        <h2 class="hero-title">ตารางการใช้ห้องประชุมประจำวัน</h2>
        <p class="hero-subtitle">ตรวจสอบช่วงเวลาว่างและการใช้งานของแต่ละห้องในรูปแบบไทม์ไลน์ภาพรวม</p>
      </div>

      <div class="filter-card" style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
          <label class="form-label" for="scheduleDateInput" style="margin-bottom:0;">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            เลือกวันที่ดูตาราง:
          </label>
          <input type="date" id="scheduleDateInput" class="form-input" style="max-width:240px;">
        </div>
      </div>

      <div id="timelineContainer" class="timeline-container">
        <!-- Timeline Table rendered via app.js -->
      </div>
    </section>

    <!-- VIEW 3: LOOKUP & MANAGE VIEW -->
    <section id="lookup-view" class="tab-view">
      <div class="hero-banner">
        <h2 class="hero-title">ค้นหาและจัดการรายการจอง</h2>
        <p class="hero-subtitle">กรอกรหัสอ้างอิงการจอง (เช่น BK-2026-XXXX) หรือเบอร์โทรศัพท์ เพื่อดูรายละเอียดและพิมพ์ใบเสร็จ หรือขอยกเลิกการจอง</p>
      </div>

      <div class="filter-card" style="max-width:680px; margin: 0 auto 30px;">
        <div style="display:flex; gap:12px;">
          <input type="text" id="lookupInput" class="form-input" placeholder="พิมพ์รหัสการจอง เช่น BK-2026-A101 หรือเบอร์โทรศัพท์">
          <button id="btnSearchBooking" class="btn-primary" type="button">
            ค้นหาข้อมูล
          </button>
        </div>
      </div>

      <div id="lookupResults" style="max-width:800px; margin: 0 auto;">
        <div style="text-align:center; padding: 40px; color: var(--text-muted);">
          พิมพ์รหัสการจองหรือเบอร์โทรศัพท์เพื่อค้นหา
        </div>
      </div>
    </section>

    <!-- VIEW 4: ANALYTICS DASHBOARD -->
    <section id="stats-view" class="tab-view">
      <div class="hero-banner">
        <h2 class="hero-title">สถิติและภาพรวมการใช้งานระบบ (Analytics)</h2>
        <p class="hero-subtitle">แสดงข้อมูลการจอง รายได้รวม และการใช้งานห้องประชุมเพื่อการบริหารจัดการ</p>
      </div>

      <div class="stats-cards-grid">
        <div class="stat-card">
          <div class="stat-icon emerald">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
          </div>
          <div class="stat-content">
            <div id="statTotalBookings" class="stat-number">0</div>
            <div class="stat-label">ยอดจองทั้งหมด</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon cyan">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
          </div>
          <div class="stat-content">
            <div id="statConfirmed" class="stat-number">0</div>
            <div class="stat-label">รายการที่ยืนยันแล้ว</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon amber">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
          </div>
          <div class="stat-content">
            <div id="statTodayBookings" class="stat-number">0</div>
            <div class="stat-label">การจองวันนี้</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon purple">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
          </div>
          <div class="stat-content">
            <div id="statRevenue" class="stat-number">฿0</div>
            <div class="stat-label">รายได้รวมสะสม</div>
          </div>
        </div>
      </div>

      <!-- Recent Bookings Table -->
      <div class="data-table-container">
        <div class="table-header-bar">
          <h3 style="font-size:1.15rem;font-weight:700;">รายการจองล่าสุดในระบบ</h3>
          <span style="font-size:0.85rem;color:var(--text-muted);">อัปเดตอัตโนมัติจาก data/bookings.json</span>
        </div>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>รหัสการจอง</th>
                <th>ผู้จอง / องค์กร</th>
                <th>ห้องประชุม</th>
                <th>วันและเวลา</th>
                <th>ผู้เข้าร่วม</th>
                <th>ยอดชำระ</th>
                <th>สถานะ</th>
              </tr>
            </thead>
            <tbody id="recentBookingsTableBody">
              <!-- Rendered via app.js -->
            </tbody>
          </table>
        </div>
      </div>
    </section>

  </main>

  <!-- BOOKING MODAL -->
  <div id="bookingModal" class="modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-card">
      <div class="modal-header">
        <h3 class="modal-title">กรอกข้อมูลยืนยันการจองห้องประชุม</h3>
        <button id="btnModalClose" class="modal-close-btn" type="button" aria-label="Close">✕</button>
      </div>

      <div class="modal-body">
        <!-- Room Selection Summary Banner -->
        <div class="booking-summary-banner">
          <div class="booking-summary-item">
            <span class="label">ห้องที่เลือก</span>
            <span id="modalRoomName" class="value">-</span>
          </div>
          <div class="booking-summary-item">
            <span class="label">ประเภทห้อง</span>
            <span id="modalRoomType" class="value">-</span>
          </div>
          <div class="booking-summary-item">
            <span class="label">วันและช่วงเวลา</span>
            <span id="modalDateTime" class="value" style="color:var(--accent-cyan);">-</span>
          </div>
        </div>

        <form id="bookingForm">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="formCustomerName">ชื่อ - นามสกุล ผู้จอง *</label>
              <input type="text" id="formCustomerName" class="form-input" placeholder="เช่น ดร. ณัฐพล จารุศิริ" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="formCustomerPhone">เบอร์โทรศัพท์ติดต่อ *</label>
              <input type="tel" id="formCustomerPhone" class="form-input" placeholder="เช่น 081-234-5678" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="formCustomerEmail">อีเมลสำหรับรับใบยืนยัน *</label>
              <input type="email" id="formCustomerEmail" class="form-input" placeholder="เช่น contact@company.com" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="formOrganization">หน่วยงาน / บริษัท / กลุ่มงาน</label>
              <input type="text" id="formOrganization" class="form-input" placeholder="เช่น Tech Startup Group หรือ บุคคลทั่วไป">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="formAttendees">จำนวนผู้เข้าร่วมจริง (คน) *</label>
              <input type="number" id="formAttendees" class="form-input" min="1" value="4" required onchange="onAttendeeChange()">
            </div>
            <div class="form-group">
              <label class="form-label" for="formPurpose">วัตถุประสงค์การใช้งาน</label>
              <input type="text" id="formPurpose" class="form-input" placeholder="เช่น สัมภาษณ์งาน, ประชุมประจำเดือน, สัมมนา">
            </div>
          </div>

          <!-- Add-on Services Checklist -->
          <div class="addons-section">
            <div class="addons-title">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
              <span>บริการเสริมและอุปกรณ์เพิ่มเติม (Add-on Services)</span>
            </div>
            <div id="addonsListContainer" class="addons-grid">
              <!-- Addons checkboxes injected via app.js -->
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="formNotes">หมายเหตุหรือความต้องการเพิ่มเติม</label>
            <textarea id="formNotes" class="form-input" rows="2" placeholder="เช่น ขอโต๊ะจัดแบบรูปตัว U หรือขอต่อโปรเจกเตอร์ Mac"></textarea>
          </div>

          <!-- Real-time Cost Calculation Box -->
          <div class="calc-breakdown">
            <div class="calc-row">
              <span>ระยะเวลาใช้งาน:</span>
              <strong id="calcDuration">0 ชั่วโมง</strong>
            </div>
            <div class="calc-row">
              <span>ค่าบริการห้องพื้นฐาน:</span>
              <span id="calcBasePrice">฿0.00</span>
            </div>
            <div class="calc-row">
              <span>ค่าบริการเสริม (Add-ons):</span>
              <span id="calcAddonsPrice">฿0.00</span>
            </div>
            <div class="calc-row">
              <span>ภาษีมูลค่าเพิ่ม (VAT 7%):</span>
              <span id="calcTaxPrice">฿0.00</span>
            </div>
            <div class="calc-row total">
              <span>ยอดชำระสุทธิ (Total Amount):</span>
              <span id="calcTotalPrice" class="price">฿0.00</span>
            </div>
          </div>

          <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:20px;">
            <button type="button" class="btn-secondary" onclick="closeModal()">ยกเลิก</button>
            <button type="submit" id="btnSubmitBooking" class="btn-primary">ยืนยันการจอง (Confirm Booking)</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- DIGITAL VOUCHER MODAL -->
  <div id="voucherModal" class="modal-backdrop" role="dialog" aria-modal="true">
    <div class="modal-card" style="max-width:640px;">
      <div class="modal-header no-print">
        <h3 class="modal-title">ใบยืนยันการจองดิจิทัล (Digital Voucher)</h3>
        <button class="modal-close-btn" onclick="closeVoucherModal()">✕</button>
      </div>
      <div class="modal-body">
        <div class="voucher-card printable-voucher">
          <div class="voucher-header">
            <div>
              <div style="font-size:0.8rem;color:var(--text-muted);letter-spacing:1px;text-transform:uppercase;">Booking Voucher</div>
              <h2 style="font-size:1.6rem;margin-top:2px;">SpaceHub Reservations</h2>
            </div>
            <div id="vCode" class="voucher-code-badge">BK-XXXX-XXXX</div>
          </div>

          <div class="voucher-details-grid">
            <div class="voucher-item">
              <div class="title">ผู้จอง (Customer)</div>
              <div id="vCustomer" class="value">-</div>
            </div>
            <div class="voucher-item">
              <div class="title">เบอร์ติดต่อ / อีเมล</div>
              <div id="vContact" class="value" style="font-size:0.9rem;">-</div>
            </div>
            <div class="voucher-item">
              <div class="title">องค์กร / หน่วยงาน</div>
              <div id="vOrg" class="value">-</div>
            </div>
            <div class="voucher-item">
              <div class="title">ห้องประชุมที่จอง</div>
              <div id="vRoom" class="value" style="color:var(--accent-cyan);">-</div>
            </div>
            <div class="voucher-item" style="grid-column: 1/-1;">
              <div class="title">วันและเวลาที่จอง</div>
              <div id="vDateTime" class="value">-</div>
            </div>
            <div class="voucher-item">
              <div class="title">จำนวนผู้เข้าร่วม</div>
              <div id="vAttendees" class="value">-</div>
            </div>
            <div class="voucher-item">
              <div class="title">วัตถุประสงค์</div>
              <div id="vPurpose" class="value">-</div>
            </div>
          </div>

          <div style="background:rgba(0,0,0,0.25);border-radius:var(--radius-sm);padding:14px;margin-bottom:20px;">
            <div style="font-size:0.78rem;color:var(--text-muted);margin-bottom:6px;">บริการเสริมที่เลือก:</div>
            <div id="vAddonsList" style="font-size:0.88rem;color:#fff;">-</div>
          </div>

          <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(255,255,255,0.1);padding-top:16px;">
            <div>
              <div style="font-size:0.75rem;color:var(--text-muted);">ยอดรวมทั้งสิ้น (รวม VAT 7%)</div>
              <div id="vTotal" style="font-size:1.6rem;font-weight:800;color:var(--accent-emerald);font-family:var(--font-display);">฿0.00</div>
            </div>
            <div style="text-align:right;">
              <div style="font-size:0.72rem;color:var(--text-dim);">สถานะ: ยืนยันสิทธิ์แล้ว</div>
              <div style="font-size:0.72rem;color:var(--text-dim);">กรุณาแสดงรหัสนี้เมื่อเข้าใช้งาน</div>
            </div>
          </div>
        </div>

        <div class="no-print" style="display:flex;justify-content:flex-end;gap:12px;margin-top:24px;">
          <button class="btn-secondary" onclick="window.print()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            พิมพ์ใบยืนยัน (Print Voucher)
          </button>
          <button class="btn-primary" onclick="closeVoucherModal()">เรียบร้อย (Done)</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Notification Container -->
  <div id="toastContainer" class="toast-container"></div>

  <!-- Application Script -->
  <script src="assets/js/app.js"></script>
</body>
</html>
