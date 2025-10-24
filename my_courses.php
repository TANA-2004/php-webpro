<?php
// (ต้องมี session_start() และ $conn)
require_once 'config.php';

// --- [ด่านตรวจ] ---
// 1. ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    // ถ้ายัง ให้เด้งไปหน้า Login พร้อมจำหน้าปัจจุบันไว้
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; // จำ URL นี้ไว้
    header('Location: Signin.php');
    exit();
}
$user_id = (int)$_SESSION['user_id'];

// --- [ R ] - Read Logic ---
// 2. ดึงข้อมูลคอร์สที่ผู้ใช้คนนี้ "ลงทะเบียนแล้ว" (มีสิทธิ์เข้าเรียน)
// โดย JOIN ตาราง 3 ตาราง:
//   user_courses (uc) -> course (c)  : เพื่อเอาชื่อ, รูปคอร์ส
//   user_courses (uc) -> orders (o) : (ทางเลือก) เพื่อดูว่ามาจากออเดอร์ไหน
$sql = "SELECT
            c.p_id,
            c.p_title,
            c.p_detail,
            c.p_pic,
            uc.enrolled_at,
            uc.order_id
        FROM user_courses uc
        JOIN course c ON uc.course_id = c.p_id
        WHERE uc.user_id = ?
        ORDER BY uc.enrolled_at DESC"; // เรียงตามวันที่ลงทะเบียนล่าสุด

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$enrolled_courses = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คอร์สเรียนของฉัน | Bundai Su Fun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <link rel="stylesheet" href="frontweb1.css">

</head>

<body class="bg-gray-50 text-gray-800">

    <?php $active = 'my_courses'; // ตั้ง active page (ถ้าจะเพิ่มเมนูนี้ใน Navbar)
    require_once 'navbar.php'; ?>

    <section class="max-w-6xl mx-auto pt-16 pb-8 px-6">
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800">
            <i class="fas fa-graduation-cap"></i> คอร์สเรียนของฉัน
        </h1>
        <p class="mt-2 text-gray-600">รายการคอร์สที่คุณได้ลงทะเบียนเรียนไว้</p>
    </section>

    <section class="max-w-6xl mx-auto pb-16 px-6">
        <?php if (empty($enrolled_courses)): ?>
            <div class="text-center bg-white shadow-lg rounded-2xl p-16">
                <i class="fas fa-book-open fa-3x text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">
                    คุณยังไม่มีคอร์สเรียน
                </h2>
                <p class="text-gray-600 mb-6">
                    เลือกดูคอร์สที่น่าสนใจและเริ่มเรียนรู้ได้เลย!
                </p>
                <a
                    href="courses.php"
                    class="inline-block bg-indigo-900 text-white py-2 px-6 rounded-lg font-medium hover:bg-indigo-800 transition">
                    เลือกดูคอร์สเรียน
                </a>
            </div>
        <?php else: ?>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <?php foreach ($enrolled_courses as $course):
                    $imgPath = 'Pictures/' . rawurlencode(trim($course['p_pic'] ?? ''));
                ?>
                    <article class="bg-white rounded-2xl shadow hover:shadow-lg transition overflow-hidden">
                        <img src="<?= $imgPath ?>"
                            alt="<?= htmlspecialchars($course['p_title']) ?>"
                            class="h-40 w-full object-cover"
                            onerror="this.src='Pictures/placeholder.jpg'">
                        <div class="p-5 flex flex-col gap-3">
                            <div>
                                <h3 class="text-xl font-bold"><?= htmlspecialchars($course['p_title']) ?></h3>
                                <p class="text-slate-600 text-sm mt-1"><?= htmlspecialchars($course['p_detail']) ?></p>
                                <p class="text-xs text-gray-400 mt-2">
                                    ลงทะเบียนเมื่อ: <?= date('d M Y', strtotime($course['enrolled_at'])) ?>
                                </p>
                            </div>
                            <a href="course_viewer.php?id=<?= $course['p_id'] ?>"
                                class="mt-2 w-full text-center px-4 py-2 rounded-full bg-green-600 hover:bg-green-700 text-white font-medium">
                                <i class="fas fa-play-circle"></i> เริ่มเรียน
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php require_once 'footer.php'; ?>

</body>

</html>