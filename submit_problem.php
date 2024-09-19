<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_login'])) {
    $user_id = $_SESSION['user_login'];

    // ... ต่อจากนี้ ...
} else {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
}


// ตรวจสอบว่ามีการส่งฟอร์มแจ้งปัญหาหรือไม่
if (isset($_POST['submit_problem'])) {
    $user_id = $_POST['user_id'];
    $problem_description = $_POST['problem_description'];
    $_SESSION['success'] = 'ส่งแจ้งปัญหาสำเร็จ';

    // ใช้ echo เพื่อแสดง SweetAlert
    echo '<script>';
    echo 'Swal.fire({ icon: "success", title: "' . $_SESSION['success'] . '" });';
    echo '</script>';

    // ตรวจสอบว่ารายละเอียดปัญหาไม่ว่างเปล่าหรือไม่
    if (empty($problem_description)) {
        $_SESSION['error'] = 'กรุณากรอกรายละเอียดปัญหา';
        header("location: {$_SERVER['PHP_SELF']}");
        exit();
    } else {
        try {
            // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
            $user_data = $conn->prepare("SELECT firstname, lastname, email, house_number FROM users WHERE id = :user_id");
            $user_data->bindParam(":user_id", $user_id);
            $user_data->execute();
            $user_info = $user_data->fetch(PDO::FETCH_ASSOC);

            // ถ้าไม่พบข้อมูลผู้ใช้
            if (!$user_info) {
                $_SESSION['error'] = 'ไม่พบข้อมูลผู้ใช้';
                header("location: {$_SERVER['PHP_SELF']}");
                exit();
            }
            // ดึงข้อมูลผู้ใช้
            $firstname = $user_info['firstname'];
            $lastname = $user_info['lastname'];
            $email = $user_info['email'];
            $house_number = $user_info['house_number'];

            // ใช้คำสั่ง SQL เตรียมข้อมูลที่จะแทรกลงในฐานข้อมูล
            $insert_problem = $conn->prepare("INSERT INTO problem_reports (user_id, house_number, firstname, lastname, email, problem_description, status) 
            VALUES (:user_id, :house_number, :firstname, :lastname, :email, :problem_description, :status)");

            $status = "รอดำเนินการ"; // สถานะเริ่มต้น

            // กำหนดค่าพารามิเตอร์
            $insert_problem->bindParam(":user_id", $user_id);
            $insert_problem->bindParam(":firstname", $firstname);
            $insert_problem->bindParam(":lastname", $lastname);
            $insert_problem->bindParam(":email", $email);
            $insert_problem->bindParam(":problem_description", $problem_description);
            $insert_problem->bindParam(":status", $status);
            $insert_problem->bindParam(":house_number", $house_number);

            // ทำการแทรกข้อมูล
            $insert_problem->execute();

            // แสดงหน้าสำเร็จหรือทำงานเพิ่มเติมตามที่ต้องการ
            header("location:submit_problem.php");
            exit();

        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
            header("location: {$_SERVER['PHP_SELF']}");
            exit();
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['request_id']) && isset($_GET['csrf_token'])) {
        $request_id = $_GET['request_id'];
        $csrf_token = $_GET['csrf_token'];

        // ตรวจสอบ CSRF Token
        if ($csrf_token === $_SESSION['csrf_token']) {
            // ดำเนินการลบข้อมูล
            // ...
        } else {
            // Token ไม่ถูกต้อง
            echo "Invalid CSRF Token!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งปัญหาการใช้น้ำ</title>

<link rel="stylesheet" href="problem_css/user_problem.css">


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
<link rel="stylesheet" href="navbaruser/navuser.css">

</head>



<body>

    <!--************************************************************************************************************************
*                                                                                                                          -->
    <!--                                          == Navigation Bar ==
*                                                                                                                           *
***************************************************************************************************************************-->
<?php
require_once 'navbaruser/usernavbar.php';

?>






    <div class="read">
        <div class="submit_record">
            <form action="submit_problem.php" method="POST">
                <label for="problem_description">
                    <a>รายละเอียดปัญหา:</a>
                </label>
                <textarea name="problem_description" id="problem_description" rows="4" required
                    placeholder="กรอกรายละเอียดที่นี่:"></textarea>

                <div class="btn_form">
                    <button class="btn-primary" id="submit" type="submit" name="submit_problem">ส่ง</button>
                    <button class="btn-primary" id="back" type="button"
                        onclick="window.location.href='user.php';">ย้อนกลับ</button>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">


            </form>
        </div>
    </div>

    <!-- แสดงประวัติการแจ้งปัญหา -->
    <div class="check_report">
        <h2>ประวัติการแจ้งปัญหา</h2>

        <!--===============================ตาราง====================================-->
        <table id="Table" class="table-light">
    <thead>
        <tr>
            <th>รหัสคำขอ</th>
            <th>อีเมล</th>
            <th>ปัญหาที่แจ้ง</th>
            <th>สถานะ</th>
            <th>จัดการ</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // ดึงข้อมูลประวัติการแจ้งปัญหา
        if ($user_id) {
            // ดึงข้อมูลประวัติการแจ้งปัญหา
            $history_query = $conn->prepare("SELECT * FROM problem_reports WHERE user_id = :user_id ORDER BY timestamp DESC");
            $history_query->bindParam(":user_id", $user_id);
            $history_query->execute();

            // วนลูปแสดงผล
            while ($row = $history_query->fetch(PDO::FETCH_ASSOC)) {
                // กำหนดสีของสถานะตามเงื่อนไข
                $status_color_class = ($row['status'] == 'รอดำเนินการ') ? 'status-orange' : 'status-green';
                echo "<tr>
                    <td class=\"data-label\" data-label=\"รหัสคำร้อง\">{$row['request_id']}</td>
                    <td class=\"data-label\" data-label=\"อีเมลล์\">{$row['email']}</td>
                    <td class=\"data-label\" data-label=\"ปัญหาที่แจ้ง\">{$row['problem_description']}</td>
                    <td class=\"data-label\" data-label=\"สถานะ\"><span class=\"$status_color_class\">{$row['status']}</span></td>
                    <td class=\"data-label\" data-label=\"แก้ไข\">
                    <button class=\"delete-btn\" onclick=\"deleteRecord('{$row['request_id']}')\">
                        <i class=\"bi bi-trash\"></i> 
                    </button>
                </td>
                
                </tr>";
            }
            
        }
        ?>
    </tbody>
</table>
<style>
/* ปรับแต่งสีพื้นหลังและสีตัวหนังสือ */
.table-light td span {
    padding: 2px 10px;
    border-radius: 5px;
    color: white;
}

/* สีพื้นหลังเมื่อสถานะเป็น "รอดำเนินการ" */
.table-light td span.status-orange {
    background-color: #FFA500;
}

/* สีพื้นหลังเมื่อสถานะเป็น "ดำเนินการเรียบร้อย" */
.table-light td span.status-green {
    background-color: #14CC48;
}

</style>
    </div>


    <div class="footer">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>




    
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




    <script>
        <?php if (isset($_SESSION['success'])): ?>
            // แสดง SweetAlert เมื่อมีการอัปเดตข้อมูลสำเร็จ
            Swal.fire({
                icon: 'success',
                title: 'แจ้งปัญหาเรียบร้อย',


                showCloseButton: true,  // เพิ่มตัวเลือกนี้เพื่อแสดงปุ่มปิด
            }).then((result) => {
                // หากผู้ใช้คลิก OK ใน SweetAlert ให้ redirect ไปที่ user_list.php
                if (result.isConfirmed || result.dismiss === Swal.DismissReason.backdrop) {
                    window.location.href = 'submit_problem.php';
                }
            });
            <?php unset($_SESSION['success']); // เคลียร์ตัวแปรเซสชันหลังจากแสดง SweetAlert ?>
        <?php endif; ?></script>

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

                    null, // แก้ไขจัดการ
                    { "orderable": false } // ลบ
                ],
                "initComplete": function () {
                    // Add placeholder to the search input
                    $('#Table_filter label input').attr('placeholder', 'ค้นหา...');
                }
            });
        });

    </script>

    <script>
        function deleteRecord(requestId) {
            if (confirm("คุณต้องการลบประวัตินี้หรือไม่?")) {
                // ใช้ Ajax เพื่อลบข้อมูล
                $.ajax({
                    type: "POST",
                    url: "problem_db.php",
                    data: { request_id: requestId },
                    success: function (response) {
                        // แสดงข้อความหรือทำอย่างอื่นตามต้องการ
                        console.log(response);

                        // ตัวอย่าง: รีโหลดหน้าหลัก
                        location.reload();
                    },
                    error: function (error) {
                        console.error("Error deleting record:", error);
                    }
                });
            }
        }
    </script>


</body>

</html>