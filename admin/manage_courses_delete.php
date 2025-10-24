<?php
// [ยามเฝ้าประตู]
require_once __DIR__ . '/auth_check.php';

// 1. ตรวจสอบว่าเป็น POST Method และมี p_id ส่งมา
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['p_id'])) {
    
    $course_id = (int)$_POST['p_id'];

    // (ควรมี CSRF Token check ด้วย)

    // 2. (ทางเลือก แต่แนะนำ) ดึงชื่อไฟล์รูปมาก่อน เพื่อลบไฟล์ออกจาก Server
    $sql_find = "SELECT p_pic FROM course WHERE p_id = ?";
    $stmt_find = mysqli_prepare($conn, $sql_find);
    mysqli_stmt_bind_param($stmt_find, "i", $course_id);
    mysqli_stmt_execute($stmt_find);
    $result = mysqli_stmt_get_result($stmt_find);
    $course = mysqli_fetch_assoc($result);
    
    if ($course) {
        $pic_path = __DIR__ . '/../Pictures/' . $course['p_pic'];
        if (file_exists($pic_path)) {
            @unlink($pic_path); // @ ใช้เพื่อซ่อน error ถ้าลบไฟล์ไม่สำเร็จ
        }
    }
    mysqli_stmt_close($stmt_find);

    // 3. สั่งลบข้อมูลออกจากฐานข้อมูล
    $sql_delete = "DELETE FROM course WHERE p_id = ?";
    $stmt_delete = mysqli_prepare($conn, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $course_id);
    
    if (mysqli_stmt_execute($stmt_delete)) {
        // (ทางเลือก) ตั้งค่าข้อความแจ้งเตือน
        $_SESSION['admin_message'] = "ลบคอร์ส (ID: $course_id) เรียบร้อยแล้ว";
    }
    mysqli_stmt_close($stmt_delete);

}

// 4. ไม่ว่าจะลบสำเร็จหรือไม่ ให้เด้งกลับไปหน้าจัดการคอร์ส
header('Location: manage_courses.php');
exit();

?>