<?php
include 'db.php';


$orders_status_id = isset($_GET['orders_status_id']) ? $_GET['orders_status_id'] : 0;


if ($orders_status_id > 0) {

    $sql = "SELECT status_order FROM orders_status WHERE orders_status_id = $orders_status_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_status = $row['status_order'];

        if (empty($current_status)) {
            $new_status = 'receive'; 
        } elseif ($current_status == 'receive') {
            $new_status = 'prepare';
        } elseif ($current_status == 'prepare') {
            $new_status = 'complete';
        } else {
            $new_status = 'complete';
        }

        $update_sql = "UPDATE orders_status SET status_order = '$new_status' WHERE orders_status_id = $orders_status_id";

        if ($conn->query($update_sql) === TRUE) {
            if ($new_status == 'complete') {
                $sql = "UPDATE orders_status SET notification = 1";

                if ($conn->query($sql) === TRUE) {
                    echo "Record updated successfully";
                } else {
                    echo "Error updating record: " . $conn->error;
                }
                $conn->close();
            }
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

$conn->close();
?>
