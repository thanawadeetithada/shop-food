<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $store_id = $_POST['store_id'];
    $store_name = $_POST['store_name'];
    $user_name = $_POST['user_name'];
    $category = $_POST['category'];

    $sql = "UPDATE stores SET store_name = ?, user_name = ?, category = ? WHERE store_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssi", $store_name, $user_name, $category, $store_id);

        if ($stmt->execute()) {
            header("Location: shop_main.php");
          exit();
        } else {
            echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล.";
        }
        $stmt->close();
    } else {
        echo "ไม่สามารถเตรียมคำสั่ง SQL ได้.";
    }
}

$conn->close();
?>
