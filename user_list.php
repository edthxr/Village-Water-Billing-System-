<?php

session_start();

// กำหนดค่าสำหรับเชื่อมต่อฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";

try {
    // เชื่อมต่อฐานข้อมูล MySQL
    $conn = new PDO("mysql:host=$servername;dbname=registration_system", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // แสดงข้อผิดพลาดเมื่อไม่สามารถเชื่อมต่อฐานข้อมูลได้
    echo "Connection failed: " . $e->getMessage();
}

// เรียกใช้ไฟล์ที่มีการกำหนดค่าเสริม (config/db.php)
require_once 'config/db.php';

// ตรวจสอบ session ของผู้ดูแลระบบ
if (!isset($_SESSION['admin_login'])) {
    // ถ้าไม่มี session ให้เก็บข้อความข้อผิดพลาดและเปลี่ยนเส้นทางไปยังหน้า sign-in
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
}

// แสดงข้อความสำเร็จหรือข้อผิดพลาด
if (isset($_SESSION['success'])) {
    echo '<p class="success">' . $_SESSION['success'] . '</p>';
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo '<p class="error">' . $_SESSION['error'] . '</p>';
    unset($_SESSION['error']);
}
?>


<!-----------------------------------------------------------------------------------------------------------------------------------------

*************************************************** START HTML PAGE ***********************************************************************

------------------------------------------------------------------------------------------------------------------------------------------>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>User List</title>
    <link rel="stylesheet" href="user_list_css/user_list.css">



</head>

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
<!-- sweet alert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!--font google-->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Libre+Bodoni:wght@300&display=swap">
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
    rel="stylesheet">
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





    <!---------------------------------------------ส่วนของตาราง รายละเอียดข้อมูลผู้ใช้ ----------------------------------------------------------------------->
    <div class="user_list">
        <h2><i class="bi bi-people custom-icon"></i>รายชื่อผู้ใช้น้ำ</h2>
        <form action="" method="GET">

            <table id="Table" class="table-light">
                <thead>
                    <tr>
                        <th class="custom-NAME">ID</th>
                        <th class="custom-NAME">ชื่อ</th>
                        <th class="custom-LASTNAME">นามสกุล</th>
                        <th class="custom-PHONE">เบอร์โทรศัพท์</th>
                        <th class="custom-ADDRESS">ที่อยู่</th>
                        <th class="custom-EDIT">จัดการ</th>
                    </tr>
                </thead>
                <tbody>


                    <?php
                    $stmt = $conn->prepare("SELECT * FROM users WHERE urole = 'user'");
                    $stmt->execute();
                    $users = $stmt->fetchAll();
                    foreach ($users as $user) {
                        ?>
                    <tr>

                        <td class="data-label" data-label="ID">
                            <?php echo $user['id'] ?>
                        </td>
                        <td class="data-label" data-label="ชื่อ">
                            <?php echo $user['firstname'] ?>
                        </td>
                        <td class="data-label" data-label="นามสกุล">
                            <?php echo $user['lastname'] ?>
                        </td>
                        <td class="data-label" data-label="เบอร์โทรศัพท์">
                            <?php echo $user['phone_number'] ?>
                        </td>
                        <td class="data-label" data-label="ที่อยู่">
                            <?php echo $user['house_number'] . ' ' . $user['village_number'] . ' ' . $user['subdistrict'] . ' ' . $user['district'] . ' ' . $user['province'] ?>
                        </td>
                        <td>


                            <!-- Button trigger modal -->
                                <a href="#" class="view-btn" onclick="openUserModal(<?php echo $user['id']; ?>)"><i
                                        class="bi bi-eye-fill"></i></a>

                                <!-- Modal -->
                                <div class="modal fade" id="userModal<?php echo $user['id']; ?>" tabindex="-1"
                                    aria-labelledby="userModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">

                                        <div class="modal-content">
                                            <div class="modal-body">

                                                <div class="con-modal">
                                                    <div class="user-card">
                                                        <h2><i
                                                                class="bi bi-person-lines-fill custom-icon"></i>รายละเอียดผู้ใช้

                                                        </h2>
                                                    </div>
                                                    <form method="post" id="user-card-in">

                                                        <div class="user-card-info">
                                                            <div class="form-column">
                                                                <p><span>ชื่อ : </span>
                                                                    <?php echo $user['firstname'] . ' ' . $user['lastname']; ?>
                                                                </p>
                                                            </div>
                                                            <div class="form-column">
                                                                <p><span>เลขมิเตอร์ : </span>
                                                                    <?php echo $user['meter_code']; ?>
                                                                </p>
                                                            </div>
                                                            <div class="form-column">
                                                                <p><span>เพศ :</span>
                                                                    <?php echo $user['gender']; ?>
                                                                </p>
                                                            </div>
                                                            <div class="form-column">
                                                                <p><span>สัญชาติ :</span>
                                                                    <?php echo $user['nationality']; ?>
                                                                </p>
                                                            </div>
                                                            <div class="form-column">
                                                                <p><span>บ้านเลขที่ :</span>
                                                                    <?php echo $user['house_number']; ?>
                                                                </p>
                                                            </div>
                                                            <div class="form-column">
                                                                <p><span>หมู่ที่ :</span>
                                                                    <?php echo $user['village_number']; ?>
                                                                </p>
                                                            </div>
                                                            <div class="form-column">
                                                                <p><span>ตำบล :</span>
                                                                    <?php echo $user['subdistrict']; ?>
                                                                </p>
                                                            </div>
                                                            <div class="form-column">
                                                                <p><span>อำเภอ :</span>
                                                                    <?php echo $user['district']; ?>
                                                                </p>
                                                            </div>
                                                            <div class="form-column">
                                                                <p><span>จังหวัด :</span>
                                                                    <?php echo $user['province']; ?>
                                                                </p>
                                                            </div>
                                                            <div class="form-column">
                                                                <p><span>รหัสบัตรประชาชน :</span>
                                                                    <?php echo $user['id_card_number']; ?>
                                                                </p>
                                                            </div>


                                                    </form>
                                                </div>
                                                <div class="btnsave">
                                                    <button type="button" class="button-style green"
                                                        data-bs-dismiss="modal">ปิด</button>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
        </div>

        <a href="edit_user.php?id=<?php echo $user['id'] ?>" class="edit-btn"><i class="bi bi-pencil-square"></i></a>
        <a href="delete_user_list.php?id=<?php echo $user['id'] ?>"
            onclick="return confirm('คุณแน่ใจหรือว่าต้องการลบผู้ใช้นี้?')" class="delete-btn"><i
                class="bi bi-trash3"></i></a>
        </td>
        </td>

        <?php
                    }
                    ?>
    </tbody>
    </table>
    </form>
    <div class="back">
        <a href="admin.php" class="back-button">ย้อนกลับ</a>
    </div>
    </div>





    <div class="footer">

    </div>

    <script>
        function closeModal(modalId) {
            var modalElement = document.getElementById(modalId);
            var modal = new bootstrap.Modal(modalElement);
            modal.hide();
        }
    </script>


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



    <!------------------------------------------------ : OPEN SCRIP สำหรับเรียกใช้ตาราง table : -------------------------------------------->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <script>
        function openUserModal(userId) {
            // ปรับปรุง target Modal เพื่อให้มันเป็น unique ตาม userId
            var modalId = 'userModal' + userId;
            var modalElement = document.getElementById(modalId);

            // เปิด Modal
            var modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    </script>
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
                    null, // เบอร์โทรศัพท์
                    null, // ที่อยู่
                    { "orderable": false } // ลบ
                ],
                "initComplete": function () {
                    // Add placeholder to the search input
                    $('#Table_filter label input').attr('placeholder', 'ค้นหา');
                }
            });
        });


        //--------------------------------------------------START :แสดงในส่วน alert------------------------------------------------------ //
        <?php if (isset($_SESSION['success'])): ?>
            // แสดง SweetAlert เมื่อมีการอัปเดตข้อมูลสำเร็จ
            Swal.fire({
                icon: 'success',
                title: '<?= $_SESSION['success'] ?>',
            });
            <?php unset($_SESSION['success']); // เคลียร์ตัวแปรเซสชันหลังจากแสดง SweetAlert ?>
        <?php endif; ?>
    </script>
    <!--------------------------------------------------end :แสดงในส่วน alert------------------------------------------------------->


    <!-- Vendor JS Files -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
    <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>


    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>
    <div id="preloader"></div>
</body>

</html>

<!-----------------------------------------------------------------------------------------------------------------------------------------

*************************************************** END HTML PAGE ***********************************************************************

------------------------------------------------------------------------------------------------------------------------------------------>