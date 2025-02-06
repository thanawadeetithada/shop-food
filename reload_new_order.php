<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $store_id = $_SESSION['store_id'];
} else {
    header("Location: index.php");
    exit;
}

include 'db.php';

$sql = "
    SELECT o.orders_status_id, o.status_order
    FROM orders_status o
    WHERE o.store_id = '" . $store_id . "' AND o.status_order = 'receive'
";

$result = $conn->query($sql);

$orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

echo json_encode($orders);
$conn->close();
?>
