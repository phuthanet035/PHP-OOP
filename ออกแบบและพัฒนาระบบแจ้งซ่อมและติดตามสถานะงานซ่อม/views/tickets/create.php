<?php
use App\Enums\TicketPriority;
?>

<div class="max-w-4xl mx-auto space-y-6">

  <!-- Header -->
  <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
    <div>
      <div class="flex items-center gap-2 text-xs font-semibold text-blue-600 mb-1">
        <a href="/tickets" class="hover:underline flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
          ย้อนกลับ
        </a>
        <span>/</span>
        <span>แบบฟอร์มแจ้งซ่อม</span>
      </div>
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">สร้างคำขอแจ้งซ่อมอุปกรณ์</h1>
      <p class="text-sm text-slate-500 mt-1">กรอกรายละเอียดปัญหาของอุปกรณ์เพื่อส่งเรื่องให้ฝ่ายช่างเทคนิคตรวจสอบ</p>
    </div>
  </div>

  <!-- Form Card -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
    <form action="/tickets" method="POST" enctype="multipart/form-data" class="space-y-6" id="repairForm">
      <?= $csrfField ?>

      <!-- 1. Category Selection -->
      <div>
        <label class="block text-sm font-bold text-slate-800 mb-3">
          1. เลือกหมวดหมู่อุปกรณ์ที่ต้องการแจ้งซ่อม <span class="text-rose-500">*</span>
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <?php foreach ($categories as $index => $cat): ?>
            <?php
              $icons = [
                1 => '💻', // Hardware
                2 => '🌐', // Network
                3 => '🖨️', // Printer
                4 => '💾', // Software
                5 => '📽️', // AV
              ];
              $icon = $icons[$cat['id']] ?? '🔧';
            ?>
            <label class="relative flex flex-col p-4 border-2 rounded-xl cursor-pointer hover:border-blue-500 transition duration-150 group has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50 has-[:checked]:shadow-sm">
              <input type="radio" name="category_id" value="<?= $cat['id'] ?>" class="sr-only" required <?= ($index === 0) ? 'checked' : '' ?>>
              <div class="flex items-center justify-between mb-2">
                <span class="text-2xl"><?= $icon ?></span>
                <span class="w-4 h-4 rounded-full border-2 border-slate-300 group-hover:border-blue-500 flex items-center justify-center transition">
                  <span class="w-2 h-2 rounded-full bg-blue-600 hidden group-has-[:checked]:block"></span>
                </span>
              </div>
              <span class="font-bold text-sm text-slate-800"><?= e($cat['name']) ?></span>
              <span class="text-xs text-slate-500 mt-1 line-clamp-2"><?= e($cat['description']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 2. Priority Selection -->
      <div>
        <label class="block text-sm font-bold text-slate-800 mb-2">
          2. ระดับความเร่งด่วนของงาน <span class="text-rose-500">*</span>
        </label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <?php foreach ($priorities as $priority): ?>
            <label class="flex items-center justify-center p-3 border-2 rounded-xl cursor-pointer hover:border-blue-500 text-sm font-semibold transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 has-[:checked]:text-blue-700">
              <input type="radio" name="priority" value="<?= $priority->value ?>" class="sr-only" <?= ($priority === TicketPriority::Medium) ? 'checked' : '' ?>>
              <span><?= $priority->label() ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 3. Title Input -->
      <div>
        <label for="title" class="block text-sm font-bold text-slate-800 mb-1.5">
          3. หัวข้อปัญหา / อาการเสียโดยย่อ <span class="text-rose-500">*</span>
        </label>
        <input type="text" id="title" name="title" required minlength="5" maxlength="200"
               placeholder="เช่น จอภาพเปิดไม่ติด มีไฟสีส้มกะพริบ (ห้อง 301)"
               class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <p class="text-xs text-slate-400 mt-1">ระบุอาการและสถานที่ให้ชัดเจนเพื่อให้ช่างเตรียมอุปกรณ์ได้ตรงจุด</p>
      </div>

      <!-- 4. Detailed Description -->
      <div>
        <label for="description" class="block text-sm font-bold text-slate-800 mb-1.5">
          4. รายละเอียดอาการเสียเพิ่มเติม <span class="text-rose-500">*</span>
        </label>
        <textarea id="description" name="description" rows="4" required minlength="10"
                  placeholder="อธิบายอาการอย่างละเอียด เช่น เกิดขึ้นเมื่อไหร่, ได้ลองทำอะไรไปแล้วบ้าง, รหัสครุภัณฑ์หรือเลขเครื่อง (ถ้ามี)..."
                  class="w-full px-4 py-2.5 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 leading-relaxed"></textarea>
      </div>

      <!-- 5. Attach Photo (with instant live preview) -->
      <div>
        <label class="block text-sm font-bold text-slate-800 mb-1.5">
          5. แนบรูปภาพประกอบอาการเสีย (ถ้ามี)
        </label>
        
        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-xl hover:border-blue-400 transition bg-slate-50/50" id="dropZone">
          <div class="space-y-2 text-center" id="uploadPrompt">
            <svg class="mx-auto h-10 w-10 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
              <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <div class="flex text-sm text-slate-600 justify-center">
              <label for="image" class="relative cursor-pointer bg-white rounded-md font-semibold text-blue-600 hover:text-blue-500 focus-within:outline-none px-2 py-0.5 border border-slate-200 shadow-sm">
                <span>เลือกไฟล์รูปภาพ</span>
                <input id="image" name="image" type="file" accept="image/png,image/jpeg,image/jpg,image/webp" class="sr-only">
              </label>
              <p class="pl-1 pt-0.5">หรือลากไฟล์มาวางที่นี่</p>
            </div>
            <p class="text-xs text-slate-400">รองรับไฟล์ PNG, JPG, JPEG, WEBP ขนาดไม่เกิน 5MB</p>
          </div>

          <!-- Live Image Preview Container -->
          <div id="previewContainer" class="hidden text-center">
            <div class="relative inline-block">
              <img id="imagePreview" src="#" alt="Preview" class="max-h-64 rounded-xl border border-slate-200 shadow-md object-cover">
              <button type="button" id="removeImageBtn" class="absolute -top-2 -right-2 p-1 bg-rose-600 text-white rounded-full hover:bg-rose-700 transition shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>
            </div>
            <p id="fileNameText" class="text-xs text-slate-500 mt-2 font-medium"></p>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-end gap-3">
        <a href="/tickets" class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-semibold text-sm text-center transition">
          ยกเลิก
        </a>
        <button type="submit" class="w-full sm:w-auto px-7 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-xl shadow-lg shadow-blue-500/25 transition transform active:scale-95 text-sm">
          ยืนยันส่งคำขอแจ้งซ่อม
        </button>
      </div>

    </form>
  </div>

</div>

<script>
  // Instant Live Preview script for file upload
  const fileInput = document.getElementById('image');
  const uploadPrompt = document.getElementById('uploadPrompt');
  const previewContainer = document.getElementById('previewContainer');
  const imagePreview = document.getElementById('imagePreview');
  const fileNameText = document.getElementById('fileNameText');
  const removeBtn = document.getElementById('removeImageBtn');

  fileInput.addEventListener('change', function(e) {
    const file = this.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(event) {
        imagePreview.src = event.target.result;
        fileNameText.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        uploadPrompt.classList.add('hidden');
        previewContainer.classList.remove('hidden');
      }
      reader.readAsDataURL(file);
    }
  });

  removeBtn.addEventListener('click', function() {
    fileInput.value = '';
    imagePreview.src = '#';
    fileNameText.textContent = '';
    previewContainer.classList.add('hidden');
    uploadPrompt.classList.remove('hidden');
  });
</script>
