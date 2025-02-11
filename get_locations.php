<?php
header('Content-Type: application/json; charset=utf-8');
require 'config.php'; // เชื่อมต่อฐานข้อมูล

$type = $_GET['type'];
$parent_id = $_GET['parent_id'] ?? null;

if ($type === 'province') {
    $sql = "SELECT id, name_th FROM thai_provinces ORDER BY name_th";
} elseif ($type === 'amphoe' && $parent_id) {
    $sql = "SELECT id, name_th FROM thai_amphures WHERE province_id = ? ORDER BY name_th";
} elseif ($type === 'tambon' && $parent_id) {
    $sql = "SELECT id, name_th FROM thai_tambons WHERE amphure_id = ? ORDER BY name_th";
} else {
    echo json_encode([]);
    exit();
}

$stmt = $conn->prepare($sql);
if ($parent_id) $stmt->bind_param("i", $parent_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
