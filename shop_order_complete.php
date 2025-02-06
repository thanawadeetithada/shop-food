<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $store_id = $_SESSION['store_id'];

} else {
    header("Location: index.php");
    exit;
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
    * {
        text-decoration: none;
    }
    .circle span {
        font-size: 16px;
        color: #333;
        margin-top: 10px;
        padding: 0.2rem 0.5rem;
        border-radius: 15px;

    }
    .circle .correct {
        background-color: #FDDF59;
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #fff;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .container {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        background: #fff;
        overflow-y: auto;
        padding: 0px 20px;
    }

    .top-tab {
        width: 100%;
        padding: 30px;
        background-color: #FDDF59;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
    }

    .header {
        margin-top: 5rem;
        color: #333;
        padding: 10px;
        font-size: 1.5em;
    }

    .step {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 15px 0 15px;
    }

    .step .circle {
        font-size: 2rem;
    }

    .status-item {
        display: flex;
        justify-content: space-between;
        padding: 1.5rem 1rem 0.5rem 1rem;
        color: black;
    }

    .icon {
        font-size: 1.8rem;
        margin-right: 0.5rem;
    }

    .status-item .details {
        flex: 1;
        margin-left: 10px;
    }

    .details .phone {
        font-size: 0.9rem;
        font-weight: bold;
    }

    .details .order {
        font-size: 1rem;
        color: black;
        margin: 5px 0;
    }

    .row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 5px;
    }

    .column {
        text-align: center;
        padding: 0 5px 0 0;
    }

    .column:last-child {
        text-align: right;
    }


    .footer {
        align-items: center;
        display: flex;
        justify-content: space-around;
        background-color: #fff;
        padding: 5px 0;
        margin-left: 20px;
        width: 90%;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 100px;
        margin-bottom: 20px;
    }

    .footer-item {
        text-align: center;
        color: #FDDF59;
        font-size: 1.5rem;
        position: relative;
        cursor: pointer;
    }

    .footer-item p {
        font-size: 0.9rem;
        font-weight: bold;
        margin: 5px 0 0;
    }

    .footer-item.active {
        background-color: #FDDF59;
        border-radius: 100px;
        padding: 10px 20px;
        color: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
    }

    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        width: 10px;
        height: 10px;
        background-color: red;
        border-radius: 50%;
        display: none;
    }

    .footer div {
        text-align: center;
    }

    .footer img {
        width: 30px;
    }

    .footer p {
        margin-top: 5px;
        font-size: 12px;
    }

    .footer button {
        background: none;
        border: none;
        font-size: 1.5em;
        cursor: pointer;
    }

    .order-pending {
        background-color: red;
        color: black;
        font-size: 1rem;
        margin: 10px 0;
        border: 1px;
        width: fit-content;
        border-radius: 15px;
        padding: 1px 8px;
    }

    .order-receive {
        background-color: #7fd854;
        color: black;
        font-size: 1rem;
        margin: 10px 0;
        border: 1px;
        width: fit-content;
        border-radius: 15px;
        padding: 1px 8px;
    }

    .order-prepare {
        background-color: #7fd854;
        color: black;
        font-size: 1rem;
        margin: 10px 0;
        border: 1px;
        width: fit-content;
        border-radius: 15px;
        padding: 1px 8px;
    }

    .order-complete {
        color: #52bb4d;
        font-size: 1rem;
        margin-bottom: 5px;
        border: 1px;
        width: fit-content;
        border-radius: 15px;
        padding: 1px 8px;
        margin: 0;
        background: #ffffff;
    }

    main a {
        color: black;
    }
    </style>
</head>

<body>
    <div class="top-tab"></div>
    <div class="container">
        <div class="order-content">
            <div class="header">รายการคำสั่งซื้อ</div>

            <div class="step">
                <div class="circle">
                    <a href="shop_order.php">
                        <span>ออเดอร์</span>
                    </a>
                </div>
                <div class="circle">
                    <a href="shop_order_prepare.php">
                        <span>ที่ต้องจัดเตรียม</span>
                    </a>
                </div>
                <div class="circle">
                    <a href="shop_order_complete.php">
                        <span class="correct">เสร็จสิ้น</span>
                    </a>
                </div>
            </div>

            <main>
                <?php

include 'db.php';
$sql = "
    SELECT o.created_at, o.orders_status_id, o.total_price, p.product_name, osi.notes, o.status_order, u.phone, osi.quantity, osi.options
    FROM orders_status o
    LEFT JOIN orders_status_items osi ON o.orders_status_id = osi.orders_status_id
    LEFT JOIN products p ON osi.product_id = p.product_id
    LEFT JOIN users u ON o.user_id = u.user_id
    WHERE o.store_id = '" . $store_id . "' AND o.status_order = 'complete'
    ORDER BY o.created_at DESC
";

$result = $conn->query($sql);
$orders = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[$row['orders_status_id']][] = $row;
    }

    foreach ($orders as $order_id => $order_items) {
        $first_item = $order_items[0];
        $created_at = date("d M y, H:i", strtotime($first_item['created_at']));
        $total_price = number_format($first_item['total_price'], 2) . "฿";
        $phone = $first_item['phone'];
        $status_order = $first_item['status_order'];
        $notes = $first_item['notes'] ? $first_item['notes'] : '-';
        $options = $first_item['options'] ? $first_item['options'] : '-';
        $status_class = '';
        $status_text = '';
        if (empty($status_order) || is_null($status_order)) {
            $status_text = 'ยังไม่ได้รับออเดอร์';
            $status_class = 'order-pending';
        } elseif ($status_order == 'receive') {
            $status_text = 'รับออเดอร์';
            $status_class = 'order-receive';
        } elseif ($status_order == 'prepare') {
            $status_text = 'กำลังจัดเตรียม';
            $status_class = 'order-prepare'; 
        } elseif ($status_order == 'complete') {
            $status_text = 'เสร็จสิ้นแล้ว';
            $status_class = 'order-complete';
        }

        echo '<a href="shop_order_status.php?orders_status_id=' . $order_id . '" class="order-link">';
        echo '<div class="status-item">';
        echo '<div class="icon"><i class="fa-solid fa-utensils"></i></div>';
        echo '<div class="details">';
        echo '<div class="row">';
        echo '<span class="column"><strong>' . $created_at . '</strong></span>';
        echo '<span class="column"><strong>Order : ' . str_pad($order_id, 3, '0', STR_PAD_LEFT) . '</strong></span>';
        echo '<span class="column"><strong>' . $total_price . '</strong></span>';
        echo '</div>';
                foreach ($order_items as $item) {
            $product_name = $item['product_name'];
            $quantity = $item['quantity'];
            $options = $item['options'];
            $notes = $item['notes'];
            echo '<p class="order"><i class="fa-solid fa-bag-shopping"></i>&nbsp;<strong>' . $product_name . ' x'.$quantity.'</strong></p>';
            echo '<p style="margin: 0 20px"> ตัวเลือกพิเศษ : ' . $options . ' </p>';
            echo '<p style="margin: 0 20px"> หมายเหตุ : ' . $notes . ' </p>';

        }
        echo '<p class="order"><i class="fa-solid fa-circle-user"></i>&nbsp;<strong>' . $phone . '</strong></p>';
        echo '</a>';
        echo '<button class="order-confirm ' . $status_class . '" onclick="updateStatus(' . $order_id . ')">' . $status_text . '</button>';
        echo '</div>';
        echo '</div>';
        echo '<hr>';
    }
} else {
    echo '<p style="margin: 20px;">ไม่พบข้อมูลคำสั่งซื้อ</p>';
}

$conn->close();
?>

            </main>

        </div>
    </div>
    <div class="footer">
        <div class="footer-item active"
            onclick="window.location.href='<?php echo ($_SESSION['role'] == 'admin') ? 'update_shopid_byAdminBack.php' : 'shop_main.php'; ?>'">
            <i class="fa-solid fa-house-chimney"></i>&nbsp;
            <p>HOME</p>
        </div>
        <div class="footer-item active" onclick="window.location.href='shop_order.php'">
            <i class="fa-solid fa-file-alt"></i>
        </div>
        <div class="footer-item " onclick="window.location.href='shop_notification.php'">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-badge"></span>
        </div>
        <div class="footer-item " onclick="window.location.href='shop_all_product.php'">
            <i class="fa-regular fa-folder-open"></i>
        </div>
    </div>

    <script>
    function fetchNotifications() {
        fetch('get_notifications_shop.php')
            .then(response => response.json())
            .then(data => {
                var hasNotification = data.includes(1);
                if (hasNotification) {
                    document.querySelector('.notification-badge').style.display = 'block';
                } else {
                    document.querySelector('.notification-badge').style.display = 'none';
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    }

    fetchNotifications();
    setInterval(fetchNotifications, 1000);
    </script>

</body>

</html>