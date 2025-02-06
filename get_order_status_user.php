<?php
include 'db.php';

$order_id = isset($_GET['orders_status_id']) ? $_GET['orders_status_id'] : null;

if ($order_id) {
    $sql = "SELECT status_order FROM orders_status WHERE orders_status_id = '$order_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $status_order = $row['status_order'];

        echo json_encode(['status_order' => $status_order]);
    } else {
        echo json_encode(['status_order' => null]);
    }
} else {
    echo json_encode(['status_order' => null]);
}


$conn->close();
?>
