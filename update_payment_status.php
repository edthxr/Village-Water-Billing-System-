<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['admin_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
    exit();
}

$record_id = isset($_POST['record_id']) ? $_POST['record_id'] : null;
$payment_status = isset($_POST['payment_status']) ? $_POST['payment_status'] : null;
$payment_status_by = isset($_POST['Payment_status_by']) ? $_POST['Payment_status_by'] : null; // เพิ่มการดึงชื่อผู้ปรับสถานะการชำระเงินจากฟอร์ม

// ตรวจสอบว่า record_id, payment_status, และ payment_status_by ไม่เป็นค่าว่าง
if ($record_id && $payment_status && $payment_status_by) {
    // ทำการปรับสถานะในฐานข้อมูล
    $updateStmt = $conn->prepare("UPDATE meter_records SET payment_status = :payment_status, payment_status_by = :payment_status_by WHERE id = :record_id");
    $updateStmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
    $updateStmt->bindParam(':payment_status', $payment_status, PDO::PARAM_STR);
    $updateStmt->bindParam(':payment_status_by', $payment_status_by, PDO::PARAM_STR); // ผูกค่าชื่อผู้ปรับสถานะการชำระเงิน

    if ($updateStmt->execute()) {
        $_SESSION['success'] = 'ปรับสถานะการชำระเงินเรียบร้อย';
    } else {
        $_SESSION['error'] = 'เกิดข้อผิดพลาดในการปรับสถานะการชำระเงิน';
    }
} else {
    $_SESSION['error'] = 'ไม่พบ record_id, payment_status, หรือ payment_status_by';
}

// รีไปที่หน้า check_payment.php?id=
if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    header('location: ' . $_SERVER['HTTP_REFERER']);
} else {
    // หากไม่มี HTTP_REFERER ให้รีไปที่หน้าหลักหรือหน้าที่คุณต้องการ
    header('location: index.php'); // แทนที่ index.php ด้วย URL ที่ต้องการ
}

exit();
?>
