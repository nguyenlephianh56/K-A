<?php
//KẾT NỐI VÀ HEADER
require_once '../../../Backend/config/db_connect.php';
require_once '../partials/header.php';


//PHÂN TRANG
$limit = 6; // Số tin mỗi trang
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1; // Trang hiện tại (mặc định là 1)
$offset = ($current_page - 1) * $limit; // Vị trí bắt đầu lấy dữ liệu

//LẤY ID LOẠI PHÒNG TỪ URL
$type_id = isset($_GET['type']) ? intval($_GET['type']) : 0;


// Lấy tùy chọn sắp xếp từ URL
$sort_option = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// set cac bien loc
$price_range = isset($_GET['price_range']) ? $_GET['price_range'] : '';
$area_range  = isset($_GET['area_range']) ? $_GET['area_range'] : '';
$amenities   = isset($_GET['amenity']) ? $_GET['amenity'] : [];


// Lấy tên loại phòng để hiển thị tiêu đề (Breadcrumb)
$type_name = "Tất cả phòng trọ";
if ($type_id > 0) {
    $sql_type = "SELECT name FROM room_types WHERE room_type_id = $type_id";
    $res_type = $conn->query($sql_type);
    if ($res_type->num_rows > 0) {
        $type_name = $res_type->fetch_assoc()['name'];
    }
}


// XÂY DỰNG CÂU LỆNH SQL CƠ BẢN (Dùng chung cho Đếm và Lấy list phòng)
// Khởi tạo mệnh đề WHERE cơ bản
$where_clauses = ["r.status = 'available'"];

// A. Lọc theo Loại phòng
if ($type_id > 0) {
    $where_clauses[] = "r.room_type_id = $type_id";
}

// B. Lọc theo Giá (Dựa trên value của Radio button)
if (!empty($price_range)) {
    switch ($price_range) {
        case '1': $where_clauses[] = "r.price < 1000000"; break;
        case '2': $where_clauses[] = "r.price BETWEEN 1000000 AND 3000000"; break;
        case '3': $where_clauses[] = "r.price BETWEEN 3000000 AND 5000000"; break;
        case '4': $where_clauses[] = "r.price BETWEEN 5000000 AND 15000000"; break;
        case '5': $where_clauses[] = "r.price > 15000000"; break;
    }
}

// C. Lọc theo Diện tích
if (!empty($area_range)) {
    switch ($area_range) {
        case '1': $where_clauses[] = "r.area < 20"; break;
        case '2': $where_clauses[] = "r.area BETWEEN 20 AND 40"; break;
        case '3': $where_clauses[] = "r.area BETWEEN 40 AND 60"; break;
        case '4': $where_clauses[] = "r.area BETWEEN 60 AND 80"; break;
        case '5': $where_clauses[] = "r.area > 80"; break;
    }
}

// D. Lọc theo Tiện nghi (Quan trọng: Dùng EXISTS để lọc phòng có tiện nghi đó)
if (!empty($amenities) && is_array($amenities)) {
    foreach ($amenities as $am_id) {
        $am_id = intval($am_id);
        // Câu này nghĩa là: Hãy chọn phòng nào mả trong bảng room_amenities có liên kết với id tiện ích này
        $where_clauses[] = "EXISTS (
            SELECT 1 FROM room_amenities ra 
            WHERE ra.room_id = r.room_id AND ra.amenity_id = $am_id
        )";
    }
}

// Nối các điều kiện lại bằng AND
$where_sql = " WHERE " . implode(" AND ", $where_clauses);

//ĐẾM TỔNG SỐ TIN (Để tính số trang)
$total_pages = 0;
$total_records = 0;

$sql_count = "SELECT COUNT(*) as total FROM rooms WHERE status = 'available'";
if ($type_id > 0) {
    $sql_count .= " AND room_type_id = $type_id";
}

$result_count = $conn->query($sql_count);
if ($result_count) {
    $row_count = $result_count->fetch_assoc();
    $total_records = $row_count['total'];
    if ($total_records > 0) {
        $total_pages = ceil($total_records / $limit);
    }
}


// TRUY VẤN DANH SÁCH PHÒNG THEO LOẠI
// lấy 1 ảnh đại diện
$sql = "SELECT 
            r.*, 
            u.full_name,
            (SELECT photo_url FROM room_photos WHERE room_id = r.room_id LIMIT 1) AS thumbnail
        FROM rooms r
        JOIN users u ON r.owner_id = u.user_id" . $where_sql;

// Xử lý sắp xếp (Sort)
switch ($sort_option) {
    case 'price_asc':  $sql .= " ORDER BY r.price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY r.price DESC"; break;
    case 'area_desc':  $sql .= " ORDER BY r.area DESC"; break;
    default:           $sql .= " ORDER BY r.created_at DESC"; break;
}

//them litmit offset cho phan trang
$sql .= " LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);
$total_rows = ($result) ? $result->num_rows : 0;

// LẤY DANH SÁCH TIỆN NGHI TỪ DB ĐỂ HIỆN RA SIDEBAR
$sql_all_amenities = "SELECT * FROM amenities";
$res_all_amenities = $conn->query($sql_all_amenities);
?>

<!-- START HTML BODY -->
<div class="container" style="margin-top: 80px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo $base_url; ?>public/index.php" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $type_name; ?></li>
        </ol>
    </nav>
</div>

<div class="container mb-4">
    <h4 class="fw-bold text-uppercase text-primary"><?php echo $type_name; ?></h4>
    <p class="text-muted">Tìm thấy <span class="fw-bold text-dark"><?php echo $total_rows; ?></span> kết quả phù hợp.</p>
</div>

<div class="container mb-5">
    <div class="row">
        
        <!-- CỘT TRÁI: DANH SÁCH SẢN PHẨM -->
        <div class="col-lg-9 col-12">
            
            <!-- Bộ lọc sắp xếp (Giữ nguyên HTML của bạn, chưa xử lý logic) -->
            <div class="d-flex justify-content-end mb-3">
                <div class="d-flex align-items-center">
                    <span class="me-2 small text-muted">Sắp xếp theo:</span>
                    <select class="form-select form-select-sm" style="width: 170px;" 
                        onchange="window.location.href='?type=<?php echo $type_id; ?>&sort=' + this.value">
                        
                        <option value="newest" <?php echo ($sort_option == 'newest') ? 'selected' : ''; ?>>Mới nhất</option>
                        <option value="price_asc" <?php echo ($sort_option == 'price_asc') ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                        <option value="price_desc" <?php echo ($sort_option == 'price_desc') ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                        <option value="area_desc" <?php echo ($sort_option == 'area_desc') ? 'selected' : ''; ?>>Diện tích lớn nhất</option>
                    </select>
                </div>
            </div>

            <!-- VÒNG LẶP HIỂN THỊ CARD -->
            <?php
            if ($total_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Include component card ngang
                    // Lưu ý đường dẫn: từ public/listing.php -> lùi ra src/component
                    include '../component/product-card-long.php';
                }
            } else {
                echo "<div class='alert alert-warning text-center'>Chưa có tin đăng nào thuộc mục này.</div>";
            }
            ?>

            <!-- Phân trang html dong, tu tao trang dua tren du lieu thuc -->
             <?php if ($total_pages > 1): ?>
           <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    
                    <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?type=<?php echo $type_id; ?>&page=<?php echo $current_page - 1; ?>">Trước</a>
                    </li>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                      <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?type=<?php echo $type_id; ?>&page=<?php echo $i; ?>&sort=<?php echo $sort_option; ?>">
                            <?php echo $i; ?>
                         </a>
                    </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?type=<?php echo $type_id; ?>&page=<?php echo $current_page + 1; ?>">Sau</a>
                    </li>

                </ul>
            </nav>
            <?php endif; ?>

        </div>

        <!-- CỘT PHẢI: BỘ LỌC TÌM KIẾM (Sidebar) -->
        <div class="col-lg-3 col-12">
            <div class="card border-0 shadow-sm p-3 sticky-top" style="top: 80px; z-index: 1;">
                <div class="d-flex align-items-center mb-3 "style="color:#4A70A9">
                    <i class="bi bi-funnel-fill me-2"></i>
                    <h6 class="fw-bold mb-0 text-uppercase">Lọc tìm kiếm</h6>
                </div>

                <!-- Form lọc -->
                <form action="listing.php" method="GET" id="filterForm">
                    
                    <input type="hidden" name="type" value="<?php echo $type_id; ?>">

                    <div class="filter-group mb-4">
                        <h6 class="fw-bold mb-2 small text-dark border-bottom pb-1">KHOẢNG GIÁ</h6>
                        <?php 
                        $prices = [
                            '1' => 'Dưới 1 triệu',
                            '2' => '1 - 3 triệu',
                            '3' => '3 - 5 triệu',
                            '4' => '5 - 15 triệu',
                            '5' => 'Trên 15 triệu'
                        ];
                        foreach($prices as $val => $label): 
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="price_range" id="gia<?php echo $val; ?>" value="<?php echo $val; ?>"
                                <?php echo ($price_range == $val) ? 'checked' : ''; ?>>
                            <label class="form-check-label small" for="gia<?php echo $val; ?>"><?php echo $label; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-group mb-4">
                        <h6 class="fw-bold mb-2 small text-dark border-bottom pb-1">DIỆN TÍCH</h6>
                        <?php 
                        $areas = [
                            '1' => 'Dưới 20 m²',
                            '2' => '20 - 40 m²',
                            '3' => '40 - 60 m²',
                            '4' => '60 - 80 m²',
                            '5' => 'Trên 80 m²'
                        ];
                        foreach($areas as $val => $label): 
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="area_range" id="dt<?php echo $val; ?>" value="<?php echo $val; ?>"
                                <?php echo ($area_range == $val) ? 'checked' : ''; ?>>
                            <label class="form-check-label small" for="dt<?php echo $val; ?>"><?php echo $label; ?></label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="filter-group mb-4">
                        <h6 class="fw-bold mb-2 small text-dark border-bottom pb-1">TIỆN NGHI</h6>
                        <?php 
                        if ($res_all_amenities && $res_all_amenities->num_rows > 0) {
                            while($am = $res_all_amenities->fetch_assoc()) {
                                // Kiểm tra xem tiện ích này có được chọn trước đó không
                                $isChecked = (in_array($am['amenity_id'], $amenities)) ? 'checked' : '';
                                ?>
                                <div class="form-check d-flex align-items-center mb-2">
                                    <input class="form-check-input me-2" type="checkbox" name="amenity[]" 
                                           id="am<?php echo $am['amenity_id']; ?>" 
                                           value="<?php echo $am['amenity_id']; ?>" 
                                           <?php echo $isChecked; ?>>
                                    
                                    <label class="form-check-label small d-flex align-items-center" for="am<?php echo $am['amenity_id']; ?>">
                                        <?php if(!empty($am['icon_url'])): ?>
                                            <?php if(strpos($am['icon_url'], 'fa-') !== false || strpos($am['icon_url'], 'bi-') !== false): ?>
                                                <i class="<?php echo $am['icon_url']; ?> me-2 text-secondary"></i>
                                            <?php else: ?>
                                                <img src="<?php echo $base_url . 'src/assets/images/' . $am['icon_url']; ?>" class="me-2" width="16" height="16">
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                        <?php echo $am['name']; ?>
                                    </label>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-custom btn-sm fw-bold">
                            <i class="bi bi-search me-1"></i> Áp dụng lọc
                        </button>
                        <a href="listing.php?type=<?php echo $type_id; ?>" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Xóa bộ lọc
                        </a>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>

<script src="../assets/js/script.js"></script>

<?php include '../partials/footer.php'; ?>