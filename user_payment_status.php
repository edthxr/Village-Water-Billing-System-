<?php
session_start();

// Include necessary files and check if the user is logged in
require_once 'config/db.php';

if (!isset($_SESSION['admin_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
}



if (isset($_SESSION['error'])) {
    echo '<p class="error">' . $_SESSION['error'] . '</p>';
    unset($_SESSION['error']);
}

// Check if error_message is set in the URL
if (isset($_GET['error_message'])) {
    echo '<p class="error">' . urldecode($_GET['error_message']) . '</p>';
}


//-------------------ดึงข้อมูลการบันทึกมิเตอร์จากฐานข้อมูล--------------------------------------------
$meterRecordsStmt = $conn->prepare("SELECT * FROM meter_records WHERE user_id = :user_id");
$meterRecordsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$meterRecordsStmt->execute();
$meterRecords = $meterRecordsStmt->fetchAll(PDO::FETCH_ASSOC);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบผู้ใช้ค้างชำระ</title>

    <!-- ลิงค์ CSS  -->
    <link rel="stylesheet" href="payment_status_css/payment_status.css">
    


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



    <link rel="stylesheet" href="navbar/nav.css">
</head>


<body>

    <!--************************************************************************************************************************
*                                                                                                                          -->
    <!--                                          == Navigation Bar ==
*                                                                                                                           *
***************************************************************************************************************************-->
<?php
require_once 'navbar/adminnavbar.php';

?>



    <!--************************************************************************************************************************-->

    <!--************************************************************************************************************************
*                                                                                                                          -->
    <!--                                          == ตาราง ตรวจสอบผู้ใช้ และดูยอดการค้างชำระ ==
*                                                                                                                           *
***************************************************************************************************************************-->



    <div class="waterusage">
        <h2>เช็คยอดค้างชำระ</h2>

        <!--===============================ตาราง====================================-->
        <table id="Table" class="table-light">
            <thead>
                <tr>
                    <th>รหัสมิเตอร์</th>
                    <th>ชื่อ</th>
                    <th>นามสกุล</th>
                    <th>ที่อยู่</th>

                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->prepare("SELECT * FROM users WHERE urole = 'user'");
                $stmt->execute();
                $users = $stmt->fetchAll();
                foreach ($users as $user) {
                    ?>
                    <!--===============================ข้อมูลในตาราง====================================-->
                    <tr>
                        <td class="data-label" data-label="รหัสมิเตอร์">
                            <?php echo $user['meter_code'] ?>
                        </td>
                        <td class="data-label" data-label="ชื่อ">
                            <?php echo $user['firstname'] ?>
                        </td>
                        <td class="data-label" data-label="นามสกุล">
                            <?php echo $user['lastname'] ?>
                        </td>
                        <td class="data-label" data-label="ที่อยู่">
                            <?php echo $user['house_number'] . ' ' . $user['village_number'] . ' ' . $user['subdistrict'] . ' ' . $user['district'] . ' ' . $user['province'] ?>
                        </td>

                        <td class="data-label" data-label="แก้ไข">
                     
                                   <a href="check_payment.php?id=<?php echo $user['id'] ?>" class="edit-btn">
                                   <i class="bi bi-search"></i>
                        </td>
                        </td>
                        <?php
                }
                ?>
            </tbody>
        </table>
        <a href="admin.php" class="back-button">ย้อนกลับ</a>
    </div>


<div class="footer">
    
</div>














    <!--================================script start ====================================-->

    <!--==================================navigation bar script ======================================-->
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
    <!--=================================================================================-->


    <!--==================================script start======================================-->

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
                    "sSearch": '<i class="bi bi-search"></i>',
                    "sUrl": "",
                    "oPaginate": {
                        "sFirst": "เริ่มต้น",
                        "sPrevious": "ก่อนหน้า",
                        "sNext": "ถัดไป",
                        "sLast": "สุดท้าย"
                    },
                },
                "columns": [
                    null, // ID
                    null, // ชื่อ
                    null, // นามสกุล

                    null, // ที่อยู่
                    { "orderable": false } // ลบ
                ],
                "initComplete": function () {
                    // Add placeholder to the search input
                    $('#Table_filter label input').attr('placeholder', 'ค้นหา');
                }
            });
        });

    </script>

</body>

</html>