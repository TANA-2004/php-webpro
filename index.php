<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bundai Su Fun | สถาบันติวเตอร์บันไดสู่ฝัน</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
  <link rel="stylesheet" href="frontweb1.css">
</head>

<body class="bg-gray-50 text-gray-800">
<?php $active = 'home'; require_once 'navbar.php'; ?>
  <!-- Hero: พื้นหลังเป็นสไลด์รูปภาพ auto -->
  <section id="hero" class="relative mt-12 isolate">
    <!-- ชั้นรูปภาพสองตัวสำหรับ crossfade -->
    <div id="heroBgA" class="absolute inset-0 -z-10 bg-center bg-cover opacity-100 transition-opacity duration-700">
    </div>
    <div id="heroBgB" class="absolute inset-0 -z-10 bg-center bg-cover opacity-0 transition-opacity duration-700"></div>
    <!-- ชั้นไล่สีทับเพื่อให้อ่านง่าย -->
    <div class="absolute inset-0 -z-10 bg-gradient-to-b from-black/40 via-black/20 to-black/30"></div>
    <div class="flex flex-col justify-center items-center text-center min-h-[80vh] px-4 pt-8 relative">
      <!-- เนื้อหาหลัก -->
      <div class="relative z-10 text-center text-white max-w-4xl mx-auto px-8 md:px-10 py-8 md:py-10 rounded-3xl">
        <div class="absolute inset-0 -z-10 bg-black/30 backdrop-blur-sm rounded-3xl"></div>
        <h1 class="text-4xl md:text-5xl font-bold mb-4 drop-shadow-lg">
          ยินดีต้อนรับสู่ Bundai Su Fun
        </h1>
        <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto text-white/90 drop-shadow">
          สถาบันติวเตอร์ออนไลน์ ที่ช่วยให้คุณ “ก้าวสู่ความฝัน” ด้วยคอร์สคุณภาพ และทีมผู้สอนมืออาชีพ
        </p>
        <a href="courses.php"
          class="bg-white text-indigo-800 font-semibold rounded-full px-8 py-3 shadow-md hover:bg-indigo-50 hover:shadow-lg transition-all duration-300">
          ดูคอร์สเรียน
        </a>
      </div>
    </div>

  </section>

  <script>
    // สคริปต์สไลด์พื้นหลัง
    const images = [
      'Pictures/math.png',
      'Pictures/Science.jpg',
      'Pictures/English.jpg',
      'Pictures/Physics.jpg',
      'Pictures/cehmi.jpg',
      'Pictures/Math + Eng + Sci.jpg',
    ];
    let currentIndex = 0;
    const bgA = document.getElementById('heroBgA');
    const bgB = document.getElementById('heroBgB');
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
<?php require_once 'footer.php'; ?>
</body>

</html>