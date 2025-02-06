<?php
include 'db.php'; // เชื่อมต่อฐานข้อมูล

// รับค่า orders_status_id จาก URL
$orders_status_id = isset($_GET['orders_status_id']) ? $_GET['orders_status_id'] : 0;

// ตรวจสอบว่าได้รับค่า orders_status_id หรือไม่
if ($orders_status_id > 0) {
    // คำสั่ง SQL เพื่อดึงค่า status_order จากฐานข้อมูล
    $sql = "SELECT status_order FROM orders_status WHERE orders_status_id = $orders_status_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_status = $row['status_order']; // ค่า status_order ที่ปัจจุบัน

        // กำหนดค่า status_order ใหม่ตามเงื่อนไข
        if (empty($current_status)) {
            $new_status = 'receive'; // ถ้า status_order เป็น NULL
        } elseif ($current_status == 'receive') {
            $new_status = 'prepare'; // ถ้า status_order เป็น "receive"
        } elseif ($current_status == 'prepare') {
            $new_status = 'complete'; // ถ้า status_order เป็น "prepare"
        } else {
            $new_status = 'complete'; // ถ้า status_order เป็น "complete" หรือค่าอื่นๆ
        }

        // คำสั่ง SQL สำหรับการอัปเดต status_order
        $update_sql = "UPDATE orders_status SET status_order = '$new_status' WHERE orders_status_id = $orders_status_id";

        if ($conn->query($update_sql) === TRUE) {
            // ถ้าอัปเดต status_order สำเร็จ และ new_status คือ 'complete'
            if ($new_status == 'complete') {
                $sql = "UPDATE orders_status SET notification = 1"; // หรือเพิ่มเงื่อนไข WHERE ตามต้องการ

                // ตรวจสอบว่าอัพเดทสำเร็จหรือไม่
                if ($conn->query($sql) === TRUE) {
                    echo "Record updated successfully";
                } else {
                    echo "Error updating record: " . $conn->error;
                }
                
                // ปิดการเชื่อมต่อ
                $conn->close();
            }

            // รีเฟรชหน้าปัจจุบันหลังจากอัปเดตสำเร็จ
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        echo "ไม่พบคำสั่งซื้อ";
    }
} else {
    echo "คำสั่งซื้อไม่ถูกต้อง";
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
