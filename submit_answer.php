<?php 
session_start();
include('server/server.php');

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    echo 'unauthorized';
    exit();
}

// ตรวจสอบการส่งคำตอบ
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['content_id']) && is_numeric($_POST['content_id']) && isset($_POST['answer'])) {
        $content_id = intval($_POST['content_id']);
        $user_answer = trim($_POST['answer']);
        $user_id = $_SESSION['user_id'];

        // ดึงข้อมูลเนื้อหาจากฐานข้อมูล
        $sql = "SELECT * FROM content WHERE id = ? AND status = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $content_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo 'content_not_found';
            exit();
        }
        $content = $result->fetch_assoc();

        // ตรวจสอบคำตอบ
        if ($user_answer === $content['answer']) {
            // ถ้าคำตอบถูกต้อง
            $points = $content['points']; // ดึงคะแนนจากเนื้อหา

            // อัปเดตสถานะคำตอบในฐานข้อมูลสำหรับผู้ใช้ที่กำหนด
            $stmt = $conn->prepare("INSERT INTO user_answers (user_id, content_id, answered) VALUES (?, ?, 1)
                                    ON DUPLICATE KEY UPDATE answered = 1");
            $stmt->bind_param("ii", $user_id, $content_id);
            $stmt->execute();

            // เพิ่มคะแนนให้กับผู้ใช้
            $stmt = $conn->prepare("UPDATE user_tb SET points = points + ? WHERE user_id = ?");
            $stmt->bind_param("ii", $points, $user_id);
            $stmt->execute();

            echo 'success';
        } else {
            echo 'incorrect_answer';
        }
    } else {
        echo 'invalid_data';
    }
} else {
    echo 'invalid_request';
}
?>
