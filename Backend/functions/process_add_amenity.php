<?php
session_start();

// KẾT NỐI DATABASE
require_once '../config/db_connect.php';

// KIỂM TRA QUYỀN ADMIN
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../../Frontend/index.php");
    exit();
}

// XỬ LÝ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $input_icon = trim($_POST['icon_url']);

    if (empty($name) || empty($input_icon)) {
        header("Location: ../../Frontend/src/pages/admin_dashboard.php?tab=amenities&error=empty");
        exit();
    }

    $final_icon_value = $input_icon; // Mặc định là link gốc nếu tải thất bại

    // --- LOGIC TẢI ẢNH MẠNH MẼ HƠN (DÙNG cURL) ---
    if (filter_var($input_icon, FILTER_VALIDATE_URL)) {
        
        // Tạo tên file
        $filename = basename(parse_url($input_icon, PHP_URL_PATH));
        // Nếu không lấy được đuôi file hoặc tên file, tự tạo tên ngẫu nhiên
        if (empty($filename) || strpos($filename, '.') === false) {
            $filename = 'icon_' . time() . '.png';
        }

        // ĐỊNH NGHĨA ĐƯỜNG DẪN TUYỆT ĐỐI (Dùng __DIR__ để không bao giờ sai)
        // __DIR__ là thư mục hiện tại (Backend/functions)
        $target_dir = __DIR__ . '/../../Frontend/src/assets/images/';
        $target_file = $target_dir . $filename;

        // Kiểm tra và tạo thư mục nếu chưa có
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        // DÙNG cURL THAY CHO file_get_contents (Vượt qua tường lửa chặn bot)
        $ch = curl_init($input_icon);
        $fp = fopen($target_file, 'wb'); // Mở file để ghi
        
        if ($fp) {
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Cho phép chuyển hướng
            // Giả danh trình duyệt Chrome để không bị chặn
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
            // Bỏ qua kiểm tra SSL (để tải được từ https trong môi trường local/dev)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
            
            curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);

            // Kiểm tra xem tải thành công không (Mã 200 OK và file có dung lượng)
            if ($http_code == 200 && filesize($target_file) > 0) {
                $final_icon_value = $filename; // LƯU TÊN FILE VÀO DB
            } else {
                // Tải thất bại -> Xóa file rỗng nếu lỡ tạo
                @unlink($target_file);
                // Giữ nguyên $final_icon_value là URL để ít nhất nó vẫn hiện ảnh online
            }
        }
    }

    // ---  LƯU VÀO DATABASE ---
    $sql = "INSERT INTO amenities (name, icon_url) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $name, $final_icon_value);

    if ($stmt->execute()) {
        header("Location: ../../Frontend/src/pages/admin_dashboard.php?tab=amenities&msg=success");
    } else {
        echo "Lỗi SQL: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: ../../Frontend/src/pages/admin_dashboard.php?tab=amenities");
}
?>