<?php
session_start();

require_once 'config/db.php';

if (!isset($_SESSION['admin_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
    exit();
}

// เพิ่ม code เพื่อดึงชื่อ admin จากฐานข้อมูล
$admin_id = $_SESSION['admin_login']; // หากมี session ที่เก็บ ID ของ admin ที่ล็อกอินอยู่
$adminStmt = $conn->prepare("SELECT * FROM users WHERE id = :admin_id");
$adminStmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
$adminStmt->execute();
$admin = $adminStmt->fetch();



$user_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$user_id) {
    $_SESSION['error'] = 'ไม่พบข้อมูลผู้ใช้';
    header('location: user_list.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$paidRecordsStmt = $conn->prepare("SELECT * FROM meter_records WHERE user_id = :user_id AND payment_status = 'ชำระเงินเรียบร้อย'");
$paidRecordsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$paidRecordsStmt->execute();
$paidRecords = $paidRecordsStmt->fetchAll(PDO::FETCH_ASSOC);

$unpaidRecordsStmt = $conn->prepare("SELECT * FROM meter_records WHERE user_id = :user_id AND payment_status = 'ค้างชำระ'");
$unpaidRecordsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$unpaidRecordsStmt->execute();
$unpaidRecords = $unpaidRecordsStmt->fetchAll(PDO::FETCH_ASSOC);

$pendingRecordsStmt = $conn->prepare("SELECT * FROM meter_records WHERE user_id = :user_id AND payment_status = 'รอดำเนินการ'");
$pendingRecordsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$pendingRecordsStmt->execute();
$pendingRecords = $pendingRecordsStmt->fetchAll(PDO::FETCH_ASSOC);

$wrongProofRecordsStmt = $conn->prepare("SELECT * FROM meter_records WHERE user_id = :user_id AND payment_status = 'หลักฐานผิดพลาด'");
$wrongProofRecordsStmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$wrongProofRecordsStmt->execute();
$wrongProofRecords = $wrongProofRecordsStmt->fetchAll(PDO::FETCH_ASSOC);


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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตรวจสอบการชำระเงิน</title>
    <link rel="stylesheet" href="payment_status_css/check_payment.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <style>
        .payment-form {
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }

        .paid {
            color: green;
        }

        .unpaid {
            color: #bf4800;
        }
        .pending {
    color: orange; /* เปลี่ยนสีข้อความเป็นสีส้ม */
}
        
    </style>
    <link rel="stylesheet" href="navbar/nav.css">
</head>

<body>

    <?php require_once 'navbar/adminnavbar.php'; ?>

    <div class="main">
        
        <div class="payment_unpaid">
            <div>
                <a>รายการค้างชำระ</a>
                <p>ชื่อผู้ใช้: <span id="username"><?php echo $user['firstname'] . ' ' . $user['lastname']; ?></span></p>
            </div>
            <p>เลขมิเตอร์: <span id="meterCode"><?php echo $user['meter_code']; ?></span></p>
        </div>
        
        <div class="payment-container">
        <?php if (count($pendingRecords) > 0 || count($wrongProofRecords) > 0): ?>
    <?php foreach (array_merge($pendingRecords, $wrongProofRecords) as $record): ?>
        <div class="payment-form">
            <p>ประจำเดือน : <?php echo getThaiMonth($record['month_name']); ?></p>
            <p>วันที่บันทึก : <?php echo $record['reading_date']; ?></p>
            <p>ปริมาณการใช้น้ำ (หน่วย) : <?php echo $record['usage_value']; ?></p>
            <p>฿ : <?php echo $record['price']; ?></p>
            <p>สถานะการชำระเงิน : <span class="<?php echo ($record['payment_status'] == 'ชำระเงินแล้ว') ? 'paid' : (($record['payment_status'] == 'รอดำเนินการ') ? 'pending' : 'unpaid'); ?>"><?php echo $record['payment_status']; ?></span></p>
            <button type="button" class="viewimage" data-toggle="modal" data-target="#proofModal<?php echo $record['id']; ?>">
                ดูหลักฐานการชำระเงิน
            </button>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#updateStatusModal" data-record-id="<?php echo $record['id']; ?>">ปรับสถานะการชำระเงิน</button>
        </div>
        
    <?php endforeach; ?>

<?php else: ?>
    
<?php endif; ?>




            <?php if (count($unpaidRecords) > 0): ?>
                <?php foreach ($unpaidRecords as $record): ?>
                    <div class="payment-form">
                        <p>ประจำเดือน : <?php echo getThaiMonth($record['month_name']); ?></p>
                        <p>วันที่บันทึก : <?php echo $record['reading_date']; ?></p>
                        <p>ปริมาณการใช้น้ำ (หน่วย) : <?php echo $record['usage_value']; ?></p>
                        <p>฿ : <?php echo $record['price']; ?></p>
                        <p>สถานะการชำระเงิน : <span class="<?php echo ($record['payment_status'] == 'ชำระเงินแล้ว') ? 'paid' : 'unpaid'; ?>"><?php echo $record['payment_status']; ?></span></p>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#updateStatusModal" data-record-id="<?php echo $record['id']; ?>">ปรับสถานะการชำระเงิน</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p></p>
            <?php endif; ?>
            
        </div>
        
        <?php foreach ($pendingRecords as $record): ?>
            <div class="modal" id="proofModal<?php echo $record['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="proofModalLabel<?php echo $record['id']; ?>" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="proofModalLabel<?php echo $record['id']; ?>">หลักฐานการชำระเงิน</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <?php
                              $proofsStmt = $conn->prepare("SELECT * FROM proofs WHERE record_id = :record_id");
                                $proofsStmt->bindParam(':record_id', $record['id'], PDO::PARAM_INT);
                                $proofsStmt->execute();
                                $proofs = $proofsStmt->fetchAll(PDO::FETCH_ASSOC);
                                
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

        <div class="modal" id="updateStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateStatusModalLabel">ปรับสถานะการชำระเงิน</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="updateStatusForm" action="update_payment_status.php" method="post">
                            <input type="hidden" name="record_id" value="">
                            <input type="hidden" name="Payment_status_by" value="<?php echo $admin['firstname'] . ' ' . $admin['lastname']; ?>">



                            <div class="form-group">
                                <label for="paymentStatus">สถานะการชำระเงิน</label>
                                <select class="form-control" id="paymentStatus" name="payment_status">
                                    <option value="ค้างชำระ">ค้างชำระ</option>
                                    <option value="ชำระเงินเรียบร้อย">ชำระเงินเรียบร้อย</option>
                                    <option value="หลักฐานผิดพลาด">หลักฐานผิดพลาด</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">บันทึก</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="space"></div>

        
            <div class="payment_unpaid">
                <div>
                    <a>ประวัติการชำระเงิน</a>
                </div>
            </div>
            <div class="payment-container">
            <?php if (count($paidRecords) > 0): ?>
    <?php foreach ($paidRecords as $record): ?>
        
        <div class="payment-form">
            <p>ประจำเดือน : <?php echo getThaiMonth($record['month_name']); ?></p>
            <p>วันที่บันทึก : <?php echo $record['reading_date']; ?></p>
            <p>ปริมาณการใช้น้ำ (หน่วย) : <?php echo $record['usage_value']; ?></p>
            <p>฿ : <?php echo $record['price']; ?></p>
            <p>สถานะการชำระเงิน : <span class="paid"><?php echo $record['payment_status']; ?><i class="bi bi-emoji-smile-fill"></i></span></p>
            <p>ยืนยันการชำระโดย : <?php echo $record['payment_status_by']; ?></p>

            <!-- ปุ่มเปิด modal เพื่อดูรูปภาพ -->
            <button type="button" class="viewimage" data-toggle="modal" data-target="#proofModal<?php echo $record['id']; ?>">
                ดูหลักฐานการชำระเงิน
            </button>


          
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>ไม่มีประวัติการชำระเงิน</p>
<?php endif; ?>

        
<!-- เพิ่ม modal สำหรับแสดงรูปภาพ -->
<?php foreach (array_merge($pendingRecords, $paidRecords, $wrongProofRecords) as $record): ?>
    <div class="modal" id="proofModal<?php echo $record['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="proofModalLabel<?php echo $record['id']; ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="proofModalLabel<?php echo $record['id']; ?>">หลักฐานการชำระเงิน</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php
                        $proofsStmt = $conn->prepare("SELECT * FROM proofs WHERE record_id = :record_id");
                        $proofsStmt->bindParam(':record_id', $record['id'], PDO::PARAM_INT);
                        $proofsStmt->execute();
                        $proofs = $proofsStmt->fetchAll(PDO::FETCH_ASSOC);

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
        <div class="footer"></div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script>
            $('#updateStatusModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var recordId = button.data('record-id');
                var modal = $(this);
                modal.find('input[name="record_id"]').val(recordId);
            });
        </script>
<script>
    // เพิ่ม event listener บน modal เมื่อปิด
    $('#updateStatusModal').on('hidden.bs.modal', function () {
        // รีเฟรชหน้าเว็บ
        location.reload();
    });
    
</script>


        <script>
            function showSweetAlert(icon, title, text, confirmButtonText, onCloseCallback) {
                Swal.fire({
                    icon: icon,
                    title: title,
                    text: text,
                    confirmButtonText: confirmButtonText,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        if (onCloseCallback && typeof onCloseCallback === 'function') {
                            onCloseCallback();
                        }
                    }
                });
            }

            <?php if (isset($_SESSION['success'])): ?>
                showSweetAlert(
                    'success',
                    'Success!',
                    '<?php echo $_SESSION['success']; ?>',
                    'OK',
                    function () {
                        window.location.href = 'user_payment_status.php';
                    }
                );
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                showSweetAlert(
                    'error',
                    'Error!',
                    '<?php echo $_SESSION['error']; ?>',
                    'OK',
                    function () {
                        window.location.href = 'user_payment_status.php';
                    }
                );
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
        </script>

    </div>
</body>

</html>
