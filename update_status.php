<?php
// update_status.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ดึงข้อมูลจาก $_POST
    $requestId = $_POST['request_id'];
    $newStatus = $_POST['new_status'];

    // เชื่อมต่อกับฐานข้อมูล
    require_once 'config/db.php';

    // อัปเดตข้อมูลในฐานข้อมูล
    $updateStatusQuery = $conn->prepare("UPDATE problem_reports SET status = :new_status WHERE request_id = :request_id");
    $updateStatusQuery->bindParam(':new_status', $newStatus);
    $updateStatusQuery->bindParam(':request_id', $requestId);

    // ทำการอัปเดต
    if ($updateStatusQuery->execute()) {
        // ถ้าอัปเดตสำเร็จ
        echo json_encode(['success' => true, 'message' => 'Update successful']);
    } else {
        // ถ้ามีข้อผิดพลาด
        echo json_encode(['success' => false, 'message' => 'Update failed']);
    }
}
?>