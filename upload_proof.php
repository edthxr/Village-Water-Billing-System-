<?php
session_start();

// เชื่อมต่อฐานข้อมูล
require_once 'config/db.php';

// ตรวจสอบว่ามีการส่งข้อมูลมาจากฟอร์ม
if(isset($_FILES['proof_image']) && isset($_POST['record_id'])) {
    $record_id = $_POST['record_id'];
    
    // คิวรี่ข้อมูลรายการที่ต้องชำระเงินจากฐานข้อมูล
    $stmt = $conn->prepare("SELECT * FROM meter_records WHERE id = :record_id");
    $stmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    // ตรวจสอบว่ามีไฟล์ที่อัพโหลดหรือไม่
    if($_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['proof_image']['tmp_name'];
        $fileName = $_FILES['proof_image']['name'];
        $fileSize = $_FILES['proof_image']['size'];
        $fileType = $_FILES['proof_image']['type'];
        
        // ตรวจสอบประเภทของไฟล์
        $allowedExtensions = array("jpg", "jpeg", "png", "gif");
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if(in_array($fileExtension, $allowedExtensions)) {
            // กำหนดตัวแปรสำหรับเก็บชื่อไฟล์ที่จะบันทึกลงในระบบ
            $newFileName = uniqid() . "." . $fileExtension;
            
            // กำหนด path สำหรับบันทึกไฟล์
            $uploadDir = 'proofs/';
            $destPath = $uploadDir . $newFileName;
            
            // ตรวจสอบว่ามีรูปเก่าในฐานข้อมูลหรือไม่
            $stmt = $conn->prepare("SELECT * FROM proofs WHERE record_id = :record_id");
            $stmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
            $stmt->execute();
            $oldProof = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // อัปเดตสถานะในตาราง meter_records เป็น "รอดำเนินการ"
$updateStmt = $conn->prepare("UPDATE meter_records SET payment_status = 'รอดำเนินการ' WHERE id = :record_id");
$updateStmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
$updateStmt->execute();
            // ถ้ามีรูปเก่าในฐานข้อมูล ให้ลบรูปเก่าออกจากโฟลเดอร์และฐานข้อมูล
            if($oldProof) {
                $oldFilePath = $oldProof['file_path'];
                if(file_exists($oldFilePath)) {
                    unlink($oldFilePath); // ลบไฟล์รูปเก่า
                }
                // ลบข้อมูลรูปเก่าออกจากฐานข้อมูล
                $stmt = $conn->prepare("DELETE FROM proofs WHERE record_id = :record_id");
                $stmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            // ย้ายไฟล์ใหม่ไปยังโฟลเดอร์ที่กำหนด
            if(move_uploaded_file($fileTmpPath, $destPath)) {
                // บันทึกข้อมูลลงในฐานข้อมูล
                $stmt = $conn->prepare("INSERT INTO proofs (record_id, file_path) VALUES (:record_id, :file_path)");
                $stmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
                $stmt->bindParam(':file_path', $destPath, PDO::PARAM_STR);
                $stmt->execute();

                $_SESSION['upload_success_message'] = "อัพโหลดสำเร็จ";
                header("Location: payment_page.php?record_id=$record_id");
                exit();
            } else {
                $_SESSION['upload_error_message'] = "เกิดข้อผิดพลาดในการอัพโหลดไฟล์";
            }
        } else {
            $_SESSION['upload_error_message'] = "ประเภทของไฟล์ไม่ถูกต้อง";
        }
    } else {
        $_SESSION['upload_error_message'] = "เกิดข้อผิดพลาดในการอัพโหลดไฟล์: " . $_FILES['proof_image']['error'];
    }
}

// หากไม่มีการส่งข้อมูลหรือเกิดข้อผิดพลาด ให้ redirect กลับไปยังหน้า payment_page.php
header("Location: payment_page.php?record_id=$record_id");
exit();
?>
