<?php
session_start();
require_once 'config/db.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['admin_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
    exit;
}

// ตรวจสอบว่ามีรหัสผู้ใช้ที่ต้องการแก้ไขหรือไม่
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // ตรวจสอบว่ามีข้อมูลผู้ใช้หรือไม่
    if (!$user) {
        $_SESSION['error'] = 'ไม่พบข้อมูลผู้ใช้';
        header('location: user_list.php');
        exit;
    }
} else {
    $_SESSION['error'] = 'รหัสผู้ใช้ไม่ถูกต้อง';
    header('location: user_list.php');
    exit;
}

// ตรวจสอบการส่งแบบฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ดึงข้อมูลจากแบบฟอร์ม
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $meter_code = $_POST['meter_code'];
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $nationality = $_POST['nationality'];
    $phone_number = $_POST['phone_number'];
    $house_number = $_POST['house_number'];
    $village_number = $_POST['village_number'];
    $subdistrict = $_POST['subdistrict'];
    $district = $_POST['district'];
    $province = $_POST['province'];
    $id_card_number = $_POST['id_card_number'];

    // คำนวณอายุจากวันเกิด
    $birth_date = new DateTime($birthdate);
    $current_date = new DateTime();
    $age = $birth_date->diff($current_date)->y;

    // อัปเดตข้อมูลผู้ใช้ในฐานข้อมูล
    $stmt = $conn->prepare("UPDATE users SET 
        firstname = ?, 
        lastname = ?, 
        meter_code = ?, 
        gender = ?, 
        birthdate = ?, 
        age = ?, 
        nationality = ?, 
        phone_number = ?, 
        house_number = ?, 
        village_number = ?, 
        subdistrict = ?, 
        district = ?, 
        province = ?, 
        id_card_number = ? 
        WHERE id = ?");
    $stmt->execute([$firstname, $lastname, $meter_code, $gender, $birthdate, $age, $nationality, $phone_number, $house_number, $village_number, $subdistrict, $district, $province, $id_card_number, $user_id]);

    $_SESSION['success'] = 'อัปเดตข้อมูลผู้ใช้สำเร็จ';

    // ใช้ echo เพื่อแสดง SweetAlert
    echo '<script>';
    echo 'Swal.fire({ icon: "success", title: "' . $_SESSION['success'] . '" });';
    echo '</script>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Edit User</title>
    <link rel="stylesheet" href="user_list_css/user_view.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">



</head>

<body>
    <!--************************************************************************************************************************
*                                                                                                                          -->
    <!--                                            start 
*                                                                                                                           *
***************************************************************************************************************************-->

    <div class="header">
        <h1>ระบบบริหารจัดการน้ำประปาหมู่บ้านม่วงเฒ่า</h1>
    </div>


    <div class="modal-container">
        <h2><i class="bi bi-person-lines-fill"></i>รายละเอียดผู้ใช้:
            <?php echo $user['firstname'] . ' ' . $user['lastname']; ?>
        </h2>

        <form method="post" class="user-form-modal">

            <div class="form-column">
                <div class="label1" for="firstname">ชื่อ:
                    <?php echo $user['firstname']; ?>
                </div>
                <div class="label2" for="lastname">นามสกุล:
                    <?php echo $user['lastname']; ?>
                </div>
            </div>


            <div class="form-column">
                <div class="label1" for="meter_code">เลขมิเตอร์:
                    <?php echo $user['meter_code']; ?>
                </div>
                <div class="label2" for="gender">เพศ:
                    <?php echo $user['gender']; ?>
                </div>
            </div>


            <div class="form-column">
                <div class="label1" for="birthdate">วันเกิด:
                    <?php echo $user['birthdate']; ?>
                </div>
                <div class="label2" for="age">อายุ:
                    <?php echo $user['age']; ?>
                </div>
            </div>


            <div class="form-column">
                <div class="label1" for="nationality">สัญชาติ:
                    <?php echo $user['nationality']; ?>
                </div>
                <div class="label2" for="phone_number">เบอร์โทรศัพท์:
                    <?php echo $user['phone_number']; ?>
                </div>
            </div>


            <div class="form-column">
                <div class="label1" for="house_number">บ้านเลขที่:
                    <?php echo $user['house_number']; ?>
                </div>
                <div class="label2" for="village_number">หมู่ที่:
                    <?php echo $user['village_number']; ?>
                </div>
            </div>


            <div class="form-column">
                <div class="label1" for="subdistrict">ตำบล:
                    <?php echo $user['subdistrict']; ?>
                </div>
                <div class="label2" for="district">อำเภอ:
                    <?php echo $user['district']; ?>
                </div>
            </div>


            <div class="form-column">
                <div class="label1" for="province">จังหวัด:
                    <?php echo $user['province']; ?>
                </div>
                <div class="label2" for="id_card_number">รหัสบัตรประชาชน:
                    <?php echo $user['id_card_number']; ?>
                </div>
            </div>

            <div class="back">
                <a href="user_list.php" class="back-button">ย้อนกลับ</a>
            </div>
        </form>
    </div>








    </div>
</body>

</html>