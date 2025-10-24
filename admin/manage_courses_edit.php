<?php
// [ยามเฝ้าประตู]
require_once __DIR__ . '/auth_check.php';

// 1. [GET] ตรวจสอบว่ามี ID ส่งมาใน URL หรือไม่
if (!isset($_GET['id'])) {
    header('Location: manage_courses.php');
    exit;
}
$course_id = (int)$_GET['id'];

$errors = [];
$success_message = '';

// 2. [POST] - เมื่อมีการกด "บันทึกการเปลี่ยนแปลง"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ตรวจสอบ ID ให้ตรงกัน (ป้องกันการปลอมแปลง)
    if ((int)$_POST['p_id'] !== $course_id) {
        die("Security error: ID mismatch.");
    }

    // 3. รับข้อมูลใหม่จากฟอร์ม
    $title = trim($_POST['p_title'] ?? '');
    $detail = trim($_POST['p_detail'] ?? '');
    $price = (float)($_POST['p_price'] ?? 0);
    $old_pic = trim($_POST['old_pic'] ?? ''); // ชื่อรูปเก่า (จาก hidden input)
    $new_pic_file = $_FILES['p_pic'] ?? null;
    $pic_name = $old_pic; // ตั้งชื่อรูปเป็นชื่อเก่าไว้ก่อน

    // 4. ตรวจสอบข้อมูล (เหมือนตอน "เพิ่ม")
    if (empty($title) || empty($detail) || $price <= 0) {
        $errors[] = "กรุณากรอกข้อมูลคอร์สเรียนและราคาให้ครบถ้วน";
    }

    // 5. [สำคัญ] ตรวจสอบว่ามีการอัปโหลด "รูปใหม่" หรือไม่
    if ($new_pic_file && $new_pic_file['error'] === UPLOAD_ERR_OK) {
        // มีการอัปโหลดไฟล์ใหม่
        $upload_dir = __DIR__ . '/../Pictures/';
        
        // สร้างชื่อไฟล์ใหม่
        $pic_extension = pathinfo($new_pic_file['name'], PATHINFO_EXTENSION);
        $pic_name = uniqid('course_') . '.' . $pic_extension;
        $upload_path = $upload_dir . $pic_name;

        if (move_uploaded_file($new_pic_file['tmp_name'], $upload_path)) {
            // อัปโหลดไฟล์ใหม่สำเร็จ
            // [สำคัญ] ลบไฟล์ "รูปเก่า" ทิ้ง
            if (!empty($old_pic) && file_exists($upload_dir . $old_pic)) {
                @unlink($upload_dir . $old_pic);
            }
        } else {
            $errors[] = "ไม่สามารถอัปโหลดรูปภาพใหม่ได้";
            $pic_name = $old_pic; // ถ้าอัปโหลดล้มเหลว, กลับไปใช้รูปเก่า
        }
    }
    // (ถ้าไม่มีการอัปโหลดไฟล์ใหม่ $pic_name ก็จะยังเป็น $old_pic ตามเดิม)

    // 6. [UPDATE] บันทึกข้อมูลใหม่ลงฐานข้อมูล
    if (empty($errors)) {
        $sql_update = "UPDATE course SET p_title = ?, p_detail = ?, p_price = ?, p_pic = ? WHERE p_id = ?";
        $stmt = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt, "ssdsi", $title, $detail, $price, $pic_name, $course_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "อัปเดตข้อมูลคอร์สเรียน (ID: $course_id) เรียบร้อยแล้ว!";
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}


// 7. [GET] - ดึงข้อมูลคอร์ส (ล่าสุด) มาแสดงในฟอร์ม
// (โค้ดส่วนนี้จะทำงาน "ทุกครั้ง" ที่เปิดหน้า ทั้ง GET และ POST)
// (ถ้าเป็น POST มันจะดึงข้อมูลที่เพิ่งอัปเดตไป มาโชว์)
$sql_select = "SELECT p_id, p_title, p_detail, p_price, p_pic FROM course WHERE p_id = ?";
$stmt_select = mysqli_prepare($conn, $sql_select);
mysqli_stmt_bind_param($stmt_select, "i", $course_id);
mysqli_stmt_execute($stmt_select);
$result = mysqli_stmt_get_result($stmt_select);
$course = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt_select);

// ถ้าหาคอร์สไม่เจอ (เช่น ID ผิด หรือถูกลบไปแล้ว) ให้เด้งกลับ
if (!$course) {
    header('Location: manage_courses.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขคอร์ส | Admin</title>
   <script src="https://cdn.tailwindcss.com"></script> 
    <link rel="stylesheet" href="../frontweb1.css">
</head>
<body class="bg-gray-100">
    
    <header class="bg-indigo-900 text-white p-4 shadow-md flex justify-between items-center sticky top-0 z-50">
        <h1 class="text-xl font-bold">
            <i class="fas fa-shield-halved"></i>
            Admin Panel - Bundai Su Fun
        </h1>
        <div>
            <span>สวัสดี, <strong class="font-medium"><?= htmlspecialchars($admin_fullname); ?></strong></span>
            <a href="../logout.php" class="ml-4 text-sm text-indigo-300 hover:text-white hover:underline">
                <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
            </a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto p-6">
        
        <div class="mb-6 flex justify-between items-center">
             <h2 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-edit"></i>
                แก้ไขคอร์สเรียน (ID: <?= $course['p_id'] ?>)
             </h2>
             <a href="manage_courses.php" class="text-indigo-600 hover:underline">
                <i class="fas fa-arrow-left"></i> กลับไปหน้ารายการ
             </a>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
            
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <strong>เกิดข้อผิดพลาด:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li>- <?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <strong>สำเร็จ!</strong> <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="manage_courses_edit.php?id=<?= $course['p_id'] ?>" enctype="multipart/form-data" class="space-y-4">
                
                <input type="hidden" name="p_id" value="<?= $course['p_id'] ?>">
                <input type="hidden" name="old_pic" value="<?= htmlspecialchars($course['p_pic']) ?>">
                
                <div>
                    <label for="p_title" class="block text-sm font-medium text-gray-700 mb-1">ชื่อคอร์ส</label>
                    <input type="text" name="p_title" id="p_title" class="w-full border-gray-300 rounded-lg shadow-sm" 
                           value="<?= htmlspecialchars($course['p_title']) ?>" required>
                </div>
                <div>
                    <label for="p_detail" class="block text-sm font-medium text-gray-700 mb-1">รายละเอียด (ย่อ)</label>
                    <input type="text" name="p_detail" id="p_detail" class="w-full border-gray-300 rounded-lg shadow-sm" 
                           value="<?= htmlspecialchars($course['p_detail']) ?>" required>
                </div>
                <div>
                    <label for="p_price" class="block text-sm font-medium text-gray-700 mb-1">ราคา (บาท)</label>
                    <input type="number" name="p_price" id="p_price" min="0" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm" 
                           value="<?= htmlspecialchars($course['p_price']) ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">รูปภาพหน้าปก (ปัจจุบัน)</label>
                    <img src="../Pictures/<?= htmlspecialchars($course['p_pic']) ?>" alt="" class="w-48 h-auto object-cover rounded mb-2 border">
                    
                    <label for="p_pic" class="block text-sm font-medium text-gray-700 mb-1">เปลี่ยนรูปภาพ (เลือกไฟล์ใหม่ถ้าต้องการ)</label>
                    <input type="file" name="p_pic" id="p_pic" class="w-full" accept="image/jpeg, image/png, image/gif">
                </div>
                <div>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                        <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>