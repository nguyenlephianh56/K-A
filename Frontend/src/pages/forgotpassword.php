<?php
session_start();
require_once '../../../Backend/config/db_connect.php';

$message = "";
$message_type = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btnReset'])) {
    //Lấy dữ liệu từ form
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    //Kiểm tra mật khẩu xác nhận
    if ($new_pass !== $confirm_pass) {
        $message = "Mật khẩu xác nhận không khớp!";
        $message_type = "error";
    } else {
        // Kiểm tra xem có người dùng nào khớp cả 3 thông tin không
        // (Username + Email + Phone phải đúng của cùng 1 người)
        $sql_check = "SELECT user_id FROM users WHERE username = ? AND email = ? AND phone = ?";
        $stmt = $conn->prepare($sql_check);
        $stmt->bind_param("sss", $username, $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Tìm thấy người dùng -> Tiến hành đổi mật khẩu
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            
            $sql_update = "UPDATE users SET password = ? WHERE username = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ss", $hashed_password, $username);
            
            if ($stmt_update->execute()) {
                $_SESSION['notification'] = [
                    'type' => 'success',
                    'title' => 'Thành công',
                    'message' => 'Mật khẩu đã được đặt lại. Hãy đăng nhập ngay!'
                ];
                header("Location: login.php");
                exit();
            } else {
                $message = "Lỗi hệ thống, vui lòng thử lại sau.";
                $message_type = "error";
            }
        } else {
            // Không tìm thấy hoặc thông tin không khớp
            $message = "Thông tin xác minh không chính xác. Vui lòng kiểm tra lại Tên đăng nhập, Email và SĐT.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khôi phục mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="card login-card">
        <div class="card-body p-4 p-md-5">
            <div class="login-header mb-4">
                <i class="fas fa-shield-alt fa-3x mb-2 text-white"></i>
                <h3 class="mb-1">Khôi Phục Mật Khẩu</h3>
                <p class="text-muted small">Nhập thông tin xác minh để đặt lại mật khẩu.</p>
            </div>

            <form action="" method="POST">
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo ($message_type == 'error') ? 'danger' : 'success'; ?> p-2 small mb-3 text-center">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="username" placeholder="Username" required>
                            <label>Tên đăng nhập</label>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="email" class="form-control" name="email" placeholder="Email" required>
                            <label>Email đăng ký</label>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="phone" placeholder="SĐT" required>
                            <label>Số điện thoại</label>
                        </div>
                    </div>
                </div>

                <hr class="my-3 text-white">
                <p class="text-white small mb-2"><i class="fas fa-key me-1"></i> Nhập mật khẩu mới:</p>

                <div class="form-floating mb-3">
                    <input type="password" class="form-control" name="new_password" placeholder="Mật khẩu mới" required>
                    <label>Mật khẩu mới</label>
                </div>

                <div class="form-floating mb-4">
                    <input type="password" class="form-control" name="confirm_password" placeholder="Xác nhận" required>
                    <label>Xác nhận mật khẩu mới</label>
                </div>

                <button type="submit" class="btn btn-custom btn-warning w-100 mb-3 text-dark fw-bold" name="btnReset">
                    Xác Minh & Đổi Mật Khẩu
                </button>
                
                <div class="text-center">
                    <a href="login.php" class="text-decoration-none small text-white-50">Quay lại Đăng nhập</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>