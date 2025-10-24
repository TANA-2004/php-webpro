<?php
require_once 'config.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $fullname = trim($_POST['fullname'] ?? '');
  $username = trim($_POST['username'] ?? '');
  $email    = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm  = $_POST['confirmPassword'] ?? '';

  if ($fullname === '' || $username === '' || $email === '' || $password === '' || $confirm === '') {
    $errors[] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
  }
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "อีเมลไม่ถูกต้อง";
  }
  if ($password !== $confirm) {
    $errors[] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
  }

  if (!$errors) {
    // ตรวจซ้ำ
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username=? OR email=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "ss", $username, $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    if (mysqli_stmt_num_rows($stmt) > 0) {
      $errors[] = "ชื่อผู้ใช้หรืออีเมลถูกใช้แล้ว";
    }
    mysqli_stmt_close($stmt);
  }

  if (!$errors) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (fullname, username, email, password) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $fullname, $username, $email, $hash);
    if (mysqli_stmt_execute($stmt)) {
      // [FIX] สมัครเสร็จแล้วเด้งไปหน้า Signin
      // (เราจะใช้ Session เพื่อส่งข้อความสำเร็จไปแสดงที่หน้า Signin)
      $_SESSION['message'] = "สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ";
      $_SESSION['is_success'] = true;
      
      header("Location: Signin.php");
      exit(); // [สำคัญ] ต้อง exit() ทันทีหลัง redirect

      } else {
    $errors[] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
  }
    mysqli_stmt_close($stmt);
  }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
  <link rel="stylesheet" href="form-register.css">
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
 <?php $active = 'home'; require_once 'navbar.php'; ?>
  <!-- Main Content -->
  <main class="flex-1 flex items-center justify-center py-10">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg px-8 py-10">
      <div class="flex flex-col items-center mb-6">
        <img src="Pictures/logo.png" alt="Logo"
          class="mb-4 w-28 h-28 object-contain rounded-full border border-sky-100 shadow" />
        <h1 class="text-3xl font-bold mb-1 text-sky-700">สมัครสมาชิก</h1>
        <p class="text-gray-500">สร้างบัญชีเพื่อเริ่มต้นการเรียนรู้ของคุณ</p>
      </div>
<?php if ($errors): ?>
  <div class="mb-6 p-3 rounded bg-rose-50 text-rose-700 border border-rose-200">
    <ul class="list-disc pl-5">
      <?php foreach ($errors as $e): ?>
        <li><?php echo htmlspecialchars($e); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
<form class="space-y-5" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
  <div class="space-y-5">
    <div>
      <input id="fullname" name="fullname" type="text" required autocomplete="name"
        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
        placeholder="ชื่อ-นามสกุล" value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>">
    </div>
    <div>
      <input id="username" name="username" type="text" required autocomplete="username"
        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
        placeholder="ชื่อผู้ใช้" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
    </div>
    <div>
      <input id="email" name="email" type="email" required autocomplete="email"
        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
        placeholder="อีเมล" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
    </div>
    <div>
      <input id="password" name="password" type="password" required autocomplete="new-password"
        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
        placeholder="รหัสผ่าน">
    </div>
    <div>
      <input id="confirmPassword" name="confirmPassword" type="password" required autocomplete="new-password"
        class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
        placeholder="ยืนยันรหัสผ่าน">
    </div>
  </div>

  <button type="submit"
    class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2 rounded-lg transition text-lg">
    สมัครสมาชิก
  </button>

  <p class="text-gray-500 text-center mt-6">
    มีบัญชีอยู่แล้ว? <a href="Signin.php" class="text-sky-600 hover:underline">เข้าสู่ระบบ</a>
  </p>
</form>
  </main>

  <?php require_once 'footer.php'; ?>
</body>

</html>