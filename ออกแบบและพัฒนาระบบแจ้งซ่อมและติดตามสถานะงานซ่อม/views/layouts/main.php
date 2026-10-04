<!DOCTYPE html>
<html lang="th" class="h-full bg-slate-50">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'ระบบแจ้งซ่อมและติดตามสถานะงานซ่อม') ?> — <?= e($appName) ?></title>
  
  <!-- Google Fonts: Plus Jakarta Sans & Sarabun -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Chart.js for Admin Analytics -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', '"Sarabun"', 'sans-serif'],
          },
          colors: {
            brand: {
              50: '#eff6ff',
              100: '#dbeafe',
              200: '#bfdbfe',
              500: '#3b82f6',
              600: '#2563eb',
              700: '#1d4ed8',
              800: '#1e40af',
              900: '#1e3a8a',
            }
          }
        }
      }
    }
  </script>

  <style>
    body {
      font-family: 'Plus Jakarta Sans', 'Sarabun', sans-serif;
    }
    .glassmorphism {
      background: rgba(255, 255, 255, 0.85);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }
    .custom-scrollbar::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background-color: #cbd5e1;
      border-radius: 9999px;
    }
  </style>
</head>
<body class="flex flex-col min-h-screen text-slate-800 bg-slate-50 antialiased">

  <!-- Top Announcement Bar / Quick Demo Switcher -->
  <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white text-xs py-2 px-4 shadow-sm">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
          ● PHP 8.2+ Custom OOP MVC
        </span>
        <span class="hidden sm:inline text-slate-300">Mini Project: Smart IT Helpdesk & Notification System</span>
      </div>

      <!-- Quick Demo Switcher -->
      <div class="flex items-center gap-2">
        <span class="text-slate-400 font-medium">สลับบทบาททดสอบ:</span>
        <a href="/demo-login/admin" class="px-2 py-1 rounded bg-purple-600/80 hover:bg-purple-600 text-white font-medium transition shadow-sm" title="เข้าสู่ระบบในฐานะ Admin">
          👑 Admin IT
        </a>
        <a href="/demo-login/technician" class="px-2 py-1 rounded bg-amber-600/80 hover:bg-amber-600 text-white font-medium transition shadow-sm" title="เข้าสู่ระบบในฐานะ ช่างเทคนิค">
          🔧 Technician
        </a>
        <a href="/demo-login/user" class="px-2 py-1 rounded bg-emerald-600/80 hover:bg-emerald-600 text-white font-medium transition shadow-sm" title="เข้าสู่ระบบในฐานะ ผู้ใช้งานทั่วไป">
          👤 User
        </a>
      </div>
    </div>
  </div>

  <!-- Primary Navigation Bar -->
  <header class="sticky top-0 z-40 glassmorphism border-b border-slate-200/80 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        
        <!-- Logo & Brand -->
        <div class="flex items-center gap-3">
          <a href="/tickets" class="flex items-center gap-3 group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-700 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </div>
            <div>
              <span class="text-lg font-bold bg-gradient-to-r from-blue-700 to-indigo-800 bg-clip-text text-transparent">Smart IT Helpdesk</span>
              <span class="block text-[11px] text-slate-500 font-medium leading-none">ระบบแจ้งซ่อมและติดตามสถานะงาน</span>
            </div>
          </a>
        </div>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center space-x-1 text-sm font-medium">
          <a href="/tickets" class="px-3 py-2 rounded-lg text-slate-700 hover:text-blue-700 hover:bg-blue-50 transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            รายการแจ้งซ่อม
          </a>

          <a href="/tickets/create" class="px-3 py-2 rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 font-semibold transition flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            สร้างคำขอแจ้งซ่อม
          </a>

          <?php if (!empty($currentUser) && in_array($currentUser['role'], ['admin', 'technician'])): ?>
            <a href="/admin/dashboard" class="px-3 py-2 rounded-lg text-slate-700 hover:text-blue-700 hover:bg-blue-50 transition flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              แดชบอร์ดสถิติ
            </a>
          <?php endif; ?>

          <?php if (!empty($currentUser) && $currentUser['role'] === 'admin'): ?>
            <a href="/admin/users" class="px-3 py-2 rounded-lg text-slate-700 hover:text-blue-700 hover:bg-blue-50 transition flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
              จัดการผู้ใช้
            </a>
            <a href="/admin/categories" class="px-3 py-2 rounded-lg text-slate-700 hover:text-blue-700 hover:bg-blue-50 transition flex items-center gap-1.5">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
              หมวดหมู่งาน
            </a>
            <a href="/admin/line-logs" class="px-3 py-2 rounded-lg text-slate-700 hover:text-blue-700 hover:bg-blue-50 transition flex items-center gap-1.5">
              <span class="text-xs">💬</span>
              LINE Logs
            </a>
          <?php endif; ?>
        </nav>

        <!-- User Profile Dropdown / Auth Menu -->
        <div class="flex items-center gap-3">
          <?php if (!empty($currentUser)): ?>
            <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
              <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-slate-700 to-slate-900 flex items-center justify-center text-white text-xs font-bold ring-2 ring-blue-500/20 shadow">
                <?= strtoupper(mb_substr($currentUser['name'], 0, 1)) ?>
              </div>
              <div class="hidden sm:block text-left">
                <div class="text-sm font-semibold text-slate-800 leading-tight flex items-center gap-1.5">
                  <?= e($currentUser['name']) ?>
                  <?php if ($currentUser['role'] === 'admin'): ?>
                    <span class="text-[10px] bg-purple-100 text-purple-700 border border-purple-200 px-1.5 py-0.2 rounded-md font-semibold">Admin</span>
                  <?php elseif ($currentUser['role'] === 'technician'): ?>
                    <span class="text-[10px] bg-amber-100 text-amber-700 border border-amber-200 px-1.5 py-0.2 rounded-md font-semibold">Tech</span>
                  <?php else: ?>
                    <span class="text-[10px] bg-emerald-100 text-emerald-700 border border-emerald-200 px-1.5 py-0.2 rounded-md font-semibold">User</span>
                  <?php endif; ?>
                </div>
                <div class="text-xs text-slate-500"><?= e($currentUser['email']) ?></div>
              </div>

              <a href="/logout" class="ml-2 p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="ออกจากระบบ">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
              </a>
            </div>
          <?php else: ?>
            <a href="/login" class="text-sm font-semibold text-slate-700 hover:text-blue-600 px-3 py-2">เข้าสู่ระบบ</a>
            <a href="/register" class="text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg shadow-sm transition">สมัครสมาชิก</a>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </header>

  <!-- Flash Alerts Notification System -->
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
    <?php if (!empty($flashSuccess)): ?>
      <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start gap-3 shadow-sm mb-4 animate-fade-in" role="alert">
        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="flex-1 font-medium text-sm"><?= e($flashSuccess) ?></div>
      </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
      <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-sm mb-4 animate-fade-in" role="alert">
        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="flex-1 font-medium text-sm"><?= e($flashError) ?></div>
      </div>
    <?php endif; ?>

    <?php if (!empty($flashWarning)): ?>
      <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 flex items-start gap-3 shadow-sm mb-4 animate-fade-in" role="alert">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div class="flex-1 font-medium text-sm"><?= e($flashWarning) ?></div>
      </div>
    <?php endif; ?>

    <?php if (!empty($flashInfo)): ?>
      <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 flex items-start gap-3 shadow-sm mb-4 animate-fade-in" role="alert">
        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div class="flex-1 font-medium text-sm"><?= e($flashInfo) ?></div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Main View Content -->
  <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full">
    <?= $content ?>
  </main>

  <!-- Modern Footer -->
  <footer class="mt-auto bg-white border-t border-slate-200/80 py-6 text-sm text-slate-500">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-6 h-6 rounded-lg bg-blue-600 flex items-center justify-center text-white text-xs font-bold">IT</div>
        <span><strong>Smart IT Helpdesk</strong> — Custom OOP PHP Edition (No Framework)</span>
      </div>
      <div class="text-xs text-slate-400 text-center md:text-right">
        รายวิชา: การออกแบบและพัฒนาเว็บขั้นสูง — สาขาวิชาเทคโนโลยีสารสนเทศ
      </div>
    </div>
  </footer>

</body>
</html>
