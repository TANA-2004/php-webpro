<?php
require_once 'config.php';

$sql = "SELECT 
          p_id      AS id,
          p_title   AS title,
          p_detail  AS detail,
          TRIM(p_pic)   AS pic,                 -- ตัดช่องว่างหัวท้ายชื่อไฟล์
          CAST(p_price AS DECIMAL(10,2)) AS price
        FROM course";
$result  = mysqli_query($conn, $sql);
$courses = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>คอร์สเรียน | Bundai Su Fun</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
  <link rel="stylesheet" href="frontweb1.css">
</head>

<body class="bg-gray-50 text-gray-800">
  <?php $active = 'course';
  require_once 'navbar.php'; ?>
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

    <!-- เนื้อหา -->
    <div class="relative z-10 mx-auto max-w-4xl">
      <h1 class="text-3xl md:text-4xl font-extrabold">
        คอร์สติวมัธยม & เตรียมเข้ามหาวิทยาลัย
      </h1>
      <p class="mt-3 text-white/90 max-w-3xl mx-auto">
        เนื้อหาแน่น อัปคะแนนสอบเข้า พร้อมตะลุยข้อสอบจริง 9 วิชาสามัญ / TGAT-TPAT / PAT1 โดยติวเตอร์มากประสบการณ์
      </p>
      <div class="mt-6 flex flex-wrap justify-center gap-3">
        <a href="#catalog"
          class="bg-white text-sky-600 font-semibold rounded-full px-6 py-3 hover:bg-sky-200 transition">
          ดูคอร์สทั้งหมด
        </a>
        <a href="register.php"
          class="rounded-full px-6 py-3 bg-sky-700 text-white hover:bg-sky-600 font-semibold transition">
          สมัครเรียนทันที
        </a>
      </div>
    </div>
  </section>

  <!-- ========= รายการคอร์สจากฐานข้อมูล ========= -->
  <section id="catalog" class="mt-8 mb-16">
    <div class="mx-auto max-w-6xl px-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <?php if (!empty($courses)): ?>
        <?php foreach ($courses as $c):
          // กันชื่อไฟล์รูปที่มีช่องว่าง/ตัวอักษรพิเศษ
          $imgPath = 'Pictures/' . rawurlencode(trim($c['pic'] ?? ''));
          // แปลงราคาเป็นตัวเลขปลอดภัย
          $price   = isset($c['price']) ? (float)$c['price'] : 0;
        ?>
          <article class="bg-white rounded-2xl shadow hover:shadow-lg transition overflow-hidden">
            <img src="<?php echo $imgPath; ?>"
              alt="<?php echo htmlspecialchars($c['title'] ?? ''); ?>"
              class="h-40 w-full object-cover"
              onerror="this.src='Pictures/placeholder.jpg'">
            <div class="p-5 flex flex-col gap-3">
              <div>
                <h3 class="text-xl font-bold"><?php echo htmlspecialchars($c['title'] ?? ''); ?></h3>
                <p class="text-slate-600"><?php echo htmlspecialchars($c['detail'] ?? ''); ?></p>
              </div>
              <div class="flex items-center justify-between">
                <span class="font-semibold text-sky-700 text-lg">
                  ฿<?php echo number_format($price, 2); ?>
                </span>
                <?php
                // [IMPROVEMENT] ตรวจสอบสถานะล็อกอินเพื่อเปลี่ยนลิงก์ของปุ่ม
                if (isset($_SESSION['user_id'])) {
                  // 1. ล็อกอินแล้ว: ลิงก์ไป cart_add.php
                  $link = "cart_add.php?id=" . $c['id'];
                  $text = "เพิ่มลงตะกร้า";
                } else {
                  // 2. ยังไม่ล็อกอิน: ลิงก์ไป register.php
                  $link = "register.php";
                  $text = "สมัครเรียน";
                }
                ?>
                <a href="<?php echo $link; ?>"
                  class="px-4 py-2 rounded-full bg-sky-700 hover:bg-sky-600 text-white">
                  <?php echo $text; ?>
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center text-gray-600 col-span-3">ยังไม่มีคอร์สในระบบ</p>
      <?php endif; ?>
    </div>
  </section>
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
  <?php require_once 'footer.php'; ?>
</body>

</html>