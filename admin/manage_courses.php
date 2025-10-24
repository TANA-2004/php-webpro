<?php
// [ยามเฝ้าประตู]
require_once __DIR__ . '/auth_check.php';
// (ต้องใช้ $conn ที่มาจาก auth_check.php -> config.php)

$errors = [];
$success_message = '';

// --- [ C ] - Create Logic (เมื่อมีการส่งฟอร์ม "เพิ่มคอร์สใหม่") ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_course'])) {
    
    // 1. รับค่าและทำความสะอาด
    $title = trim($_POST['p_title'] ?? '');
    $detail = trim($_POST['p_detail'] ?? '');
    $price = (float)($_POST['p_price'] ?? 0);
    $pic_file = $_FILES['p_pic'] ?? null;
    $pic_name = '';

    // 2. ตรวจสอบข้อมูล
    if (empty($title) || empty($detail) || $price <= 0) {
        $errors[] = "กรุณากรอกข้อมูลคอร์สเรียนและราคาให้ครบถ้วน";
    }
    if (empty($pic_file) || $pic_file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "กรุณาอัปโหลดรูปภาพหน้าปก";
    }

    // 3. จัดการการอัปโหลดรูปภาพ
    if (empty($errors)) {
        // หาที่เก็บไฟล์ (ถอยหลัง 1 ชั้น ไปที่โฟลเดอร์ /Pictures/)
        $upload_dir = __DIR__ . '/../Pictures/';
        // สร้างชื่อไฟล์ใหม่ที่ไม่ซ้ำกัน (ป้องกันการเขียนทับ)
        $pic_extension = pathinfo($pic_file['name'], PATHINFO_EXTENSION);
        $pic_name = uniqid('course_') . '.' . $pic_extension;
        $upload_path = $upload_dir . $pic_name;

        // (ควรมีการตรวจสอบประเภทไฟล์และขนาดไฟล์เพิ่มเติมในระบบจริง)
        
        if (move_uploaded_file($pic_file['tmp_name'], $upload_path)) {
            // อัปโหลดสำเร็จ
        } else {
            $errors[] = "ไม่สามารถอัปโหลดรูปภาพได้";
        }
    }

    // 4. บันทึกลงฐานข้อมูล
    if (empty($errors)) {
        $sql = "INSERT INTO course (p_title, p_detail, p_price, p_pic) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssds", $title, $detail, $price, $pic_name);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "เพิ่มคอร์สเรียน '$title' เรียบร้อยแล้ว!";
        } else {
            $errors[] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

// --- [ R ] - Read Logic (ดึงข้อมูลคอร์สทั้งหมดมาแสดง) ---
$courses = [];
$sql_select = "SELECT p_id, p_title, p_detail, p_price, p_pic FROM course ORDER BY p_id DESC";
$result = mysqli_query($conn, $sql_select);
if ($result) {
    $courses = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการคอร์สเรียน | Admin</title>
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

    <main class="max-w-7xl mx-auto p-6">
        
        <div class="mb-6 flex justify-between items-center">
             <h2 class="text-3xl font-bold text-gray-800">จัดการคอร์สเรียน</h2>
             <a href="dashboard.php" class="text-indigo-600 hover:underline">
                <i class="fas fa-arrow-left"></i> กลับไป Dashboard
             </a>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-lg mb-8">
            <h3 class="text-2xl font-semibold mb-6 text-indigo-800">
                <i class="fas fa-plus-circle"></i> เพิ่มคอร์สใหม่
            </h3>

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

            <form method="POST" action="manage_courses.php" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="add_course" value="1">
                
                <div>
                    <label for="p_title" class="block text-sm font-medium text-gray-700 mb-1">ชื่อคอร์ส</label>
                    <input type="text" name="p_title" id="p_title" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                </div>
                <div>
                    <label for="p_detail" class="block text-sm font-medium text-gray-700 mb-1">รายละเอียด (ย่อ)</label>
                    <input type="text" name="p_detail" id="p_detail" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                </div>
                <div>
                    <label for="p_price" class="block text-sm font-medium text-gray-700 mb-1">ราคา (บาท)</label>
                    <input type="number" name="p_price" id="p_price"  class="w-full border-gray-300 rounded-lg shadow-sm" required>
                </div>
                <div>
                    <label for="p_pic" class="block text-sm font-medium text-gray-700 mb-1">รูปภาพหน้าปก</label>
                    <input type="file" name="p_pic" id="p_pic" class="w-full" accept="image/jpeg, image/png, image/gif" required>
                </div>
                <div>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-lg">
            <h3 class="text-2xl font-semibold mb-6 text-gray-800">
                <i class="fas fa-list"></i> คอร์สเรียนทั้งหมดในระบบ
            </h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">รูป</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อคอร์ส</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ราคา</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($courses)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">ยังไม่มีคอร์สในระบบ</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td class="px-6 py-4"><?= $course['p_id'] ?></td>
                                    <td class="px-6 py-4">
                                        <img src="../Pictures/<?= htmlspecialchars($course['p_pic']) ?>" alt="" class="w-16 h-10 object-cover rounded">
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-900"><?= htmlspecialchars($course['p_title']) ?></td>
                                    <td class="px-6 py-4">฿<?= number_format($course['p_price'], 2) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="manage_courses_edit.php?id=<?= $course['p_id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-edit"></i> แก้ไข
                                        </a>
                                        <form action="manage_courses_delete.php" method="POST" class="inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบคอร์สนี้?');">
                                            <input type="hidden" name="p_id" value="<?= $course['p_id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i> ลบ
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>