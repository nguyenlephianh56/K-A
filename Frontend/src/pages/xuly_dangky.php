<?php
session_start();
require_once '../../../Backend/config/db_connect.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Nhận dữ liệu
    $role = $_POST['role']; // student hoặc owner
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $fullname = $_POST['fullname'];
    $phone = $_POST['phonenumber'];
    
    // Nếu là student thì lấy university_id, nếu owner thì là NULL
    $university_id = ($role == 'student' && isset($_POST['university_id'])) ? $_POST['university_id'] : null;

    // Kiểm tra tài khoản đã tồn tại chưa
    $check_sql = "SELECT user_id FROM users WHERE username = ? OR email = ?";
    if ($stmt_check = $conn->prepare($check_sql)) {
        $stmt_check->bind_param("ss", $username, $email);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            // Lỗi trùng
            $_SESSION['notification'] = [
                'type' => 'error',
                'title' => 'Đăng ký thất bại',
                'message' => 'Tên đăng nhập hoặc Email đã tồn tại trong hệ thống.'
            ];
            // Quay lại trang đăng ký kèm role
            echo "<script>window.location.href='register.php?role=$role';</script>";
            exit();
        }
        $stmt_check->close();
    }

    // Mã hóa mật khẩu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert vào CSDL
    // Lưu ý: Cấu trúc bảng users của bạn phải có cột university_id
    $sql = "INSERT INTO users (username, password, full_name, email, phone, role, university_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";

    if ($stmt = $conn->prepare($sql)) {
        // 'ssssssi' -> 6 chuỗi, 1 số nguyên (university_id)
        // Nếu university_id là null thì bind_param vẫn xử lý được
        $stmt->bind_param("ssssssi", $username, $hashed_password, $fullname, $email, $phone, $role, $university_id);
        
        if ($stmt->execute()) {
            // --- THÀNH CÔNG ---
            $_SESSION['notification'] = [
                'type' => 'success',
                'title' => 'Đăng ký thành công!',
                'message' => 'Tài khoản đã được tạo. Vui lòng đăng nhập để tiếp tục.'
            ];
            // Chuyển hướng nhưng thực chất Modal trong register.php sẽ bắt sự kiện này 
            // và chuyển người dùng sang login.php khi bấm OK
            echo "<script>window.location.href='register.php?role=$role';</script>";
            exit();
        } else {
            // Lỗi Insert
             $_SESSION['notification'] = [
                'type' => 'error',
                'title' => 'Lỗi hệ thống',
                'message' => 'Không thể tạo tài khoản: ' . $conn->error
            ];
             echo "<script>window.location.href='register.php?role=$role';</script>";
             exit();
        }
       
    }
    $conn->close();
}
?>