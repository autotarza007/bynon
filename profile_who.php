<?php
session_start(); // เริ่มต้นเซสชัน

// เชื่อมต่อฐานข้อมูล
include('server/server.php');

// ตรวจสอบว่ามี user_id ที่ส่งมาหรือไม่
if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo "User ID not specified.";
    exit;
}

$user_id = intval($_GET['user_id']);

// ดึงข้อมูลโปรไฟล์ของผู้ใช้
$query = "
    SELECT user_id, user_gmail, user_profile, points
    FROM user_tb
    WHERE user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
        .background {
            background-color: #f8f9fa; /* สีพื้นหลังเพื่อความสบายตา */
        }
    </style>
</head>
<body class="background">
    <?php 
        if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            include('admin/headeradmin_out.php'); // ตรวจสอบเส้นทางให้ถูกต้อง
        } elseif (isset($_SESSION['success']) && $_SESSION['success'] === true) {
            include('header1.php'); // ตรวจสอบเส้นทางให้ถูกต้อง
        } else {
            include('header.php'); // ตรวจสอบเส้นทางให้ถูกต้อง
        }
    ?>
    <div class="container mt-5 text-white">
        <h1 class="text-center mb-4">User Profile</h1>
        <div class="text-center mb-5">
            <img src="<?php echo $user['user_profile'] ? $user['user_profile'] : 'default-profile-icon.png'; ?>" alt="Profile Image" class="profile-img">
            <h2 class="mb-3"><?php echo htmlspecialchars($user['user_gmail']); ?></h2>
            <p>Points: <?php echo htmlspecialchars($user['points']); ?></p>
        </div>
        <a href="ranking.php" class="btn btn-primary mt-4 mb-5">Back to Rankings</a>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <?php include('footer.php'); ?>
    <?php include('js.php'); ?>
</body>
</html>

<?php
$conn->close(); // ปิดการเชื่อมต่อฐานข้อมูล
?>
