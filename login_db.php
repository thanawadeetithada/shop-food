<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    $sql = "SELECT u.user_id, u.phone, u.password, u.role, s.store_id
            FROM users u
            LEFT JOIN stores s ON u.user_id = s.user_id  
            WHERE u.phone = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['phone'] = $user['phone'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'store_owner' && !is_null($user['store_id'])) {
                $_SESSION['store_id'] = $user['store_id']; 
            } else {
                $_SESSION['store_id'] = null; 
            }

            if ($user['role'] == "customer") {
                header("Location: user_main.php");
                exit();
            } elseif ($user['role'] == "admin") {
                header("Location: admin_main.php");
                exit();
            } elseif ($user['role'] == "store_owner") {
                header("Location: shop_main.php");
                exit();
            }
        } else {
            echo "<script>alert('รหัสผ่านไม่ถูกต้อง!'); window.location.href = 'index.php';</script>";
        }
    } else {
        echo "<script>alert('ไม่พบเบอร์โทรนี้ในระบบ!'); window.location.href = 'index.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
