<div class="space-y-6">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
    <div>
      <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 mb-1">
        <a href="/admin/dashboard" class="hover:underline">แดชบอร์ด</a>
        <span>/</span>
        <span>LINE Notification Service</span>
      </div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">ประวัติการแจ้งเตือน LINE Messaging API</h1>
      <p class="text-sm text-slate-500 mt-1">
        ทำงานผ่าน <strong>Event Dispatcher & Observer Pattern</strong> ตาม Diagram 1 & Diagram 5
      </p>
    </div>

    <!-- Status badge -->
    <div class="flex items-center gap-2">
      <?php if ($hasToken): ?>
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span> LINE Token Active (Production Mode)
        </span>
      <?php else: ?>
        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1.5" title="กำลังบันทึกจำลองลง storage/logs/line_notifications.log">
          <span class="w-2 h-2 rounded-full bg-amber-500"></span> Simulation / Mock Log Mode
        </span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Send Test Notification Box -->
  <div class="bg-gradient-to-r from-emerald-900 to-teal-900 text-white p-6 rounded-2xl shadow-md">
    <div class="max-w-2xl">
      <h3 class="text-base font-bold flex items-center gap-2">
        <span>💬</span> ทดสอบยิงข้อความจำลองผ่าน Notification Channel
      </h3>
      <p class="text-xs text-emerald-200 mt-1">
        จำลองการ Dispatch Event เมื่อมีตั๋วใหม่หรือสถานะเปลี่ยน เพื่อดูผลลัพธ์ใน Log ทันที
      </p>

      <form action="/admin/line-logs/test" method="POST" class="mt-4 flex flex-col sm:flex-row gap-2">
        <?= $csrfField ?>
        <input type="text" name="recipient" placeholder="LINE User ID (เช่น U123... หรือเว้นว่างเพื่อทดสอบบรอดแคสต์)"
               class="flex-1 px-4 py-2 rounded-xl text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
        <button type="submit" class="px-5 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-bold rounded-xl text-sm transition shadow shrink-0">
          ทดสอบส่งแจ้งเตือน
        </button>
      </form>
    </div>
  </div>

  <!-- Recent Notifications Table -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
      <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
        <span>📋</span> รายการแจ้งเตือนล่าสุด (Recent Notifications Log)
      </h3>
      <span class="text-xs text-slate-400">อ่านจาก storage/logs/line_notifications.log</span>
    </div>

    <?php if (empty($logs)): ?>
      <div class="p-12 text-center text-slate-400 text-sm">
        ยังไม่มีประวัติการแจ้งเตือนในระบบ ลองสร้างใบแจ้งซ่อมใหม่หรือกดปุ่มทดสอบด้านบน
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-200">
            <tr>
              <th class="p-4">เวลา</th>
              <th class="p-4">ผู้รับ (Recipient)</th>
              <th class="p-4">สถานะ</th>
              <th class="p-4">ข้อความแจ้งเตือน (Payload Message)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($logs as $log): ?>
              <?php
                $statusColor = match($log['status'] ?? '') {
                  'SUCCESS' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                  'MOCK_SIMULATED' => 'bg-blue-100 text-blue-800 border-blue-200',
                  default => 'bg-rose-100 text-rose-800 border-rose-200',
                };
              ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="p-4 text-xs text-slate-400 whitespace-nowrap">
                  <?= e($log['timestamp'] ?? '-') ?>
                </td>
                <td class="p-4 text-xs font-mono text-slate-700">
                  <?= e($log['recipient'] ?? '-') ?>
                </td>
                <td class="p-4 whitespace-nowrap">
                  <span class="px-2 py-0.5 rounded text-[11px] font-bold border <?= $statusColor ?>">
                    <?= e($log['status'] ?? '-') ?>
                  </span>
                </td>
                <td class="p-4 text-xs text-slate-700 font-mono whitespace-pre-line leading-relaxed">
                  <?= e($log['message'] ?? '-') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
