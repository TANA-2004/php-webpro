<?php
// (config.php ต้องมี session_start() อยู่แล้ว)
require_once 'config.php';

$cart_items = [];
$total = 0;

// ตรวจสอบว่ามีตะกร้า และในตะกร้ามีสินค้าหรือไม่
if (!empty($_SESSION['cart'])) {

    // 1. ดึง ID ของคอร์สทั้งหมดจาก Session
    $course_ids = array_keys($_SESSION['cart']);

    // 2. ป้องกัน SQL Injection โดยการแปลง ID ทั้งหมดเป็นตัวเลข
    $sanitized_ids = [];
    foreach ($course_ids as $id) {
        $sanitized_ids[] = (int)$id;
    }

    if (!empty($sanitized_ids)) {
        // 3. สร้างรายการ ID ที่ปลอดภัยสำหรับใช้ใน SQL
        $id_list = implode(',', $sanitized_ids);

        // 4. ดึงข้อมูลคอร์สเฉพาะที่อยู่ในตะกร้า (ใช้คอลัมน์จากตาราง course ของคุณ)
        $sql = "SELECT 
                    p_id, 
                    p_title, 
                    TRIM(p_pic) AS p_pic,
                    CAST(p_price AS DECIMAL(10,2)) AS p_price 
                FROM course 
                WHERE p_id IN ($id_list)";

        $result = mysqli_query($conn, $sql);

        if ($result) {
            $cart_items = mysqli_fetch_all($result, MYSQLI_ASSOC);

            // 5. คำนวณยอดรวม
            foreach ($cart_items as $item) {
                $total += (float)$item['p_price'];
            }
        }
    }
}
// 6. ปิดการเชื่อมต่อ
mysqli_close($conn);

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ตะกร้าคอร์สเรียน | Bundai Su Fun</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
    <link rel="stylesheet" href="frontweb1.css" />
</head>

<body class="bg-gray-50 text-gray-800">

    <?php $active = 'cart'; // (ตั้งค่า active page สำหรับ navbar)
    require_once 'navbar.php'; ?>

    <section class="max-w-6xl mx-auto pt-16 pb-8 px-6">
        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-800">
            ตะกร้าคอร์สเรียน
        </h1>
        <p class="mt-2 text-gray-600">ตรวจสอบรายการคอร์สเรียนของคุณก่อนดำเนินการชำระเงิน</p>
    </section>

    <section class="max-w-6xl mx-auto pb-16 px-6">

        <?php if (!empty($cart_items)): ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2">
                    <div class="bg-white shadow-lg rounded-2xl overflow-hidden">
                        <h2 class="text-xl font-semibold p-6 border-b">
                            คอร์สเรียนทั้งหมด (<?= count($cart_items); ?>)
                        </h2>

                        <div class="divide-y divide-gray-200">
                            <?php foreach ($cart_items as $item):
                                // (ใช้ rawurlencode เหมือนหน้า courses.php)
                                $imgPath = 'Pictures/' . rawurlencode(trim($item['p_pic'] ?? ''));
                            ?>
                                <div class="flex flex-col sm:flex-row items-center gap-4 p-6 hover:bg-gray-50 transition">
                                    <img
                                        src="<?= $imgPath; ?>"
                                        alt="<?= htmlspecialchars($item['p_title']); ?>"
                                        class="w-full sm:w-32 h-auto sm:h-20 rounded-lg object-cover flex-shrink-0"
                                        onerror="this.src='Pictures/placeholder.jpg'">
                                    <div class="flex-grow">
                                        <h3 class="font-semibold text-gray-800">
                                            <?= htmlspecialchars($item['p_title']); ?>
                                        </h3>
                                    </div>
                                    <div class="flex-shrink-0 text-left sm:text-right w-full sm:w-auto mt-4 sm:mt-0">
                                        <p class="font-semibold text-lg text-indigo-900">
                                            ฿<?= number_format($item['p_price'], 2); ?>
                                        </p>
                                        <a href="cart_remove.php?id=<?= $item['p_id']; ?>" class="text-red-500 text-sm hover:underline mt-1">
                                            <i class="fas fa-trash-alt"></i> ลบ
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white shadow-lg rounded-2xl p-6 sticky top-24">
                        <h2 class="text-xl font-semibold border-b pb-4 mb-4">
                            สรุปยอด
                        </h2>

                        <div class="space-y-3">
                            <div class="flex justify-between text-gray-600">
                                <span>ยอดรวม</span>
                                <span>฿<?= number_format($total, 2); ?></span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>ส่วนลด</span>
                                <span class="text-gray-500">- ฿0.00</span>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="flex justify-between font-bold text-xl">
                            <span>ยอดสุทธิ</span>
                            <span>฿<?= number_format($total, 2); ?></span>
                        </div>

                        <a
                            href="checkout.php"
                            class="block w-full text-center bg-indigo-900 text-white py-3 mt-6 rounded-lg font-medium hover:bg-indigo-800 transition">
                            ดำเนินการชำระเงิน
                        </a>
                    </div>
                </div>
            </div>

        <?php else: ?>

            <div class="text-center bg-white shadow-lg rounded-2xl p-16">
                <i class="fas fa-shopping-cart fa-3x text-gray-300 mb-4"></i>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">
                    ตะกร้าของคุณว่างเปล่า
                </h2>
                <p class="text-gray-600 mb-6">
                    ดูเหมือนว่าคุณยังไม่ได้เพิ่มคอร์สเรียนใดๆ ลงในตะกร้า
                </p>
                <a
                    href="courses.php"
                    class="inline-block bg-indigo-900 text-white py-2 px-6 rounded-lg font-medium hover:bg-indigo-800 transition">
                    เลือกดูคอร์สเรียน
                </a>
            </div>

        <?php endif; ?>

    </section>

    <?php require_once 'footer.php'; ?>

</body>

</html>