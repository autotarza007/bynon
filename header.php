<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My site</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet"> <!-- ใส่ CSS เพิ่มถ้าต้องการ -->
    <style>
        /* ปรับระยะห่างของโลโก้และเมนู */
        .header-logo {
            margin-right: 2rem; /* ระยะห่างขวาจากโลโก้ */
        }
        .header-buttons {
            margin-left: 2rem; /* ระยะห่างซ้ายของปุ่ม */
        }
    </style>
</head>
<body>
    <div class="custom-background pd123">
        <header class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-between py-3">
            <!-- Logo section -->
            <div class="header-logo">
                <a href="/" class="d-flex align-items-center text-decoration-none">
                    <img src="logo.png" alt="Logo" width="40" height="32"> <!-- ใส่โลโก้ของคุณ -->
                </a>
            </div>

            <!-- Navigation links -->
            <ul class="nav col-12 col-lg-auto me-lg-auto mb-2 justify-content-center mb-md-0">
                <li><a href="index.php" class="nav-link px-2 link-secondary text-white">หน้าแรก</a></li>
                <li><a href="catalog.php" class="nav-link px-2 link-body-emphasis text-white">เนื้อหา/หลักสูตร</a></li>
                <li><a href="#" class="nav-link px-2 link-body-emphasis text-white">เรียนตัวต่อตัว</a></li>
                <li><a href="https://www.facebook.com/profile.php?id=100029124764561&locale=th_TH" class="nav-link px-2 link-body-emphasis text-white">ติดต่อ</a></li>
                <li><a href="#" class="nav-link px-2 link-body-emphasis text-white">Discord</a></li>
            </ul>

            <!-- Buttons section -->
            <div class="header-buttons">
                <button type="button" class="btn btn-outline-primary me-2 text-white" onclick="location.href='login.php'">Login</button>
                <button type="button" class="btn btn-primary" onclick="location.href='register.php'">Register</button>
            </div>
        </header>
    </div>
</body>
</html>
