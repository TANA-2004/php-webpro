<?php
require_once 'config.php'; // (ต้องมี session_start() และ $conn)

$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');

    // 1. ตรวจสอบข้อมูลเบื้องต้น
    if (empty($email)) {
        $errors[] = "กรุณากรอกอีเมลของคุณ";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "รูปแบบอีเมลไม่ถูกต้อง";
    } else {
        // 2. ตรวจสอบว่ามีอีเมลนี้ในระบบหรือไม่
        $stmt_check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_check, "s", $email);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);

        if (mysqli_stmt_num_rows($stmt_check) === 0) {
            // ไม่พบอีเมล (แต่เรามักจะแสดงข้อความสำเร็จเหมือนกัน เพื่อความปลอดภัย)
            $success_message = "หากอีเมลนี้มีอยู่ในระบบ เราได้ส่งลิงก์รีเซ็ตรหัสผ่านไปให้แล้ว";
        } else {
            // 3. พบอีเมล: สร้าง Token
            $token = bin2hex(random_bytes(32)); // สร้าง Token แบบสุ่ม ปลอดภัย
            $token_hash = password_hash($token, PASSWORD_DEFAULT); // เก็บ Hash ของ Token ใน DB

            // 4. ลบ Token เก่า (ถ้ามี) ของอีเมลนี้
            $stmt_delete = mysqli_prepare($conn, "DELETE FROM password_resets WHERE email = ?");
            mysqli_stmt_bind_param($stmt_delete, "s", $email);
            mysqli_stmt_execute($stmt_delete);
            mysqli_stmt_close($stmt_delete);

            // 5. บันทึก Token ใหม่ลงตาราง password_resets
            $stmt_insert = mysqli_prepare($conn, "INSERT INTO password_resets (email, token) VALUES (?, ?)");
            // (เราเก็บ Hash ของ Token จริง) - แก้ไข: ควรเก็บ hash จริงๆ แต่เพื่อความง่ายในการจำลอง จะเก็บ token ตรงๆ ก่อน
            // mysqli_stmt_bind_param($stmt_insert, "ss", $email, $token_hash);
            mysqli_stmt_bind_param($stmt_insert, "ss", $email, $token); // เก็บ token ตรงๆ เพื่อจำลองง่ายๆ

            if (mysqli_stmt_execute($stmt_insert)) {
                // 6. [จำลอง] การส่งอีเมล
                // ในระบบจริง เราจะใช้ Library อย่าง PHPMailer ส่งอีเมลที่มีลิงก์นี้:
                $reset_link = "http://localhost/BundaiSuFun/reset_password.php?token=" . $token;

                // **แสดงลิงก์จำลองบนหน้าจอ** (เพราะเราส่งเมลจริงไม่ได้บน XAMPP)
                $success_message = "หากอีเมลนี้มีอยู่ในระบบ เราได้ส่งลิงก์รีเซ็ตรหัสผ่านไปให้แล้ว (ลิงก์จำลอง: <a href='$reset_link' class='underline'>คลิกที่นี่</a>)";

                // *** โค้ดส่งเมลจริง (ตัวอย่าง PHPMailer - ต้องติดตั้ง Library เพิ่ม) ***
                /*
                require 'vendor/autoload.php'; // ถ้าใช้ Composer
                use PHPMailer\PHPMailer\PHPMailer;
                $mail = new PHPMailer(true);
                try {
                    //Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.example.com'; // ใส่ SMTP Server ของคุณ
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'user@example.com'; // ใส่ username SMTP
                    $mail->Password   = 'password';        // ใส่ password SMTP
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet = 'UTF-8';

                    //Recipients
                    $mail->setFrom('from@example.com', 'Bundai Su Fun');
                    $mail->addAddress($email);

                    //Content
                    $mail->isHTML(true);
                    $mail->Subject = 'รีเซ็ตรหัสผ่านสำหรับ Bundai Su Fun';
                    $mail->Body    = "กรุณาคลิกลิงก์นี้เพื่อรีเซ็ตรหัสผ่านของคุณ: <br><a href='$reset_link'>$reset_link</a><br>ลิงก์นี้จะหมดอายุใน 1 ชั่วโมง";
                    $mail->AltBody = "กรุณาไปที่ลิงก์นี้เพื่อรีเซ็ตรหัสผ่าน: $reset_link (ลิงก์นี้จะหมดอายุใน 1 ชั่วโมง)";

                    $mail->send();
                    $success_message = "หากอีเมลนี้มีอยู่ในระบบ เราได้ส่งลิงก์รีเซ็ตรหัสผ่านไปให้แล้ว";
                } catch (Exception $e) {
                    $errors[] = "ไม่สามารถส่งอีเมลได้ Mailer Error: {$mail->ErrorInfo}";
                }
                */
            } else {
                $errors[] = "เกิดข้อผิดพลาดในการสร้าง Token";
            }
            mysqli_stmt_close($stmt_insert);
        }
        mysqli_stmt_close($stmt_check);
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ลืมรหัสผ่าน | Bundai Su Fun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <link rel="stylesheet" href="frontweb1.css">
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
    <?php $active = ''; // ไม่มีเมนู active
    require_once 'navbar.php'; ?>

    <main class="flex-1 flex items-center justify-center py-10">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg px-8 py-10">
            <div class="flex flex-col items-center mb-6">
                <i class="fas fa-key fa-3x text-sky-600 mb-4"></i>
                <h1 class="text-3xl font-bold mb-1 text-sky-700">ลืมรหัสผ่าน</h1>
                <p class="text-gray-500 text-center">กรอกอีเมลของคุณเพื่อรับลิงก์สำหรับรีเซ็ตรหัสผ่าน</p>
            </div>

            <?php if ($success_message): ?>
                <div class="mb-4 p-4 rounded-lg bg-green-50 text-green-700 border border-green-200">
                    <?= $success_message ?> </div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="mb-4 p-3 rounded bg-rose-50 text-rose-700 border border-rose-200">
                    <ul class="list-disc pl-5">
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
                <form class="space-y-5" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div>
                        <label for="email" class="sr-only">อีเมล</label>
                        <input id="email" name="email" type="email" required autocomplete="email"
                            class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
                            placeholder="อีเมลของคุณ"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>

                    <button type="submit"
                        class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2 rounded-lg transition text-lg">
                        ส่งลิงก์รีเซ็ตรหัสผ่าน
                    </button>

                    <p class="text-gray-500 text-center mt-6">
                        <a href="Signin.php" class="text-sky-600 hover:underline"><i class="fas fa-arrow-left"></i> กลับไปหน้าเข้าสู่ระบบ</a>
                    </p>
                </form>
        </div>
    </main>
    <?php require_once 'footer.php'; ?>
</body>

</html>