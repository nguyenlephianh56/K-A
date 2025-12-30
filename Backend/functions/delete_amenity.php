<?php
session_start();

// KẾT NỐI DATABASE
require_once '../config/db_connect.php';

// KIỂM TRA QUYỀN ADMIN
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../Frontend/index.php");
    exit();
}

// XỬ LÝ XÓA
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // BƯỚC QUAN TRỌNG: Lấy thông tin ảnh cũ để xóa file trên ổ cứng
    $sql_get_img = "SELECT icon_url FROM amenities WHERE amenity_id = ?";
    $stmt_get = $conn->prepare($sql_get_img);
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $icon_url = $row['icon_url'];

        // Kiểm tra xem đây có phải là file ảnh lưu trên server không
        // (Không xóa nếu là link online 'http...' hoặc class icon 'bi-wifi')
        if (!empty($icon_url) && strpos($icon_url, 'http') === false && strpos($icon_url, 'bi-') === false && strpos($icon_url, 'fa-') === false) {
            
            // Xác định đường dẫn file ảnh
            $file_path = __DIR__ . '/../../Frontend/src/assets/images/' . $icon_url;

            // Nếu file tồn tại thì xóa nó đi
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }
    }
    $stmt_get->close();

    // BƯỚC TIẾP THEO: Xóa dòng dữ liệu trong Database
    $sql_delete = "DELETE FROM amenities WHERE amenity_id = ?";
    $stmt_del = $conn->prepare($sql_delete);
    $stmt_del->bind_param("i", $id);

    if ($stmt_del->execute()) {
        // Thành công: Quay về dashboard
        header("Location: ../../Frontend/src/pages/admin_dashboard.php?tab=amenities&msg=deleted");
    } else {
        echo "Lỗi xóa dữ liệu: " . $conn->error;
    }
    $stmt_del->close();
} else {
    // Nếu không có ID, quay về trang danh sách
    header("Location: ../../Frontend/src/pages/admin_dashboard.php?tab=amenities");
}

$conn->close();
?>