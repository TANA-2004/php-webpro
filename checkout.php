<?php
require_once 'config.php'; // (ต้องมี session_start() และ $conn)

// 1. [ด่านตรวจ] ล็อกอิน
if (!isset($_SESSION['user_id'])) {
    header('Location: Signin.php');
    exit();
}

// 2. [ด่านตรวจ] ตะกร้าว่าง
if (empty($_SESSION['cart'])) {
    header('Location: courses.php');
    exit();
}

// === เริ่มกระบวนการสร้างออเดอร์ (สถานะ Pending) ===

$user_id = (int)$_SESSION['user_id'];
$cart_items = $_SESSION['cart'];
$total = 0;
$course_details = [];

// 3. ดึงราคาคอร์ส
$course_ids = array_keys($cart_items);
$id_list = implode(',', array_map('intval', $course_ids));

if (empty($id_list)) {
    header('Location: courses.php');
    exit();
}

$sql_courses = "SELECT p_id, CAST(p_price AS DECIMAL(10,2)) AS price FROM course WHERE p_id IN ($id_list)";
$result = mysqli_query($conn, $sql_courses);

if (!$result) {
    die("Error fetching course data: " . mysqli_error($conn));
}

while ($row = mysqli_fetch_assoc($result)) {
    $course_details[$row['p_id']] = $row['price'];
    $total += (float)$row['price'];
}

// 4. เริ่ม Transaction
mysqli_autocommit($conn, false);

try {
    // 5. สร้าง `orders` (สถานะ 'pending')
    $sql_order = "INSERT INTO orders (user_id, total_amount, order_status) VALUES (?, ?, ?)";
    $stmt_order = mysqli_prepare($conn, $sql_order);
    $status = 'pending'; // <--- **สถานะเริ่มต้น**
    mysqli_stmt_bind_param($stmt_order, "ids", $user_id, $total, $status);
    mysqli_stmt_execute($stmt_order);
    
    $order_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt_order);

    if ($order_id == 0) {
        throw new Exception("Failed to create order.");
    }

    // 6. สร้าง `order_items`
    $sql_items = "INSERT INTO order_items (order_id, course_id, price_at_purchase) VALUES (?, ?, ?)";
    $stmt_items = mysqli_prepare($conn, $sql_items);
    
    foreach ($course_details as $course_id => $price) {
        mysqli_stmt_bind_param($stmt_items, "iid", $order_id, $course_id, $price);
        mysqli_stmt_execute($stmt_items);
    }
    mysqli_stmt_close($stmt_items);

    // 7. [สำเร็จ] ยืนยันข้อมูล
    mysqli_commit($conn);

    // 8. ล้างตะกร้า
    unset($_SESSION['cart']);

    // 9. **ส่งไปหน้า "ขอบคุณ"** (ไม่ใช่ my_courses.php)
    header("Location: payment_success.php?order_id=" . $order_id);
    exit();

} catch (Exception $e) {
    // 10. [ล้มเหลว] ย้อนกลับ
    mysqli_rollback($conn);
    
    $_SESSION['message'] = "เกิดข้อผิดพลาดในการสร้างออเดอร์: " . $e->getMessage();
    $_SESSION['is_success'] = false;
    header('Location: cart.php'); 
    exit();
}

?>