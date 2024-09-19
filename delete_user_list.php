<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['admin_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // ทำการลบ
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $delete_stmt->bindParam(':id', $user_id, PDO::PARAM_INT);

    if ($delete_stmt->execute()) {
        $_SESSION['success'] = 'ลบข้อมูลผู้ใช้เรียบร้อยแล้ว';
    } else {
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการลบข้อมูลผู้ใช้';
    }

    header('location: user_list.php');
    exit();
} else {
    $_SESSION['error'] = 'ไม่พบรหัสผู้ใช้ที่ต้องการลบ';
    header('location: user_list.php');
    exit();
}
?>