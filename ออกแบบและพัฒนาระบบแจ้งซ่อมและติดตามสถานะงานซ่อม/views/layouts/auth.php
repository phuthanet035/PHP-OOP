<!DOCTYPE html>
<html lang="th" class="h-full bg-slate-900">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'เข้าสู่ระบบ') ?> — <?= e($appName) ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Plus Jakarta Sans', 'Sarabun', sans-serif; }
  </style>
</head>
<body class="min-h-full flex items-center justify-center p-4 bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-slate-100">

  <div class="w-full max-w-md">
    <!-- Brand Header -->
    <div class="text-center mb-6">
      <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-xl shadow-blue-500/30 mb-3">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
      </div>
      <h1 class="text-2xl font-extrabold tracking-tight text-white">Smart IT Helpdesk</h1>
      <p class="text-sm text-slate-400 mt-1">ระบบแจ้งซ่อมและติดตามสถานะงานซ่อม</p>
    </div>

    <!-- Flash Alerts -->
    <?php if (!empty($flashSuccess)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm">
        <?= e($flashSuccess) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
      <div class="mb-4 p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm">
        <?= e($flashError) ?>
      </div>
    <?php endif; ?>

    <!-- Card Content -->
    <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/60 rounded-2xl shadow-2xl p-6 sm:p-8">
      <?= $content ?>
    </div>

    <!-- Quick Role Switcher on Auth page -->
    <div class="mt-6 text-center">
      <div class="text-xs text-slate-400 mb-2 font-medium">เข้าใช้งานทันทีด้วยบัญชีสาธิต:</div>
      <div class="flex justify-center gap-2 text-xs">
        <a href="/demo-login/admin" class="px-2.5 py-1.5 rounded-lg bg-purple-600/30 text-purple-300 border border-purple-500/30 hover:bg-purple-600 hover:text-white transition">
          👑 Admin IT
        </a>
        <a href="/demo-login/technician" class="px-2.5 py-1.5 rounded-lg bg-amber-600/30 text-amber-300 border border-amber-500/30 hover:bg-amber-600 hover:text-white transition">
          🔧 Technician
        </a>
        <a href="/demo-login/user" class="px-2.5 py-1.5 rounded-lg bg-emerald-600/30 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-600 hover:text-white transition">
          👤 General User
        </a>
      </div>
    </div>

    <div class="mt-8 text-center text-xs text-slate-500">
      Mini Project: Smart IT Helpdesk — Custom OOP PHP Edition
    </div>
  </div>

</body>
</html>
