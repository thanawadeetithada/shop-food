<?php
session_start();
include 'db.php'; 

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sqlUpdate = "UPDATE orders_status SET notification = 0 WHERE user_id = ? AND notification = 1";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("i", $user_id);
    $stmtUpdate->execute();
} else {
    header("Location: index.php");
    exit;
}

$sql = "SELECT os.orders_status_id, os.status, os.total_price, os.user_id, os.store_id, os.created_at, os.status_order, s.store_name
        FROM orders_status os
        LEFT JOIN stores s ON os.store_id = s.store_id
        WHERE os.user_id = ? AND os.status_order = 'complete'
        ORDER BY os.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <title>แจ้งเตือน</title>
    <style>
  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    text-decoration: none;
}

body {
    font-family: Arial, sans-serif;
    background-color: #fff;
    height: 100vh;
    display: flex;
    flex-direction: column;
    margin-bottom: 20px;
}
.container {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}
header {
    padding: 1rem 1rem 0rem 1.8rem;
    font-size: 1.2rem;
    font-weight: bold;
    color: #000;
    margin-top: 4rem;
}

main {
    flex: 1;
    overflow-y: auto;
    padding: 0 1rem;
}


    .status-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1rem;

    }

    .icon {
        font-size: 1.8rem;
        margin-right: 0.5rem;
        color: black;
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
    }

    .details .status {
        font-size: 16px;
        margin-right: 1rem;
    }

    .price {
        font-size: 0.9rem;
        font-weight: bold;
        color: #ff5722;
    }

    .dot {
        width: 8px;
        height: 8px;
        background-color: red;
        border-radius: 50%;
    }

    nav {
        display: flex;
        justify-content: space-around;
    }

    .nav-item {
        text-decoration: none;
        color: #000;
        font-size: 0.9rem;
        padding: 0.5rem;
    }

    .nav-item.active {
        color: #ff5722;
        font-weight: bold;
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
        padding: 0 0 0 30px;
        font-size: 1.5em;
    }

    .price {
        color: orange;
        font-size: 16px;
    }

    /* Footer Section */
    .footer {
    display: flex;
    justify-content: space-around;
    background-color: #fff;
    padding: 5px 0;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 100px;
    margin: 20px;
}

    .footer-item {
        text-align: center;
        color: #FFDE59;
        font-size: 1.5rem;
        position: relative;
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    .footer-item p {
        font-size: 0.9rem;
        font-weight: bold;
        margin: 5px 0 0;
    }

    .footer-item.active {
        background-color: #FFDE59;
        border-radius: 100px;
        padding: 10px 20px;
        color: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);

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

    .status-receive {
        color: #4caf50;
    }

    .status-prepare {
        color: orange;
    }

    .status-complete {
        color: #4caf50;
    }

    .status-pending {
        color: red;
    }
    </style>
</head>

<body>
    <div class="top-tab"></div>
    <div class="container">
        <div class="header">แจ้งเตือน</div>
        <br>
        <main>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $orderId = $row['orders_status_id'];
                    $status = $row['status_order'];
                    $totalPrice = $row['total_price'];
                    $userId = $row['user_id'];
                    $storeId = $row['store_id'];
                    $storeName = $row['store_name']; 


                    if ($status === 'receive') {
                        $status_display = 'รับออเดอร์';
                        $status_class = 'status-receive'; 
                    } elseif ($status === 'prepare') {
                        $status_display = 'กำลังเตรียม';
                        $status_class = 'status-prepare';
                    } elseif ($status === 'complete') {  
                        $status_display = 'เสร็จสิ้นนแล้ว'; 
                        $status_class = 'status-complete';
                    } else {
                        $status_display = 'รับออเดอร์';
                        $status_class = 'status-receive';
                    }

                    echo "
                    <a href='user_order_status.php?orders_status_id={$orderId}'>
                        <div class='status-item'>
                            <div class='icon'>
                                <i class='fa-solid fa-utensils'></i>
                            </div>
                            <div class='details'>
                                <span class='order'>
                                      <strong>Order : {$orderId}</strong>
                                </span>&nbsp;&nbsp;
                                <span class='order'>
                                    <strong>{$storeName}</strong>
                                </span>
                                <br>
                                <span class='status {$status_class}'>{$status_display}</span>
                            </div>              
                        </div>
                    </a>
                    <hr>
                    ";
                }
            } else {
                echo "<p style='margin-left: 1rem;'>ไม่มีคำสั่งซื้อ</p>";
            }
            ?>
        </main>
    </div>

    <div class="footer">
        <div class="footer-item " onclick="window.location.href='user_main.php'">
            <i class="fa-solid fa-house-chimney"></i>&nbsp;
            <p>HOME</p>
        </div>
        <div class="footer-item" onclick="window.location.href='user_order.php'">
            <i class="fa-solid fa-file-alt"></i>
        </div>
        <div class="footer-item" onclick="window.location.href='user_cart.php'">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
        <div class="footer-item active notification" onclick="window.location.href='user_notification.php'">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-badge"></span>
        </div>
        </div>
    <script>
     function fetchNotifications() {
        fetch('get_notifications_user.php')
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