<?php
session_start();
include 'db.php';


if ($_SESSION['role'] == 'customer') {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    $store_id = $_SESSION['store_id'];

    $sql = "DELETE FROM products WHERE product_id = ? AND store_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $product_id, $store_id);
    if ($stmt->execute()) {

        echo "<script>alert('ลบสินค้าเรียบร้อย'); window.location.href = 'shop_all_product.php';</script>";
    } else {
        echo "<script>alert('ไม่สามารถลบสินค้าได้'); window.location.href = 'shop_all_product.php';</script>";
    }

    $stmt->close();
} else {
    echo "<script>alert('ไม่พบสินค้า'); window.location.href = 'shop_all_product.php';</script>";
}

$conn->close();
?>
