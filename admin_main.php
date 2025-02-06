<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['admin']))) {
    header('Location: index.php'); 
    exit();
}
// เพิ่มโค้ดหลังจากเริ่มต้นการเชื่อมต่อกับฐานข้อมูล
// เช็คว่ามี store_id ในตาราง users หรือไม่
$sql_check_store = "SELECT store_id FROM users WHERE store_id IS NOT NULL LIMIT 1"; 
$stmt_check = $conn->prepare($sql_check_store);
$stmt_check->execute();
$check_result = $stmt_check->get_result();

if ($check_result->num_rows > 0) {
    // ถ้ามี store_id ในตาราง users ให้ทำการอัพเดต store_id เป็น NULL
    $sql_update = "UPDATE users SET store_id = NULL WHERE store_id IS NOT NULL";
    $stmt_update = $conn->prepare($sql_update);
    if ($stmt_update->execute()) {
         error_log("อัพเดต store_id เป็น NULL เรียบร้อยแล้ว");
    } else {
        error_log("เกิดข้อผิดพลาดในการอัพเดต store_id");
    }
} else {
    // ถ้าไม่มี store_id ในตาราง users
    error_log("ไม่มี store_id ในตาราง users");
}



// ลบเงื่อนไขการกรองหมวดหมู่
$sql = "SELECT store_id, store_name, user_name, category, phone, image_url FROM stores";

$stmt = $conn->prepare($sql);
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
        min-height: 100vh;
    }

    .main-content {
        flex-grow: 1;
    }

    .top-tab {
        background-color: #FFDE59;
        padding: 10px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    .top-tab form {
        margin: 0 10px 0 0px;
        align-items: center;
        justify-content: center;
        width: 80%;
    }

    .top-tab input {
        border: none;
        padding: 10px;
        border-radius: 20px;
        width: 70%;
        font-size: 14px;
    }

    .banner {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 10px 20px;
    }

    .banner-img {
        width: 100%;
        max-height: 200px;
        object-fit: contain;
        border-radius: 10px;
    }

    .categories {
        display: flex;
        justify-content: space-around;
        padding: 10px;
        background-color: white;
    }

    .category button {
        background: #FFDE59;
        border: none;
        padding: 15px 20px;
        border-radius: 10px;
        font-size: 1.5em;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 80px;
        height: 80px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }


    .category p {
        margin-top: 8px;
        font-size: 14px;
        color: #333;
        font-weight: bold;
    }

    .categories button svg {
        font-size: 2rem;
        color: white;
    }

    .category {
        text-align: center;
    }

    .recommended {
        margin: 0px 20px 20px 20px;
    }

    .recommended h3 {
        margin-bottom: 10px;
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
        padding: 10px 10px 0px;
        border-radius: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        max-width: 100%;
    }

    .shop img {
        width: 100%;
        max-width: 250px;
        height: 100%;
        max-height: 100px;
        border-radius: 10px;
        margin-bottom: 10px;
    }

    .shop a {
        text-decoration: none;
        color: black;
        margin-bottom: 20px !important;
    }

    /* Footer Section */
    .footer {
        align-items: center;
        display: flex;
        justify-content: space-around;
        background-color: #fff;
        padding: 5px 0;
        margin-left: 20px;
        bottom: 0;
        width: 90%;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 100px;
        margin-bottom: 1rem;
        background-color: #ffd700;
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

    .search-form {
        width: 100%;
        max-width: 500px;
        position: relative;
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

    .fa-circle-user {
        font-size: 1.8rem;
        color: #ffffff;
        background-color: #ccc;
        border-radius: 15px;
    }

    a {
        color: black;
        text-decoration: none;
    }

    .view-shop {
        background-color: #0448A9;
        text-decoration: none;
        color: black;
        margin-bottom: 20px !important;
        border: 0px;
        padding: 0.4rem;
        border-radius: 5px;
        color: white !important;
        width: fit-content;
        margin-top: 10px;
        cursor: pointer;
        font-size: 1rem;
    }

    .delete-shop {
        background-color: red;
        border: 0px;
        padding: 0.4rem;
        border-radius: 5px;
        color: white !important;
        width: fit-content;
        margin-top: 10px;
        cursor: pointer;
        font-size: 1rem;
    }

    .shop-buttons {
        width: 100%;
        display: flex;
        justify-content: space-between;
    }
    </style>
</head>

<body>
    <div class="top-tab">
        <form method="GET" action="search.php" class="search-form">
            <div class="search-box">
                <input type="text" name="query" placeholder="ค้นหาสินค้า"
                    value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>
        <a href="logout.php">
            <i class="fa-solid fa-circle-user"></i>
        </a>
    </div>

    <div class="main-content">
        <div class="recommended">
            <h3>ร้านค้าทั้งหมด</h3>
            <div class="shops">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $image_url = htmlspecialchars($row['image_url']); 
                        $store_name = htmlspecialchars($row['store_name']);
                        $store_id = htmlspecialchars($row['store_id']);
                ?>
                <div class="shop">
                    <img src="<?php echo $image_url; ?>" alt="<?php echo $store_name; ?>">
                    <p style="margin:0;text-align: left;"><?php echo $store_name; ?></p>

                    <div class="shop-buttons">
                        <button class="view-shop"
                            onclick="window.location.href = 'update_shopid_byAdmin.php?store_id=<?php echo $store_id; ?>'">ดูร้านค้า</button>

                        <a href="delete_store.php?store_id=<?php echo $store_id; ?>" class="delete-shop"
                            onclick="return confirm('คุณต้องการลบร้านค้านี้หรือไม่?')">ลบ</a>
                    </div>
                </div>
                <?php
                    }
                } else {
                    echo "<p>ไม่มีร้านค้า</p>";
                }
                ?>
            </div>
        </div>
    </div>
    <a href="shop_register_admin.php">
        <div class="footer">
            <div class="reorder-button">เพิ่มร้านค้า</div>
        </div>
    </a>
</body>

</html>