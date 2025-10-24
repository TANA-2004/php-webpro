<?php
// [ยามเฝ้าประตู]
// เรียกใช้ "ยาม" (auth_check.php) มาเฝ้าหน้านี้เป็นหน้าแรก
// ไฟล์นี้ต้องอยู่บรรทัดบนสุดเสมอ
require_once __DIR__ . '/auth_check.php'; 

//
// ถ้าผู้ใช้ไม่ใช่แอดมิน โค้ดที่อยู่ด้านล่างบรรทัดนี้ทั้งหมดจะไม่ทำงาน
// เพราะจะถูก auth_check.php สั่ง "exit()" ไปก่อน
//

// (ในอนาคต เราสามารถดึงข้อมูลสถิติ เช่น ยอดขายวันนี้, ออเดอร์ใหม่ มาแสดงตรงนี้ได้)
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Bundai Su Fun</title>
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
        <h2 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <a href="manage_courses.php" 
               class="block bg-white p-6 rounded-xl shadow-lg hover:shadow-indigo-100 hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-center gap-4">
                    <i class="fas fa-book fa-2x text-indigo-600"></i>
                    <div>
                        <h3 class="text-xl font-semibold text-indigo-800">จัดการคอร์สเรียน</h3>
                        <p class="text-gray-600 mt-1">เพิ่ม ลบ หรือแก้ไขคอร์สเรียน</p>
                    </div>
                </div>
            </a>

            <a href="manage_orders.php" 
               class="block bg-white p-6 rounded-xl shadow-lg hover:shadow-green-100 hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-center gap-4">
                    <i class="fas fa-receipt fa-2x text-green-600"></i>
                    <div>
                        <h3 class="text-xl font-semibold text-green-800">ดูรายการสั่งซื้อ</h3>
                        <p class="text-gray-600 mt-1">ตรวจสอบสถานะการชำระเงิน</p>
                    </div>
                </div>
            </a>
            
            <a href="manage_reviews.php" 
               class="block bg-white p-6 rounded-xl shadow-lg hover:shadow-blue-100 hover:scale-[1.02] transition-all duration-300">
                <div class="flex items-center gap-4">
                    <i class="fas fa-star fa-2x text-blue-600"></i>
                    <div>
                        <h3 class="text-xl font-semibold text-blue-800">จัดการรีวิว</h3>
                        <p class="text-gray-600 mt-1">อนุมัติหรือลบรีวิวจากผู้ใช้</p>
                    </div>
                </div>
            </a>

             <a href="manage_contacts.php" 
               class="block bg-white p-6 rounded-xl shadow-lg hover:shadow-yellow-100 hover:scale-[1.02] transition-all duration-300">
                 <div class="flex items-center gap-4">
                    <i class="fas fa-inbox fa-2x text-yellow-600"></i>
                    <div>
                        <h3 class="text-xl font-semibold text-yellow-800">ข้อความติดต่อ</h3>
                        <p class="text-gray-600 mt-1">อ่านข้อความจากหน้า "ติดต่อเรา"</p>
                    </div>
                </div>
            </a>

        </div>
    </main>

</body>
</html>