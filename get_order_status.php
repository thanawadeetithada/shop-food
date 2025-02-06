<?php
session_start();
require_once "db.php";

// รับ user_id และ role จาก session
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];  // ตรวจสอบ role ใน session

// ถ้าเป็น admin ให้ใช้ store_id จาก session
if ($role == 'admin') {
    $store_id = $_SESSION['store_id'];  // ใช้ store_id จาก session
} else {
    // ถ้าไม่ใช่ admin ให้คิวรีหาข้อมูลร้านค้าที่เกี่ยวข้อง
    $sql = "SELECT store_id FROM stores WHERE user_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($store_id);
    $stmt->fetch();
    $stmt->close();
}

// คิวรีหาจำนวนคำสั่งซื้อในแต่ละสถานะ
$sql = "
    SELECT 
        COUNT(CASE WHEN status_order IS NULL OR status_order = 'receive' THEN 1 END) AS order_count,
        COUNT(CASE WHEN status_order = 'prepare' THEN 1 END) AS preparing_count,
        COUNT(CASE WHEN status_order = 'complete' THEN 1 END) AS completed_count
    FROM orders_status 
    WHERE store_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $store_id);
$stmt->execute();
$stmt->bind_result($order_count, $preparing_count, $completed_count);
$stmt->fetch();
$stmt->close();

// ส่งผลลัพธ์กลับเป็น JSON
echo json_encode([
    'order_count' => $order_count,
    'preparing_count' => $preparing_count,
    'completed_count' => $completed_count
]);
?>
