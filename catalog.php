<?php 
session_start(); 
include('server/server.php');

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    die("คุณต้องเข้าสู่ระบบเพื่อดูเนื้อหา");
}

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลหมวดหมู่
$sql = "SELECT * FROM catalog_tb";
$query = mysqli_query($conn, $sql);

// ฟังก์ชันเพื่อคำนวณเปอร์เซ็นต์การตอบคำถาม
function get_completion_percentage($catalog_id, $user_id, $conn) {
    $sql = "SELECT content_id FROM catalog2content WHERE catalog_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $catalog_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $total_content = $result->num_rows;

    $sql = "SELECT content_id FROM user_answers WHERE user_id = ? AND answered = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $answered_result = $stmt->get_result();

    $answered_content_ids = [];
    while ($row = $answered_result->fetch_assoc()) {
        $answered_content_ids[] = $row['content_id'];
    }

    $answered_count = 0;
    while ($row = $result->fetch_assoc()) {
        if (in_array($row['content_id'], $answered_content_ids)) {
            $answered_count++;
        }
    }

    return $total_content > 0 ? ($answered_count / $total_content) * 100 : 0;
}

// ฟังก์ชันตรวจสอบการทำครบทุกข้อ
function has_completed_catalog($catalog_id, $user_id, $conn) {
    $sql = "SELECT content_id FROM catalog2content WHERE catalog_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $catalog_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $total_content = $result->num_rows;

    $sql = "SELECT content_id FROM user_answers WHERE user_id = ? AND answered = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $answered_result = $stmt->get_result();

    $answered_content_ids = [];
    while ($row = $answered_result->fetch_assoc()) {
        $answered_content_ids[] = $row['content_id'];
    }

    $answered_count = 0;
    while ($row = $result->fetch_assoc()) {
        if (in_array($row['content_id'], $answered_content_ids)) {
            $answered_count++;
        }
    }

    return $total_content > 0 && $answered_count == $total_content;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="logo.png">
    <link href="css/header1.css" rel="stylesheet">
    <link href="css/catalog.css" rel="stylesheet">
    <style>
      .card {
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
        position: relative;
        cursor: pointer;
      }
      .card-img-top {
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        height: 225px;
        position: relative;
      }
      .card-img-top img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
      }
      .card:hover {
        transform: scale(1.05);
      }
      .card.disabled {
        pointer-events: none;
        opacity: 0.5;
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
        display: none;
      }
      .card.disabled .status-message {
        display: flex;
      }
      .completion-percentage-wrapper {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 30px;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        padding: 0 5px;
        box-sizing: border-box;
      }
      .completion-percentage-bar {
        position: relative;
        width: 100%;
        height: 10px;
        background: #e0e0e0;
        border-radius: 5px;
        overflow: hidden;
        background-color: #e0e0e0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
      }
      .completion-percentage {
        height: 100%;
        background-color: #00bfae;
        width: 0;
        border-radius: 5px;
        transition: width 0.3s ease;
      }
      .completion-percentage-text {
        position: absolute;
        right: 10px;
        color: white;
        font-size: 0.8rem;
        font-weight: bold;
        margin-left: 5px;
      }
      .completed-message {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 123, 255, 0.2); /* ปรับให้สีพื้นหลังจางลง */
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-size: 1.5rem;
        font-weight: bold;
        z-index: 2;
        display: none;
      }

      .completed-icon {
        margin-right: 10px;
      }
      .card.completed .completed-message {
        display: flex;
      }
      .completed-message a {
        color: white;
        text-decoration: none;
      }
      .completed-message a:hover {
        text-decoration: underline;
      }
      .bg-custom-gray {
        background-color: #6c757d; /* สีเทาอ่อน */
        color: white; /* สีข้อความให้เข้ากันกับพื้นหลัง */
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

    <!-- แสดงหมวดหมู่ -->
    <main>
      <div class="album py-5">
        <div class="container custom-background">
          <h1 class="text-white">หมวดหมู่</h1>
          <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            <?php while($row = $query->fetch_assoc()) { 
                $completion_percentage = get_completion_percentage($row['id'], $user_id, $conn);
                $completed = has_completed_catalog($row['id'], $user_id, $conn);
            ?>
              <div class="col d-flex align-items-stretch mb-4">
                <div class="card shadow-sm d-flex flex-column <?php echo $row['status'] == 0 ? 'disabled' : ($completed ? 'completed' : ''); ?>" onclick="window.location.href='<?php echo $row['status'] == 0 ? '#' : 'content.php?catalog_id=' . $row['id']; ?>'">
                  <div class="card-img-top d-flex align-items-center justify-content-center position-relative">
                    <img src="admin/images_add_category/<?php echo $row['img']; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>" style="opacity: 0.8;" />
                    <?php if ($completed) { ?>
                        <div class="completed-message">
                            <i class="completed-icon">&#10004;</i> คุณทำเสร็จหมดแล้ว
                        </div>
                    <?php } ?>
                    <?php if ($row['status'] == 0) { ?>
                        <div class="status-message">ไม่พร้อมใช้งาน</div>
                    <?php } ?>
                    <div class="completion-percentage-wrapper">
                      <div class="completion-percentage-bar">
                        <div class="completion-percentage" style="width: <?php echo $completion_percentage; ?>%;"></div>
                        <span class="completion-percentage-text"><?php echo round($completion_percentage); ?>%</span>
                      </div>
                    </div>
                  </div>
                  <p class="position-absolute m-3 shadow p-auto bg-custom-gray text-light display-custom"><?php echo htmlspecialchars($row['title']); ?></p>
                </div>
              </div>
            <?php } ?>
          </div>
        </div>
      </div>
    </main>
    <?php include('footer.php'); ?>
    <?php include('js.php'); ?>
</body>
</html>
