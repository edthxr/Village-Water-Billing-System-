<?php
session_start();

require_once 'config/db.php';

if (!isset($_SESSION['admin_login'])) {
    $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
    header('location: signin.php');
}

// SQL query เพื่อเลือกไฟล์ล่าสุดที่ถูกอัปโหลด
$sql = "SELECT file_name, file_path FROM qrcodes ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->query($sql);
$latestImage = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบว่ามีรูปภาพที่ถูกอัปโหลดล่าสุดหรือไม่
if ($latestImage) {
    $latestFileName = $latestImage['file_name'];
    $latestFilePath = $latestImage['file_path'];
} else {
    // หากไม่พบรูปภาพ
    $latestFileName = "";
    $latestFilePath = "";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อัพโหลด QRCODE</title>
    <link rel="stylesheet" href="QR_css/QR.css">
    <link rel="stylesheet" href="navbar/nav.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    require_once 'navbar/adminnavbar.php';

    require_once 'config/db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if the "qrcode" key is set in the $_FILES array
        if (isset($_FILES['qrcode'])) {
            $uploadDir = 'uploads/';
            $file = $uploadDir . basename($_FILES['qrcode']['name']);
            $fileType = pathinfo($file, PATHINFO_EXTENSION);
    
            $check = getimagesize($_FILES['qrcode']['tmp_name']);
    
            // Check if the file size is greater than 30 MB
            if ($_FILES['qrcode']['size'] > 30000000) {
                echo '<script>';
                echo 'Swal.fire({
                        title: "ไฟล์มีขนาดใหญ่เกินกว่า 30 MB",
                        text: "กรุณาเลือกไฟล์ที่มีขนาดน้อยกว่า 30 MB",
                        icon: "error",
                        confirmButtonText: "OK"
                    }).then(() => {
                        window.location.href = "QR_admin.php"; 
                    });';
                echo '</script>';
                exit(); // Stop script execution
            }
    
            if ($check !== false) {
                move_uploaded_file($_FILES['qrcode']['tmp_name'], $file);
                $fileName = $_FILES['qrcode']['name'];
                $filePath = $file;
    
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
                // SQL query เพื่อลบรูปภาพเก่าออกจากฐานข้อมูล
                $sqlDeleteOldImage = "DELETE FROM qrcodes WHERE file_name = :latestFileName";
                $stmtDeleteOldImage = $conn->prepare($sqlDeleteOldImage);
                $stmtDeleteOldImage->bindParam(':latestFileName', $latestFileName);
                $stmtDeleteOldImage->execute();
    
                // เพิ่มรูปภาพใหม่เข้าไปในฐานข้อมูล
                $sqlInsertNewImage = "INSERT INTO qrcodes (file_name, file_path, created_at) VALUES (:fileName, :filePath, CURRENT_TIMESTAMP)";
                $stmtInsertNewImage = $conn->prepare($sqlInsertNewImage);
                $stmtInsertNewImage->bindParam(':fileName', $fileName);
                $stmtInsertNewImage->bindParam(':filePath', $filePath);
    
                $conn->beginTransaction();
    
                try {
                    // ลบรูปภาพเก่า
                    $stmtDeleteOldImage->execute();
    
                    // เพิ่มรูปภาพใหม่
                    $stmtInsertNewImage->execute();
    
                    $conn->commit(); // Commit transaction if everything goes fine
    
                    echo '<script>';
                    echo 'Swal.fire({
                            title: "อัปโหลดสำเร็จ",
                            icon: "success",
                            confirmButtonText: "OK"
                        }).then(() => {
                            window.location.href = "QR_admin.php"; // Redirect to your admin page
                        });';
                    echo '</script>';
                } catch (PDOException $e) {
                    $conn->rollback(); // Rollback transaction if any error occurs
    
                    echo '<script>';
                    echo 'Swal.fire({
                            title: "เกิดข้อผิดพลาด",
                            text: "' . $e->getMessage() . '",
                            icon: "error",
                            confirmButtonText: "OK"
                        });';
                    echo '</script>';
                }
            } else {
                echo '<script>';
                echo 'Swal.fire({
                        title: "ไม่ได้เลือกรูปภาพ",
                        text: "โปรดเลือกรูปภาพ QR code",
                        icon: "warning",
                        confirmButtonText: "OK"
                    }).then(() => {
                        window.location.href = "QR_admin.php"; // Redirect to your starting page
                    });';
                echo '</script>';
            }
        } else {
            echo '<script>';
            echo 'Swal.fire({
                    title: "ไม่ได้เลือกรูปภาพ",
                    text: "โปรดเลือกรูปภาพ QR code",
                    icon: "warning",
                    confirmButtonText: "OK"
                }).then(() => {
                    window.location.href = "QR_admin.php"; // Redirect to your starting page
                });';
            echo '</script>';
        }
    }
    

    ?>


<form id="uploadForm" action="" method="post" enctype="multipart/form-data">
        <div class="upload-form">
            <h2>อัปโหลด QR Code</h2>
            <!-- File input element, hidden -->
            <input type="file" id="qrcode" name="qrcode" accept="image/*" style="display: none;" onchange="uploadConfirmation()" required>
            <!-- Button to trigger file input click event -->
            <button type="button" onclick="chooseFile()">เลือกรูปภาพ</button>
        </div>
    </form>

    <div class="latest-image">
    <?php if ($latestFilePath !== ""): ?>
        <p>รูปภาพที่ถูกแสดงในขณะนี้</p>
        <img src="<?php echo $latestFilePath; ?>" alt="<?php echo $latestFileName; ?>">
        <?php else: ?>
        <p>ยังไม่มีรูปภาพที่ถูกอัปโหลด</p>
    <?php endif; ?>
</div>

=
    <script>
        // Function to trigger file input click event
        function chooseFile() {
            document.getElementById('qrcode').click();
        }

        // Function to trigger SweetAlert when file is selected
        function uploadConfirmation() {
            const fileInput = document.getElementById('qrcode');
            if (fileInput.files.length > 0) {
                Swal.fire({
                    title: 'ต้องการอัปโหลดรูปภาพนี้ใช่หรือไม่?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'ใช่',
                    cancelButtonText: 'ไม่',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit form if user confirms
                        document.getElementById('uploadForm').submit();
                    }
                });
            } else {
                Swal.fire({
                    title: 'ไม่ได้เลือกรูปภาพ',
                    text: 'โปรดเลือกรูปภาพ QR code',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        }
    </script>

</body>

</html>