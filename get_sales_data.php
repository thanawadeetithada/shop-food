<?php
include "db.php";
header('Content-Type: application/json');

$startDate = $_GET['start_date'] ?? '1970-01-01';
$endDate = $_GET['end_date'] ?? date('Y-m-d'); 

if ($startDate === $endDate) {
    $startDate = $startDate . ' 00:00:00';
    $endDate = $endDate . ' 23:59:59';
} else {
    $startDate = $startDate . ' 00:00:00';
    $endDate = $endDate . ' 23:59:59';
}

$store_id = $_GET['store_id'] ?? null;

error_log("Fetching sales data for Store ID: $store_id, Start Date: $startDate, End Date: $endDate");

if ($store_id) {
    $sql_total_sales = "
    SELECT SUM(oi.subtotal * oi.quantity) AS total_sales_all
    FROM orders_status_items oi
    JOIN orders_status co ON oi.orders_status_id = co.orders_status_id
    WHERE co.store_id = ? 
    AND co.created_at BETWEEN ? AND ? 
    AND co.status_order = 'complete'
";

    $stmt = $conn->prepare($sql_total_sales);
    $stmt->bind_param('iss', $store_id, $startDate, $endDate);
    $stmt->execute();
    $stmt->bind_result($total_sales_all);
    $stmt->fetch();
    $stmt->close();
   $sql_product_sales = "
    SELECT oi.product_id, p.product_name, SUM(oi.subtotal * oi.quantity) AS total_sales
    FROM orders_status_items oi
    JOIN orders_status co ON oi.orders_status_id = co.orders_status_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE co.store_id = ? 
    AND co.created_at BETWEEN ? AND ? 
    AND co.status_order = 'complete'
    GROUP BY oi.product_id, p.product_name
";

    $stmt = $conn->prepare($sql_product_sales);
    $stmt->bind_param('iss', $store_id, $startDate, $endDate);
    $stmt->execute();
    $stmt->bind_result($product_id, $product_name, $total_sales);

    $product_sales = [];
    while ($stmt->fetch()) {
        $product_sales[] = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'total_sales' => $total_sales
        ];
    }

    $stmt->close();
    $conn->close();
    echo json_encode([
        'total_sales_all' => $total_sales_all ?? 0,
        'product_sales' => $product_sales
    ]);
} else {
    echo json_encode(['error' => 'Missing store_id parameter']);
}
?>
