<?php
// ต้องเป็นบรรทัดแรกของไฟล์ — ห้ามมีช่องว่างก่อนหน้า
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "bundai_db";

$conn = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if (!$conn) {
  die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
