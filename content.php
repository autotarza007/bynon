<?php 
session_start();
include('server/server.php');

// ตรวจสอบว่าได้รับ catalog_id หรือไม่
if (isset($_GET['catalog_id']) && is_numeric($_GET['catalog_id'])) {
    $catalog_id = intval($_GET['catalog_id']);

    // ค้นหาเนื้อหาที่อยู่ในหมวดหมู่ที่เลือก
    $sql = "SELECT content.* FROM content 
            INNER JOIN catalog2content ON content.id = catalog2content.content_id 
            WHERE catalog2content.catalog_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $catalog_id);
    $stmt->execute();
    $query = $stmt->get_result();
} else {
    // ถ้าไม่มี catalog_id แสดงเนื้อหาทั้งหมด
    $sql = "SELECT * FROM content";
    $query = $conn->query($sql);
}

// ตรวจสอบการตอบคำถามของผู้ใช้
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$user_answers = [];
$total_content = 0;
$completed_content = 0;
$user_permission_level = 0;

// ดึงระดับสิทธิ์ของผู้ใช้
if ($user_id > 0) {
    $sql = "SELECT user_permission_level FROM user_tb WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $user_permission_level = $row['user_permission_level'];
    }

    $sql = "SELECT content_id FROM user_answers WHERE user_id = ? AND answered = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $user_answers[] = $row['content_id'];
    }
    
    // คำนวณจำนวนข้อทั้งหมดและจำนวนที่ทำเสร็จแล้ว
    $total_content = $query->num_rows;
    $completed_content = count($user_answers);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="logo.png">
    <link href="css/header1.css" rel="stylesheet">
    <link href="css/catalog.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <style>
        .card {
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card-img-top {
            position: relative;
            overflow: hidden;
            height: 225px;
        }
        .card-img-top img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .status-message {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            z-index: 2;
            transition: background-color 0.3s ease;
        }
        .card:hover .status-message {
            background-color: rgba(0, 123, 255, 0.7);
        }
        .card-title {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: rgba(0, 0, 0, 0.3);
            color: white;
            padding: 5px;
            z-index: 2;
            font-size: 1.2rem;
        }
        .card-details {
            position: absolute;
            bottom: 10px;
            left: 10px;
            right: 10px;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 10px;
            text-align: center;
            z-index: 2;
            display: none;
        }
        .card:hover .card-details {
            display: block;
        }
        .no-access {
            pointer-events: none;
            opacity: 0.5;
        }
        .completion-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 128, 0, 0.8);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            z-index: 2;
        }
        .completion-icon {
            font-size: 3rem;
            margin-bottom: 10px;
        }
        .completion-percent {
            font-size: 1.2rem;
        }
    </style>
</head>
<body class="background">
    
    <!-- แสดง header ตามสถานะเซสชัน -->
    <?php 
        if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            include('admin/headeradmin_out.php'); 
        } elseif (isset($_SESSION['success']) && $_SESSION['success'] === true) {
            include('header1.php'); 
        } else {
            include('header.php'); 
        }
    ?>

    <!-- แสดงเนื้อหา -->
    <main>
        <div class="album py-5">
            <div class="container custom-background">
                <h1 class="custom-background text-white">เนื้อหา</h1>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4 custom-background">
                    <?php if ($query->num_rows > 0) { ?>
                        <?php while ($row = $query->fetch_assoc()) { ?>
                            <?php
                                $is_completed = $row['status'] == 1 && in_array($row['id'], $user_answers);
                                $completion_percent = $total_content > 0 ? round(($completed_content / $total_content) * 100) : 0;
                                $has_access = $row['level'] <= $user_permission_level; // ตรวจสอบสิทธิ์
                            ?>
                            <div class="col d-flex align-items-stretch mb-4">
                                <div class="card shadow-sm d-flex flex-column <?php echo (!$has_access || $row['status'] == 0) ? 'no-access' : ''; ?>">
                                    <a href="<?php echo (!$has_access || $row['status'] == 0) ? '#' : 'content_detail.php?id=' . $row['id']; ?>" class="card-img-top d-flex align-items-center justify-content-center position-relative">
                                        <img src="admin/images_add_category/<?php echo $row['img']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" style="opacity: 0.8;" />
                                        <?php if ($is_completed) { ?>
                                            <div class="completion-overlay">
                                                <div class="completion-icon">&#10003;</div>
                                                <div class="completion-percent">ทำไปแล้ว</div>
                                            </div>
                                        <?php } elseif ($row['status'] == 0) { ?>
                                            <div class="status-message">กำลังปิดปรับปรุง</div>
                                        <?php } elseif (in_array($row['id'], $user_answers)) { ?>
                                            <div class="status-message">ผ่านแล้ว</div>
                                        <?php } elseif (!$has_access) { ?>
                                            <div class="status-message">ไม่มีสิทธิ์เข้าถึง</div>
                                        <?php } ?>
                                    </a>
                                    <div class="card-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                    <?php if ($row['status'] == 1 && $has_access) { ?>
                                        <div class="card-details"><?php echo htmlspecialchars($row['details']); ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="col-12">
                            <p class="text-center text-white">ยังไม่มีเนื้อหา</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </main>

    <!-- ท้ายสุดเว็บ -->
    <?php include('footer.php'); ?>

    <?php include('js.php'); ?>
</body>
</html>
