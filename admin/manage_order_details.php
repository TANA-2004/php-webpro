<?php
// [ยามเฝ้าประตู]
require_once __DIR__ . '/auth_check.php';

// 1. [GET] ตรวจสอบว่ามี ID ออเดอร์ส่งมาใน URL หรือไม่
if (!isset($_GET['id'])) {
    header('Location: manage_orders.php');
    exit;
}
$order_id = (int)$_GET['id'];

$errors = [];
$success_message = '';

// 2. [POST] - เมื่อแอดมินกด "อัปเดตสถานะ"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {

    $new_status = $_POST['order_status'] ?? 'pending';

    // 3. [ U ] - อัปเดตตาราง `orders`
    $sql_update = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt_update = mysqli_prepare($conn, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "si", $new_status, $order_id);

    if (mysqli_stmt_execute($stmt_update)) {
        $success_message = "อัปเดตสถานะเป็น '$new_status' เรียบร้อยแล้ว!";

        // 4. [สำคัญ!] ถ้าสถานะเปลี่ยนเป็น "ชำระเงินแล้ว" (completed)
        //    เราต้อง "ให้สิทธิ์" นักเรียนเข้าคอร์สทันที!
        if ($new_status == 'completed') {

            // 4.1 ดึง user_id และ course_id ทั้งหมดในออเดอร์นี้
            $sql_items = "SELECT o.user_id, oi.course_id 
                          FROM orders o
                          JOIN order_items oi ON o.order_id = oi.order_id
                          WHERE o.order_id = ?";
            $stmt_items = mysqli_prepare($conn, $sql_items);
            mysqli_stmt_bind_param($stmt_items, "i", $order_id);
            mysqli_stmt_execute($stmt_items);
            $result_items = mysqli_stmt_get_result($stmt_items);

            $items_to_enroll = mysqli_fetch_all($result_items, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt_items);

            if (!empty($items_to_enroll)) {
                // 4.2 เตรียมคำสั่ง INSERT ลงตาราง `user_courses`
                // (ใช้ INSERT IGNORE เพื่อป้องกันการ Error หากลงทะเบียนซ้ำ)
                $sql_enroll = "INSERT IGNORE INTO user_courses (user_id, course_id, order_id) VALUES (?, ?, ?)";
                $stmt_enroll = mysqli_prepare($conn, $sql_enroll);

                foreach ($items_to_enroll as $item) {
                    mysqli_stmt_bind_param($stmt_enroll, "iii", $item['user_id'], $item['course_id'], $order_id);
                    mysqli_stmt_execute($stmt_enroll);
                }
                mysqli_stmt_close($stmt_enroll);
                $success_message .= " และให้สิทธิ์เข้าเรียนเรียบร้อย!";
            }
        }
    } else {
        $errors[] = "เกิดข้อผิดพลาดในการอัปเดต: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt_update);
}


// 5. [ R ] - ดึงข้อมูลออเดอร์นี้ (รวมชื่อลูกค้า) มาแสดง
$sql_order = "SELECT o.*, u.fullname, u.email 
              FROM orders o
              JOIN users u ON o.user_id = u.id
              WHERE o.order_id = ?";
$stmt_order = mysqli_prepare($conn, $sql_order);
mysqli_stmt_bind_param($stmt_order, "i", $order_id);
mysqli_stmt_execute($stmt_order);
$result_order = mysqli_stmt_get_result($stmt_order);
$order = mysqli_fetch_assoc($result_order);
mysqli_stmt_close($stmt_order);

if (!$order) {
    // ถ้าหาออเดอร์ไม่เจอ
    header('Location: manage_orders.php');
    exit;
}

// 6. [ R ] - ดึง "รายการคอร์ส" ในออเดอร์นี้ (รวมชื่อคอร์ส)
$sql_items_details = "SELECT oi.price_at_purchase, c.p_title 
                      FROM order_items oi
                      JOIN course c ON oi.course_id = c.p_id
                      WHERE oi.order_id = ?";
$stmt_items_details = mysqli_prepare($conn, $sql_items_details);
mysqli_stmt_bind_param($stmt_items_details, "i", $order_id);
mysqli_stmt_execute($stmt_items_details);
$result_items_details = mysqli_stmt_get_result($stmt_items_details);
$order_items = mysqli_fetch_all($result_items_details, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_items_details);

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดออเดอร์ #<?= $order_id ?> | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../frontweb1.css">
</head>

<body class="bg-gray-100">

    <header class="bg-indigo-900 text-white p-4 shadow-md flex justify-between items-center sticky top-0 z-50">
        <h1 class="text-xl font-bold">Admin Panel</h1>
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
                <i class="fas fa-file-invoice-dollar"></i>
                รายละเอียดออเดอร์ #<?= $order['order_id'] ?>
            </h2>
            <a href="manage_orders.php" class="text-indigo-600 hover:underline">
                <i class="fas fa-arrow-left"></i> กลับไปหน้ารายการ
            </a>
        </div>

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

        <div class="bg-white p-6 rounded-xl shadow-lg mb-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">จัดการสถานะออเดอร์</h3>
            <form method="POST" action="manage_order_details.php?id=<?= $order_id ?>">
                <input type="hidden" name="update_status" value="1">
                <div class="flex items-end gap-4">
                    <div class="flex-grow">
                        <label for="order_status" class="block text-sm font-medium text-gray-700 mb-1">
                            เปลี่ยนสถานะเป็น:
                        </label>
                        <select name="order_status" id="order_status" class="w-full border-gray-300 rounded-lg shadow-sm">
                            <option value="pending" <?= ($order['order_status'] == 'pending') ? 'selected' : '' ?>>
                                1. รอชำระเงิน
                            </option>
                            <option value="completed" <?= ($order['order_status'] == 'completed') ? 'selected' : '' ?>>
                                2. ชำระเงินแล้ว (✅ ให้สิทธิ์เข้าเรียน)
                            </option>
                            <option value="failed" <?= ($order['order_status'] == 'failed') ? 'selected' : '' ?>>
                                3. ล้มเหลว/ยกเลิก
                            </option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">
                            <i class="fas fa-save"></i> อัปเดต
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-semibold mb-4 text-gray-800">ข้อมูลลูกค้า</h3>
                <p><strong>ชื่อ:</strong> <?= htmlspecialchars($order['fullname']) ?></p>
                <p><strong>อีเมล:</strong> <?= htmlspecialchars($order['email']) ?></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-lg">
                <h3 class="text-xl font-semibold mb-4 text-gray-800">ข้อมูลการสั่งซื้อ</h3>
                <p><strong>วันที่สั่งซื้อ:</strong> <?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
                <p class="font-bold text-2xl text-indigo-900 mt-2">
                    ยอดรวม: ฿<?= number_format($order['total_amount'], 2) ?>
                </p>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-lg mt-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">รายการคอร์ส (<?= count($order_items) ?>)</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ชื่อคอร์ส</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ราคา (ณ ตอนซื้อ)</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td class="px-6 py-4 font-medium"><?= htmlspecialchars($item['p_title']) ?></td>
                            <td class="px-6 py-4">฿<?= number_format($item['price_at_purchase'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>

</html>