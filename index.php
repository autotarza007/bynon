<?php 
session_start(); 

// เชื่อมต่อฐานข้อมูล
include('server/server.php');

// ฟังก์ชันเพื่อดึงข้อมูลจำนวนผู้ใช้
function getUserCount($conn) {
    $sql = "SELECT COUNT(*) AS user_count FROM user_tb";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);
    return $data['user_count'];
}

// ฟังก์ชันเพื่อดึงข้อมูลจำนวนเนื้อหา
function getContentCount($conn) {
    $sql = "SELECT COUNT(*) AS content_count FROM content";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);
    return $data['content_count'];
}

// ฟังก์ชันเพื่อดึงข้อมูลจำนวนหมวดหมู่
function getCatalogCount($conn) {
    $sql = "SELECT COUNT(*) AS catalog_count FROM catalog_tb";
    $result = mysqli_query($conn, $sql);
    $data = mysqli_fetch_assoc($result);
    return $data['catalog_count'];
}

// สร้างการเชื่อมต่อ
$conn = new mysqli($server, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_count = getUserCount($conn);
$content_count = getContentCount($conn);
$catalog_count = getCatalogCount($conn);

// ปิดการเชื่อมต่อ
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phantom code</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="logo.png">
    <link href="css/header1.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* สไตล์สำหรับหน้าจอโหลด */
        #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #f3f3f3;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        #loader img {
            width: 80px;
            height: 80px;
            animation: spin 1s linear infinite;
        }

        /* CSS สำหรับการ์ด */
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .card-cover {
            background-size: cover;
            background-position: center;
            height: 100%;
        }
        .card img {
            object-fit: cover;
        }
        .feature-icon {
            width: 4rem;
            height: 4rem;
            border-radius: .75rem;
        }
        .icon-square {
            width: 3rem;
            height: 3rem;
            border-radius: .75rem;
        }
        .text-shadow-1 { text-shadow: 0 .125rem .25rem rgba(0, 0, 0, .25); }
        .text-shadow-2 { text-shadow: 0 .25rem .5rem rgba(0, 0, 0, .25); }
        .text-shadow-3 { text-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .25); }
        .card-cover {
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
        }
        .feature-icon-small {
            width: 3rem;
            height: 3rem;
        }

        .size-6 {
            width: 2rem;  /* ปรับขนาดที่ต้องการ */
            height: 2rem; /* ปรับขนาดที่ต้องการ */
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @media (min-width: 992px) {
            .rounded-lg-3 { border-radius: .3rem; }
        }

        .icon-users { color: rgb(255, 153, 0) }
        .icon-content { color: rgb(0, 255, 41) }
        .icon-catalog { color: rgb(6, 210, 255) }
    </style>
</head>
<body class="background">
    
    <!-- แสดงหน้าโหลด -->
    <div id="loader">
        <img src="loading-icon.png" alt="Loading..."> <!-- คุณสามารถเปลี่ยนเป็นไอคอนของคุณเอง -->
    </div>

    <!-- แสดง header ตามสถานะเซสชัน -->
    <?php 
        if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            include('admin/headeradmin_out.php'); // ตรวจสอบเส้นทางให้ถูกต้อง
        } elseif (isset($_SESSION['success']) && $_SESSION['success'] === true) {
            include('header1.php'); // ตรวจสอบเส้นทางให้ถูกต้อง
        } else {
            include('header.php'); // ตรวจสอบเส้นทางให้ถูกต้อง
        }
    ?>

    <div class="px-4 py-5 my-5 text-center ">
        <div class="container px-2 py-5 custom-background">
            <div class="custom2-background">
            <img class="d-block mx-auto mb-4" src="logo.png" alt="" width="72" height="57">
                <h1 class="display-5 fw-bold text-body-emphasis text-white">Phantom code</h1>
            <div class="col-lg-6 mx-auto">
                <p class="lead mb-4 text-white">แพลทฟอร์มการเรียนรู้ด้าน การทำเว็บไซต์ และ สอนป้องกันระบบ</p>
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <button type="button" class="btn btn-primary btn-lg px-4 gap-3 me-2" onclick="location.href='login.php'">ดูเนื้อหา</button>
            </div>
            </div>
        </div>
        </div>
    </div>

    <div class="container px-4 py-5 custom-background">
    <h2 class="pb-2 border-bottom text-white">STATISTICS - สถิติ</h2>
    <div class="row">
        <div class="col-md-4">
            <div class="card text-center custom-background2">
                <div class="card-body">
                    <i class="fas fa-users fa-3x icon-users"></i>
                    <h5 class="card-title text-white">Users</h5>
                    <p id="user-count" class="card-text text-white"><?php echo $user_count; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center custom-background2">
                <div class="card-body">
                    <i class="fas fa-file-alt fa-3x icon-content"></i>
                    <h5 class="card-title text-white">Contents</h5>
                    <p id="content-count" class="card-text text-white"><?php echo $content_count; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center custom-background2">
                <div class="card-body">
                    <i class="fas fa-th-list fa-3x icon-catalog"></i>
                    <h5 class="card-title text-white">Catalogs</h5>
                    <p id="catalog-count" class="card-text text-white"><?php echo $catalog_count; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>


    <div class="container px-4 py-5 custom-background mt-5" id="custom-cards">
        <h2 class="pb-2 border-bottom text-white">เรียนกับเราได้อะไร ?</h2>

        <div class="row row-cols-1 row-cols-lg-3 align-items-stretch g-4 py-5">
          <div class="col custom-background2">
            <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg" style="background-image: url('slide1.png');">
              <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                <h3 class="pt-5 mt-5 mb-4 display-6 lh-1 fw-bold">Developer</h3>
              </div>
            </div>
          </div>

          <div class="col custom-background2">
            <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg mx-2" style="background-image: url('slide2.png');">
              <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                <h3 class="pt-5 mt-5 mb-4 display-6 lh-1 fw-bold">Pentester</h3>
              </div>
            </div>
          </div>

          <div class="col custom-background2">
            <div class="card card-cover h-100 overflow-hidden text-bg-dark rounded-4 shadow-lg" style="background-image: url('slide3.png');">
              <div class="d-flex flex-column h-100 p-5 pb-3 text-white text-shadow-1">
                <h3 class="pt-5 mt-5 mb-4 display-6 lh-1 fw-bold">Hacker</h3>
                <ul class="d-flex list-unstyled mt-auto">
                </ul>
              </div>
            </div>
          </div>
        </div>
    </div>

    <!-- ท้ายสุดเว็บ -->
    <?php include('footer.php'); ?>

    <?php include('js.php'); ?>

    <script>
        // เมื่อหน้าเว็บโหลดเสร็จแล้ว ให้ซ่อนหน้าโหลด
        window.addEventListener("load", function(){
            var loader = document.getElementById("loader");
            loader.style.display = "none";
        });
    </script>
    
</body>
</html>
