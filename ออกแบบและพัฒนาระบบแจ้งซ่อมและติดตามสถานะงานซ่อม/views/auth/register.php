<h2 class="text-xl font-bold text-white mb-6 text-center">สมัครสมาชิก (Register)</h2>

<form action="/register" method="POST" class="space-y-4">
  <?= $csrfField ?>

  <div>
    <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">ชื่อ-นามสกุล (Full Name)</label>
    <input type="text" id="name" name="name" value="<?= e($name ?? '') ?>" required placeholder="เช่น นายสมชาย ใจดี"
           class="w-full px-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
  </div>

  <div>
    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">อีเมล (Email)</label>
    <input type="email" id="email" name="email" value="<?= e($email ?? '') ?>" required placeholder="user@company.com"
           class="w-full px-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
  </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
      <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">รหัสผ่าน</label>
      <input type="password" id="password" name="password" required minlength="6" placeholder="อย่างน้อย 6 ตัวอักษร"
             class="w-full px-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
    </div>
    <div>
      <label for="password_confirmation" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">ยืนยันรหัสผ่าน</label>
      <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6" placeholder="พิมพ์ซ้ำอีกครั้ง"
             class="w-full px-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
    </div>
  </div>

  <div>
    <label for="line_user_id" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
      LINE User ID (สำหรับรับการแจ้งเตือน - ไม่บังคับ)
    </label>
    <input type="text" id="line_user_id" name="line_user_id" value="<?= e($line_user_id ?? '') ?>" placeholder="เช่น U1234567890abcdef"
           class="w-full px-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
    <p class="text-[11px] text-slate-400 mt-1">สามารถใส่หรือไม่ใส่ก็ได้ หากใส่ระบบจะส่งการแจ้งเตือนงานซ่อมผ่าน LINE</p>
  </div>

  <button type="submit" class="w-full mt-2 py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/25 transition duration-150 transform active:scale-[0.99] text-sm">
    ยืนยันการสมัครสมาชิก
  </button>
</form>

<div class="mt-6 pt-5 border-t border-slate-700/60 text-center text-sm text-slate-400">
  มีบัญชีผู้ใช้อยู่แล้ว? 
  <a href="/login" class="text-blue-400 hover:text-blue-300 font-semibold ml-1">เข้าสู่ระบบ</a>
</div>
