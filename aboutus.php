<!DOCTYPE html>
<html lang="th">
    
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>เกี่ยวกับเรา | Bundai Su Fun</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
  <link rel="stylesheet" href="frontweb1.css">
</head>
<body class="bg-gray-50 text-gray-800">
<?php $active = 'about'; require_once 'navbar.php'; ?>
  <!-- ========= Hero ========= -->
  <section id="course-hero"
    class="mt-10 relative text-white text-center py-16 px-6 overflow-hidden rounded-3xl shadow-lg">
    <!-- พื้นหลัง (จะเปลี่ยนภาพด้วย JS) -->
    <div id="bg-sliderA"
      class="absolute inset-0 bg-cover bg-center transition-all duration-[1500ms] ease-in-out rounded-3xl opacity-100">
    </div>
    <div id="bg-sliderB"
      class="absolute inset-0 bg-cover bg-center transition-all duration-[1500ms] ease-in-out rounded-3xl opacity-0">
    </div>
    <div class="absolute inset-0 bg-black/40 rounded-3xl backdrop-blur-sm"></div>

  <!-- เนื้อหา Hero -->
   <div class="relative z-10 mx-auto max-w-4xl">
      <h1 class="text-3xl md:text-4xl font-extrabold">
        เกี่ยวกับเรา
      </h1>
      <p class="mt-3 text-white/90 max-w-3xl mx-auto">
        เราคือสถาบันติวออนไลน์ที่มุ่งมั่นพัฒนาคุณภาพการศึกษาให้กับนักเรียนไทยด้วย
        คอร์สติวเข้มเนื้อหาครบเข้าใจง่าย
      </p>
  </div>
</section>

  <!-- Content Section -->
  <section class="max-w-6xl mx-auto py-16 px-6 grid md:grid-cols-2 gap-10 items-center">
    <div>
      <img src="Pictures/logo.png" alt="ทีมติวเตอร์ Bundai Su Fun" class="rounded-2xl shadow-md" />
    </div>
    <div>
      <h2 class="text-3xl font-bold text-gray-800 mb-4">แรงบันดาลใจของเรา</h2>
      <p class="text-gray-600 mb-4 leading-relaxed">
        “Bundai Su Fun” ก่อตั้งขึ้นจากกลุ่มติวเตอร์รุ่นใหม่ที่เชื่อว่าการเรียนไม่จำเป็นต้องน่าเบื่อ  
        เราใช้เทคโนโลยีและสื่อการสอนสมัยใหม่เพื่อช่วยให้นักเรียนเข้าใจบทเรียนได้จริง  
        ทั้งในห้องเรียนและในชีวิตจริง
      </p>
      <p class="text-gray-600 leading-relaxed">
        เราเน้นให้ความรู้ควบคู่กับแรงบันดาลใจ เพื่อให้นักเรียนทุกคนมีเป้าหมาย  
        และสนุกกับการเรียนรู้ในทุกวัน — ไม่ใช่แค่เพื่อสอบ แต่เพื่อเติบโตอย่างมั่นใจ
      </p>
    </div>
  </section>

  <!-- Mission Section -->
  <section class="bg-white py-16">
    <div class="max-w-6xl mx-auto px-6 text-center">
      <h2 class="text-3xl font-bold mb-6">พันธกิจของเรา</h2>
      <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-blue-50 p-6 rounded-xl shadow-sm">
          <h3 class="font-semibold text-xl mb-3">📘 พัฒนาเนื้อหาคุณภาพ</h3>
          <p class="text-gray-600">เรารวบรวมทีมติวเตอร์ที่มีประสบการณ์จริงในสนามสอบ เพื่อออกแบบบทเรียนที่ครบ เข้าใจง่าย และอัปเดตเสมอ</p>
        </div>
        <div class="bg-blue-50 p-6 rounded-xl shadow-sm">
          <h3 class="font-semibold text-xl mb-3">💻 เรียนได้ทุกที่ทุกเวลา</h3>
          <p class="text-gray-600">แพลตฟอร์มออนไลน์ของเรารองรับทุกอุปกรณ์ ให้คุณเข้าถึงบทเรียนได้ทุกที่ — ไม่ต้องเดินทาง</p>
        </div>
        <div class="bg-blue-50 p-6 rounded-xl shadow-sm">
          <h3 class="font-semibold text-xl mb-3">🎯 มุ่งสู่เป้าหมาย</h3>
          <p class="text-gray-600">เราช่วยวางแผนการเรียนและแนวทางเตรียมสอบอย่างมีประสิทธิภาพ เพื่อให้คุณพร้อมที่สุดในการสอบจริง</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ========= Footer ========= -->
  <footer class="text-center py-8 bg-gray-100">
    <p class="text-gray-600">&copy; 2025 Bundai Su Fun Learning Center | ติดต่อ: info@bundaisufun.com</p>
  </footer>
  <!--กรองอัตโนมัติ-->
  <script>
    // ดึง element ของ select ทั้งสองอันและการ์ดทุกใบ
    const subjectSel = document.getElementById('filter-subject');
    const gradeSel = document.getElementById('filter-grade');
    const cards = document.querySelectorAll('.course-card');

    // ฟังก์ชันกรอง
    function filterCourses() {
      const s = subjectSel.value; // all / math / sci / eng / phy / chem
      const g = gradeSel.value;   // all / m1-3 / m4-6 / prep

      cards.forEach(card => {
        const okS = (s === 'all') || (card.dataset.subject === s);
        const okG = (g === 'all') || (card.dataset.grade === g);
        card.style.display = (okS && okG) ? '' : 'none';
      });
    }

    // เมื่อมีการเปลี่ยนค่าใน select ใด ๆ ให้เรียก filterCourses ทันที
    subjectSel.addEventListener('change', filterCourses);
    gradeSel.addEventListener('change', filterCourses);

    // เรียกตอนโหลดหน้าเว็บครั้งแรก
    filterCourses();
  </script>
  <!-- สคริปต์สไลด์พื้นหลัง -->
  <script>
    const images = [
      'Pictures/math.png',
      'Pictures/Science.jpg',
      'Pictures/English.jpg',
      'Pictures/Physics.jpg',
      'Pictures/cehmi.jpg',
      'Pictures/Math + Eng + Sci.jpg',
    ];
    let currentIndex = 0;
    const bgA = document.getElementById('bg-sliderA');
    const bgB = document.getElementById('bg-sliderB');
    let showingA = true;

    function changeBackground() {
      const nextIndex = (currentIndex + 1) % images.length;
      if (showingA) {
        bgB.style.backgroundImage = `url(${images[nextIndex]})`;
        bgB.classList.add('opacity-100');
        bgB.classList.remove('opacity-0');
        bgA.classList.add('opacity-0');
        bgA.classList.remove('opacity-100');
      } else {
        bgA.style.backgroundImage = `url(${images[nextIndex]})`;
        bgA.classList.add('opacity-100');
        bgA.classList.remove('opacity-0');
        bgB.classList.add('opacity-0');
        bgB.classList.remove('opacity-100');
      }
      showingA = !showingA;
      currentIndex = nextIndex;
    }

    // เริ่มต้นด้วยภาพแรก
    bgA.style.backgroundImage = `url(${images[0]})`;

    // เปลี่ยนภาพทุก 5 วินาที
    setInterval(changeBackground, 5000);
  </script>
</body>

</html>
