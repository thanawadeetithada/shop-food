<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_order_item_id'])) {
    $cart_order_item_id = intval($_POST['cart_order_item_id']);

    $sql = "SELECT cart_order_id, subtotal FROM cart_order_items WHERE cart_order_item_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_order_item_id);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($cart_order_id, $subtotal);
        $stmt->fetch();
        $stmt->close();

        $sql_delete = "DELETE FROM cart_order_items WHERE cart_order_item_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $cart_order_item_id);
        
        if ($stmt_delete->execute()) {
            $stmt_delete->close();

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

echo json_encode(["success" => false, "message" => "คำขอไม่ถูกต้อง"]);
exit;
?>
