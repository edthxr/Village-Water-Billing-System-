<?php
session_start();

// Include ไฟล์ที่จำเป็นและตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบหรือไม่
require_once 'config/db.php';
if (!isset($_SESSION['admin_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
}

// กำหนด locale เป็นภาษาไทย
setlocale(LC_TIME, 'th_TH.utf8', 'th_TH');

// สร้างวัตถุ DateTime จากข้อมูลที่ได้รับ
$readingDate = DateTime::createFromFormat('d monthName Y', $_POST['reading_date'], new DateTimeZone('Asia/Bangkok'));

// ถ้าไม่สามารถสร้าง DateTime ได้ กำหนดให้เป็นวันที่และเวลาปัจจุบัน
if ($readingDate === false) {
    $readingDate = new DateTime('now', new DateTimeZone('Asia/Bangkok'));
} else {
    // เพิ่มเวลาปัจจุบัน
    $readingDate->setTime(date('H'), date('i'), date('s'));
}

// ตรวจสอบว่าสร้าง DateTime สำเร็จหรือไม่
if ($readingDate === false) {
    $_SESSION['error'] = 'Error: Invalid date format';
    header('location: waterusage.php?error_message=' . urlencode('Invalid date format'));
    exit();
}

// ดึงข้อมูลเดือนและปี
$readingDateFormatted = $readingDate->format('Y-m-d H:i:s');
$day = $readingDate->format('d');
$monthName = $readingDate->format('F'); // F คือฟอร์แมตให้เป็นชื่อเดือน
$year = $readingDate->format('Y'); // Y คือฟอร์แมตให้เป็นปี



// ตรวจสอบว่าเป็น HTTP POST request หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ดึงข้อมูลจากฟอร์ม
    $user_name = $_POST['user_name'];
    $meter_code = $_POST['meter_code'];
    $user_id = $_POST['user_id'];
    $reading_date = $_POST['reading_date'];
    $previous_reading = $_POST['previous_reading'];
    $current_reading = $_POST['current_reading'];
    $price = $_POST['price'];
    $usage = $_POST['current_reading'] - $_POST['previous_reading'];
    $house_number = $_POST['house_number'];
    $admin_name = $_POST['submit_by'];
    $payment_status = $_POST['payment_status'];

    // เตรียมคำสั่ง SQL สำหรับการเพิ่มข้อมูลลงในฐานข้อมูล
    $stmt = $conn->prepare("INSERT INTO meter_records (user_id, reading_date, previous_reading, current_reading, usage_value, price, user_name, meter_code, month_name, year, house_number, day, submit_by, payment_status) VALUES (:user_id, :reading_date, :previous_reading, :current_reading, :usage_value, :price, :user_name, :meter_code, :month_name, :year, :house_number, :day , :submit_by, :payment_status)");

    // กำหนดค่าพารามิเตอร์ในคำสั่ง SQL
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':reading_date', $readingDate->format('Y-m-d H:i:s'), PDO::PARAM_STR);
    $stmt->bindParam(':previous_reading', $previous_reading, PDO::PARAM_INT);
    $stmt->bindParam(':current_reading', $current_reading, PDO::PARAM_INT);
    $stmt->bindParam(':price', $price, PDO::PARAM_STR);
    $stmt->bindParam(':user_name', $user_name, PDO::PARAM_STR);
    $stmt->bindParam(':meter_code', $meter_code, PDO::PARAM_STR);
    $stmt->bindParam(':usage_value', $usage, PDO::PARAM_INT);
    $stmt->bindParam(':month_name', $monthName, PDO::PARAM_STR);
    $stmt->bindParam(':year', $year, PDO::PARAM_STR);
    $stmt->bindParam(':house_number', $house_number, PDO::PARAM_STR);
    $stmt->bindParam(':day', $day, PDO::PARAM_INT);
    $stmt->bindParam(':submit_by', $admin_name, PDO::PARAM_STR);
    $stmt->bindParam(':payment_status', $payment_status, PDO::PARAM_STR);

    try {
        // ทำการ execute คำสั่ง SQL
        if ($stmt->execute()) {
            $_SESSION['success'] = 'บันทึกมิเตอร์เรียบร้อยแล้ว';
            header('location: waterusage.php');
        } else {
            $_SESSION['error'] = 'เกิดข้อผิดพลาดในการบันทึกมิเตอร์';
            header('location: waterusage.php?error_message=' . urlencode('Error in executing the statement'));
        }
    } catch (PDOException $e) {
        // ---------------กรณีเกิด error ในการ execute-------------
        error_log('Error: ' . $e->getMessage());
        $_SESSION['error'] = 'An error occurred while processing your request. Please try again later.';
        header('location: waterusage.php');
        exit();
    }
} else {
    // กรณีที่ไม่ใช่ HTTP POST request, ทำการ redirect กลับไปที่หน้า waterusage.php
    header('location: waterusage.php');
    exit();
}
?>