<?php
session_start();

require_once __DIR__ . '/../config/db_connect.php'; 

header('Content-Type: application/json');
// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn cần đăng nhập để thực hiện chức năng này']);
    exit();
}

// Lấy dữ liệu
$user_id = $_SESSION['user']['id'];
$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;

if ($room_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID phòng không hợp lệ']);
    exit();
}

//Kiểm tra xem đã tim chưa
$sql_check = "SELECT favourite_id FROM favourites WHERE user_id = ? AND room_id = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("ii", $user_id, $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Đã tim -> Xóa
    $sql_del = "DELETE FROM favourites WHERE user_id = ? AND room_id = ?";
    $stmt_del = $conn->prepare($sql_del);
    $stmt_del->bind_param("ii", $user_id, $room_id);
    $stmt_del->execute();
    echo json_encode(['status' => 'success', 'action' => 'removed']);
} else {
    // Chưa tim -> Thêm
    $sql_add = "INSERT INTO favourites (user_id, room_id) VALUES (?, ?)";
    $stmt_add = $conn->prepare($sql_add);
    $stmt_add->bind_param("ii", $user_id, $room_id);
    $stmt_add->execute();
    echo json_encode(['status' => 'success', 'action' => 'added']);
}
?>