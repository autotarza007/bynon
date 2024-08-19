<?php
include('server/server.php');

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (isset($_SESSION['user_id'])) {
    $user_ids = $_SESSION['user_id'];

    // ดึงข้อมูลของผู้ใช้จากฐานข้อมูล
    $querys = "SELECT user_profile FROM user_tb WHERE user_id = ?";
    $stmts = $conn->prepare($querys);
    $stmts->bind_param("i", $user_ids);
    $stmts->execute();
    $results = $stmts->get_result();
    $users = $results->fetch_assoc();

    // เก็บ URL ของรูปโปรไฟล์ไว้ในตัวแปร
    $profile_images = !empty($users['user_profile']) ? $users['user_profile'] : 'default-profile-icon.png'; // ใช้รูปไอคอน default ถ้าไม่มีรูปในฐานข้อมูล
} else {
    // ถ้าไม่เข้าสู่ระบบให้ไปหน้า login
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My site</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet"> <!-- ใส่ CSS เพิ่มถ้าต้องการ -->

    <style>

.nav-link {
    transition: transform 0.3s ease-in-out; /* เวลาและรูปแบบของการเปลี่ยนแปลง */
}

.nav-link:hover {
    transform: scale(1.1); /* ขยายขนาด 10% เมื่อเมาส์ชี้ */
}

.logo {
        margin-right: 50px; /* <span style="color:red;">ดันโลโก้ไปทางซ้าย</span> */
    }

    .dropdown {
        margin-left: 50px; /* <span style="color:red;">ดันโปรไฟล์ไปทางขวา</span> */
    }
</style>
</head>
<body>
    <header>
        <div class="custom-background pd123">
            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3">
                <!-- Logo section -->
                <div class="logo">
                    <a href="/" class="d-flex align-items-center text-decoration-none">
                        <img src="logo.png" alt="Logo" width="40" height="32" class="me-2"> <!-- ใส่โลโก้ของคุณ -->
                    </a>
                </div>

                <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                    <li><a href="index.php" class="nav-link px-2 text-white">หน้าแรก</a></li>
                    <li><a href="catalog.php" class="nav-link px-2 text-white">เนื้อหา/หลักสูตร</a></li>
                    <li><a href="oneone.php" class="nav-link px-2 text-white">เรียนตัวต่อตัว</a></li>
                    <li><a href="ranking.php" class="nav-link px-2 text-white">อันดับ</a></li>
                    <li><a href="https://www.facebook.com/profile.php?id=100029124764561&locale=th_TH" class="nav-link px-2 text-white">ติดต่อ</a></li>
                    <li><a href="#" class="nav-link px-2 text-white">Discord</a></li>
                </ul>

                <!-- Dropdown -->
                <div class="dropdown text-end ms-auto">
                    <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <img src="<?php echo htmlspecialchars($profile_images); ?>" alt="user profile" width="32" height="32" class="rounded-circle">
                    </a>
                    <ul class="dropdown-menu text-small" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>
</body>
</html>
