<h2 class="text-xl font-bold text-white mb-6 text-center">เข้าสู่ระบบ (Sign In)</h2>

<form action="/login" method="POST" class="space-y-4">
  <?= $csrfField ?>
  <input type="hidden" name="redirect" value="<?= e($redirect ?? '/tickets') ?>">

  <div>
    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">อีเมล (Email)</label>
    <div class="relative">
      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
      </div>
      <input type="email" id="email" name="email" value="<?= e($email ?? 'admin@helpdesk.com') ?>" required
             class="w-full pl-10 pr-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
    </div>
  </div>

  <div>
    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">รหัสผ่าน (Password)</label>
    <div class="relative">
      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
      </div>
      <input type="password" id="password" name="password" value="password123" required
             class="w-full pl-10 pr-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm">
    </div>
  </div>

  <button type="submit" class="w-full mt-2 py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/25 transition duration-150 transform active:scale-[0.99] text-sm">
    เข้าสู่ระบบ
  </button>
</form>

<div class="mt-6 pt-5 border-t border-slate-700/60 text-center text-sm text-slate-400">
  ยังไม่มีบัญชีผู้ใช้? 
  <a href="/register" class="text-blue-400 hover:text-blue-300 font-semibold ml-1">สมัครสมาชิกใหม่</a>
</div>
