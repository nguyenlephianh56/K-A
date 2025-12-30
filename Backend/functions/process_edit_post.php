<?php
session_start();
require_once '../config/db_connect.php';

// Check quyền Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../Frontend/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = $_POST['room_id'];
    $title = $_POST['title'];
    $price = $_POST['price'];
    $area = $_POST['area'];
    
    // NHẬN 3 DỮ LIỆU ĐỊA CHỈ
    $street = $_POST['street'];
    $ward = $_POST['ward'];
    $city = $_POST['city'];
    
    $status = $_POST['status'];

    // Cập nhật vào đúng 3 cột trong CSDL
    $sql = "UPDATE rooms SET title = ?, price = ?, area = ?, street = ?, ward = ?, city = ?, status = ? WHERE room_id = ?";
    
    $stmt = $conn->prepare($sql);
    // Chuỗi định dạng: s=string, d=double/decimal, i=integer
    $stmt->bind_param("sdsssssi", $title, $price, $area, $street, $ward, $city, $status, $room_id);

    if ($stmt->execute()) {
        header("Location: ../../Frontend/src/pages/admin_dashboard.php?tab=posts&msg=updated");
    } else {
        echo "Lỗi: " . $conn->error;
    }
    $stmt->close();
    $conn->close();
}
?>