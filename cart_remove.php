<?php
// (config.php ต้องมี session_start() อยู่แล้ว)
require_once 'config.php';

// 1. ตรวจสอบว่ามี ID คอร์สส่งมาหรือไม่
if (isset($_GET['id'])) {
    
    $course_id = (int)$_GET['id']; // แปลงเป็นตัวเลขเพื่อความปลอดภัย

    // 2. ตรวจสอบว่ามีตะกร้าใน Session
    if (isset($_SESSION['cart'])) {
        
        // 3. ลบ ID คอร์สนี้ออกจากตะกร้า
        unset($_SESSION['cart'][$course_id]);

        // 4. (ทางเลือก) แจ้งเตือน
        $_SESSION['message'] = "ลบคอร์สออกจากตะกร้าแล้ว";
        $_SESSION['is_success'] = true;
    }
}

// 5. ไม่ว่าจะเกิดอะไรขึ้น ให้พากลับไปที่หน้าตะกร้า
header('Location: cart.php');
exit();
?>