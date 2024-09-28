<?php
session_start();
require_once 'config/db.php';

// ---------------------ตรวจสอบว่ามี session user_login หรือไม่------------------------------------
if (!isset($_SESSION['user_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
}

// -------------------ดึงข้อมูล user จากฐานข้อมูล----------------------------------------------------
$user_id = $_SESSION['user_login'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

//-------------------ดึงข้อมูลการบันทึกมิเตอร์จากฐานข้อมูล--------------------------------------------
$meterRecordsStmt = $conn->prepare("SELECT * FROM meter_records WHERE user_id = :user_id");
$meterRecordsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$meterRecordsStmt->execute();
$meterRecords = $meterRecordsStmt->fetchAll(PDO::FETCH_ASSOC);


// -----------------------แปลงชื่อเดือนในภาษาอังกฤษเป็นภาษาไทย-----------------------------------------
$thaiMonthNames = [
    'January' => 'มกราคม',
    'February' => 'กุมภาพันธ์',
    'March' => 'มีนาคม',
    'April' => 'เมษายน',
    'May' => 'พฤษภาคม',
    'June' => 'มิถุนายน',
    'July' => 'กรกฎาคม',
    'August' => 'สิงหาคม',
    'September' => 'กันยายน',
    'October' => 'ตุลาคม',
    'November' => 'พฤศจิกายน',
    'December' => 'ธันวาคม',
];

?>



<!-----------------------------------------------------------------------------------------------------------------------------------------

*************************************************** START HTML PAGE *************************************************************************
l
------------------------------------------------------------------------------------------------------------------------------------------>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>User Dashboard</title>

    <!-- ลิงค์ CSS  -->
    <link rel="stylesheet" href="user_page1_css/user_page1.css">


    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <!-- sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!--font google-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Bootstrap JS (make sure it's after jQuery) -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>

    <!-- DataTables JS (make sure it's after jQuery) -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="navbaruser/navuser.css">
</head>

<body>
<style>
    .status-pending {
    
        color: white; 
                background-color: #FFA500;
                border-radius: 5px;
                padding: 0 10px;
    }

    .status-paid {
        color: white;
                background-color: #14CC48;
                border-radius: 5px;
                padding: 0 10px;
    }

    .status-pending-payment {
        color: white; 
                background-color: #F44336;
                border-radius: 5px;
                padding: 0 10px;

    }
</style>


<?php
    require_once 'navbaruser/usernavbar.php';
?>



    <!--************************************************************************************************************************
*                                                                                                                          -->
    <!--                                          == Navigation Bar ==
*                                                                                                                           *
***************************************************************************************************************************-->




    <!---------------------------------------------ส่วนของรายละเอียดข้อมูลผู้ใช้ ----------------------------------------------------------------------->

    <div class="user_details">
        <div class="font-header">
            <h2><i class="bi bi-person-vcard custom-icon"></i>ข้อมูล</h2>
        </div>
        <div class="user_details1">
            <p class="form-column">ชื่อ :
                <?php echo $user['firstname'] . ' ' . $user['lastname']; ?>
            </p>
            <p class="form-column">อีเมล :
                <?php echo $user['email']; ?>
            </p>
            <p class="form-column">รหัสมิเตอร์ :
                <?php echo $user['meter_code']; ?>
            </p>
            <p class="form-column">เพศ :
                <?php echo $user['gender']; ?>
            </p>
            <p class="form-column">สัญชาติ :
                <?php echo $user['nationality']; ?>
            </p>
            <p class="form-column">เบอร์โทรศัพท์ :
                <?php echo $user['phone_number']; ?>
            </p>
            <p class="form-column">บ้านเลขที่ :
                <?php echo $user['house_number']; ?>
            </p>
            <p class="form-column">หมู่ที่ :
                <?php echo $user['village_number']; ?>
            </p>
            <p class="form-column">ตำบล :
                <?php echo $user['subdistrict']; ?>
            </p>
            <p class="form-column">อำเภอ :
                <?php echo $user['district']; ?>
            </p>
            <p class="form-column">จังหวัด :
                <?php echo $user['province']; ?>
            </p>
            </h3>
            <p class="form-column">รหัสบัตรประชาชน :
                <?php echo $user['id_card_number']; ?>
            </p>
        </div>
    </div>



    <!-------------------------------------------------- END: ส่วนของรายละเอียดข้อมูลผู้ใช้ ---------------------------------------------------->

    <!--------------------------------------------------- START: ส่วนของตารางการใช้น้ำ ---------------------------------------------------->
    <div class="history">
        <h2><i class="bi bi-clipboard-data custom-icon"></i>ตารางการใช้น้ำในแต่ละเดือน</h2>

        <form action="" method="GET">

            <table id="Table" class="table-light">

                <thead>
                    <tr>
                 
                        <th>วัน</th>
                        <th class="hide-on-small-screen">เดือน</th><br>
                        <th>ปี</th>
                        <th>ค่ามิเตอร์รอบก่อน</th>
                        <th>ค่ามิเตอร์ล่าสุด</th>
                        <th>อ่านได้</th>
                        <th>ราคา</th>
                        <th>สถานะ</th>
                        <th>บันทึกโดย</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($meterRecords as $record): ?>
                        <tr>
               
                            <td class="data-label" data-label="วัน">
                                <?php echo $record['day']; ?>
                            </td>
                            <td class="data-label" data-label="เดือน">
                                <?php echo $thaiMonthNames[$record['month_name']]; ?>
                            </td>
                            <td class="data-label" data-label="ปี">
                                <?php echo $record['year']; ?>
                            </td>
                            <td class="data-label" data-label="ค่ามิเตอร์รอบก่อน">
                                <?php echo $record['previous_reading']; ?>
                            </td>
                            <td class="data-label" data-label="ค่ามิเตอร์ล่าสุด">
                                <?php echo $record['current_reading']; ?>
                            </td>
                            <td class="data-label" data-label="อ่านได้">
                                <?php echo $record['usage_value']; ?>
                            </td>
                            <td class="data-label" data-label="ราคา">
                                <?php echo $record['price']; ?>
                            </td>
                            <td class="data-label" data-label="สถานะ">
    <?php
    $status = $record['payment_status'];
    $statusClass = '';

    switch ($status) {
        case 'รอดำเนินการ':
            $statusClass = 'status-pending';
            break;
        case 'ชำระเงินเรียบร้อย':
            $statusClass = 'status-paid';
            break;
        case 'ค้างชำระ':
            $statusClass = 'status-pending-payment';
            break;
        default:
            $statusClass = '';
            break;
    }
    ?>
    <span class="<?php echo $statusClass; ?>">
        <?php echo $status; ?>
    </span>
</td>


                            <td class="data-label" data-label="บันทึกโดย">
                                <?php echo $record['submit_by']; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <a href="user.php" class="back-button">ย้อนกลับ</a>
            <button type="button" class="back-button" data-bs-toggle="modal" data-bs-target="#chartModal">
            รายละเอียด
        </button>
        </form>
        
    </div>

    <div class="modal fade" id="chartModal" tabindex="-1" aria-labelledby="chartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chartModalLabel">รายละเอียดการใช้น้ำ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">ปิด</button>
            </div>
            <div class="modal-body">
                <canvas id="myBarChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

    
<script>
        // ข้อมูลเดือนและการใช้น้ำของแต่ละเดือน
        var months = [
            <?php foreach ($meterRecords as $record): ?>
                "<?php echo $thaiMonthNames[$record['month_name']]; ?>",
            <?php endforeach; ?>
        ];

        var waterUsages = [
            <?php foreach ($meterRecords as $record): ?>
                <?php echo $record['usage_value']; ?>,
            <?php endforeach; ?>
        ];

        // สร้างกราฟแบบแท่ง
      // สร้างกราฟแบบแท่ง
      var ctx = document.getElementById('myBarChart').getContext('2d');
var myBarChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'การใช้น้ำ',
            data: waterUsages,
            backgroundColor: '#00008B',
            borderColor: '#00008B',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        },
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        animation: {
            duration: 1000, // ระยะเวลาในการแสดงอนิเมชัน (มีหน่วยเป็นมิลลิวินาที)
            easing: 'easeInOutCubic' // ลักษณะของอนิเมชัน
        },
        elements: {
            bar: {
                hoverBackgroundColor: 'rgba(0, 0, 139, 0.8)',
                hoverBorderColor: 'rgba(0, 0, 139, 0.8)',
                borderWidth: 2,
                hoverBorderWidth: 4 // ความหนาของเส้นขอบแท่งกราฟเมื่อ hover
            }
        }
    }
});
    </script>


    </div>
    <div class="footer">
        
    </div>


    <!-------------------------------------------------- END: ส่วนของตารางการใช้น้ำ ------------------------------------------------------>


    <!------------------------------------------------ : OPEN SCRIP สำหรับเรียกใช้ตาราง table : -------------------------------------------->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <!-----------------------------------------------START: SCRIP แปลงตัวอักษรเป็นภาษาไทยใน TABLE ---------------------------------------->
    <script>
        $(document).ready(function () {
            $('#Table').DataTable({
                "language": {
                    "ordering": false,
                    "sProcessing": "กำลังดำเนินการ...",
                    "sLengthMenu": "แสดง _MENU_ รายการ",
                    "sZeroRecords": "ไม่พบรายการที่ตรงกับคำค้น",
                    "sInfo": "กำลังแสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                    "sInfoEmpty": "กำลังแสดง 0 ถึง 0 จากทั้งหมด 0 รายการ",
                    "sInfoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                    "sInfoPostFix": "",
                    "sSearch": '<i class="bi bi-search">  </i>',
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "เริ่มต้น",
                        "sPrevious": "ก่อนหน้า",
                        "sNext": "ถัดไป",
                        "sLast": "สุดท้าย"
                    },
                },
                "initComplete": function () {
                    // Add placeholder to the search input
                    $('#Table_filter label input').attr('placeholder', 'ค้นหา');
                }
            });
        });    
    </script>
    <!-----------------------------------------------END: SCRIP แปลงตัวอักษรเป็นภาษาไทยใน TABLE ---------------------------------------->
    <script>
    
    function showSidebar() {
        const sidebar = document.querySelector('.sidebar')
        sidebar.style.display = 'flex'
    }
    function closeSidebar() {
        const sidebar = document.querySelector('.sidebar')
        sidebar.style.display = 'none'
    }
      </script>    


    <!-----------------------------------------------เรียกใช้ตาราง TABLE --------------------------------------------------------------->
    <script>
        $(document).ready(function () {
            $('#Table').DataTable();
        });
    </script>
    <!-----------------------------------------------LINK SCRIP เพิ่มเติม ------------------------------------------------------------------>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-pzjw8+49pLwAI+5/6PTfXwq1jstA7eb/buLZ84NBfDrlD9LpZIBbhd4G8b0Fpo0"
        crossorigin="anonymous"></script>
    <!-- Vendor JS Files -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

</body>

</html>


<!-----------------------------------------------------------------------------------------------------------------------------------------

*************************************************** END HTML PAGE *************************************************************************
l
------------------------------------------------------------------------------------------------------------------------------------------>