<?php
session_start();
include 'db.php';

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มสินค้าใหม่</title>
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
        background-color: #ffde59;
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
        background-color: #ffde59;
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
        <div class="header">เพิ่มสินค้าใหม่</div>
        <div class="login-container">
            <form action="add_product_db.php" method="POST" enctype="multipart/form-data">
                <span>ชื่อสินค้า : </span>
                <input type="text" name="product_name" required>

                <span>ราคา (บาท) : </span>
                <input type="number" step="0.01" name="price" required>

                <div class="option-row" style="margin-bottom: 0px;">
                    <span>ตัวเลือกเพิ่มเติม :</span>
                    <span>ค่าใช้จ่ายเพิ่มเติม :</span>
                </div>

                <div id="option-container">
                    <div class="option-row" style="margin-bottom: 0px;">
                        <input class="option" type="text" name="option[]" placeholder="ธรรมดา">
                        <input class="extra" type="number" step="0.01" name="extra_cost[]" placeholder="0">
                        <button type="button" class="remove-option" disabled>
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>

                <button type="button" class="add-option" onclick="addOption()">+</button>

                <br>
                <span>รูปภาพสินค้า : </span>
                <div class="import-img">
                    <input type="file" name="product_image" accept="image/*" required>
                </div>
                <span>หมายเหตุ : </span>
                <input type="text" name="notes" placeholder="">

                <button type="submit">บันทึกสินค้า</button>
                <button class="cancel" type="button"
                    onclick="window.location.href='shop_all_product.php'">กลับไปหน้าหลัก</button>
            </form>
            <br>
        </div>
    </div>
    <script>
    let optionCount = 1;
    const maxOptions = 5;

    function addOption() {
        if (optionCount < maxOptions) {
            const container = document.getElementById("option-container");
            const newRow = document.createElement("div");
            newRow.classList.add("option-row");
            newRow.setAttribute("style", "margin-bottom: 0px;"); 

            newRow.innerHTML = `
                <input class="option" type="text" name="option[]" placeholder="พิเศษ">
                <input class="extra" type="number" step="0.01" name="extra_cost[]" placeholder="20">
                <button type="button" class="remove-option" onclick="removeOption(this)">
                    <i class="fa-solid fa-trash"></i>
                </button>
            `;

            container.appendChild(newRow);
            optionCount++;

            if (optionCount >= maxOptions) {
                document.querySelector(".add-option").style.display = "none"; // ซ่อนปุ่ม +
            }
        }
    }

    function removeOption(button) {
        if (optionCount > 1) {
            button.parentElement.remove();
            optionCount--;

            if (optionCount < maxOptions) {
                document.querySelector(".add-option").style.display = "block"; // แสดงปุ่ม + กลับมา
            }
        }
    }
</script>

</body>

</html>