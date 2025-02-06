<?php
session_start();
include('db.php');

if (isset($_GET['store_id'])) {
    $store_id = $_GET['store_id'];

    $sql = "SELECT * FROM stores WHERE store_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $store_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $store = $result->fetch_assoc();
        } else {
            echo "ข้อมูลร้านค้าไม่พบ";
            exit;
        }

        $stmt->close();
    } else {
        echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
        exit;
    }
} else {
    echo "ไม่พบ store_id";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลร้านค้า</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js" crossorigin="anonymous">
    </script>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        color: black;
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
        background-color: #FDDF59;
        padding: 2rem;
        width: 90%;
        max-width: 400px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        transition: box-shadow 0.3s ease;
    }

    .login-container:hover {
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
    }

    form input[type="text"],
    form input[type="password"] {
        width: 100%;
        padding: 0.75rem;
        margin: 0.8rem 0;
        border-radius: 25px;
        border: 1px solid #ccc;
        outline: none;
        font-size: 1rem;
        color: #333;
        transition: border 0.3s ease;
    }

    form input[type="text"]:focus,
    form input[type="password"]:focus {
        border-color: #f6a821;
    }

    form select {
        width: 100%;
        padding: 0.75rem;
        margin: 0.8rem 0;
        border-radius: 25px;
        border: 1px solid #ccc;
        outline: none;
        font-size: 1rem;
        color: #333;
        transition: border 0.3s ease;
    }

    form select:focus {
        border-color: #f6a821;
    }
    form .btn-confirm {
        width: 100%;
        padding: 0.75rem;
        background-color: #00BD5F;
        color: #000;
        border: 0px;
        border-radius: 25px;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 0.8rem;
    }

    form .btn-cancel {
        width: 100%;
        padding: 0.75rem;
        background-color: #d43e3f;
        color: #000;
        border: 0px;
        border-radius: 25px;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 0.8rem;
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
        margin-top: 13rem;
        height: 100vh;
    }

    .login-wrapper h2 {
        font-size: 2rem;
        color: black;
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
    </style>
</head>

<body>
    <div class="top-tab">
        <i class="fa-solid fa-arrow-left" onclick="window.history.back();"></i>
    </div>
    <div class="login-wrapper">
        <h2 class="login-title">แก้ไขข้อมูลร้านค้า</h2>
        <div class="login-container">
            <form action="edit_shop_db.php" method="POST">
                <input type="hidden" name="store_id" value="<?php echo $store['store_id']; ?>">
                <input type="text" name="store_name" placeholder="ชื่อร้าน" value="<?php echo htmlspecialchars($store['store_name']); ?>" required>
                <input type="text" name="user_name" placeholder="ชื่อเจ้าของร้าน" value="<?php echo htmlspecialchars($store['user_name']); ?>" required>
                <select name="category">
                    <option value="" disabled>หมวดหมู่</option>
                    <option value="อาหาร" <?php echo ($store['category'] == 'อาหาร') ? 'selected' : ''; ?>>อาหาร</option>
                    <option value="เครื่องดื่ม" <?php echo ($store['category'] == 'เครื่องดื่ม') ? 'selected' : ''; ?>>เครื่องดื่ม</option>
                    <option value="ของทานเล่น" <?php echo ($store['category'] == 'ของทานเล่น') ? 'selected' : ''; ?>>ของทานเล่น</option>
                    <option value="อื่นๆ" <?php echo ($store['category'] == 'อื่นๆ') ? 'selected' : ''; ?>>อื่นๆ</option>
                </select>
                <button class="btn-confirm" type="submit">ยืนยัน</button>
                <button class="btn-cancel" type="button">ยกเลิก</button>
            </form>
        </div>
    </div>
</body>

</html>
