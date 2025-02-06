<?php
session_start();
include "db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['store_id'])) {
    $_SESSION['store_id'] = $_GET['store_id']; // เก็บค่า store_id ไว้ใน session
    $store_id = $_GET['store_id'];
} elseif (isset($_SESSION['store_id'])) {
    $store_id = $_SESSION['store_id'];
} else {
    // กรณีไม่มี store_id
    echo "ไม่พบร้านค้า";
    exit(); // ไม่ให้ทำการดึงข้อมูลร้านค้าต่อ
}

$user_id = $_SESSION['user_id'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
$store_id = isset($_GET['store_id']) ? $_GET['store_id'] : null;
$order_count = isset($order_count) ? $order_count : 0;
$preparing_count = isset($preparing_count) ? $preparing_count : 0;
$completed_count = isset($completed_count) ? $completed_count : 0;

if (isset($_GET['store_id'])) {
    $_SESSION['store_id'] = $_GET['store_id'];
    $store_id = $_GET['store_id'];
} elseif (isset($_SESSION['store_id'])) {
    $store_id = $_SESSION['store_id'];
} else {
    echo "ไม่พบร้านค้า";
    exit();
}


if ($role === 'admin') {
    $sql = "SELECT store_name, user_name, store_id FROM stores WHERE store_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($store_name, $user_name, $store_id);
    $stmt->fetch();
    $stmt->close();
} else {
    $sql = "SELECT store_name, user_name, store_id FROM stores WHERE user_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($store_name, $user_name, $store_id);
    $stmt->fetch();
    $stmt->close();
}

$sql = "SELECT store_name, user_name, store_id FROM stores WHERE store_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $store_id);
$stmt->execute();
$stmt->bind_result($store_name, $user_name, $store_id);
$stmt->fetch();
$stmt->close();

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;


if ($startDate && $endDate) {
    $sql = "
        SELECT SUM(total_price) AS total_sales_all
        FROM orders_status 
        WHERE store_id = ? AND status_order = 'complete' 
        AND created_at BETWEEN ? AND ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $store_id, $startDate, $endDate);
} else {
    $sql = "
        SELECT SUM(total_price) AS total_sales_all
        FROM orders_status 
        WHERE store_id = ? AND status_order = 'complete'
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $store_id);
}

$stmt->execute();
$stmt->bind_result($total_sales_all);
$stmt->fetch();
$stmt->close();

$status_order = isset($_GET['status_order']) ? $_GET['status_order'] : '';

$sql = "
    SELECT oi.product_id, p.product_name, SUM(oi.subtotal) AS total_sales
    FROM orders_status os
    JOIN orders_status_items oi ON os.orders_status_id = oi.orders_status_id
    JOIN products p ON oi.product_id = p.product_id
    WHERE os.store_id = ? AND os.status_order = 'complete'
    GROUP BY oi.product_id
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $store_id);
$stmt->execute();
$stmt->bind_result($product_id, $product_name, $total_sales);
$product_sales = [];

while ($stmt->fetch()) {
    $product_sales[] = ['product_id' => $product_id, 'product_name' => $product_name, 'total_sales' => $total_sales];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <style>
    body {
        background-color: #f8f9fa;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .container {
        margin-top: 5rem;
        padding: 0 20px;
        text-align: center;
        font-weight: bold;
        flex: 1;
    }

    .summary-box {
        width: fit-content;
        text-align: center;
        background: white;
        padding: 20px 25px;
        border-radius: 10px;
        box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 5px;
    }

    .prepare {
        margin-left: 20px;
    }

    .complete {
        margin-left: 8px;
    }

    .bottom-menu {
        position: fixed;
        bottom: 0;
        width: 100%;
        background: #FFD700;
        display: flex;
        justify-content: space-around;
        padding: 10px 0;
    }

    .bottom-menu a {
        text-decoration: none;
        color: black;
        font-size: 18px;
    }

    .top-tab {
        width: 100%;
        padding: 15px;
        background-color: #FDDF59;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }

    .top-tab a {
        text-decoration: none;
    }

    .top-tab svg {
        cursor: pointer;
        font-size: 1.8rem;
        color: #ffffff;
        background-color: #ccc;
        border-radius: 15px;
    }

    button {
        background-color: #0448A9;
        border: 0px;
        padding: 0.4rem;
        border-radius: 5px;
        color: white;
        font-size: 14px;
        margin-bottom: 0.5rem;
    }

    .fa-angle-down {
        margin-left: 5px;
        margin-top: 5px;
    }

    .fa-chevron-right {
        margin-top: 5px;
    }

    .total-sell p {
        font-size: 12px;
        margin: 0;
    }

    .total-sell {
        padding: 10px;
        border-radius: 10px;
        box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 5px;
        width: fit-content;
    }


    /* Footer Section */
    .footer {
        align-items: center;
        display: flex;
        justify-content: space-around;
        background-color: #fff;
        padding: 5px 0;
        margin-left: 20px;
        margin-top: 20px;
        margin-bottom: 20px;
        width: 90%;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 100px;
    }

    .footer-item {
        text-align: center;
        color: #FFDE59;
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
        background-color: #FFDE59;
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

    .store-id {
        font-size: 1rem;
        margin: 0;
        font-weight: 400;
        color: black;
    }

    .col-4 {
        display: flex;
        justify-content: center;
    }

    .edit-buttons {
        background-color: #0448A9;
        border: 0px;
        padding: 0.4rem;
        border-radius: 5px;
        color: white !important;
        width: fit-content;
        margin-top: 10px;
        cursor: pointer;
        font-size: 1rem;
    }

    .col.text-end {
        display: flex;
        justify-content: flex-end;
    }

    .admin-icon svg {
        display: none;

    }

    .admin-icon {
        padding: 30px;
    }
    </style>
</head>

<body>
    <div class="top-tab <?php echo $role === 'admin' ? 'admin-icon' : ''; ?>">
        <a href="logout.php">
            <i class="fa-solid fa-circle-user"></i>
        </a>
    </div>

    <div class="container">
        <div class="row align-items-center">
            <div class="col text-start">
                <h4><?php echo htmlspecialchars($store_name, ENT_QUOTES, 'UTF-8'); ?></h4>
            </div>
            <div class="col text-end">
                <h5><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></h5>
            </div>
        </div>

        <div class="row align-items-center">
            <div class="col text-start">
                <p class="store-id">Store_ID <?php echo htmlspecialchars($store_id, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="col text-end">
                <?php if ($role === 'admin'): ?>
                <a href="admin_edit_shop.php?store_id=<?php echo $store_id; ?>"
                    style="text-decoration: none; color: inherit;">
                    <div class="edit-buttons">แก้ไขข้อมูล</div>
                </a>

                <?php endif; ?>
            </div>
        </div>

        <div class="row align-items-center">
            <div class="col text-start mt-1">
                <h6>สถานะคำสั่งซื้อ</h6>
            </div>
            <div class="col text-end">
                <a href="shop_order.php" style="text-decoration: none; color: inherit;">
                    <h6>ดูประวัติการขาย <i class="fa-solid fa-chevron-right"></i></h6>
                </a>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-4">
                <a href="shop_order.php" style="text-decoration: none; color: inherit;">
                    <div class="summary-box">
                        <span class="order-count"><?php echo $order_count; ?></span>
                    </div>
                    <h6>ออเดอร์</h6>
                </a>
            </div>
            <div class="col-4 d-flex justify-content-center align-items-center">
                <a href="shop_order_prepare.php" style="text-decoration: none; color: inherit;">
                    <div class="summary-box prepare">
                        <span class="preparing-count"><?php echo $preparing_count; ?></span>
                    </div>
                    <h6>ที่ต้องจัดเตรียม</h6>
                </a>
            </div>
            <div class="col-4 d-flex justify-content-center align-items-center">
                <a href="shop_order_complete.php" style="text-decoration: none; color: inherit;">
                    <div class="summary-box complete">
                        <span class="completed-count"><?php echo $completed_count; ?></span>
                    </div>
                    <h6>เสร็จสิ้นแล้ว</h6>
                </a>
            </div>
        </div>

        <hr>

        <div class="row align-items-center">
            <div class="col text-start">
                <span style="font-size: 22px;font-weight: bold;">สรุปยอดขาย</span>
            </div>
            <div class="col text-end">
                <button id="date-range-picker-btn" class="btn btn-light">
                    <span style="font-size: 22px;font-weight: bold;">ระยะเวลา </span>
                    <i class="fa-solid fa-chevron-down"></i></button>
                <input type="text" id="date-range-picker" value="" class="form-control" style="display: none;" />
            </div>
        </div>
        <br>
        <div class="total-sell">
            <p>ยอดขาย (฿) </p>
            <p id="total-sales">0.00</p>
        </div>

        <div class="row mt-3">
            <canvas id="salesChart"></canvas>
        </div>
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
        <div class="footer-item " onclick="window.location.href='shop_notification.php'">
            <i class="fa-solid fa-bell"></i>
            <span class="notification-badge"></span>
        </div>
        <div class="footer-item" onclick="window.location.href='shop_all_product.php'">
            <i class="fa-regular fa-folder-open"></i>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
    $(document).ready(function() {
        var totalSalesAll = <?php echo is_numeric($total_sales_all) ? $total_sales_all : 0; ?>;
        document.getElementById('total-sales').textContent = parseFloat(totalSalesAll).toFixed(2);

        // เริ่มต้นการตั้งค่าของ daterangepicker
        $('#date-range-picker').daterangepicker({
            opens: 'right',
            locale: {
                format: 'YYYY-MM-DD'
            }
        }, function(start, end, label) {
            var startDate = start.format('YYYY-MM-DD');
            var endDate = end.format('YYYY-MM-DD');

            console.log('Selected Date Range:', startDate, endDate);

            fetchSalesData(startDate, endDate);
        });

        $('#date-range-picker-btn').on('click', function() {
            var daterangepicker = $('#date-range-picker').data('daterangepicker');
            daterangepicker.toggle();

            if ($('.daterangepicker').is(':visible')) {}
        });

        $('#date-range-picker').on('apply.daterangepicker', function(ev, picker) {
            if ($('.daterangepicker').is(':visible')) {}
        });
    });

    var store_id = <?php echo $store_id; ?>;

    function fetchSalesData(startDate, endDate) {
        console.log(
            `Requesting sales data with parameters: store_id=${store_id}, start_date=${startDate}, end_date=${endDate}`
        );

        fetch(`get_sales_data.php?store_id=${store_id}&start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                console.log('Response data:', data);

                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }

                let totalSalesAll = data.total_sales_all;

                if (totalSalesAll === null || totalSalesAll === undefined || isNaN(totalSalesAll)) {
                    totalSalesAll = 0;
                } else {
                    totalSalesAll = parseFloat(totalSalesAll);
                }

                console.log('Total Sales:', totalSalesAll);
                document.getElementById('total-sales').textContent = totalSalesAll.toFixed(2);

                const productSales = data.product_sales;
                updateSalesChart(productSales);
            })
            .catch(error => {
                console.error('Error fetching sales data:', error);
            });
    }

    function updateSalesChart(data) {
        var productNames = data.map(item => item.product_name);
        var salesData = data.map(item => item.total_sales);

        salesChart.data.labels = productNames;
        salesChart.data.datasets[0].data = salesData;
        salesChart.update();
    }

    var productSales = <?php echo json_encode($product_sales); ?>;
    var productNames = productSales.map(function(item) {
        return item.product_name;
    });
    var salesData = productSales.map(function(item) {
        return item.total_sales;
    });
    var ctx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: productNames,
            datasets: [{
                label: 'ยอดขาย (บาท)',
                data: salesData,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                fill: true
            }]
        },
        options: {
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'สินค้า'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'ยอดขาย (฿)'
                    }
                }
            }
        }
    });


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

    function fetchOrderStatus() {
        fetch('get_order_status.php')
            .then(response => response.json())
            .then(data => {
                document.querySelector('.order-count').textContent = data.order_count || 0;
                document.querySelector('.preparing-count').textContent = data.preparing_count || 0;
                document.querySelector('.completed-count').textContent = data.completed_count || 0;
            })
            .catch(error => console.error('Error fetching order status:', error));
    }

    setInterval(fetchOrderStatus, 5000);
    fetchOrderStatus();
    </script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

</body>

</html>