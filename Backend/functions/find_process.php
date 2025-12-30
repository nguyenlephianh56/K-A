<?php
require_once '../../../Backend/config/db_connect.php';
$conn->set_charset("utf8mb4");

// QUAN TRỌNG: Thiết lập font tiếng Việt
$conn->set_charset("utf8mb4");

// NHẬN DỮ LIỆU ĐẦU VÀO
$limit = 6; 
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; 
$offset = ($current_page - 1) * $limit; 

// Nhận tham số (có kiểm tra isset để tránh lỗi Warning)
$keyword  = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$district = isset($_GET['district']) ? $_GET['district'] : '';
$type_id  = isset($_GET['type']) ? intval($_GET['type']) : 0;
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$area_range  = isset($_GET['area_range']) ? $_GET['area_range'] : '';
$amenities   = isset($_GET['amenity']) ? $_GET['amenity'] : [];

// XÂY DỰNG MỆNH ĐỀ WHERE
$where_clauses = ["r.status = 'available'"]; 

// A. Tìm theo TỪ KHÓA 
if (!empty($keyword)) {
    $safe_key = $conn->real_escape_string($keyword);
    // Tìm trong Tên phòng, Tên đường, Phường, Quận
    $where_clauses[] = "(r.title LIKE '%$safe_key%' 
                        OR r.street LIKE '%$safe_key%' 
                        OR r.ward LIKE '%$safe_key%' 
                        OR r.city LIKE '%$safe_key%')";
}

// B. Tìm theo QUẬN (tìm trong ward và city)
if (!empty($district)) {
    $safe_district = $conn->real_escape_string($district);
    // Dùng LIKE để tìm tương đối (VD: chọn 'Hải Châu' vẫn tìm ra 'Quận Hải Châu')
    $where_clauses[] = "(r.ward LIKE '%$safe_district%' OR r.city LIKE '%$safe_district%')";
}
// C. Lọc Giá
if (!empty($price_range)) {
    switch ($price_range) {
        case '1': $where_clauses[] = "r.price < 1000000"; break;
        case '2': $where_clauses[] = "r.price BETWEEN 1000000 AND 3000000"; break;
        case '3': $where_clauses[] = "r.price BETWEEN 3000000 AND 5000000"; break;
        case '4': $where_clauses[] = "r.price BETWEEN 5000000 AND 15000000"; break;
        case '5': $where_clauses[] = "r.price > 15000000"; break;
    }
}

// D. Lọc Diện tích
if (!empty($area_range)) {
    switch ($area_range) {
        case '1': $where_clauses[] = "r.area < 20"; break;
        case '2': $where_clauses[] = "r.area BETWEEN 20 AND 40"; break;
        case '3': $where_clauses[] = "r.area BETWEEN 40 AND 60"; break;
        case '4': $where_clauses[] = "r.area BETWEEN 60 AND 80"; break;
        case '5': $where_clauses[] = "r.area > 80"; break;
    }
}

// E. Lọc Tiện nghi
if (!empty($amenities) && is_array($amenities)) {
    foreach ($amenities as $am_id) {
        $am_id = intval($am_id);
        $where_clauses[] = "EXISTS (
            SELECT 1 FROM room_amenities ra 
            WHERE ra.room_id = r.room_id AND ra.amenity_id = $am_id
        )";
    }
}

$where_sql = " WHERE " . implode(" AND ", $where_clauses);

//  ĐẾM TỔNG SỐ TIN
$total_pages = 0;
$total_records = 0;
$sql_count = "SELECT COUNT(*) as total FROM rooms r " . $where_sql;
$result_count = $conn->query($sql_count);

if ($result_count) {
    $row_count = $result_count->fetch_assoc();
    $total_records = $row_count['total'];
    if ($total_records > 0) {
        $total_pages = ceil($total_records / $limit);
    }
}

// TRUY VẤN DỮ LIỆU CHÍNH (Sửa JOIN thành LEFT JOIN để an toàn)
$sql = "SELECT 
            r.*, 
            u.full_name,
            (SELECT photo_url FROM room_photos WHERE room_id = r.room_id LIMIT 1) AS thumbnail
        FROM rooms r
        LEFT JOIN users u ON r.owner_id = u.user_id" . $where_sql;

// Sắp xếp
switch ($sort_option) {
    case 'price_asc':  $sql .= " ORDER BY r.price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY r.price DESC"; break;
    case 'area_desc':  $sql .= " ORDER BY r.area DESC"; break;
    default:           $sql .= " ORDER BY r.created_at DESC"; break;
}

$sql .= " LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);
$total_rows = ($result) ? $result->num_rows : 0;

// ĐOẠN KIỂM TRA LỖI 
if (!$result) {
    die("Lỗi SQL: " . $conn->error . "<br>Câu lệnh: " . $sql);
}
$total_rows = $result->num_rows;

// Lấy danh sách tiện nghi cho Sidebar
$sql_all_amenities = "SELECT * FROM amenities";
$res_all_amenities = $conn->query($sql_all_amenities);
?>