<?php
// เริ่ม session
session_start();

// ตรวจสอบว่าเป็นการส่งข้อมูลแบบ POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่ามีข้อมูล email และไม่ใช่ค่าว่าง
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        $email = $_POST['email'];

        // เชื่อมต่อกับฐานข้อมูล
        $servername = "localhost";
        $username = "root";
        $password = "";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=registration_system", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // สร้างคำสั่ง SQL สำหรับดึงรหัสผ่านจากอีเมล
            $stmt = $conn->prepare("SELECT password FROM users WHERE email = :email");
            $stmt->bindParam(":email", $email);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // ตรวจสอบว่าพบผลลัพธ์หรือไม่
            if ($result) {
                $password = $result['password'];

                // ส่งอีเมล
                $subject = 'รีเซ็ตรหัสผ่าน';
                $message = 'รหัสผ่านของคุณคือ: ' . $password;
                mail($email, $subject, $message);

                // แสดงข้อความสำเร็จ
                $_SESSION['forgot_success'] = 'ระบบได้ส่งรหัสผ่านไปยังอีเมลของคุณแล้ว';
            } else {
                $_SESSION['error'] = 'ไม่พบอีเมลนี้ในระบบ';
            }

            // ปิดการเชื่อมต่อ
            $conn = null;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }

        // โอนเว็บไซต์ไปที่หน้าหลัก
        header('Location: signin.php');
        exit();
    } else {
        $_SESSION['error'] = 'กรุณากรอกอีเมลของคุณ';
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="login_css/forgot.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">
</head>

<body>


    <!--************************************************************************************************************************
*                                                                                                                          -->
    <!--                                             start ฟอร์ม หน้าลืมรหัสผ่าน
*                                                                                                                           *
***************************************************************************************************************************-->




    <div class="container">

        <form action="forgot_password.php" method="post">
            <h2><i class="bi bi-person-fill-exclamation"></i> ลืมรหัสผ่าน</h2>
            <div class="input-box">
                <input type="email" class="input" name="email" aria-describedby="email"
                    placeholder="กรุณากรอกอีเมลของคุณ">
                <label for="email" class="label">Email</label>
                <i></i>
            </div>
            <button type="submit" name="forgot_password" class="btn">ส่งรหัสผ่านไปที่อีเมล</button>

            <div class="link">
                <a href="signin.php">เข้าสู่ระบบ</a>
            </div>
        </form>
    </div>
    <?php if (isset($_SESSION['error'])) { ?>
        <div class="alert alert-danger" role="alert">
            <?php
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php } ?>
    <?php if (isset($_SESSION['success'])) { ?>
        <div class="alert alert-success" role="alert">
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>

        </div>
    <?php } ?>

    <!--************************************************************************************************************************
*                                                                                                                          -->
    <!--                                            END ฟอร์ม หน้าลืมรหัสผ่าน
*                                                                                                                           *
***************************************************************************************************************************-->



</body>

</html>