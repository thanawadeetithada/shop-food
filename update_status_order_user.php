<?php
include 'db.php';

$order_id = isset($_GET['orders_status_id']) ? intval($_GET['orders_status_id']) : 0;

if ($order_id > 0) {
    $stmt = $conn->prepare("SELECT status_order FROM orders_status WHERE orders_status_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'status_order' => $row['status_order']
        ]);
    } else {
        echo json_encode(['status_order' => '']);
    }

    $stmt->close();
} else {
    echo json_encode(['status_order' => '']);
}
?>
