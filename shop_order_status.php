<?php
include 'db.php'; 

$orders_status_id = isset($_GET['orders_status_id']) ? $_GET['orders_status_id'] : 0;

$sql = "SELECT os.created_at, os.orders_status_id, os.status_order, os.payment_method, os.total_price, 
               osi.quantity, osi.subtotal, osi.notes, osi.options, p.product_name, p.price, u.phone
        FROM orders_status os
        LEFT JOIN orders_status_items osi ON os.orders_status_id = osi.orders_status_id
        LEFT JOIN products p ON osi.product_id = p.product_id
        LEFT JOIN users u ON os.user_id = u.user_id
        WHERE os.orders_status_id = $orders_status_id";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $created_at = date("d M y, H:i", strtotime($row['created_at']));
    $order_id = str_pad($row['orders_status_id'], 3, "0", STR_PAD_LEFT);
    $status_order = $row['status_order'];
    $payment_method = $row['payment_method'];
    $total_price = $row['total_price'];
    $user_phone = $row['phone'];
    $items = [];

    do {
        $product_name = $row['product_name'];
        $subtotal = $row['subtotal'];
        $quantity = $row['quantity'];
        $notes = $row['notes'];
        $options = $row['options'];

        $items[] = [
            'product_name' => $product_name,
            'quantity' => $quantity,
            'subtotal' => isset($row['subtotal']) ? $row['subtotal'] : 0,
            'notes' => $notes,
            'options' => $options
        ];
             
    } while ($row = $result->fetch_assoc());

} else {
    echo "ไม่พบข้อมูลคำสั่งซื้อ";
}

if ($status_order == "receive") {
    $status_message = "รับออเดอร์";
} elseif ($status_order == "prepare") {
    $status_message = "เสร็จสิ้น";
} elseif ($status_order == "complete") {
    $status_message = "เรียบร้อย";
} else {
    $status_message = "สถานะไม่ทราบ"; 
}

if ($orders_status_id != 0 && $status_order != 'complete') {
    $sqlUpdate = "UPDATE orders_status SET notification = 0 WHERE orders_status_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("i", $orders_status_id);
    $stmtUpdate->execute();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถานะคำสั่งซื้อ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f8f8f8;
    }

    .container {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        overflow-y: auto;
        padding: 0px 20px;
    }

    .order-content {
        flex-grow: 1;
    }

    .details-bottom {
        background-color: #fff;
        padding: 20px;
    }

    .header {
        margin-top: 5rem;
        color: #333;
        padding: 10px;
        font-size: 1.5em;
    }

    .order {
        padding: 15px;
        border-bottom: 1px solid #ddd;
    }

    .order:last-child {
        border-bottom: none;
    }

    .order-status {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .status {
        font-weight: bold;
    }

    .status.pending {
        color: orange;
    }

    .status.completed {
        color: #0FBE19;
    }

    .details {
        padding: 15px;
        font-size: 1.2rem;
    }

    .details strong {
        display: block;
    }

    .reorder-button {
        display: block;
        text-align: center;
        background-color: #7ed956;
        color: #333;
        text-decoration: none;
        padding: 10px;
        border-radius: 15px;
        font-size: 1.2rem;
    }

    .step {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
    }

    .step .circle {
        font-size: 2rem;
    }

    .step .line {
        flex-grow: 1;
        height: 2px;
        margin: 0 10px;
        border-top: 5px dotted #ddd;
        margin-bottom: 25px;
    }

    .step .line.active {
        border-top: 5px dotted #0FBE19;
        margin-bottom: 25px;
    }

    .status.pending {
        color: orange;
    }

    .status.completed {
        color: #0FBE19;
    }

    .top-tab {
        width: 100%;
        padding: 20px;
        background-color: #FDDF59;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
    }

    .circle {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .circle span {
        font-size: 16px;
        color: #333;
        margin-top: 10px;
    }

    .order-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
    }

    .order-right {
        margin-left: auto;
    }

    ul {
        padding: 0;
        list-style-type: none;
    }
    </style>
</head>

<body>
    <div class="top-tab">
        <i class="fa-solid fa-arrow-left" onclick="window.location.href='shop_order.php';" style="cursor: pointer;"></i>

    </div>

    <div class="container">
        <div class="order-content">
            <div class="header">รายการคำสั่งซื้อ</div>

            <div class="details">
                <div class="order-info">
                    <span><strong><?php echo $created_at; ?></strong></span>
                    <span class="order-right"><strong>Order : <?php echo $order_id; ?></strong></span>
                </div>
                <span style="display: inline-flex;align-items: center;margin-bottom: 10px;">
                    <i class="fa-solid fa-circle-user" style="margin-right: 5px;"></i>
                    <strong><?php echo $user_phone; ?></strong>
                </span>
                <hr>
                <ul>
                    <?php foreach ($items as $item) : ?>
                    <li style="display: flex; justify-content: space-between; margin-top: 20px;">
                        <span style="width: 50%;"><?php echo $item['product_name']; ?></span>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; width: 25%;">
                            <span><?php echo number_format($item['subtotal'], 2); ?>฿</span>
                            <span>x<?php echo $item['quantity']; ?></span>
                        </div>
                    </li>
                    <span>ตัวเลือกพิเศษ : <span>
                            <span><?php echo (empty($item['options']) || $item['options'] === null) ? '-' : $item['options']; ?></span>
                        </span></span><br>
                    <span style="color:#e1e1e1;">หมายเหตุ :
                        <span><?php echo !empty($item['notes']) ? $item['notes'] : '-'; ?></span></span>
                    <?php endforeach; ?>
                </ul>

            </div>

        </div>

        <div class="details-bottom">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin-bottom: 0px;"><strong>ยอดชำระ</strong></h2>
                <h2 style="color: red; margin-bottom: 0px;">
                    <strong><?php echo number_format($total_price, 2); ?>฿</strong>
                </h2>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <p><strong>วิธีการชำระ</strong></p>
                <p><?php echo $payment_method; ?></p>
            </div>
            <hr>
            <br>
            <a href="<?php echo ($status_message == "เรียบร้อย") ? 'shop_order.php' : 'update_status.php?orders_status_id=' . $orders_status_id; ?>"
                class="reorder-button <?php echo ($status_message == "เรียบร้อย") ? 'finsh' : ''; ?>"
                <?php echo ($status_message == "เรียบร้อย") ? 'style="background-color: #ccc; cursor: not-allowed;"' : ''; ?>>
                <?php echo $status_message; ?>
            </a>
        </div>

    </div>
    <script>
    var items = <?php echo json_encode($items); ?>;
    console.log(items);
    </script>

</body>

</html>

<?php
$conn->close();
?>