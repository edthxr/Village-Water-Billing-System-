    <?php
    session_start();

    // Include necessary files and check if the user is logged in
    require_once 'config/db.php';

    if (!isset($_SESSION['admin_login'])) {
        $_SESSION['error'] = 'กรุณาเข้าสู่ระบบก่อน';
        header('location: signin.php');
    }

    if (isset($_SESSION['error'])) {
        echo '<p class="error">' . $_SESSION['error'] . '</p>';
        unset($_SESSION['error']);
    }

    // Check if error_message is set in the URL
    if (isset($_GET['error_message'])) {
        echo '<p class="error">' . urldecode($_GET['error_message']) . '</p>';
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

    // ดึงข้อมูลผู้ใช้ที่ชำระเงินเรียบร้อยแล้ว
    $paidRecordsStmt = $conn->prepare("SELECT *, YEAR(reading_date) AS year FROM meter_records WHERE payment_status = 'ชำระเงินเรียบร้อย'");
    $paidRecordsStmt->execute();
    $paidRecords = $paidRecordsStmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูลผู้ใช้ที่ยังค้างชำระ
    $unpaidRecordsStmt = $conn->prepare("SELECT *, YEAR(reading_date) AS year FROM meter_records WHERE payment_status = 'ค้างชำระ'");
    $unpaidRecordsStmt->execute();
    $unpaidRecords = $unpaidRecordsStmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงข้อมูลผู้ใช้ที่รอดำเนินการ
    $pendingRecordsStmt = $conn->prepare("SELECT *, YEAR(reading_date) AS year FROM meter_records WHERE payment_status = 'รอดำเนินการ'");
    $pendingRecordsStmt->execute();
    $pendingRecords = $pendingRecordsStmt->fetchAll(PDO::FETCH_ASSOC);


    ?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ตรวจสอบผู้ใช้ค้างชำระ</title>

        <!-- ลิงค์ CSS  -->
        <link rel="stylesheet" href="order_css/order.css">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
        <!-- sweet alert -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <!-- Bootstrap JS (make sure it's after jQuery) -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <!-- DataTables JS (make sure it's after jQuery) -->
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="navbar/nav.css">

            <!-- โค้ด CSS -->
            <style>
            .paid {
                color: white;
                background-color: #14CC48;
                border-radius: 5px;
                padding: 0 10px;
            }
            
            .unpaid {
                color: white; 
                background-color: #F44336;
                border-radius: 5px;
                padding: 0 10px;
            }
            .pending {
                color: white; 
                background-color: #FFA500;
                border-radius: 5px;
                padding: 0 10px;
            }
        </style>
    </head>

    <body>
        <!-- Navigation Bar -->
        <?php
        require_once 'navbar/adminnavbar.php';
        ?>

        <!-- Table to Display User Payment Status -->
        <div class="welcome">
        <h2>ตรวจสอบการชำระเงิน</h2>
        </div>


<div class="waterusage">
    <h2>รอดำเนินการ</h2>

    <table id="pendingTable" class="table-light">
        <thead>
            <tr>
                <th>รหัสมิเตอร์</th>
                <th>ชื่อ-นามสกุล</th>
                <th>บ้านเลขที่</th>
                <th>เดือน</th>
                <th>ปี</th>
                <th>สถานะ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pendingRecords as $record) : ?>
                <tr>
                    <td class="data-label" data-label="รหัสมิเตอร์"><?php echo $record['meter_code']; ?></td>
                    <td class="data-label" data-label="ชื่อ-นามสกุล"><?php echo $record['user_name']; ?></td>
                    <td class="data-label" data-label="บ้านเลขที่"><?php echo $record['house_number']; ?></td>
                    <td class="data-label" data-label="เดือน"><?php echo thai_month(date('n', strtotime($record['reading_date']))); ?></td>
                    <td class="data-label" data-label="ปี"><?php echo $record['year']; ?></td>
                    <td class="data-label" data-label="สถานะ"><span class="pending"><?php echo $record['payment_status']; ?></span></td>
                    <td class="data-label" data-label="จัดการ">
                    <a href="check_payment.php?id=<?php echo $record['user_id']; ?>" class="manage-link">
    <i class="bi bi-chevron-double-right icon-arrow"></i>
</a>

</td>

                </tr>
            <?php endforeach; ?>
            
            
        </tbody>
    </table>

</div>
    
        <div class="waterusage">
        
            <div class="headother" id="header">

            <!-- ตารางแสดงผู้ใช้ที่ชำระเงินเรียบร้อยแล้ว -->
            <div class="check">
            <h2 id="yearselect">เดือน:
                <select id="monthDropdown" class="form-select">
                    <option value="ทั้งหมด">ทั้งหมด</option>
                    <?php
                    // Loop through months
                    for ($i = 1; $i <= 12; $i++) {
                        echo '<option value="' . thai_month($i) . '">' . thai_month($i) . '</option>';
                    }
                    ?>
                </select>
            </h2>
            </div>
            <div class="check">
            <h2 id="yearselect">ปี:
        <select id="yearDropdown" class="form-select">
            <option value="ทั้งหมด">ทั้งหมด</option>
            <?php
            // Get current year
            $currentYear = date('Y');
            
            // Loop through years, starting from 2023 and going up to the current year
            for ($i = 2023; $i <= $currentYear; $i++) {
                echo '<option value="' . $i . '">' . $i . '</option>';
            }
            ?>
        </select>

    </h2>
    </div>
    </div>

    
    <script>
    const headother = document.querySelector('.headother');
    let isHidden = false; // ตรวจสอบสถานะของการซ่อน

    function hideHeadother() {
        headother.classList.add('hidden');
        isHidden = true;
    }

    function showHeadother() {
        headother.classList.remove('hidden');
        isHidden = false;
    }

    function handleScroll() {
        if (window.scrollY > 0 && !isHidden) {
            hideHeadother();
        } else if (window.scrollY === 0 && isHidden) {
            requestAnimationFrame(showHeadother);
        }
    }

    window.addEventListener('scroll', handleScroll);


    </script>

            <h2>ชำระเงินเรียบร้อย</h2>
            <table id="paidTable" class="table-light">
                <thead>
                    <tr>
                        <th>รหัสมิเตอร์</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>บ้านเลขที่</th>
                        <th>เดือน</th>
                        <th>ปี</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paidRecords as $record) : ?>
                        <tr>
                        <td class="data-label" data-label="รหัสมิเตอร์"><?php echo $record['meter_code']; ?></td>
                        <td class="data-label" data-label="ชื่อ-นามสกุล"><?php echo $record['user_name']; ?></td>
                        <td class="data-label" data-label="บ้านเลขที่"><?php echo $record['house_number']; ?></td>
                        <td class="data-label" data-label="เดือน"><?php echo thai_month(date('n', strtotime($record['reading_date']))); ?></td>
                        <td class="data-label" data-label="ปี"><?php echo $record['year']; ?></td>
                        <td class="data-label" data-label="สถานะ"><span class="paid"><?php echo $record['payment_status']; ?></span></td>
                        <td class="data-label" data-label="จัดการ">
                        <a href="check_payment.php?id=<?php echo $record['user_id']; ?>" class="manage-link">
                            <i class="bi bi-chevron-double-right icon-arrow"></i>
                        </a>
                    </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>

<

            <!-- ตารางแสดงผู้ใช้ที่ยังค้างชำระ -->
            <div class="waterusage">
                <h2>ค้างชำระ</h2>

                <table id="unpaidTable" class="table-light">
                    <thead>
                        <tr>
                            <th>รหัสมิเตอร์</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>บ้านเลขที่</th>
                            <th>เดือน</th>
                            <th>ปี</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($unpaidRecords as $record) : ?>
                            <tr>
                            <td class="data-label" data-label="รหัสมิเตอร์"><?php echo $record['meter_code']; ?></td>
                            <td class="data-label" data-label="ชื่อ-นามสกุล"><?php echo $record['user_name']; ?></td>
                            <td class="data-label" data-label="บ้านเลขที่"><?php echo $record['house_number']; ?></td>
                            <td class="data-label" data-label="เดือน"><?php echo thai_month(date('n', strtotime($record['reading_date']))); ?></td>
                            <td class="data-label" data-label="ปี"><?php echo $record['year']; ?></td>
                            <td class="data-label" data-label="สถานะ"><span class="unpaid"><?php echo $record['payment_status']; ?></span></td>
                            <td class="data-label" data-label="จัดการ">
                        <a href="check_payment.php?id=<?php echo $record['user_id']; ?>" class="manage-link">
                            <i class="bi bi-chevron-double-right icon-arrow"></i>
                        </a>
                    </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <a href="admin.php" class="back-button">ย้อนกลับ</a>
            </div>

            <!-- Footer -->
            <div class="footer"></div>

     
            <!-- Script -->
            <script>

      $(document).ready(function() {
    $('#paidTable, #unpaidTable, #pendingTable').DataTable({
        "language": {
            "searchPlaceholder": "ค้นหา", 
            "ordering": false,
            "sProcessing": "กำลังดำเนินการ...",
            "sLengthMenu": "แสดง _MENU_ รายการ",
            "sZeroRecords": "ไม่พบรายการที่ตรงกับคำค้น",
            "sInfo": "กำลังแสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
            "sInfoEmpty": "กำลังแสดง 0 ถึง 0 จากทั้งหมด 0 รายการ",
            "sInfoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
            "sInfoPostFix": "",
            "sSearch": '<i class="bi bi-search"></i>',
            "sUrl": "",
            "oPaginate": {
                "sFirst": "เริ่มต้น",
                "sPrevious": "ก่อนหน้า",
                "sNext": "ถัดไป",
                "sLast": "สุดท้าย"
            },
            
        }
        
    });
// Function to filter tables based on selected month and year
function filterTables(selectedMonth, selectedYear) {
    filterTable('#paidTable', selectedMonth, selectedYear);
    filterTable('#unpaidTable', selectedMonth, selectedYear);
    filterTable('#pendingTable', selectedMonth, selectedYear);
}

    // When dropdown value changes
    $('#monthDropdown, #yearDropdown').change(function() {
    var selectedMonth = $('#monthDropdown').val(); // Get selected month
    var selectedYear = $('#yearDropdown').val(); // Get selected year

    // If "ทั้งหมด" is selected for both month and year, show all rows
    if (selectedMonth === 'ทั้งหมด' && selectedYear === 'ทั้งหมด') {
        $('#paidTable tbody tr, #unpaidTable tbody tr, #pendingTable tbody tr').show();
    } else {
        filterTables(selectedMonth, selectedYear); // Call filter function
    }
});

});

// Function to filter table based on selected month and year
function filterTable(table, month, year) {
    var foundData = false; // ตัวแปรเพื่อตรวจสอบว่าพบข้อมูลที่ตรงกับเงื่อนไขหรือไม่

    $(table).find('tbody tr').each(function() {
        var rowMonth = $(this).find('td:eq(3)').text();
        var rowYear = $(this).find('td:eq(4)').text();

        if ((month === 'ทั้งหมด' || rowMonth === month) && (year === 'ทั้งหมด' || rowYear === year)) {
            $(this).show();
            foundData = true; // เมื่อพบข้อมูลที่ตรงกับเงื่อนไข กำหนดค่า foundData เป็น true
        } else {
            $(this).hide();
        }
    });

    // ถ้าไม่พบข้อมูลที่ตรงกับเงื่อนไข
    if (!foundData) {
        $(table).find('tbody').append('<tr><td colspan="6" style="text-align: center;">ไม่พบข้อมูล</td></tr>');
    }
}



    </script>


    </body>

    </html>
