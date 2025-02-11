<?php
include 'config.php'; // เชื่อมต่อฐานข้อมูล

// กำหนดข้อมูลที่ต้องการรีเซ็ต
$username = "admin1"; // 🔹 เปลี่ยนเป็นชื่อผู้ใช้ที่ต้องการรีเซ็ต
$new_password = "123456"; // 🔹 เปลี่ยนเป็นรหัสผ่านใหม่ที่ต้องการ

// เข้ารหัสรหัสผ่านใหม่
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// อัปเดตรหัสผ่านในฐานข้อมูล
$sql = "UPDATE users SET password = ? WHERE user = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hashed_password, $username);

if ($stmt->execute()) {
    echo "รีเซ็ตรหัสผ่านใหม่สำเร็จ ✅<br>";
    echo "รหัสผ่านใหม่สำหรับ $username คือ: $new_password";
} else {
    echo "เกิดข้อผิดพลาด ❌";
}

$conn->close();
?>
