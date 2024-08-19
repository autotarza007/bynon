<?php 
session_start();
include('server/server.php');

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ตรวจสอบว่าได้รับ id หรือไม่
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $content_id = intval($_GET['id']);

    // ค้นหาข้อมูลเนื้อหาจากฐานข้อมูล
    $sql = "SELECT * FROM content WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("เนื้อหาที่คุณค้นหาไม่มีอยู่");
    }
    $content = $result->fetch_assoc();

    // ตรวจสอบระดับสิทธิ์ของเนื้อหา
    $required_permission_level = $content['level'];

    // ตรวจสอบระดับสิทธิ์ของผู้ใช้
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT user_permission_level FROM user_tb WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();
    $user_permission_level = $user['user_permission_level'];

    // ตรวจสอบสิทธิ์การเข้าถึงเนื้อหา
    if ($user_permission_level < $required_permission_level) {
        die("คุณไม่มีสิทธิ์เข้าถึงเนื้อหานี้");
    }

    // ตรวจสอบสถานะคำตอบจากตาราง user_answers
    $stmt = $conn->prepare("SELECT answered FROM user_answers WHERE user_id = ? AND content_id = ?");
    $stmt->bind_param("ii", $user_id, $content_id);
    $stmt->execute();
    $answer_result = $stmt->get_result();
    $is_answered = $answer_result->num_rows > 0 && $answer_result->fetch_assoc()['answered'] == 1;

} else {
    die("ข้อมูลเนื้อหาไม่ถูกต้อง");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($content['title']); ?></title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .content-detail {
            margin-top: 20px;
        }
        .content-detail img {
            max-width: 100%;
            height: auto;
        }
        .video-container {
            position: relative;
            padding-bottom: 56.25%; /* 16:9 */
            height: 0;
            overflow: hidden;
            max-width: 100%;
            background: #000;
        }
        .video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        .question-box {
            border: 1px solid #ddd;
            padding: 20px;
            margin-top: 20px;
            background-color: #f9f9f9;
        }
        .answered-box {
            border: 1px solid #28a745;
            padding: 20px;
            margin-top: 20px;
            background-color: #d4edda;
            color: #155724;
            text-align: center;
            font-weight: bold;
            position: relative;
            animation: fadeIn 1s ease-in-out;
        }
        .answered-box .remove-answer {
            position: absolute;
            bottom: 10px;
            left: 10px;
            background-color: #dc3545;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        @keyframes fadeIn {
            0% { opacity: 0; transform: scale(0.9); }
            100% { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body class="background">
    
    <!-- แสดง header ตามสถานะเซสชัน -->
    <?php 
        if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            include('admin/headeradmin_out.php'); 
        } elseif (isset($_SESSION['success'])) {
            include('header1.php'); 
        } else {
            include('header.php'); 
        }
    ?>

    <main>
        <div class="container content-detail custom-background">
            <h1 class="text-white"><?php echo htmlspecialchars($content['title']); ?></h1>
            <div class="custom-background">
                <p><strong class="text-white">Details:</strong></p>
                <div class="text-white"><?php echo $content['details']; ?></div> <!-- เปลี่ยนจาก <p> เป็น <div> เพื่อรองรับ HTML -->
                <?php if ($content['video']) { ?>
                <div class="video-container">
                    <video controls>
                        <source src="admin/video/<?php echo htmlspecialchars($content['video']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            <?php } ?>   
            </div>
            
            <?php if (!$is_answered) { ?>
                <div class="question-box custom-background">
                    <p class="text-white"> <strong>คำถาม:</strong> <?php echo htmlspecialchars($content['question']); ?></p>
                </div>
                <div>
                    <form id="answerForm">
                        <div class="form-group">
                            <label for="answer" class="text-white">คำตอบ:</label>
                            <input type="text" class="form-control" id="answer" name="answer" required>
                        </div>
                        <button type="submit" class="btn btn-primary">ส่งคำตอบ</button>
                    </form>
                    <div id="responseMessage" class="mt-2"></div>
                </div>
            <?php } else { ?>
                <div class="answered-box">
                    คุณได้ตอบคำถามนี้แล้ว
                    <div class="remove-answer">เริ่มทำอีกครั้ง</div>
                </div>
            <?php } ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include('footer.php'); ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    
    <!-- Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <!-- Custom Script -->
    <script>
         $(document).ready(function() {
        $('#answerForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'submit_answer.php',
                data: {
                    content_id: <?php echo $content_id; ?>,
                    answer: $('#answer').val()
                },
                success: function(response) {
                    let responseMessage;
                    if (response.trim() === 'success') {
                        responseMessage = "คำตอบของคุณถูกต้อง!";
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: responseMessage,
                            timer: 2000,
                            timerProgressBar: true,
                            didClose: () => {
                                location.reload();  // รีเฟรชหน้าเว็บหลังจากแสดงข้อความสำเร็จ
                            }
                        });
                    } else {
                        responseMessage = "คำตอบไม่ถูกต้อง หรือคุณได้ตอบคำถามนี้แล้ว";
                        Swal.fire({
                            icon: 'error',
                            title: 'ผิดพลาด!',
                            text: responseMessage,
                            timer: 2000,
                            timerProgressBar: true
                        });
                    }
                    $('#responseMessage').text(responseMessage);
                }
            });
        });
        
        $('.remove-answer').on('click', function() {
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: "คุณต้องการเริ่มทำคำถามนี้อีกครั้งหรือไม่? ข้อมูลคำตอบเดิมจะถูกลบออก.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'ใช่, เริ่มใหม่',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'remove_answer.php',
                        data: {
                            content_id: <?php echo $content_id; ?>
                        },
                        success: function(response) {
                            if (response.trim() === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'สำเร็จ!',
                                    text: 'คุณได้เริ่มทำคำถามนี้อีกครั้ง',
                                    timer: 2000,
                                    timerProgressBar: true,
                                    didClose: () => {
                                        location.reload();  // รีเฟรชหน้าเว็บหลังจากแสดงข้อความสำเร็จ
                                    }
                                });
                            }
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
