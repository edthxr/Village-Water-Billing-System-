<?php
session_start();

// ตรวจสอบว่า Admin ล็อกอินหรือไม่
if (!isset($_SESSION['admin_login'])) {
    header("location: login.php"); // ไปที่หน้าล็อกอินของ Admin
    exit();
}

require_once 'config/db.php';

// ดึงข้อมูลรายงานปัญหา
$fetch_reports = $conn->prepare("SELECT * FROM problem_reports");
$fetch_reports->execute();
$reports = $fetch_reports->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

    <title>Admin - รับแจ้งปัญหา</title>
    <link rel="stylesheet" href="problem_css/admin_problem.css">
    <link rel="stylesheet" href="navbar/nav.css">
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
</head>

<body>
<style>
    .status-pending {
        color: white; 
                background-color: #FFA500;
                border-radius: 5px;
                padding: 0 10px;
    }

    .status-completed {
        color: white; /* สีสำหรับสถานะ "ดำเนินการเรียบร้อย" */
        border-radius: 5px;
                padding: 0 10px;
                background-color: green;
    }
</style>


<?php
    require_once 'navbar/adminnavbar.php';

?>

    <!-- แสดงประวัติการแจ้งปัญหา -->
    <div class="check_report">
        <h2><i class="bi bi-exclamation-triangle icon-edit"></i> ตรวจสอบการแจ้งปัญหา</h2>

        <!--===============================ตาราง====================================-->
        <table id="Table" class="table-light">
            <thead>
                <tr>
                    <th>ชื่อ</th>
                    <th>นามสกุล</th>
                    <th>บ้านเลขที่</th>
                    <th>ปี/เดือน/วัน</th>
                    <th>ปัญหาที่แจ้ง</th>
                    <th>สถานะ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // ดึงข้อมูลประวัติการแจ้งปัญหา
                // แสดงประวัติการแจ้งปัญหา
                foreach ($reports as $report) {
                    // ดึงข้อมูลผู้ใช้
                    $fetch_user_info = $conn->prepare("SELECT firstname, lastname, email,house_number FROM users WHERE id = :user_id");
                    $fetch_user_info->bindParam(":user_id", $report['user_id']);
                    $fetch_user_info->execute();
                    $user_info = $fetch_user_info->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <tr>
                        <td class="data-label" data-label="ชื่อ">
                            <?php echo $user_info['firstname']; ?>
                        </td>
                        <td class="data-label" data-label="นามสกุล">
                            <?php echo $user_info['lastname']; ?>
                        </td>
                        <td class="data-label" data-label="บ้านเลขที่">
                            <?php echo $user_info['house_number']; ?>
                        </td>
                        <td class="data-label" data-label="ปี/เดือน/วัน">
                            <?php echo $report['timestamp']; ?>
                        </td>
                        <td class="data-label" data-label="ปัญหาที่แจ้ง">
                            <?php echo $report['problem_description']; ?>
                        </td>
                        <td class="data-label" data-label="สถานะ" data-status="<?php echo $report['status']; ?>"
    data-requestid="<?php echo $report['request_id']; ?>">
    <span class="<?php echo ($report['status'] == 'รอดำเนินการ') ? 'status-pending' : 'status-completed'; ?>">
        <?php echo $report['status']; ?>
    </span>
</td>

                        <td class="data-label" data-label="จัดการ">
                            <button class="edit-btn" data-toggle="modal" data-target="#editModal"
                                data-requestid="<?php echo $report['request_id']; ?>">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <button class="delete-btn" onclick="deleteRecord('<?php echo $report['request_id']; ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <a href="admin.php" class="back-button">ย้อนกลับ</a>
    </div>


    <div class="modal fade" id="editStatusModal" tabindex="-1" role="dialog" aria-labelledby="editStatusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editStatusModalLabel">แก้ไขสถานะ</h5>

                </div>
                <div class="modal-body">
                    <label for="newStatus">สถานะใหม่:</label>
                    <select id="newStatus" class="form-control" required>
                        <option value="รอดำเนินการ">รอดำเนินการ</option>
                        <option value="ดำเนินการเรียบร้อย">ดำเนินการเรียบร้อย</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary " id="save" onclick="updateStatus()">บันทึก</button>
                    <button type="button" class="btn btn-secondary" id="close" data-dismiss="modal">ปิด</button>
                </div>
            </div>
        </div>
    </div>


  

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
    <script>$(document).on("click", ".edit-btn", function () {
            var requestId = $(this).data("requestid");
            var currentStatus = $(this).closest("tr").find("[data-label='สถานะ']").data("status");

            // ใส่ค่าเดิมลงใน input และแสดง Modal
            $("#newStatus").val(currentStatus);
            $("#editStatusModal").modal("show");

            // ส่ง requestId ไปเพื่อให้ทราบว่าจะแก้ไขข้อมูลของรายการไหน
            $("#editStatusModal").data("requestid", requestId);
        });


        function updateStatus() {
            var newStatus = $("#newStatus").val();
            var requestId = $("#editStatusModal").data("requestid");

            // ใช้ Ajax เพื่ออัปเดต status
            $.ajax({
                type: "POST",
                url: "update_status.php", // แก้ไขเป็นไฟล์ที่จะทำการอัปเดต status
                data: { request_id: requestId, new_status: newStatus },
                success: function (response) {
                    console.log(response);

                    // ปิด Modal หลังจากทำการอัปเดต
                    $("#editStatusModal").modal("hide");

                    // ปรับปรุงสถานะในตาราง
                    $("td[data-requestid='" + requestId + "'][data-label='สถานะ']").text(newStatus);

                    // รีเฟรชหน้าเว็บ
                    window.location.href = 'problem.php';
                },
                error: function (error) {
                    console.error("Error updating status:", error);
                }
            });
        }

    </script>
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
                    null, // 1st column
                    null, // 2nd column
                    null, // 3rd column
                    null, // 4th column
                    null, // 5th column
                    null, // 6th column
                    null  // 7th column
                ],
                "initComplete": function () {
                    // Add placeholder to the search input
                    $('#Table_filter label input').attr('placeholder', 'ค้นหา...');
                }
            });
        });
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