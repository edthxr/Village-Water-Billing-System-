<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
    exit();
}

$user_id = $_SESSION['user_login'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงรายการที่ยังค้างชำระจากฐานข้อมูล
$unpaidRecordsStmt = $conn->prepare("SELECT * FROM meter_records WHERE user_id = :user_id AND (payment_status = 'ค้างชำระ' OR payment_status = 'รอดำเนินการ'OR payment_status = 'หลักฐานผิดพลาด')");
$unpaidRecordsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$unpaidRecordsStmt->execute();
$unpaidRecords = $unpaidRecordsStmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงรายการที่ชำระเงินแล้วจากฐานข้อมูล
$paidRecordsStmt = $conn->prepare("SELECT * FROM meter_records WHERE user_id = :user_id AND payment_status = 'ชำระเงินเรียบร้อย'");
$paidRecordsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$paidRecordsStmt->execute();
$paidRecords = $paidRecordsStmt->fetchAll(PDO::FETCH_ASSOC);
// ดึงรายการที่ชำระเงินแล้วจากฐานข้อมูล


?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าชำระเงิน</title>

    <!-- ลิงค์ CSS  -->
    <link rel="stylesheet" href="payment_user_css/payment.css">

    <!-- css navbar -->
    <link rel="stylesheet" href="navbaruser/navuser.css">
    <style>
        .payment-form {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <?php
    require_once 'navbaruser/usernavbar.php';

    ?>

    <div class="main">

        <div class="payment_unpaid">
            <a>รายการค้างชำระ</a>

            <p>ชื่อผู้ใช้: <span id="username">
                    <?php echo $user['firstname'] . ' ' . $user['lastname']; ?>
                </span></p>
            <p>เลขมิเตอร์: <span id="meterCode">
                    <?php echo $user['meter_code']; ?>
                </span></p>
        </div>


        <div class="payment-container">
            <?php if (count($unpaidRecords) > 0): ?>
                <?php foreach ($unpaidRecords as $record): ?>
                    <div class="payment-form">
                        <p>ประจำเดือน:
                            <?php echo getThaiMonth($record['month_name']); ?>
                        </p>
                        <p>วันที่บันทึก:
                            <?php echo $record['reading_date']; ?>
                        </p>
                        <p>ปริมาณการใช้น้ำ (หน่วย):
                            <?php echo $record['usage_value']; ?>
                        </p>
                        <p>ราคาที่ต้องชำระ (บาท):
                            <?php echo $record['price']; ?>
                        </p>
                        <p>สถานะการชำระเงิน: <span style="color: 
                        
                        <?php
                        if ($record['payment_status'] == 'ชำระเงินแล้ว') {
                            echo 'green'; // สีเขียวสำหรับ "ชำระเงินแล้ว"
                        } elseif ($record['payment_status'] == 'รอดำเนินการ' || $record['payment_status'] == 'หลักฐานผิดพลาด') {
                            echo 'orange'; // สีส้มสำหรับ "รอดำเนินการ" และ "หลักฐานผิดพลาด"
                        } else {
                            echo 'red'; // สีแดงสำหรับสถานะอื่น ๆ
                        }
                        ?>

            ">
                                <?php echo $record['payment_status']; ?>
                            </span></p>


                        <button class="btn btn-primary"
                            onclick="window.location.href='payment_page.php?record_id=<?php echo $record['id']; ?>'">ชำระเงิน</button>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>ไม่มีรายการค้างชำระ</p>
            <?php endif; ?>


            <?php
            // Function to convert English month names to Thai
            function getThaiMonth($englishMonth)
            {
                $monthMap = [
                    'January' => 'มกราคม',
                    'February' => 'กุมภาพันธ์',
                    'March' => 'มีนาคม',
                    'April' => 'เมษายน',
                    'May' => 'พฤษภาคม',
                    'June' => 'มิถุนายน',
                    'July' => 'กรกฎาคม',
                    'August' => 'สิงหาคม',
                    'September' => 'กันยายน',
                    'October' => 'ตุลาคม',
                    'November' => 'พฤศจิกายน',
                    'December' => 'ธันวาคม',
                ];

                return $monthMap[$englishMonth];
            }
            ?>
        </div>
    </div>

    <div class="main">

        <div class="payment_unpaid">
            <a>ประวัติการชำระเงิน</a>

        </div>

        <div class="payment-container">
            <?php if (count($paidRecords) > 0): ?>
                <?php foreach ($paidRecords as $record): ?>
                    <div class="payment-form">
                        <p>ประจำเดือน:
                            <?php echo getThaiMonth($record['month_name']); ?>
                        </p>
                        <p>วันที่บันทึก:
                            <?php echo $record['reading_date']; ?>
                        </p>
                        <p>ปริมาณการใช้น้ำ (หน่วย):
                            <?php echo $record['usage_value']; ?>
                        </p>
                        <p>ราคาที่ต้องชำระ (บาท):
                            <?php echo $record['price']; ?>
                        </p>
                        <p>สถานะการชำระเงิน: <span style="color: green;">
                                ชำระเงินแล้ว
                            </span></p>
                            <p>ยืนยันการชำระโดย : <?php echo $record['payment_status_by']; ?></p>

                        <!-- ปุ่มเปิด modal เพื่อดูรูปภาพ -->
                        <button type="button" class="viewimage" data-toggle="modal"
                            data-target="#proofModal<?php echo $record['id']; ?>">
                            ดูหลักฐานการชำระเงิน
                        </button>


                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>ไม่มีประวัติการชำระเงิน</p>
            <?php endif; ?>


            <!-- เพิ่ม modal สำหรับแสดงรูปภาพ -->
            <?php foreach ($paidRecords as $record): ?>
                <div class="modal" id="proofModal<?php echo $record['id']; ?>" tabindex="-1" role="dialog"
                    aria-labelledby="proofModalLabel<?php echo $record['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="proofModalLabel<?php echo $record['id']; ?>">หลักฐานการชำระเงิน
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <?php
                                // ดึงข้อมูลรูปภาพจากฐานข้อมูล
                                $proofsStmt = $conn->prepare("SELECT * FROM proofs WHERE record_id = :record_id");
                                $proofsStmt->bindParam(':record_id', $record['id'], PDO::PARAM_INT);
                                $proofsStmt->execute();
                                $proofs = $proofsStmt->fetchAll(PDO::FETCH_ASSOC);

                                // ถ้ามีรูปภาพให้แสดง
                                if ($proofs) {
                                    foreach ($proofs as $proof) {
                                        echo '<img src="' . $proof['file_path'] . '" alt="Proof of Payment" class="img-fluid">';
                                    }
                                } else {
                                    echo '<p>ไม่พบหลักฐานการชำระเงิน</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    </div>
    </div>

    <div class="footer">

    </div>

</body>

</html>