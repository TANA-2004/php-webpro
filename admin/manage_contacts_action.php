<?php
// [ยามเฝ้าประตู]
require_once __DIR__ . '/auth_check.php';

// 1. ตรวจสอบว่าเป็น POST และมีข้อมูลครบ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_id']) && isset($_POST['action'])) {
    
    $contact_id = (int)$_POST['contact_id'];
    $action = $_POST['action'];

    // (ควรมี CSRF Token check ด้วย)

    switch ($action) {
        case 'mark_read':
            // 2. [ U ] - อัปเดต (ตั้ง is_read = 1)
            $sql = "UPDATE contacts SET is_read = 1 WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $contact_id);
            mysqli_stmt_execute($stmt);
            $_SESSION['admin_message'] = "ทำเครื่องหมาย 'อ่านแล้ว' (ID: $contact_id)";
            break;
            
        case 'mark_unread':
            // 3. [ U ] - อัปเดต (ตั้ง is_read = 0)
            $sql = "UPDATE contacts SET is_read = 0 WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $contact_id);
            mysqli_stmt_execute($stmt);
            $_SESSION['admin_message'] = "ทำเครื่องหมาย 'ยังไม่อ่าน' (ID: $contact_id)";
            break;

        case 'delete':
            // 4. [ D ] - ลบ
            $sql = "DELETE FROM contacts WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "i", $contact_id);
            mysqli_stmt_execute($stmt);
            $_SESSION['admin_message'] = "ลบข้อความ (ID: $contact_id) เรียบร้อยแล้ว";
            break;
    }
}

// 5. ไม่ว่าจะทำอะไรเสร็จ ให้เด้งกลับไปหน้าจัดการข้อความ
header('Location: manage_contacts.php');
exit();
?>