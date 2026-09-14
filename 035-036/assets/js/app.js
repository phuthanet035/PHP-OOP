/**
 * SpaceHub - Front-end Application Controller
 * Modern SPA-like interactions, Real-time calculations, and API Bridge
 */

// Available add-on services catalog
const ADDON_CATALOG = [
  { id: 'coffee', name: 'ชุดกาแฟสดพรีเมียม & เบเกอรี่ฝรั่งเศส', price: 120, perPerson: true, desc: 'เสิร์ฟตามจำนวนผู้เข้าร่วม (120฿/คน)' },
  { id: 'projector_4k', name: 'ระบบเชื่อมต่อไร้สาย 4K Wireless ClickShare', price: 350, perPerson: false, desc: 'อุปกรณ์สลับหน้าจอความเร็วสูง (350฿/รอบ)' },
  { id: 'sound_eng', name: 'เจ้าหน้าที่เทคนิคประจำห้องคุมระบบภาพ-เสียง', price: 600, perPerson: false, desc: 'ดูแลตลอดการประชุม (600฿/รอบ)' },
  { id: 'whiteboard_kit', name: 'ชุดอุปกรณ์ Post-it & ปากกา Brainstorming', price: 250, perPerson: false, desc: 'เซ็ตอุปกรณ์ Workshop ครบชุด (250฿/ชุด)' },
];

const AppState = {
  rooms: [],
  availableRoomIds: [],
  selectedRoom: null,
  activeTab: 'booking',
  filter: {
    date: new Date().toISOString().split('T')[0],
    start: '09:00',
    end: '12:00',
    attendees: 1
  }
};

// Initialize Application
document.addEventListener('DOMContentLoaded', () => {
  initNavigation();
  initFilterControls();
  initBookingModal();
  initLookupTab();
  initScheduleTab();
  
  // Set default filter date input to today
  const dateInput = document.getElementById('filterDate');
  if (dateInput) {
    const todayStr = new Date().toISOString().split('T')[0];
    dateInput.value = todayStr;
    dateInput.min = todayStr;
    AppState.filter.date = todayStr;
  }

  // Load initial rooms and check availability
  loadRoomsAndCheckAvailability();
});

// --- Tab Navigation ---
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
  
  // Update button active state
  document.querySelectorAll('.nav-btn').forEach(btn => {
    btn.classList.toggle('active', btn.getAttribute('data-tab') === tabId);
  });

  // Update view visibility
  document.querySelectorAll('.tab-view').forEach(view => {
    view.classList.toggle('active', view.id === `${tabId}-view`);
  });

  // Lazy load tab data
  if (tabId === 'schedule') {
    renderScheduleGrid();
  } else if (tabId === 'stats') {
    loadDashboardStats();
  }
}

// --- Rooms & Availability Logic ---
async function loadRoomsAndCheckAvailability() {
  try {
    const res = await fetch(`api.php?action=check_availability&date=${AppState.filter.date}&start=${AppState.filter.start}&end=${AppState.filter.end}&attendees=${AppState.filter.attendees}`);
    const result = await res.json();

    if (result.success) {
      AppState.availableRoomIds = result.availableRoomIds || [];
      
      // Fetch all rooms if not loaded
      if (AppState.rooms.length === 0) {
        const roomsRes = await fetch('api.php?action=get_rooms');
        const roomsData = await roomsRes.json();
        if (roomsData.success) {
          AppState.rooms = roomsData.data;
        }
      }
      renderRoomCards();
    } else {
      showToast(result.message || 'ไม่สามารถโหลดข้อมูลห้องได้', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('การเชื่อมต่อขัดข้อง กรุณาลองใหม่อีกครั้ง', 'error');
  }
}

function initFilterControls() {
  const dateInput = document.getElementById('filterDate');
  const startSelect = document.getElementById('filterStartTime');
  const endSelect = document.getElementById('filterEndTime');
  const attendeesInput = document.getElementById('filterAttendees');
  const checkBtn = document.getElementById('btnCheckAvailability');

  if (checkBtn) {
    checkBtn.addEventListener('click', () => {
      const date = dateInput.value;
      const start = startSelect.value;
      const end = endSelect.value;
      const attendees = parseInt(attendeesInput.value, 10) || 1;

      if (!date) {
        showToast('กรุณาเลือกวันที่ต้องการจอง', 'error');
        return;
      }
      if (start >= end) {
        showToast('เวลาสิ้นสุดต้องมากกว่าเวลาเริ่มต้น', 'error');
        return;
      }

      AppState.filter = { date, start, end, attendees };
      loadRoomsAndCheckAvailability();
      showToast(`อัปเดตตารางเวลา: ${date} (${start} - ${end} น.)`, 'info');
    });
  }
}

function renderRoomCards() {
  const container = document.getElementById('roomsContainer');
  if (!container) return;

  container.innerHTML = '';

  AppState.rooms.forEach(room => {
    const isAvailable = AppState.availableRoomIds.includes(room.id);
    const isCapacityOk = room.capacity >= AppState.filter.attendees;

    const card = document.createElement('div');
    card.className = 'room-card';

    // Amenities tags HTML
    const amenitiesHtml = (room.amenities || [])
      .slice(0, 4)
      .map(item => `<span class="amenity-pill">${escapeHtml(item)}</span>`)
      .join('');

    card.innerHTML = `
      <div class="room-img-wrapper">
        <img src="${escapeHtml(room.imageUrl)}" alt="${escapeHtml(room.name)}" class="room-img" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80'">
        <span class="room-badge">${escapeHtml(room.type)}</span>
        <span class="room-status-tag ${isAvailable && isCapacityOk ? 'available' : 'busy'}">
          <span class="pulse-dot" style="${isAvailable && isCapacityOk ? '' : 'background:#ef4444;box-shadow:0 0 8px #ef4444;'}"></span>
          ${isAvailable && isCapacityOk ? 'ว่างพร้อมจอง' : (!isCapacityOk ? 'ความจุไม่พอ' : 'มีผู้จองแล้ว')}
        </span>
      </div>
      <div class="room-body">
        <div class="room-header-row">
          <h3 class="room-name">${escapeHtml(room.name)}</h3>
          <div class="room-rate">
            <span class="rate-amount">฿${Number(room.hourlyRate).toLocaleString()}</span>
            <span class="rate-unit">/ ชั่วโมง</span>
          </div>
        </div>
        <p class="room-desc">${escapeHtml(room.description)}</p>

        <div class="room-specs">
          <div class="spec-item">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            <span>รองรับได้ ${room.capacity} ท่าน</span>
          </div>
          <div class="spec-item">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>ขั้นต่ำ ${room.minHours} ชม.</span>
          </div>
        </div>

        <div class="amenities-list">
          ${amenitiesHtml}
        </div>

        <div class="room-footer">
          <button class="btn-book-room" ${isAvailable && isCapacityOk ? '' : 'disabled'} onclick="openBookingModal('${room.id}')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            ${isAvailable && isCapacityOk ? 'จองห้องนี้ทันที' : 'ไม่สามารถจองได้ในเวลานี้'}
          </button>
        </div>
      </div>
    `;

    container.appendChild(card);
  });
}

// --- Booking Modal & Form Logic ---
function initBookingModal() {
  const modal = document.getElementById('bookingModal');
  const closeBtn = document.getElementById('btnModalClose');
  const form = document.getElementById('bookingForm');

  if (closeBtn) {
    closeBtn.addEventListener('click', () => closeModal());
  }

  // Close when clicking backdrop
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  // Handle form submit
  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      await submitBookingForm();
    });
  }
}

window.openBookingModal = function(roomId) {
  const room = AppState.rooms.find(r => r.id === roomId);
  if (!room) return;

  AppState.selectedRoom = room;

  // Fill in summary items
  document.getElementById('modalRoomName').textContent = room.name;
  document.getElementById('modalRoomType').textContent = room.type;
  document.getElementById('modalDateTime').textContent = `${AppState.filter.date} (${AppState.filter.start} - ${AppState.filter.end} น.)`;
  
  // Set attendee count
  const attendeesInput = document.getElementById('formAttendees');
  attendeesInput.value = AppState.filter.attendees;
  attendeesInput.max = room.capacity;

  // Render add-on checkboxes
  renderAddOnCheckboxes();

  // Recalculate and show modal
  calculateBookingPrice();

  const modal = document.getElementById('bookingModal');
  modal.classList.add('show');
};

function closeModal() {
  const modal = document.getElementById('bookingModal');
  modal.classList.remove('show');
}

function renderAddOnCheckboxes() {
  const container = document.getElementById('addonsListContainer');
  if (!container) return;

  container.innerHTML = '';

  ADDON_CATALOG.forEach(addon => {
    const card = document.createElement('label');
    card.className = 'addon-card';
    card.innerHTML = `
      <div class="addon-left">
        <input type="checkbox" name="addons" value="${addon.id}" onchange="calculateBookingPrice()">
        <div class="addon-info">
          <div class="name">${escapeHtml(addon.name)}</div>
          <div class="desc">${escapeHtml(addon.desc)}</div>
        </div>
      </div>
      <div class="addon-price">+฿${addon.price.toLocaleString()}</div>
    `;
    container.appendChild(card);
  });
}

function calculateBookingPrice() {
  if (!AppState.selectedRoom) return;

  // Duration calculation
  const [sH, sM] = AppState.filter.start.split(':').map(Number);
  const [eH, eM] = AppState.filter.end.split(':').map(Number);
  const durationHours = Math.max(0.5, ((eH * 60 + eM) - (sH * 60 + sM)) / 60);

  // Attendees
  const attendees = parseInt(document.getElementById('formAttendees')?.value, 10) || AppState.filter.attendees;

  // Room charge
  const baseRate = AppState.selectedRoom.hourlyRate;
  const baseTotal = durationHours * baseRate;

  // Add-ons
  let addonsTotal = 0;
  const checkedAddons = document.querySelectorAll('input[name="addons"]:checked');
  checkedAddons.forEach(cb => {
    const addon = ADDON_CATALOG.find(a => a.id === cb.value);
    if (addon) {
      const multiplier = addon.perPerson ? attendees : 1;
      addonsTotal += (addon.price * multiplier);
    }
  });

  const subtotal = baseTotal + addonsTotal;
  const tax = subtotal * 0.07;
  const grandTotal = subtotal + tax;

  // Update labels
  document.getElementById('calcDuration').textContent = `${durationHours.toFixed(1)} ชั่วโมง`;
  document.getElementById('calcBasePrice').textContent = `฿${baseTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
  document.getElementById('calcAddonsPrice').textContent = `฿${addonsTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
  document.getElementById('calcTaxPrice').textContent = `฿${tax.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
  document.getElementById('calcTotalPrice').textContent = `฿${grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
}

// Recalculate when attendees input changes
window.onAttendeeChange = function() {
  calculateBookingPrice();
};

async function submitBookingForm() {
  if (!AppState.selectedRoom) return;

  const submitBtn = document.getElementById('btnSubmitBooking');
  submitBtn.disabled = true;
  submitBtn.innerHTML = 'กำลังประมวลผล...';

  // Gather form values
  const customerName = document.getElementById('formCustomerName').value.trim();
  const customerPhone = document.getElementById('formCustomerPhone').value.trim();
  const customerEmail = document.getElementById('formCustomerEmail').value.trim();
  const organization = document.getElementById('formOrganization').value.trim();
  const purpose = document.getElementById('formPurpose').value.trim();
  const notes = document.getElementById('formNotes').value.trim();
  const attendeeCount = parseInt(document.getElementById('formAttendees').value, 10) || 1;

  // Gather selected addons
  const selectedAddons = [];
  document.querySelectorAll('input[name="addons"]:checked').forEach(cb => {
    const addon = ADDON_CATALOG.find(a => a.id === cb.value);
    if (addon) {
      selectedAddons.push({
        id: addon.id,
        name: addon.name,
        price: addon.price,
        quantity: addon.perPerson ? attendeeCount : 1
      });
    }
  });

  const payload = {
    roomId: AppState.selectedRoom.id,
    customerName,
    customerPhone,
    customerEmail,
    organization,
    purpose,
    notes,
    bookingDate: AppState.filter.date,
    startTime: AppState.filter.start,
    endTime: AppState.filter.end,
    attendeeCount,
    addOnServices: selectedAddons
  };

  try {
    const res = await fetch('api.php?action=create_booking', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const result = await res.json();
    if (result.success) {
      closeModal();
      showToast('การจองสำเร็จ! กำลังแสดงใบยืนยันการจอง', 'success');
      
      // Reset form
      document.getElementById('bookingForm').reset();

      // Show Voucher Modal
      showBookingVoucher(result.booking);

      // Refresh availability & background data
      loadRoomsAndCheckAvailability();
    } else {
      showToast(result.message || 'ไม่สามารถทำการจองได้', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
  } finally {
    submitBtn.disabled = false;
    submitBtn.innerHTML = 'ยืนยันการจอง (Confirm Booking)';
  }
}

// --- Digital Voucher Presentation ---
function showBookingVoucher(booking) {
  const modal = document.getElementById('voucherModal');
  if (!modal) return;

  document.getElementById('vCode').textContent = booking.bookingCode;
  document.getElementById('vCustomer').textContent = booking.customerName;
  document.getElementById('vContact').textContent = `${booking.customerPhone || '-'} / ${booking.customerEmail || '-'}`;
  document.getElementById('vOrg').textContent = booking.organization || 'บุคคลทั่วไป';
  document.getElementById('vRoom').textContent = `${booking.roomName} (${booking.roomType})`;
  document.getElementById('vDateTime').textContent = `${booking.bookingDate} เวลา ${booking.startTime} - ${booking.endTime} น. (${booking.durationHours} ชม.)`;
  document.getElementById('vAttendees').textContent = `${booking.attendeeCount} ท่าน`;
  document.getElementById('vPurpose').textContent = booking.purpose || '-';
  document.getElementById('vTotal').textContent = `฿${Number(booking.totalAmount).toLocaleString(undefined, {minimumFractionDigits: 2})}`;

  // Add-ons list
  const addOnsContainer = document.getElementById('vAddonsList');
  if (addOnsContainer) {
    if (booking.addOnServices && booking.addOnServices.length > 0) {
      addOnsContainer.innerHTML = booking.addOnServices
        .map(a => `<div>• ${escapeHtml(a.name)} (x${a.quantity || 1}) - ฿${(a.price * (a.quantity || 1)).toLocaleString()}</div>`)
        .join('');
    } else {
      addOnsContainer.innerHTML = '<span style="color:var(--text-dim)">ไม่มีบริการเสริม</span>';
    }
  }

  modal.classList.add('show');
}

window.closeVoucherModal = function() {
  const modal = document.getElementById('voucherModal');
  if (modal) modal.classList.remove('show');
};

// --- Schedule Grid View ---
async function renderScheduleGrid() {
  const container = document.getElementById('timelineContainer');
  const dateInput = document.getElementById('scheduleDateInput');
  const targetDate = dateInput ? dateInput.value : AppState.filter.date;

  if (!container) return;
  container.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted)">กำลังโหลดตารางการจอง...</div>';

  try {
    const [roomsRes, bookingsRes] = await Promise.all([
      fetch('api.php?action=get_rooms').then(r => r.json()),
      fetch(`api.php?action=get_bookings&date=${targetDate}&status=CONFIRMED`).then(r => r.json())
    ]);

    const rooms = roomsRes.data || [];
    const bookings = bookingsRes.data || [];

    // Working hours: 08:00 to 20:00 (12 slots)
    const hours = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'];

    let html = `
      <table class="timeline-table">
        <thead>
          <tr>
            <th>ห้องประชุม / พื้นที่</th>
            ${hours.map(h => `<th>${h}</th>`).join('')}
          </tr>
        </thead>
        <tbody>
    `;

    rooms.forEach(room => {
      html += `<tr><td class="room-label">${escapeHtml(room.name)}<br><small style="color:var(--text-dim)">${escapeHtml(room.type)}</small></td>`;

      hours.forEach(hour => {
        const slotHour = parseInt(hour.split(':')[0], 10);
        
        // Find if any booking occupies this slot hour
        const matchedBooking = bookings.find(b => {
          if (b.roomId !== room.id) return false;
          const [sH] = b.startTime.split(':').map(Number);
          const [eH] = b.endTime.split(':').map(Number);
          return slotHour >= sH && slotHour < eH;
        });

        if (matchedBooking) {
          html += `<td class="time-slot-cell booked" title="รหัส: ${matchedBooking.bookingCode} โดย ${escapeHtml(matchedBooking.customerName)} (${matchedBooking.startTime} - ${matchedBooking.endTime})">
            ${escapeHtml(matchedBooking.customerName)}
          </td>`;
        } else {
          html += `<td class="time-slot-cell" style="color:var(--text-dim);font-size:0.75rem;">ว่าง</td>`;
        }
      });

      html += '</tr>';
    });

    html += '</tbody></table>';
    container.innerHTML = html;
  } catch (err) {
    console.error(err);
    container.innerHTML = '<div style="padding:20px;text-align:center;color:var(--accent-rose)">ไม่สามารถโหลดตารางการจองได้</div>';
  }
}

function initScheduleTab() {
  const dateInput = document.getElementById('scheduleDateInput');
  if (dateInput) {
    dateInput.value = AppState.filter.date;
    dateInput.addEventListener('change', () => renderScheduleGrid());
  }
}

// --- Lookup Tab ---
function initLookupTab() {
  const btnSearch = document.getElementById('btnSearchBooking');
  const inputSearch = document.getElementById('lookupInput');

  if (btnSearch && inputSearch) {
    btnSearch.addEventListener('click', () => performBookingLookup());
    inputSearch.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') performBookingLookup();
    });
  }
}

async function performBookingLookup() {
  const query = document.getElementById('lookupInput').value.trim();
  const resultsContainer = document.getElementById('lookupResults');

  if (!query) {
    showToast('กรุณากรอกรหัสการจอง หรือ เบอร์โทรศัพท์', 'error');
    return;
  }

  resultsContainer.innerHTML = '<div style="padding:20px;text-align:center;color:var(--text-muted)">กำลังค้นหา...</div>';

  try {
    const res = await fetch(`api.php?action=lookup_booking&code=${encodeURIComponent(query)}`);
    const result = await res.json();

    if (!result.success) {
      resultsContainer.innerHTML = `
        <div style="text-align:center;padding:40px 20px;background:var(--bg-card);border-radius:var(--radius-lg);border:1px solid var(--border-color);">
          <svg style="width:48px;height:48px;stroke:var(--accent-rose);margin-bottom:12px;" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          <h3 style="font-size:1.15rem;margin-bottom:6px;">ไม่พบรายการจอง</h3>
          <p style="color:var(--text-muted);font-size:0.9rem;">${escapeHtml(result.message)}</p>
        </div>
      `;
      return;
    }

    const items = result.isList ? result.data : [result.data];

    resultsContainer.innerHTML = items.map(b => `
      <div class="voucher-card" style="margin-bottom:20px;">
        <div class="voucher-header">
          <div>
            <span class="status-badge ${b.status === 'CONFIRMED' ? 'confirmed' : 'cancelled'}">${b.status === 'CONFIRMED' ? '✓ ได้รับการยืนยันแล้ว' : '✕ ยกเลิกแล้ว'}</span>
            <h3 style="font-size:1.3rem;margin-top:8px;">${escapeHtml(b.roomName)}</h3>
            <p style="color:var(--text-muted);font-size:0.85rem;">${escapeHtml(b.organization || 'บุคคลทั่วไป')}</p>
          </div>
          <div class="voucher-code-badge">${escapeHtml(b.bookingCode)}</div>
        </div>

        <div class="voucher-details-grid">
          <div class="voucher-item">
            <div class="title">ผู้จอง</div>
            <div class="value">${escapeHtml(b.customerName)} (${escapeHtml(b.customerPhone || '-')})</div>
          </div>
          <div class="voucher-item">
            <div class="title">วันที่และเวลา</div>
            <div class="value">${escapeHtml(b.bookingDate)} | ${escapeHtml(b.startTime)} - ${escapeHtml(b.endTime)} น.</div>
          </div>
          <div class="voucher-item">
            <div class="title">วัตถุประสงค์</div>
            <div class="value">${escapeHtml(b.purpose || '-')}</div>
          </div>
          <div class="voucher-item">
            <div class="title">ยอดชำระสุทธิ</div>
            <div class="value" style="color:var(--accent-emerald);">฿${Number(b.totalAmount).toLocaleString(undefined, {minimumFractionDigits: 2})}</div>
          </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;border-top:1px solid rgba(255,255,255,0.08);padding-top:16px;">
          <button class="btn-secondary" onclick="showBookingVoucher(${JSON.stringify(b).replace(/"/g, '&quot;')})">
            ดูใบเสร็จดิจิทัล
          </button>
          ${b.status === 'CONFIRMED' ? `
            <button class="btn-secondary" style="border-color:rgba(239,68,68,0.4);color:var(--accent-rose);" onclick="cancelBookingAction('${b.bookingCode}')">
              ยกเลิกการจองนี้
            </button>
          ` : ''}
        </div>
      </div>
    `).join('');

  } catch (err) {
    console.error(err);
    resultsContainer.innerHTML = '<div style="padding:20px;text-align:center;color:var(--accent-rose)">การเชื่อมต่อขัดข้อง</div>';
  }
}

window.cancelBookingAction = async function(bookingCode) {
  if (!confirm(`คุณต้องการยกเลิกการจองรหัส ${bookingCode} ใช่หรือไม่?`)) {
    return;
  }

  try {
    const res = await fetch('api.php?action=cancel_booking', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ bookingCode })
    });

    const result = await res.json();
    if (result.success) {
      showToast(`ยกเลิกรายการจอง ${bookingCode} เรียบร้อยแล้ว`, 'success');
      performBookingLookup();
      loadRoomsAndCheckAvailability();
    } else {
      showToast(result.message || 'ไม่สามารถยกเลิกได้', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('เกิดข้อผิดพลาดในการยกเลิกรายการ', 'error');
  }
};

// --- Analytics Dashboard Tab ---
async function loadDashboardStats() {
  try {
    const [statsRes, bookingsRes] = await Promise.all([
      fetch('api.php?action=get_stats').then(r => r.json()),
      fetch('api.php?action=get_bookings').then(r => r.json())
    ]);

    if (statsRes.success) {
      const s = statsRes.data;
      document.getElementById('statTotalBookings').textContent = s.totalBookings;
      document.getElementById('statConfirmed').textContent = s.confirmedBookings;
      document.getElementById('statTodayBookings').textContent = s.todayBookings;
      document.getElementById('statRevenue').textContent = `฿${Number(s.totalRevenue).toLocaleString()}`;
    }

    if (bookingsRes.success) {
      renderRecentBookingsTable(bookingsRes.data);
    }
  } catch (err) {
    console.error(err);
  }
}

function renderRecentBookingsTable(bookings) {
  const tbody = document.getElementById('recentBookingsTableBody');
  if (!tbody) return;

  if (!bookings || bookings.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text-muted)">ยังไม่มีรายการจองในระบบ</td></tr>';
    return;
  }

  tbody.innerHTML = bookings.slice(0, 10).map(b => `
    <tr>
      <td><strong style="font-family:var(--font-display);color:var(--accent-cyan);">${escapeHtml(b.bookingCode)}</strong></td>
      <td>
        <div style="font-weight:600;">${escapeHtml(b.customerName)}</div>
        <div style="font-size:0.75rem;color:var(--text-dim);">${escapeHtml(b.organization || '-')}</div>
      </td>
      <td>
        <div>${escapeHtml(b.roomName)}</div>
        <div style="font-size:0.75rem;color:var(--text-dim);">${escapeHtml(b.roomType)}</div>
      </td>
      <td>${escapeHtml(b.bookingDate)}<br><small style="color:var(--text-dim)">${b.startTime} - ${b.endTime}</small></td>
      <td>${b.attendeeCount} ท่าน</td>
      <td><strong style="color:var(--accent-emerald);">฿${Number(b.totalAmount).toLocaleString()}</strong></td>
      <td>
        <span class="status-badge ${b.status === 'CONFIRMED' ? 'confirmed' : 'cancelled'}">
          ${b.status === 'CONFIRMED' ? 'ยืนยัน' : 'ยกเลิก'}
        </span>
      </td>
    </tr>
  `).join('');
}

// --- Utility Helpers ---
function showToast(message, type = 'info') {
  const container = document.getElementById('toastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      ${type === 'success' ? '<polyline points="20 6 9 17 4 12"></polyline>' : 
        type === 'error' ? '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>' :
        '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>'}
    </svg>
    <span>${escapeHtml(message)}</span>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(50px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
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
