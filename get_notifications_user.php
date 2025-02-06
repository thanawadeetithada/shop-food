<?php
session_start();
require_once "db.php";

// ตรวจสอบว่า user_id ถูกตั้งใน session หรือไม่
if (!isset($_SESSION['user_id'])) {
    echo json_encode([]); // ถ้าไม่มี user_id ให้ส่งกลับเป็น array ว่าง
    exit();
}

$user_id = $_SESSION['user_id'];

// ตรวจสอบ SQL query ที่ถูกต้อง
$sql = "
    SELECT notification
    FROM orders_status 
    WHERE user_id = ? AND status_order = 'complete'";

// สร้างคำสั่ง prepare และ execute
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // ใช้ $user_id แทน $store_id
$stmt->execute();

// Bind ค่าผลลัพธ์
$stmt->bind_result($notification);
$notifications = [];

while ($stmt->fetch()) {
    $notifications[] = $notification;
}

$stmt->close();

// ส่งค่าผลลัพธ์ในรูปแบบ JSON
header('Content-Type: application/json'); // แจ้งว่าเรากำลังส่งข้อมูลในรูปแบบ JSON
echo json_encode($notifications);
?>
