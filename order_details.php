<?php
// (ต้องมี session_start() และ $conn)
require_once 'config.php';

// --- [ด่านตรวจ] ---
// 1. ตรวจสอบว่าล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: Signin.php');
    exit();
}
$user_id = (int)$_SESSION['user_id'];

// 2. รับ ID ออเดอร์จาก URL
if (!isset($_GET['id'])) {
    header('Location: order_history.php'); // ถ้าไม่มี ID ส่งกลับไปหน้าประวัติ
    exit();
}
$order_id = (int)$_GET['id'];

// --- [ R ] - Read Logic ---
// 3. ดึงข้อมูล "หัวออเดอร์" นี้ **(สำคัญ: ต้องเช็กว่าเป็นออเดอร์ของผู้ใช้คนนี้จริง)**
$sql_order = "SELECT order_id, total_amount, order_status, created_at
              FROM orders
              WHERE order_id = ? AND user_id = ?"; // <-- เช็ก user_id ด้วย

$stmt_order = mysqli_prepare($conn, $sql_order);
mysqli_stmt_bind_param($stmt_order, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt_order);
$result_order = mysqli_stmt_get_result($stmt_order);
$order = mysqli_fetch_assoc($result_order);
mysqli_stmt_close($stmt_order);

// ถ้าหาไม่เจอ หรือไม่ใช่ของ user นี้ ให้เด้งกลับ
if (!$order) {
    header('Location: order_history.php');
    exit();
}

// 4. ดึง "รายการคอร์ส" ที่อยู่ในออเดอร์นี้
$sql_items = "SELECT
                  oi.price_at_purchase,
                  c.p_title,
                  c.p_pic
              FROM order_items oi
              JOIN course c ON oi.course_id = c.p_id
              WHERE oi.order_id = ?";

$stmt_items = mysqli_prepare($conn, $sql_items);
mysqli_stmt_bind_param($stmt_items, "i", $order_id);
mysqli_stmt_execute($stmt_items);
$result_items = mysqli_stmt_get_result($stmt_items);
$order_items = mysqli_fetch_all($result_items, MYSQLI_ASSOC);
mysqli_stmt_close($stmt_items);

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดออเดอร์ #<?= $order_id ?> | Bundai Su Fun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1  "type="module"></script>
    <link rel="stylesheet" href="frontweb1.css">
</head>
<body class="bg-gray-50 text-gray-800">

    <?php $active = 'order_history'; // ให้เมนู 'ประวัติสั่งซื้อ' Active
    require_once 'navbar.php'; ?>

    <section class="max-w-4xl mx-auto pt-16 pb-8 px-6">
        <div class="mb-4">
            <a href="order_history.php" class="text-indigo-600 hover:underline">
                <i class="fas fa-arrow-left"></i> กลับไปประวัติการสั่งซื้อ
             </a>
        </div>
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800">
            <i class="fas fa-file-invoice"></i> รายละเอียดออเดอร์ #<?= $order['order_id'] ?>
        </h1>
    </section>

    <section class="max-w-4xl mx-auto pb-16 px-6">
        <div class="bg-white rounded-xl shadow-lg p-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-b pb-6">
                <div>
                    <p class="text-sm text-gray-500">วันที่สั่งซื้อ</p>
                    <p class="font-medium"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">สถานะ</p>
                    <p>
                        <?php
                            $status_text = ''; $status_color = '';
                            switch ($order['order_status']) {
                                case 'pending': $status_text = 'รอชำระเงิน'; $status_color = 'bg-yellow-100 text-yellow-800'; break;
                                case 'completed': $status_text = 'ชำระเงินแล้ว'; $status_color = 'bg-green-100 text-green-800'; break;
                                case 'failed': $status_text = 'ล้มเหลว/ยกเลิก'; $status_color = 'bg-red-100 text-red-800'; break;
                                default: $status_text = ucfirst($order['order_status']); $status_color = 'bg-gray-100 text-gray-800';
                            }
                        ?>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_color ?>">
                            <?= $status_text ?>
                        </span>
                    </p>
                </div>
                <div class="md:col-span-2">
                     <p class="text-sm text-gray-500">ยอดรวม</p>
                     <p class="font-bold text-xl text-indigo-900">฿<?= number_format($order['total_amount'], 2) ?></p>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold mb-4">รายการคอร์ส (<?= count($order_items) ?>)</h2>
                <div class="space-y-4">
                    <?php if (empty($order_items)): ?>
                        <p class="text-gray-500">ไม่พบรายการคอร์สในออเดอร์นี้</p>
                    <?php else: ?>
                        <?php foreach ($order_items as $item):
                            $imgPath = 'Pictures/' . rawurlencode(trim($item['p_pic'] ?? ''));
                        ?>
                            <div class="flex items-center gap-4 border-b pb-4 last:border-b-0 last:pb-0">
                                <img src="<?= $imgPath ?>"
                                     alt="<?= htmlspecialchars($item['p_title']) ?>"
                                     class="w-20 h-16 object-cover rounded flex-shrink-0"
                                     onerror="this.src='Pictures/placeholder.jpg'">
                                <div class="flex-grow">
                                    <h3 class="font-medium"><?= htmlspecialchars($item['p_title']) ?></h3>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-sm text-gray-500">ราคา ณ ตอนซื้อ</p>
                                    <p class="font-semibold text-gray-900">฿<?= number_format($item['price_at_purchase'], 2) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </section>

    <?php require_once 'footer.php'; ?>

</body>
</html>