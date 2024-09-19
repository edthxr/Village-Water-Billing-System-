<?php
session_start();

require_once 'config/db.php';

if (!isset($_SESSION['admin_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $record_id = $_POST['record_id'];
    $previous_reading = $_POST['previous_reading'];
    $current_reading = $_POST['current_reading'];
    $price = $_POST['price'];
    $admin_name = $_POST['submit_by'];

    // ทำการอัพเดทข้อมูลในตาราง meter_records ในฐานข้อมูล
    $updateRecordStmt = $conn->prepare("UPDATE meter_records SET previous_reading = :previous_reading, submit_by = :submit_by, current_reading = :current_reading, submit_by = :submit_by, price = :price WHERE id = :record_id");

    $updateRecordStmt->bindParam(':previous_reading', $previous_reading, PDO::PARAM_INT);
    $updateRecordStmt->bindParam(':current_reading', $current_reading, PDO::PARAM_INT);
    $updateRecordStmt->bindParam(':price', $price, PDO::PARAM_INT);
    $updateRecordStmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
    $updateRecordStmt->bindParam(':submit_by', $admin_name, PDO::PARAM_STR);

    if ($updateRecordStmt->execute()) {
        $_SESSION['success'] = 'บันทึกการใช้น้ำถูกอัพเดทเรียบร้อยแล้ว';
        header('location: waterusage.php');
        exit();
    } else {
        $_SESSION['error'] = 'มีข้อผิดพลาดเกิดขึ้นในการอัพเดทข้อมูล';
        header('location: edit_meter_reading.php?record_id=' . $record_id);
        exit();
    }
} else {
    $_SESSION['error'] = 'คำขอไม่ถูกต้อง';
    header('location: waterusage.php');
    exit();
}
?>
