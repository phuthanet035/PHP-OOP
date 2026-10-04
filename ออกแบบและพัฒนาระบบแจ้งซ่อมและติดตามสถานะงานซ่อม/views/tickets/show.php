<?php
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;

$statusEnum = TicketStatus::tryFrom($ticket['status']) ?? TicketStatus::Open;
$priorityEnum = TicketPriority::tryFrom($ticket['priority']) ?? TicketPriority::Medium;
$currentStep = $statusEnum->stepIndex();

$isOwner = ((int)$currentUser['id'] === (int)$ticket['user_id']);
$isAssignedTech = ((int)$currentUser['id'] === (int)($ticket['technician_id'] ?? 0));
$isAdmin = ($currentUser['role'] === 'admin');
$canManageJob = ($isAssignedTech || $isAdmin);
?>

<div class="space-y-6 max-w-6xl mx-auto">

  <!-- Breadcrumb & Back -->
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-2 text-sm text-slate-500">
      <a href="/tickets" class="hover:text-blue-600 flex items-center gap-1 font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        รายการทั้งหมด
      </a>
      <span>/</span>
      <span class="font-bold text-slate-700">#TK-<?= str_pad((string)$ticket['id'], 4, '0', STR_PAD_LEFT) ?></span>
    </div>

    <!-- Quick Status Badge -->
    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border <?= $statusEnum->badgeClass() ?>">
      <span class="w-2 h-2 rounded-full <?= $statusEnum->dotColor() ?> <?= ($statusEnum === TicketStatus::InProgress) ? 'animate-ping' : '' ?>"></span>
      <?= $statusEnum->label() ?>
    </span>
  </div>

  <!-- Ticket Header Hero Card -->
  <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm space-y-6">
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
      <div class="space-y-2">
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
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight leading-snug">
          <?= e($ticket['title']) ?>
        </h1>
        <p class="text-xs text-slate-400">
          แจ้งเมื่อวันที่ <?= date('d/m/Y เวลา H:i น.', strtotime($ticket['created_at'])) ?>
        </p>
      </div>

      <!-- Action Buttons Based on Role and State Machine -->
      <div class="flex flex-wrap gap-2 shrink-0">
        
        <!-- Case 1: Open -> Assigned (Admin Only) -->
        <?php if ($statusEnum === TicketStatus::Open && $isAdmin): ?>
          <button onclick="document.getElementById('assignModal').classList.remove('hidden')"
                  class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            มอบหมายช่างเทคนิค
          </button>
        <?php endif; ?>

        <!-- Case 2: Assigned -> InProgress (Assigned Tech or Admin) -->
        <?php if ($statusEnum === TicketStatus::Assigned && $canManageJob): ?>
          <form action="/tickets/<?= $ticket['id'] ?>/status" method="POST">
            <?= $csrfField ?>
            <input type="hidden" name="status" value="InProgress">
            <input type="hidden" name="note" value="ช่างกดรับงานและเริ่มตรวจสอบ">
            <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-semibold shadow-sm transition flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              กดรับงาน & เริ่มซ่อม
            </button>
          </form>
        <?php endif; ?>

        <!-- Case 3: InProgress -> Resolved (Assigned Tech or Admin) -->
        <?php if ($statusEnum === TicketStatus::InProgress && $canManageJob): ?>
          <button onclick="document.getElementById('resolveModal').classList.remove('hidden')"
                  class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-sm transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            บันทึกซ่อมเสร็จสิ้น (แนบรูป)
          </button>
        <?php endif; ?>

        <!-- Case 4: Resolved -> Closed OR InProgress (Ticket Owner or Admin) -->
        <?php if ($statusEnum === TicketStatus::Resolved && ($isOwner || $isAdmin)): ?>
          <button onclick="document.getElementById('rateModal').classList.remove('hidden')"
                  class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-sm transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            ยืนยันผลงาน & ให้คะแนน (ปิดงาน)
          </button>
          <button onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                  class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-sm font-semibold transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            ผลงานไม่ผ่าน / ส่งซ่อมต่อ
          </button>
        <?php endif; ?>

      </div>
    </div>

    <!-- 5-Step Progress Stepper -->
    <div class="pt-6 border-t border-slate-100">
      <div class="relative">
        <div class="hidden sm:block absolute top-1/2 left-0 right-0 h-1 bg-slate-200 -translate-y-1/2 z-0"></div>
        <div class="hidden sm:block absolute top-1/2 left-0 h-1 bg-gradient-to-r from-blue-600 to-indigo-600 -translate-y-1/2 z-0 transition-all duration-500"
             style="width: <?= (($currentStep - 1) / 4) * 100 ?>%"></div>

        <div class="relative z-10 grid grid-cols-2 sm:grid-cols-5 gap-3">
          <?php
            $steps = [
              1 => ['key' => 'Open', 'title' => 'รับแจ้งเรื่อง', 'desc' => 'บันทึกคำขอ'],
              2 => ['key' => 'Assigned', 'title' => 'มอบหมายช่าง', 'desc' => 'เลือกช่างเทคนิค'],
              3 => ['key' => 'InProgress', 'title' => 'กำลังซ่อม', 'desc' => 'ดำเนินการแก้ไข'],
              4 => ['key' => 'Resolved', 'title' => 'ซ่อมเสร็จสิ้น', 'desc' => 'แนบรูปผลงาน'],
              5 => ['key' => 'Closed', 'title' => 'ปิดงานสมบูรณ์', 'desc' => 'ประเมินความพึงพอใจ'],
            ];
          ?>
          <?php foreach ($steps as $num => $info): ?>
            <?php
              $isCompleted = ($currentStep > $num);
              $isCurrent = ($currentStep === $num);
            ?>
            <div class="flex sm:flex-col items-center sm:text-center gap-3 sm:gap-2">
              <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-sm
                          <?= $isCompleted ? 'bg-emerald-500 text-white ring-4 ring-emerald-100' : '' ?>
                          <?= $isCurrent ? 'bg-blue-600 text-white ring-4 ring-blue-100 shadow-md scale-110' : '' ?>
                          <?= (!$isCompleted && !$isCurrent) ? 'bg-white border-2 border-slate-300 text-slate-400' : '' ?>">
                <?php if ($isCompleted): ?>
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                <?php else: ?>
                  <?= $num ?>
                <?php endif; ?>
              </div>
              <div>
                <p class="text-xs font-bold <?= $isCurrent ? 'text-blue-700' : ($isCompleted ? 'text-slate-800' : 'text-slate-400') ?>">
                  <?= $info['title'] ?>
                </p>
                <p class="text-[11px] text-slate-400 hidden sm:block"><?= $info['desc'] ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- People Info Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100">
      <!-- Requester Info -->
      <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm">
          <?= strtoupper(mb_substr($ticket['user_name'], 0, 1)) ?>
        </div>
        <div>
          <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">ผู้แจ้งซ่อม (Requester)</span>
          <span class="text-sm font-bold text-slate-800"><?= e($ticket['user_name']) ?></span>
          <span class="text-xs text-slate-500 block"><?= e($ticket['user_email']) ?></span>
        </div>
      </div>

      <!-- Technician Info -->
      <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full <?= !empty($ticket['technician_name']) ? 'bg-amber-100 text-amber-700' : 'bg-slate-200 text-slate-400' ?> flex items-center justify-center font-bold text-sm">
          🔧
        </div>
        <div>
          <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">ช่างผู้รับผิดชอบ (Assigned Tech)</span>
          <?php if (!empty($ticket['technician_name'])): ?>
            <span class="text-sm font-bold text-slate-800"><?= e($ticket['technician_name']) ?></span>
            <span class="text-xs text-slate-500 block"><?= e($ticket['technician_email']) ?></span>
          <?php else: ?>
            <span class="text-sm font-medium text-amber-600 italic">ยังไม่มอบหมายช่างผู้ดูแล</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Satisfaction Rating Banner if Closed -->
  <?php if (!empty($rating)): ?>
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 p-6 rounded-2xl shadow-sm">
      <div class="flex items-start gap-4">
        <div class="text-3xl">⭐</div>
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <h3 class="font-bold text-slate-900">ผลการประเมินความพึงพอใจ:</h3>
            <div class="flex text-amber-400 text-lg">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <span><?= ($i <= $rating['score']) ? '★' : '☆' ?></span>
              <?php endfor; ?>
            </div>
            <span class="text-sm font-bold text-amber-800">(<?= $rating['score'] ?> / 5 ดาว)</span>
          </div>
          <?php if (!empty($rating['feedback'])): ?>
            <p class="text-sm text-slate-700 italic bg-white/70 p-3 rounded-xl border border-amber-100">
              "<?= e($rating['feedback']) ?>"
            </p>
          <?php endif; ?>
          <p class="text-xs text-slate-400">ประเมินเมื่อ <?= date('d/m/Y H:i', strtotime($rating['created_at'])) ?></p>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Main Content Grid: Left Details & Right Audit Logs -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left Column: Problem Details & Comments (2 Cols) -->
    <div class="lg:col-span-2 space-y-6">

      <!-- Problem Description Card -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
          <span>📋</span> รายละเอียดอาการเสีย
        </h3>
        <p class="text-slate-700 text-sm whitespace-pre-line leading-relaxed">
          <?= e($ticket['description']) ?>
        </p>
      </div>

      <!-- Discussion & Comments Thread -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-6">
        <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center justify-between">
          <span class="flex items-center gap-2">
            <span>💬</span> ข้อความการสื่อสาร & รูปภาพประกอบ (<?= count($comments) ?>)
          </span>
        </h3>

        <!-- Comments List -->
        <div class="space-y-4">
          <?php if (empty($comments)): ?>
            <p class="text-sm text-slate-400 text-center py-4">ยังไม่มีข้อความเพิ่มเติมในงานนี้</p>
          <?php else: ?>
            <?php foreach ($comments as $comment): ?>
              <?php
                $isCommentSelf = ((int)$currentUser['id'] === (int)$comment['user_id']);
                $roleLabel = match($comment['user_role']) {
                  'admin' => 'Admin',
                  'technician' => 'ช่างเทคนิค',
                  default => 'ผู้แจ้งซ่อม'
                };
              ?>
              <div class="flex items-start gap-3 <?= $isCommentSelf ? 'flex-row-reverse' : '' ?>">
                <div class="w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center text-xs font-bold shrink-0">
                  <?= strtoupper(mb_substr($comment['user_name'], 0, 1)) ?>
                </div>

                <div class="max-w-[85%] rounded-2xl p-4 <?= $isCommentSelf ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-800' ?>">
                  <div class="flex items-center gap-2 text-xs mb-1 <?= $isCommentSelf ? 'text-blue-100 justify-end' : 'text-slate-500' ?>">
                    <span class="font-bold"><?= e($comment['user_name']) ?></span>
                    <span>(<?= $roleLabel ?>)</span>
                    <span>• <?= date('d/m H:i', strtotime($comment['created_at'])) ?></span>
                  </div>

                  <p class="text-sm whitespace-pre-line leading-relaxed"><?= e($comment['body']) ?></p>

                  <!-- Image Attachment in comment -->
                  <?php if (!empty($comment['image_path'])): ?>
                    <div class="mt-2.5">
                      <a href="/uploads/<?= urlencode($comment['image_path']) ?>" target="_blank" class="block group relative">
                        <img src="/uploads/<?= urlencode($comment['image_path']) ?>" alt="Attachment"
                             class="rounded-xl border border-slate-200/60 max-h-56 object-cover shadow-sm group-hover:opacity-90 transition">
                        <span class="inline-block text-[11px] <?= $isCommentSelf ? 'text-blue-200' : 'text-slate-500' ?> mt-1 font-medium underline">
                          🔍 คลิกเพื่อดูรูปภาพขนาดเต็ม
                        </span>
                      </a>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Add Comment Form -->
        <form action="/tickets/<?= $ticket['id'] ?>/comments" method="POST" enctype="multipart/form-data" class="pt-4 border-t border-slate-100 space-y-3">
          <?= $csrfField ?>
          <div>
            <textarea name="body" rows="2" placeholder="พิมพ์ข้อความแสดงความคิดเห็นหรือแจ้งรายละเอียดเพิ่มเติม..."
                      class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
          </div>

          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-1.5 text-xs text-slate-600 hover:text-blue-600 cursor-pointer font-medium">
              <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
              <span>แนบรูปภาพ</span>
              <input type="file" name="image" accept="image/*" class="sr-only">
            </label>

            <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-semibold rounded-xl text-sm shadow transition">
              ส่งข้อความ
            </button>
          </div>
        </form>

      </div>

    </div>

    <!-- Right Column: Audit Trail Log (status_logs) -->
    <div class="space-y-6">
      
      <!-- Timeline Card -->
      <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
        <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
          <span>📜</span> ประวัติการเปลี่ยนสถานะ (Audit Trail)
        </h3>

        <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
          <?php foreach ($statusLogs as $log): ?>
            <?php
              $fromE = TicketStatus::tryFrom($log['from_status']);
              $toE = TicketStatus::tryFrom($log['to_status']);
            ?>
            <div class="relative">
              <div class="absolute -left-[19px] top-1 w-3.5 h-3.5 rounded-full bg-blue-600 ring-4 ring-white"></div>
              <div>
                <div class="flex items-center gap-1.5 flex-wrap">
                  <span class="text-xs font-bold text-slate-800"><?= $toE ? $toE->label() : e($log['to_status']) ?></span>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">
                  <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                </p>
                <p class="text-xs text-slate-600 mt-1">
                  โดย: <strong class="text-slate-800"><?= e($log['user_name']) ?></strong> (<?= e($log['user_role']) ?>)
                </p>
                <?php if (!empty($log['note'])): ?>
                  <p class="text-xs text-slate-500 mt-1 bg-slate-50 p-2 rounded-lg border border-slate-100">
                    <?= e($log['note']) ?>
                  </p>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

  </div>

</div>

<!-- ============================================================ -->
<!-- MODALS FOR STATE MACHINE TRANSITIONS -->
<!-- ============================================================ -->

<!-- Modal 1: Assign Technician (Open -> Assigned) -->
<div id="assignModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
    <div class="flex items-center justify-between border-b pb-3">
      <h3 class="font-bold text-slate-800 text-lg">มอบหมายงานให้ช่างเทคนิค</h3>
      <button onclick="document.getElementById('assignModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
    </div>
    <form action="/tickets/<?= $ticket['id'] ?>/assign" method="POST" class="space-y-4">
      <?= $csrfField ?>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">เลือกช่างเทคนิคผู้ดูแล</label>
        <select name="technician_id" required class="w-full px-3 py-2.5 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500 bg-white">
          <option value="">-- เลือกช่างเทคนิค --</option>
          <?php foreach ($technicians as $tech): ?>
            <option value="<?= $tech['id'] ?>" <?= ((int)($ticket['technician_id'] ?? 0) === (int)$tech['id']) ? 'selected' : '' ?>>
              <?= e($tech['name']) ?> (<?= e($tech['email']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('assignModal').classList.add('hidden')" class="px-4 py-2 border rounded-xl text-sm text-slate-600 hover:bg-slate-50">ยกเลิก</button>
        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow">ยืนยันมอบหมาย</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 2: Resolve Ticket with Photo (InProgress -> Resolved) -->
<div id="resolveModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4">
    <div class="flex items-center justify-between border-b pb-3">
      <h3 class="font-bold text-slate-800 text-lg">บันทึกผลการซ่อมเสร็จสิ้น</h3>
      <button onclick="document.getElementById('resolveModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
    </div>
    <form action="/tickets/<?= $ticket['id'] ?>/status" method="POST" enctype="multipart/form-data" class="space-y-4">
      <?= $csrfField ?>
      <input type="hidden" name="status" value="Resolved">

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
          แนบรูปภาพผลการซ่อม (Repair Result Photo) <span class="text-rose-500">* บังคับตามระเบียบ</span>
        </label>
        <input type="file" name="repair_photo" required accept="image/*"
               class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        <p class="text-[11px] text-slate-400 mt-1">รูปถ่ายอุปกรณ์หลังการซ่อมหรือทดสอบใช้งาน</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">รายละเอียดผลการซ่อม / วิธีแก้ปัญหา</label>
        <textarea name="comment" rows="3" required placeholder="เช่น ทำความสะอาดชิ้นส่วน, เปลี่ยนอะไหล่, ทดสอบเปิดเครื่องใช้งานได้ปกติแล้ว..."
                  class="w-full px-3.5 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500"></textarea>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('resolveModal').classList.add('hidden')" class="px-4 py-2 border rounded-xl text-sm text-slate-600 hover:bg-slate-50">ยกเลิก</button>
        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm shadow">บันทึกซ่อมเสร็จสิ้น</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 3: Confirm & Rate to Close (Resolved -> Closed) -->
<div id="rateModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4 text-center">
    <div class="flex items-center justify-between border-b pb-3 text-left">
      <h3 class="font-bold text-slate-800 text-lg">ประเมินความพึงพอใจ & ปิดงาน</h3>
      <button onclick="document.getElementById('rateModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
    </div>

    <form action="/tickets/<?= $ticket['id'] ?>/status" method="POST" class="space-y-4 text-left">
      <?= $csrfField ?>
      <input type="hidden" name="status" value="Closed">

      <div class="text-center py-2">
        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">ให้คะแนนการบริการ (1 - 5 ดาว)</label>
        <div class="flex justify-center gap-2 text-3xl cursor-pointer" id="starContainer">
          <input type="hidden" name="rating_score" id="ratingScoreInput" value="5">
          <span class="star text-amber-400" data-value="1">★</span>
          <span class="star text-amber-400" data-value="2">★</span>
          <span class="star text-amber-400" data-value="3">★</span>
          <span class="star text-amber-400" data-value="4">★</span>
          <span class="star text-amber-400" data-value="5">★</span>
        </div>
        <p class="text-xs text-amber-600 font-semibold mt-1" id="starLabel">5/5 - พึงพอใจมากที่สุด</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">ความคิดเห็นหรือข้อเสนอแนะเพิ่มเติม</label>
        <textarea name="rating_feedback" rows="3" placeholder="ระบุความเห็นต่อการบริการของช่างเทคนิค..."
                  class="w-full px-3.5 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500"></textarea>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('rateModal').classList.add('hidden')" class="px-4 py-2 border rounded-xl text-sm text-slate-600 hover:bg-slate-50">ยกเลิก</button>
        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl text-sm shadow">ยืนยันปิดงาน</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 4: Reject Resolution (Resolved -> InProgress) -->
<div id="rejectModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
    <div class="flex items-center justify-between border-b pb-3">
      <h3 class="font-bold text-rose-700 text-lg">แจ้งส่งซ่อมต่อ / ผลงานไม่ผ่าน</h3>
      <button onclick="document.getElementById('rejectModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
    </div>
    <form action="/tickets/<?= $ticket['id'] ?>/status" method="POST" class="space-y-4">
      <?= $csrfField ?>
      <input type="hidden" name="status" value="InProgress">

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
          ระบุเหตุผลที่ยังใช้งานไม่ได้ <span class="text-rose-500">*</span>
        </label>
        <textarea name="comment" rows="3" required placeholder="เช่น ทดลองเปิดเครื่องแล้วยังเปิดไม่ติดเหมือนเดิม หรือมีปัญหาใหม่เกิดขึ้น..."
                  class="w-full px-3.5 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-rose-500"></textarea>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="px-4 py-2 border rounded-xl text-sm text-slate-600 hover:bg-slate-50">ยกเลิก</button>
        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl text-sm shadow">ส่งเรื่องให้ช่างแก้ไขต่อ</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Interactive Star Rating Logic
  const stars = document.querySelectorAll('#starContainer .star');
  const ratingInput = document.getElementById('ratingScoreInput');
  const starLabel = document.getElementById('starLabel');

  const labels = {
    1: '1/5 - ปรับปรุง',
    2: '2/5 - พอใช้',
    3: '3/5 - ปานกลาง',
    4: '4/5 - ดีมาก',
    5: '5/5 - พึงพอใจมากที่สุด'
  };

  stars.forEach(star => {
    star.addEventListener('click', function() {
      const val = parseInt(this.getAttribute('data-value'));
      ratingInput.value = val;
      starLabel.textContent = labels[val] || '';

      stars.forEach(s => {
        const sVal = parseInt(s.getAttribute('data-value'));
        if (sVal <= val) {
          s.classList.add('text-amber-400');
          s.classList.remove('text-slate-300');
        } else {
          s.classList.remove('text-amber-400');
          s.classList.add('text-slate-300');
        }
      });
    });
  });
</script>
