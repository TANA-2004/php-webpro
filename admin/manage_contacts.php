<?php
// [ยามเฝ้าประตู]
require_once __DIR__ . '/auth_check.php';

// [ R ] - Read Logic (ดึงข้อความ "ทั้งหมด" มาแสดง)
// [สำคัญ] เรียงลำดับโดยเอา "ยังไม่อ่าน" (is_read = 0) ขึ้นก่อน
// แล้วค่อยเรียงตาม "ล่าสุด"
$sql_select = "SELECT id, full_name, email, message, is_read, created_at 
                FROM contacts 
                ORDER BY is_read ASC, created_at DESC";

$result = mysqli_query($conn, $sql_select);
$contacts = [];
if ($result) {
    $contacts = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// (รับข้อความแจ้งเตือนจากไฟล์ action)
$message = $_SESSION['admin_message'] ?? null;
unset($_SESSION['admin_message']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale-1.0">
    <title>ข้อความติดต่อ | Admin</title>
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
                <i class="fas fa-inbox"></i>
                ข้อความติดต่อ
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

        <div class="bg-white rounded-xl shadow-lg">
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">สถานะ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ผู้ส่ง</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ข้อความ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">วันที่</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php if (empty($contacts)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">ยังไม่มีข้อความติดต่อ</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($contacts as $contact): ?>
                                <tr <?= !$contact['is_read'] ? 'class="bg-yellow-50"' : '' ?>>
                                    <td class="px-6 py-4">
                                        <?php if (!$contact['is_read']): ?>
                                            <span class="px-2 py-1 text-xs font-bold rounded-full bg-yellow-200 text-yellow-800">
                                                <i class="fas fa-envelope"></i> ใหม่
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">
                                                <i class="fas fa-envelope-open"></i> อ่านแล้ว
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium <?= !$contact['is_read'] ? 'text-gray-900 font-bold' : 'text-gray-700' ?>">
                                            <?= htmlspecialchars($contact['full_name']) ?>
                                        </div>
                                        <div class="text-sm text-gray-500"><?= htmlspecialchars($contact['email']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 max-w-sm break-words">
                                        <?= nl2br(htmlspecialchars($contact['message'])) ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?= date('d M Y, H:i', strtotime($contact['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if (!$contact['is_read']): ?>
                                            <form action="manage_contacts_action.php" method="POST" class="inline">
                                                <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                                <input type="hidden" name="action" value="mark_read">
                                                <button type="submit" class="text-green-600 hover:text-green-900" title="ทำเครื่องหมายว่าอ่านแล้ว">
                                                    <i class="fas fa-check"></i> อ่าน
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form action="manage_contacts_action.php" method="POST" class="inline">
                                                <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                                <input type="hidden" name="action" value="mark_unread">
                                                <button type="submit" class="text-gray-500 hover:text-gray-800" title="ทำเครื่องหมายว่ายังไม่อ่าน">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <form action="manage_contacts_action.php" method="POST" class="inline ml-3" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบข้อความนี้?');">
                                            <input type="hidden" name="contact_id" value="<?= $contact['id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="ลบ">
                                                <i class="fas fa-trash"></i>
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