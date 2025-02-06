<?php
include 'db.php';

if (isset($_POST['cart_order_item_id']) && isset($_POST['action'])) {
    $cart_order_item_id = $_POST['cart_order_item_id'];
    $action = $_POST['action'];

    if ($action == 'increase') {
        $sql = "UPDATE cart_order_items SET quantity = quantity + 1 WHERE cart_order_item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cart_order_item_id);
        $stmt->execute();
    } else if ($action == 'decrease') {
        $sql = "UPDATE cart_order_items SET quantity = GREATEST(quantity - 1, 1) WHERE cart_order_item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cart_order_item_id);
        $stmt->execute();
    }

    if ($stmt->affected_rows > 0) {
        $stmt = $conn->prepare("SELECT product_id, quantity, extra_cost, subtotal FROM cart_order_items WHERE cart_order_item_id = ?");
        $stmt->bind_param("i", $cart_order_item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt = $conn->prepare("SELECT price FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $item['product_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        $new_subtotal = ($product['price'] + $item['extra_cost']) * $item['quantity'];

        $stmt = $conn->prepare("SELECT total_price FROM cart_orders WHERE cart_order_id = (SELECT cart_order_id FROM cart_order_items WHERE cart_order_item_id = ?)");
        $stmt->bind_param("i", $cart_order_item_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $old_total_price = $row['total_price'];
        if ($action == 'increase') {
            $new_total_price = $old_total_price +  $item['subtotal'];
        } else if ($action == 'decrease') {
            $new_total_price = $old_total_price - ($item['subtotal']);
        }


        $sql = "UPDATE cart_orders SET total_price = ? WHERE cart_order_id = (SELECT cart_order_id FROM cart_order_items WHERE cart_order_item_id = ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $new_total_price, $cart_order_item_id);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'action' => $action, 
            'old_total_price' => $old_total_price,
            'subtotal' => $item['subtotal'],
            'br',
            'new_quantity' => $item['quantity'],
            'new_subtotal' => number_format($new_subtotal, 2),
            'new_total_price' => number_format($new_total_price, 2)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัพเดตข้อมูลได้']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
}
?>