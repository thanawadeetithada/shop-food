<?php
session_start();
include 'db.php'; // นำเข้าไฟล์ db.php

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่ (ต้องมี user_id)
if (!isset($_SESSION['user_id'])) {
    die("กรุณาเข้าสู่ระบบ");
}

$user_id = $_SESSION['user_id']; // ดึง user_id จาก session

// ดึงข้อมูล order ล่าสุดของ user พร้อม product_name
$sql = "SELECT o.cart_order_id, o.total_price, o.payment_method, o.store_id, 
               oi.cart_order_item_id, oi.product_id, oi.quantity, oi.extra_cost, oi.subtotal, oi.notes, 
               p.product_name
        FROM cart_orders o 
        JOIN cart_order_items oi ON o.cart_order_id = oi.cart_order_id
        JOIN products p ON oi.product_id = p.product_id
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_orders = [];

while ($row = $result->fetch_assoc()) {
    $cart_orders[$row['cart_order_id']]['total_price'] = $row['total_price'];
    $cart_orders[$row['cart_order_id']]['payment_method'] = $row['payment_method'];
    $cart_orders[$row['cart_order_id']]['items'][] = [
        'cart_order_item_id' => $row['cart_order_item_id'],
        'product_id' => $row['product_id'],
        'product_name' => $row['product_name'],
        'quantity' => $row['quantity'],
        'subtotal' => $row['subtotal'],
        'notes' => $row['notes'],
        'extra_cost' => $row['extra_cost']
    ];
}

$sql = "SELECT store_id FROM cart_orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $store_id = $row['store_id'];
} else {
    $store_id = null;
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายการอาหารที่สั่ง</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: white;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: flex-start;
        align-items: flex-start;
        height: 100vh;
        overflow: hidden;
    }

    .order-container {
        background: white;
        padding: 20px;
        width: 100%;
        max-width: 400px;
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        padding-bottom: 100px;
    }

    .order-item {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        background: white;
        padding: 10px;
        border-radius: 10px;
        box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
        margin-bottom: 15px;
        margin-top: 1rem;
        gap: 10px;
    }

    .order-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .food-name {
        font-size: 16px;
        font-weight: bold;
    }

    .note {
        font-size: 14px;
        color: gray;
    }

    /* Quantity Controls */
    .quantity {
        display: flex;
        align-items: center;
        background: #FFDE59;
        padding: 2px 2px;
        border-radius: 20px;
    }

    .qty-btn {
        background: none;
        border: none;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        color: #333;
    }

    .qty-value {
        margin: 0 10px;
        font-weight: bold;
        font-size: 16px;
    }

    /* Price */
    .price {
        font-size: 16px;
        font-weight: bold;
        margin: 10px;
    }

    /* Delete Icon */
    .delete-icon {
        color: red;
        font-size: 18px;
        cursor: pointer;
    }

    .header {
        margin-top: 4rem;
        color: #333;
        padding: 10px;
        font-size: 1.5em;
        text-align: center;
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

    .details-bottom {
        margin-top: auto;
        width: 100%;
        background-color: #fff;
        padding: 10px 0 40px;
        text-align: center;
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
    </style>
</head>

<body>
    <div class="top-tab">
        <i class="fa-solid fa-arrow-left"
            onclick="window.location.href='<?php echo isset($store_id) && $store_id ? 'user_detail_shop.php?store_id=' . (int) $store_id : 'javascript:history.back()'; ?>';"
            style="cursor: pointer;"></i>
    </div>


    <div class="order-container">
        <div class="header">รายการที่สั่งอาหาร</div>

        <?php if (!empty($cart_orders)) : ?>
        <?php foreach ($cart_orders as $cart_order_id => $order) : ?>
        <?php foreach ($order['items'] as $item) : ?>
        <div class="order-item">
            <div class="order-details">
                <p class="food-name"><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></p>
                <p class="note" title="<?php echo htmlspecialchars($item['notes']); ?>">
                    หมายเหตุ :
                    <?php echo (!empty($item['notes'])) 
        ? htmlspecialchars(mb_strimwidth($item['notes'], 0, 15, "...")) 
        : "-"; ?>
                </p>
            </div>

            <div class="order-actions">
                <div class="quantity">
                    <button class="qty-btn decrease"
                        data-order-item-id="<?php echo $item['cart_order_item_id']; ?>">-</button>
                    <span class="qty-value"
                        id="qty-<?php echo $item['cart_order_item_id']; ?>"><?php echo $item['quantity']; ?></span>
                    <button class="qty-btn increase"
                        data-order-item-id="<?php echo $item['cart_order_item_id']; ?>">+</button>
                </div>
                <p class="price" id="subtotal-<?php echo $item['cart_order_item_id']; ?>">
                    <?php echo number_format($item['subtotal']* $item['quantity'], 2); ?>.-</p>
                <i class="fa-solid fa-trash delete-icon"
                    data-order-item-id="<?php echo htmlspecialchars($item['cart_order_item_id']); ?>"></i>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="details-bottom">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin-bottom: 0px;"><strong>ชำระเงินโดย</strong></h2>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <p><?php echo $order['payment_method']; ?></p>
            </div>
            <hr>
            <br>
            <a href="user_payment.php?cart_order_id=<?php echo $cart_order_id; ?>&total_price=<?php echo number_format($order['total_price'], 2, '.', ''); ?>"
                class="reorder-button">
                ยืนยันคำสั่งซื้อ <?php echo number_format($order['total_price'], 2); ?>.-
            </a>
        </div>
        <?php endforeach; ?>
        <?php else : ?>
        <p style="text-align: center;">ไม่มีรายการอาหารที่สั่ง</p>
        <?php endif; ?>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll(".delete-icon").forEach(icon => {
            icon.addEventListener("click", function() {
                let orderItemId = this.getAttribute("data-order-item-id");

                if (confirm("คุณต้องการลบรายการนี้หรือไม่?")) {
                    fetch("delete_order_item_db.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/x-www-form-urlencoded"
                            },
                            body: "cart_order_item_id=" + encodeURIComponent(orderItemId)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert("ลบข้อมูลสำเร็จ");
                                location.reload();
                            } else {
                                alert("เกิดข้อผิดพลาด: " + data.message);
                            }
                        })
                        .catch(error => console.error("Error:", error));
                }
            });
        });

        document.querySelectorAll(".qty-btn").forEach(button => {
            button.addEventListener("click", function() {
                let orderItemId = this.getAttribute("data-order-item-id");
                let action = this.classList.contains("increase") ? "increase" : "decrease";

                fetch("update_quantity_db.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `cart_order_item_id=${encodeURIComponent(orderItemId)}&action=${encodeURIComponent(action)}`
                    })
                    .then(response => {
                        if (!response.ok) {
                        
                            throw new Error('Response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            console.log('data',data)
                            document.getElementById(`qty-${orderItemId}`).textContent = data
                                .new_quantity;
                            document.getElementById(`subtotal-${orderItemId}`).textContent =
                                `${data.new_subtotal}.-`;
                            document.querySelector(".reorder-button").textContent =
                                `ยืนยันคำสั่งซื้อ ${data.new_total_price}.-`;
                        } else if (data.new_quantity = 1){
                            alert("สินค้าจำนวน 1 ชิ้น กรุณากดลบ");
                        } else {
                            alert("เกิดข้อผิดพลาด: " + data.message);
                        }
                    })
                    .catch(error => console.error("Error:", error));
            });
        });

    });
    </script>

</body>

</html>