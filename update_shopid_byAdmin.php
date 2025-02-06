<?php
session_start();
include 'db.php';

// ตรวจสอบว่าผู้ใช้ล็อกอินอยู่หรือไม่
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$store_id = $_GET['store_id']; // รับค่า store_id จาก URL

// อัปเดต store_id ในตาราง users สำหรับผู้ใช้งานที่ล็อกอิน
$user_id = $_SESSION['user_id']; // ค่า user_id จาก session

// คำสั่ง SQL อัปเดต store_id ของผู้ใช้ในตาราง users
$sql = "UPDATE users SET store_id = ? WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $store_id, $user_id); // bind ค่า store_id และ user_id เป็น integer
$stmt->execute();

// ตรวจสอบผลการอัปเดต
if ($stmt->affected_rows > 0) {
    // ถ้าอัปเดตสำเร็จ ให้ไปที่หน้าร้านค้า
    header('Location: shop_main.php?store_id=' . $store_id);
    exit();
} else {
    // ถ้าไม่สำเร็จ ให้แสดงข้อความผิดพลาด
    echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล.";
}
?>
