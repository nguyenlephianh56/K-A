<?php
session_start();
// Đảm bảo đường dẫn tới file connect DB là chính xác
require_once '../config/db_connect.php';

// Chặn truy cập trực tiếp bằng đường dẫn (chỉ nhận POST từ nút btnReset)
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_POST['btnReset'])) {
    header("Location: recovery_password.php");
    exit();
}

// 1. Lấy và làm sạch dữ liệu
$username = trim($_POST['username']);
$email    = trim($_POST['email']);
$phone    = trim($_POST['phone']);
$new_pass = $_POST['new_password'];
$confirm_pass = $_POST['confirm_password'];

// Lưu lại dữ liệu nhập cũ để điền lại form nếu lỗi (trừ mật khẩu)
$_SESSION['old_input'] = [
    'username' => $username,
    'email'    => $email,
    'phone'    => $phone
];

// 2. Validate cơ bản
if (empty($username) || empty($email) || empty($phone) || empty($new_pass)) {
    $_SESSION['error_msg'] = "Vui lòng nhập đầy đủ thông tin.";
    header("Location: recovery_password.php");
    exit();
}

if ($new_pass !== $confirm_pass) {
    $_SESSION['error_msg'] = "Mật khẩu xác nhận không khớp!";
    header("Location: recovery_password.php");
    exit();
}

// 3. Kiểm tra thông tin xác thực trong Database
// Cả 3 trường Username, Email, Phone phải cùng thuộc về 1 người dùng
$sql_check = "SELECT user_id FROM users WHERE username = ? AND email = ? AND phone = ?";
$stmt = $conn->prepare($sql_check);

if ($stmt) {
    $stmt->bind_param("sss", $username, $email, $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // --- THÔNG TIN KHỚP: TIẾN HÀNH ĐỔI MẬT KHẨU ---
        
        $stmt->close(); // Đóng kết nối cũ

        // Hash mật khẩu mới
        $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
        
        $sql_update = "UPDATE users SET password = ? WHERE username = ?";
        $stmt_update = $conn->prepare($sql_update);
        
        if ($stmt_update) {
            $stmt_update->bind_param("ss", $hashed_password, $username);
            
            if ($stmt_update->execute()) {
                // Thành công: Tạo thông báo Session để hiển thị bên trang Login
                $_SESSION['notification'] = [
                    'type'    => 'success',
                    'title'   => 'Thành công',
                    'message' => 'Mật khẩu đã được đặt lại. Hãy đăng nhập ngay!'
                ];
                
                // Xóa dữ liệu input cũ vì đã thành công
                unset($_SESSION['old_input']);
                
                // Chuyển hướng về trang đăng nhập
                header("Location: login.php");
                exit();
            } else {
                $_SESSION['error_msg'] = "Lỗi hệ thống khi cập nhật mật khẩu. Thử lại sau.";
            }
            $stmt_update->close();
        } else {
            $_SESSION['error_msg'] = "Lỗi chuẩn bị truy vấn cập nhật.";
        }

    } else {
        // --- THÔNG TIN KHÔNG KHỚP ---
        $_SESSION['error_msg'] = "Thông tin xác minh không chính xác (Username, Email hoặc SĐT bị sai).";
    }
} else {
    $_SESSION['error_msg'] = "Lỗi kết nối cơ sở dữ liệu.";
}

$conn->close();

// Nếu chạy đến đây nghĩa là có lỗi, quay lại trang nhập
header("Location: forgotpassword.php");
exit();
?>