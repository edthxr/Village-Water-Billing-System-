<?php

session_start();
require_once 'config/db.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="viewport" content="width=device-width, initial-scale=0.75">
    <title>Registration System PDO</title>
    <link rel="stylesheet" href="register_css/register.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">
</head>

<body>


    <!--************************************************************************************************************************
*                                                                                                                          -->
    <!--                                            START ฟอร์ม หน้าสมัครสมาชิก
*                                                                                                                           *
***************************************************************************************************************************-->



    <div class="container">
        <form action="adduser_db.php" method="post">
            <h2><i class="bi bi-person-vcard-fill"></i>สมัครสมาชิก</h2>


            <div class="input-box">
                <input type="text" class="input" name="firstname" aria-describedby="firstname" placeholder">
                <label for="firstname" class="label">ชื่อ</label>
                <div class="o"><i></i></div>
            </div>
            <div class="input-box">
                <input type="text" class="input" name="lastname" aria-describedby="lastname">
                <label for="lastname" class="label">นามสกุล</label>
                <div class="o"><i></i></div>
            </div>
            <div class="input-box">
                <input type="email" class="input" name="email" aria-describedby="email">
                <label for="email" class="label">อีเมล</label>
                <div class="o"><i></i></div>
            </div>
            <div class="input-box">
                <input type="password" class="input" name="password">
                <label for="password" class="label">พาสเวิร์ด</label></label>
                <div class="o"><i></i></div>
            </div>
            <div class="input-box">
                <input type="password" class="input" name="c_password">
                <label for="confirm password" class="label">ยืนยัน พาสเวิร์ด</label>
                <div class="o"><i></i></div>
            </div>
            <button type="submit" name="signup" class="btn">สมัครสมาชิก</button>

            <div class="link">
                <a href="admin.php">ย้อนกลับ</a>
            </div>


    </div>
    </form>

    <!--************************************************************************************************************************
*                                                                                                                          -->
    <!--                                            end ฟอร์ม หน้าสมัครสมาชิก
*                                                                                                                           *
***************************************************************************************************************************-->



    <!--================================alert แจ้งเตือนต่างๆ =====================================-->
    <script>
        <?php if (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: '<?= $_SESSION['error'] ?>',
                text: 'กรุณาลองใหม่อีกครั้ง'
            });
            <?php unset($_SESSION['error']); endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({
                icon: 'success',
                title: '<?= $_SESSION['success'] ?>',
            }).then((result) => {
                // หากผู้ใช้คลิก OK ใน SweetAlert ให้ redirect ไปที่ user_list.php
                if (result.isConfirmed || result.dismiss === Swal.DismissReason.backdrop) {
                    window.location.href = 'signin.php';
                }
            });


            <?php unset($_SESSION['success']); endif; ?>

        <?php if (isset($_SESSION['warning'])): ?>
            Swal.fire({
                icon: 'warning',
                title: '<?= $_SESSION['warning'] ?>',
                text: 'มีอีเมลล์นี้อยู่ในระบบแล้ว'
            });
            <?php unset($_SESSION['warning']); endif; ?>


    </script>
    <!--================================END alert แจ้งเตือนต่างๆ =====================================-->
</body>

</html>