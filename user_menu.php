<?php
session_start();
include 'db.php';

if (!isset($_GET['product_id'])) {
    die("ไม่พบสินค้านี้");
}

$product_id = intval($_GET['product_id']);

$sql = "SELECT product_name, price, image_url, options, extra_cost FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    die("สินค้านี้ไม่มีอยู่ในระบบ");
}

$product['price'] = isset($product['price']) ? floatval($product['price']) : 0.00;
$options = (!empty($product['options']) && is_string($product['options'])) ? json_decode($product['options'], true) : [];
$extra_costs = (!empty($product['extra_cost']) && is_string($product['extra_cost'])) ? json_decode($product['extra_cost'], true) : [];

if ($options === null || $options === [""] || empty($options)) {
    $options = [];
}
if ($extra_costs === null || $extra_costs === [""] || empty($extra_costs)) {
    $extra_costs = [];
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เมนูอาหาร</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #fff;
        display: flex;
        flex-direction: column;
        height: 100vh;
    }

    .top-tab {
        width: -webkit-fill-available;
        padding: 20px;
        background-color: #FDDF59;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
    }

    .food-img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        margin-top: 3.5rem;
    }

    .food-details {
        padding: 2rem;
    }

    .food-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 18px;
        font-weight: bold;
    }

    .option {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 0;
        border-bottom: 1px solid #ddd;
        font-size: 16px;
        color: black;
    }

    .option label {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-left: 1rem;
    }

    .input-box {
        width: 95%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-top: 5px;
        font-size: 14px;
    }

    .quantity-selector {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 1rem;
    }

    .qty-btn {
        width: 30px;
        height: 30px;
        border: 1px solid #ddd;
        background: #fff;
        font-size: 16px;
        cursor: pointer;
        border-radius: 5px;
        color: #FFDE59;
    }

    .qty-value {
        padding: 0 15px;
        font-size: 16px;
        font-weight: bold;
    }

    .btn-bottom {
        margin-top: auto;
        /* ผลักดันไปที่ด้านล่างสุด */
        width: 90%;
        padding: 1rem 1rem 0;
        font-size: 16px;
        font-weight: bold;
        border-radius: 30px;
        cursor: pointer;
        text-align: center;
    }

    .add-to-cart {
        width: 100%;
        background-color: #FFDE59;
        border: none;
        padding: 15px;
        font-size: 16px;
        font-weight: bold;
        border-radius: 30px;
        cursor: pointer;
        text-align: center;
    }

    .food-choice {
        margin-top: 1rem;
        font-size: 16px;
    }

    .food-note {
        margin-top: 1rem;
        font-size: 16px;
        margin-bottom: 1rem;
    }

    .container {
        flex-grow: 1;
        padding-bottom: 20px;
    }
    </style>
</head>

<body>
    <div class="top-tab">
        <i class="fa-solid fa-arrow-left" onclick="window.history.back();"></i>
    </div>

    <div class="container">
        <img src="<?php echo htmlspecialchars($product['image_url']); ?>"
            alt="<?php echo htmlspecialchars($product['product_name']); ?>" class="food-img">

        <div class="food-details">
            <div class="food-header">
                <span><?php echo htmlspecialchars($product['product_name']); ?></span>
                <span><?php echo number_format($product['price'], 2); ?>฿</span>
            </div>

            <?php if (!empty($options) && $options !== [""]) : ?>
            <div class="food-choice">
                <span><strong>ตัวเลือก</strong></span>
                <span>ไม่จำเป็นต้องระบุ</span>
            </div>

            <?php foreach ($options as $index => $option) : ?>
            <div class="option">
                <label>
                    <input type="radio" name="option" value="<?php echo htmlspecialchars($option); ?>"
                        data-extra-cost="<?php echo isset($extra_costs[$index]) ? floatval($extra_costs[$index]) : 0; ?>">
                    <?php echo htmlspecialchars($option); ?>
                </label>
                <span>
                    <?php echo isset($extra_costs[$index]) && $extra_costs[$index] !== "" ? "+ " . number_format(floatval($extra_costs[$index]), 2) . "฿" : ""; ?>
                </span>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>

            <div class="food-note">
                <span><strong>หมายเหตุถึงร้าน</strong></span>
                <span>ไม่จำเป็นต้องระบุ</span>
            </div>
            <textarea class="input-box" id="notes" placeholder="ระบุรายละเอียดเพิ่มเติม"></textarea>
        </div>

        <div class="btn-bottom">
            <div class="quantity-selector">
                <button class="qty-btn" id="decrease">-</button>
                <span class="qty-value" id="quantity">1</span>
                <button class="qty-btn" id="increase">+</button>
            </div>
            <form id="cart-form" method="POST" action="add_to_cart_db.php">
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="extra_cost" id="extra_cost" value="0">
                <input type="hidden" name="options" id="selected_options" value="">
                <input type="hidden" name="quantity" id="quantity_input" value="1">
                <input type="hidden" name="notes" id="notes_input" value="">

                <button type="submit" class="add-to-cart">
                    เพิ่มไปยังตะกร้า - <?php echo number_format(floatval($product['price']), 2); ?>฿
                </button>
            </form>
        </div>
    </div>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        let quantity = 1;
        const minQuantity = 1;
        const maxQuantity = 99;
        const quantityDisplay = document.getElementById("quantity");
        const quantityInput = document.getElementById("quantity_input");
        const optionsInput = document.getElementById("selected_options");
        const increaseBtn = document.getElementById("increase");
        const decreaseBtn = document.getElementById("decrease");
        const addToCartBtn = document.querySelector(".add-to-cart");
        const productPrice = <?php echo floatval($product['price']); ?>;
        let extraCost = 0;

        function updateDisplay() {
            let totalPrice = (productPrice + extraCost) * quantity;
            quantityDisplay.textContent = quantity;
            quantityInput.value = quantity;
            document.getElementById("extra_cost").value = extraCost;
            addToCartBtn.textContent = `เพิ่มไปยังตะกร้า - ${totalPrice.toFixed(2)}฿`;
        }

        function updateOptions() {
            let selectedOption = document.querySelector('input[name="option"]:checked');
            if (selectedOption) {
                optionsInput.value = selectedOption.value;
                extraCost = parseFloat(selectedOption.getAttribute("data-extra-cost")) ||
                    0;
            } else {
                optionsInput.value = "";
                extraCost = 0;
            }
            updateDisplay();
        }

        increaseBtn.addEventListener("click", function() {
            if (quantity < maxQuantity) {
                quantity++;
                updateDisplay();
            }
        });

        decreaseBtn.addEventListener("click", function() {
            if (quantity > minQuantity) {
                quantity--;
                updateDisplay();
            }
        });

        document.querySelectorAll('input[name="option"]').forEach(option => {
            option.addEventListener("change", function() {
                updateOptions();
            });
        });

        const notesTextarea = document.getElementById("notes");
        const notesInput = document.getElementById("notes_input");

        notesTextarea.addEventListener("input", function() {
            notesInput.value = notesTextarea.value;
        });

        updateDisplay();
    });
    </script>

</body>

</html>