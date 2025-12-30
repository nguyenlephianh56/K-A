<?php
session_start();
require_once '../config/db_connect.php';

//  Check quyền Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../Frontend/index.php");
    exit();
}

// Xử lý Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);

    if (empty($title) || empty($message)) {
        echo "<script>alert('Vui lòng nhập đủ tiêu đề và nội dung!'); window.location.href='../../Frontend/src/pages/admin_dashboard.php?tab=notifications';</script>";
        exit();
    }

    // Insert vào DB
    // Cột created_at sẽ tự động lấy giờ hiện tại (CURRENT_TIMESTAMP)
    $sql = "INSERT INTO notifications (title, message) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $title, $message);

    if ($stmt->execute()) {
        // Thành công -> Quay lại dashboard
        header("Location: ../../Frontend/src/pages/admin_dashboard.php?tab=notifications&msg=sent");
    } else {
        echo "Lỗi: " . $conn->error;
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../../Frontend/src/pages/admin_dashboard.php?tab=notifications");
}
?>