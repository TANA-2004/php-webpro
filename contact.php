<?php
// (config.php ต้องมี session_start() และ $conn)
require_once 'config.php';

/**
 * ฟังก์ชันสำหรับจัดการการส่งฟอร์มติดต่อ
 */
function handleContactSubmission($conn)
{
  if ($_SERVER["REQUEST_METHOD"] != "POST") {
    return; // ไม่ใช่การส่งฟอร์ม
  }

  // 1. ตรวจสอบ CSRF Token
  if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $_SESSION['message'] = "การส่งข้อมูลไม่ถูกต้อง (Invalid Token)";
    $_SESSION['is_success'] = false;
    header("Location: contact.php");
    exit();
  }

  // 2. รับค่าและทำความสะอาด (ใช้ "name" ที่เราจะเพิ่มในฟอร์ม)
  $full_name = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $message = trim($_POST['message'] ?? '');

  // 3. ตรวจสอบข้อมูล
  if (empty($full_name) || empty($email) || empty($message)) {
    $_SESSION['message'] = "กรุณากรอกข้อมูลให้ครบถ้วนทุกช่อง";
    $_SESSION['is_success'] = false;
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = "รูปแบบอีเมลไม่ถูกต้อง";
    $_SESSION['is_success'] = false;
  } else {
    // 4. บันทึกลงฐานข้อมูล (ตาราง contacts)
    $sql = "INSERT INTO contacts (full_name, email, message) VALUES (?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $full_name, $email, $message);

    if (mysqli_stmt_execute($stmt)) {
      $_SESSION['message'] = "ส่งข้อความเรียบร้อยแล้ว! ทีมงานจะติดต่อกลับโดยเร็วที่สุด ✨";
      $_SESSION['is_success'] = true;
    } else {
      $_SESSION['message'] = "เกิดข้อผิดพลาดในการส่งข้อความ กรุณาลองใหม่อีกครั้ง";
      $_SESSION['is_success'] = false;
    }
    mysqli_stmt_close($stmt);
  }

  // 5. Post-Redirect-Get (PRG) Pattern
  header("Location: contact.php");
  exit();
}

// --- ส่วนประมวลผลหลัก ---

// 1. เรียกใช้ฟังก์ชันจัดการฟอร์ม
handleContactSubmission($conn);

// 2. สร้าง CSRF Token
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. จัดการการแจ้งเตือน (Alerts)
$alert_message = null;
$is_success = null;
if (isset($_SESSION['message'])) {
  $alert_message = $_SESSION['message'];
  $is_success = $_SESSION['is_success'];
  unset($_SESSION['message']);
  unset($_SESSION['is_success']);
}

// 4. ปิดการเชื่อมต่อ
mysqli_close($conn);

?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ติดต่อเรา | Bundai Su Fun</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
  <link rel="stylesheet" href="frontweb1.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body class="bg-gray-50 text-gray-800">
  <?php $active = 'contact';
  require_once 'navbar.php'; ?>
  <section id="course-hero"
    class="mt-10 relative text-white text-center py-16 px-6 overflow-hidden rounded-3xl shadow-lg">
    <div id="bg-sliderA"
      class="absolute inset-0 bg-cover bg-center transition-all duration-[1500ms] ease-in-out rounded-3xl opacity-100">
    </div>
    <div id="bg-sliderB"
      class="absolute inset-0 bg-cover bg-center transition-all duration-[1500ms] ease-in-out rounded-3xl opacity-0">
    </div>
    <div class="absolute inset-0 bg-black/40 rounded-3xl backdrop-blur-sm"></div>

    <div class="relative z-10 mx-auto max-w-4xl">
      <h1 class="text-3xl md:text-4xl font-extrabold">
        ติดต่อเรา
      </h1>
      <p class="mt-3 text-white/90 max-w-3xl mx-auto">
        มีคำถามหรืออยากปรึกษาเรื่องคอร์สเรียน?
        <br>
        ทีมงาน Bundai Su Fun ยินดีให้คำแนะนำทุกขั้นตอน
      </p>
    </div>
  </section>

  <section class="max-w-6xl mx-auto py-16 px-6 grid md:grid-cols-2 gap-10 items-start">
    <div>
      <h2 class="text-3xl font-bold text-gray-800 mb-6">ติดต่อเราได้ที่</h2>
      <p class="text-gray-600 mb-4">
        📍 <strong>สำนักงานใหญ่:</strong> 
        99/99 ถนนการศึกษา อำเภอเมือง จังหวัดสงขลา 90110
      </p>
      <p class="text-gray-600 mb-2">📞 โทร: <a href="tel:0901234567" class="text-blue-600 hover:underline">090-123-4567</a></p>
      <p class="text-gray-600 mb-2">✉️ อีเมล: <a href="mailto:support@bundaisufun.com" class="text-blue-600 hover:underline">support@bundaisufun.com</a></p>
      <p class="text-gray-600 mb-6">⏰ เวลาทำการ: จันทร์ - ศุกร์ 09.00 - 18.00 น.</p>

      <div class="flex space-x-4 mt-6">
        <a href="#" class="text-blue-600 hover:text-blue-800 text-2xl"><i class="fab fa-facebook"></i></a>
        <a href="#" class="text-pink-500 hover:text-pink-700 text-2xl"><i class="fab fa-instagram"></i></a>
        <a href="#" class="text-green-500 hover:text-green-700 text-2xl"><i class="fab fa-line"></i></a>
      </div>
    </div>

    <div class="bg-white shadow-lg rounded-2xl p-8">
      <h3 class="text-2xl font-semibold mb-4 text-gray-700">ส่งข้อความถึงเรา</h3>

      <?php if (isset($alert_message)): ?>
        <div class="p-4 mb-4 rounded-lg <?= $is_success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
          <?= htmlspecialchars($alert_message); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">ชื่อของคุณ</label>
          <input type="text" name="full_name" class="w-full border-gray-300 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-400 focus:outline-none" placeholder="เช่น นางสาวสุดารัตน์ ศรีใจดี" required>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">อีเมล</label>
          <input type="email" name="email" class="w-full border-gray-300 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-400 focus:outline-none" placeholder="your@email.com" required>
        </div>
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">ข้อความของคุณ</label>
          <textarea name="message" class="w-full border-gray-300 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-400 focus:outline-none" rows="4" placeholder="เขียนข้อความที่ต้องการติดต่อเรา..." required></textarea>
        </div>
        <button type="submit" class="w-full bg-sky-600 text-white py-2 rounded-lg font-medium hover:bg-sky-700 transition">
          ส่งข้อความ
        </button>
      </form>
    </div>
  </section>

  <section class="w-full h-96 mt-10">
    <iframe
      class="w-full h-full"
      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15840.45781072978!2d100.48518882522778!3d7.000302324677937!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x304d285e6485b001%3A0x1084294f55469448!2z4Lih4Lir4Liy4LiB4Liy4Lij4Li44LiX4Lii4Liy4LiB4Liy4Lij4Liw4Liy4LiB4Lij4Lih4Lir4Liy4Liq4Liy4Lih4Lii4LiB4Liy4Lij!5e0!3m2!1sth!2sth!4v1729604085423!5m2!1sth!2sth"
      allowfullscreen=""
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade">
    </iframe>
  </section>
  <?php require_once 'footer.php'; ?>

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