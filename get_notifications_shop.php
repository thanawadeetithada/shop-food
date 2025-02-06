<?php
session_start();
require_once "db.php";

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'admin') {
    $store_id = $_SESSION['store_id'];
} else {
    $sql = "SELECT store_id FROM stores WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($store_id);
    $stmt->fetch();
    $stmt->close();
}

$sql = "
    SELECT notification
    FROM orders_status 
    WHERE store_id = ? AND (status_order != 'complete' OR status_order IS NULL)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $store_id);
$stmt->execute();
$stmt->bind_result($notification);
$notifications = [];
while ($stmt->fetch()) {
    $notifications[] = $notification;
}
$stmt->close();
echo json_encode($notifications);
?>
