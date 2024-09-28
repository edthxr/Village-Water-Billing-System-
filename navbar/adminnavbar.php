<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../navbar/nav.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <!-- sweet alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!--font google-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
        integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Bootstrap JS (make sure it's after jQuery) -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>

    <!-- DataTables JS (make sure it's after jQuery) -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
</head>

<body>


    <nav>
        <div class="containernav">
            <ul class="sidebar">
                <li onclick=closeSidebar()>
                    <a href="#"><i class="bi bi-x-lg"></i></a>
                </li>
                <li>
                    <a href="admin.php">หน้าหลัก</a>
                </li>
                <li>
                    <a href="user_list.php">รายชื่อผู้ใช้น้ำ</a>

                </li>
                <li>
                    <a href="adduser.php">เพิ่มรายชื่อผู้ใช้</a>
                </li>
                <li>
                    <a href="waterusage.php">บันทึกมิเตอร์</a>
                </li>
                <li>
                    <a href="user_payment_status.php">เช็คยอดค้างชำระ</a>
                </li>
                <li>
                    <a href="check_order.php">ตรวจสอบการชำระ</a>
                </li>
                <li>
                    <a href="checktotal.php">ยอดรวม</a>
                </li>
                <li>
                    <a href="QR_admin.php">อัปโหลด QRCODE</a>
                </li>
                <li>
                    <a href="problem.php">ตรวจสอบการแจ้งปัญหา</a>
                </li>
                <li>
                    <a href="logout.php">Logout</a>
                </li>


            </ul>

            <ul>
                <li>
                    <a href="admin.php">ADMIN</a>
                </li>
                <li class="hideOnMoblie">
                    <a href="admin.php">หน้าหลัก</a>
                </li>
                <li class="hideOnMoblie">
                    <a href="user_list.php">รายชื่อผู้ใช้น้ำ </a>
                    <a href="adduser.php" class="else specialLink">เพิ่มรายชื่อผู้ใช้</a>
                </li>
                <li class="hideOnMoblie">
                    <a href="waterusage.php">บันทึกมิเตอร์</a>
                </li>
                <li class="hideOnMoblie">
                    <a href="user_payment_status.php">เช็คยอดค้างชำระ </i></a>
                    <div class="dropdown">
                        <a href="check_order.php" class="else specialLink">ตรวจสอบการชำระ</a>
                        <a href="checktotal.php" class="else specialLink">ยอดรวม</a>
                        <a href="QR_admin.php" class="else specialLink">อัปโหลด QRCODE</a>

                    </div>
                </li>
                <li class="hideOnMoblie">
                    <a href="problem.php">ตรวจสอบการแจ้งปัญหา</a>
                </li>
                <li class="hideOnMoblie">
                    <a href="logout.php">Logout</a>
                </li>
                <li class="menu-button" onclick=showSidebar()>
                    <a href="#"><i class="bi bi-list"></i></a>
                </li>

            </ul>

        </div>
    </nav>

    <!-- Template Main JS File -->
    <script>

        function showSidebar() {
            const sidebar = document.querySelector('.sidebar')
            sidebar.style.display = 'flex'
        }
        function closeSidebar() {
            const sidebar = document.querySelector('.sidebar')
            sidebar.style.display = 'none'
        }
        
    </script>
    <script>

        // เลือกลิงก์ "ยอดรวม" และกำหนดขนาดตัวอักษร
        var checktotalLink = document.querySelector('a[href="checktotal.php"]');
        checktotalLink.style.fontSize = '16px';

        var checkOrderLink = document.querySelector('a[href="check_order.php"]');
        checkOrderLink.style.fontSize = '16px';
        checkOrderLink.style.fontWeight = 'normal'; // เพิ่มการกำหนด fontWeight เป็น 'normal' เพื่อให้เป็นฟอนต์ธรรมดา
        checkOrderLink.style.textShadow = 'none';

        var menuButton = document.querySelector('.menu-button');

    menuButton.style.fontSize = '30px';
   
   
    var closeButton = document.querySelector('.containernav a[href="#"] i.bi-x-lg');
    // กำหนดขนาดของไอคอนในลิงก์เป็น 30px
    closeButton.style.fontSize = '30px';
    

    </script>

</body>

</html>