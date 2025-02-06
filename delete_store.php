<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin'])) {
    header('Location: index.php'); 
    exit();
}

if (isset($_GET['store_id'])) {
    $store_id = $_GET['store_id'];

    $sql = "DELETE FROM stores WHERE store_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $store_id);

    if ($stmt->execute()) {
        echo "<script>alert('ร้านค้าถูกลบเรียบร้อยแล้ว'); window.location.href='admin_main.php';</script>";
    } else {
        echo "<script>alert('ไม่สามารถลบร้านค้าได้'); window.location.href='admin_main.php';</script>";
    }
}
?>
