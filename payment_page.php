<?php
session_start();
// เชื่อมต่อฐานข้อมูล
require_once 'config/db.php';

// ตรวจสอบว่ามีการส่ง record_id มาจากหน้า payment.php
if (isset($_GET['record_id'])) {
    $record_id = $_GET['record_id'];

    // คิวรี่ข้อมูลรายการที่ต้องชำระเงินจากฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM meter_records WHERE id = :record_id");
    $stmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    // ตรวจสอบว่ามีข้อมูลรายการที่ต้องชำระเงินหรือไม่
    if ($record) {
        // แสดงข้อมูลรายการที่ต้องชำระเงิน
        ?>
        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>หน้าชำระเงิน</title>
            <!-- ลิงค์ sweet alert  -->
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <!-- ลิงค์ CSS  -->
            <link rel="stylesheet" href="payment_user_css/payment_page.css">
            <link rel="stylesheet" href="navbaruser/navuser.css">
        </head>

        <body>
            <?php
            require_once 'navbaruser/usernavbar.php';
            ?>

<div class="head-font">
<?php if ($record['payment_status'] == 'หลักฐานผิดพลาด'): ?>
    <h1>หลักฐานของผู้ใช้ผิดพลาด โปรดตรวจสอบให้แน่ใจ และส่งหลักฐานใหม่อีกครั้งด้วยค่ะ</h1>
<?php endif; ?>

</div>
            
            <div class="payment-container">
                <div class="payment-form">
                    
                    <h2>รายละเอียดการชำระเงิน</h2>
                    <p>ประจำเดือน:
                        <?php echo getThaiMonth($record['month_name']); ?>
                    </p>
                    <p>การอ่านครั้งก่อน:
                        <?php echo $record['previous_reading']; ?>
                    </p>
                    <p>การอ่านครั้งล่าสุด:
                        <?php echo $record['current_reading']; ?>
                    </p>
                    <p>ปริมาณการใช้น้ำ (หน่วย):
                        <?php echo $record['usage_value']; ?>
                    </p>
                    <p>ราคาที่ต้องชำระ (บาท):
                        <?php echo $record['price']; ?>
                    </p>
                    <p>วันที่บันทึก:
                        <?php echo $record['reading_date']; ?>
                    </p>
                    <p>บันทึกโดย:
                        <?php echo $record['submit_by']; ?>
                    </p>
                    <p>สถานะการชำระเงิน: <span style="color: 
    <?php
            if ($record['payment_status'] == 'ชำระเงินแล้ว') {
                echo 'green'; // สีเขียวสำหรับ "ชำระเงินแล้ว"
            } elseif ($record['payment_status'] == 'รอดำเนินการ') {
                echo 'orange'; // สีส้มสำหรับ "รอดำเนินการ"
            } else {
                echo 'red'; // สีแดงสำหรับสถานะอื่น ๆ
            }
            ?>">
                            <?php echo $record['payment_status']; ?>
                        </span></p>
                </div>


                <div class="payment-form">
                    <div class="qr-code">
                        <h2>สแกน QR Code </h2>
                        <?php
                        // ดึงข้อมูล QR code จากฐานข้อมูล
                        $stmt = $conn->prepare("SELECT * FROM qrcodes ORDER BY id DESC LIMIT 1");
                        $stmt->execute();
                        $qrCode = $stmt->fetch(PDO::FETCH_ASSOC);

                        // ตรวจสอบว่ามีข้อมูล QR code หรือไม่
                        if ($qrCode) {
                            // แสดงรูปภาพ QR code
                            echo '<img src="' . $qrCode['file_path'] . '" alt="QR Code">';
                        } else {
                            echo '<p>ไม่พบรูปภาพ QR Code</p>';
                        }
                        ?>
                    </div>
                </div>

                <div class="payment-form">
                <div class="Proof-payment">
    <h2>แนบหลักฐานการชำระเงิน</h2>
    <?php
    // ตรวจสอบว่าสถานะไม่ใช่ "ค้างชำระ" และมีการส่งหลักฐานการชำระเงินเข้ามาแล้วหรือไม่
    if ($record['payment_status'] != 'ค้างชำระ') {
        // คิวรี่ข้อมูลรูปภาพหลักฐานการชำระเงินจากฐานข้อมูล
        $stmt = $conn->prepare("SELECT * FROM proofs WHERE record_id = :record_id");
        $stmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
        $stmt->execute();
        $proofs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ตรวจสอบว่ามีรูปภาพหลักฐานการชำระเงินหรือไม่
        if ($proofs) {
            // แสดงรูปภาพ
            foreach ($proofs as $proof) {
                echo '<img src="' . $proof['file_path'] . '" alt="">';
            }
        } else {
            echo '<p></p>';
        }
    }
    ?>
    <form action="upload_proof.php" method="post" enctype="multipart/form-data" id="proof-form">
        <!-- ใช้ label เพื่อแสดงข้อความ "เลือกรูปภาพ" แทนที่จะใช้ value attribute ใน input -->
        <label for="proof_image" class="file-label">เลือกรูปภาพ</label>
        <input type="file" name="proof_image" id="proof_image" accept="image/*" required style="display: none;">
        <!-- แสดงรูปภาพที่ผู้ใช้เลือก -->
        <img id="image-preview">
        <input type="hidden" name="record_id" value="<?php echo $record_id; ?>">
    </form>

    <script>
        document.getElementById("proof_image").addEventListener("change", function () {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById("image-preview").setAttribute("src", e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        });
    </script>
    <script>
        document.getElementById("proof_image").addEventListener("change", function () {
            // เมื่อมีการเลือกรูปภาพ
            Swal.fire({
                title: 'ยืนยันการอัปโหลดรูปภาพ?',
                text: "คุณต้องการอัปโหลดรูปภาพนี้ใช่หรือไม่?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่, อัปโหลดเลย!',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    // ถ้าผู้ใช้กดยืนยัน
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        document.getElementById("image-preview").setAttribute("src", e.target.result);
                    }
                    reader.readAsDataURL(this.files[0]);

                    // ส่งฟอร์มเมื่อมีการยืนยัน
                    document.getElementById("proof-form").submit();
                }
            });
        });
    </script>
</div>



                </div>


            </div>

            <div class="footer"></div>


            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <!-- Script สำหรับแสดง SweetAlert -->
            <script>
                // ตรวจสอบว่ามี session upload_success_message หรือไม่
                <?php if (isset($_SESSION['upload_success_message'])): ?>
                    Swal.fire({
                        icon: 'success',
                        text: 'ระบบจะดำเนินการแก้ไขสถานะให้ภายใน 7 วัน',
                        title: '<?= $_SESSION['upload_success_message'] ?>',
                    });
                    <?php unset($_SESSION['upload_success_message']); ?>
                <?php endif; ?>

                // ตรวจสอบว่ามี session upload_error_message หรือไม่
                <?php if (isset($_SESSION['upload_error_message'])): ?>
                    Swal.fire({
                        icon: 'error',
                        title: '<?= $_SESSION['upload_error_message'] ?>',
                    });
                    <?php unset($_SESSION['upload_error_message']); ?>
                <?php endif; ?>
            </script>
            <?php
    } else {
        echo "ไม่พบข้อมูลรายการชำระเงิน";
    }
} else {
    echo "ไม่พบรหัสรายการ";
}

// Function เพื่อแปลงเดือนจากภาษาอังกฤษเป็นภาษาไทย
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

</body>

</html>