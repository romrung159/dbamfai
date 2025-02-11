<?php
include 'config.php';

$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date("Y") + 543;

$sql = "SELECT * FROM projects WHERE budget_year = ? ORDER BY id DESC";

// ตรวจสอบว่าการ prepare คำสั่ง SQL สำเร็จหรือไม่
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $selected_year);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    $stmt->close(); // ปิด statement หลังใช้งานเสร็จ
} else {
    die("เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $conn->error);
}

// ปิดการเชื่อมต่อฐานข้อมูลถ้ายังเปิดอยู่
if (isset($conn)) {
    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard งบประมาณ</title>
    <!-- การเรียกใช้ใน html -->
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        body { background-color: #f8f9fa; 
            font-family: 'Kanit', 'Poppins', sans-serif;}
            .card { box-shadow: 0 4px 8px rgba(0,0,0,0.1); }

            h1, h2, h3, h4, h5, h6 {
            font-family: 'Kanit', sans-serif;
            font-weight: 600;
    }

            button, .btn {
            font-family: 'Kanit', sans-serif;
            font-weight: 400;
}

            .search-box {
            width: 200px;
            }
            button, .btn-outline-light{
            font-family: 'Kanit', sans-serif;
            }
            .budget-year-label {
             font-family: 'Kanit', sans-serif;
            font-size: 1.2rem;
            }
            .showLoginModal {
            cursor: pointer;
            font-size: 1.2rem;
            }
            .widget-box {
                margin-top: 10px; /* ลดระยะห่างจากวิดเจ็ตก่อนหน้า */
                border: 1px solid #ddd;
                border-radius: 10px;
                padding: 15px;
                background: #fff;
                text-align: left;
                width: 100%; /* ให้ขยายเต็ม col-md-4 */
}

.widget-title {
    font-size: 18px;
    margin-bottom: 10px;
}

.widget-content {
    display: flex;
    align-items: baseline;
}

.focus-number {
    font-size: 32px;
    color: #65c0dc;
    font-weight: bold;
    margin-right: 5px;
}

.unit {
    font-size: 14px;
    color: #666;
}


    </style>

<style>
    .widget-box1 {
        width: 320px;
        margin-top: 10px; /* ลดระยะห่างจากวิดเจ็ตก่อนหน้า */
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        overflow: hidden;
        width: 100%; /* ให้ขยายเต็ม col-md-4 */
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    }

    .widget-title1 {
        font-size: 18px;
        padding: 10px;
        font-weight: bold;
    }

    .widget-content2 {
        background-color: #4A5A6A; /* สีเทาเข้ม */
        color: white;
        padding: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 120px;
    }

    .widget-name2 {
        font-size: 16px;
        font-weight: bold;
    }

    .widget-name a {
        color: white;
        text-decoration: none;
    }

    .focus-number {
        font-size: 32px;
        color: #FF6A00; /* สีส้ม */
        font-weight: bold;
        text-align: right;
    }

    .mini-number {
        font-size: 14px;
        color: white;
        text-align: right;
    }
    .toggle-content {
            display: none;
            padding: 10px;
            background: #ffffff;
        }
        .active {
            display: block;
        }
    /* ปรับสีลิงก์ทั่วไป */
/* สไตล์ปกติของลิงก์ */
a:link {
  color: white;
  background-color: transparent;
  text-decoration: none;
}
a:visited {
  color: #FF6A00;
  background-color: transparent;
  text-decoration: none;
}
a:hover {
  color: #FF6A00;
  background-color: transparent;
  text-decoration: underline;
}
a:active {
  color: yellow;
  background-color: transparent;
  text-decoration: underline;
}

/* แยกสไตล์ dropdown-item ออกจาก a:link */
#budget1-year-dropdown .dropdown-item {
  color: black; /* สีตัวอักษรปกติ */
  background-color: transparent;
  text-decoration: none;
}

/* Hover */
#budget1-year-dropdown .dropdown-item:hover {
  background-color: #f8f9fa;
  color: black;
}

/* Active */
#budget1-year-dropdown .dropdown-item.active {
  background-color: #007bff;
  color: white;
}

/* Active + Hover */
#budget1-year-dropdown .dropdown-item.active:hover {
  background-color: #0056b3;
  color: white;
}

/* Focus */
#budget1-year-dropdown .dropdown-item:focus {
  background-color: #f8f9fa;
  color: black;
}

.toggle-content {
    display: none;
    padding: 10px;
    background: #ffffff;
    transition: all 0.3s ease-in-out;
}

.toggle-content.active {
    display: block;
}

.dark-footer-div {
    background:rgb(11, 201, 201); /* สีพื้นหลังเข้ม */
    color: white;
    text-align: center;
    padding: 0px;
    cursor: pointer;
    border-top: 1px solid #ddd;
}

.dark-footer-div:hover {
    background:rgb(8, 163, 163); /* เปลี่ยนสีเมื่อ Hover */
}

.dark-footer-div i {
    font-size: 18px;
    transition: transform 0.3s ease-in-out;
}

.dark-footer-div.active i {
    transform: rotate(180deg); /* หมุนลูกศรลง */
}


.chart-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .chart-container {
            max-width: 300px; /* ลดขนาด Pie Chart */
            margin: auto;
        }
</style>
<style>
/* ปรับแต่ง DataTables */
.dataTables_wrapper .dataTables_filter {
    float: left !important; /* จัดช่องค้นหาให้อยู่ชิดซ้าย */
    margin-bottom: 10px !important; /* ลดช่องว่างด้านล่าง */
}
.dataTables_wrapper .dataTables_length {
    float: right !important; /* จัดตัวเลือก "แสดงรายการ" ไปทางขวา */
    margin-bottom: 10px!important; /* ลดช่องว่างด้านล่าง */
}
</style>

</head>

<script>document.addEventListener("DOMContentLoaded", function () {
    const dropdownButton = document.getElementById("budget1-year-button");
    const dropdownItems = document.querySelectorAll("#budget1-year-dropdown .dropdown-item");

    dropdownItems.forEach(item => {
        item.addEventListener("click", function (event) {
            event.preventDefault(); // ป้องกันการโหลดหน้าใหม่
            const selectedYear = this.getAttribute("data-year");
            dropdownButton.innerHTML = selectedYear + ' <span class="caret"></span>';
        });
    });
});
</script>
<body class="container py-4">
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">   <div class="container-fluid">
        <!-- โลโก้ -->
        <!-- <a class="navbar-brand" href="#">
            <img src="logo.png" alt="Logo" width="40" height="40"> NSO-Dashboard
        </a> -->

        <!-- ปุ่ม Toggle สำหรับมือถือ -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- โลโก้ -->
        <!-- <a class="navbar-brand" href="#">
            <img src="logo.png" alt="Logo" width="40" height="40"> NSO-Dashboard
        </a> -->
        <!-- เมนู Navbar -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#">ข้อมูลโครงการ</a></li>
                <li class="nav-item"><a class="nav-link" href="#">ข้อมูลสถิติ</a></li>
            </ul>

            <!-- ช่องค้นหา -->
            <form class="d-flex ms-3">
                <input class="form-control me-2 search-box" type="search" placeholder="ค้นหาข้อมูล">
                <button class="btn btn-outline-light" type="submit">🔍</button>
            </form>

            <!-- ปุ่มเปลี่ยนภาษา -->
            <a href="#" class="nav-link text-white ms-3">🇬🇧</a>

            <!-- ปุ่มเข้าสู่ระบบ -->
            <a href="#" class="btn btn-outline-light ms-3" onclick="showLoginModal()">เข้าสู่ระบบ</a>

        </div>
    </div>
</nav>

<div class="pt-5 mt-4">
    <h2 class="text-center">📊 Dashboard </h2>
    <!-- <h2 class="text-center">📊 Dashboard งบประมาณ ปี <span id="selectedYear"><?= $selected_year; ?></span></h2> -->
</div>

<!-- Section สำหรับเลือกปีงบประมาณและเดือน -->
<div class="d-flex justify-content-end align-items-center gap-3 mb-3">
    <!-- Dropdown ปีงบประมาณ -->
    <div>
        <label class="budget-year-label" for="budget-year">ปีงบประมาณ</label>
        <input id="budget-year" type="hidden">
        <div class="btn-group">
            <button id="budget1-year-button" class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                2568
            </button>
            <ul id="budget1-year-dropdown" class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="setBudgetYear(2568)">2568</a></li>
                <li><a class="dropdown-item" href="#" onclick="setBudgetYear(2567)">2567</a></li>
                <li><a class="dropdown-item" href="#" onclick="setBudgetYear(2566)">2566</a></li>
            </ul>
        </div>
    </div>
</div>

<script>
    function setBudgetYear(year) {
    document.getElementById("budget1-year-button").innerText = year; // เปลี่ยนปุ่มให้แสดงค่าที่เลือก
    window.location.href = "?year=" + year; // โหลดหน้าใหม่พร้อมส่งค่า
}
</script>

    <!-- Dropdown เลือกเดือน -->
    <!-- <div>
        <label for="monthFilter" class="form-label visually-hidden">เลือกเดือน</label>
        <select id="monthFilter" class="form-select form-select-sm" style="width: 150px;">
            <option value="" disabled selected>กรุณาเลือกเดือน</option>
            <!-- <option value="all">ทุกเดือน</option>
                        <?php 
                        $months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
                        foreach ($months as $index => $month) {
                            echo "<option value='" . ($index + 1) . "'>$month</option>";
                        } 
                        ?> -->
        </select>
    </div> 
</div>


<?php
$fixed_date = date("d/m");// 🔹 ตั้งค่าคงที่ (แก้เป็นวันที่ต้องการ)
?>

<div class="text-end text-muted">
    ข้อมูล ณ วันที่ <?= $fixed_date . "/" . $selected_year; ?>
</div>

   
<!-- เลือกเดือน -->
<!-- <div class="mb-3"> -->
    <!-- <label for="monthFilter" class="form-label">เลือกเดือน:</label> -->
    
    <!-- <select id="monthFilter" class="form-select"> -->
        <!-- <option value="" disabled="" selected="">กรุณาเลือก</option>
        <option value="all">ทุกเดือน</option> -->
        <!-- //<?php 
        // $months = ["มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
        // foreach ($months as $index => $month) {
        //     echo "<option value='" . ($index + 1) . "'>$month</option>";
        // } 
        // ?>
    </select>
</div> -->

<!-- ตารางแสดงข้อมูลงบประมาณ -->

<?php
function formatThaiDate($date) {
    $months = [
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];

    $dt = new DateTime($date);
    $day = $dt->format('d'); // วันที่
    $month = $months[(int)$dt->format('m') - 1]; // ชื่อเดือนภาษาไทย
    $year = $dt->format('Y') + 543; // แปลง ค.ศ. เป็น พ.ศ.

    return "$day $month $year";
}

// ตัวอย่างใช้งาน
//$date = "2026-02-10";
//echo formatThaiDate($date); // ผลลัพธ์: 10 กุมภาพันธ์ 2569
?>
 
 <div class="card p-3">
 <table id="projectTable" class="table table-bordered text-center">
        <thead class="table-dark" >
            <tr>
        <th class="text-center">ชื่อโครงการ</th>
        <th class="text-center">ปีงบประมาณ</th>
        <th class="text-center">ระยะเวลาดำเนินการ</th>
        <th class="text-center">สถานะโครงการ</th>
        <th class="text-center">ผู้รับผิดชอบ(การดำเนินการ)</th>
    </tr>
   
        </thead>
        <tbody>
            <?php
            include 'config.php'; // เชื่อมต่อฐานข้อมูล

            // ตรวจสอบว่า $conn ยังเปิดอยู่หรือไม่
            if ($conn) {
                $sql = "SELECT project_name, budget_year, contract_start, contract_end, project_status, ministry FROM projects";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) { // ✅ เช็คว่ามีข้อมูลในตาราง
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['project_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['budget_year']) . "</td>";
                            echo "<td>" . formatThaiDate($row['contract_start']) . " - " . formatThaiDate($row['contract_end']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['project_status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ministry']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center text-danger'>ไม่มีข้อมูลโครงการ</td></tr>"; // ✅ แสดงข้อความถ้าไม่มีข้อมูล
                    }
        
                    // ปิด statement หลังจากใช้เสร็จ
                    $stmt->close();
                } else {
                    echo "<tr><td colspan='5'>เกิดข้อผิดพลาดในการเตรียมคำสั่ง: " . $conn->error . "</td></tr>";
                }

                // ปิดการเชื่อมต่อฐานข้อมูลหลังจากใช้งานเสร็จ
                $conn->close();
            } else {
                echo "<tr><td colspan='5'>การเชื่อมต่อฐานข้อมูลล้มเหลว</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $('#projectTable').DataTable({
        "pageLength": 10,  // จำนวนแถวต่อหน้า
        "lengthMenu": [5, 10, 25, 50], // ตัวเลือกจำนวนแถวต่อหน้า
        "ordering": true,  // เปิดใช้งานการเรียงลำดับ
        "searching": true, // เปิดใช้งานช่องค้นหา
        "language": {
            "search": "ค้นหา:",
            "lengthMenu": "แสดง _MENU_ รายการ",
            "info": "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
            "paginate": {
                "first": "หน้าแรก",
                "last": "หน้าสุดท้าย",
                "next": "ถัดไป",
                "previous": "ก่อนหน้า"
            }
        }
    });
});
</script>


<!-- ปุ่ม Export -->
<div class="d-flex justify-content-center gap-1 mt-3">
    <button id="exportExcelBtn" class="btn btn-success" onclick="exportToExcel()" disabled>📂 Export Excel</button>
    <button id="exportPdfBtn" class="btn btn-danger" onclick="exportToPDF()" disabled>📄 Export PDF</button>
</div>




<!-- กราฟ และ Widget -->
<div class="row mt-4">
    <div class="col-md-4">
        <div class="widget-box">
            <h2 class="widget-title">งบประมาณจัดซื้อจัดจ้าง</h2>
            <div class="widget-content">
                <span class="focus-number">322,553.64</span>
                <span class="unit">ล้านบาท</span>
            </div>
        </div>
        
    </div>
   
    <div class="col-md-4">
        <div class="widget-box">
            <h2 class="widget-title">มูลค่าโครงการรวม</h2>
            <div class="widget-content">
                <span class="focus-number">322,553.64</span>
                <span class="unit">ล้านบาท</span>
            </div>
        </div>
        </div>
        
        <div class="col-md-4">
        <div class="widget-box">
            <h2 class="widget-title">มูลค่าโครงการรวม</h2>
            <div class="widget-content">
                <span class="focus-number">322,553.64</span>
                <span class="unit">ล้านบาท</span>
            </div>
        </div>
        </div>

        <div class="col-sm-12">
        <div class="widget-box">
                        <div class="border-div">
                            <h2 class="widget-title">ผลการจัดซื้อจัดจ้างรายเดือน</h2>
                            <div id="budgetLineChart" data-highcharts-chart="0" style="overflow: hidden;"><div id="highcharts-20cud33-0" dir="ltr" class="highcharts-container " style="position: relative; overflow: hidden; width: 1130px; height: 250px; text-align: left; line-height: normal; z-index: 0; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); user-select: none; touch-action: manipulation; outline: none; font-family: csChatthaiUI; font-size: 14px;"><svg version="1.1" class="highcharts-root" style="font-family:&quot;csChatthaiUI&quot;;font-size:14px;" xmlns="http://www.w3.org/2000/svg" width="1130" height="250" viewBox="0 0 1130 250"><desc>Created with Highcharts 9.1.2</desc><defs><clipPath id="highcharts-20cud33-1-"><rect x="0" y="0" width="1045" height="165" fill="none"></rect></clipPath></defs><rect fill="#ffffff" class="highcharts-background" x="0" y="0" width="1130" height="250" rx="0" ry="0"></rect><rect fill="none" class="highcharts-plot-background" x="75" y="10" width="1045" height="165"></rect><g class="highcharts-grid highcharts-xaxis-grid" data-z-index="1"><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 161.5 10 L 161.5 175" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 248.5 10 L 248.5 175" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 335.5 10 L 335.5 175" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 422.5 10 L 422.5 175" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 509.5 10 L 509.5 175" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 597.5 10 L 597.5 175" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 684.5 10 L 684.5 175" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 771.5 10 L 771.5 175" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 858.5 10 L 858.5 175" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 945.5 10 L 945.5 175" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 1032.5 10 L 1032.5 175" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 1119.5 10 L 1119.5 175" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 74.5 10 L 74.5 175" opacity="1"></path></g><g class="highcharts-grid highcharts-yaxis-grid" data-z-index="1"><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 75 175.5 L 1120 175.5" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 75 120.5 L 1120 120.5" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 75 65.5 L 1120 65.5" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 75 9.5 L 1120 9.5" opacity="1"></path></g><rect fill="none" class="highcharts-plot-border" data-z-index="1" x="75" y="10" width="1045" height="165"></rect><g class="highcharts-axis highcharts-xaxis" data-z-index="2"><path fill="none" class="highcharts-axis-line" stroke="#ccd6eb" stroke-width="1" data-z-index="7" d="M 75 175.5 L 1120 175.5"></path></g><g class="highcharts-axis highcharts-yaxis" data-z-index="2"><text x="25.4375" data-z-index="7" text-anchor="middle" transform="translate(0,0) rotate(270 25.4375 92.5)" class="highcharts-axis-title" style="color:#666666;fill:#666666;" y="92.5">ล้านบาท</text><path fill="none" class="highcharts-axis-line" data-z-index="7" d="M 75 10 L 75 175"></path></g><path fill="none" class="highcharts-crosshair highcharts-crosshair-category" data-z-index="2" stroke="rgba(204,214,235,0.25)" stroke-width="87.08333333333333" style="pointer-events:none;" visibility="hidden" d="M 292.5 10 L 292.5 175"></path><g class="highcharts-series-group" data-z-index="3"><g class="highcharts-series highcharts-series-0 highcharts-line-series highcharts-color-0" data-z-index="0.1" opacity="1" transform="translate(75,10) scale(1 1)" clip-path="url(#highcharts-20cud33-1-)"><path fill="none" d="M 43.541666666667 95.19883057258201 L 130.625 138.729219102078 L 217.70833333333 46.45395236526001" class="highcharts-graph" data-z-index="1" stroke="#62c2cc" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"></path><path fill="none" d="M 43.541666666667 95.19883057258201 L 130.625 138.729219102078 L 217.70833333333 46.45395236526001" visibility="visible" data-z-index="2" class="highcharts-tracker-line" stroke-linecap="round" stroke-linejoin="round" stroke="rgba(192,192,192,0.0001)" stroke-width="22"></path></g><g class="highcharts-markers highcharts-series-0 highcharts-line-series highcharts-color-0 highcharts-tracker" data-z-index="0.1" opacity="1" transform="translate(75,10) scale(1 1)"><path fill="#62c2cc" d="M 217 46.45395236526001 A 0 0 0 1 1 217 46.45395236526001 Z" class="highcharts-halo highcharts-color-0" data-z-index="-1" fill-opacity="0.25" visibility="hidden"></path><path fill="#62c2cc" d="M 43 99.19883057258201 A 4 4 0 1 1 43.00399999933334 99.19882857258217 Z" opacity="1" class="highcharts-point highcharts-color-0" stroke-width="0.0016670205476124744"></path><path fill="#62c2cc" d="M 130 142.729219102078 A 4 4 0 1 1 130.00399999933333 142.72921710207817 Z" opacity="1" class="highcharts-point highcharts-color-0" stroke-width="0.0004835326609375912"></path><path fill="#62c2cc" d="M 217 50.45395236526001 A 4 4 0 1 1 217.00399999933333 50.45395036526018 Z" opacity="1" class="highcharts-point highcharts-color-0" stroke-width="0.00000497904986177097"></path></g><g class="highcharts-series highcharts-series-1 highcharts-line-series highcharts-color-1" data-z-index="0.1" opacity="1" transform="translate(75,10) scale(1 1)" clip-path="url(#highcharts-20cud33-1-)"><path fill="none" d="M 43.541666666667 108.13712046141399 L 130.625 148.783836786134 L 217.70833333333 59.133846308921974" class="highcharts-graph" data-z-index="1" stroke="#fdb813" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"></path><path fill="none" d="M 43.541666666667 108.13712046141399 L 130.625 148.783836786134 L 217.70833333333 59.133846308921974" visibility="visible" data-z-index="2" class="highcharts-tracker-line" stroke-linecap="round" stroke-linejoin="round" stroke="rgba(192,192,192,0.0001)" stroke-width="22"></path></g><g class="highcharts-markers highcharts-series-1 highcharts-line-series highcharts-color-1 highcharts-tracker" data-z-index="0.1" opacity="1" transform="translate(75,10) scale(1 1)"><path fill="#fdb813" d="M 217 59.133846308921974 A 0 0 0 1 1 217 59.133846308921974 Z" class="highcharts-halo highcharts-color-1" data-z-index="-1" fill-opacity="0.25" visibility="hidden"></path><path fill="#fdb813" d="M 43 104.13712046141399 L 47 108.13712046141399 L 43 112.13712046141399 L 39 108.13712046141399 Z" opacity="1" class="highcharts-point highcharts-color-1" stroke-width="0.0014205498696930885"></path><path fill="#fdb813" d="M 130 144.783836786134 L 134 148.783836786134 L 130 152.783836786134 L 126 148.783836786134 Z" opacity="1" class="highcharts-point highcharts-color-1" stroke-width="0.0004835326609375912"></path><path fill="#fdb813" d="M 217 55.133846308921974 L 221 59.133846308921974 L 217 63.133846308921974 L 213 59.133846308921974 Z" opacity="1" class="highcharts-point highcharts-color-1" stroke-width="0.00002242568935974243"></path></g><g class="highcharts-series highcharts-series-2 highcharts-line-series highcharts-color-2" data-z-index="0.1" opacity="1" transform="translate(75,10) scale(1 1)" clip-path="url(#highcharts-20cud33-1-)"><path fill="none" d="M 43.541666666667 80.31905791428198 L 130.625 137.174868795316 L 217.70833333333 45.10638271546" class="highcharts-graph" data-z-index="1" stroke="#f17022" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"></path><path fill="none" d="M 43.541666666667 80.31905791428198 L 130.625 137.174868795316 L 217.70833333333 45.10638271546" visibility="visible" data-z-index="2" class="highcharts-tracker-line" stroke-linecap="round" stroke-linejoin="round" stroke="rgba(192,192,192,0.0001)" stroke-width="22"></path></g><g class="highcharts-markers highcharts-series-2 highcharts-line-series highcharts-color-2 highcharts-tracker" data-z-index="0.1" opacity="1" transform="translate(75,10) scale(1 1)"><path fill="#f17022" d="M 217 45.10638271546 A 0 0 0 1 1 217 45.10638271546 Z" class="highcharts-halo highcharts-color-2" data-z-index="-1" fill-opacity="0.25" visibility="hidden"></path><path fill="#f17022" d="M 39 76.31905791428198 L 47 76.31905791428198 L 47 84.31905791428198 L 39 84.31905791428198 Z" opacity="1" class="highcharts-point highcharts-color-2" stroke-width="0.0014205498696930885"></path><path fill="#f17022" d="M 126 133.174868795316 L 134 133.174868795316 L 134 141.174868795316 L 126 141.174868795316 Z" opacity="1" class="highcharts-point highcharts-color-2" stroke-width="0.0004835326609375912"></path><path fill="#f17022" d="M 213 41.10638271546 L 221 41.10638271546 L 221 49.10638271546 L 213 49.10638271546 Z" opacity="1" class="highcharts-point highcharts-color-2" stroke-width="0.00002242568935974243"></path></g></g><text x="565" text-anchor="middle" class="highcharts-title" data-z-index="4" style="color:#333333;font-size:18px;fill:#333333;" y="24"></text><text x="565" text-anchor="middle" class="highcharts-subtitle" data-z-index="4" style="color:#666666;fill:#666666;" y="26"></text><text x="10" text-anchor="start" class="highcharts-caption" data-z-index="4" style="color:#666666;fill:#666666;" y="249"></text><g class="highcharts-legend highcharts-no-tooltip" data-z-index="7" transform="translate(356,206)"><rect fill="none" class="highcharts-legend-box" rx="0" ry="0" x="0" y="0" width="418" height="29" visibility="visible"></rect><g data-z-index="1"><g><g class="highcharts-legend-item highcharts-line-series highcharts-color-0 highcharts-series-0" data-z-index="1" transform="translate(8,3)"><path fill="none" d="M 0 11 L 16 11" class="highcharts-graph" stroke="#62c2cc" stroke-width="2"></path><path fill="#62c2cc" d="M 8 15 A 4 4 0 1 1 8.003999999333336 14.999998000000167 Z" class="highcharts-point" opacity="1"></path><text x="21" style="color:#333333;cursor:pointer;font-size:12px;font-weight:bold;font:14px csChatthaiUI;fill:#333333;" text-anchor="start" data-z-index="2" y="15">งบประมาณจัดซื้อจัดจ้าง</text></g><g class="highcharts-legend-item highcharts-line-series highcharts-color-1 highcharts-series-1" data-z-index="1" transform="translate(186.267578125,3)"><path fill="none" d="M 0 11 L 16 11" class="highcharts-graph" stroke="#fdb813" stroke-width="2"></path><path fill="#fdb813" d="M 8 7 L 12 11 L 8 15 L 4 11 Z" class="highcharts-point" opacity="1"></path><text x="21" y="15" style="color:#333333;cursor:pointer;font-size:12px;font-weight:bold;font:14px csChatthaiUI;fill:#333333;" text-anchor="start" data-z-index="2">มูลค่าโครงการรวม</text></g><g class="highcharts-legend-item highcharts-line-series highcharts-color-2 highcharts-series-2" data-z-index="1" transform="translate(330.408203125,3)"><path fill="none" d="M 0 11 L 16 11" class="highcharts-graph" stroke="#f17022" stroke-width="2"></path><path fill="#f17022" d="M 4 7 L 12 7 L 12 15 L 4 15 Z" class="highcharts-point" opacity="1"></path><text x="21" y="15" style="color:#333333;cursor:pointer;font-size:12px;font-weight:bold;font:14px csChatthaiUI;fill:#333333;" text-anchor="start" data-z-index="2">ราคากลาง</text></g></g></g></g><g class="highcharts-axis-labels highcharts-xaxis-labels" data-z-index="7"><text x="118.54166666666333" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="194" opacity="1">ต.ค. 67</text><text x="205.62500000000335" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="194" opacity="1">พ.ย. 67</text><text x="292.7083333333333" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="194" opacity="1">ธ.ค. 67</text><text x="379.79166666666333" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="194" opacity="1">ม.ค. 68</text><text x="466.8750000000033" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="194" opacity="1">ก.พ. 68</text><text x="553.9583333333334" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="194" opacity="1">มี.ค. 68</text><text x="641.0416666666633" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="194" opacity="1">เม.ย. 68</text><text x="728.1250000000034" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="194" opacity="1">พ.ค. 68</text><text x="815.2083333333334" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="194" opacity="1">มิ.ย. 68</text><text x="902.2916666666633" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="194" opacity="1">ก.ค. 68</text><text x="989.3750000000333" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="194" opacity="1">ส.ค. 68</text><text x="1076.4583333333333" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="194" opacity="1">ก.ย. 68</text></g><g class="highcharts-axis-labels highcharts-yaxis-labels" data-z-index="7"><text x="60" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="181" opacity="1">75k</text><text x="60" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="126" opacity="1">100k</text><text x="60" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="71" opacity="1">125k</text><text x="60" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="16" opacity="1">150k</text></g><g class="highcharts-label highcharts-tooltip highcharts-color-0" style="cursor:default;white-space:nowrap;pointer-events:none;" data-z-index="8" transform="translate(309,-9999)" opacity="0" visibility="hidden"><path fill="none" class="highcharts-label-box highcharts-tooltip-box highcharts-shadow" d="M 3.5 0.5 L 303.5 0.5 C 306.5 0.5 306.5 0.5 306.5 3.5 L 306.5 90.5 C 306.5 93.5 306.5 93.5 303.5 93.5 L 3.5 93.5 C 0.5 93.5 0.5 93.5 0.5 90.5 L 0.5 3.5 C 0.5 0.5 0.5 0.5 3.5 0.5" stroke="#000000" stroke-opacity="0.049999999999999996" stroke-width="5" transform="translate(1, 1)"></path><path fill="none" class="highcharts-label-box highcharts-tooltip-box highcharts-shadow" d="M 3.5 0.5 L 303.5 0.5 C 306.5 0.5 306.5 0.5 306.5 3.5 L 306.5 90.5 C 306.5 93.5 306.5 93.5 303.5 93.5 L 3.5 93.5 C 0.5 93.5 0.5 93.5 0.5 90.5 L 0.5 3.5 C 0.5 0.5 0.5 0.5 3.5 0.5" stroke="#000000" stroke-opacity="0.09999999999999999" stroke-width="3" transform="translate(1, 1)"></path><path fill="none" class="highcharts-label-box highcharts-tooltip-box highcharts-shadow" d="M 3.5 0.5 L 303.5 0.5 C 306.5 0.5 306.5 0.5 306.5 3.5 L 306.5 90.5 C 306.5 93.5 306.5 93.5 303.5 93.5 L 3.5 93.5 C 0.5 93.5 0.5 93.5 0.5 90.5 L 0.5 3.5 C 0.5 0.5 0.5 0.5 3.5 0.5" stroke="#000000" stroke-opacity="0.15" stroke-width="1" transform="translate(1, 1)"></path><path fill="rgba(247,247,247,0.85)" class="highcharts-label-box highcharts-tooltip-box" d="M 3.5 0.5 L 303.5 0.5 C 306.5 0.5 306.5 0.5 306.5 3.5 L 306.5 90.5 C 306.5 93.5 306.5 93.5 303.5 93.5 L 3.5 93.5 C 0.5 93.5 0.5 93.5 0.5 90.5 L 0.5 3.5 C 0.5 0.5 0.5 0.5 3.5 0.5" stroke="#62c2cc" stroke-width="1"></path></g></svg><div class="highcharts-label highcharts-tooltip highcharts-color-0" style="position: absolute; left: 309px; top: -9999px; opacity: 0; cursor: default; pointer-events: none; visibility: hidden;"><span data-z-index="1" style="position: absolute; font-family: csChatthaiUI; font-size: 14px; white-space: nowrap; color: rgb(51, 51, 51); margin-left: 0px; margin-top: 0px; left: 8px; top: 8px;"><table class="line-tooltip"><tbody><tr><th colspan="3">ธ.ค. 67</th></tr><tr><td>งบประมาณจัดซื้อจัดจ้าง: </td><td>128,884.57</td><td>ล้านบาท</td></tr><tr><td>มูลค่าโครงการรวม: </td><td>123,120.98</td><td>ล้านบาท</td></tr><tr><td>ราคากลาง: </td><td>129,497.10</td><td>ล้านบาท</td></tr></tbody></table></span></div></div></div>
                        </div>
                    </div>
    <!-- <div class="col-md-4">
    <canvas id="budgetPieChart" style="width: 100%; height: 300px;"></canvas>
</div>
<div class="col-md-4">
    <canvas id="budgetBarChart" style="width: 100%; height: 300px;"></canvas>
</div> -->


<div class="col-sm-12">
        <div class="widget-box">
        <h2 class="widget-title">ผลการจัดซื้อจัดจ้างรายเดือน</h2>
        <div id="chart-province" data-highcharts-chart="1" style="overflow: hidden;">
        <div id="highcharts-20cud33-40" dir="ltr" class="highcharts-container " style="position: relative; overflow: hidden; width: 1130px; height: 400px; text-align: left; line-height: normal; z-index: 0; -webkit-tap-highlight-color: rgba(0, 0, 0, 0); user-select: none; touch-action: manipulation; outline: none; font-family: csChatthaiUI; font-size: 12px;"><svg version="1.1" class="highcharts-root" style="font-family:&quot;csChatthaiUI&quot;;font-size:12px;" xmlns="http://www.w3.org/2000/svg" width="1130" height="400" viewBox="0 0 1130 400"><desc>Created with Highcharts 9.1.2</desc><defs><clipPath id="highcharts-20cud33-41-"><rect x="0" y="0" width="319" height="932" fill="none"></rect></clipPath></defs><rect fill="#ffffff" class="highcharts-background" x="0" y="0" width="1130" height="400" rx="0" ry="0"></rect><rect fill="none" class="highcharts-plot-background" x="99" y="10" width="932" height="319"></rect><g class="highcharts-grid highcharts-xaxis-grid" data-z-index="1"><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 297.5 L 1031 297.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 265.5 L 1031 265.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 233.5 L 1031 233.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 201.5 L 1031 201.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 170.5 L 1031 170.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 138.5 L 1031 138.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 106.5 L 1031 106.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 74.5 L 1031 74.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 42.5 L 1031 42.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 10.5 L 1031 10.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 329.5 L 1031 329.5" opacity="1"></path></g><g class="highcharts-grid highcharts-xaxis-grid" data-z-index="1"><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 297.5 L 1031 297.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 265.5 L 1031 265.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 233.5 L 1031 233.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 201.5 L 1031 201.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 170.5 L 1031 170.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 138.5 L 1031 138.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 106.5 L 1031 106.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 74.5 L 1031 74.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 42.5 L 1031 42.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 10.5 L 1031 10.5" opacity="1"></path><path fill="none" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 99 329.5 L 1031 329.5" opacity="1"></path></g><g class="highcharts-grid highcharts-yaxis-grid" data-z-index="1"><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 98.5 10 L 98.5 329" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 183.5 10 L 183.5 329" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 267.5 10 L 267.5 329" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 352.5 10 L 352.5 329" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 437.5 10 L 437.5 329" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 522.5 10 L 522.5 329" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 606.5 10 L 606.5 329" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 691.5 10 L 691.5 329" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 776.5 10 L 776.5 329" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 861.5 10 L 861.5 329" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 945.5 10 L 945.5 329" opacity="1"></path><path fill="none" stroke="#e6e6e6" stroke-width="1" stroke-dasharray="none" data-z-index="1" class="highcharts-grid-line" d="M 1031.5 10 L 1031.5 329" opacity="1"></path></g><rect fill="none" class="highcharts-plot-border" data-z-index="1" x="99" y="10" width="932" height="319"></rect><g class="highcharts-axis highcharts-xaxis" data-z-index="2"><path fill="none" class="highcharts-axis-line" stroke="#ccd6eb" stroke-width="1" data-z-index="7" d="M 98.5 10 L 98.5 329"></path></g><g class="highcharts-axis highcharts-xaxis" data-z-index="2"><path fill="none" class="highcharts-axis-line" stroke="#ccd6eb" stroke-width="1" data-z-index="7" d="M 1031.5 10 L 1031.5 329"></path></g><g class="highcharts-axis highcharts-yaxis" data-z-index="2"><path fill="none" class="highcharts-axis-line" data-z-index="7" d="M 99 329 L 1031 329"></path></g><g class="highcharts-series-group" data-z-index="3"><g class="highcharts-series highcharts-series-0 highcharts-bar-series highcharts-color-0 highcharts-tracker" data-z-index="0.1" opacity="1" transform="translate(1031,329) rotate(90) scale(-1,1) scale(1 1)" clip-path="url(#highcharts-20cud33-41-)" width="932" height="319"><rect x="7.5" y="339.5" width="16" height="530" fill="rgb(98,194,204)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-negative highcharts-color-0"></rect><rect x="39.5" y="339.5" width="16" height="38" fill="rgb(98,194,204)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-negative highcharts-color-0"></rect><rect x="71.5" y="339.5" width="16" height="36" fill="rgb(98,194,204)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-negative highcharts-color-0"></rect><rect x="103.5" y="339.5" width="16" height="33" fill="rgb(98,194,204)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-negative highcharts-color-0"></rect><rect x="135.5" y="339.5" width="16" height="29" fill="rgb(98,194,204)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-negative highcharts-color-0"></rect><rect x="167.5" y="339.5" width="16" height="27" fill="rgb(98,194,204)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-negative highcharts-color-0"></rect><rect x="199.5" y="339.5" width="16" height="27" fill="rgb(98,194,204)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-negative highcharts-color-0"></rect><rect x="231.5" y="339.5" width="16" height="26" fill="rgb(98,194,204)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-negative highcharts-color-0"></rect><rect x="262.5" y="339.5" width="16" height="23" fill="rgb(98,194,204)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-negative highcharts-color-0"></rect><rect x="294.5" y="339.5" width="16" height="19" fill="rgb(98,194,204)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-negative highcharts-color-0"></rect></g><g class="highcharts-markers highcharts-series-0 highcharts-bar-series highcharts-color-0" data-z-index="0.1" opacity="1" transform="translate(1031,329) rotate(90) scale(-1,1) scale(1 1)" clip-path="none"></g><g class="highcharts-series highcharts-series-1 highcharts-bar-series highcharts-color-1 highcharts-tracker" data-z-index="0.1" opacity="1" transform="translate(1031,329) rotate(90) scale(-1,1) scale(1 1)" clip-path="url(#highcharts-20cud33-41-)" width="932" height="319"><rect x="7.5" y="110.5" width="16" height="229" fill="rgb(253,184,19)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-color-1"></rect><rect x="39.5" y="267.5" width="16" height="72" fill="rgb(253,184,19)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-color-1"></rect><rect x="71.5" y="170.5" width="16" height="169" fill="rgb(253,184,19)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-color-1"></rect><rect x="103.5" y="182.5" width="16" height="157" fill="rgb(253,184,19)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-color-1"></rect><rect x="135.5" y="237.5" width="16" height="102" fill="rgb(253,184,19)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-color-1"></rect><rect x="167.5" y="234.5" width="16" height="105" fill="rgb(253,184,19)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-color-1"></rect><rect x="199.5" y="299.5" width="16" height="40" fill="rgb(253,184,19)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-color-1"></rect><rect x="231.5" y="222.5" width="16" height="117" fill="rgb(253,184,19)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-color-1"></rect><rect x="262.5" y="216.5" width="16" height="123" fill="rgb(253,184,19)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-color-1"></rect><rect x="294.5" y="242.5" width="16" height="97" fill="rgb(253,184,19)" stroke="#ffffff" stroke-width="1" opacity="1" class="highcharts-point highcharts-color-1"></rect></g><g class="highcharts-markers highcharts-series-1 highcharts-bar-series highcharts-color-1" data-z-index="0.1" opacity="1" transform="translate(1031,329) rotate(90) scale(-1,1) scale(1 1)" clip-path="none"></g></g><text x="565" text-anchor="middle" class="highcharts-title" data-z-index="4" style="color:#333333;font-size:18px;fill:#333333;" y="24"></text><text x="565" text-anchor="middle" class="highcharts-subtitle" data-z-index="4" style="color:#666666;fill:#666666;" y="24"></text><text x="10" text-anchor="start" class="highcharts-caption" data-z-index="4" style="color:#666666;fill:#666666;" y="397"></text><g class="highcharts-legend highcharts-no-tooltip" data-z-index="7" transform="translate(351,360)"><rect fill="none" class="highcharts-legend-box" rx="0" ry="0" x="0" y="0" width="427" height="25" visibility="visible"></rect><g data-z-index="1"><g><g class="highcharts-legend-item highcharts-bar-series highcharts-color-0 highcharts-series-0" data-z-index="1" transform="translate(8,3)"><text x="21" style="color:#333333;cursor:pointer;font-size:12px;font-weight:bold;font:14px csChatthaiUI;fill:#333333;" text-anchor="start" data-z-index="2" y="15">มูลค่าโครงการ</text><rect x="2" y="4" width="12" height="12" fill="#62c2cc" rx="6" ry="6" class="highcharts-point" data-z-index="3"></rect></g><g class="highcharts-legend-item highcharts-bar-series highcharts-color-1 highcharts-series-1" data-z-index="1" transform="translate(310.953125,3)"><text x="21" y="15" style="color:#333333;cursor:pointer;font-size:12px;font-weight:bold;font:14px csChatthaiUI;fill:#333333;" text-anchor="start" data-z-index="2">จำนวนโครงการ</text><rect x="2" y="4" width="12" height="12" fill="#fdb813" rx="6" ry="6" class="highcharts-point" data-z-index="3"></rect></g></g></g></g><g class="highcharts-axis-labels highcharts-xaxis-labels" data-z-index="7"><text x="84" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="319" opacity="1">กรุงเทพมหานคร</text><text x="84" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="287" opacity="1">นนทบุรี</text><text x="84" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="254" opacity="1">เชียงใหม่</text><text x="84" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="223" opacity="1">นครราชสีมา</text><text x="84" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="191" opacity="1">ชลบุรี</text><text x="84" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="159" opacity="1">สงขลา</text><text x="84" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="127" opacity="1">สมุทรปราการ</text><text x="84" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="95" opacity="1">ขอนแก่น</text><text x="84" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="63" opacity="1">อุบลราชธานี</text><text x="84" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="end" transform="translate(0,0)" y="31" opacity="1">อุดรธานี</text></g><g class="highcharts-axis-labels highcharts-xaxis-labels" data-z-index="7"><text x="1046" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="start" transform="translate(0,0)" y="319" opacity="1">กรุงเทพมหานคร</text><text x="1046" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="start" transform="translate(0,0)" y="287" opacity="1">นนทบุรี</text><text x="1046" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="start" transform="translate(0,0)" y="254" opacity="1">เชียงใหม่</text><text x="1046" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="start" transform="translate(0,0)" y="223" opacity="1">นครราชสีมา</text><text x="1046" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="start" transform="translate(0,0)" y="191" opacity="1">ชลบุรี</text><text x="1046" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="start" transform="translate(0,0)" y="159" opacity="1">สงขลา</text><text x="1046" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="start" transform="translate(0,0)" y="127" opacity="1">สมุทรปราการ</text><text x="1046" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="start" transform="translate(0,0)" y="95" opacity="1">ขอนแก่น</text><text x="1046" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="start" transform="translate(0,0)" y="63" opacity="1">อุบลราชธานี</text><text x="1046" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="start" transform="translate(0,0)" y="31" opacity="1">อุดรธานี</text></g><g class="highcharts-axis-labels highcharts-yaxis-labels" data-z-index="7"><text x="99" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="348" opacity="1">140k</text><text x="183.72727272727" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="348" opacity="1">120k</text><text x="268.45454545455" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="348" opacity="1">100k</text><text x="353.18181818182" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="348" opacity="1">80k</text><text x="437.90909090909" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="348" opacity="1">60k</text><text x="522.63636363636" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="348" opacity="1">40k</text><text x="607.36363636364" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="348" opacity="1">20k</text><text x="692.09090909091" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="348" opacity="1">0</text><text x="776.81818181818" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="348" opacity="1">20k</text><text x="861.54545454545" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="348" opacity="1">40k</text><text x="946.27272727273" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="348" opacity="1">60k</text><text x="1031" style="color:#666666;cursor:default;font-size:11px;fill:#666666;" text-anchor="middle" transform="translate(0,0)" y="348" opacity="1">80k</text></g><g class="highcharts-label highcharts-tooltip highcharts-color-0" style="cursor:default;white-space:nowrap;pointer-events:none;" data-z-index="8" transform="translate(698,-9999)" opacity="0" visibility="hidden"><path fill="none" class="highcharts-label-box highcharts-tooltip-box highcharts-shadow" d="M 3.5 0.5 L 251.5 0.5 C 254.5 0.5 254.5 0.5 254.5 3.5 L 254.5 74.5 C 254.5 77.5 254.5 77.5 251.5 77.5 L 3.5 77.5 C 0.5 77.5 0.5 77.5 0.5 74.5 L 0.5 3.5 C 0.5 0.5 0.5 0.5 3.5 0.5" stroke="#000000" stroke-opacity="0.049999999999999996" stroke-width="5" transform="translate(1, 1)"></path><path fill="none" class="highcharts-label-box highcharts-tooltip-box highcharts-shadow" d="M 3.5 0.5 L 251.5 0.5 C 254.5 0.5 254.5 0.5 254.5 3.5 L 254.5 74.5 C 254.5 77.5 254.5 77.5 251.5 77.5 L 3.5 77.5 C 0.5 77.5 0.5 77.5 0.5 74.5 L 0.5 3.5 C 0.5 0.5 0.5 0.5 3.5 0.5" stroke="#000000" stroke-opacity="0.09999999999999999" stroke-width="3" transform="translate(1, 1)"></path><path fill="none" class="highcharts-label-box highcharts-tooltip-box highcharts-shadow" d="M 3.5 0.5 L 251.5 0.5 C 254.5 0.5 254.5 0.5 254.5 3.5 L 254.5 74.5 C 254.5 77.5 254.5 77.5 251.5 77.5 L 3.5 77.5 C 0.5 77.5 0.5 77.5 0.5 74.5 L 0.5 3.5 C 0.5 0.5 0.5 0.5 3.5 0.5" stroke="#000000" stroke-opacity="0.15" stroke-width="1" transform="translate(1, 1)"></path><path fill="rgba(247,247,247,0.85)" class="highcharts-label-box highcharts-tooltip-box" d="M 3.5 0.5 L 251.5 0.5 C 254.5 0.5 254.5 0.5 254.5 3.5 L 254.5 74.5 C 254.5 77.5 254.5 77.5 251.5 77.5 L 3.5 77.5 C 0.5 77.5 0.5 77.5 0.5 74.5 L 0.5 3.5 C 0.5 0.5 0.5 0.5 3.5 0.5" stroke="#62c2cc" stroke-width="1"></path></g></svg><div class="highcharts-label highcharts-tooltip highcharts-color-0" style="position: absolute; left: 698px; top: -9999px; opacity: 0; cursor: default; pointer-events: none; visibility: hidden;"><span data-z-index="1" style="position: absolute; font-family: csChatthaiUI; font-size: 14px; white-space: nowrap; color: rgb(51, 51, 51); margin-left: 0px; margin-top: 0px; left: 8px; top: 8px;"><table class="bar-tooltip"><tbody><tr><th colspan="4">กรุงเทพมหานคร</th></tr><tr><td><span style="color:#62c2cc">มูลค่าโครงการ</span></td><td>125,089.89</td><td>ล้านบาท</td></tr><tr><td><span style="color:#fdb813">จำนวนโครงการ</span></td><td>53,988</td><td>โครงการ</td></tr></tbody></table></span></div></div></div>
                
         </div>  
         
       
         <div class="container">
    <div class="row ">
        <!-- Widget 1 -->
        <div class="col-md-4">
            <div class="widget-box1">
                <h2 class="widget-title1">10. จังหวัดที่มีโครงการมากที่สุด (ไม่รวม กทม.)</h2>
                <div class="widget-content2 dark-border-div">
                    <div class="widget-name2 tooltip-style widget-big-name">
                        <i class="fa-solid fa-location-dot"></i>
                        <a href="/budget?year=2568&amp;prov=50&amp;sort=DESC" target="_blank"> 1. เชียงใหม่</a>
                    </div>
                    
                    <div class="text-right">
                        <div class="focus-number">3.62 %</div>
                        <div class="mini-number">39,805 โครงการ</div>
                        
                    </div>
        
                </div>
                        <!-- เนื้อหาที่จะซ่อน/แสดง -->
        <div id="widget1" class="toggle-content">
            <ol>
                <li>โครงการ 1 - 28,679 ล้านบาท</li>
                <li>โครงการ 2 - 1,185 ล้านบาท</li>
                <li>โครงการ 3 - 1,165 ล้านบาท</li>
                <li>โครงการ 4 - 948 ล้านบาท</li>
                <li>โครงการ 5 - 899 ล้านบาท</li>
                <li>โครงการ 6 - 836 ล้านบาท</li>
                <li>โครงการ 7 - 752 ล้านบาท</li>
                <li>โครงการ 8 - 703 ล้านบาท</li>
                <li>โครงการ 9 - 699 ล้านบาท</li>
                <li>โครงการ 10 - 699 ล้านบาท</li>
            </ol>
        </div>
                <div class="dark-footer-div" onclick="toggleMenu('widget1',this)">
                <i class="fa-sharp fa-solid fa-caret-down"></i></div>
            </div>
        </div>
        
        <!-- Widget 2 -->
        <div class="col-md-4">
            <div class="widget-box1">
                <h2 class="widget-title1">10. จังหวัดที่มีโครงการมากที่สุด (ไม่รวม กทม.)</h2>
                <div class="widget-content2 dark-border-div">
                    <div class="widget-name2 tooltip-style widget-big-name">
                        <i class="fa-solid fa-location-dot"></i>
                        <a href="#"> 2. นนทบุรี</a>
                    </div>
                    <div class="text-right">
                        <div class="focus-number">0.32 %</div>
                        <div class="mini-number">992.80 ล้านโครงการ</div>
                    </div>
                    
                </div>
                              <!-- เนื้อหาที่จะซ่อน/แสดง -->
        <div id="widget2" class="toggle-content">
            <ol>
                <li>โครงการ 1 - 28,679 ล้านบาท</li>
                <li>โครงการ 2 - 1,185 ล้านบาท</li>
                <li>โครงการ 3 - 1,165 ล้านบาท</li>
                <li>โครงการ 4 - 948 ล้านบาท</li>
                <li>โครงการ 5 - 899 ล้านบาท</li>
                <li>โครงการ 6 - 836 ล้านบาท</li>
                <li>โครงการ 7 - 752 ล้านบาท</li>
                <li>โครงการ 8 - 703 ล้านบาท</li>
                <li>โครงการ 9 - 699 ล้านบาท</li>
                <li>โครงการ 10 - 699 ล้านบาท</li>
            </ol>
        </div>
                <div class="dark-footer-div" onclick="toggleMenu('widget2',this)">
                <i class="fa-sharp fa-solid fa-caret-down"></i></div>
            </div>
        </div>

         <!-- Widget 3 -->
         <div class="col-md-4">
            <div class="widget-box1">
                <h2 class="widget-title1">10. จังหวัดที่มีโครงการมากที่สุด (ไม่รวม กทม.)</h2>
                <div class="widget-content2 dark-border-div">
                    <div class="widget-name2 tooltip-style widget-big-name">
                        <i class="fa-solid fa-location-dot"></i>
                        <a href="#"> 3. เชียงใหม่</a>
                    </div>
                    <div class="text-right">
                        <div class="focus-number">0.12 %</div>
                        <div class="mini-number">356.50 ล้านโครงการ</div>
                    </div>
                </div>
                 <!-- เนื้อหาที่จะซ่อน/แสดง -->
        <div id="widget3" class="toggle-content">
            <ol>
                <li>โครงการ 1 - 28,679 ล้านบาท</li>
                <li>โครงการ 2 - 1,185 ล้านบาท</li>
                <li>โครงการ 3 - 1,165 ล้านบาท</li>
                <li>โครงการ 4 - 948 ล้านบาท</li>
                <li>โครงการ 5 - 899 ล้านบาท</li>
                <li>โครงการ 6 - 836 ล้านบาท</li>
                <li>โครงการ 7 - 752 ล้านบาท</li>
                <li>โครงการ 8 - 703 ล้านบาท</li>
                <li>โครงการ 9 - 699 ล้านบาท</li>
                <li>โครงการ 10 - 699 ล้านบาท</li>
            </ol>
        </div>
        <div class="dark-footer-div" onclick="toggleMenu('widget3',this)"><i class="fa-sharp fa-solid fa-caret-down"></i></div>
            </div>
        </div>

        
        </div>
</div>



<div class="container mt-4">
    <div class="row g-3">
        <!-- วิดเจ็ตแรก -->
        <div class="col-md-6">
            <div class="chart-card">
                <h5>มูลค่าโครงการ จำแนกตามประเภทโครงการ</h5>
                <div class="chart-container">
                    <canvas id="chart1"></canvas>
                </div>
            </div>
        </div>

        <!-- วิดเจ็ตที่สอง -->
        <div class="col-md-6">
            <div class="chart-card">
                <h5>จำนวนโครงการ จำแนกตามประเภทโครงการ</h5>
                <div class="chart-container">
                    <canvas id="chart2"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // ข้อมูลของกราฟ
    const labels = ["จ้างก่อสร้าง", "จ้างทำของ/จ้างเหมาบริการ", "จ้างที่ปรึกษา", "จ้างออกแบบ"];
    const data1 = [30, 40, 20, 10]; // ข้อมูลมูลค่าโครงการ
    const data2 = [5, 25, 60, 10];  // ข้อมูลจำนวนโครงการ

    // ตั้งค่า Chart.js
    function createDoughnutChart(canvasId, data) {
        new Chart(document.getElementById(canvasId), {
            type: "doughnut",
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: ["#50c8ff", "#ffbb33", "#ff4444", "#00c851"]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: "bottom" }
                }
            }
        });
    }

    // สร้างกราฟ
    createDoughnutChart("chart1", data1);
    createDoughnutChart("chart2", data2);
</script>
    
<script>
   function changeYear(year) {
    // เปลี่ยนค่าใน hidden input
    document.getElementById('budget-year').value = year;

    // เปลี่ยนแปลงข้อความในปุ่ม dropdown
    document.getElementById('budget-year-button').innerHTML = year + ' <span class="caret"></span>';

    // อัปเดตค่าใน URL พร้อมเปลี่ยนปีที่เลือก
    window.location.href = "?year=" + year;
}


function checkDataAndToggleButtons() {
    let tableBody = document.querySelector("#projectTable tbody");
    let hasData = tableBody && tableBody.rows.length > 0;

    document.getElementById("exportExcelBtn").disabled = !hasData;
    document.getElementById("exportPdfBtn").disabled = !hasData;
}


    // ✅ ตรวจสอบข้อมูล **หลังจากหน้าเว็บโหลด**
document.addEventListener("DOMContentLoaded", function () {
    setTimeout(checkDataAndToggleButtons, 500); // หน่วงเวลาเล็กน้อยเพื่อให้โหลดข้อมูลจาก PHP ทัน
});
// ✅ ตรวจจับการเปลี่ยนแปลงของ `tbody`
const observer = new MutationObserver(checkDataAndToggleButtons);
observer.observe(document.querySelector("#projectTable tbody"), { childList: true });
    function exportToExcel() {
        let table = document.querySelector("table");
        let wb = XLSX.utils.table_to_book(table, { sheet: "งบประมาณ" });
        XLSX.writeFile(wb, "งบประมาณ.xlsx");
    }

    function exportToPDF() {
        const { jsPDF } = window.jspdf;
        let doc = new jsPDF();
        doc.text("รายงานงบประมาณ", 20, 10);
        doc.save("งบประมาณ.pdf");
    }

    function renderCharts() {
        const months = Array.from(document.querySelectorAll("#budgetTableBody tr")).map(row => row.cells[0].textContent);
        const sum_budget = Array.from(document.querySelectorAll("#budgetTableBody tr")).map(row => parseFloat(row.cells[1].textContent.replace(/,/g, "")));
        const sum_used = Array.from(document.querySelectorAll("#budgetTableBody tr")).map(row => parseFloat(row.cells[2].textContent.replace(/,/g, "")));
        const expect_price = Array.from(document.querySelectorAll("#budgetTableBody tr")).map(row => parseFloat(row.cells[3].textContent.replace(/,/g, "")));

        new Chart(document.getElementById('budgetPieChart'), {
            type: 'pie',
            data: {
                labels: months,
                datasets: [{ data: sum_used, backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4CAF50', '#9C27B0', '#FF5722'] }]
            }
        });

        new Chart(document.getElementById('budgetBarChart'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    { label: 'งบประมาณ (บาท)', data: sum_budget, backgroundColor: 'rgba(54, 162, 235, 0.5)' },
                    { label: 'ใช้ไป (บาท)', data: sum_used, backgroundColor: 'rgba(255, 99, 132, 0.5)' },
                    { label: 'คาดการณ์ (บาท)', data: expect_price, backgroundColor: 'rgba(255, 206, 86, 0.5)' }
                ]
            }
        });
    }

    document.addEventListener("DOMContentLoaded", renderCharts);
</script>
<script>
function showLoginModal() {
    Swal.fire({
        title: '<h2 style="color: #333; font-weight: 600;">เข้าสู่ระบบ</h2>',
        html: `
            <style>
                .swal2-popup {
                    border-radius: 12px;
                    padding: 20px;
                    width: 350px;
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
                }
                .swal2-title {
                    font-size: 20px !important;
                }
                .swal2-input {
                    font-size: 16px;
                    padding: 12px;
                    width: 100%;
                    border-radius: 8px;
                    border: 1px solid #ccc;
                    box-sizing: border-box;
                    transition: all 0.2s ease-in-out;
                    background-color: #f9f9f9;
                }
                .swal2-input:focus {
                    border-color: #6c5ce7;
                    outline: none;
                    box-shadow: 0 0 5px rgba(108, 92, 231, 0.5);
                    background-color: #fff;
                }
                .password-container {
                    position: relative;
                    width: 100%;
                }
                .password-container input {
                    padding-right: 40px;
                }
                .password-container i {
                    position: absolute;
                    right: 0px;
                    top: 50%;
                    transform: translateY(-10%);
                    cursor: pointer;
                    color: #888;
                    font-size: 18px;
                    transition: color 0.3s;
                }
                .password-container i:hover {
                    color: #6c5ce7;
                }
                .swal2-confirm {
                    background-color: #6c5ce7 !important;
                    color: white !important;
                    border-radius: 8px !important;
                    font-size: 16px !important;
                    padding: 10px 20px !important;
                    transition: background 0.3s;
                }
                .swal2-confirm:hover {
                    background-color: #5a4bd3 !important;
                }
                .swal2-cancel {
                    background-color: #ccc !important;
                    color: #333 !important;
                    border-radius: 8px !important;
                    font-size: 16px !important;
                    padding: 10px 20px !important;
                }
                .swal2-cancel:hover {
                    background-color: #bbb !important;
                }
            </style>
            <input type="text" id="username" class="swal2-input" placeholder="ชื่อผู้ใช้">
            <div class="password-container">
                <input type="password" id="password" class="swal2-input" placeholder="รหัสผ่าน">
                <i id="eye-icon" class="fa-solid fa-eye"></i>
            </div>
        `,
        confirmButtonText: 'เข้าสู่ระบบ',
        showCancelButton: true,
        cancelButtonText: 'ยกเลิก',
        customClass: {
            popup: 'custom-swal-popup'
        },
        didOpen: () => {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');

            eyeIcon.addEventListener('click', () => {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        },
        preConfirm: () => {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            if (!username || !password) {
                Swal.showValidationMessage('กรุณากรอกชื่อผู้ใช้และรหัสผ่าน');
                return false;
            }

            return { username, password };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            login(result.value.username, result.value.password);
        }
    });
}


function login(username, password) {
    fetch('login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `username=${encodeURIComponent(username)}&password=${encodeURIComponent(password)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'เข้าสู่ระบบสำเร็จ',
                text: `ยินดีต้อนรับ, ${data.username}!`
            }).then(() => {
                window.location.href = data.redirect; // เปลี่ยนเส้นทางไปยัง URL ที่ได้รับจากเซิร์ฟเวอร์
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'เข้าสู่ระบบล้มเหลว',
                text: data.message
            });
        }
    })
    .catch(() => {
        Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด',
            text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์'
        });
    });
}

function toggleMenu(id) {
    var content = document.getElementById(id);
    var footer = document.querySelector(".dark-footer-div");
    
    // Toggle แสดง/ซ่อนข้อมูล
    content.classList.toggle("active");

    // Toggle การหมุนของไอคอน
    footer.classList.toggle("active");
}



</script>

</body>
</html>
<!-- <script>
    function renderCharts() {
        const months = Array.from(document.querySelectorAll("#budgetTableBody tr")).map(row => row.cells[0].textContent);
        const sum_budget = Array.from(document.querySelectorAll("#budgetTableBody tr")).map(row => parseFloat(row.cells[1].textContent.replace(/,/g, "")));
        const sum_used = Array.from(document.querySelectorAll("#budgetTableBody tr")).map(row => parseFloat(row.cells[2].textContent.replace(/,/g, "")));
        const expect_price = Array.from(document.querySelectorAll("#budgetTableBody tr")).map(row => parseFloat(row.cells[3].textContent.replace(/,/g, "")));

        new Chart(document.getElementById('budgetPieChart'), {
            type: 'pie',
            data: {
                labels: months,
                datasets: [{ data: sum_used, backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4CAF50', '#9C27B0', '#FF5722'] }]
            }
        });

        new Chart(document.getElementById('budgetBarChart'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    { label: 'งบประมาณ (บาท)', data: sum_budget, backgroundColor: 'rgba(54, 162, 235, 0.5)' },
                    { label: 'ใช้ไป (บาท)', data: sum_used, backgroundColor: 'rgba(255, 99, 132, 0.5)' },
                    { label: 'คาดการณ์ (บาท)', data: expect_price, backgroundColor: 'rgba(255, 206, 86, 0.5)' }
                ]
            }
        });

        new Chart(document.getElementById('budgetLineChart'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    { label: 'งบประมาณ (บาท)', data: sum_budget, borderColor: 'rgba(54, 162, 235, 1)', fill: false },
                    { label: 'ใช้ไป (บาท)', data: sum_used, borderColor: 'rgba(255, 99, 132, 1)', fill: false },
                    { label: 'คาดการณ์ (บาท)', data: expect_price, borderColor: 'rgba(255, 206, 86, 1)', fill: false }
                ]
            }
        });
    }

    document.addEventListener("DOMContentLoaded", renderCharts);
</script> -->