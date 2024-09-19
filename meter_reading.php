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


// ตรวจสอบว่ามี session success หรือไม่ และแสดงข้อความ
if (isset($_SESSION['success'])) {
    echo '<p class="success">' . $_SESSION['success'] . '</p>';
    unset($_SESSION['success']);
}

// ตรวจสอบว่ามี session error หรือไม่ และแสดงข้อความ
if (isset($_SESSION['error'])) {
    echo '<p class="error">' . $_SESSION['error'] . '</p>';
    unset($_SESSION['error']);
}

// ตรวจสอบว่ามีค่า user_id ที่ถูกส่งมาใน URL หรือไม่
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();
} else {
    // ถ้าไม่มี user_id ที่ถูกส่งมา, ทำการ redirect กลับไปที่หน้า user_list.php
    header('location: user_list.php');
}
// ตรวจสอบสิทธิ์การเข้าถึง
if (isset($_GET['id']) && $_GET['id'] == $user_id) {
    // ผู้ใช้ที่ล็อกอินตนเอง

    // ดำเนินการดึงข้อมูลผู้ใช้
    $user_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch();
} else {
    if ($_SESSION['user_role'] !== 'admin') {
        header('location: user_dashboard.php');
    }
    // ผู้ใช้ไม่มีสิทธิ์การแก้ไขข้อมูลผู้ใช้อื่น
    header('location: user_dashboard.php');
}


// ดึงข้อมูลการบันทึกล่าสุดของผู้ใช้
$latestRecordStmt = $conn->prepare("SELECT * FROM meter_records WHERE user_id = :user_id ORDER BY reading_date DESC LIMIT 1");
$latestRecordStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$latestRecordStmt->execute();
$latestRecord = $latestRecordStmt->fetch();


// ดึงข้อมูล current_reading ล่าสุดของผู้ใช้ก่อนหน้านี้
$previousReadingStmt = $conn->prepare("SELECT current_reading FROM meter_records WHERE user_id = :user_id ORDER BY reading_date DESC LIMIT 1");
$previousReadingStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$previousReadingStmt->execute();
$previousReadingResult = $previousReadingStmt->fetch();

$previous_reading_previous_user = $previousReadingResult ? $previousReadingResult['current_reading'] : 0;


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>บันทึกมิเตอร์ผู้ใช้:
        <?php echo $user['firstname'] . ' ' . $user['lastname']; ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="meter_reading_css/meter.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
</head>

<body>

    <!-------------------------------------------------------input form --------------------------------------------------------------->


    <div class="container">
        <?php
        // ตรวจสอบว่ามีข้อมูลการบันทึกในเดือนปัจจุบันแล้วหรือไม่
        if ($latestRecord) {
            $latestRecordDate = new DateTime($latestRecord['reading_date']);
            $currentDate = new DateTime();

            if ($latestRecordDate->format('Y-m') == $currentDate->format('Y-m')) {
                // ถ้ามีข้อมูลการบันทึกในเดือนปัจจุบันแล้ว
                // แสดงข้อความหรือทำการแจ้งเตือนตามที่คุณต้องการ
                echo '<p class="error"><i class="bi bi-patch-check icon-edit"></i><br>เจ้าหน้าที่ได้บันทึกข้อมูลของผู้ใช้ในเดือนนี้แล้ว</p>';
                // แสดงลิงก์ไปยังหน้า edit_meter_reading.php พร้อมกับ ID ของการบันทึกมิเตอร์
        
                // สามารถหยุดการทำงานต่อไปได้
            }
        }
        ?>

        <div class="font-header">
            <h2><i class="bi bi-floppy custom-icon"></i>บันทึกมิเตอร์ผู้ใช้:
                <?php echo $user['firstname'] . ' ' . $user['lastname']; ?>
            </h2>
        </div>
        <form action="save_meter_reading.php" method="POST" class="user-form">
            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">



            <div class="form-column" id="hidetime">

                <label for="reading_date">วันที่บันทึก:</label>
                <input type="text" id="reading_date" name="reading_date" readonly>
            </div>



            <div class="form-column" id="hidepr">
                <?php
                // ตรวจสอบเงื่อนไขว่ามีข้อมูลการบันทึกในเดือนปัจจุบันหรือไม่
                if ($latestRecord) {
                    $latestRecordDate = new DateTime($latestRecord['reading_date']);
                    $currentDate = new DateTime();

                    if ($latestRecordDate->format('Y-m') == $currentDate->format('Y-m')) {
                        // ถ้ามีข้อมูลการบันทึกในเดือนปัจจุบันแล้ว
                        // เปลี่ยน label เป็น "ค่ามิเตอร์เดือนล่าสุด"
                        echo '<label for="previous_reading">ค่ามิเตอร์เดือนล่าสุด:</label>';
                    } else {
                        // ถ้ายังไม่มีข้อมูลการบันทึกในเดือนปัจจุบัน
                        // ใช้ label เดิมคือ "ค่ามิเตอร์เดือนก่อน"
                        echo '<label for="previous_reading">ค่ามิเตอร์เดือนก่อน:</label>';
                    }
                } else {
                    // ถ้าไม่มีข้อมูลการบันทึกเลย
                    // ใช้ label เดิมคือ "ค่ามิเตอร์เดือนก่อน"
                    echo '<label for="previous_reading">ค่ามิเตอร์เดือนก่อน:</label>';
                }
                ?>
                <?php
                // ตรวจสอบว่ามีข้อมูล current_reading หรือไม่
                if ($previousReadingResult && isset($previousReadingResult['current_reading'])) {
                    // มีข้อมูล current_reading ให้แสดงค่านั้น
                    echo '<input type="number" id="previous_reading" name="previous_reading" value="' . $previousReadingResult['current_reading'] . '" readonly>';
                } else {
                    // ไม่มีข้อมูล current_reading ให้ใส่ค่าเริ่มต้นหรือว่าง
                    echo '<input type="number" id="previous_reading" name="previous_reading" value="" placeholder="0" required>';
                }
                ?>
            </div>




            <div class="form-column" id="hidecurrent">
                <label for="current_reading">ค่ามิเตอร์ล่าสุด:</label>
                <input type="number" id="current_reading" name="current_reading" required placeholder="ป้อนข้อมูล">
            </div>


            <div class="form-column" id="hideprice">
                <label for="price">ราคา (บาท)</label>
                <input type="text" id="price" name="price" readonly placeholder="คำนวณราคาอัตโนมัติ">
            </div>
            <div class="form-column">
                <?php
                // ตรวจสอบเงื่อนไขว่ามีข้อมูลการบันทึกในเดือนปัจจุบันหรือไม่
                if ($latestRecord) {
                    $latestRecordDate = new DateTime($latestRecord['reading_date']);
                    $currentDate = new DateTime();

                    if ($latestRecordDate->format('Y-m') == $currentDate->format('Y-m')) {
                        // ถ้ามีข้อมูลการบันทึกในเดือนปัจจุบันแล้ว
                        // เปลี่ยนข้อความเป็น "ดำเนินการเรียบร้อย"
                        echo '<small class="form-text text-muted">* ท่านสามารถตรวจสอบหรือแก้ไขได้ที่ปุ่มด้านล่าง</small>';
                    } else {
                        // ถ้ายังไม่มีข้อมูลการบันทึกในเดือนปัจจุบัน
                        // ใช้ข้อความเดิม
                        echo '<small class="form-text text-muted">* ราคาคำนวณจากการใช้น้ำ: 1 หน่วย = 12 บาท</small>';
                    }
                } else {
                    // ถ้าไม่มีข้อมูลการบันทึกเลย
                    // ใช้ข้อความเดิม
                    echo '<small class="form-text text-muted">* ราคาคำนวณจากการใช้น้ำ: 1 หน่วย = 12 บาท</small>';
                }
                ?>
            </div>


            <input type="hidden" name="user_name" value="<?php echo $user['firstname'] . ' ' . $user['lastname']; ?>">
            <input type="hidden" name="meter_code" value="<?php echo $user['meter_code']; ?>">
            <input type="hidden" name="house_number" value="<?php echo $user['house_number']; ?>">
            <input type="hidden" name="submit_by" value="<?php echo $admin['firstname'] . ' ' . $admin['lastname']; ?>">
            <input type="hidden" name="payment_status" value="ค้างชำระ">



            <?php
            // ตรวจสอบว่ามีข้อมูลการบันทึกในเดือนปัจจุบันแล้วหรือไม่
            if ($latestRecord) {
                $latestRecordDate = new DateTime($latestRecord['reading_date']);
                $currentDate = new DateTime();

                if ($latestRecordDate->format('Y-m') == $currentDate->format('Y-m')) {
                    // ถ้ามีข้อมูลการบันทึกในเดือนปัจจุบันแล้ว
                    // แสดงข้อความหรือทำการแจ้งเตือนตามที่คุณต้องการ
            
                    // แสดงลิงก์ไปยังหน้า edit_meter_reading.php พร้อมกับ ID ของการบันทึกมิเตอร์
                    echo '<a href="edit_meter_reading.php?id=' . $user_id . '&record_id=' . $latestRecord['id'] . '" class="button-style blue">ตรวจสอบ</a>';
                    // สามารถหยุดการทำงานต่อไปได้
                }
            }
            ?>
            <div class="button-row">
                <div class="btnsave">
                    <a href="#" id="saveLink" class="button-style green">บันทึก</a>
                </div>

                <div class="btncancel">
                    <a href="waterusage.php" class="button-style red">ยกเลิก</a>
                </div>
            </div>




            <!------------------------------------------------------- input form end --------------------------------------------------------------->


            <!--------------------------------------------------------------- START check warning-------------------------------------------------------------------------------->


            <script>

                var showAlert = true;
                document.getElementById('saveLink').addEventListener('click', function (event) {
    event.preventDefault(); // ยกเลิกการทำงานปกติของลิงก์

    var previousReading = parseFloat(document.getElementById('previous_reading').value) || 0;
    var currentReading = parseFloat(document.getElementById('current_reading').value) || 0;

    if (previousReading > currentReading) {
        // แสดงข้อความแจ้งเตือนว่าค่ามิเตอร์เดือนก่อนต้องไม่มากกว่าค่ามิเตอร์ปัจจุบัน
        Swal.fire({
            icon: 'warning',
            title: 'คำเตือน',
            text: 'ค่ามิเตอร์เดือนก่อนต้องไม่มากกว่าค่ามิเตอร์ปัจจุบัน',
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
                    document.getElementById('hidecurrent').style.display = 'none';
                    document.getElementById('hideprice').style.display = 'none';
                    document.getElementById('hidetime').style.display = 'none';
                    document.getElementById('hidepr').style.display = 'none';



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
    <!----------------------------------------------------------End Js processor---------------------------------------------------->


</body>

</html>