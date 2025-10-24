<?php
require_once 'config.php'; // (ใน config.php ต้องมี session_start() อยู่แล้ว)

// [IMPROVEMENT] 
// 1. ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) {
    
    // [!] ผู้ใช้ยังไม่ได้ล็อกอิน
    
    // (ทางเลือก: เพื่อ UX ที่ดี)
    // บันทึกไว้ว่าเขากำลังจะแอดคอร์สไหน
    if (isset($_GET['id'])) {
         $_SESSION['redirect_to_cart_id'] = (int)$_GET['id'];
    }
    
    // ตั้งค่าข้อความแจ้งเตือน (ให้แสดงผลในหน้า register.php)
    $_SESSION['message'] = "กรุณาสมัครสมาชิก หรือเข้าสู่ระบบก่อนเพิ่มคอร์สค่ะ/ครับ";
    $_SESSION['is_success'] = false; // (ตั้งเป็น false = สีแดง/เตือน)
    
    // [!] ส่งไปหน้าสมัครสมาชิก (ตามที่คุณต้องการ)
    // (หมายเหตุ: โดยทั่วไปจะส่งไปหน้า 'login.php' จะดีกว่าครับ)
    header('Location: register.php');
    exit();

} else {
    
    // [OK] ผู้ใช้ล็อกอินแล้ว -> ดำเนินการตามปกติ
    
    if (isset($_GET['id'])) {
        $course_id = (int)$_GET['id']; 
        
        // ตรวจสอบว่ามีตะกร้าหรือยัง
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // เพิ่ม ID คอร์สลงในตะกร้า (ใช้ ID เป็น key เพื่อกันการ add ซ้ำ)
        $_SESSION['cart'][$course_id] = 1; 
        
        $_SESSION['message'] = "เพิ่มคอร์สลงในตะกร้าเรียบร้อยแล้ว!";
        $_SESSION['is_success'] = true;
    }

    // ส่งไปหน้าตะกร้า
    header('Location: cart.php');
    exit();
}
?>