<!DOCTYPE html>
<html lang="th" class="h-full bg-slate-900">
<head>
  <meta charset="UTF-8">
  <title>403 - Permission Denied</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center p-4 text-center text-white">
  <div class="max-w-md space-y-4">
    <div class="w-16 h-16 rounded-full bg-rose-500/20 text-rose-400 mx-auto flex items-center justify-center text-3xl font-bold">
      🚫
    </div>
    <h1 class="text-4xl font-extrabold text-rose-500">403</h1>
    <h2 class="text-xl font-bold">ไม่มีสิทธิ์ในการเข้าถึง (Forbidden)</h2>
    <p class="text-sm text-slate-400">
      บัญชีของคุณ (<?= e($role ?? 'user') ?>) ไม่มีสิทธิ์ในการเข้าถึงหน้าหรือทำรายการนี้ตามสิทธิ์ RBAC
    </p>
    <div class="pt-4">
      <a href="/tickets" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold inline-block">
        กลับสู่หน้ารายการแจ้งซ่อม
      </a>
    </div>
  </div>
</body>
</html>
