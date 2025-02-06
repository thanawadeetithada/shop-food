<?php
session_start();
require_once "db.php";
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role == 'admin') {
    $store_id = $_SESSION['store_id'];
} else {
    $sql = "SELECT store_id FROM stores WHERE user_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($store_id);
    $stmt->fetch();
    $stmt->close();
}

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

echo json_encode([
    'order_count' => $order_count,
    'preparing_count' => $preparing_count,
    'completed_count' => $completed_count
]);
?>
