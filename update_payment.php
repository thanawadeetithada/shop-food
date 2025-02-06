<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $logged_in_user_id = $_SESSION['user_id'] ?? 0;

    $sql = "SELECT cart_order_id, total_price, extra_cost, options, payment_method, store_id
            FROM cart_orders 
            WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $logged_in_user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('ไม่พบคำสั่งซื้อของคุณ'); window.history.back();</script>";
        exit();
    }

    if (!empty($_FILES["payment_proof"]["name"])) {
        $target_dir = "img_payments/";
        $file_name = basename($_FILES["payment_proof"]["name"]);
        $file_tmp = $_FILES["payment_proof"]["tmp_name"];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $new_file_name = "payment_" . $logged_in_user_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_file_name;

        $allowed_types = array("jpg", "jpeg", "png");
        if (!in_array($file_ext, $allowed_types)) {
            echo "<script>alert('อนุญาตเฉพาะไฟล์ JPG, JPEG, PNG เท่านั้น'); window.history.back();</script>";
            exit();
        }

        if (move_uploaded_file($file_tmp, $target_file)) {
            $conn->begin_transaction();

            try {
                while ($order = $result->fetch_assoc()) {
                    $cart_order_id = $order['cart_order_id'];
                    $total_price = $order['total_price'];
                    $extra_cost = $order['extra_cost'];
                    $options = $order['options'];
                    $payment_method = $order['payment_method'];
                    $store_id = $order['store_id'];
                    $sql = "INSERT INTO orders_status (user_id, store_id, total_price, extra_cost, options, payment_method, status, img_payment, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'Paid', ?, DATE_ADD(NOW(), INTERVAL 15 HOUR))";
$stmt_insert = $conn->prepare($sql);
$stmt_insert->bind_param("iiddsss", $logged_in_user_id, $store_id, $total_price, $extra_cost, $options, $payment_method, $new_file_name);
$stmt_insert->execute();

            
                    $orders_status_id = $stmt_insert->insert_id;
                    $sql = "SELECT product_id, quantity, subtotal, options, extra_cost, notes 
                            FROM cart_order_items 
                            WHERE cart_order_id = ?";
                    $stmt_items = $conn->prepare($sql);
                    $stmt_items->bind_param("i", $cart_order_id);
                    $stmt_items->execute();
                    $items_result = $stmt_items->get_result();

                    while ($row = $items_result->fetch_assoc()) {
                        $sql = "INSERT INTO orders_status_items (orders_status_id, product_id, quantity, subtotal, options, extra_cost, notes)
                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt_item_insert = $conn->prepare($sql);
                        $stmt_item_insert->bind_param("iiidsds", $orders_status_id, $row['product_id'], $row['quantity'], $row['subtotal'], $row['options'], $row['extra_cost'], $row['notes']);
                        $stmt_item_insert->execute();
                    }

                    $sql = "DELETE FROM cart_order_items WHERE cart_order_id = ?";
                    $stmt_delete_items = $conn->prepare($sql);
                    $stmt_delete_items->bind_param("i", $cart_order_id);
                    $stmt_delete_items->execute();

                    $sql = "DELETE FROM cart_orders WHERE cart_order_id = ?";
                    $stmt_delete_order = $conn->prepare($sql);
                    $stmt_delete_order->bind_param("i", $cart_order_id);
                    $stmt_delete_order->execute();
                }

                $conn->commit();

                echo "<script>alert('ชำระเงินสำเร็จ!'); window.location.href='user_order.php';</script>";
            } catch (Exception $e) {
                $conn->rollback();
                echo "<script>alert('เกิดข้อผิดพลาด: " . $e->getMessage() . "'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดไฟล์'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('กรุณาเลือกไฟล์รูปภาพ'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('ไม่สามารถเข้าถึงหน้านี้โดยตรง'); window.history.back();</script>";
}
$conn->close();

?>