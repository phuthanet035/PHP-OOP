<!DOCTYPE html>
<html lang="th" class="h-full bg-slate-900">
<head>
  <meta charset="UTF-8">
  <title>404 - Page Not Found</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center p-4 text-center text-white">
  <div class="max-w-md space-y-4">
    <div class="w-16 h-16 rounded-full bg-blue-500/20 text-blue-400 mx-auto flex items-center justify-center text-3xl font-bold">
      🔍
    </div>
    <h1 class="text-4xl font-extrabold text-blue-500">404</h1>
    <h2 class="text-xl font-bold">ไม่พบหน้าที่คุณเรียก (Page Not Found)</h2>
    <p class="text-sm text-slate-400">
      ที่อยู่ URL '<?= e($uri ?? '') ?>' ไม่มีอยู่ในระบบหรืออาจถูกย้ายไปแล้ว
    </p>
    <div class="pt-4">
      <a href="/tickets" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold inline-block">
        กลับสู่หน้ารายการแจ้งซ่อม
      </a>
    </div>
  </div>
</body>
</html>
