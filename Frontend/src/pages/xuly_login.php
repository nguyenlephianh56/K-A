<?php
session_start();
require_once '../../../Backend/config/db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username_input = trim($_POST['username']); 
    $password_input = $_POST['password'];       

    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    
    // Biến cờ để kiểm tra trạng thái login
    $login_success = false;

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $username_input, $username_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password_input, $user['password'])) {
                // --- 1. ĐĂNG NHẬP THÀNH CÔNG ---
                $login_success = true;

                // Lưu Session User
                $_SESSION['user'] = [
                    'id'        => $user['user_id'],
                    'username'  => $user['username'],
                    'fullname'  => $user['full_name'],
                    'email'     => $user['email'],
                    'role'      => $user['role'],
                    'avatar'    => $user['avatar'] ?? 'default.jpg'
                ];

                // Lưu thông báo Success
                $_SESSION['notification'] = [
                    'type'    => 'success',
                    'title'   => 'Đăng nhập thành công!',
                    'message' => 'Chào mừng ' . $user['full_name'] . ' quay trở lại hệ thống.'
                ];

            } else {
                //SAI MẬT KHẨU 
                $_SESSION['notification'] = [
                    'type'    => 'error',
                    'title'   => 'Đăng nhập thất bại',
                    'message' => 'Mật khẩu không chính xác. Vui lòng thử lại!'
                ];
            }
        } else {
            //SAI TÀI KHOẢN 
            $_SESSION['notification'] = [
                'type'    => 'error',
                'title'   => 'Tài khoản không tồn tại',
                'message' => 'Không tìm thấy Tên đăng nhập hoặc Email này.'
            ];
        }

        // DỌN DẸP TÀI NGUYÊN 
        
        $stmt->close(); 
    } else {
        die("Lỗi SQL: " . $conn->error);
    }
    
    // Đóng kết nối CSDL
    $conn->close();

    // --- CHUYỂN HƯỚNG ---
    // Dù thành công hay thất bại đều quay về login.php để hiện Modal
    echo '<script>window.location.href="login.php";</script>';
    exit();
}
?>