<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    $check_sql = "SELECT * FROM users WHERE phone = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('เบอร์โทรนี้ถูกใช้แล้ว!'); window.location.href = 'register.php';</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (phone, password, role, created_at) VALUES (?, ?, 'customer', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $phone, $hashed_password);

        if ($stmt->execute()) {
            echo "<script>alert('ลงทะเบียนสำเร็จ!'); window.location.href = 'index.php';</script>";
        } else {
            echo "เกิดข้อผิดพลาด: " . $stmt->error;
        }

        $stmt->close();
    }
}
$conn->close();
?>
