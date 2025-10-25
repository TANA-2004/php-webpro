<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$active = $active ?? '';
function navClass($key, $active)
{
  return $key === $active ? 'text-sky-700 font-semibold' : 'text-slate-800 hover:text-sky-600';
}
?>
<nav class="pt-4">
  <div class="mx-auto max-w-6xl px-4">
    <div class="relative bg-white/95 backdrop-blur rounded-full shadow-lg ring-1 ring-black/5">
      <div class="flex h-16 items-center justify-between px-3 md:px-6">
        <div class="flex items-center">
          <a class="flex items-center" href="index.php" title="หน้าแรก">
            <img src="Pictures/logo.png" alt="โลโก้ Bundai Su Fun" class="h-8 w-auto mr-2" />
            <span class="font-bold text-lg text-sky-600">Bundai Su Fun</span>
          </a>
        </div>

        <div class="hidden md:flex items-center gap-8 font-medium">
          <a href="aboutus.php" class="<?php echo navClass('about', $active); ?>">เกี่ยวกับเรา</a>
          <a href="courses.php" class="<?php echo navClass('course', $active); ?>">คอร์สเรียน</a>
          <a href="Review.php" class="<?php echo navClass('review', $active); ?>">รีวิวผู้เรียน</a>
          <a href="contact.php" class="<?php echo navClass('contact', $active); ?>">ติดต่อเรา</a>
        </div>

        <div class="hidden md:flex items-center gap-3">
          <?php if (empty($_SESSION['user'])): ?>
            <a href="Signin.php" class="rounded-full px-4 py-2 text-slate-700 bg-slate-100 hover:bg-slate-200">เข้าสู่ระบบ</a>
            <a href="register.php" class="rounded-full px-4 py-2 bg-sky-600 text-white hover:bg-sky-700">สมัครสมาชิก</a>
          <?php else: ?>
            <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
              <a href="admin/dashboard.php"
                class="rounded-lg px-3 py-2 text-red-600 bg-red-100 hover:bg-red-200 text-center font-medium">
                <i class="fas fa-shield-halved"></i> จัดการหลังบ้าน
              </a>
            <?php endif; ?>
            <a href="my_courses.php" class="text-sm font-medium <?php echo navClass('my_courses', $active); ?>">
              คอร์สของฉัน
            </a>
            <a href="order_history.php" class="text-sm font-medium <?php echo navClass('order_history', $active); ?>">
              ประวัติสั่งซื้อ
            </a>
            <span class="text-slate-700 text-sm">
              <b><?php echo htmlspecialchars($_SESSION['user']['username']); ?></b>
            </span>
            <a href="logout.php" class="rounded-full px-4 py-2 bg-sky-600 text-white hover:bg-sky-700">ออกจากระบบ</a>
          <?php endif; ?>
        </div>

        <!-- ปุ่มมือถือ -->
        <div class="flex items-center md:hidden">
          <button type="button" command="--toggle" commandfor="mobile-menu"
            class="inline-flex items-center justify-center rounded-full p-2 text-slate-600 hover:bg-slate-100">
            <span class="sr-only">เปิดเมนู</span>
            <svg class="size-6 in-aria-expanded:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
            </svg>
          </button>
        </div>
      </div>

      <!-- เมนูมือถือ -->
      <el-disclosure id="mobile-menu" hidden class="md:hidden">
        <div class="px-4 pb-4 pt-2">
          <div class="grid gap-2 rounded-2xl bg-white ring-1 ring-black/5 p-3">
            <a href="aboutus.php" class="rounded-lg px-3 py-2 <?php echo $active === 'about' ? 'bg-indigo-50 text-indigo-900' : 'text-slate-700 hover:bg-slate-100'; ?>">เกี่ยวกับเรา</a>
            <a href="courses.php" class="rounded-lg px-3 py-2 <?php echo $active === 'course' ? 'bg-indigo-50 text-indigo-900' : 'text-slate-700 hover:bg-slate-100'; ?>">คอร์สเรียน</a>
            <a href="Review.php" class="rounded-lg px-3 py-2 <?php echo $active === 'review' ? 'bg-indigo-50 text-indigo-900' : 'text-slate-700 hover:bg-slate-100'; ?>">รีวิวผู้เรียน</a>
            <a href="contact.php" class="rounded-lg px-3 py-2 <?php echo $active === 'contact' ? 'bg-indigo-50 text-indigo-900' : 'text-slate-700 hover:bg-slate-100'; ?>">ติดต่อเรา</a>
            <div class="h-px bg-slate-200 my-1"></div>
            <?php if (empty($_SESSION['user'])): ?>
              <a href="Signin.php" class="rounded-full px-3 py-2 text-slate-700 bg-slate-100 hover:bg-slate-200 text-center">เข้าสู่ระบบ</a>
              <a href="register.php" class="rounded-full px-3 py-2 bg-sky-600 text-white hover:bg-sky-700 text-center">สมัครสมาชิก</a>
            <?php else: ?>
              <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin'): ?>
                <a href="admin/dashboard.php"
                  class="rounded-lg px-3 py-2 text-red-600 bg-red-100 hover:bg-red-200 text-center font-medium">
                  <i class="fas fa-shield-halved"></i> จัดการหลังบ้าน
                </a>
              <?php endif; ?>
              <a href="my_courses.php" class="rounded-lg px-3 py-2 <?php echo $active === 'my_courses' ? 'bg-indigo-50 text-indigo-900' : 'text-slate-700 hover:bg-slate-100'; ?>">
                <i class="fas fa-book"></i> คอร์สของฉัน
              </a>
              <a href="order_history.php"
                class="text-sm text-gray-600 hover:text-indigo-700 <?= ($active == 'order_history') ? 'text-indigo-700 font-semibold' : '' ?>">
                ประวัติสั่งซื้อ
              </a>
              <span class="text-center text-slate-700"><?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>
              <a href="logout.php" class="rounded-full px-3 py-2 bg-sky-600 text-white hover:bg-sky-700 text-center">ออกจากระบบ</a>
            <?php endif; ?>
          </div>
        </div>
      </el-disclosure>
    </div>
  </div>
</nav>