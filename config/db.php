<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
   
    try {
        $conn = new PDO("mysql:host=$servername;dbname=registration_system", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo "Connected successfully";
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }


?>
