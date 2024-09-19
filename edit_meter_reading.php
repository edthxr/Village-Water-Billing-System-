<?php
// เริ่ม session
session_start();

// Include ไฟล์ที่จำเป็นและตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบหรือไม่
require_once 'config/db.php';

// ตรวจสอบว่า Admin ล็อกอินหรือไม่
if (!isset($_SESSION['admin_login'])) {
    // ถ้ายังไม่ได้ล็อกอิน กำหนดข้อความข้อผิดพลาดและ Redirect ไปยังหน้า signin.php
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
}


// เพิ่ม code เพื่อดึงชื่อ admin จากฐานข้อมูล
$admin_id = $_SESSION['admin_login']; // หากมี session ที่เก็บ ID ของ admin ที่ล็อกอินอยู่
$adminStmt = $conn->prepare("SELECT * FROM users WHERE id = :admin_id");
$adminStmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
$adminStmt->execute();
$admin = $adminStmt->fetch();


// กำหนด locale เป็นภาษาไทย
setlocale(LC_TIME, 'th_TH.utf8', 'th_TH');

// ตรวจสอบว่ามีค่า record_id ที่ถูกส่งมาใน URL หรือไม่
if (isset($_GET['record_id'])) {
    $record_id = $_GET['record_id'];

    // ดึงข้อมูลการบันทึกมิเตอร์ที่ต้องการแก้ไขจากฐานข้อมูล
    $editRecordStmt = $conn->prepare("SELECT * FROM meter_records WHERE id = :record_id");
    $editRecordStmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
    $editRecordStmt->execute();
    $editRecord = $editRecordStmt->fetch();

    // ตรวจสอบว่ามีข้อมูลการบันทึกที่ต้องการแก้ไขหรือไม่
    if (!$editRecord) {
        $_SESSION['error'] = 'ไม่พบข้อมูลการบันทึกที่ต้องการแก้ไข';
        header('location: waterusage.php');
        exit();
    }

    // ดึงข้อมูลผู้ใช้ที่ต้องการแก้ไขจากฐานข้อมูล
    $getUserStmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
    $getUserStmt->bindParam(':user_id', $editRecord['user_id'], PDO::PARAM_INT);
    $getUserStmt->execute();
    $user = $getUserStmt->fetch();
} else {
    // ถ้าไม่มี record_id ที่ถูกส่งมา, ทำการ redirect กลับไปที่หน้า waterusage.php
    header('location: waterusage.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>แก้ไขบันทึกมิเตอร์ผู้ใช้:
        <?php echo $user['firstname'] . ' ' . $user['lastname']; ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="meter_reading_css/meter.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
</head>
</head>

<body>

<div class="container">
    <div class="font-header">
        <h2><i class="bi bi-pencil-square custom-icon"></i></i> แก้ไขบันทึกมิเตอร์ผู้ใช้: <?php echo $user['firstname'] . ' ' . $user['lastname']; ?></h2>
    </div>

    <form action="update_meter_reading.php" method="POST" class="user-form">
        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
        <input type="hidden" name="record_id" value="<?php echo $editRecord['id']; ?>">

        <div class="form-column">
    <label for="reading_date">วันที่บันทึก:</label>
    <input type="text" id="reading_date" name="reading_date" value="<?php echo $editRecord['created_at']; ?>" readonly>
</div>


        <div class="form-column">
            <label for="previous_reading">ค่ามิเตอร์เดือนก่อน:</label>
            <input type="number" id="previous_reading" name="previous_reading" value="<?php echo $editRecord['previous_reading']; ?>" required>
        </div>

        <div class="form-column">
            <label for="current_reading">ค่ามิเตอร์ล่าสุด:</label>
            <input type="number" id="current_reading" name="current_reading" value="<?php echo $editRecord['current_reading']; ?>" required>
        </div>

        <div class="form-column">
            <label for="price">ราคา (บาท)</label>
            <input type="text" id="price" name="price" value="<?php echo $editRecord['price']; ?>" readonly>
        </div>

        <div class="form-column">
            <small class="form-text text-muted">* ราคาคำนวณจากการใช้น้ำ: 1 หน่วย = 12 บาท</small>
        </div>
        <input type="hidden" name="submit_by" value="<?php echo $admin['firstname'] . ' ' . $admin['lastname']; ?>">
        <div class="button-row">
            <div class="btnsave">
                <a href="#" id="saveLink" class="button-style green">บันทึก</a>
            </div>

            <div class="btncancel">
            <a href="meter_reading.php<?php echo isset($_GET['id']) ? '?id=' . $_GET['id'] : ''; ?>" class="button-style red">ย้อนกลับ</a>

            </div>
        </div>
    </form>
</div>

    <!-- ส่วนท้ายฟอร์ม -->
    

    <script>

var showAlert = true;

document.getElementById('saveLink').addEventListener('click', function (event) {
    event.preventDefault(); // ยกเลิกการทำงานปกติของลิงก์


    var previousReading = parseFloat(document.getElementById('previous_reading').value) || 0;
    var currentReading = parseFloat(document.getElementById('current_reading').value) || 0;

    if (previousReading > currentReading && showAlert) {
        Swal.fire({
            icon: 'warning',
            title: 'คำเตือน',
            text: 'ค่ามิเตอร์เดือนก่อนต้องไม่มากกว่าค่ามิเตอร์เดือนล่าสุด',
        });
    } else {
        // แสดงกล่องยืนยัน
        Swal.fire({
            title: 'ยืนยันการบันทึกข้อมูล',
            text: 'กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนที่จะกดบันทึก',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // ทำการบันทึกหลังจากผู้ใช้ยืนยัน
                saveFunction();
            }
        });
    }
});



// เพิ่มเงื่อนไขตรวจสอบว่ามีข้อความแจ้งเตือนหรือไม่
var hasError = document.querySelector('.error');
if (hasError) {
    // ปิดการให้กรอกข้อมูล
    disableForm();
}

function disableForm() {
    // ปิดการให้กรอกข้อมูลทั้งฟอร์ม
    document.getElementById('previous_reading').disabled = true;
    document.getElementById('current_reading').disabled = true;
    // สามารถเพิ่มตัวแปรอื่น ๆ และปรับการปิดให้กรอกข้อมูลตามที่ต้องการ
}


document.querySelector('.btn-secondary').addEventListener('click', function () {
    showAlert = false;
    cancelForm();
});

// ตัวอย่างฟังก์ชันที่ทำงานเมื่อกดปุ่มบันทึก
function saveFunction() {
    // ทำงานที่ต้องการเมื่อกดปุ่มบันทึก
    document.querySelector('form').submit(); // ส่งฟอร์ม
}

// เพิ่ม event listener สำหรับปุ่มยกเลิก
document.querySelector('.btn-secondary').addEventListener('click', function () {
    showAlert = false;  // ปิดการแสดง alert เมื่อกดปุ่มยกเลิก
    cancelForm();
});

</script>


</form>
</div>
<!--------------------------------------------------------------- END THIS------------------------------------------------------------------------------------->






<!-------------------------------------------------ปุ่มยกเลิก------------------------------------------------------------>
<script>
function cancelForm() {
// รีเซ็ตค่า input ให้เป็นค่าว่างหรือค่าเริ่มต้น
document.getElementById('previous_reading').value = '';
document.getElementById('current_reading').value = '';
document.getElementById('price').value = '';

// กลับไปที่หน้า waterusage.php
window.location.href = 'waterusage.php';
}
</script>

<!-------------------------------------------------ปุ่มยกเลิก------------------------------------------------------------>





<!----------------------------------------------------------Start Js processor---------------------------------------------------->

<script>
window.onload = function () {
//------------------------- ดึงข้อมูลปัจจุบันของวันที่-------------------------------------------------
var currentDate = new Date();
var day = currentDate.getDate();
var monthIndex = currentDate.getMonth();
var year = currentDate.getFullYear();

var months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม',
'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
];
var monthName = months[monthIndex];

var formattedDate = day + ' ' + monthName + ' ' + year;
// แสดงวันที่ใน input ที่มี id เท่ากับ 'reading_date'
document.getElementById('reading_date').value = formattedDate;
};

// -------------------------เมื่อมีการ input ข้อมูลใน input ที่มี id เท่ากับ 'current_reading'-----------------------

document.getElementById('current_reading').addEventListener('input', function () {
// ดึงค่า previous_reading จาก input ที่มี id เท่ากับ 'previous_reading'
var previousReading = parseFloat(document.getElementById('previous_reading').value) || 0;
// ดึงค่า current_reading จาก input ที่มี id เท่ากับ 'current_reading'
var currentReading = parseFloat(this.value) || 0;

// คำนวณค่า usage จาก current_reading ลบด้วย previous_reading
var usage = currentReading - previousReading;
// คำนวณค่า price จาก usage คูณด้วย 12 ปล.12คือหน่วยน้ำที่ใช้
var price = usage * 12;

// แสดงค่า price ใน input ที่มี id เท่ากับ 'price'
document.getElementById('price').value = price.toFixed(2);
});

</script>
</body>

</html>
