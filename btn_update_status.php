<?php
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['orders_status_id']) && isset($data['new_status'])) {
    $orders_status_id = $data['orders_status_id'];
    $new_status = $data['new_status'];

    $sql = "UPDATE orders_status SET status_order = ? WHERE orders_status_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('si', $new_status, $orders_status_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัพเดตสถานะได้']);
        }
        
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
}

$conn->close();
?>
