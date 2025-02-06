<?php
include "db.php";
header('Content-Type: application/json');

// รับค่า start_date และ end_date จาก URL
$startDate = $_GET['start_date'] ?? '1970-01-01'; // ค่าเริ่มต้นเมื่อไม่มีการเลือกวันที่
$endDate = $_GET['end_date'] ?? date('Y-m-d'); 

// รับค่า store_id จาก URL
$store_id = $_GET['store_id'] ?? null;

error_log("Fetching sales data for Store ID: $store_id, Start Date: $startDate, End Date: $endDate");

if ($store_id) {

    // SQL สำหรับการคำนวณยอดขายรวมทั้งหมด
    $sql_total_sales = "
    SELECT SUM(oi.subtotal) AS total_sales_all
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

    // SQL สำหรับการคำนวณยอดขายของแต่ละสินค้า
    $sql_product_sales = "
    SELECT oi.product_id, p.product_name, SUM(oi.subtotal) AS total_sales
    FROM orders_status_items oi
    JOIN orders_status co ON oi.orders_status_id = co.orders_status_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE co.store_id = ? 
    AND co.created_at BETWEEN ? AND ? 
    AND co.status_order = 'complete'
    GROUP BY oi.product_id
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

    // ส่งข้อมูลกลับไปเป็น JSON
    echo json_encode([
        'total_sales_all' => $total_sales_all ?? 0,  // กำหนดค่าเป็น 0 ถ้าไม่มีข้อมูลยอดขาย
        'product_sales' => $product_sales
    ]);
} else {
    // ถ้าหากไม่มี store_id หรือ parameter ที่จำเป็น
    echo json_encode(['error' => 'Missing store_id parameter']);
}
?>
