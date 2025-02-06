<?php
session_start();
include 'db.php';

$order_id = isset($_GET['orders_status_id']) ? intval($_GET['orders_status_id']) : 0;

if ($order_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM orders_status WHERE orders_status_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        die("ไม่พบคำสั่งซื้อ");
    }

    $stmt = $conn->prepare("SELECT osi.*, p.product_name, osi.notes
                        FROM orders_status_items osi 
                        JOIN products p ON osi.product_id = p.product_id 
                        WHERE osi.orders_status_id = ?");

    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $status_order = $order['status_order'];
    $step_class = [
        ($status_order == 'receive' || $status_order == 'prepare' || $status_order == 'complete') ? 'active' : '',
        ($status_order == 'prepare' || $status_order == 'complete') ? 'active' : '',
        ($status_order == 'complete') ? 'active' : ''
    ];
} else {
    die("ไม่พบคำสั่งซื้อ");
}

$user_id = 0; 
if ($order_id > 0) {
    $stmt = $conn->prepare("SELECT user_id FROM orders_status WHERE orders_status_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $user_id = $row['user_id'];
    }
    $stmt->close();
}

$phone = "ไม่พบเบอร์โทรศัพท์";
if ($user_id > 0) {
    $stmt = $conn->prepare("SELECT phone FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $phone = $row['phone'];
    }
    $stmt->close();
}

if ($order_id != 0 && $status_order == 'complete') {
    $sqlUpdate = "UPDATE orders_status SET notification = 0 WHERE orders_status_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("i", $order_id);
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
    * {
        margin: 0;
        color: black;
        padding: 0;
        box-sizing: border-box;
        text-decoration: none;
    }

    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f8f8f8;
        display: flex;
        flex-direction: column;
        height: 100vh;
    }

    .container {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        overflow-y: auto;
        padding: 0px;
    }

    .details-bottom {
        background-color: #fff;
        padding: 20px 30px;
    }

    .header {
        margin-top: 4rem;
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
        background-color: #ffd700;
        color: #333;
        text-decoration: none;
        padding: 10px;
        border-radius: 15px;
        font-size: 1.2rem;
    }

    .reorder-button:hover {
        background-color: #ffc107;
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

    .order-content {
        flex: 1;
        padding: 20px;
    }

    .order-list {
        margin-top: 10px;
    }
    </style>
</head>

<body>
    <div class="top-tab">
        <a href="javascript:history.back();"><i class="fa-solid fa-arrow-left"></i></a>
    </div>
    <div class="container">
        <div class="order-content">
            <div class="header">
                <?php
            if ($status_order == 'receive') {
                echo "ร้านได้รับออเดอร์แล้ว";
            } elseif ($status_order == 'prepare') {
                echo "อาหารกำลังเตรียม";
            } elseif ($status_order == 'complete') {
                echo "ออเดอร์เสร็จสิ้นแล้ว";
            } else {
                echo "กำลังดำเนินการ";
            }
            ?>
            </div>

            <div class="step">
                <div class="circle <?= $step_class[0] ?>">
                    <i class="fa-solid fa-circle-check"
                        style="color: <?= ($step_class[0]) ? '#0FBE19' : '#ddd'; ?>;"></i>
                    <span>รับออเดอร์</span>
                </div>
                <div class="line <?= $step_class[1] ?>"></div>
                <div class="circle <?= $step_class[1] ?>">
                    <i class="fa-solid fa-circle-check"
                        style="color: <?= ($step_class[1]) ? '#0FBE19' : '#ddd'; ?>;"></i>
                    <span>กำลังเตรียม</span>
                </div>
                <div class="line <?= $step_class[2] ?>"></div>
                <div class="circle <?= $step_class[2] ?>">
                    <i class="fa-solid fa-circle-check"
                        style="color: <?= ($step_class[2]) ? '#0FBE19' : '#ddd'; ?>;"></i>
                    <span>เสร็จสิ้นแล้ว</span>
                </div>
            </div>

            <div class="details">
                <div class="order-info">
                    <span><strong><?= date("d M Y, H:i", strtotime($order["created_at"])); ?></strong></span>
                    <span class="order-right"><strong>Order : <?= $order["orders_status_id"]; ?></strong></span>
                </div>
                <span style="display: inline-flex;align-items: center;margin-bottom: 10px;">
                    <i class="fa-solid fa-circle-user" style="margin-right: 5px;"></i>
                    <strong><?= htmlspecialchars($phone); ?></strong>
                </span>
                <hr>
                <ul>
                    <span><strong class="order-list">รายการคำสั่งซื้อ</strong></span>
                    <?php foreach ($order_items as $item): ?>
                    <li style="display: flex; justify-content: space-between;margin-top: 20px;">
                        <span style="width: 50%;"><?= htmlspecialchars($item['product_name']); ?></span>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; width: 25%;">
                            <span><?= number_format($item['subtotal'], 2); ?>฿</span><br>
                            <span>x<?= $item['quantity']; ?></span>
                        </div>
                    </li>
                    <span>ตัวเลือกพิเศษ : <span>
                            <span><?php echo (empty($item['options']) || $item['options'] === null) ? '-' : $item['options']; ?></span>
                        </span></span><br>
                    <span style="color:#e1e1e1;">หมายเหตุ :
                        <?php echo $item['notes'] ? $item['notes'] : '-'; ?></span>
                    <?php endforeach; ?>
                </ul>

            </div>
        </div>
        <div class="details-bottom">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin-bottom: 0px;"><strong>ยอดชำระ</strong></h2>
                <h2 style="color: red; margin-bottom: 0px;">
                    <strong><?= number_format($order["total_price"], 2); ?>฿</strong>
                </h2>
            </div>
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom:20px;margin-top:20px">
                <p><strong>วิธีการชำระ</strong></p>
                <p><?= $order["payment_method"]; ?></p>
            </div>
            <hr>
            <br>
            <a href="user_main.php" class="reorder-button">สั่งซื้ออีกครั้ง</a>
        </div>
    </div>

    <script>
    function updateStatus() {
        const orderId = <?= $order_id ?>;
        const statusDiv = document.querySelector(".step");

        fetch(`update_status_order_user.php?orders_status_id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                const statusOrder = data.status_order;
                const stepClass = [
                    (statusOrder === 'receive' || statusOrder === 'prepare' || statusOrder === 'complete') ? 'active' : '',
                    (statusOrder === 'prepare' || statusOrder === 'complete') ? 'active' : '',
                    (statusOrder === 'complete') ? 'active' : ''
                ];

                const circles = statusDiv.querySelectorAll('.circle');
                const lines = statusDiv.querySelectorAll('.line');

                circles.forEach((circle, index) => {
                    const icon = circle.querySelector('i');
                    icon.style.color = stepClass[index] === 'active' ? '#0FBE19' : '#ddd';
                });

                lines.forEach((line, index) => {
                    line.style.borderTop = stepClass[index] === 'active' ? '5px dotted #0FBE19' : '5px dotted #ddd';
                });
            })
            .catch(error => console.error('Error fetching order status:', error));
    }
    setInterval(updateStatus, 1000);
</script>

</body>

</html>