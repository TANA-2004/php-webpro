<?php
require_once 'config.php';
$errors = [];

// ฟังก์ชันดึงผู้ใช้ตาม username/email (รองรับทั้งมี/ไม่มี mysqlnd)
function fetch_user_by_login($conn, $login)
{
  $stmt = mysqli_prepare(
    $conn,
    "SELECT id, fullname, username, email, password, role FROM users WHERE username=? OR email=? LIMIT 1"
  );
  mysqli_stmt_bind_param($stmt, "ss", $login, $login);
  mysqli_stmt_execute($stmt);

  if (function_exists('mysqli_stmt_get_result')) {
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
    return $row;
  }

  mysqli_stmt_bind_result($stmt, $id, $fullname, $username, $email, $hash, $role);
  $row = null;
  if (mysqli_stmt_fetch($stmt)) {
    $row = ['id' => $id, 'fullname' => $fullname, 'username' => $username, 'email' => $email, 'password' => $hash, 'role' => $role];
  }
  mysqli_stmt_close($stmt);
  return $row;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $usernameOrEmail = trim($_POST['usernameOrEmail'] ?? '');
  $password = $_POST['password'] ?? '';

  if ($usernameOrEmail === '' || $password === '') {
    $errors[] = "กรุณากรอกชื่อผู้ใช้/อีเมล และรหัสผ่าน";
  } else {
    $user = fetch_user_by_login($conn, $usernameOrEmail);
    if ($user && password_verify($password, $user['password'])) {
      $_SESSION['user'] = [
        'id'       => $user['id'],
        'fullname' => $user['fullname'],
        'username' => $user['username'],
        'email'    => $user['email'],
        'role'     => $user['role']
      ];
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['role'] = $user['role'];
      // DEBUG ชั่วคราว: ถ้า header ส่งไม่ได้ จะบอกไฟล์/บรรทัดที่มีเอาต์พุตมาก่อน
      if (headers_sent($file, $line)) {
        die("Headers already sent at $file:$line");
      }

      header("Location: index.php");
      exit;
    } else {
      $errors[] = "ชื่อผู้ใช้/อีเมล หรือรหัสผ่านไม่ถูกต้อง";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign In</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
  <link rel="stylesheet" href="form-register.css">
</head>

<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col">
  <?php $active = 'signin';
  require_once 'navbar.php'; ?>
  <!-- Main Sign In Form -->
  <main class="flex-1 flex items-center justify-center py-10">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg px-8 py-10">
      <div class="flex flex-col items-center mb-6">
        <img src="Pictures/logo.png" alt="Logo"
          class="mb-4 w-28 h-28 object-contain rounded-full border border-sky-100 shadow" />
        <h1 class="text-3xl font-bold mb-1 text-sky-700">เข้าสู่ระบบ</h1>
        <p class="text-gray-500">กรุณาเข้าสู่ระบบเพื่อใช้งาน</p>
      </div>
      <?php if (!empty($errors)): ?>
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
            <input id="usernameOrEmail" name="usernameOrEmail" type="text" required autocomplete="username"
              class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
              placeholder="ชื่อผู้ใช้/อีเมล"
              value="<?php echo htmlspecialchars($_POST['usernameOrEmail'] ?? ''); ?>">
          </div>
          <div>
            <input id="password" name="password" type="password" required autocomplete="current-password"
              class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-sky-400 transition"
              placeholder="รหัสผ่าน">
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input id="remember" name="remember" type="checkbox" class="h-4 w-4 accent-sky-600">
              <span class="ml-2 text-gray-700 text-sm">จดจำฉัน</span>
            </div>
            <a href="reset_password.php" class="text-sm text-sky-600 hover:underline">ลืมรหัสผ่าน?</a>
          </div>
          <button type="submit"
            class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2 rounded-lg transition text-lg">
            เข้าสู่ระบบ
          </button>
        </div>
      </form>
      <p class="text-gray-500 text-center mt-6">ยังไม่มีบัญชี? <a href="register.php"
          class="text-sky-600 hover:underline font-medium">สมัครสมาชิก</a></p>
    </div>
  </main>
</body>

</html>