<?php
session_start();
require_once '../config/db_connect.php';

// Check quyền Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../Frontend/index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id_to_delete = $_GET['id'];
    $current_admin_id = $_SESSION['user']['user_id']; 

    // QUAN TRỌNG: Không cho phép Admin tự xóa chính mình
    if ($id_to_delete == $current_admin_id) {
        echo "<script>alert('Bạn không thể tự xóa tài khoản của chính mình!'); window.location.href='../../Frontend/src/pages/admin_dashboard.php?tab=users';</script>";
        exit();
    }

    // Thực hiện xóa
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_to_delete);

    if ($stmt->execute()) {
        header("Location: ../../Frontend/src/pages/admin_dashboard.php?tab=users&msg=deleted");
    } else {
        echo "Lỗi: " . $conn->error;
    }
    $stmt->close();
}
$conn->close();
?>