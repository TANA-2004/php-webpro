<?php
require_once 'config.php'; // (ต้องมี session_start() และ $conn)

$errors = []; // ยังคงใช้สำหรับ error ตอนกรอกรหัสใหม่
$success_message = '';
$token = $_GET['token'] ?? '';
$email = null;
$show_form = false; // เริ่มต้นคือไม่แสดงฟอร์ม

// --- [GET] ตรวจสอบ Token เมื่อเปิดหน้า ---
if (empty($token)) {
    // 1. ไม่มี Token ส่งมา -> เด้งกลับไปหน้าลืมรหัสผ่าน
    $_SESSION['message'] = "ลิงก์สำหรับรีเซ็ตรหัสผ่านไม่ถูกต้องหรือไม่สมบูรณ์";
    $_SESSION['is_success'] = false;
    header("Location: forgot_password.php");
    exit();
} else {
    // 2. มี Token ส่งมา -> เริ่มตรวจสอบ
    $stmt_check = mysqli_prepare($conn, "SELECT email, created_at FROM password_resets WHERE token = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt_check, "s", $token);
    mysqli_stmt_execute($stmt_check);
    $result = mysqli_stmt_get_result($stmt_check);
    $reset_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt_check);

    if (!$reset_data) {
        // 2.1 Token ที่ส่งมา หาไม่เจอใน DB -> เด้งกลับ
        $_SESSION['message'] = "Token ไม่ถูกต้องหรือไม่พบ";
        $_SESSION['is_success'] = false;
        header("Location: forgot_password.php");
        exit();
    } else {
        // 2.2 เจอ Token -> เช็กหมดอายุ
        $token_timestamp = strtotime($reset_data['created_at']);
        $current_timestamp = time();
        $expiry_duration = 3600; // 1 ชั่วโมง

        if (($current_timestamp - $token_timestamp) > $expiry_duration) {
            // Token หมดอายุ -> เด้งกลับ
            $_SESSION['message'] = "Token หมดอายุแล้ว กรุณาขอลืมรหัสผ่านใหม่อีกครั้ง";
            $_SESSION['is_success'] = false;
            // (ลบ token ที่หมดอายุ)
            $stmt_delete_expired = mysqli_prepare($conn, "DELETE FROM password_resets WHERE token = ?");
            mysqli_stmt_bind_param($stmt_delete_expired, "s", $token);
            mysqli_stmt_execute($stmt_delete_expired);
            mysqli_stmt_close($stmt_delete_expired);

            header("Location: forgot_password.php");
            exit();
        } else {
            // 2.3 Token ถูกต้อง -> แสดงฟอร์ม
            $email = $reset_data['email'];
            $show_form = true; // ตั้งค่าให้แสดงฟอร์ม
        }
    }
}

// --- [POST] เมื่อผู้ใช้ส่งฟอร์มรหัสผ่านใหม่ ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && $show_form) {
    // ... (โค้ดส่วน POST สำหรับตรวจสอบและอัปเดตรหัสผ่าน เหมือนเดิม) ...
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>รีเซ็ตรหัสผ่าน | Bundai Su Fun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <link rel="stylesheet" href="frontweb1.css">          
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
    <?php $active = '';
    require_once 'navbar.php'; ?>

    <main class="flex-1 flex items-center justify-center py-10">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg px-8 py-10">
            <div class="flex flex-col items-center mb-6">
                <i class="fas fa-lock fa-3x text-sky-600 mb-4"></i>
                <h1 class="text-3xl font-bold mb-1 text-sky-700">รีเซ็ตรหัสผ่าน</h1>
            </div>

            <?php if (!empty($errors)): ?>
            <div class="mb-4 p-3 rounded bg-rose-50 text-rose-700 border border-rose-200">
                <ul class="list-disc pl-5">
                    <?php foreach ($errors as $e): ?>
                        <li><?php echo htmlspecialchars($e); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if ($show_form): // แสดงฟอร์มเมื่อ Token ถูกต้อง ?>
                <p class="text-gray-500 text-center mb-4">กรอกรหัสผ่านใหม่สำหรับอีเมล: <strong><?= htmlspecialchars($email) ?></strong></p>
                <form class="space-y-5" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?token=' . htmlspecialchars($token); ?>">
                     <div>
                         <label for="password" class="sr-only">รหัสผ่านใหม่</label>
                         <input id="password" name="password" type="password" required autocomplete="new-password"
                                class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
                                placeholder="รหัสผ่านใหม่อย่างน้อย 6 ตัวอักษร">
                     </div>
                     <div>
                        <label for="confirmPassword" class="sr-only">ยืนยันรหัสผ่านใหม่</label>
                         <input id="confirmPassword" name="confirmPassword" type="password" required autocomplete="new-password"
                                class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
                                placeholder="ยืนยันรหัสผ่านใหม่">
                     </div>

                    <button type="submit"
                            class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2 rounded-lg transition text-lg">
                        บันทึกรหัสผ่านใหม่
                    </button>
                </form>
            <?php else: // ถ้า $show_form เป็น false (ไม่ว่าจะมี error หรือไม่มี) ?>
                <?php if (empty($errors) && !empty($page_message)): ?>
                     <p class="text-center text-gray-500"><?= htmlspecialchars($page_message) ?></p>
                <?php endif; ?>
                 <p class="text-center mt-4"><a href="forgot_password.php" class="text-sky-600 hover:underline">ขอลืมรหัสผ่านอีกครั้ง</a></p>
            <?php endif; ?>
        </div>
    </main>
    <?php require_once 'footer.php'; ?>
</body>
</html>