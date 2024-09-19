<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$servername;registration_system", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    /* echo "Connected successfully";*/
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>login</title>
    <link rel="stylesheet" href="login_css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">


</head>
<div id="alert-container"></div>

<body>
    <div class="font-header">
        <h1>ระบบบริหารจัดการน้ำประปาหมู่บ้านม่วงเฒ่า</h1>
    </div>
    <div class="container">

        <span class="borderLine"></span>
        <form action="signin_db.php" method="post">
            <h2><i class="bi bi-person-circle"></i> เข้าสู่ระบบ</h2>
            <div class="input-box">
                <input type="email" class="input" name="email" aria-describedby="email" placeholder="">

                <label for="email" class="label">Email </label>
                <div class="o"><i></i></div>
            </div>
            <div class="input-box">
                <input type="password" class="input" name="password" aria-describedby="password" placeholder="">
                <label for="password" class="label">Password</label>
                <div class="o"><i></i></div>
            </div>
            <div class="link">
                <a href="index.php" id="register-link"><i class="bi bi-person-vcard-fill"></i> สมัครสมาชิก</a>
                <a href="forgot_password.php">ลืมรหัสผ่าน?</a>

            </div>
            <button type="submit" name="signin" class="btn">เข้าสู่ระบบ</button>

        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Script สำหรับแสดง SweetAlert -->
    <script>


        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: '<?= $_SESSION['error'] ?>',
                text: 'โปรดลองใหม่อีกครั้ง'
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>


        <?php if (isset($_SESSION['logout_success'])): ?>
            Swal.fire({
                icon: 'warning',
                title: '<?= $_SESSION['logout_success'] ?>',
            });
            <?php unset($_SESSION['logout_success']); ?>
        <?php endif; ?>


        <?php if (isset($_SESSION['forgot_success'])): ?>
            Swal.fire({
                icon: 'success',
                title: '<?= $_SESSION['forgot_success'] ?>',
            });
            <?php unset($_SESSION['forgot_success']); ?>
        <?php endif; ?>

    </script>



</body>

</html>