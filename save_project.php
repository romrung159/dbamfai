<?php
include 'config.php'; // เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_name = $_POST['project_name'];
    $budget_year = $_POST['budget_year'];
    $province = $_POST['province'];
    $amphoe = $_POST['amphoe'];
    $tambon = $_POST['tambon'];
    $ministry = $_POST['ministry'];
    $project_type = $_POST['project_type'];
    $project_status = $_POST['project_status'];
    $contract_start = $_POST['contract_start'];
    $contract_end = $_POST['contract_end'];

    // เตรียม SQL
    $sql = "INSERT INTO projects (project_name, budget_year, province, amphoe, tambon, ministry, project_type, project_status, contract_start, contract_end) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sissssssss", $project_name, $budget_year, $province, $amphoe, $tambon, $ministry, $project_type, $project_status, $contract_start, $contract_end);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "บันทึกข้อมูลสำเร็จ!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "เกิดข้อผิดพลาด: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}
?>
