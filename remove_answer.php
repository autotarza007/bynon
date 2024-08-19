<?php 
session_start();
include('server/server.php');

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['user_id'])) {
    echo 'error';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['content_id']) && is_numeric($_POST['content_id'])) {
        $content_id = intval($_POST['content_id']);
        $user_id = $_SESSION['user_id'];

        // ค้นหาคะแนนที่ได้รับจากเนื้อหาที่ผู้ใช้ตอบไปแล้ว
        $stmt = $conn->prepare("SELECT points FROM content WHERE id = ?");
        $stmt->bind_param("i", $content_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            echo 'error';
            exit();
        }
        $content = $result->fetch_assoc();
        $points = $content['points'];

        // ลบสถานะคำตอบของผู้ใช้ในเนื้อหาที่กำหนด
        $stmt = $conn->prepare("DELETE FROM user_answers WHERE user_id = ? AND content_id = ?");
        $stmt->bind_param("ii", $user_id, $content_id);
        $stmt->execute();

        // ลดคะแนนของผู้ใช้
        $stmt = $conn->prepare("UPDATE user_tb SET points = points - ? WHERE user_id = ?");
        $stmt->bind_param("ii", $points, $user_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo 'success';
        } else {
            echo 'error';
        }
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}
