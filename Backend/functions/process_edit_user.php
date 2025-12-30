<?php
session_start();
require_once '../config/db_connect.php'; // Đảm bảo đường dẫn này đúng

// Check quyền Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../Frontend/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $new_password = $_POST['password']; // Lấy mật khẩu mới (nếu có)
    
    // Khởi tạo các phần cần update
    $updates = "full_name = ?, email = ?, phone = ?, role = ?";
    $bind_types = "ssss";
    $bind_params = [$full_name, $email, $phone, $role];
    
    // XỬ LÝ PASSWORD: Nếu admin nhập mật khẩu mới
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $updates .= ", password = ?";
        $bind_types .= "s";
        $bind_params[] = $hashed_password;
    }
    
    // Chuẩn bị SQL cuối cùng
    $sql = "UPDATE users SET " . $updates . " WHERE user_id = ?";
    $bind_types .= "i";
    $bind_params[] = $user_id;

    // Thực thi
    $stmt = $conn->prepare($sql);
    
    // Dùng ReflectionClass để gọi bind_param với mảng tham số động
    $ref = new ReflectionClass('mysqli_stmt');
    $method = $ref->getMethod("bind_param");
    
    // Mảng tham số cần truyền vào bind_param: [$bind_types, $full_name, $email, ...]
    $method_params = array_merge([$bind_types], $bind_params);
    $method->invokeArgs($stmt, $method_params);

    if ($stmt->execute()) {
        header("Location: ../../Frontend/src/pages/admin_dashboard.php?tab=users&msg=updated");
    } else {
        echo "Lỗi: " . $conn->error;
    }
    $stmt->close();
    $conn->close();
}
?>