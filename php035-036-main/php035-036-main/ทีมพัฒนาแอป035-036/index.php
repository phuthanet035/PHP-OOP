<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SpaceHub - ระบบจองห้องประชุมและพื้นที่ทำงานอัจฉริยะ (CoSpace Booking)</title>
  <meta name="description" content="ระบบจองห้องประชุมและพื้นที่ทำงาน Co-working Space สถาปัตยกรรม OOP 3 Classes โดย รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม">
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <!-- Custom Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- ============================================================================== -->
  <!-- ส่วนหัวเว็บไซต์ (Site Header & Navigation Bar) -->
  <!-- พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม -->
  <!-- ============================================================================== -->
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
        <button class="nav-btn" data-tab="admin" id="tabAdmin">
          <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
          <span>หลังบ้าน (Admin)</span>
        </button>
      </nav>

      <!-- Operational Status & User Auth Badge -->
      <div style="display:flex;align-items:center;gap:16px;">
        <div class="header-badge">
          <span class="pulse-dot"></span>
          <span id="dbStatusText">ระบบพร้อมใช้งาน (MySQL Connected)</span>
        </div>
        <div id="userAuthWidget">
          <button class="user-auth-btn" onclick="openAuthModal('login')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <span>เข้าสู่ระบบ / สมัครสมาชิก</span>
          </button>
        </div>
      </div>
    </div>
  </header>

  <!-- ============================================================================== -->
  <!-- ส่วนเนื้อหาหลัก (Main Content Section) -->
  <!-- ============================================================================== -->
  <main class="container">

    <!-- VIEW 1: BOOKING VIEW (หน้าหลักเลือกการจองห้องประชุม - พัฒนาโดย รหัส 035) -->
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
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="filterAttendees">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
              จำนวนผู้เข้าร่วม (คน)
            </label>
            <input type="number" id="filterAttendees" class="form-input" min="1" max="100" value="4">
          </div>
        </div>
      </div>

      <!-- Categories & Search Filter -->
      <div class="category-search-bar">
        <div class="category-pills">
          <button class="pill active" data-category="all">ทั้งหมด (All Spaces)</button>
          <button class="pill" data-category="Meeting Room">Meeting Room</button>
          <button class="pill" data-category="Boardroom">Boardroom</button>
          <button class="pill" data-category="Creative Studio">Creative Studio</button>
          <button class="pill" data-category="Focus Pod">Focus Pod</button>
          <button class="pill" data-category="Seminar Hall">Seminar Hall</button>
          <button class="pill" data-category="Open Space Zone">Open Space Zone</button>
        </div>
        <div class="search-box">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input type="text" id="searchInput" class="search-input" placeholder="ค้นหาชื่อห้อง อุปกรณ์ หรือประเภท...">
        </div>
      </div>

      <!-- Room Cards Grid -->
      <div id="roomsGrid" class="room-grid">
        <!-- Injected via app.js -->
      </div>
    </section>

    <!-- VIEW 2: SCHEDULE VIEW (ตารางเวลาใช้งาน - พัฒนาร่วมกัน 035 & 036) -->
    <section id="schedule-view" class="tab-view">
      <div class="hero-banner">
        <h2 class="hero-title">ตารางการใช้งานห้องประชุมรายวัน (Room Schedule Grid)</h2>
        <p class="hero-subtitle">
          ตรวจสอบความพร้อมและช่วงเวลาที่มีการจองแล้วของห้องประชุมทั้งหมดในแต่ละวัน
        </p>
      </div>

      <div class="filter-card">
        <div class="form-group" style="max-width:300px;">
          <label class="form-label" for="scheduleDate">เลือกวันที่ต้องการดูตาราง</label>
          <input type="date" id="scheduleDate" class="form-input" onchange="renderScheduleGrid()">
        </div>
      </div>

      <div class="data-table-container" style="overflow-x:auto;">
        <table class="schedule-table">
          <thead>
            <tr>
              <th style="min-width:180px;">ห้องประชุม</th>
              <th>08:00</th>
              <th>09:00</th>
              <th>10:00</th>
              <th>11:00</th>
              <th>12:00</th>
              <th>13:00</th>
              <th>14:00</th>
              <th>15:00</th>
              <th>16:00</th>
              <th>17:00</th>
              <th>18:00</th>
              <th>19:00</th>
            </tr>
          </thead>
          <tbody id="scheduleTableBody">
            <!-- Injected via app.js -->
          </tbody>
        </table>
      </div>
    </section>

    <!-- VIEW 3: LOOKUP / CANCEL VIEW (ค้นหาและยกเลิกการจอง - พัฒนาโดย รหัส 036 ศุภนัฐ จันทร์เปรม) -->
    <section id="lookup-view" class="tab-view">
      <div class="hero-banner">
        <h2 class="hero-title">ค้นหาและตรวจสอบรายละเอียดการจอง (Booking Lookup)</h2>
        <p class="hero-subtitle">
          กรอกรหัสการจอง (เช่น BK-2026-001) เพื่อตรวจสอบสถานะ รายละเอียดการเงิน หรือทำการยกเลิกการจอง
        </p>
      </div>

      <div class="lookup-card">
        <div class="lookup-input-group">
          <input type="text" id="lookupCodeInput" class="lookup-input" placeholder="พิมพ์รหัสการจอง เช่น BK-2026-001">
          <button class="btn-primary" onclick="handleLookupSubmit()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            ค้นหารายการ
          </button>
        </div>
        <div id="lookupResultArea" class="lookup-result"></div>
      </div>
    </section>

    <!-- VIEW 4: STATS VIEW (สถิติภาพรวมระบบ - พัฒนาร่วมกัน 035 & 036) -->
    <section id="stats-view" class="tab-view">
      <div class="hero-banner">
        <h2 class="hero-title">สถิติภาพรวมของระบบ (Dashboard & Analytics)</h2>
        <p class="hero-subtitle">
          สรุปยอดผู้เข้าใช้งาน รายได้สะสม และจำนวนการจองทั้งหมดที่ประมวลผลผ่าน OOP Class Architecture
        </p>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
          </div>
          <div class="stat-content">
            <div id="statTotalRooms" class="stat-number">0</div>
            <div class="stat-label">ห้องประชุมทั้งหมด</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon green">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
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
            <div class="stat-label">การจองรวมทั้งหมด</div>
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
          <span style="font-size:0.85rem;color:var(--text-muted);">อัปเดตแบบ Real-time จาก MySQL Database</span>
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

    <!-- VIEW 5: ADMIN BACK-OFFICE VIEW (ระบบหลังบ้านสำหรับผู้ดูแลระบบ - พัฒนาร่วมกัน 035 & 036) -->
    <section id="admin-view" class="tab-view">
      <div class="hero-banner" style="background: linear-gradient(135deg, rgba(239,68,68,0.15) 0%, rgba(139,92,246,0.15) 100%); border-color: rgba(239,68,68,0.3);">
        <h2 class="hero-title" style="color:#f87171;">ระบบบริหารจัดการการจองหลังบ้าน (Admin Back-office)</h2>
        <p class="hero-subtitle">
          จัดการอนุมัติ เปลี่ยนสถานะ หรือลบรายการจองของทั้ง 20 รายการทดสอบ (สลับกันโดย 036 ศุภนัฐ และ 035) ได้ทันทีในระบบ
        </p>
      </div>

      <div class="filter-card">
        <div style="display:flex; gap:16px; align-items:center; flex-wrap:wrap;">
          <div class="form-group" style="flex:1; min-width:240px;">
            <label class="form-label" for="adminSearchInput">ค้นหาด้วยรหัสการจอง หรือชื่อผู้จอง</label>
            <input type="text" id="adminSearchInput" class="form-input" placeholder="พิมพ์เช่น BK-2026 หรือ ศุภนัฐ..." oninput="renderAdminBookingsTable()">
          </div>
          <div class="form-group" style="width:220px;">
            <label class="form-label" for="adminStatusFilter">กรองตามสถานะ</label>
            <select id="adminStatusFilter" class="form-select" onchange="renderAdminBookingsTable()">
              <option value="ALL">ทั้งหมด (All Status)</option>
              <option value="CONFIRMED">CONFIRMED (ยืนยันแล้ว)</option>
              <option value="PENDING">PENDING (รออนุมัติ)</option>
              <option value="COMPLETED">COMPLETED (เสร็จสิ้น)</option>
              <option value="CANCELLED">CANCELLED (ยกเลิกแล้ว)</option>
            </select>
          </div>
          <button type="button" class="btn-primary" style="margin-top:22px;" onclick="loadRoomsAndCheckAvailability(); loadDashboardStats(); renderAdminBookingsTable();">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
            รีเฟรชข้อมูล
          </button>
        </div>
      </div>

      <div class="data-table-container">
        <div class="table-header-bar">
          <h3 style="font-size:1.15rem;font-weight:700;">รายการจองทั้งหมดในระบบ (20 รายการทดสอบ)</h3>
          <span id="adminTotalCountBadge" class="voucher-code-badge" style="background:rgba(59,130,246,0.2);color:#60a5fa;">0 รายการ</span>
        </div>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead>
              <tr>
                <th>รหัสการจอง</th>
                <th>ผู้จอง (รหัสทีม)</th>
                <th>เบอร์ / อีเมล</th>
                <th>ห้องประชุม</th>
                <th>วันและเวลา</th>
                <th>ผู้เข้าร่วม</th>
                <th>ราคารวม</th>
                <th>สถานะ</th>
                <th style="text-align:center;">จัดการ (Actions)</th>
              </tr>
            </thead>
            <tbody id="adminBookingsTableBody">
              <!-- Rendered via app.js -->
            </tbody>
          </table>
        </div>
      </div>
    </section>

  </main>

  <!-- ============================================================================== -->
  <!-- MODAL DIALOGS (หน้าต่างป๊อปอัปฟอร์มการจอง และ ใบยืนยันดิจิทัล) -->
  <!-- ============================================================================== -->

  <!-- BOOKING MODAL (ฟอร์มลงทะเบียนการจองห้องประชุม - พัฒนาโดย รหัส 036 ศุภนัฐ จันทร์เปรม) -->
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
              <input type="text" id="formCustomerName" class="form-input" placeholder="เช่น ศุภนัฐ จันทร์เปรม" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="formCustomerPhone">เบอร์โทรศัพท์ติดต่อ *</label>
              <input type="tel" id="formCustomerPhone" class="form-input" placeholder="เช่น 081-234-5678" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="formCustomerEmail">อีเมลสำหรับรับใบยืนยัน *</label>
              <input type="email" id="formCustomerEmail" class="form-input" placeholder="เช่น suppanat036@spacehub.com" required>
            </div>
            <div class="form-group">
              <label class="form-label" for="formOrganization">หน่วยงาน / บริษัท / กลุ่มงาน</label>
              <input type="text" id="formOrganization" class="form-input" placeholder="เช่น ทีมพัฒนาแอป 036 หรือ บุคคลทั่วไป">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label" for="formAttendees">จำนวนผู้เข้าร่วมจริง (คน) *</label>
              <input type="number" id="formAttendees" class="form-input" min="1" value="4" required onchange="onAttendeeChange()">
            </div>
            <div class="form-group">
              <label class="form-label" for="formPurpose">วัตถุประสงค์การใช้งาน</label>
              <input type="text" id="formPurpose" class="form-input" placeholder="เช่น ประชุมวางแผนโปรเจกต์ 036">
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
            <textarea id="formNotes" class="form-input" rows="2" placeholder="เช่น ขอปลั๊กไฟเพิ่ม 2 จุด"></textarea>
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

  <!-- DIGITAL VOUCHER MODAL (ใบเสร็จ/ใบยืนยันดิจิทัล - พัฒนาโดย รหัส 036 ศุภนัฐ จันทร์เปรม) -->
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
              <div id="vRoom" class="value">-</div>
            </div>
            <div class="voucher-item">
              <div class="title">วันและเวลาใช้งาน</div>
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

  <!-- USER LOGIN & REGISTER MODAL DIALOG (ป๊อปอัปเข้าสู่ระบบและสมัครสมาชิก - พัฒนาร่วมกัน 035 & 036) -->
  <div id="authModalOverlay" class="auth-modal-overlay">
    <div class="auth-modal-card">
      <button type="button" class="auth-modal-close" onclick="closeAuthModal()">&times;</button>
      
      <div class="auth-tabs">
        <button type="button" id="authTabLoginBtn" class="auth-tab-btn active" onclick="switchAuthTab('login')">เข้าสู่ระบบ (Login)</button>
        <button type="button" id="authTabRegisterBtn" class="auth-tab-btn" onclick="switchAuthTab('register')">สมัครสมาชิก (Register)</button>
      </div>

      <div id="authAlert" class="auth-alert"></div>

      <!-- Login Form -->
      <form id="loginForm" onsubmit="handleLoginSubmit(event)">
        <div class="form-group" style="margin-bottom:16px;">
          <label class="form-label" for="loginUsername">ชื่อบัญชี หรือ อีเมล</label>
          <input type="text" id="loginUsername" class="form-input" placeholder="ชื่อผู้ใช้งาน หรือ อีเมล" required>
        </div>
        <div class="form-group" style="margin-bottom:24px;">
          <label class="form-label" for="loginPassword">รหัสผ่าน</label>
          <div style="position:relative;">
            <input type="password" id="loginPassword" class="form-input" placeholder="••••••••" required style="padding-right:40px;">
            <button type="button" onclick="togglePasswordVisibility('loginPassword', this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:4px;" title="แสดง/ซ่อนรหัสผ่าน">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:12px;">
          เข้าสู่ระบบ (Login)
        </button>
      </form>

      <!-- Register Form -->
      <form id="registerForm" onsubmit="handleRegisterSubmit(event)" style="display:none;">
        <div class="form-group" style="margin-bottom:14px;">
          <label class="form-label" for="regUsername">ชื่อบัญชีผู้ใช้งาน (Username)</label>
          <input type="text" id="regUsername" class="form-input" placeholder="กรอกชื่อผู้ใช้งาน" required>
        </div>
        <div class="form-group" style="margin-bottom:14px;">
          <label class="form-label" for="regFullname">ชื่อ - นามสกุลจริง</label>
          <input type="text" id="regFullname" class="form-input" placeholder="เช่น ศุภนัฐ จันทร์เปรม" required>
        </div>
        <div class="form-group" style="margin-bottom:14px;">
          <label class="form-label" for="regEmail">อีเมล (Email Address)</label>
          <input type="email" id="regEmail" class="form-input" placeholder="example@spacehub.com" required>
        </div>
        <div class="form-group" style="margin-bottom:14px;">
          <label class="form-label" for="regPhone">เบอร์โทรศัพท์</label>
          <input type="tel" id="regPhone" class="form-input" placeholder="08x-xxx-xxxx">
        </div>
        <div class="form-group" style="margin-bottom:20px;">
          <label class="form-label" for="regPassword">รหัสผ่าน</label>
          <div style="position:relative;">
            <input type="password" id="regPassword" class="form-input" placeholder="กำหนดรหัสผ่าน 4 ตัวอักษรขึ้นไป" required style="padding-right:40px;">
            <button type="button" onclick="togglePasswordVisibility('regPassword', this)" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:4px;" title="แสดง/ซ่อนรหัสผ่าน">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:12px;">
          ลงทะเบียนสมาชิกใหม่
        </button>
      </form>
    </div>
  </div>

  <!-- Toast Notification Container -->
  <div id="toastContainer" class="toast-container"></div>

  <!-- ============================================================================== -->
  <!-- ส่วนท้ายเว็บไซต์ (Footer Component & Attribution) -->
  <!-- ============================================================================== -->
  <footer style="border-top:1px solid var(--border-color);padding:32px 0;margin-top:60px;background:rgba(11,15,25,0.6);">
    <div class="container" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px;">
      <div>
        <h4 style="font-size:1.1rem;color:var(--text-main);margin-bottom:6px;">SpaceHub Booking (OOP 3 Classes Architecture)</h4>
        <p style="font-size:0.85rem;color:var(--text-muted);">พัฒนาโดย ทีมพัฒนาแอปพลิเคชัน 2 คน | รองรับ MySQL PDO & Hybrid JSON Fallback</p>
      </div>
      <div style="display:flex;gap:16px;">
        <div style="background:var(--bg-card);border:1px solid var(--border-color);padding:10px 16px;border-radius:var(--radius-md);font-size:0.82rem;">
          <strong style="color:var(--accent-emerald);">สมาชิกคนที่ 1 (035):</strong> พัฒนาคลาส <code>Room.php</code>
        </div>
        <div style="background:var(--bg-card);border:1px solid var(--border-color);padding:10px 16px;border-radius:var(--radius-md);font-size:0.82rem;">
          <strong style="color:var(--accent-cyan);">สมาชิกคนที่ 2 (036 ศุภนัฐ จันทร์เปรม):</strong> พัฒนาคลาส <code>Booking.php</code>
        </div>
      </div>
    </div>
  </footer>

  <!-- Application Script -->
  <script src="assets/js/app.js"></script>
</body>
</html>
