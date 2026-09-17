/**
 * ==============================================================================
 * SpaceHub - Front-end Application Controller (Single Page Application SPA)
 * ==============================================================================
 * พัฒนาร่วมกันโดย: รหัส 035 และ รหัส 036 ศุภนัฐ จันทร์เปรม
 * คำอธิบาย: สคริปต์ควบคุมส่วนหน้าเว็บทั้งหมด ได้แก่ การโหลดห้องประชุม, การคำนวณราคาแบบเรียลไทม์
 * ภาษี VAT 7%, บริการเสริม, ตารางเวลาใช้งาน, ระบบค้นหา/ยกเลิกการจอง, ระบบล็อกอิน/สมัครสมาชิก
 * และระบบบริหารจัดการหลังบ้านสำหรับผู้ดูแลระบบ (Admin Back-office)
 * ==============================================================================
 */

// แคตตาล็อกบริการเสริม (Add-on Services Catalog - พัฒนาโดย รหัส 036 ศุภนัฐ จันทร์เปรม)
const ADDON_CATALOG = [
  { id: 'coffee', name: 'ชุดกาแฟสดพรีเมียม & เบเกอรี่ฝรั่งเศส', price: 120, perPerson: true, desc: 'เสิร์ฟตามจำนวนผู้เข้าร่วม (120฿/คน)' },
  { id: 'projector_4k', name: 'ระบบเชื่อมต่อไร้สาย 4K Wireless ClickShare', price: 350, perPerson: false, desc: 'อุปกรณ์สลับหน้าจอความเร็วสูง (350฿/รอบ)' },
  { id: 'sound_eng', name: 'เจ้าหน้าที่เทคนิคประจำห้องคุมระบบภาพ-เสียง', price: 600, perPerson: false, desc: 'ดูแลตลอดการประชุม (600฿/รอบ)' },
  { id: 'whiteboard_kit', name: 'ชุดอุปกรณ์ Post-it & ปากกา Brainstorming', price: 250, perPerson: false, desc: 'เซ็ตอุปกรณ์ Workshop ครบชุด (250฿/ชุด)' },
];

// ฟังก์ชันคืนค่าวันที่ปัจจุบันตามเวลาท้องถิ่น (Local YYYY-MM-DD)
function getTodayDateString() {
  const d = new Date();
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

// ตัวแปรสถานะแอปพลิเคชัน (Application Central State)
const AppState = {
  rooms: [],
  availableRoomIds: [],
  selectedRoom: null,
  activeTab: 'booking',
  selectedCategory: 'all',
  searchQuery: '',
  currentUser: null,
  allBookings: [],
  filter: {
    date: getTodayDateString(),
    start: '09:00',
    end: '12:00',
    attendees: 1
  }
};

// เริ่มทำงานเมื่อ DOM โหลดเรียบร้อย
document.addEventListener('DOMContentLoaded', () => {
  initNavigation();
  initFilterControls();
  initCategoryPillsAndSearch();
  initBookingModal();
  initLookupTab();
  initScheduleTab();
  checkCurrentUser();
  loadDashboardStats();
  
  // กำหนดวันที่เริ่มต้นเป็นวันปัจจุบัน
  const dateInput = document.getElementById('filterDate');
  if (dateInput) {
    const todayStr = getTodayDateString();
    dateInput.value = todayStr;
    dateInput.min = todayStr;
    AppState.filter.date = todayStr;
  }

  // โหลดรายการห้องประชุมและตรวจสอบเวลาว่าง
  loadRoomsAndCheckAvailability();
});

// --- 1. ระบบนำทางแท็บเมนู (Tab Navigation - พัฒนาร่วมกัน 035 & 036) ---
function initNavigation() {
  const tabButtons = document.querySelectorAll('.nav-btn');
  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const tabTarget = btn.getAttribute('data-tab');
      switchTab(tabTarget);
    });
  });
}

function switchTab(tabId) {
  AppState.activeTab = tabId;
  
  // อัปเดตสถานะปุ่ม active
  document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.classList.toggle('active', btn.getAttribute('data-tab') === tabId);
  });

  // อัปเดตการมองเห็นของ View
  document.querySelectorAll('.tab-view').forEach(view => {
    view.classList.toggle('active', view.id === `${tabId}-view`);
  });

  // โหลดข้อมูลตามแท็บที่เลือก
  if (tabId === 'schedule') {
    renderScheduleGrid();
  } else if (tabId === 'stats') {
    loadDashboardStats();
  } else if (tabId === 'admin') {
    renderAdminBookingsTable();
  }
}

// --- 2. ระบบโหลดห้องประชุมและเช็คเวลาว่าง (Room & Availability - พัฒนาโดย รหัส 035) ---
async function loadRoomsAndCheckAvailability() {
  try {
    // 2.1 โหลดข้อมูลห้องทั้งหมดจาก API
    if (!AppState.rooms || AppState.rooms.length === 0) {
      const roomsRes = await fetch('api.php?action=get_rooms');
      const roomsData = await roomsRes.json();
      if (roomsData.success && Array.isArray(roomsData.data)) {
        AppState.rooms = roomsData.data;
      }
    }

    // 2.2 เช็คห้องที่ว่างตรงตามเงื่อนไขวันที่ เวลา และจำนวนคน
    const res = await fetch(`api.php?action=check_availability&date=${AppState.filter.date}&start=${AppState.filter.start}&end=${AppState.filter.end}&attendees=${AppState.filter.attendees}`);
    const result = await res.json();

    if (result.success) {
      AppState.availableRoomIds = result.availableRoomIds || [];
    }

    renderRoomGrid();
  } catch (err) {
    console.error('Error loading rooms:', err);
    showToast('เกิดข้อผิดพลาดในการโหลดข้อมูลห้องประชุม', 'error');
  }
}

// --- 3. ระบบแสดงผลการ์ดห้องประชุม (Render Room Cards - พัฒนาโดย รหัส 035) ---
function renderRoomGrid() {
  const container = document.getElementById('roomsGrid');
  if (!container) return;

  // กรองห้องตามประเภท (Category) และข้อความค้นหา (Search Query)
  let filteredRooms = AppState.rooms.filter(room => {
    const matchCategory = AppState.selectedCategory === 'all' || room.type === AppState.selectedCategory;
    const matchQuery = !AppState.searchQuery || 
      room.name.toLowerCase().includes(AppState.searchQuery.toLowerCase()) ||
      room.type.toLowerCase().includes(AppState.searchQuery.toLowerCase()) ||
      room.description.toLowerCase().includes(AppState.searchQuery.toLowerCase());
    return matchCategory && matchQuery;
  });

  if (filteredRooms.length === 0) {
    container.innerHTML = `
      <div class="no-rooms-card" style="grid-column:1/-1;text-align:center;padding:60px 20px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:12px;stroke:var(--text-dim);"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
        <h3 style="font-size:1.2rem;color:var(--text-main);margin-bottom:6px;">ไม่พบห้องประชุมที่ตรงตามเงื่อนไข</h3>
        <p style="font-size:0.9rem;color:var(--text-muted);">ลองปรับเปลี่ยนประเภทห้อง ช่วงเวลา หรือข้อความค้นหาใหม่อีกครั้ง</p>
      </div>
    `;
    return;
  }

  container.innerHTML = filteredRooms.map(room => {
    const isAvailable = AppState.availableRoomIds.includes(room.id);
    const capacityOk = room.capacity >= AppState.filter.attendees;

    // แยกแยะสถานะระหว่างความจุไม่พอ กับ ห้องติดจองจริง
    let statusTagHtml = '';
    let bookBtnHtml = '';

    if (!capacityOk) {
      statusTagHtml = `<span class="room-status-tag capacity-exceeded">ความจุไม่พอ (สูงสุด ${room.capacity} คน)</span>`;
      bookBtnHtml = `<button type="button" class="btn-book-room" disabled style="background:#475569;cursor:not-allowed;">ความจุไม่เพียงพอ</button>`;
    } else if (!isAvailable) {
      statusTagHtml = `<span class="room-status-tag booked">ไม่ว่าง / ติดจอง</span>`;
      bookBtnHtml = `<button type="button" class="btn-book-room" disabled style="background:#334155;cursor:not-allowed;">ช่วงเวลานี้ติดจอง</button>`;
    } else {
      statusTagHtml = `<span class="room-status-tag available">ว่าง จองได้</span>`;
      bookBtnHtml = `<button type="button" class="btn-book-room" onclick="openBookingModal('${room.id}')">จองห้องนี้ทันที</button>`;
    }

    return `
      <div class="room-card ${!isAvailable || !capacityOk ? 'disabled' : ''}">
        <div class="room-thumb">
          <img src="${room.imageUrl}" alt="${escapeHtml(room.name)}" loading="lazy" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80';">
          <span class="room-type-badge">${escapeHtml(room.type)}</span>
          ${statusTagHtml}
        </div>

        <div class="room-content">
          <h3 class="room-title">${escapeHtml(room.name)}</h3>
          <p class="room-desc">${escapeHtml(room.description)}</p>

          <div class="room-specs">
            <div class="spec-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
              <span>ความจุสูงสุด ${room.capacity} คน</span>
            </div>
            <div class="spec-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              <span>จองขั้นต่ำ ${room.minHours} ชม.</span>
            </div>
          </div>

          <div class="amenities-list">
            ${(room.amenities || []).slice(0, 3).map(a => `<span class="amenity-pill">${escapeHtml(a)}</span>`).join('')}
            ${(room.amenities || []).length > 3 ? `<span class="amenity-pill">+${room.amenities.length - 3}</span>` : ''}
          </div>

          <div class="room-footer">
            <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:12px;">
              <div>
                <span class="rate-price">฿${room.hourlyRate.toLocaleString()}</span>
                <span class="rate-unit">บาท / ชั่วโมง</span>
              </div>
            </div>

            ${bookBtnHtml}
          </div>
        </div>
      </div>
    `;
  }).join('');
}

// --- 4. ตัวควบคุมฟิลเตอร์และประเภทห้อง (Category Pills & Search Filter) ---
function initFilterControls() {
  const dateInput = document.getElementById('filterDate');
  const startSelect = document.getElementById('filterStartTime');
  const endSelect = document.getElementById('filterEndTime');
  const attendeesInput = document.getElementById('filterAttendees');

  const onFilterChange = (e) => {
    // ตรวจสอบและปรับเวลาสิ้นสุดอัตโนมัติหากเวลาเริ่ม >= เวลาจบ
    if (e && e.target === startSelect) {
      const startH = parseInt(startSelect.value.split(':')[0], 10);
      const endH = parseInt(endSelect.value.split(':')[0], 10);
      if (startH >= endH) {
        const nextH = Math.min(20, startH + 2);
        endSelect.value = String(nextH).padStart(2, '0') + ':00';
      }
    }

    AppState.filter.date = dateInput.value;
    AppState.filter.start = startSelect.value;
    AppState.filter.end = endSelect.value;
    AppState.filter.attendees = parseInt(attendeesInput.value, 10) || 1;

    loadRoomsAndCheckAvailability();
  };

  dateInput?.addEventListener('change', onFilterChange);
  startSelect?.addEventListener('change', onFilterChange);
  endSelect?.addEventListener('change', onFilterChange);
  attendeesInput?.addEventListener('change', onFilterChange);
}

function initCategoryPillsAndSearch() {
  const pills = document.querySelectorAll('.category-pills .pill');
  pills.forEach(pill => {
    pill.addEventListener('click', () => {
      pills.forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      AppState.selectedCategory = pill.getAttribute('data-category');
      renderRoomGrid();
    });
  });

  const searchInput = document.getElementById('searchInput');
  searchInput?.addEventListener('input', (e) => {
    AppState.searchQuery = e.target.value.trim();
    renderRoomGrid();
  });
}

// --- 5. ระบบป๊อปอัปฟอร์มการจองและการคำนวณการเงิน (Booking Modal & Financials - พัฒนาโดย รหัส 036 ศุภนัฐ จันทร์เปรม) ---
function initBookingModal() {
  const modal = document.getElementById('bookingModal');
  const btnClose = document.getElementById('btnModalClose');
  const bookingForm = document.getElementById('bookingForm');

  btnClose?.addEventListener('click', closeModal);
  bookingForm?.addEventListener('submit', handleBookingSubmit);
}

function openBookingModal(roomId) {
  const room = AppState.rooms.find(r => r.id === roomId);
  if (!room) return;

  AppState.selectedRoom = room;

  document.getElementById('modalRoomName').textContent = room.name;
  document.getElementById('modalRoomType').textContent = room.type;
  document.getElementById('modalDateTime').textContent = `${formatDateThai(AppState.filter.date)} (${AppState.filter.start} - ${AppState.filter.end} น.)`;

  document.getElementById('formAttendees').value = AppState.filter.attendees;
  document.getElementById('formAttendees').max = room.capacity;

  // เติมข้อมูลอัตโนมัติหากเข้าสู่ระบบอยู่
  if (AppState.currentUser) {
    autoFillUserData(AppState.currentUser);
  }

  renderAddonsChecklist();
  recalculateBookingCosts();

  const modal = document.getElementById('bookingModal');
  modal?.classList.add('active');
}

function closeModal() {
  const modal = document.getElementById('bookingModal');
  modal?.classList.remove('active');
}

function renderAddonsChecklist() {
  const container = document.getElementById('addonsListContainer');
  if (!container) return;

  container.innerHTML = ADDON_CATALOG.map(item => `
    <label class="addon-item" for="addon_${item.id}">
      <input type="checkbox" id="addon_${item.id}" value="${item.id}" onchange="recalculateBookingCosts()">
      <div class="addon-info">
        <div class="addon-name">${escapeHtml(item.name)}</div>
        <div class="addon-desc">${escapeHtml(item.desc)}</div>
      </div>
      <div class="addon-price">+฿${item.price}</div>
    </label>
  `).join('');
}

function onAttendeeChange() {
  recalculateBookingCosts();
}

// คำนวณราคาเรียลไทม์ (Real-time Financial Calculator by รหัส 036 ศุภนัฐ จันทร์เปรม)
function recalculateBookingCosts() {
  if (!AppState.selectedRoom) return;

  const startHour = parseInt(AppState.filter.start.split(':')[0], 10);
  const endHour = parseInt(AppState.filter.end.split(':')[0], 10);
  const duration = Math.max(1, endHour - startHour);
  const minHours = AppState.selectedRoom.minHours || 1;
  const billableHours = Math.max(minHours, duration);
  const attendees = parseInt(document.getElementById('formAttendees')?.value, 10) || 1;

  // 1. ค่าห้องพื้นฐาน (คำนวณตามชั่วโมงขั้นต่ำ minHours ของห้อง)
  const basePrice = billableHours * AppState.selectedRoom.hourlyRate;

  // 2. ค่าบริการเสริม
  let addonsTotal = 0;
  ADDON_CATALOG.forEach(item => {
    const chk = document.getElementById(`addon_${item.id}`);
    if (chk && chk.checked) {
      if (item.perPerson) {
        addonsTotal += item.price * attendees;
      } else {
        addonsTotal += item.price;
      }
    }
  });

  // 3. ภาษี VAT 7% และราคารวม
  const subtotal = basePrice + addonsTotal;
  const tax = subtotal * 0.07;
  const grandTotal = subtotal + tax;

  // แสดงผลตัวเลขบน UI
  const durationText = duration < minHours 
    ? `${duration} ชม. (คิดขั้นต่ำ ${minHours} ชม.)`
    : `${duration} ชั่วโมง`;
  document.getElementById('calcDuration').textContent = durationText;
  document.getElementById('calcBasePrice').textContent = `฿${basePrice.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
  document.getElementById('calcAddonsPrice').textContent = `฿${addonsTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
  document.getElementById('calcTaxPrice').textContent = `฿${tax.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
  document.getElementById('calcTotalPrice').textContent = `฿${grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2})}`;
}

// ส่งฟอร์มการจองเข้าสู่ API backend
async function handleBookingSubmit(e) {
  e.preventDefault();

  const customerName = document.getElementById('formCustomerName').value.trim();
  const customerPhone = document.getElementById('formCustomerPhone').value.trim();
  const customerEmail = document.getElementById('formCustomerEmail').value.trim();
  const organization = document.getElementById('formOrganization').value.trim();
  const purpose = document.getElementById('formPurpose').value.trim();
  const attendeeCount = parseInt(document.getElementById('formAttendees').value, 10) || 1;
  const notes = document.getElementById('formNotes').value.trim();

  // รวบรวมรายการแอดออนที่เลือก
  const selectedAddons = [];
  ADDON_CATALOG.forEach(item => {
    const chk = document.getElementById(`addon_${item.id}`);
    if (chk && chk.checked) selectedAddons.push(item.id);
  });

  const payload = {
    roomId: AppState.selectedRoom.id,
    customerName,
    customerPhone,
    customerEmail,
    organization,
    purpose,
    bookingDate: AppState.filter.date,
    startTime: AppState.filter.start,
    endTime: AppState.filter.end,
    attendeeCount,
    addOnServices: selectedAddons,
    notes
  };

  const btnSubmit = document.getElementById('btnSubmitBooking');
  btnSubmit.disabled = true;
  btnSubmit.textContent = 'กำลังทำรายการ...';

  try {
    const res = await fetch('api.php?action=create_booking', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();

    btnSubmit.disabled = false;
    btnSubmit.textContent = 'ยืนยันการจอง (Confirm Booking)';

    if (result.success && result.data) {
      closeModal();
      showVoucherModal(result.data);
      showToast('การจองสำเร็จเรียบร้อยแล้ว!', 'success');
      loadRoomsAndCheckAvailability();
    } else {
      showToast(result.message || 'ไม่สามารถทำการจองได้', 'error');
    }
  } catch (err) {
    btnSubmit.disabled = false;
    btnSubmit.textContent = 'ยืนยันการจอง (Confirm Booking)';
    showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
  }
}

// --- 6. แสดงผลใบยืนยันดิจิทัล (Digital Voucher Modal - พัฒนาโดย รหัส 036 ศุภนัฐ จันทร์เปรม) ---
function showVoucherModal(booking) {
  document.getElementById('vCode').textContent = booking.bookingCode;
  document.getElementById('vCustomer').textContent = booking.customerName;
  document.getElementById('vContact').textContent = `${booking.customerPhone} | ${booking.customerEmail}`;
  document.getElementById('vOrg').textContent = booking.organization || 'บุคคลทั่วไป';
  document.getElementById('vRoom').textContent = `${booking.room.name} (${booking.room.type})`;
  document.getElementById('vDateTime').textContent = `${formatDateThai(booking.bookingDate)} | ${booking.startTime.substring(0,5)} - ${booking.endTime.substring(0,5)} น.`;
  document.getElementById('vAttendees').textContent = `${booking.attendeeCount} คน`;
  document.getElementById('vPurpose').textContent = booking.purpose || 'การประชุมทั่วไป';

  const addonsText = (booking.addOnServices || []).map(id => {
    const item = ADDON_CATALOG.find(a => a.id === id);
    return item ? item.name : id;
  }).join(', ') || 'ไม่มีบริการเสริมเพิ่มเติม';

  document.getElementById('vAddonsList').textContent = addonsText;
  document.getElementById('vTotal').textContent = `฿${(booking.financials.totalAmount || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}`;

  const voucherModal = document.getElementById('voucherModal');
  voucherModal?.classList.add('active');
}

function closeVoucherModal() {
  const voucherModal = document.getElementById('voucherModal');
  voucherModal?.classList.remove('active');
}

// --- 7. ระบบค้นหาและยกเลิกการจอง (Lookup & Cancel Tab - พัฒนาโดย รหัส 036 ศุภนัฐ จันทร์เปรม) ---
function initLookupTab() {
  const input = document.getElementById('lookupCodeInput');
  input?.addEventListener('keyup', (e) => {
    if (e.key === 'Enter') handleLookupSubmit();
  });
}

async function handleLookupSubmit() {
  const code = document.getElementById('lookupCodeInput').value.trim();
  const area = document.getElementById('lookupResultArea');
  if (!code) {
    showToast('กรุณากรอกรหัสการจอง', 'error');
    return;
  }

  area.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted);">กำลังค้นหาข้อมูล...</div>';

  try {
    const res = await fetch(`api.php?action=lookup_booking&code=${encodeURIComponent(code)}`);
    const result = await res.json();

    if (result.success && result.data) {
      const b = result.data;
      const statusBadge = getStatusBadgeHTML(b.status);

      area.innerHTML = `
        <div class="voucher-card" style="margin-top:20px;">
          <div class="voucher-header">
            <div>
              <div style="font-size:0.8rem;color:var(--text-muted);">ผลการค้นหาการจอง</div>
              <h3 style="font-size:1.3rem;margin-top:2px;">${escapeHtml(b.room.name)}</h3>
            </div>
            <div>${statusBadge}</div>
          </div>

          <div class="voucher-details-grid">
            <div class="voucher-item"><div class="title">รหัสการจอง</div><div class="value" style="color:var(--accent-cyan);">${b.bookingCode}</div></div>
            <div class="voucher-item"><div class="title">ชื่อผู้จอง</div><div class="value">${escapeHtml(b.customerName)}</div></div>
            <div class="voucher-item"><div class="title">เบอร์โทรศัพท์</div><div class="value">${escapeHtml(b.customerPhone)}</div></div>
            <div class="voucher-item"><div class="title">วันและเวลา</div><div class="value">${formatDateThai(b.bookingDate)} (${b.startTime.substring(0,5)} - ${b.endTime.substring(0,5)} น.)</div></div>
            <div class="voucher-item"><div class="title">ยอดเงินรวม</div><div class="value" style="color:var(--accent-emerald);">฿${(b.financials.totalAmount || 0).toLocaleString()}</div></div>
            <div class="voucher-item"><div class="title">จำนวนผู้เข้าร่วม</div><div class="value">${b.attendeeCount} คน</div></div>
          </div>

          ${b.status !== 'CANCELLED' ? `
            <div style="margin-top:20px;display:flex;justify-content:flex-end;">
              <button class="btn-secondary" onclick="confirmCancelBooking('${b.bookingCode}')" style="background:rgba(244,63,94,0.15);border-color:rgba(244,63,94,0.3);color:#fca5a5;">
                ยกเลิกรายการจองนี้
              </button>
            </div>
          ` : ''}
        </div>
      `;
    } else {
      area.innerHTML = `
        <div style="text-align:center;padding:40px;background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.2);border-radius:var(--radius-md);color:#fca5a5;margin-top:20px;">
          ${escapeHtml(result.message || 'ไม่พบรหัสการจองดังกล่าวในระบบ')}
        </div>
      `;
    }
  } catch (err) {
    area.innerHTML = '<div style="text-align:center;padding:40px;color:#fca5a5;">เกิดข้อผิดพลาดในการเชื่อมต่อ</div>';
  }
}

async function confirmCancelBooking(code) {
  if (!confirm(`คุณแน่ใจหรือไม่ที่จะยกเลิกรายการจองรหัส ${code} ?`)) return;

  try {
    const res = await fetch('api.php?action=cancel_booking', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ bookingCode: code })
    });
    const result = await res.json();
    if (result.success) {
      showToast('ยกเลิกรายการจองเรียบร้อยแล้ว', 'info');
      handleLookupSubmit();
      loadRoomsAndCheckAvailability();
    } else {
      showToast(result.message, 'error');
    }
  } catch (err) {
    showToast('เกิดข้อผิดพลาดในการยกเลิก', 'error');
  }
}

// --- 8. ระบบตารางเวลารายวัน (Schedule Grid View - พัฒนาร่วมกัน 035 & 036) ---
function initScheduleTab() {
  const dateInput = document.getElementById('scheduleDate');
  if (dateInput) {
    dateInput.value = getTodayDateString();
  }
}

async function renderScheduleGrid() {
  const dateInput = document.getElementById('scheduleDate');
  const date = dateInput?.value || getTodayDateString();
  const tbody = document.getElementById('scheduleTableBody');
  if (!tbody) return;

  tbody.innerHTML = '<tr><td colspan="13" style="text-align:center;padding:30px;">กำลังโหลดตาราง...</td></tr>';

  try {
    const res = await fetch('api.php?action=get_bookings');
    const result = await res.json();
    const allBookings = result.success ? result.data : [];

    // ดึงเฉพาะวันดังกล่าว
    const dayBookings = allBookings.filter(b => b.bookingDate === date && b.status !== 'CANCELLED');

    const hours = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'];

    tbody.innerHTML = AppState.rooms.map(room => {
      const roomBookings = dayBookings.filter(b => b.room.id === room.id);

      const hourCells = hours.map(h => {
        const currentH = parseInt(h.split(':')[0], 10);
        const isOccupied = roomBookings.some(b => {
          const bStart = parseInt(b.startTime.split(':')[0], 10);
          const bEnd = parseInt(b.endTime.split(':')[0], 10);
          return currentH >= bStart && currentH < bEnd;
        });

        return `<td class="${isOccupied ? 'slot-occupied' : 'slot-free'}">${isOccupied ? 'ไม่ว่าง' : 'ว่าง'}</td>`;
      }).join('');

      return `
        <tr>
          <td><strong>${escapeHtml(room.name)}</strong><br><small style="color:var(--text-muted);">${escapeHtml(room.type)}</small></td>
          ${hourCells}
        </tr>
      `;
    }).join('');

  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="13" style="text-align:center;color:#fca5a5;">เกิดข้อผิดพลาดในการโหลดตารางเวลา</td></tr>';
  }
}

// --- 9. ระบบสถิติและแดชบอร์ด (Overview Stats Dashboard - พัฒนาร่วมกัน 035 & 036) ---
async function loadDashboardStats() {
  try {
    const res = await fetch('api.php?action=get_stats');
    const result = await res.json();

    if (result.success && result.data) {
      const s = result.data;
      document.getElementById('statTotalRooms').textContent = s.totalRooms || 0;
      document.getElementById('statConfirmed').textContent = s.confirmedBookings || 0;
      document.getElementById('statTodayBookings').textContent = s.totalBookings || 0;
      document.getElementById('statRevenue').textContent = `฿${(s.totalRevenue || 0).toLocaleString()}`;

      const dbText = document.getElementById('dbStatusText');
      if (dbText) {
        dbText.textContent = s.isDatabaseConnected 
          ? 'ระบบพร้อมใช้งาน (MySQL Connected)' 
          : 'ระบบพร้อมใช้งาน (JSON Fallback Mode)';
      }
    }

    // โหลดตารางรายการจองล่าสุด
    const bookingsRes = await fetch('api.php?action=get_bookings');
    const bookingsData = await bookingsRes.json();
    if (bookingsData.success && Array.isArray(bookingsData.data)) {
      renderRecentBookingsTable(bookingsData.data.slice(0, 10));
    }
  } catch (err) {
    console.error('Error loading stats:', err);
  }
}

function renderRecentBookingsTable(bookings) {
  const tbody = document.getElementById('recentBookingsTableBody');
  if (!tbody) return;

  if (bookings.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted);">ยังไม่มีรายการจองในระบบ</td></tr>';
    return;
  }

  tbody.innerHTML = bookings.map(b => `
    <tr>
      <td><strong style="color:var(--accent-cyan);">${b.bookingCode}</strong></td>
      <td><strong>${escapeHtml(b.customerName)}</strong><br><small style="color:var(--text-muted);">${escapeHtml(b.organization || 'บุคคลทั่วไป')}</small></td>
      <td>${escapeHtml(b.room.name)}</td>
      <td>${formatDateThai(b.bookingDate)}<br><small style="color:var(--text-muted);">${b.startTime.substring(0,5)} - ${b.endTime.substring(0,5)} น.</small></td>
      <td>${b.attendeeCount} คน</td>
      <td style="color:var(--accent-emerald);font-weight:600;">฿${(b.financials.totalAmount || 0).toLocaleString()}</td>
      <td>${getStatusBadgeHTML(b.status)}</td>
    </tr>
  `).join('');
}

// --- 10. ระบบจัดการหลังบ้านแอดมิน (Admin Back-office Management - พัฒนาร่วมกัน 035 & 036) ---
async function renderAdminBookingsTable() {
  const tbody = document.getElementById('adminBookingsTableBody');
  const badge = document.getElementById('adminTotalCountBadge');
  if (!tbody) return;

  // ตรวจสอบสิทธิ์ผู้ดูแลระบบ (Admin Guard) - หลังบ้านเข้าได้เฉพาะ Admin เท่านั้น
  if (!AppState.currentUser || AppState.currentUser.role !== 'ADMIN') {
    if (badge) badge.textContent = '0 รายการ (ไม่มีสิทธิ์)';
    tbody.innerHTML = `
      <tr>
        <td colspan="9" style="padding:0;">
          <div style="text-align:center;padding:50px 20px;background:rgba(239,68,68,0.06);border:1px dashed rgba(239,68,68,0.3);border-radius:var(--radius-lg);margin:20px 0;">
            <div style="width:64px;height:64px;margin:0 auto 16px;background:rgba(239,68,68,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            </div>
            <h3 style="font-size:1.3rem;color:#f87171;margin-bottom:8px;font-weight:700;">🔒 สงวนสิทธิ์เฉพาะผู้ดูแลระบบ (Admin Only)</h3>
            <p style="font-size:0.92rem;color:var(--text-muted);max-width:540px;margin:0 auto 20px;line-height:1.6;">
              ระบบบริหารจัดการหลังบ้านถูกจำกัดสิทธิ์เพื่อความปลอดภัย สมาชิกหรือผู้เยี่ยมชมทั่วไปไม่สามารถเข้าดูหรือจัดการรายการจองได้ กรุณาเข้าสู่ระบบด้วยบัญชี Admin
            </p>
            <div style="display:flex;gap:12px;justify-content:center;align-items:center;flex-wrap:wrap;">
              <button type="button" class="btn-primary" onclick="openAuthModal('login')" style="background:linear-gradient(135deg,#ef4444 0%,#b91c1c 100%);border:none;padding:12px 24px;font-size:0.95rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                เข้าสู่ระบบด้วยบัญชี Admin
              </button>
            </div>
          </div>
        </td>
      </tr>
    `;
    return;
  }

  tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);">กำลังดึงข้อมูลการจองทั้งหมด...</td></tr>';

  try {
    const res = await fetch('api.php?action=get_bookings');
    const result = await res.json();

    if (!result.success || !Array.isArray(result.data)) {
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#fca5a5;">ไม่สามารถโหลดข้อมูลได้</td></tr>';
      return;
    }

    AppState.allBookings = result.data;

    const searchQuery = (document.getElementById('adminSearchInput')?.value || '').trim().toLowerCase();
    const statusFilter = document.getElementById('adminStatusFilter')?.value || 'ALL';

    let filtered = AppState.allBookings.filter(b => {
      const matchStatus = statusFilter === 'ALL' || b.status === statusFilter;
      const matchSearch = !searchQuery || 
        b.bookingCode.toLowerCase().includes(searchQuery) ||
        b.customerName.toLowerCase().includes(searchQuery) ||
        b.customerPhone.toLowerCase().includes(searchQuery) ||
        b.room.name.toLowerCase().includes(searchQuery);
      return matchStatus && matchSearch;
    });

    if (badge) badge.textContent = `${filtered.length} รายการ`;

    if (filtered.length === 0) {
      tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:40px;color:var(--text-muted);">ไม่พบรายการจองที่ตรงกับเงื่อนไขค้นหา</td></tr>';
      return;
    }

    tbody.innerHTML = filtered.map(b => `
      <tr>
        <td><strong style="color:var(--accent-cyan);">${b.bookingCode}</strong></td>
        <td>
          <strong>${escapeHtml(b.customerName)}</strong>
          <br><small style="color:var(--text-muted);">${escapeHtml(b.organization || '-')}</small>
        </td>
        <td>
          <span style="font-size:0.85rem;">${escapeHtml(b.customerPhone)}</span>
          <br><small style="color:var(--text-dim);">${escapeHtml(b.customerEmail)}</small>
        </td>
        <td><strong>${escapeHtml(b.room.name)}</strong></td>
        <td>
          ${formatDateThai(b.bookingDate)}
          <br><small style="color:var(--text-muted);">${b.startTime.substring(0,5)} - ${b.endTime.substring(0,5)} น.</small>
        </td>
        <td>${b.attendeeCount} คน</td>
        <td style="color:var(--accent-emerald);font-weight:700;">฿${(b.financials.totalAmount || 0).toLocaleString()}</td>
        <td>${getStatusBadgeHTML(b.status)}</td>
        <td style="text-align:center;">
          <div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap;">
            <select onchange="changeAdminStatus('${b.bookingCode}', this.value)" style="background:var(--bg-input);color:#fff;border:1px solid var(--border-color);border-radius:var(--radius-sm);padding:4px 8px;font-size:0.78rem;">
              <option value="CONFIRMED" ${b.status === 'CONFIRMED' ? 'selected' : ''}>CONFIRMED</option>
              <option value="PENDING" ${b.status === 'PENDING' ? 'selected' : ''}>PENDING</option>
              <option value="COMPLETED" ${b.status === 'COMPLETED' ? 'selected' : ''}>COMPLETED</option>
              <option value="CANCELLED" ${b.status === 'CANCELLED' ? 'selected' : ''}>CANCELLED</option>
            </select>
            <button class="btn-secondary" onclick="deleteAdminBooking('${b.bookingCode}')" style="padding:4px 8px;font-size:0.75rem;background:rgba(244,63,94,0.15);border-color:rgba(244,63,94,0.3);color:#fca5a5;">
              ลบ
            </button>
          </div>
        </td>
      </tr>
    `).join('');

  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#fca5a5;">เกิดข้อผิดพลาดในการโหลดข้อมูลหลังบ้าน</td></tr>';
  }
}

async function changeAdminStatus(bookingCode, newStatus) {
  try {
    const res = await fetch('api.php?action=update_booking_status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ bookingCode, status: newStatus })
    });
    const result = await res.json();
    if (result.success) {
      showToast(result.message, 'success');
      renderAdminBookingsTable();
      loadRoomsAndCheckAvailability();
    } else {
      showToast(result.message, 'error');
    }
  } catch (err) {
    showToast('ไม่สามารถเปลี่ยนสถานะได้', 'error');
  }
}

async function deleteAdminBooking(bookingCode) {
  if (!confirm(`คุณแน่ใจหรือไม่ที่จะลบรายการจองรหัส ${bookingCode} ออกจากระบบอย่างถาวร?`)) return;

  try {
    const res = await fetch('api.php?action=delete_booking', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ bookingCode })
    });
    const result = await res.json();
    if (result.success) {
      showToast(result.message, 'info');
      renderAdminBookingsTable();
      loadRoomsAndCheckAvailability();
    } else {
      showToast(result.message, 'error');
    }
  } catch (err) {
    showToast('เกิดข้อผิดพลาดในการลบ', 'error');
  }
}

// --- 11. ระบบยืนยันตัวตน และจัดการผู้ใช้งาน (User Auth Logic - พัฒนาร่วมกัน 035 & 036) ---
async function checkCurrentUser() {
  try {
    const res = await fetch('api.php?action=get_current_user');
    const data = await res.json();
    if (data.success && data.user) {
      AppState.currentUser = data.user;
    } else {
      AppState.currentUser = null;
    }
    updateAuthWidgetUI(AppState.currentUser);
  } catch (e) {
    AppState.currentUser = null;
    updateAuthWidgetUI(null);
  }
  if (AppState.activeTab === 'admin') {
    renderAdminBookingsTable();
  }
}

function updateAuthWidgetUI(user) {
  const widget = document.getElementById('userAuthWidget');
  if (!widget) return;

  if (user) {
    widget.innerHTML = `
      <div style="display:flex;align-items:center;gap:12px;">
        <img src="${escapeHtml(user.avatar)}" class="user-profile-avatar" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid var(--accent-emerald);">
        <div style="font-size:0.85rem;">
          <div style="font-weight:600;color:var(--text-main);">${escapeHtml(user.fullname)}</div>
          <div style="font-size:0.75rem;color:var(--accent-emerald);">${user.role === 'ADMIN' ? 'ผู้ดูแลระบบ' : 'สมาชิก'}</div>
        </div>
        <button type="button" class="user-auth-btn" onclick="handleLogout()" style="padding:6px 12px;font-size:0.8rem;background:rgba(244,63,94,0.15);border-color:rgba(244,63,94,0.3);color:#fca5a5;">
          ออกจากระบบ
        </button>
      </div>
    `;
  } else {
    widget.innerHTML = `
      <button type="button" class="user-auth-btn" onclick="openAuthModal('login')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        <span>เข้าสู่ระบบ / สมัครสมาชิก</span>
      </button>
    `;
  }
}

function autoFillUserData(user) {
  const nameInput = document.getElementById('formCustomerName');
  const phoneInput = document.getElementById('formCustomerPhone');
  const emailInput = document.getElementById('formCustomerEmail');

  if (nameInput && !nameInput.value) nameInput.value = user.fullname;
  if (phoneInput && !phoneInput.value && user.phone) phoneInput.value = user.phone;
  if (emailInput && !emailInput.value && user.email) emailInput.value = user.email;
}

function openAuthModal(tab = 'login') {
  const overlay = document.getElementById('authModalOverlay');
  if (overlay) {
    overlay.classList.add('active');
    switchAuthTab(tab);
  }
}

function closeAuthModal() {
  const overlay = document.getElementById('authModalOverlay');
  if (overlay) {
    overlay.classList.remove('active');
    hideAuthAlert();
  }
}

function switchAuthTab(tab) {
  const loginBtn = document.getElementById('authTabLoginBtn');
  const regBtn = document.getElementById('authTabRegisterBtn');
  const loginForm = document.getElementById('loginForm');
  const regForm = document.getElementById('registerForm');

  hideAuthAlert();

  if (tab === 'login') {
    loginBtn?.classList.add('active');
    regBtn?.classList.remove('active');
    if (loginForm) loginForm.style.display = 'block';
    if (regForm) regForm.style.display = 'none';
  } else {
    regBtn?.classList.add('active');
    loginBtn?.classList.remove('active');
    if (regForm) regForm.style.display = 'block';
    if (loginForm) loginForm.style.display = 'none';
  }
}

function showAuthAlert(msg, type = 'error') {
  const alert = document.getElementById('authAlert');
  if (alert) {
    alert.className = `auth-alert ${type}`;
    alert.style.display = 'block';
    alert.textContent = msg;
  }
}

function hideAuthAlert() {
  const alert = document.getElementById('authAlert');
  if (alert) alert.style.display = 'none';
}

function handleLoginSubmit(e) {
  e.preventDefault();
  const username = document.getElementById('loginUsername').value.trim();
  const password = document.getElementById('loginPassword').value;

  fetch('api.php?action=login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      closeAuthModal();
      checkCurrentUser();
    } else {
      showAuthAlert(data.message, 'error');
    }
  })
  .catch(err => {
    showAuthAlert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
  });
}

function handleRegisterSubmit(e) {
  e.preventDefault();
  const username = document.getElementById('regUsername').value.trim();
  const fullname = document.getElementById('regFullname').value.trim();
  const email = document.getElementById('regEmail').value.trim();
  const phone = document.getElementById('regPhone').value.trim();
  const password = document.getElementById('regPassword').value;

  fetch('api.php?action=register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, fullname, email, phone, password })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      closeAuthModal();
      checkCurrentUser();
    } else {
      showAuthAlert(data.message, 'error');
    }
  })
  .catch(err => {
    showAuthAlert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
  });
}

function handleLogout() {
  fetch('api.php?action=logout')
    .then(res => res.json())
    .then(data => {
      showToast(data.message, 'info');
      AppState.currentUser = null;
      updateAuthWidgetUI(null);
      if (AppState.activeTab === 'admin') {
        renderAdminBookingsTable();
      }
    });
}

// --- 12. Helper Utility Functions ---
function getStatusBadgeHTML(status) {
  switch (status) {
    case 'CONFIRMED':
      return '<span class="status-badge status-confirmed">CONFIRMED</span>';
    case 'PENDING':
      return '<span class="status-badge status-pending" style="background:rgba(234,179,8,0.2);color:#facc15;">PENDING</span>';
    case 'COMPLETED':
      return '<span class="status-badge status-completed" style="background:rgba(59,130,246,0.2);color:#60a5fa;">COMPLETED</span>';
    case 'CANCELLED':
      return '<span class="status-badge status-cancelled">CANCELLED</span>';
    default:
      return `<span class="status-badge">${escapeHtml(status)}</span>`;
  }
}

function formatDateThai(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  
  const months = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
  return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear() + 543}`;
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function showToast(msg, type = 'info') {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <div class="toast-content">
      <span>${escapeHtml(msg)}</span>
    </div>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

function togglePasswordVisibility(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  if (input.type === 'password') {
    input.type = 'text';
    btn.style.color = 'var(--accent-cyan)';
  } else {
    input.type = 'password';
    btn.style.color = 'var(--text-muted)';
  }
}
