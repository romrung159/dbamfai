<?php
$host = '127.0.0.1'; // หรือ 'localhost'
$user = 'root';
$password = ''; // ถ้ามีรหัสผ่านให้ใส่ที่นี่
$database = 'dashboard_system';
$port = 3307; // เปลี่ยนเป็นพอร์ต 3307 ตามที่ตั้งค่า

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
