<?php
// ===========================================================================
// FILE QUẢN LÝ XÁC THỰC (AUTHENTICATION)
// Nhiệm vụ: Khởi động session và cung cấp các hàm kiểm tra user
// ===========================================================================

// KHỞI ĐỘNG SESSION (Luôn luôn là việc đầu tiên)
// Kiểm tra xem session đã chạy chưa, nếu chưa thì mới start để tránh lỗi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Hàm kiểm tra xem người dùng đã đăng nhập chưa
 * @return boolean (true: đã đăng nhập, false: chưa)
 */
function isLoggedIn() {
    return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

/**
 * Hàm lấy thông tin người dùng hiện tại
 * @return array|null (Trả về mảng thông tin user hoặc null nếu chưa login)
 */
function getCurrentUser() {
    if (isLoggedIn()) {
        return $_SESSION['user'];
    }
    return null;
}

/**
 * Hàm kiểm tra vai trò (Role)
 * Ví dụ: checkRole('owner') để xem có phải chủ trọ không
 */
function checkRole($roleName) {
    $user = getCurrentUser();
    if ($user && isset($user['role']) && $user['role'] === $roleName) {
        return true;
    }
    return false;
}
/**
 * Hàm yêu cầu đăng nhập (Dùng để chặn các trang nhạy cảm)
 * Nếu chưa login thì đá về trang login ngay lập tức
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // Sửa đường dẫn login.html cho đúng với thư mục của bạn
        header("Location: /K&A/Frontend/src/pages/login.html");
        exit();
    }
}
?>