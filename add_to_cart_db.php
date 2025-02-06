<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อ");
}

$user_id = intval($_SESSION['user_id']);
$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);
$options = isset($_POST['options']) ? trim($_POST['options']) : null;
$extra_cost = isset($_POST['extra_cost']) ? floatval($_POST['extra_cost']) : 0.00;
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;
$payment_method = "QR PromptPay";


$sql = "SELECT price, store_id FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    die("สินค้าไม่มีอยู่ในระบบ");
}

$price = floatval($product['price']);
$store_id = intval($product['store_id']);
$subtotal = ($price + $extra_cost);


$sql = "SELECT cart_order_id FROM cart_orders WHERE user_id = ? AND store_id = ? AND status = 'Pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $store_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_order = $result->fetch_assoc();
$stmt->close();

if ($existing_order) {
    $cart_order_id = $existing_order['cart_order_id'];
    
    $total_price = $subtotal * $quantity;

    $sql = "UPDATE cart_orders SET total_price = total_price + ?, extra_cost = extra_cost + ? WHERE cart_order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddi", $total_price, $extra_cost, $cart_order_id);
    $stmt->execute();
    $stmt->close();
} else {
    $total_price = $subtotal * $quantity;

    $sql = "INSERT INTO cart_orders (user_id, store_id, total_price, extra_cost, options, payment_method, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidsss", $user_id, $store_id, $total_price, $extra_cost, $options, $payment_method);
    $stmt->execute();
    $cart_order_id = $stmt->insert_id;
    $stmt->close();
}

$sql = "INSERT INTO cart_order_items (cart_order_id, product_id, quantity, subtotal, options, extra_cost, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiidsds", $cart_order_id, $product_id, $quantity, $subtotal, $options, $extra_cost, $notes);
$stmt->execute();
$stmt->close();

$conn->close();
header("Location: user_cart.php");
exit();
?>
