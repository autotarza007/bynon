<?php
session_start();
include('server/server.php');

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// เรียกข้อมูลของผู้ใช้
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM user_tb WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Initialize response array
$response = array('success' => false, 'message' => '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    $success_message = '';

    // แก้ไขรูปภาพ
    if (isset($_FILES['profile_image']) && !empty($_FILES['profile_image']['name'])) {
        $profile_image = $_FILES['profile_image'];
        $target_dir = "admin/uploads/";
        $target_file = $target_dir . basename($profile_image["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');

        // ตรวจสอบประเภทไฟล์
        if (!in_array($imageFileType, $allowed_extensions)) {
            $errors[] = "ไฟล์ที่อัปโหลดไม่ใช่ไฟล์ภาพ";
        }

        // ตรวจสอบขนาดไฟล์
        if ($profile_image["size"] > 5000000) { // 5MB
            $errors[] = "ขนาดไฟล์ภาพเกินขนาดที่อนุญาต (5MB)";
        }

        if (empty($errors)) {
            if (move_uploaded_file($profile_image["tmp_name"], $target_file)) {
                $profile_image_path = $target_file;
                $update_image_query = "UPDATE user_tb SET user_profile = ? WHERE user_id = ?";
                $stmt = $conn->prepare($update_image_query);
                $stmt->bind_param("si", $profile_image_path, $user_id);

                if ($stmt->execute()) {
                    $_SESSION['user_profile'] = $profile_image_path;
                    $success_message = "รูปภาพโปรไฟล์ได้รับการอัปเดตเรียบร้อยแล้ว";
                } else {
                    $errors[] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูลรูปภาพ";
                }
            } else {
                $errors[] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
            }
        }
    }

    // อัปเดตรหัสผ่าน
    if (isset($_POST['old_password'], $_POST['new_password'], $_POST['confirm_password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // ตรวจสอบรหัสผ่านเก่า
        if (empty($old_password)) {
            $errors[] = "กรุณากรอกรหัสผ่านเก่า";
        } else {
            $old_password = md5($old_password);
            if ($old_password !== $user['user_password']) {
                $errors[] = "รหัสผ่านเก่าไม่ถูกต้อง";
            }
        }

        // ตรวจสอบรหัสผ่านใหม่และยืนยันรหัสผ่านใหม่
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $errors[] = "รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน";
            } else {
                $new_password = md5($new_password);
            }
        }

        if (empty($errors)) {
            $update_query = "UPDATE user_tb SET user_password = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $new_password, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "ข้อมูลโปรไฟล์ได้รับการอัปเดตเรียบร้อยแล้ว";
            } else {
                $errors[] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูลโปรไฟล์";
            }
        }
    }

    // Set response data
    if (empty($errors)) {
        $response['success'] = true;
        $response['message'] = $success_message;
    } else {
        $response['message'] = implode("<br>", $errors);
    }

    // Return response as JSON
    echo json_encode($response);
    exit();
}

// เรียกข้อมูลคะแนน
$points_query = "SELECT points FROM user_tb WHERE user_id = ?";
$stmt = $conn->prepare($points_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$points_result = $stmt->get_result();
$points = $points_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขโปรไฟล์</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .profile-form {
            margin-top: 20px;
        }
        .profile-form img {
            max-width: 200px;
            max-height: 200px;
            display: block;
            margin: 10px 0;
        }
        .profile-info {
            margin-top: 20px;
        }
    </style>
</head>
<body class="background">
    <?php 
        if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            include('admin/headeradmin_out.php'); 
        } elseif (isset($_SESSION['success']) && $_SESSION['success'] === true) {
            include('header1.php'); 
        } else {
            include('header.php'); 
        }
    ?>
    
    <main class="container profile-form text-white">
        <!-- แสดงข้อมูลระดับสิทธิ์และคะแนน -->
        <div class="profile-info">
            <h2>ข้อมูลบัญชี</h2>
            <p><strong>ระดับสิทธิ์:</strong> 
                <?php 
                switch ($user['user_permission_level']) {
                    case 1:
                        echo "Student";
                        break;
                    case 0:
                        echo "Free";
                        break;
                    case 2:
                        echo "Admin";
                        break;
                    default:
                        echo "Unknown";
                }
                ?>
            </p>
            <p><strong>คะแนน:</strong> <?php echo htmlspecialchars($points['points'] ?? '0'); ?></p>
        </div>
        <h1>แก้ไขโปรไฟล์</h1>
        
        <form id="profile-form" action="" method="POST" enctype="multipart/form-data">
            <!-- แก้ไขรูปภาพ -->
            <div class="form-group">
                <label for="profile_image">อัปโหลดรูปภาพ:</label>
                <?php if (!empty($user['user_profile'])) { ?>
                    <img src="<?php echo htmlspecialchars($user['user_profile']); ?>" alt="Current Profile Image">
                <?php } ?>
                <input type="file" class="form-control-file" id="profile_image" name="profile_image">
            </div>
            <button type="submit" class="btn btn-primary">อัปโหลดรูปภาพ</button>
        </form>
        
        <form id="password-form" action="" method="POST">
            <!-- แก้ไขข้อมูลอีเมลและรหัสผ่าน -->
            <div class="form-group">
                <label for="new_gmail">อีเมล:</label>
                <input type="email" class="form-control" id="new_gmail" name="new_gmail" value="<?php echo htmlspecialchars($user['user_gmail']); ?>" disabled>
            </div>
            <div class="form-group">
                <label for="old_password">รหัสผ่านเก่า:</label>
                <input type="password" class="form-control" id="old_password" name="old_password">
            </div>
            <div class="form-group">
                <label for="new_password">รหัสผ่านใหม่:</label>
                <input type="password" class="form-control" id="new_password" name="new_password">
            </div>
            <div class="form-group">
                <label for="confirm_password">ยืนยันรหัสผ่านใหม่:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
            </div>
            <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
        </form>

    </main>
    <?php include('footer.php'); ?>
    <?php include('js.php'); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#profile-form').on('submit', function(event) {
                event.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: '', // Specify the URL for the profile image update if different
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ',
                                text: data.message
                            }).then(function() {
                                location.reload(); // รีเฟรชหน้าเว็บหลังจากสำเร็จ
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'ข้อผิดพลาด',
                                html: data.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'ข้อผิดพลาด',
                            text: 'เกิดข้อผิดพลาดในการส่งข้อมูล'
                        });
                    }
                });
            });

            $('#password-form').on('submit', function(event) {
                event.preventDefault();
                $.ajax({
                    url: '', // Specify the URL for the password update if different
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'สำเร็จ',
                                text: data.message
                            }).then(function() {
                                location.reload(); // รีเฟรชหน้าเว็บหลังจากสำเร็จ
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'ข้อผิดพลาด',
                                html: data.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'ข้อผิดพลาด',
                            text: 'เกิดข้อผิดพลาดในการส่งข้อมูล'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
