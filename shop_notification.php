<?php
session_start();
include 'db.php'; 

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $store_id = $_SESSION['store_id'];
} else {
    header("Location: index.php");
    exit;
}

// ตั้งค่า notification เป็น 0 สำหรับคำสั่งซื้อทั้งหมดของร้าน
$sqlUpdateNotification = "UPDATE orders_status SET notification = 0 WHERE store_id = ?";
$stmtUpdateNotification = $conn->prepare($sqlUpdateNotification);
$stmtUpdateNotification->bind_param("i", $store_id);
$stmtUpdateNotification->execute();

$sql = "SELECT orders_status_id, status, total_price, user_id, store_id, created_at 
        FROM orders_status 
        WHERE store_id = ? 
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $store_id);  // "i" indicates that the parameter is an integer
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
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        /* ทำให้ body ครอบคลุมพื้นที่ทั้งหมด */
    }


    .container {
        display: flex;
        flex-direction: column;
        flex: 1;
        /* ให้ container ขยายเต็มพื้นที่ที่เหลือ */
    }

    header {
        padding: 1rem 1rem 0rem 1.8rem;
        font-size: 1.2rem;
        font-weight: bold;
        color: #000;
        margin-top: 4rem;
    }

    main {
        flex-grow: 1;
        /* ทำให้ main content ขยายเต็มพื้นที่ที่เหลือ */
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
        color: #4caf50;
        margin-left: 1rem;
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

    .footer {
        display: flex;
        justify-content: space-around;
        align-items: center;
        background-color: #fff;
        padding: 5px 0;
        width: 90%;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 100px;
        margin: 20px;
        margin-top: 20px;
        /* ดัน footer ไปที่ด้านล่างสุด */
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
                    $status = $row['status'];
                    $totalPrice = $row['total_price'];
                    $userId = $row['user_id'];
                    $storeId = $row['store_id'];

                    $sqlUser = "SELECT phone FROM users WHERE user_id = '$userId'";
                    $resultUser = $conn->query($sqlUser);
                    $phone = "ไม่ระบุ";
                    if ($resultUser->num_rows > 0) {
                        $rowUser = $resultUser->fetch_assoc();
                        $phone = $rowUser['phone'];
                    }

                    if ($status == "Paid") {
                        $statusText = "ชำระแล้ว";
                    } else {
                        $statusText = $status;
                    }

                    echo "
                    <a href='shop_order_status.php?orders_status_id={$orderId}'>
                        <div class='status-item'>
                            <div class='icon'>
                                <i class='fa-solid fa-utensils'></i>
                            </div>
                            <div class='details'>
                                <span class='order'>
                                    <i class='fa-solid fa-circle-user'></i>&nbsp;&nbsp;<strong>{$phone}</strong>
                                </span>&nbsp;&nbsp;
                                <span class='order'>
                                    <strong>Order : {$orderId}</strong>
                                </span>
                                <br>
                                &nbsp;&nbsp;<span class='status'>{$statusText}</span>
                                <span class='price'>{$totalPrice}฿</span>
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
        <div class="footer-item active"
            onclick="window.location.href='<?php echo ($_SESSION['role'] == 'admin') ? 'update_shopid_byAdminBack.php' : 'shop_main.php'; ?>'">
            <i class="fa-solid fa-house-chimney"></i>&nbsp;
            <p>HOME</p>
        </div>
        <div class="footer-item" onclick="window.location.href='shop_order.php'">
            <i class="fa-solid fa-file-alt"></i>
        </div>
        <div class="footer-item active" onclick="window.location.href='shop_notification.php'">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-badge"></span>
        </div>
        <div class="footer-item" onclick="window.location.href='shop_all_product.php'">
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