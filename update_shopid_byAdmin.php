<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$store_id = $_GET['store_id'];

$user_id = $_SESSION['user_id'];

$sql = "UPDATE users SET store_id = ? WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $store_id, $user_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    header('Location: shop_main.php?store_id=' . $store_id);
    exit();
} else {
    echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล.";
}
?>
