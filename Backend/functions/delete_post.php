<?php
session_start();
require_once '../config/db_connect.php';

//  Check quyền Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../Frontend/index.php");
    exit();
}

if (isset($_GET['id'])) {
    $room_id = $_GET['id'];

    // XÓA ẢNH TRÊN SERVER TRƯỚC
    // Lấy danh sách ảnh của bài đăng này
    $sql_get_imgs = "SELECT photo_url FROM room_photos WHERE room_id = ?";
    $stmt_imgs = $conn->prepare($sql_get_imgs);
    $stmt_imgs->bind_param("i", $room_id);
    $stmt_imgs->execute();
    $result_imgs = $stmt_imgs->get_result();

    while ($row = $result_imgs->fetch_assoc()) {
        $path = $row['photo_url'];
        // Đường dẫn file ảnh (Từ Backend/functions/ đi ra Frontend/src/uploads/)
        if (!empty($path) && strpos($path, 'http') === false) {
            $file_path = __DIR__ . '/../../Frontend/src/uploads/' . basename($path);
            if (file_exists($file_path)) {
                @unlink($file_path); // Xóa file
            }
        }
    }
    $stmt_imgs->close();

    // XÓA DỮ LIỆU TRONG DATABASE
    
    $sql_del = "DELETE FROM rooms WHERE room_id = ?";
    $stmt_del = $conn->prepare($sql_del);
    $stmt_del->bind_param("i", $room_id);

    if ($stmt_del->execute()) {
        header("Location: ../../Frontend/src/pages/admin_dashboard.php?tab=posts&msg=deleted");
    } else {
        echo "Lỗi xóa tin: " . $conn->error;
    }
    $stmt_del->close();
}
$conn->close();
?>