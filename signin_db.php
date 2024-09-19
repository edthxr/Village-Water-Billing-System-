<?php

session_start();
require_once 'config/db.php';

if (isset($_POST['signin'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];


    if (empty($email)) {
        $_SESSION['error'] = 'ท่านยังไม่ได้กรอกEmail';
        header("location: signin.php");
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'รูปแบบอีเมลไม่ถูกต้อง';
        header("location: signin.php");
    } else if (empty($password)) {
        $_SESSION['error'] = 'กรุณากรอกรหัสผ่าน';
        header("location: signin.php");
    } else if (strlen($_POST['password']) > 20 || strlen($_POST['password']) < 5) {
        $_SESSION['error'] = 'รหัสผ่านผิดพลาด';
        header("location: signin.php");
    } else {
        try {

            $check_data = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $check_data->bindParam(":email", $email);
            $check_data->execute();
            $row = $check_data->fetch(PDO::FETCH_ASSOC);

            if ($check_data->rowCount() > 0) {

                if ($email == $row['email']) {
                    if (password_verify($password, $row['password'])) {
                        if ($row['urole'] == 'admin') {
                            $_SESSION['admin_login'] = $row['id'];
                            $_SESSION['login_success'] = 'เข้าสู่ระบบสำเร็จ';
                            header("location: admin.php");
                            exit(); // อย่าลืมใส่ exit() เพื่อให้โค้ดหยุดทำงานทันที
                        } else {
                            $_SESSION['user_login'] = $row['id'];
                            $_SESSION['login_success'] = 'เข้าสู่ระบบสำเร็จ';
                            header("location: user.php");
                            exit(); // อย่าลืมใส่ exit() เพื่อให้โค้ดหยุดทำงานทันที
                        }

                    } else {
                        $_SESSION['error'] = 'รหัสผ่านผิด';
                        header("location: signin.php");
                    }
                } else {
                    $_SESSION['error'] = 'อีเมลผิด';
                    header("location: signin.php");
                }
            } else {
                $_SESSION['error'] = "ไม่มีข้อมูลในระบบ";
                header("location: signin.php");
            }

        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }
}


?>