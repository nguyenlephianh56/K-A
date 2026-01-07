<?php
//Khởi động session (để biết đang hủy phiên làm việc của ai)
session_start();

// Xóa tất cả các biến trong session
session_unset();

//Hủy hoàn toàn session trên server
session_destroy();

// Chuyển hướng người dùng về lại trang chủ
header("Location: index.php");
exit();
?>