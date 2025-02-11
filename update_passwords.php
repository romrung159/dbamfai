<?php
include 'config.php'; // เชื่อมต่อฐานข้อมูล

// ดึงข้อมูลผู้ใช้ที่ยังไม่ได้เข้ารหัสรหัสผ่าน
$sql = "SELECT id, username, password FROM users";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $user_id = $row['id'];
        $username = $row['username'];
        $old_password = $row['password'];

        // ถ้ารหัสผ่านถูกเข้ารหัสอยู่แล้ว ให้ข้าม
        if (password_needs_rehash($old_password, PASSWORD_DEFAULT) === false) {
            echo "User: $username - รหัสผ่านถูกเข้ารหัสแล้ว ✅<br>";
            continue;
        }

        // เข้ารหัสรหัสผ่านใหม่
        $hashed_password = password_hash($old_password, PASSWORD_DEFAULT);

        // อัปเดตรหัสผ่านในฐานข้อมูล
        $update_sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {
            echo "User: $username - อัปเดตรหัสผ่านเรียบร้อย ✅<br>";
        } else {
            echo "User: $username - อัปเดตรหัสผ่านล้มเหลว ❌<br>";
        }
    }
} else {
    echo "ไม่พบผู้ใช้ในระบบ";
}

$conn->close();
?>
