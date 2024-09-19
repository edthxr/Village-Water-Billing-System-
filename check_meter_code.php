<?php
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['meter_code'])) {
    $meter_code = $_POST['meter_code'];

    // ตรวจสอบว่า meter_code ซ้ำหรือไม่
    $stmt_check_meter_code = $conn->prepare("SELECT * FROM users WHERE meter_code = ?");
    $stmt_check_meter_code->execute([$meter_code]);
    $existing_user = $stmt_check_meter_code->fetch(PDO::FETCH_ASSOC);

    if ($existing_user) {
        echo '<span style="color: red;">*เลขมิเตอร์ซ้ำ</span>';
    } else {
        echo '<span style="color: green;">*สามารถใช้งานได้</span>';
    }
}
?>