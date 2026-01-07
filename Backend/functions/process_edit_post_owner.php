<?php
session_start();
require_once '../config/db_connect.php';

// 1. CHECK QUYỀN OWNER (CHỦ TRỌ)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'owner') {
    header("Location: ../../Frontend/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Kiểm tra kỹ key session ID (thường là 'id' dựa trên code useraccount.php cũ)
    $current_owner_id = $_SESSION['user']['id'] ?? $_SESSION['user']['user_id']; 

    $room_id = $_POST['room_id'];
    $title = $_POST['title'];
    $price = $_POST['price'];
    $area = $_POST['area'];
    
    // NHẬN 3 DỮ LIỆU ĐỊA CHỈ
    $street = $_POST['street'];
    $ward = $_POST['ward'];
    $city = $_POST['city'];
    
    $status = $_POST['status'];

    //CẬP NHẬT SQL
    $sql = "UPDATE rooms 
            SET title = ?, price = ?, area = ?, street = ?, ward = ?, city = ?, status = ? 
            WHERE room_id = ? AND owner_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    // Chuỗi định dạng: "sdsssssii"
    // title(s), price(d), area(s), street(s), ward(s), city(s), status(s), room_id(i), owner_id(i)
    $stmt->bind_param("sdsssssii", $title, $price, $area, $street, $ward, $city, $status, $room_id, $current_owner_id);

    if ($stmt->execute()) {
        // Kiểm tra xem có dòng nào bị ảnh hưởng không
        // (Nếu update thành công hoặc dữ liệu y chang cũ thì stmt->errno == 0)
        if ($stmt->errno == 0) {
            
            //Gán thông báo Session để hiện Popup xanh đẹp mắt
            $_SESSION['notification'] = [
                'type' => 'success', 
                'title' => 'Thành công', 
                'message' => 'Cập nhật bài đăng thành công!'
            ];
            
        } else {
            // Lỗi logic (không tìm thấy bài hoặc sai ID)
            $_SESSION['notification'] = [
                'type' => 'error', 
                'title' => 'Lỗi', 
                'message' => 'Không tìm thấy bài đăng hoặc bạn không có quyền sửa.'
            ];
        }
    } else {
        // Lỗi SQL (Cú pháp, sai kiểu dữ liệu...)
        $_SESSION['notification'] = [
            'type' => 'error', 
            'title' => 'Lỗi SQL', 
            'message' => 'Lỗi hệ thống: ' . $conn->error
        ];
    }
    
    $stmt->close();
    $conn->close();

    // QUAY VỀ TRANG QUẢN LÝ
    header("Location: ../../Frontend/src/pages/useraccount.php?tab=myposts");
    exit();
}
?>