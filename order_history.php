<?php
// (ต้องมี session_start() และ $conn)
require_once 'config.php';

// --- [ด่านตรวจ] ---
// 1. ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    // ถ้ายัง ให้เด้งไปหน้า Login พร้อมจำหน้าปัจจุบันไว้
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: Signin.php');
    exit();
}
$user_id = (int)$_SESSION['user_id'];

// --- [ R ] - Read Logic ---
// 2. ดึงข้อมูล "ออเดอร์ทั้งหมด" ของผู้ใช้คนนี้
$sql = "SELECT
            order_id,
            total_amount,
            order_status,
            created_at
        FROM orders
        WHERE user_id = ?
        ORDER BY created_at DESC"; // เรียงตามออเดอร์ล่าสุด

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการสั่งซื้อ | Bundai Su Fun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <link rel="stylesheet" href="frontweb1.css">
</head>

<body class="bg-gray-50 text-gray-800">

    <?php $active = 'order_history'; // ตั้ง active page (ถ้าจะเพิ่มเมนูนี้ใน Navbar)
    require_once 'navbar.php'; ?>

    <section class="max-w-6xl mx-auto pt-16 pb-8 px-6">
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800">
            <i class="fas fa-history"></i> ประวัติการสั่งซื้อ
        </h1>
        <p class="mt-2 text-gray-600">รายการสั่งซื้อคอร์สเรียนทั้งหมดของคุณ</p>
    </section>

    <section class="max-w-6xl mx-auto pb-16 px-6">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">รายละเอียด</th>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เลขที่ออเดอร์</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่สั่งซื้อ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ยอดรวม</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($user_orders)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">คุณยังไม่มีประวัติการสั่งซื้อ</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($user_orders as $order): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-indigo-900">#<?= $order['order_id'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">฿<?= number_format($order['total_amount'], 2) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                        $status_text = '';
                                        $status_color = '';
                                        switch ($order['order_status']) {
                                            case 'pending':
                                                $status_text = 'รอชำระเงิน';
                                                $status_color = 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'completed':
                                                $status_text = 'ชำระเงินแล้ว';
                                                $status_color = 'bg-green-100 text-green-800';
                                                break;
                                            case 'failed':
                                                $status_text = 'ล้มเหลว/ยกเลิก';
                                                $status_color = 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                $status_text = ucfirst($order['order_status']);
                                                $status_color = 'bg-gray-100 text-gray-800';
                                        }
                                        ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_color ?>">
                                            <?= $status_text ?>
                                        </span>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="order_details.php?id=<?= $order['order_id'] ?>" class="text-indigo-600 hover:text-indigo-900">
                                            ดูรายละเอียด
                                        </a>
                                    </td>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <?php require_once 'footer.php'; ?>

</body>

</html>