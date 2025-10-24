<?php
// [ยามเฝ้าประตู]
require_once __DIR__ . '/auth_check.php';

// [ R ] - Read Logic (ดึงข้อมูลออเดอร์ทั้งหมด)
// เราจะ "JOIN" ตาราง orders (o) เข้ากับตาราง users (u)
// เพื่อให้ได้ชื่อของลูกค้า (u.fullname) มาแสดงพร้อมกับออเดอร์
$sql_select = "SELECT 
                    o.order_id, 
                    o.total_amount, 
                    o.order_status, 
                    o.created_at, 
                    u.fullname 
                FROM orders o
                JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC"; // เรียงจากออเดอร์ล่าสุด

$result = mysqli_query($conn, $sql_select);
$orders = [];
if ($result) {
    $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการรายการสั่งซื้อ | Admin</title>
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
            <h2 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-receipt"></i>
                จัดการรายการสั่งซื้อ
            </h2>
            <a href="dashboard.php" class="text-indigo-600 hover:underline">
                <i class="fas fa-arrow-left"></i> กลับไป Dashboard
            </a>
        </div>

        <div class="bg-white p-8 rounded-xl shadow-lg">

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ลูกค้า</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ยอดรวม</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันที่สั่งซื้อ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">ยังไม่มีรายการสั่งซื้อ</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="px-6 py-4 font-medium">#<?= $order['order_id'] ?></td>
                                    <td class="px-6 py-4"><?= htmlspecialchars($order['fullname']) ?></td>
                                    <td class="px-6 py-4">฿<?= number_format($order['total_amount'], 2) ?></td>
                                    <td class="px-6 py-4">
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
                                                $status_text = 'ล้มเหลว';
                                                $status_color = 'bg-red-100 text-red-800';
                                                break;
                                        }
                                        ?>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_color ?>">
                                            <?= $status_text ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <a href="manage_order_details.php?id=<?= $order['order_id'] ?>" class="text-indigo-600 hover:text-indigo-900">
                                            <i class="fas fa-eye"></i> ดูรายละเอียด
                                        </a>
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