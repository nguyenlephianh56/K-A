<?php
// 1. Khởi động session (để biết đang hủy phiên làm việc của ai)
session_start();

// 2. Xóa tất cả các biến trong session
session_unset();

// 3. Hủy hoàn toàn session trên server
session_destroy();

// 4. Chuyển hướng người dùng về lại trang chủ
header("Location: index.php");
exit();
?>