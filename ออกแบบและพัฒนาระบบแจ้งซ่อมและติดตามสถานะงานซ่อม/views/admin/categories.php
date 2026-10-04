<div class="space-y-6">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
    <div>
      <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 mb-1">
        <a href="/admin/dashboard" class="hover:underline">แดชบอร์ด</a>
        <span>/</span>
        <span>หมวดหมู่อุปกรณ์</span>
      </div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">หมวดหมู่อุปกรณ์ที่รับแจ้งซ่อม</h1>
      <p class="text-sm text-slate-500 mt-1">จัดการประเภทอุปกรณ์ (คอมพิวเตอร์, เครือข่าย, เครื่องพิมพ์, ซอฟต์แวร์, โสตฯ)</p>
    </div>

    <button onclick="document.getElementById('createCategoryModal').classList.remove('hidden')"
            class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow transition flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
      เพิ่มหมวดหมู่ใหม่
    </button>
  </div>

  <!-- Categories Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($categories as $cat): ?>
      <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between">
            <span class="text-2xl">📁</span>
            <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
              <?= $cat['ticket_count'] ?? 0 ?> งานแจ้ง
            </span>
          </div>
          <h3 class="text-base font-bold text-slate-800 mt-2"><?= e($cat['name']) ?></h3>
          <p class="text-xs text-slate-500 mt-1 line-clamp-3 leading-relaxed"><?= e($cat['description'] ?: 'ไม่มีคำอธิบายเพิ่มเติม') ?></p>
        </div>

        <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs">
          <span class="text-slate-400 font-mono">ID: #<?= $cat['id'] ?></span>
          <?php if (($cat['ticket_count'] ?? 0) == 0): ?>
            <form action="/admin/categories/<?= $cat['id'] ?>/delete" method="POST" onsubmit="return confirm('ยืนยันลบหมวดหมู่นี้หรือไม่?')">
              <?= $csrfField ?>
              <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold">ลบหมวดหมู่</button>
            </form>
          <?php else: ?>
            <span class="text-slate-400 italic">มีงานใช้งานอยู่</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<!-- Modal Create Category -->
<div id="createCategoryModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
    <div class="flex items-center justify-between border-b pb-3">
      <h3 class="font-bold text-slate-800 text-lg">เพิ่มหมวดหมู่อุปกรณ์ใหม่</h3>
      <button onclick="document.getElementById('createCategoryModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">✕</button>
    </div>

    <form action="/admin/categories" method="POST" class="space-y-4">
      <?= $csrfField ?>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">ชื่อหมวดหมู่</label>
        <input type="text" name="name" required placeholder="เช่น อุปกรณ์รักษาความปลอดภัยและกล้องวงจรปิด"
               class="w-full px-3.5 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500">
      </div>

      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">คำอธิบาย</label>
        <textarea name="description" rows="3" placeholder="ระบุขอบเขตของอุปกรณ์ในหมวดหมู่นี้..."
                  class="w-full px-3.5 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-blue-500"></textarea>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="document.getElementById('createCategoryModal').classList.add('hidden')" class="px-4 py-2 border rounded-xl text-sm text-slate-600 hover:bg-slate-50">ยกเลิก</button>
        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm shadow">บันทึกหมวดหมู่</button>
      </div>
    </form>
  </div>
</div>
