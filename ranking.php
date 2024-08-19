<?php
// เชื่อมต่อฐานข้อมูล
include('server/server.php');

// เริ่มต้นเซสชัน
session_start();

// ดึงข้อมูลการจัดอันดับทั้งหมด
$query = "
    SELECT user_id, user_gmail, user_profile, points
    FROM user_tb
    ORDER BY points DESC
";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet"> <!-- ไฟล์ CSS ของ FontAwesome -->
    <link rel="icon" type="image/png" href="logo.png">
    <link href="css/header1.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .ranking-table {
            margin-top: 30px;
        }
        .ranking-table img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
        .ranking-table tr {
            cursor: pointer;
        }
        .ranking-table tr:hover {
            background-color: #f5f5f5;
        }
        .ranking-table a {
            color: black;
            text-decoration: none;
        }
        .ranking-table a:hover {
            text-decoration: underline;
        }
        .rank-icon {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        .rank-icon.first {
            color: gold;
        }
        .rank-icon.second {
            color: silver;
        }
        .rank-icon.third {
            color: #cd7f32;
        }
    </style>
</head>
<body class="background">
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
    
    <div class="container mt-5 custom-background2">
        <h1 class="text-center mb-4 text-white">Rankings</h1>
        
        <!-- ตารางสำหรับอันดับทั้งหมด -->
        <div class="ranking-table">
            <h2 class="text-center text-white">All Rankings</h2>
            <table class="table table-bordered">
                <thead>
                    <tr class="text-white">
                        <th>Rank</th>
                        <th>Profile</th>
                        <th>Email</th>
                        <th>Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // รีเซ็ตตัวชี้ของผลลัพธ์กลับไปที่ต้น
                    $result->data_seek(0);
                    $rank = 1;
                    while ($row = $result->fetch_assoc()) {
                        $profileImage = $row['user_profile'] ? $row['user_profile'] : 'default-profile-icon.png';
                        ?>
                        <tr onclick="window.location.href='profile_who.php?user_id=<?php echo $row['user_id']; ?>';">
                            <td class="text-white">
                                <?php if ($rank === 1): ?>
                                    <i class="rank-icon first fas fa-trophy"></i>
                                <?php elseif ($rank === 2): ?>
                                    <i class="rank-icon second fas fa-medal"></i>
                                <?php elseif ($rank === 3): ?>
                                    <i class="rank-icon third fas fa-certificate"></i>
                                <?php else: ?>
                                    <?php echo $rank; ?>
                                <?php endif; ?>
                            </td>
                            <td><img src="<?php echo $profileImage; ?>" alt="Profile Image"></td>
                            <td class="text-white"><?php echo $row['user_gmail']; ?></td>
                            <td class="text-white"><?php echo $row['points']; ?></td>
                        </tr>
                        <?php
                        $rank++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <!-- ท้ายสุดเว็บ -->
    <?php include('footer.php'); ?>


    </div>
    <!-- js.php -->
    <?php include('js.php'); ?>
</body>
</html>
