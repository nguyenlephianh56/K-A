<?php
//KẾT NỐI VÀ HEADER
require_once '../../../Backend/config/db_connect.php';
// Header nằm cùng thư mục public thì dùng require_once 'header.php'
require_once '../partials/header.php'; 

//LẤY ID TỪ URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='container mt-5 alert alert-danger'>Không tìm thấy tin đăng này.</div>";
    require_once '../partials/footer.php';
    exit();
}

$room_id = intval($_GET['id']);

//TRUY VẤN THÔNG TIN CHI TIẾT PHÒNG
$sql_detail = "SELECT r.*, rt.name AS type_name, u.full_name, u.phone
               FROM rooms r
               JOIN room_types rt ON r.room_type_id = rt.room_type_id
               JOIN users u ON r.owner_id = u.user_id
               WHERE r.room_id = ?";

$stmt = $conn->prepare($sql_detail);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result_detail = $stmt->get_result();

if ($result_detail->num_rows == 0) {
    echo "<div class='container mt-5 alert alert-warning'>Tin đăng không tồn tại hoặc đã bị xóa.</div>";
    require_once 'footer.php';
    exit();
}

$room = $result_detail->fetch_assoc();

//TRUY VẤN HÌNH ẢNH PHÒNG
$sql_photos = "SELECT photo_url FROM room_photos WHERE room_id = ?";
$stmt_photos = $conn->prepare($sql_photos);
$stmt_photos->bind_param("i", $room_id);
$stmt_photos->execute();
$result_photos = $stmt_photos->get_result();
$photos = [];
while ($row_photo = $result_photos->fetch_assoc()) {
    $photos[] = $row_photo['photo_url'];
}

// Nếu không có ảnh, thêm ảnh mặc định
if (empty($photos)) {
    $photos[] = $assets_path . 'images/no-image.jpg';
}

//TRUY VẤN TIỆN ÍCH
$sql_amenities = "SELECT a.name, a.icon_url 
                  FROM amenities a
                  JOIN room_amenities ra ON a.amenity_id = ra.amenity_id
                  WHERE ra.room_id = ?";
$stmt_amenities = $conn->prepare($sql_amenities);
$stmt_amenities->bind_param("i", $room_id);
$stmt_amenities->execute();
$result_amenities = $stmt_amenities->get_result();
$amenities = [];
while ($row_amenity = $result_amenities->fetch_assoc()) {
    $amenities[] = $row_amenity;
}

// Xử lý dữ liệu hiển thị
$price_formatted = number_format($room['price'], 0, ',', '.') . ' VNĐ';
$created_date = date('d/m/Y', strtotime($room['created_at']));
$address_full = $room['street'] . ', ' . $room['ward'] . ', ' . $room['city'];

// Hàm xử lý link ảnh (Fix lỗi đường dẫn)
function get_image_url($path, $base_url) {
    if (strpos($path, 'http') === 0) return $path;
    $filename = basename($path); 
    // Trỏ về src/uploads/
    return $base_url . 'src/uploads/' . $filename; 
}
?>

<div class="container main-content product-details-container" style="margin-top: 100px;">
    
    <div class="row mb-3 align-items-center border-bottom pb-3">
        <div class="col-lg-9 col-md-8">
            <small class="text-muted">
                <?php if($room['status'] == 'available'): ?>
                    <span class="badge bg-success me-2 shadow-sm">Còn phòng</span>
                <?php else: ?>
                    <span class="badge bg-secondary me-2 shadow-sm">Đã thuê</span>
                <?php endif; ?>
                
                <a href="listing.php?type=<?php echo $room['room_type_id']; ?>" class="text-secondary text-decoration-none">
                    <?php echo $room['type_name']; ?>
                </a> / 
                <span class="text-secondary"><?php echo $room['city']; ?></span>
            </small>
            <h2 class="mt-2 fw-bold text-dark text-uppercase"><?php echo $room['title']; ?></h2>
            <p class="text-muted small mb-0">
                <i class="bi bi-geo-alt me-1"></i> <?php echo $address_full; ?>
            </p>
        </div>
       
    </div>

    <div class="row g-3">
        
        <div class="col-lg-9 col-md-12">
            
            <div id="productMainCarousel" class="carousel slide shadow-sm" data-bs-ride="carousel">
                <div class="carousel-inner rounded-3 main-gallery-container bg-dark">
                    <?php 
                    $first = true;
                    foreach ($photos as $photo): 
                        $img_src = get_image_url($photo, $base_url);
                        $active_class = $first ? 'active' : '';
                        $first = false;
                    ?>
                    <div class="carousel-item <?php echo $active_class; ?>" style="height: 500px;">
                        <img src="<?php echo $img_src; ?>" class="d-block w-100 h-100" style="object-fit: contain;" alt="Ảnh chi tiết">
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if(count($photos) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#productMainCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Trước</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productMainCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Sau</span>
                </button>
                <?php endif; ?>
            </div>
            
            <?php if(count($photos) > 1): ?>
            <div class="d-flex justify-content-start mt-3 overflow-auto thumbnail-gallery pb-2">
                <?php 
                $i = 0;
                foreach ($photos as $photo): 
                    $img_src = get_image_url($photo, $base_url);
                    $active_class = ($i == 0) ? 'border-primary border-2' : '';
                ?>
                <img src="<?php echo $img_src; ?>" 
                     data-bs-target="#productMainCarousel" 
                     data-bs-slide-to="<?php echo $i; ?>" 
                     class="thumbnail-item rounded-3 me-2 border <?php echo $active_class; ?>" 
                     alt="Thumbnail <?php echo $i; ?>"
                     style="width: 100px; height: 70px; object-fit: cover; cursor: pointer;">
                <?php 
                $i++;
                endforeach; 
                ?>
            </div>
            <?php endif; ?>

             <div class="card p-4 mt-4 shadow-sm border-0">
                <h4 class="fw-bold mb-3 border-bottom pb-2">Thông tin chi tiết</h4>
                <div class="product-description-content">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong class="text-secondary">Mức giá:</strong> <span class="text-danger fw-bold fs-5"><?php echo $price_formatted; ?>/tháng</span></p>
                            <p class="mb-2"><strong class="text-secondary">Diện tích:</strong> <?php echo $room['area']; ?> m²</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong class="text-secondary">Loại phòng:</strong> <?php echo $room['type_name']; ?></p>
                            <p class="mb-2"><strong class="text-secondary">Ngày đăng:</strong> <?php echo $created_date; ?></p>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mt-4">Mô tả chi tiết</h5>
                    <div class="text-break" style="white-space: pre-line;">
                        <?php echo $room['description']; ?>
                    </div>
                    
                    <hr>
                    
                    <?php if (!empty($amenities)): ?>
                    <h6 class="fw-bold mt-4 mb-3">Tiện ích phòng</h6>
                    <div class="row row-cols-2 row-cols-md-3 g-3">
                        <?php foreach ($amenities as $am): 
                             // Xử lý icon
                             $icon_src = '';
                             if (!empty($am['icon_url'])) {
                                 if(strpos($am['icon_url'], 'fa-') !== false || strpos($am['icon_url'], 'bi-') !== false) {
                                     $icon_html = "<i class='{$am['icon_url']} text-success me-2'></i>";
                                 } else {
                                     $icon_name = basename($am['icon_url']);
                                     $src = $assets_path . 'images/' . $icon_name;
                                     $icon_html = "<img src='$src' class='me-2' width='20' height='20'>";
                                 }
                             } else {
                                 $icon_html = "<i class='bi bi-check-circle-fill text-success me-2'></i>";
                             }
                        ?>
                        <div class="col d-flex align-items-center">
                            <?php echo $icon_html; ?>
                            <span><?php echo $am['name']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                </div>
            </div>
            
        </div>
        
        <div class="col-lg-3 col-md-12">
            <div class="card p-3 shadow-sm sticky-top border-0" style="top: 100px;">
                <div class="text-center mb-3">
                    
                    <h6 class="mt-2 fw-bold"><?php echo $room['full_name']; ?></h6>
                    <small class="text-muted">Đã tham gia: <?php echo date('m/Y', strtotime($room['created_at'])); ?></small>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-custom btn-lg fw-bold show-phone-btn" data-phone="<?php echo $room['phone']; ?>">
                        <i class="bi bi-telephone-fill me-2"></i> 
                        <span class="phone-text">Bấm để hiện số</span>
                    </button>
                    <a href="https://zalo.me/<?php echo $room['phone']; ?>" target="_blank" class="btn btn-outline-primary fw-bold d-flex align-items-center justify-content-center">
                        <img src="<?php echo $assets_path; ?>images/zalo_icon.png" alt="Zalo" style="width: 24px; height: 24px; object-fit: contain;" class="me-2">
                        Chat Zalo
                    </a>
                </div>
                
                <hr>
                <div class="small">
                    <p class="mb-2"><i class="bi bi-shield-check text-success me-1"></i> Tin đã được kiểm duyệt</p>
                    <p class="mb-2"><i class="bi bi-info-circle text-info me-1"></i> Vui lòng liên hệ chủ nhà để biết thêm chi tiết</p>
                    <p class="mb-2"><i class="bi bi-facebook text-primary"></i> Báo lỗi cho admin <strong>
                        <a href="https://www.facebook.com/Pnh2k6" target="_blank" style="text-decoration:none; color:#4A70A9">Tại đây</a></strong>
                </div>
            </div>
        </div>
    </div> 
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const phoneBtn = document.querySelector('.show-phone-btn');
        if(phoneBtn) {
            phoneBtn.addEventListener('click', function() {
                const phone = this.getAttribute('data-phone');
                const textSpan = this.querySelector('.phone-text');
                
                if (textSpan.innerText === 'Bấm để hiện số') {
                    textSpan.innerText = phone;
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-success');
                } else {
                    navigator.clipboard.writeText(phone);
                    alert('Đã sao chép số điện thoại: ' + phone);
                }
            });
        }
    });
</script>

<?php
//Truy vấn các phòng CÙNG LOẠI nhưng KHÁC ID hiện tại
$sql_related = "SELECT 
                    r.*, 
                    rt.name AS type_name,
                    (SELECT photo_url FROM room_photos WHERE room_id = r.room_id LIMIT 1) AS thumbnail
                FROM rooms r
                JOIN room_types rt ON r.room_type_id = rt.room_type_id
                WHERE r.room_type_id = ? 
                AND r.room_id != ? 
                AND r.status = 'available'
                ORDER BY r.created_at DESC 
                LIMIT 4"; // Lấy 4 tin mới nhất

$stmt_related = $conn->prepare($sql_related);
$stmt_related->bind_param("ii", $room['room_type_id'], $room_id);
$stmt_related->execute();
$result_related = $stmt_related->get_result();
?>

<?php if ($result_related->num_rows > 0): ?>
<div class="container mb-5">
    <hr class="my-5"> <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-uppercase mb-0" style="color: #4A70A9;">Tin đăng tương tự</h4>
        <a href="<?php echo $base_url; ?>src/pages/listing.php?type=<?php echo $room['room_type_id']; ?>" class="text-decoration-none fw-bold small">
            Xem tất cả <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php while ($row = $result_related->fetch_assoc()): ?>
            <?php 
                // --- XỬ LÝ ẢNH THUMBNAIL (GIỐNG INDEX.PHP) ---
                $thumb = $assets_path . 'images/no-image.jpg';
                if (!empty($row['thumbnail'])) {
                    // Logic cắt tên file và ghép đường dẫn chuẩn
                    $filename = basename($row['thumbnail']);
                    $thumb = $base_url . 'src/uploads/' . $filename;
                }
                $project_root = '';
                // --- GỌI COMPONENT CARD DỌC ---              
                include '../component/product-card.php'; 
            ?>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<?php include '../partials/footer.php'; ?>

<script src="../assets/js/script.js"></script>