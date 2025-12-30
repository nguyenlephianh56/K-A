<?php
session_start();
require_once '../config/db_connect.php'; // Kết nối CSDL

// Kiểm tra đăng nhập và quyền Owner
// Nếu chưa đăng nhập hoặc không phải chủ trọ thì dừng ngay
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'owner') {
    // Thay vì die(), ta lưu thông báo vào Session
    $_SESSION['permission_error'] = "Bạn không có quyền đăng tin hoặc phiên đăng nhập đã hết hạn. Vui lòng đăng nhập lại tài khoản Chủ trọ.";
    
    // Chuyển hướng về lại trang User Account (đường dẫn tùy cấu trúc của bạn)
    header("Location: ../../Frontend/src/pages/useraccount.php");
    exit();
}

// Chỉ xử lý khi có dữ liệu gửi lên (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- A. LẤY DỮ LIỆU TỪ FORM ---
    // Dùng toán tử ?? '' để tránh lỗi nếu dữ liệu bị thiếu
    $owner_id = $_SESSION['user']['id'];
    $title = $_POST['title'] ?? '';
    $price = $_POST['price'] ?? 0;
    $area = $_POST['area'] ?? 0;
    $description = $_POST['description'] ?? '';
    
    // Địa chỉ
    $street = $_POST['street'] ?? ''; 
    $ward = $_POST['ward'] ?? '';
    $city = $_POST['city'] ?? 'TP. Đà Nẵng';
    
    $room_type_id = $_POST['room_type_id']; 

    // --- B. LƯU THÔNG TIN PHÒNG (TABLE ROOMS) ---
    $sql_room = "INSERT INTO rooms (owner_id, room_type_id, title, description, price, area, street, ward, city, status, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', NOW())";
                 
    $stmt = $conn->prepare($sql_room);
    
    // Kiểm tra lỗi SQL nếu có
    if (!$stmt) {
        die("Lỗi chuẩn bị SQL: " . $conn->error);
    }

    $stmt->bind_param("iissdisss", $owner_id, $room_type_id, $title, $description, $price, $area, $street, $ward, $city);
    
    if ($stmt->execute()) {
        // Lấy ID của phòng vừa tạo để dùng cho ảnh và tiện ích
        $room_id = $conn->insert_id; 
        
        // --- C. XỬ LÝ TIỆN ÍCH (AMENITIES) ---
        if (isset($_POST['amenities']) && is_array($_POST['amenities'])) {
            $sql_amenity = "INSERT INTO room_amenities (room_id, amenity_id) VALUES (?, ?)";
            $stmt_am = $conn->prepare($sql_amenity);
            
            foreach ($_POST['amenities'] as $amenity_id) {
                $stmt_am->bind_param("ii", $room_id, $amenity_id);
                $stmt_am->execute();
            }
            $stmt_am->close();
        }

       // --- D. XỬ LÝ HÌNH ẢNH ---
        if (isset($_FILES['room_images'])) {
            
            // Xác định đường dẫn thư mục uploads (Vẫn giữ nguyên để move file)
            $base_dir = dirname(__DIR__, 2); 
            $target_dir = $base_dir . "/Frontend/src/uploads/";

            // Tạo thư mục nếu chưa có
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $count = count($_FILES['room_images']['name']);
            
            $sql_photo = "INSERT INTO room_photos (room_id, photo_url) VALUES (?, ?)";
            $stmt_photo = $conn->prepare($sql_photo);

            for ($i = 0; $i < $count; $i++) {
                $name = $_FILES['room_images']['name'][$i];
                $tmp = $_FILES['room_images']['tmp_name'][$i];
                $error = $_FILES['room_images']['error'][$i];
                
                if (!empty($name) && $error === 0) {
                    
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    
                    // Tạo tên file mới
                    $new_name = time() . "_" . uniqid() . "_" . $i . "." . $ext; 
                    
                    // Đường dẫn vật lý để lưu file vào ổ cứng (Tuyệt đối)
                    $dest = $target_dir . $new_name;

                    // Di chuyển file vào thư mục uploads
                    if (move_uploaded_file($tmp, $dest)) {
                        
                        // --- SỬA Ở ĐÂY ---
                        // Tạo đường dẫn tương đối để lưu vào CSDL theo yêu cầu của bạn
                        $db_url = "Frontend/src/uploads/" . $new_name;

                        // Lưu $db_url vào CSDL thay vì $new_name
                        $stmt_photo->bind_param("is", $room_id, $db_url);
                        $stmt_photo->execute();
                    }
                }
            }
            $stmt_photo->close();
        }

        // --- E. TẠO THÔNG BÁO VÀ CHUYỂN HƯỚNG ---
        
        // Lưu thông báo vào Session để hiển thị ở trang sau
        $_SESSION['notification'] = [
            'type' => 'success',
            'title' => 'Đăng tin thành công!',
            'message' => 'Bài đăng của bạn đã được gửi lên hệ thống và đang chờ duyệt.'
        ];

        // Đóng kết nối
        $stmt->close();
        $conn->close();

        // Chuyển hướng về trang quản lý tin
        header("Location: ../../Frontend/src/pages/useraccount.php?tab=myposts");
        exit();

    } else {
        echo "Lỗi khi lưu bài đăng: " . $stmt->error;
    }
} else {
    // Nếu truy cập trực tiếp file này mà không submit form
    header("Location: ../../index.php");
    exit();
}
?>