<?php
session_start();
include "db.php";
if ($_SESSION['role'] == 'admin') {
    $sql = "UPDATE users SET store_id = NULL WHERE role = 'admin'";

    if ($stmt = $conn->prepare($sql)) {
        if ($stmt->execute()) {
            echo "store_id ของ admin ได้ถูกอัปเดตเป็น NULL แล้ว";
        } else {
            echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
        }
        $stmt->close();
    }
}

header("Location: admin_main.php");
exit();
?>
