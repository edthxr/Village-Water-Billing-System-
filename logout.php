

<?php
session_start();
$_SESSION['logout_success'] = 'ออกจากระบบสำเร็จ';
unset($_SESSION['user_login']);
unset($_SESSION['admin_login']);
header('location:signin.php');
exit();
?>