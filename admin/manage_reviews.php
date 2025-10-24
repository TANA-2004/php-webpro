<?php
// [ยามเฝ้าประตู]
require_once __DIR__ . '/auth_check.php';

// [ R ] - Read Logic (ดึงรีวิว "ทั้งหมด" มาแสดง)
$sql_select = "SELECT id, full_name, course_name, review_text, created_at 
                FROM reviews 
                ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql_select);
$reviews = [];
if ($result) {
    $reviews = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// (รับข้อความแจ้งเตือนจากไฟล์ action)
$message = $_SESSION['admin_message'] ?? null;
unset($_SESSION['admin_message']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการรีวิว | Admin</title>
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

    <main class="max-w-7xl mx-auto p-6">
        
        <div class="mb-6 flex justify-between items-center">
             <h2 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-star"></i>
                จัดการรีวิว
             </h2>
             <a href="dashboard.php" class="text-indigo-600 hover:underline">
                <i class="fas fa-arrow-left"></i> กลับไป Dashboard
             </a>
        </div>
        
        <?php if ($message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <strong>สำเร็จ!</strong> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white p-8 rounded-xl shadow-lg">
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ผู้รีวิว</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">คอร์ส</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ข้อความ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">ยังไม่มีรีวิว</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td class="px-6 py-4 font-medium"><?= htmlspecialchars($review['full_name']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($review['course_name']) ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-sm break-words">
                                        <?= nl2br(htmlspecialchars($review['review_text'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <form action="manage_reviews_action.php" method="POST" class="inline ml-3" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรีวิวนี้?');">
                                            <input type="hidden" name="review_id" value="<?= $review['id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i> ลบ
                                            </button>
                                        </form>
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