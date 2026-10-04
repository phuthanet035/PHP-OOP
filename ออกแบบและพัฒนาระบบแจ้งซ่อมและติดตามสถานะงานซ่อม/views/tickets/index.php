<?php
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
?>

<div class="space-y-6">

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
    <div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">รายการแจ้งซ่อมและติดตามสถานะ</h1>
      <p class="text-sm text-slate-500 mt-1">
        <?php if ($currentUserRole === 'user'): ?>
          รายการคำขอแจ้งซ่อมอุปกรณ์ที่คุณส่งเรื่องเข้ามาในระบบ
        <?php elseif ($currentUserRole === 'technician'): ?>
          แดชบอร์ดติดตามและจัดการงานซ่อมของฝ่ายช่างเทคนิค
        <?php else: ?>
          ระบบบริหารจัดการใบแจ้งซ่อมและมอบหมายงานทั้งหมด
        <?php endif; ?>
      </p>
    </div>

    <div class="flex items-center gap-3">
      <a href="/tickets/create" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-md shadow-blue-500/20 transition transform active:scale-95 text-sm">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        แจ้งซ่อมใหม่
      </a>
    </div>
  </div>

  <!-- Status Tabs -->
  <div class="flex overflow-x-auto custom-scrollbar gap-2 pb-2">
    <?php
      $tabs = [
        ['key' => '', 'label' => 'ทั้งหมด', 'count' => $statusCounts['total'] ?? 0],
        ['key' => 'Open', 'label' => 'รอดำเนินการ', 'count' => $statusCounts['Open'] ?? 0],
        ['key' => 'Assigned', 'label' => 'มอบหมายช่างแล้ว', 'count' => $statusCounts['Assigned'] ?? 0],
        ['key' => 'InProgress', 'label' => 'กำลังซ่อม', 'count' => $statusCounts['InProgress'] ?? 0],
        ['key' => 'Resolved', 'label' => 'ซ่อมเสร็จแล้ว', 'count' => $statusCounts['Resolved'] ?? 0],
        ['key' => 'Closed', 'label' => 'ปิดงานสมบูรณ์', 'count' => $statusCounts['Closed'] ?? 0],
      ];
    ?>
    <?php foreach ($tabs as $tab): ?>
      <?php $isActive = ($currentStatus ?? '') === $tab['key']; ?>
      <a href="/tickets?status=<?= urlencode($tab['key']) ?>&q=<?= urlencode($keyword ?? '') ?>&priority=<?= urlencode($currentPriority ?? '') ?>&category_id=<?= urlencode($currentCategory ?? '') ?>&scope=<?= urlencode($currentScope ?? 'all') ?>"
         class="px-4 py-2 rounded-xl text-sm font-semibold whitespace-nowrap transition flex items-center gap-2 border <?= $isActive ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' ?>">
        <span><?= e($tab['label']) ?></span>
        <span class="px-2 py-0.5 text-xs rounded-full <?= $isActive ? 'bg-blue-700 text-white' : 'bg-slate-100 text-slate-600' ?>">
          <?= $tab['count'] ?>
        </span>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Search & Filter Controls -->
  <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
    <form action="/tickets" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
      <input type="hidden" name="status" value="<?= e($currentStatus ?? '') ?>">

      <!-- Search Input -->
      <div class="lg:col-span-5 relative">
        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <input type="text" name="q" value="<?= e($keyword ?? '') ?>" placeholder="ค้นหาเลขที่งาน, หัวข้อ, หรือผู้แจ้ง..."
               class="w-full pl-9 pr-4 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <!-- Category Filter -->
      <div class="lg:col-span-3">
        <select name="category_id" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
          <option value="">ทุกหมวดหมู่อุปกรณ์</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['id'] ?>" <?= ((string)$cat['id'] === (string)($currentCategory ?? '')) ? 'selected' : '' ?>>
              <?= e($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Priority Filter -->
      <div class="lg:col-span-2">
        <select name="priority" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
          <option value="">ทุกระดับความเร่งด่วน</option>
          <option value="Low" <?= ($currentPriority === 'Low') ? 'selected' : '' ?>>ต่ำ (Low)</option>
          <option value="Medium" <?= ($currentPriority === 'Medium') ? 'selected' : '' ?>>ปานกลาง (Medium)</option>
          <option value="High" <?= ($currentPriority === 'High') ? 'selected' : '' ?>>สูง (High)</option>
          <option value="Urgent" <?= ($currentPriority === 'Urgent') ? 'selected' : '' ?>>เร่งด่วนที่สุด (Urgent)</option>
        </select>
      </div>

      <!-- Scope filter for technician -->
      <?php if ($currentUserRole === 'technician'): ?>
        <div class="lg:col-span-2">
          <select name="scope" class="w-full px-3 py-2 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
            <option value="all" <?= ($currentScope === 'all') ? 'selected' : '' ?>>งานทั้งหมด</option>
            <option value="my_jobs" <?= ($currentScope === 'my_jobs') ? 'selected' : '' ?>>งานที่ฉันรับผิดชอบ</option>
          </select>
        </div>
      <?php endif; ?>

      <!-- Submit Filter Button -->
      <div class="<?= ($currentUserRole === 'technician') ? 'lg:col-span-12 flex justify-end gap-2' : 'lg:col-span-2 flex gap-2' ?>">
        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold transition">
          ค้นหา
        </button>
        <?php if (!empty($keyword) || !empty($currentStatus) || !empty($currentPriority) || !empty($currentCategory)): ?>
          <a href="/tickets" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-sm font-medium transition">
            ล้างตัวกรอง
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Tickets Cards Grid / Table -->
  <?php if (empty($tickets)): ?>
    <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center shadow-sm">
      <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
      </div>
      <h3 class="text-base font-bold text-slate-800">ไม่พบรายการแจ้งซ่อม</h3>
      <p class="text-sm text-slate-500 mt-1">ยังไม่มีข้อมูลที่ตรงกับเงื่อนไขการค้นหาของคุณ หรือยังไม่มีการสร้างคำขอ</p>
      <div class="mt-6">
        <a href="/tickets/create" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          แจ้งซ่อมใหม่ทันที
        </a>
      </div>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 gap-4">
      <?php foreach ($tickets as $ticket): ?>
        <?php
          $statusEnum = TicketStatus::tryFrom($ticket['status']) ?? TicketStatus::Open;
          $priorityEnum = TicketPriority::tryFrom($ticket['priority']) ?? TicketPriority::Medium;
          $step = $statusEnum->stepIndex();
        ?>
        <div class="bg-white rounded-2xl border border-slate-200/90 hover:border-blue-400 hover:shadow-md transition duration-200 p-5 group flex flex-col md:flex-row md:items-center justify-between gap-4">
          
          <!-- Left Ticket Details -->
          <div class="flex-1 space-y-2">
            <div class="flex flex-wrap items-center gap-2">
              <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-0.5 rounded-lg">
                #TK-<?= str_pad((string)$ticket['id'], 4, '0', STR_PAD_LEFT) ?>
              </span>
              <span class="text-xs font-medium px-2.5 py-0.5 rounded-md border <?= $priorityEnum->badgeClass() ?>">
                <?= $priorityEnum->label() ?>
              </span>
              <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-md">
                📂 <?= e($ticket['category_name']) ?>
              </span>
              <span class="text-xs text-slate-400">
                <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?>
              </span>
            </div>

            <a href="/tickets/<?= $ticket['id'] ?>" class="block">
              <h2 class="text-base font-bold text-slate-800 group-hover:text-blue-600 transition">
                <?= e($ticket['title']) ?>
              </h2>
            </a>

            <p class="text-sm text-slate-500 line-clamp-1">
              <?= e($ticket['description']) ?>
            </p>

            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-1">
              <div class="flex items-center gap-1.5">
                <span class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-700">
                  <?= strtoupper(mb_substr($ticket['user_name'], 0, 1)) ?>
                </span>
                <span>ผู้แจ้ง: <strong class="text-slate-700"><?= e($ticket['user_name']) ?></strong></span>
              </div>

              <div class="flex items-center gap-1.5">
                <span class="text-slate-400">ช่างเทคนิค:</span>
                <?php if (!empty($ticket['technician_name'])): ?>
                  <span class="text-blue-700 font-semibold flex items-center gap-1">
                    🔧 <?= e($ticket['technician_name']) ?>
                  </span>
                <?php else: ?>
                  <span class="text-amber-600 font-medium italic">ยังไม่มอบหมายช่าง</span>
                <?php endif; ?>
              </div>

              <?php if (!empty($ticket['rating_score'])): ?>
                <div class="flex items-center gap-1 text-amber-500 font-bold">
                  ★ <?= $ticket['rating_score'] ?>/5 ดาว
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Right Status & Stepper Tracker -->
          <div class="flex flex-col md:items-end justify-between gap-3 border-t md:border-t-0 md:border-l border-slate-100 pt-3 md:pt-0 md:pl-5 min-w-[200px]">
            <div>
              <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border <?= $statusEnum->badgeClass() ?>">
                <span class="w-2 h-2 rounded-full <?= $statusEnum->dotColor() ?> <?= ($statusEnum === TicketStatus::InProgress) ? 'animate-ping' : '' ?>"></span>
                <?= $statusEnum->label() ?>
              </span>
            </div>

            <!-- Mini Progress Bar (5 Steps) -->
            <div class="w-full max-w-[160px]">
              <div class="flex justify-between text-[10px] text-slate-400 mb-1 font-semibold">
                <span>ความคืบหน้า</span>
                <span>ขั้นที่ <?= $step ?>/5</span>
              </div>
              <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-300" style="width: <?= ($step / 5) * 100 ?>%"></div>
              </div>
            </div>

            <a href="/tickets/<?= $ticket['id'] ?>" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 group-hover:text-blue-700 transition">
              ดูรายละเอียด & ติดตาม
              <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
