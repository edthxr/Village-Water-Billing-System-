<?php
session_start();

if (!isset($_SESSION['admin_login'])) {
    header("location: login.php");
    exit();
}

require_once 'config/db.php';

// ตรวจสอบว่ามีค่า id ที่ต้องการลบหรือไม่
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $report_id = $_GET['id'];

    // ลบข้อมูลจากตาราง problem_reports
    $delete_report = $conn->prepare("DELETE FROM problem_reports WHERE id = :report_id");
    $delete_report->bindParam(":report_id", $report_id);

    if ($delete_report->execute()) {
        echo "ลบข้อมูลเรียบร้อยแล้ว";
    } else {
        echo "เกิดข้อผิดพลาดในการลบข้อมูล";
    }

    // เพิ่มเส้นทางกลับไปที่หน้า admin.php
    header("location: admin.php");
    exit();
} else {
    echo "ไม่ได้ระบุรายการที่ต้องการลบ";
}



// แสดงรายงานปัญหา
echo '<h2>ปัญหาที่ได้รับแจ้ง</h2>';
echo '<p>การคำร้องแจ้งปัญหาจะถูกลบออกภายใน 30 วัน</p>';
echo '<table border="1">';
echo '<tr><th>User ID</th><th>ชื่อ</th><th>นามสกุล</th><th>อีเมล</th><th>รายละเอียดปัญหา</th></tr>';
foreach ($reports as $report) {
    echo '<tr>';
    echo '<td>' . $report['user_id'] . '</td>';

    // ดึงข้อมูลผู้ใช้
    $fetch_user_info = $conn->prepare("SELECT firstname, lastname, email FROM users WHERE id = :user_id");
    $fetch_user_info->bindParam(":user_id", $report['user_id']);
    $fetch_user_info->execute();
    $user_info = $fetch_user_info->fetch(PDO::FETCH_ASSOC);

    echo '<td>' . $user_info['firstname'] . '</td>';
    echo '<td>' . $user_info['lastname'] . '</td>';
    echo '<td>' . $user_info['email'] . '</td>';

    echo '<td>' . $report['problem_description'] . '</td>';
    echo '</tr>';
}
echo '</table>';
echo '<a href="admin.php" class="back-button">กลับ</a>';

// ... โค้ดที่อยู่ข้างล่าง ...
?>
