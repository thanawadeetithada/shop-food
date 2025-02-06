<?php
session_start();
require 'db.php';

if ($_SESSION['role'] == 'customer') {
    die("ไม่ได้รับอนุญาตให้แก้ไขสินค้า");
}

$store_id = $_SESSION['store_id'];

if (!isset($_POST['product_id']) || empty($_POST['product_id'])) {
    die("ไม่พบสินค้าที่ต้องการแก้ไข (product_id ไม่ถูกส่งมา)");
}

$product_id = intval($_POST['product_id']);
$product_name = $_POST['product_name'] ?? '';
$price = $_POST['price'] ?? 0;
$notes = $_POST['notes'] ?? '';

$options = isset($_POST['option']) ? json_encode($_POST['option']) : json_encode([]);
$extra_costs = isset($_POST['extra_cost']) ? json_encode($_POST['extra_cost']) : json_encode([]);

$stmt = $conn->prepare("SELECT image_url FROM products WHERE product_id = ? AND store_id = ?");
$stmt->bind_param("ii", $product_id, $store_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    die("ไม่พบสินค้านี้ในฐานข้อมูล หรือสินค้านี้ไม่ใช่ของร้านคุณ (product_id = " . $product_id . ")");
}

$image_url = $product['image_url'];
$target_dir = "uploads/";

if (!empty($_FILES["product_image"]["name"])) {
    $image_file = $target_dir . basename($_FILES["product_image"]["name"]);
    
    if (move_uploaded_file($_FILES["product_image"]["tmp_name"], $image_file)) {
        $image_url = $image_file;
    } else {
        die("อัปโหลดรูปภาพไม่สำเร็จ");
    }
}

$stmt = $conn->prepare("UPDATE products SET product_name = ?, price = ?, options = ?, extra_cost = ?, image_url = ?, notes = ? WHERE product_id = ? AND store_id = ?");
$stmt->bind_param("sdssssii", $product_name, $price, $options, $extra_costs, $image_url, $notes, $product_id, $store_id);

if ($stmt->execute()) {
    echo "บันทึกการแก้ไขสำเร็จ!";
    header("Location: shop_all_product.php");
    exit();
} else {
    echo "เกิดข้อผิดพลาด: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
