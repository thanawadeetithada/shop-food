<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = $_POST['phone'];
    $new_password = $_POST['new_password'];

    if (!empty($phone) && !empty($new_password)) {
        $stmt = $conn->prepare("SELECT phone FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE phone = ?");
            $stmt->bind_param("ss", $hashed_password, $phone);

            if ($stmt->execute()) {
                echo "<script>
                        alert('อัปเดตรหัสผ่านสำเร็จ!');
                        window.location.href = 'index.php';
                      </script>";
            } else {
                echo "เกิดข้อผิดพลาดในการอัปเดตรหัสผ่าน: " . $stmt->error;
            }
        } else {
            echo "<script>
                    alert('เบอร์โทรศัพท์ไม่ถูกต้อง');
                    window.location.href = 'forgot_password.php';
                  </script>";
        }

        $stmt->close();
    } else {
        echo "กรุณากรอกข้อมูลให้ครบถ้วน";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .forgot-password-container {
        width: 70%;
        max-width: 400px;
        background-color: #FFDE59;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        text-align: center;
        transition: box-shadow 0.3s ease;
    }

    .forgot-password-container:hover {
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
    }

    h2 {
        color: #000;
        font-size: 2rem;
        margin-bottom: 1rem;
    }

    form {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    form input {
        width: 100%;
        padding: 0.75rem;
        margin: 0.5rem 0;
        border-radius: 25px;
        border: none;
        outline: none;
        font-size: 1rem;
        color: #333;
    }

    button {
        width: 100%;
        padding: 0.75rem;
        margin: 1rem 0;
        border-radius: 25px;
        border: 2px solid #000;
        background-color: #fff;
        color: #000;
        font-size: 1rem;
        cursor: pointer;
    }
    </style>
</head>

<body>
    <div class="forgot-password-container">
        <h2>ลืมรหัสผ่าน</h2>
        <form method="post">
            <input type="tel" name="phone" placeholder="เบอร์โทร" required pattern="[0-9]{10}" maxlength="10"
                oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.setCustomValidity('');"
                oninvalid="this.setCustomValidity('กรุณาใส่เบอร์โทรให้ถูกต้อง (ตัวเลข 10 หลัก)');">
            <input type="password" name="new_password" placeholder="รหัสผ่านใหม่" required>
            <button type="submit">อัปเดตรหัสผ่าน</button>
        </form>
        <p>จำรหัสผ่านได้แล้ว? <a href="index.php">เข้าสู่ระบบ</a></p>
    </div>
</body>

</html>