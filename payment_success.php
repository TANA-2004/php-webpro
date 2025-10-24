<?php
require_once 'config.php'; // (ต้องมี session_start() และ $conn)

// 1. รับเลขที่ออเดอร์ที่ถูกส่งมาจาก checkout.php
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// 2. (ทางเลือก) ดึงข้อมูลออเดอร์เพื่อความชัวร์ (ถ้าอยากแสดงชื่อหรือยอดรวม)
// (ในตัวอย่างนี้ เราจะแสดงแค่ ID ก่อนเพื่อให้ง่าย)

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ขอบคุณสำหรับคำสั่งซื้อ | Bundai Su Fun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <link rel="stylesheet" href="frontweb1.css">
</head>

<body class="bg-gray-50 text-gray-800">

    <?php $active = ''; // ไม่มีเมนู active
    require_once 'navbar.php'; ?>

    <main class="max-w-2xl mx-auto text-center py-20 px-6">
        <div class="bg-white p-10 rounded-2xl shadow-lg">

            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>

            <h1 class="text-3xl font-bold text-green-600">ขอบคุณสำหรับคำสั่งซื้อ!</h1>
            <p class="mt-4 text-lg text-gray-700">เราได้รับคำสั่งซื้อของคุณเรียบร้อยแล้ว</p>

            <p class="mt-2 text-gray-600">
                เลขที่ออเดอร์ของคุณคือ:
                <strong class="text-indigo-900 text-lg">#<?= htmlspecialchars($order_id) ?></strong>
            </p>
            <p class="mt-1 text-gray-600">
                สถานะ:
                <span class="font-semibold text-yellow-600">รอการชำระเงิน</span>
            </p>

            <p class="mt-4 text-sm text-gray-500">
                (ในระบบจริง ขั้นตอนต่อไปคือการแสดง QR Code หรือฟอร์มบัตรเครดิตเพื่อชำระเงิน)
            </p>

            <a href="index.php" class="mt-8 inline-block bg-indigo-900 text-white py-2 px-6 rounded-lg font-medium hover:bg-indigo-800 transition">
                กลับหน้าแรก
            </a>
        </div>
    </main>

    <?php require_once 'footer.php'; ?>

</body>

</html>