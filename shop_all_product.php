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

$store_id = $_SESSION['store_id'];

$search_query = isset($_GET['query']) ? '%' . $_GET['query'] . '%' : '%%';  // ใช้ % เพื่อค้นหาทุกอย่างที่มีคำนี้

$sql = "SELECT product_id, product_name, price, image_url, is_show 
        FROM products 
        WHERE store_id = ? AND product_name LIKE ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $store_id, $search_query);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>RMUTP Food</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js" crossorigin="anonymous">
    </script>

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

    .main-content {
        flex-grow: 1;
    }

    .header {
        background-color: #FFDE59;
        padding: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .header form {
        margin: 0;
        align-items: center;
        justify-content: center;
        width: 80%;
    }

    .header input {
        border: none;
        padding: 10px;
        border-radius: 20px;
        width: 70%;
        font-size: 14px;
    }

    .recommended {
        margin: 20px;
    }

    .recommended h3 {
        margin-bottom: 10px;
        margin-top: 15px;
        font-size: 18px;
        color: #333;
    }

    .recommended .shops {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
        justify-content: center;
    }

    .shop {
        text-align: center;
        background: #f9f9f9;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        max-width: 250px;
        margin: 10px auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: space-between;
    }

    .shop img {
        width: 100%;
        max-width: 200px;
        height: auto;
        border-radius: 10px;
        object-fit: cover;
    }

    .shop .menu-item {
        margin-top: 10px;
        font-size: 1em;
    }

    .shop .menu-item span {
        font-weight: bold;
        font-size: 1.2em;
    }

    .shop .menu-item p {
        font-size: 1.1em;
        color: #333;
        margin-bottom: 10px;
    }

    .shop .toggle-switch {
        margin-top: 10px;
    }

    .shop .menu-item .shop-btn,
    .shop .menu-item .edit-shop-btn {
        background-color: #0448A9;
        border: 0px;
        padding: 0.4rem;
        border-radius: 5px;
        color: white;
        width: fit-content;
        margin-top: 10px;
        cursor: pointer;
        font-size: 1rem;
    }

    .shop .menu-item .edit-shop-btn {
        background-color: red;
    }

    .details-bottom {
        bottom: 0;
        left: 0;
        width: 90%;
        padding: 20px;
        z-index: 1000;
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

    .shop .menu-item {

        width: 100%;
        margin-top: 10px;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 40px;
        height: 20px;
    }

    .toggle-switch input {
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 2;
        /* ✅ ให้ checkbox อยู่เหนือสุด */
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
        border-radius: 34px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 14px;
        width: 14px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }

    input:checked+.toggle-slider {
        background-color: #4CAF50;
    }

    input:checked+.toggle-slider:before {
        transform: translateX(20px);
    }

    .menu-item-btn {
        display: flex;
        justify-content: space-evenly;

        a {
            text-decoration: none;
        }
    }

    .search-box {
        display: flex;
        align-items: center;
        position: relative;
        border-radius: 20px;
        background-color: #fff;
        border: 1px solid #ccc;
        overflow: hidden;
    }

    .search-box input {
        flex: 1;
        border: none;
        padding: 10px 15px;
        border-radius: 20px;
        font-size: 14px;
        outline: none;
    }

    .search-box button {
        border: none;
        background: none;
        cursor: pointer;
        padding: 10px 15px;
        color: #555;
    }

    .fa-arrow-left {
        margin-right: 20px;
    }
    </style>
</head>
<body>
    <div class="header">
        <i class="fa-solid fa-arrow-left" onclick="window.location.href='shop_main.php';" style="cursor: pointer;"></i>&nbsp;&nbsp;
        <form method="GET" action="shop_all_product.php" class="search-form"> <!-- แก้ไข action ให้ตรงกับหน้าที่ค้นหา -->
            <div class="search-box">
                <input type="text" name="query" placeholder="ค้นหาสินค้า"
                    value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>

    <div class="main-content">
        <div class="recommended">
            <h3>สินค้าทั้งหมด</h3>
            <div class="shops">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $image_url = htmlspecialchars($row['image_url']);
                        $product_name = htmlspecialchars($row['product_name']);
                        $price = number_format($row['price'], 2);
                        $product_id = htmlspecialchars($row['product_id']);
                        $is_checked = ($row['is_show'] == 1) ? 'checked' : '';
            
                        echo '<div class="shop">
                            <img src="' . $image_url . '" alt="' . $product_name . '">
                            <div class="menu-item">
                                <span>' . $product_name . '</span>
                                <p>' . $price . '฿</p>
                            </div>
                            <div class="toggle-switch">
                                <input type="checkbox" class="toggle-checkbox" data-product-id="' . $product_id . '" ' . $is_checked . '>
                                <span class="toggle-slider"></span>
                            </div>
                            <div class="menu-item">
                                <div class="menu-item-btn">
                                    <a href="shop_edit_product.php?product_id=' . $product_id . '" class="shop-btn">แก้ไข</a>
                                    <form method="POST" action="delete_product_db.php" style="display:inline;">
                                        <input type="hidden" name="product_id" value="' . $product_id . '">
                                        <button type="submit" class="edit-shop-btn">ลบ</button>
                                    </form>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo "<p>ไม่พบสินค้า</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <div class="details-bottom">
        <a href="shop_add_product.php" class="reorder-button">เพิ่มสินค้าใหม่</a>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggles = document.querySelectorAll(".toggle-checkbox");

        toggles.forEach(toggle => {
            toggle.addEventListener("change", function() {
                const productId = this.getAttribute("data-product-id");
                const isShow = this.checked ? 1 : 0;

                fetch("update_is_show.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `product_id=${productId}&is_show=${isShow}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log("Response:", data);
                        if (data.status !== "success") {
                            alert("อัปเดตล้มเหลว: " + data.message);
                            toggle.checked = !toggle.checked;
                        }
                    })
                    .catch(error => {
                        alert("เกิดข้อผิดพลาดขณะอัปเดตข้อมูล");
                        toggle.checked = !toggle.checked;
                    });
            });
        });
    });
    </script>

</body>

</html>

<?php
$stmt->close();
$conn->close();
?>