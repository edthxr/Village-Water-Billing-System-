<?php
require_once 'config/db.php';
session_start();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_problem'])) {
        // Form submission logic here
        // ...

        // Show success message
        $_SESSION['success'] = 'ส่งแจ้งปัญหาเรียบร้อยแล้ว';
        header("location: submit_problem.php");
        exit();
    } elseif (isset($_POST['request_id'])) {
        // Record deletion logic here
        $request_id = $_POST['request_id'];

        try {
            $delete_query = $conn->prepare("DELETE FROM problem_reports WHERE request_id = :request_id");
            $delete_query->bindParam(":request_id", $request_id);
            $delete_query->execute();

            echo "ลบประวัติเรียบร้อยแล้ว";
            exit();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            exit();
        }
    } else {
        echo "Invalid request.";
        exit();
    }
}

?>