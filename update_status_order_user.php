<?php
include 'db.php';

// Ensure that the `orders_status_id` is provided and is a valid integer
$order_id = isset($_GET['orders_status_id']) ? intval($_GET['orders_status_id']) : 0;

if ($order_id > 0) {
    // Prepare the query to get the status of the order
    $stmt = $conn->prepare("SELECT status_order FROM orders_status WHERE orders_status_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // If the order exists, return its status
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'status_order' => $row['status_order']
        ]);
    } else {
        // If no such order exists, return an empty status
        echo json_encode(['status_order' => '']);
    }

    $stmt->close();
} else {
    // If the order ID is invalid or not provided, return an empty status
    echo json_encode(['status_order' => '']);
}
?>
