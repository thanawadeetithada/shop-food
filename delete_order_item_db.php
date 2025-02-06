<?php
session_start();
include 'db.php'; // นำเข้าไฟล์เชื่อมต่อฐานข้อมูล

// ตรวจสอบว่ามีค่า cart_order_item_id ถูกส่งมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_order_item_id'])) {
    $cart_order_item_id = intval($_POST['cart_order_item_id']);

    // ค้นหา subtotal และ cart_order_id ของรายการที่กำลังจะลบ
    $sql = "SELECT cart_order_id, subtotal FROM cart_order_items WHERE cart_order_item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_order_item_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($cart_order_id, $subtotal);
        $stmt->fetch();
        $stmt->close();

        // ลบรายการออกจาก order_items
        $sql_delete = "DELETE FROM cart_order_items WHERE cart_order_item_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $cart_order_item_id);
        
        if ($stmt_delete->execute()) {
            $stmt_delete->close();

            // อัปเดต total_price ในตาราง orders
            $sql_update = "UPDATE cart_orders SET total_price = total_price - ? WHERE cart_order_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("di", $subtotal, $cart_order_id);
            
            if ($stmt_update->execute()) {
                echo json_encode(["success" => true, "message" => "ลบข้อมูลสำเร็จและอัปเดตราคารวม"]);
            } else {
                echo json_encode(["success" => false, "message" => "ลบข้อมูลสำเร็จแต่ไม่สามารถอัปเดตราคารวมได้"]);
            }
            $stmt_update->close();
        } else {
            echo json_encode(["success" => false, "message" => "ลบข้อมูลไม่สำเร็จ"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "ไม่พบรายการที่ต้องการลบ"]);
    }
    
    exit;
}

// ถ้าไม่มีค่า cart_order_item_id หรือไม่ได้ใช้ POST
echo json_encode(["success" => false, "message" => "คำขอไม่ถูกต้อง"]);
exit;
?>
