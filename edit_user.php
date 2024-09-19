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
    $nationality = $_POST['nationality'];
    $phone_number = $_POST['phone_number'];
    $house_number = $_POST['house_number'];
    $village_number = $_POST['village_number'];
    $subdistrict = $_POST['subdistrict'];
    $district = $_POST['district'];
    $province = $_POST['province'];
    $id_card_number = $_POST['id_card_number'];


    // อัปเดตข้อมูลผู้ใช้ในฐานข้อมูล
    $stmt = $conn->prepare("UPDATE users SET 
        firstname = ?, 
        lastname = ?, 
        meter_code = ?, 
        gender = ?, 
        nationality = ?, 
        phone_number = ?, 
        house_number = ?, 
        village_number = ?, 
        subdistrict = ?, 
        district = ?, 
        province = ?, 
        id_card_number = ? 
        WHERE id = ?");
    $stmt->execute([$firstname, $lastname, $meter_code, $gender, $nationality, $phone_number, $house_number, $village_number, $subdistrict, $district, $province, $id_card_number, $user_id]);

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
    <link rel="stylesheet" href="user_list_css/user_edit.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">
</head>

<body>



    <!--************************************************************************************************************************
*                                                                                                                          -->
    <!----------------------------------------------START--ฟอร์ม แก้ไขข้อมูลผู้ใช้---------------------------------------------------------
*                                                                                                                           *
***************************************************************************************************************************-->



    <div class="container">
        <h2><i class="bi bi-pencil-square custom-icon"></i>แก้ไขข้อมูลผู้ใช้ :
            <?php echo $user['firstname'] . ' ' . $user['lastname']; ?>
        </h2>

        <form method="post" class="user-form">
            <div class="form-column">
                <label for="firstname">ชื่อ :</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo $user['firstname']; ?>" required>
            </div>

            <div class="form-column">
                <label for="lastname">นามสกุล :</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo $user['lastname']; ?>" required>
            </div>

            <div class="form-column">
                <label for="meter_code">เลขมิเตอร์ :</label>
                <input type="text" id="meter_code" name="meter_code" value="<?php echo $user['meter_code']; ?>" required
                    oninput="checkMeterCode()">
                <div id="meter_code_comment"></div> <!-- ส่วนนี้จะแสดงผลลัพธ์การตรวจสอบ meter_code -->
            </div>

            <div class="form-column">
                <label for="gender">เพศ :</label>
                <input type="text" id="gender" name="gender" value="<?php echo $user['gender']; ?>" required hidden>
                <div class="ganderinput">
                    <input type="radio" name="gender" value="ชาย" <?php echo ($user['gender'] === 'ชาย') ? 'checked' : ''; ?>> ชาย
                    <input type="radio" name="gender" value="หญิง" <?php echo ($user['gender'] === 'หญิง') ? 'checked' : ''; ?>> หญิง
                </div>
            </div>



            <div class="form-column">
                <label for="nationality">สัญชาติ :</label>
                <input type="text" id="nationality" name="nationality" value="<?php echo $user['nationality']; ?>"
                    required>
            </div>

            <div class="form-column">
                <label for="phone_number">เบอร์โทรศัพท์ :</label>
                <input type="number" id="phone_number" name="phone_number" value="<?php echo $user['phone_number']; ?>"
                    required>
            </div>

            <div class="form-column">
                <label for="house_number">บ้านเลขที่ :</label>
                <input type="text" id="house_number" name="house_number" value="<?php echo $user['house_number']; ?>"
                    required>
            </div>

            <div class="form-column">
                <label for="village_number">หมู่ที่ :</label>
                <input type="number" id="village_number" name="village_number"
                    value="<?php echo $user['village_number']; ?>" required>
            </div>

            <div class="form-column">
                <label for="subdistrict">ตำบล :</label>
                <input type="text" id="subdistrict" name="subdistrict" value="<?php echo $user['subdistrict']; ?>"
                    required>
            </div>

            <div class="form-column">
                <label for="district">อำเภอ :</label>
                <input type="text" id="district" name="district" value="<?php echo $user['district']; ?>" required>
            </div>

            <div class="form-column">
                <label for="province">จังหวัด :</label>
                <input type="text" id="province" name="province" value="<?php echo $user['province']; ?>" required>
            </div>

            <div class="form-column">
                <label for="id_card_number">รหัสบัตรประชาชน :</label>
                <input type="number" id="id_card_number" name="id_card_number"
                    value="<?php echo $user['id_card_number']; ?>" required>
            </div>



            <!--************************************************************************************************************************
*                                                                                                                          -->
            <!--                                             END ฟอร์ม แก้ไขข้อมูลผู้ใช้
*                                                                                                                           *
***************************************************************************************************************************-->





            <!-------------------------------------------------ปุ่ม บันทึก และยกเลิก ---------------------------------------------------------->

            <div class="btnsave">
                <a href="#" id="saveLink" class="button-style green">บันทึก</a>
            </div>
            <div class="btncancel">
                <a href="user_list.php" class="button-style red">ยกเลิก</a>
            </div>
        </form>
    </div>

    <!------------------------------------------------------------------------------------------------------------------------>

    </div>

    </div>
    </div>
    </div>


    </div>
</body>

<!--************************************************************************************************************************
*                                                                                                                          -->
<!--                                             START SCRIPT JAVA
*                                                                                                                           *
***************************************************************************************************************************-->
<!-- ตรวจสอบ meter_code แบบ Real-time ด้วย JavaScript -->
<script>
    // ฟังก์ชันที่ถูกเรียกเมื่อมีการเปลี่ยนแปลงใน input field meter_code
    function checkMeterCode() {
        var meterCodeInput = document.getElementById('meter_code');
        var meterCodeComment = document.getElementById('meter_code_comment');

        // ทำการตรวจสอบ meter_code แบบ Real-time โดยใช้ AJAX
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'check_meter_code.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // รับข้อมูลที่ส่งกลับมาจากเซิร์ฟเวอร์
                var response = xhr.responseText;

                // แสดงผลลัพธ์ที่ได้ใน Comment
                meterCodeComment.innerHTML = response;
            }
        };
        // ส่งค่า meter_code ไปยังเซิร์ฟเวอร์
        xhr.send('meter_code=' + meterCodeInput.value);
    }
</script>



<script>
    document.getElementById('saveLink').addEventListener('click', function () {
        // ส่งแบบฟอร์มเมื่อคลิกที่ลิงก์
        document.forms[0].submit();
    });
</script>

<script>
    function calculateAge() {
        var birthdateInput = document.getElementById('birthdate');
        var ageInput = document.getElementById('age');

        // ดึงข้อมูลวันเกิดจาก Input
        var birthdate = new Date(birthdateInput.value);

        // คำนวณอายุ
        var today = new Date();
        var age = today.getFullYear() - birthdate.getFullYear();

        // ตรวจสอบว่าวันเกิดยังไม่ถึงก็ลดอายุลง 1 ปี
        if (today.getMonth() < birthdate.getMonth() || (today.getMonth() === birthdate.getMonth() && today.getDate() < birthdate.getDate())) {
            age--;
        }

        // แสดงผลที่ฟิลด์อายุ
        ageInput.value = age;
    }

    <?php if (isset($_SESSION['success'])): ?>
        // แสดง SweetAlert เมื่อมีการอัปเดตข้อมูลสำเร็จ
        Swal.fire({
            icon: 'success',
            title: '<?= $_SESSION['success'] ?>',
            showCloseButton: true,  // เพิ่มตัวเลือกนี้เพื่อแสดงปุ่มปิด
        }).then((result) => {
            // หากผู้ใช้คลิก OK ใน SweetAlert ให้ redirect ไปที่ user_list.php
            if (result.isConfirmed || result.dismiss === Swal.DismissReason.backdrop) {
                window.location.href = 'user_list.php';
            }
        });
        <?php unset($_SESSION['success']); // เคลียร์ตัวแปรเซสชันหลังจากแสดง SweetAlert ?>
    <?php endif; ?>
</script>

</html>

<!----------------------------------------------------------script END----------------------------------------------------->