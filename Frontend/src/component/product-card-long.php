<?php
// Xử lý ảnh đại diện
// 1. Ảnh mặc định
$thumb = $assets_path . 'images/no-image.jpg'; 

// 2. Nếu có ảnh từ DB
if (!empty($row['thumbnail'])) {
    
    // lấy cái tên file cuối cùng (Vd: "06-1.jpg")
    //cắt bỏ hết mấy cái "Frontend/src/..." bị thừa đi
    $filename = basename($row['thumbnail']);    

    // Nên đường dẫn đúng chỉ cần là: "uploads/ten_file.jpg"
    $thumb = $base_url . 'src/uploads/' . $filename;
}
// Xử lý thời gian đăng (Vd: 2 ngày trước)
$time_ago = "Vừa xong";
if (isset($row['created_at'])) {
    $diff = time() - strtotime($row['created_at']);
    if ($diff < 60) $time_ago = "Vừa xong";
    else if ($diff < 3600) $time_ago = floor($diff/60) . " phút trước";
    else if ($diff < 86400) $time_ago = floor($diff/3600) . " giờ trước";
    else $time_ago = floor($diff/86400) . " ngày trước";
}
?>

<div class="card mb-3 room-item-card shadow-sm">
    <div class="row g-0">
        <!-- HÌNH ẢNH -->
        <div class="col-md-4 position-relative">
            <a href="<?php echo $base_url; ?>src/pages/details.php?id=<?php echo $row['room_id']; ?>">
                <img src="<?php echo $thumb; ?>" class="img-fluid rounded-start h-100 w-100 object-fit-cover" 
                alt="Hình phòng trọ" 
                style="object-fit: cover; object-position: center; position: absolute; top: 0; left: 0;"> 
                <!-- ép ảnh cho những ảnh dọc sẽ vừa với khung ảnh -->
            </a>
            
            <!-- Badge HOT (Ví dụ: nếu giá rẻ hoặc mới đăng) -->
            <span class="badge bg-danger position-absolute top-0 start-0 m-2 px-3 py-2">MỚI</span>
            <!-- nút yêu thích nè -->
           <div class="position-absolute bottom--10 end-0 m-1" style="z-index: 10;">
                <?php
                    // Logic kiểm tra đã tim chưa
                    $is_liked = (isset($wishlist_ids) && is_array($wishlist_ids) && in_array($row['room_id'], $wishlist_ids));
                    
                    // Nếu nằm cạnh chữ (nền trắng) thì tim rỗng nên màu xám (text-secondary) hoặc đen
                    $heart_icon = $is_liked ? 'bi-heart-fill' : 'bi-heart';
                    
                    // Sửa lại: Vì nằm trên ảnh nên dùng text-white cho nổi, text-secondary sẽ bị chìm
                    $heart_color = $is_liked ? 'text-danger' : 'text-white';
                ?>
                <button class="btn btn-sm btn-toggle-fav p-0 border-0" 
                        type="button" 
                        data-id="<?php echo $row['room_id']; ?>"
                        style="background: transparent;">
                    <i class="bi <?php echo $heart_icon; ?> fs-4 <?php echo $heart_color; ?>" style="text-shadow: 0 0 3px rgba(0,0,0,0.5);"></i>
                </button>
            </div>
        </div>

        <!-- THÔNG TIN -->
        <div class="col-md-8">
            <div class="card-body">
                <!-- Tiêu đề -->
                <h5 class="card-title fw-bold text-dark hover-title text-truncate">
                    <a href="<?php echo $base_url; ?>src/pages/details.php?id=<?php echo $row['room_id']; ?>" class="text-decoration-none text-dark">
                        <?php echo $row['title']; ?>
                    </a>
                </h5>

                <!-- Giá | Diện tích -->
                <div class="d-flex align-items-center gap-3 mb-2">
                    <h6 class="text-danger fw-bold mb-0"><?php echo number_format($row['price'], 0, ',', '.'); ?>₫/tháng</h6>
                    <span class="text-muted small">|</span>
                    <small class="text-muted"><?php echo $row['area']; ?> m²</small>
                </div>

                <!-- Địa chỉ -->
                <p class="card-text text-muted small mb-2 text-truncate">
                    <i class="bi bi-geo-alt-fill me-1 text-secondary"></i> 
                    <?php echo $row['ward']; ?>, <?php echo $row['city']; ?>
                </p>

                <!-- Mô tả ngắn -->
                <p class="card-text text-muted small description-truncate" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                    <?php echo $row['description']; ?>
                </p>

                <!-- Footer Card: Người đăng & Thời gian -->
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                    <div class="user-info d-flex align-items-center gap-2">
                        <small class="text-muted">Đăng bởi: <strong><?php echo $row['full_name']; ?></strong></small>
                    </div>
                    <small class="text-muted fst-italic"><?php echo $time_ago; ?></small>
                </div>
            </div>
        </div>
    </div>
</div>