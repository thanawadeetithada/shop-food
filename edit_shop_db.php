<?php
// รวมไฟล์การเชื่อมต่อฐานข้อมูล
include('db.php');

// ตรวจสอบว่ามีการส่งข้อมูลมาจากฟอร์มหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับค่าที่ส่งมาจากฟอร์ม
    $store_id = $_POST['store_id'];
    $store_name = $_POST['store_name'];
    $user_name = $_POST['user_name'];
    $category = $_POST['category'];

    // เขียนคำสั่ง SQL สำหรับการอัปเดตข้อมูลในฐานข้อมูล
    $sql = "UPDATE stores SET store_name = ?, user_name = ?, category = ? WHERE store_id = ?";

    // เตรียมคำสั่ง SQL
    if ($stmt = $conn->prepare($sql)) {
        // ผูกค่าพารามิเตอร์ที่ได้รับจากฟอร์ม
        $stmt->bind_param("sssi", $store_name, $user_name, $category, $store_id);

        // ทำการ execute คำสั่ง SQL
        if ($stmt->execute()) {
            header("Location: shop_main.php");
          exit();
        } else {
            // หากไม่สามารถอัปเดตได้
            echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล.";
        }

        // ปิด statement
        $stmt->close();
    } else {
        // ถ้าไม่สามารถเตรียมคำสั่ง SQL ได้
        echo "ไม่สามารถเตรียมคำสั่ง SQL ได้.";
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
