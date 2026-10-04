<div class="space-y-6">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
    <div>
      <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 mb-1">
        <span>ฝ่ายบริหารและควบคุมระบบ</span>
        <span>/</span>
        <span>ภาพรวมเชิงสถิติ</span>
      </div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">แดชบอร์ดสถิติ & ประสิทธิภาพการซ่อม</h1>
      <p class="text-sm text-slate-500 mt-1">สรุปข้อมูลเชิงวิเคราะห์ ตามสถาปัตยกรรม Diagram 6.3 (Admin Dashboard Sequence)</p>
    </div>

    <div class="flex items-center gap-2">
      <a href="/tickets" class="px-4 py-2 border border-slate-300 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
        ดูตั๋วงานทั้งหมด
      </a>
      <a href="/admin/users" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-semibold shadow transition">
        จัดการผู้ใช้
      </a>
    </div>
  </div>

  <!-- KPI Metric Cards Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    <!-- Total Tickets -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">งานแจ้งซ่อมทั้งหมด</p>
        <p class="text-3xl font-extrabold text-slate-900 mt-1"><?= $stats['totalTickets'] ?></p>
        <p class="text-xs text-slate-500 mt-1">
          รอดำเนินการ: <span class="font-bold text-amber-600"><?= $stats['openTickets'] ?></span> งาน
        </p>
      </div>
      <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl font-bold">
        📋
      </div>
    </div>

    <!-- In Progress -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">กำลังดำเนินการซ่อม</p>
        <p class="text-3xl font-extrabold text-indigo-600 mt-1"><?= $stats['inProgressTickets'] ?></p>
        <p class="text-xs text-slate-500 mt-1">
          ช่างกำลังเข้าตรวจซ่อม
        </p>
      </div>
      <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl font-bold">
        ⚙️
      </div>
    </div>

    <!-- Avg Resolution SLA Time -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">เวลาเฉลี่ยในการซ่อม (SLA)</p>
        <p class="text-3xl font-extrabold text-emerald-600 mt-1"><?= $stats['avgResolutionHours'] ?> <span class="text-base font-semibold text-slate-500">ชม.</span></p>
        <p class="text-xs text-slate-500 mt-1">
          คำนวณจากงานที่ซ่อมเสร็จ
        </p>
      </div>
      <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl font-bold">
        ⏱️
      </div>
    </div>

    <!-- Customer Satisfaction -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
      <div>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">คะแนนความพึงพอใจ</p>
        <p class="text-3xl font-extrabold text-amber-500 mt-1"><?= $stats['avgRating'] ?: '5.0' ?> <span class="text-base font-semibold text-slate-500">/ 5</span></p>
        <p class="text-xs text-slate-500 mt-1">
          จากทั้งหมด <?= $stats['totalRatings'] ?> การประเมิน
        </p>
      </div>
      <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl font-bold">
        ⭐
      </div>
    </div>

  </div>

  <!-- Charts Row: Donut Chart & Top Techs -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Status Distribution Donut Chart -->
    <div class="lg:col-span-5 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
      <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
        <span>📊</span> สัดส่วนสถานะงานซ่อม (Status Distribution)
      </h3>
      <div class="relative max-h-64 flex justify-center">
        <canvas id="statusChart"></canvas>
      </div>
      <div class="grid grid-cols-2 gap-2 mt-4 text-xs text-slate-600 pt-3 border-t border-slate-100">
        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-400"></span> Open: <?= $stats['statusCounts']['Open'] ?? 0 ?></div>
        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Assigned: <?= $stats['statusCounts']['Assigned'] ?? 0 ?></div>
        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-indigo-600"></span> InProgress: <?= $stats['statusCounts']['InProgress'] ?? 0 ?></div>
        <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Resolved/Closed: <?= ($stats['statusCounts']['Resolved'] ?? 0) + ($stats['statusCounts']['Closed'] ?? 0) ?></div>
      </div>
    </div>

    <!-- Top Technicians Leaderboard -->
    <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
      <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center justify-between">
        <span class="flex items-center gap-2">
          <span>🏆</span> อันดับช่างเทคนิคดีเด่น (Top Technicians)
        </span>
        <span class="text-xs font-normal text-slate-500">เรียงตามจำนวนงานที่ปิดสำเร็จ</span>
      </h3>

      <?php if (empty($stats['topTechnicians'])): ?>
        <p class="text-sm text-slate-400 text-center py-8">ยังไม่มีข้อมูลผลงานช่างที่ปิดงาน</p>
      <?php else: ?>
        <div class="space-y-3">
          <?php foreach ($stats['topTechnicians'] as $idx => $tech): ?>
            <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100/80 transition">
              <div class="flex items-center gap-3">
                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                             <?= ($idx === 0) ? 'bg-amber-400 text-slate-900' : (($idx === 1) ? 'bg-slate-300 text-slate-800' : 'bg-slate-200 text-slate-600') ?>">
                  <?= $idx + 1 ?>
                </span>
                <div>
                  <h4 class="text-sm font-bold text-slate-800"><?= e($tech['name']) ?></h4>
                  <p class="text-xs text-slate-400"><?= e($tech['email']) ?></p>
                </div>
              </div>

              <div class="text-right">
                <div class="text-sm font-bold text-blue-700"><?= $tech['total_resolved'] ?> งานสำเร็จ</div>
                <div class="text-xs text-amber-500 font-semibold">
                  ★ <?= round((float)($tech['avg_rating'] ?? 5.0), 1) ?> ดาว
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>

  <!-- Recent System Audit Logs Table -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
      <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
        <span>⚡</span> บันทึกกิจกรรมระบบล่าสุด (Recent System Activity Logs)
      </h3>
      <span class="text-xs text-slate-400">อัปเดตแบบเรียลไทม์จาก status_logs</span>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-200">
          <tr>
            <th class="p-4">เวลา</th>
            <th class="p-4">ใบแจ้งซ่อม</th>
            <th class="p-4">การเปลี่ยนสถานะ</th>
            <th class="p-4">ผู้ดำเนินการ</th>
            <th class="p-4">บันทึกประกอบ</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($stats['recentActivity'] as $act): ?>
            <tr class="hover:bg-slate-50/80 transition">
              <td class="p-4 text-xs text-slate-400 whitespace-nowrap">
                <?= date('d/m/Y H:i:s', strtotime($act['created_at'])) ?>
              </td>
              <td class="p-4">
                <a href="/tickets/<?= $act['ticket_id'] ?>" class="font-bold text-blue-600 hover:underline">
                  #TK-<?= str_pad((string)$act['ticket_id'], 4, '0', STR_PAD_LEFT) ?>
                </a>
                <span class="text-xs text-slate-500 block line-clamp-1"><?= e($act['ticket_title']) ?></span>
              </td>
              <td class="p-4 whitespace-nowrap">
                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-700">
                  <?= e($act['from_status']) ?>
                </span>
                <span class="text-slate-400 mx-1">➔</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-blue-100 text-blue-800">
                  <?= e($act['to_status']) ?>
                </span>
              </td>
              <td class="p-4 whitespace-nowrap">
                <span class="font-semibold text-slate-800"><?= e($act['user_name']) ?></span>
                <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded ml-1"><?= e($act['user_role']) ?></span>
              </td>
              <td class="p-4 text-xs text-slate-600">
                <?= e($act['note'] ?: '-') ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
  // Chart.js Status Donut Chart
  const ctx = document.getElementById('statusChart').getContext('2d');
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['รอดำเนินการ (Open)', 'มอบหมายช่าง (Assigned)', 'กำลังซ่อม (InProgress)', 'ซ่อมเสร็จ/ปิดงาน'],
      datasets: [{
        data: [
          <?= $stats['statusCounts']['Open'] ?? 0 ?>,
          <?= $stats['statusCounts']['Assigned'] ?? 0 ?>,
          <?= $stats['statusCounts']['InProgress'] ?? 0 ?>,
          <?= ($stats['statusCounts']['Resolved'] ?? 0) + ($stats['statusCounts']['Closed'] ?? 0) ?>
        ],
        backgroundColor: ['#fbbf24', '#3b82f6', '#4f46e5', '#10b981'],
        borderWidth: 2,
        borderColor: '#ffffff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      cutout: '70%'
    }
  });
</script>
