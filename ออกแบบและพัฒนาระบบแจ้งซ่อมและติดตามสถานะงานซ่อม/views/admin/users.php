<div class="space-y-6">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
    <div>
      <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 mb-1">
        <a href="/admin/dashboard" class="hover:underline">แดชบอร์ด</a>
        <span>/</span>
        <span>จัดการผู้ใช้งาน</span>
      </div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">การจัดการผู้ใช้งานในระบบ (RBAC)</h1>
      <p class="text-sm text-slate-500 mt-1">กำหนดสิทธิ์และบทบาท (Admin, Technician, General User)</p>
    </div>

    <button onclick="document.getElementById('createUserModal').classList.remove('hidden')"
            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow transition flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
      เพิ่มผู้ใช้งานใหม่
    </button>
  </div>

  <!-- Role Filters -->
  <div class="flex gap-2">
    <a href="/admin/users" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold border <?= empty($currentRole) ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' ?>">
      ทั้งหมด (<?= array_sum($roleCounts) ?>)
    </a>
    <a href="/admin/users?role=admin" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold border <?= ($currentRole === 'admin') ? 'bg-purple-600 text-white border-purple-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' ?>">
      👑 Admin (<?= $roleCounts['admin'] ?? 0 ?>)
    </a>
    <a href="/admin/users?role=technician" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold border <?= ($currentRole === 'technician') ? 'bg-amber-600 text-white border-amber-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' ?>">
      🔧 Technician (<?= $roleCounts['technician'] ?? 0 ?>)
    </a>
    <a href="/admin/users?role=user" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold border <?= ($currentRole === 'user') ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' ?>">
      👤 User (<?= $roleCounts['user'] ?? 0 ?>)
    </a>
  </div>

  <!-- Users Table -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-200">
          <tr>
            <th class="p-4">#</th>
            <th class="p-4">ชื่อ - นามสกุล</th>
            <th class="p-4">อีเมล</th>
            <th class="p-4">บทบาท (Role)</th>
            <th class="p-4">LINE User ID</th>
            <th class="p-4">วันที่ลงทะเบียน</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($users as $u): ?>
            <?php
              $badgeClass = match($u['role']) {
                'admin' => 'bg-purple-100 text-purple-700 border-purple-200',
                'technician' => 'bg-amber-100 text-amber-700 border-amber-200',
                default => 'bg-emerald-100 text-emerald-700 border-emerald-200',
              };
            ?>
            <tr class="hover:bg-slate-50 transition">
              <td class="p-4 text-xs text-slate-400 font-mono"><?= $u['id'] ?></td>
              <td class="p-4 font-bold text-slate-800"><?= e($u['name']) ?></td>
              <td class="p-4 text-slate-600"><?= e($u['email']) ?></td>
              <td class="p-4">
                <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold border <?= $badgeClass ?>">
                  <?= ucfirst($u['role']) ?>
                </span>
              </td>
              <td class="p-4 text-xs font-mono text-slate-500">
                <?= e($u['line_user_id'] ?: '-') ?>
              </td>
              <td class="p-4 text-xs text-slate-400">
                <?= date('d/m/Y H:i', strtotime($u['created_at'])) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Create User Modal -->
<div id="createUserModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
    <div class="flex items-center justify-between border-b pb-3">
      <h3 class="font-bold text-slate-800 text-lg">เพิ่มผู้ใช้งานใหม่</h3>
      <button onclick="document.getElementById('createUserModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
    </div>

    <form action="/admin/users" method="POST" class="space-y-4">
      <?= $csrfField ?>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">ชื่อ-นามสกุล</label>
        <input type="text" name="name" required placeholder="นายสมศักดิ์ ช่างซ่อม"
               class="w-full px-3.5 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">อีเมล</label>
        <input type="email" name="email" required placeholder="tech2@helpdesk.com"
               class="w-full px-3.5 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">บทบาท (Role)</label>
        <select name="role" required class="w-full px-3.5 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500 bg-white">
          <option value="technician">ช่างเทคนิค (Technician)</option>
          <option value="admin">ผู้ดูแลระบบ (Admin)</option>
          <option value="user">ผู้แจ้งซ่อมทั่วไป (General User)</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">รหัสผ่านเริ่มต้น</label>
        <input type="password" name="password" required minlength="6" value="password123"
               class="w-full px-3.5 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">LINE User ID (ถ้ามี)</label>
        <input type="text" name="line_user_id" placeholder="U12345678..."
               class="w-full px-3.5 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('createUserModal').classList.add('hidden')" class="px-4 py-2 border rounded-xl text-sm text-slate-600 hover:bg-slate-50">ยกเลิก</button>
        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow">บันทึกข้อมูล</button>
      </div>
    </form>
  </div>
</div>
