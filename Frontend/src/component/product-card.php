<div class="col"> 
     <div class="card product-card-custom shadow-sm h-100">
     
        <?php //hiển thị ảnh của phòng trọ?>

        <div class="card-img-top-container position-relative">
            <a href="<?php echo $base_url; ?>src/pages/details.php?id=<?php echo $row['room_id']; ?>"> 
                <img src="<?php echo $project_root . $thumb; ?>" class="card-img-top img-fluid" alt="<?php echo $row['title']; ?>" style="height: 200px; object-fit: cover;">
            </a> 
            
            
            <?php if($row['status'] == 'occupied'): ?>
                <span class="position-absolute top-0 start-0 badge bg-danger m-2">Đã thuê</span>
            <?php endif; ?>
        </div>

        <div class="card-body">
            <h5 class="card-title product-title text-truncate">
                <a href="<?php echo $base_url; ?>src/pages/details.php?id=<?php echo $row['room_id']; ?>" class="text-decoration-none text-dark">
                    <?php echo $row['title']; ?>
                </a>
            </h5>

            <div class="d-flex justify-content-between align-items-center mt-1">
                <div class="price-container">
                    <p class="current-price mb-0 text-danger fw-bold">
                        <?php echo number_format($row['price'], 0, ',', '.'); ?>₫
                    </p>

                    <p class="location-info text-muted mb-0 mt-1 small text-truncate" style="max-width: 150px;">
                        <i class="bi bi-geo-alt-fill"></i> 
                        <?php echo $row['ward']; ?>, <?php echo $row['city']; ?>
                    </p>

                    <a href="#" class="details badge bg-light text-dark border mt-1 text-decoration-none">
                        <?php echo $row['type_name']; ?>
                    </a> 
                </div>

               <?php
            // Logic kiểm tra đã tim chưa
            $is_liked = (isset($wishlist_ids) && in_array($row['room_id'], $wishlist_ids));
            // Nếu nằm cạnh chữ (nền trắng) thì tim rỗng nên màu xám (text-secondary) hoặc đen
            $heart_icon = $is_liked ? 'bi-heart-fill' : 'bi-heart';
            $heart_color = $is_liked ? 'text-danger' : 'text-secondary';
        ?>
        <button class="btn btn-sm btn-toggle-fav p-0 ms-2 border-0" 
                type="button" 
                data-id="<?php echo $row['room_id']; ?>"
                style="background: transparent;">
            <i class="bi <?php echo $heart_icon; ?> fs-5 <?php echo $heart_color; ?>"></i>
        </button>
            </div>
        </div>
        
    </div>
</div>