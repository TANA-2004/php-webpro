<?php
require_once 'config.php'; 
/**
 * [IMPROVEMENT] 
 * ฟังก์ชันสำหรับจัดการการส่งฟอร์มรีวิว
 */
function handleReviewSubmission($conn) {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        return;
    }

    // [SECURITY] 1. ตรวจสอบ CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // ถ้า Token ไม่ถูกต้อง ให้หยุดทำงาน
        $_SESSION['message'] = "การส่งข้อมูลไม่ถูกต้อง (Invalid Token)";
        $_SESSION['is_success'] = false;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // 2. รับค่าจากฟอร์มและทำความสะอาด
    $full_name = trim($_POST['full_name'] ?? '');
    $course_name = trim($_POST['course_name'] ?? '');
    $review_text = trim($_POST['review_text'] ?? '');

    // 3. ตรวจสอบค่าว่าง
    if (empty($full_name) || empty($course_name) || empty($review_text)) {
        $_SESSION['message'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        $_SESSION['is_success'] = false;
    } else {
        // 4. เตรียมคำสั่ง SQL
        // (ข้อควรระวัง: ตาราง reviews ของคุณควรกำหนดคอลัมน์ created_at ให้มี DEFAULT CURRENT_TIMESTAMP
        // เพื่อให้วันที่ถูกบันทึกอัตโนมัติ)
        $sql = "INSERT INTO reviews (full_name, course_name, review_text) VALUES (?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $full_name, $course_name, $review_text);

        if ($stmt->execute()) {
            $_SESSION['message'] = "ส่งรีวิวเรียบร้อยแล้ว ขอบคุณมากค่ะ/ครับ! ✨";
            $_SESSION['is_success'] = true;
        } else {
            // [IMPROVEMENT] ไม่แสดง $conn->error ให้ผู้ใช้เห็น
            $_SESSION['message'] = "เกิดข้อผิดพลาดในการส่งรีวิว กรุณาลองใหม่อีกครั้ง";
            $_SESSION['is_success'] = false;
            // สำหรับนักพัฒนา: ควร log error นี้ไว้ดูเอง
            // error_log("Review submission error: " . $conn->error);
        }
        $stmt->close();
    }

    // Post/Redirect/Get (PRG) Pattern
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/**
 * [IMPROVEMENT] 
 * ฟังก์ชันสำหรับดึงข้อมูลรีวิวจากฐานข้อมูล
 */
function getReviews($conn, $limit = 9) {
    $reviews = [];
    $sql_select = "SELECT full_name, course_name, review_text, created_at 
                   FROM reviews 
                   WHERE is_approved = 1
                   ORDER BY created_at DESC 
                   LIMIT ?";
    
    $stmt = $conn->prepare($sql_select);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
    }
    $stmt->close();
    return $reviews;
}

// --- ส่วนประมวลผลหลัก (Main Logic) ---

// 1. เรียกใช้ฟังก์ชันจัดการฟอร์ม (ถ้ามีการส่งข้อมูลมา)
handleReviewSubmission($conn);

// [SECURITY] 2. สร้าง CSRF Token ถ้ายังไม่มี
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// 3. จัดการการแจ้งเตือนจาก SESSION
$message = null;
$is_success = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $is_success = $_SESSION['is_success'];
    unset($_SESSION['message']);
    unset($_SESSION['is_success']);
}

// 4. ดึงข้อมูลรีวิวทั้งหมดมาแสดงผล
$reviews = getReviews($conn, 9); // ดึง 9 รีวิวล่าสุด

// 5. ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();

?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>รีวิวผู้เรียน | Bundai Su Fun</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
  <link rel="stylesheet" href="frontweb1.css" />
</head>

<body class="bg-gray-50 text-gray-800">
  <?php $active = 'review';
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
        รีวิวจากผู้เรียนจริง
      </h1>
      <p class="mt-3 text-white/90 max-w-3xl mx-auto">
        เสียงตอบรับจากนักเรียนของเราที่เรียนกับ Bundai Su Fun
        <br>
        ทุกคำบอกเล่าคือแรงบันดาลใจในการพัฒนาอย่างต่อเนื่อง
      </p>
    </div>
  </section>

  <section class="max-w-6xl mx-auto py-16 px-6">
    <div class="grid md:grid-cols-3 gap-8">
      <?php if (count($reviews) > 0): ?>
        <?php foreach ($reviews as $review): ?>
          <div class="bg-white shadow-md rounded-2xl p-6">
            <div class="flex items-center mb-4">
              <div class="w-14 h-14 rounded-full mr-4 bg-blue-200 flex items-center justify-center text-blue-800 font-bold text-xl">
                <?= mb_substr(htmlspecialchars($review['full_name']), 0, 1, 'UTF-8'); ?>
              </div>
              <div>
                <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($review['full_name']); ?></h3>
                <p class="text-gray-500 text-sm">คอร์ส: <?= htmlspecialchars($review['course_name']); ?></p>
              </div>
            </div>
            <p class="text-gray-600 mb-3">
              "<?= nl2br(htmlspecialchars($review['review_text'])); ?>"
            </p>
            <p class="text-gray-400 text-xs">
              ส่งเมื่อ: <?= date('d M Y', strtotime($review['created_at'])); ?>
            </p>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="md:col-span-3 text-center text-gray-500">
          ยังไม่มีรีวิวในระบบ ส่งรีวิวของคุณเป็นคนแรกได้เลย!
        </div>
      <?php endif; ?>
    </div>

    <div class="mt-16 text-center">
      <h2 class="text-2xl font-bold text-gray-800 mb-4">อยากแชร์ประสบการณ์ของคุณ?</h2>
      <p class="text-gray-600 mb-6">ส่งรีวิวของคุณมาให้เราที่นี่ แล้วเราจะนำไปแสดงบนเว็บไซต์</p>

      <?php if (isset($message)): ?>
        <div class="max-w-xl mx-auto p-4 mb-4 rounded-lg <?= $is_success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
          <?= htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="max-w-xl mx-auto bg-white shadow-lg rounded-2xl p-8 text-left">
        
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="mb-4">
          <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">ชื่อ-นามสกุล</label>
          <input type="text" id="full_name" name="full_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-400 focus:outline-none" placeholder="ชื่อของคุณ" required>
        </div>
        
        <div class="mb-4">
          <label for="course_name" class="block text-sm font-medium text-gray-700 mb-2">คอร์สที่เรียน</label>
          <input type="text" id="course_name" name="course_name" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-400 focus:outline-none" placeholder="ชื่อคอร์สเรียน เช่น คณิต ม.6 TGAT" required>
          <p class="text-xs text-gray-500 mt-1">ตัวอย่าง: คณิตศาสตร์ ม.6, คอร์สติว TGAT</p>
        </div>
        
        <div class="mb-4">
          <label for="review_text" class="block text-sm font-medium text-gray-700 mb-2">ข้อความรีวิว</label>
          <textarea 
            id="review_text" 
            name="review_text" 
            rows="5" 
            class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-sky-400 focus:outline-none" 
            placeholder="เขียนรีวิวของคุณที่นี่..." 
            required></textarea>
        </div>

        <button type="submit" class="w-full bg-sky-600 text-white py-2 rounded-lg font-medium hover:bg-sky-700 transition">
          ส่งรีวิว
        </button>
      </form>
    </div>
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
    bgA.style.backgroundImage = `url(${images[0]})`;
    setInterval(changeBackground, 5000);
  </script>
</body>

</html>