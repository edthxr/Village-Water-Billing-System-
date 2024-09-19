<?php
session_start();

require_once 'config/db.php';

if (isset($_GET['email']) && isset($_GET['token'])) {
    $email = $_GET['email'];
    $token = $_GET['token'];

    // ตรวจสอบว่ามี email และ token ตรงกับที่มีในฐานข้อมูลหรือไม่
    $check_reset = $conn->prepare("SELECT * FROM password_resets WHERE email = :email AND token = :token");
    $check_reset->bindParam(":email", $email);
    $check_reset->bindParam(":token", $token);
    $check_reset->execute();

    if ($check_reset->rowCount() > 0) {
        // แสดงฟอร์มรีเซ็ตรหัสผ่าน
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
            <meta name="viewport" content="width=device-width, initial-scale=0.75">
            <title>Reset Password</title>
            <link rel="stylesheet" href="login_css/style.css">
        </head>
        <h5>ระบบบริหารจัดการน้ำประปาหมู่บ้าน</h5>

        <body>

            <div class="container">
                <h3 class="mt-4">รีเซ็ตรหัสผ่าน</h3>

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

                <form action="reset_password_process.php" method="post">
                    <input type="hidden" name="email" value="<?= $email ?>">
                    <input type="hidden" name="token" value="<?= $token ?>">
                    <div class="mb-3">
                        <label for="new_password" class="form-label">รหัสผ่านใหม่</label>
                        <input type="password" class="form-control" name="new_password">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" class="form-control" name="confirm_password">
                    </div>
                    <button type="submit" name="reset_password" class="btn btn-primary">รีเซ็ตรหัสผ่าน</button>
                </form>

                <p><a href="index.php">เข้าสู่ระบบ</a></p>
            </div>

        </body>

        </html>
        <?php
    } else {
        $_SESSION['error'] = "ลิงค์ไม่ถูกต้องหรือหมดอายุ";
        header("location: index.php");
    }
} else {
    header("location: index.php");
}
?>

