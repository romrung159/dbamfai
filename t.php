<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ฟอร์มบันทึกข้อมูลโครงการ</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .card {
            max-width: array_multisort(array_keys($data), SORT_ASC, $data);
            margin: 0 auto;
        }
    </style>

    <script>
       
        document.addEventListener("DOMContentLoaded", function () {
    fetch("get_locations.php?type=province")
        .then(response => response.json())
        .then(data => {
            let select = document.getElementById("province");
            select.innerHTML = `<option value="">เลือกจังหวัด</option>` +
                data.map(prov => `<option value="${prov.id}">${prov.name_th}</option>`).join('');
        });

    document.getElementById("province").addEventListener("change", function () {
        fetch(`get_locations.php?type=amphoe&parent_id=${this.value}`)
            .then(response => response.json())
            .then(data => {
                let select = document.getElementById("amphoe");
                select.innerHTML = `<option value="">เลือกอำเภอ</option>` +
                    data.map(amp => `<option value="${amp.id}">${amp.name_th}</option>`).join('');
                document.getElementById("tambon").innerHTML = `<option value="">เลือกตำบล</option>`;
            });
    });

    document.getElementById("amphoe").addEventListener("change", function () {
        fetch(`get_locations.php?type=tambon&parent_id=${this.value}`)
            .then(response => response.json())
            .then(data => {
                let select = document.getElementById("tambon");
                select.innerHTML = `<option value="">เลือกตำบล</option>` +
                    data.map(tam => `<option value="${tam.id}">${tam.name_th}</option>`).join('');
            });
    });
});



        function submitForm(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById("project-form"));
            
            Swal.fire({
        title: "ยืนยันการบันทึกข้อมูล?",
        text: "คุณแน่ใจหรือไม่ว่าต้องการบันทึกข้อมูลนี้",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "ใช่, บันทึกเลย!",
        cancelButtonText: "ยกเลิก"
    }).then((result) => {
        if (result.isConfirmed) {
            // ส่งข้อมูลไปที่เซิร์ฟเวอร์
            fetch("save_project.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    Swal.fire({
                icon: "success",
                title: "สำเร็จ!",
                text: data.message,
                confirmButtonText: "ตกลง"
            }).then(() => {
                document.getElementById("project-form").reset();
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "เกิดข้อผิดพลาด!",
                text: data.message,
                confirmButtonText: "ลองอีกครั้ง"
            });
        }
    })
    .catch(error => {
        Swal.fire({
            icon: "error",
            title: "ข้อผิดพลาด!",
            text: "เกิดข้อผิดพลาดในการเชื่อมต่อกับเซิร์ฟเวอร์",
            confirmButtonText: "ตกลง"
        });
    });
}
});
}
    </script>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h4>ฟอร์มบันทึกข้อมูลโครงการ</h4>
            </div>
            <div class="card-body">
                <form id="project-form" onsubmit="submitForm(event)">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">ชื่อโครงการ:</label>
                            <input type="text" name="project_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ปีงบประมาณ:</label>
                            <select name="budget_year" class="form-select">
                                <option value="2567">2567</option>
                                <option value="2568">2568</option>
                                <option value="2569">2569</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">จังหวัด:</label>
                            <select id="province" name="province" class="form-select"></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">อำเภอ:</label>
                            <select id="amphoe" name="amphoe" class="form-select"></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ตำบล:</label>
                            <select id="tambon" name="tambon" class="form-select"></select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">หน่วยงานที่รับผิดชอบ:</label>
                            <select name="ministry" class="form-select">
                                <option value="กระทรวงกลาโหม">กระทรวงกลาโหม</option>
                                <option value="กระทรวงการคลัง">กระทรวงการคลัง</option>
                                <option value="กระทรวงศึกษาธิการ">กระทรวงศึกษาธิการ</option>
                                <option value="กระทรวงสาธารณสุข">กระทรวงสาธารณสุข</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ประเภทโครงการ:</label>
                            <select name="project_type" class="form-select">
                                <option value="ทั่วไป">ทั่วไป</option>
                                <option value="ก่อสร้าง">ก่อสร้าง</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">สถานะโครงการ:</label>
                            <select name="project_status" class="form-select">
                                <option value="รอดำเนินการ">รอดำเนินการ</option>
                                <option value="กำลังดำเนินการ">กำลังดำเนินการ</option>
                                <option value="เสร็จสิ้น">เสร็จสิ้น</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">วันที่ลงนามในสัญญา:</label>
                            <div class="input-group">
                                <input type="date" name="contract_start" class="form-control">
                                <input type="date" name="contract_end" class="form-control">
                            </div>
                        </div>

                        <div class="col-12 text-center mt-3">
                            <button type="submit" class="btn btn-success">บันทึกข้อมูล</button>
                            <button type="reset" class="btn btn-secondary">ล้างค่า</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
