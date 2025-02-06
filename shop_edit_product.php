<?php
session_start();
require 'db.php';

if (!isset($_GET['product_id'])) {
    die("ไม่พบสินค้าที่ต้องการแก้ไข");
}

$product_id = $_GET['product_id'];
$user_id = $_SESSION['user_id'] ?? null;


$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    die("ไม่พบสินค้านี้ในฐานข้อมูล");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $notes = $_POST['notes'] ?? '';

    $options = isset($_POST['option']) ? json_encode($_POST['option']) : json_encode([]);
    $extra_costs = isset($_POST['extra_cost']) ? json_encode($_POST['extra_cost']) : json_encode([]);


    $target_dir = "uploads/";
    $image_url = $product['image_url'];

    if (!empty($_FILES["product_image"]["name"])) {
        $image_file = $target_dir . basename($_FILES["product_image"]["name"]);
        move_uploaded_file($_FILES["product_image"]["tmp_name"], $image_file);
        $image_url = $image_file;
    }

    $stmt = $conn->prepare("UPDATE products SET product_name = ?, price = ?, options = ?, extra_cost = ?, image_url = ?, notes = ? WHERE product_id = ?");
    $stmt->bind_param("sdssssi", $product_name, $price, $options, $extra_costs, $image_url, $notes, $product_id);

    if ($stmt->execute()) {
        echo "บันทึกการแก้ไขสำเร็จ!";
        header("Location: shop_all_product.php"); // กลับไปที่หน้ารายการสินค้า
        exit();
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสินค้า</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        color: black;
        text-decoration: none;
    }

    body {
        font-family: 'Sarabun', sans-serif !important;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background-color: #fff;
    }

    .login-container {
        display: flex;
        align-items: center;
        align-content: space-between;
        justify-content: center;
        padding: 2rem;
        width: 90%;
        transition: box-shadow 0.3s ease;
    }



    h2 {
        color: #000;
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    form input[type="text"],
    form input[type="password"],
    form input[type="number"] {
        width: 100%;
        padding: 0.75rem;
        margin: 0.5rem 0;
        border-radius: 25px;
        border: 1px solid black;
        outline: none;
        font-size: 1rem;
        color: #333;
        transition: border 0.3s ease;
    }

    form input[type="text"]:focus,
    form input[type="password"]:focus,
    form input[type="number"]:focus {
        border-color: #f6a821;
    }

    form button {
        width: 100%;
        padding: 0.75rem;
        background-color: #00bf62;
        color: #000;
        border: 0px;
        border-radius: 25px;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 1rem;
        transition: background-color 0.3s ease;
    }

    .cancel {
        width: 100%;
        padding: 0.75rem;
        background-color: #e93125;
        color: #000;
        border: 0px;
        border-radius: 25px;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 1rem;
        transition: background-color 0.3s ease;
    }

    .forgot-password {
        text-align: right;
        margin: 5px 0;
    }

    .forgot-password a {
        color: #000;
        text-decoration: none;
    }

    .forgot-password a:hover {
        text-decoration: underline;
    }

    .register-link a {
        color: #fff;
        text-decoration: none;
    }

    .register-link a:hover {
        text-decoration: underline;
    }

    p {
        margin-top: 15px;
        font-size: 0.9rem;
        color: #000;
    }

    .login-title {
        color: #000;
        font-size: 2rem;
        margin-bottom: 2rem;
        text-align: left;
        width: 100%;
        padding-left: 20px;
    }

    .login-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100vh;

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

    .import-img {
        width: 100%;
        padding: 0.6rem;
        margin: 0.5rem 0;
        border-radius: 25px;
        border: 1px solid black;
        outline: none;
        font-size: 1rem;
        color: #333;
        transition: border 0.3s ease;
    }

    .header {
        margin-top: 2rem;
        color: #333;
        padding: 0px;
        font-size: 1.5em;
    }

    .option-row {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px;
        align-items: center;
    }

    .option-row .option {
        width: 70%;
    }

    .option-row .extra {
        width: 30%
    }

    .add-option {
        width: 20%;
        padding: 0.75rem;
        background-color: #ffde59;
        color: #000;
        border: 0px;
        border-radius: 25px;
        font-size: 1rem;
        margin: 0 0 10px 0;
    }

    .remove-option {
        width: 10%;
        background-color: #ffffff;
        color: #000;
        border: 0px;
        font-size: 1rem;
        margin: 0;
        padding: 0;
    }

    .remove-option:disabled .fa-trash {
        cursor: not-allowed;
        color: gray;
    }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="header">แก้ไขสินค้า</div>
        <div class="login-container">
            <form action="edit_product_db.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product_id); ?>">

                <label>ชื่อสินค้า :</label>
                <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>"
                    required>

                <label>ราคา (บาท) :</label>
                <input type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($product['price']); ?>"
                    required>

                <label>ตัวเลือกเพิ่มเติม :</label>
                <div id="option-container">
                    <?php 
                $options = json_decode($product['options'], true) ?? [];
                $extra_costs = json_decode($product['extra_cost'], true) ?? [];

                for ($i = 0; $i < count($options); $i++) {
                    echo '<div class="option-row">
                            <input class="option" type="text" name="option[]" value="' . htmlspecialchars($options[$i]) . '">
                            <input class="extra" type="number" step="0.01" name="extra_cost[]" value="' . htmlspecialchars($extra_costs[$i] ?? 0) . '">
                            <button type="button" class="remove-option" onclick="removeOption(this)">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                          </div>';
                }
                ?>
                </div>
                <button type="button" class="add-option" onclick="addOption()">+</button>
                <br>
                <label>รูปภาพสินค้า :</label>
                <input type="file" name="product_image" id="product_image">
                <br><br>
                <label>หมายเหตุ :</label>
                <input type="text" name="notes" value="<?php echo htmlspecialchars($product['notes']); ?>">

                <button type="submit">บันทึกการแก้ไข</button>
                <button class="cancel" type="button"
                    onclick="window.location.href='shop_all_product.php'">ยกเลิก</button>
            </form>
        </div>
    </div>

    <script>
    let optionCount = document.querySelectorAll('.option-row').length;
    const maxOptions = 5;

    function addOption() {
        if (optionCount < maxOptions) {
            const container = document.getElementById("option-container");
            const newRow = document.createElement("div");
            newRow.classList.add("option-row");

            newRow.innerHTML = `
                    <input class="option" type="text" name="option[]" placeholder="พิเศษ">
                    <input class="extra" type="number" step="0.01" name="extra_cost[]" placeholder="20">
                    <button type="button" class="remove-option" onclick="removeOption(this)">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                `;

            container.appendChild(newRow);
            optionCount++;
        }
    }

    function removeOption(button) {
        if (optionCount > 1) {
            button.parentElement.remove();
            optionCount--;
        }
    }
    </script>
</body>

</html>