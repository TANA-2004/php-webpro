<?php
// [ยามเฝ้าประตู]
require_once __DIR__ . '/auth_check.php';

// 1. ตรวจสอบว่าเป็น POST และมีข้อมูลครบ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['review_id']) && isset($_POST['action'])) {
    
    $review_id = (int)$_POST['review_id'];
    $action = $_POST['action'];

    if ($action == 'delete') {
        // [ D ] - ลบ
        $sql = "DELETE FROM reviews WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $review_id);
        mysqli_stmt_execute($stmt);
        $_SESSION['admin_message'] = "ลบรีวิว (ID: $review_id) เรียบร้อยแล้ว";
    }
}

// 2. ไม่ว่าจะทำอะไรเสร็จ ให้เด้งกลับไปหน้าจัดการรีวิว
header('Location: manage_reviews.php');
exit();
?>