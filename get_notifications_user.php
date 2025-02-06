<?php
session_start();
require_once "db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT notification
    FROM orders_status 
    WHERE user_id = ? AND status_order = 'complete'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$stmt->bind_result($notification);
$notifications = [];

while ($stmt->fetch()) {
    $notifications[] = $notification;
}

$stmt->close();
header('Content-Type: application/json');
echo json_encode($notifications);
?>
