<?php
// Include necessary files and check if the user is logged in
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['admin_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
    exit(); // ออกจากการทำงานเพื่อป้องกันการดำเนินการต่อ
}

// Function to format Thai month name
function thai_month($month) {
    $thai_months = array(
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน",
        "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม",
        "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    );
    return $thai_months[$month-1];
}

// ดึงข้อมูลทั้งหมดจากฐานข้อมูล
if(isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $recordsStmt = $conn->prepare("SELECT SUM(price) AS total_price, MONTH(reading_date) AS month, YEAR(reading_date) AS year FROM meter_records WHERE reading_date BETWEEN :start_date AND :end_date GROUP BY YEAR(reading_date), MONTH(reading_date)");
    $recordsStmt->bindParam(':start_date', $start_date);
    $recordsStmt->bindParam(':end_date', $end_date);
} else {
    // ถ้าไม่มีวันที่ค้นหาถูกส่งมา ให้ดึงข้อมูลทั้งหมด
    $recordsStmt = $conn->prepare("SELECT SUM(price) AS total_price, MONTH(reading_date) AS month, YEAR(reading_date) AS year FROM meter_records GROUP BY YEAR(reading_date), MONTH(reading_date)");
}

// ดึงข้อมูลและคำนวณยอดรวมทั้งหมด
$recordsStmt->execute();
$records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);

$totalSum = 0; // กำหนดตัวแปรเพื่อเก็บยอดรวมทั้งหมด
foreach ($records as $record) {
    $totalSum += $record['total_price']; // เพิ่มยอดรวมในแต่ละเดือนเข้าไปในยอดรวมทั้งหมด
}

$recordsStmt->execute();
$records = $recordsStmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Total Payment</title>
    <!-- CSS Styles -->
    <link rel="stylesheet" href="checktotal_css/total.css">
    <link rel="stylesheet" href="navbar/nav.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- เพิ่ม Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">




</head>
<body>
        <!-- Navigation Bar -->
        <?php
        require_once 'navbar/adminnavbar.php';
        ?>
    <div class="container">
        <h1>ยอดเงินทั้งหมดในแต่ละเดือน</h1>

<!-- เพิ่มฟอร์มค้นหา -->

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script> <!-- ใส่เพิ่มเพื่อเปลี่ยนภาษาเป็นไทย -->
<script>
    flatpickr('#start_date', {
        dateFormat: 'j F Y', // กำหนดรูปแบบวันที่เป็น 1/มกราคม/2024
        locale: 'th', // กำหนดภาษาเป็นไทย
        onClose: function(selectedDates, dateStr, instance) {
            // เมื่อผู้ใช้เลือกวันที่เสร็จสมบูรณ์ ปรับรูปแบบให้เป็น 1/มกราคม/2024
            let formattedDate = instance.formatDate(selectedDates[0], "j F Y");
            document.getElementById('start_date').value = formattedDate;
        }
    });
    flatpickr('#end_date', {
        dateFormat: 'j F Y', // กำหนดรูปแบบวันที่เป็น 1/มกราคม/2024
        locale: 'th', // กำหนดภาษาเป็นไทย
        onClose: function(selectedDates, dateStr, instance) {
            // เมื่อผู้ใช้เลือกวันที่เสร็จสมบูรณ์ ปรับรูปแบบให้เป็น 1/มกราคม/2024
            let formattedDate = instance.formatDate(selectedDates[0], "j F Y");
            document.getElementById('end_date').value = formattedDate;
        }
    });
</script>





        <!-- ตารางข้อมูล -->
        <table>
            <!-- ส่วนหัวตาราง -->
            <thead>
                <tr>
                    <th>เดือน</th>
                    <th>ปี</th>
                    <th>ยอดเงินทั้งหมด (บาท)</th>
                </tr>
            </thead>
            <tbody>
                <!-- ข้อมูลในตาราง -->
                <?php 
                $totalSum = 0; // กำหนดตัวแปรเพื่อเก็บยอดรวมสุทธิ
                foreach ($records as $record) : 
                    $totalSum += $record['total_price']; // เพิ่มยอดรวมในแต่ละเดือนเข้าไปในยอดรวมสุทธิ
                ?>
                    <tr>
                        <td><?php echo thai_month($record['month']); ?></td>
                        <td><?php echo $record['year']; ?></td>
                        <td><?php echo number_format($record['total_price'], 2); ?></td>
                        
                    </tr>
                <?php endforeach; ?>
                <!-- เพิ่มแถวสุดท้ายเพื่อแสดงยอดรวมสุทธิ -->
                <tr>
                    <td colspan="2" style="text-align: right;">ยอดรวมสุทธิ</td>
                    <td><?php echo number_format($totalSum, 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
 

</body>
</html>