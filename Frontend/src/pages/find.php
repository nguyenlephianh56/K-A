<?php 
// NHÚNG FILE XỬ LÝ (Đường dẫn lùi 3 cấp ra thư mục gốc K&A)
include '../../../Backend/functions/find_process.php'; 

// Nhúng Header
require_once '../partials/header.php'; 

// Xử lý biến hiển thị mặc định để tránh lỗi Undefined
$keyword_show = isset($keyword) ? htmlspecialchars($keyword) : '';
$district_show = isset($district) ? htmlspecialchars($district) : '';
$total_show = isset($total_records) ? $total_records : 0;
?>

<div class="container" style="margin-top: 80px;">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo isset($base_url) ? $base_url : '/'; ?>public/index.php" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Tìm kiếm phòng trọ</li>
        </ol>
    </nav>
</div>

<div class="container mb-4">
    <h4 class="fw-bold text-uppercase text-primary">KẾT QUẢ TÌM KIẾM</h4>
    <p class="text-muted">
        <?php if(!empty($keyword_show)): ?>
            Từ khóa: "<strong><?php echo $keyword_show; ?></strong>"
        <?php endif; ?>
        
        <?php if(!empty($district_show)): ?>
            <?php echo (!empty($keyword_show) ? '- ' : ''); ?>Khu vực: "<strong><?php echo $district_show; ?></strong>"
        <?php endif; ?>

        <br>
        Tìm thấy <span class="fw-bold text-dark"><?php echo $total_show; ?></span> kết quả phù hợp.
    </p>
</div>

<div class="container mb-5">
    <div class="row">
        
        <div class="col-lg-9 col-12">
            
            <div class="d-flex justify-content-end mb-3">
                <div class="d-flex align-items-center">
                    <span class="me-2 small text-muted">Sắp xếp theo:</span>
                    <select class="form-select form-select-sm" style="width: 170px;" 
                        onchange="this.form.submit()" form="filterForm" name="sort">
                        <option value="newest" <?php echo ($sort_option == 'newest') ? 'selected' : ''; ?>>Mới nhất</option>
                        <option value="price_asc" <?php echo ($sort_option == 'price_asc') ? 'selected' : ''; ?>>Giá thấp đến cao</option>
                        <option value="price_desc" <?php echo ($sort_option == 'price_desc') ? 'selected' : ''; ?>>Giá cao đến thấp</option>
                        <option value="area_desc" <?php echo ($sort_option == 'area_desc') ? 'selected' : ''; ?>>Diện tích lớn nhất</option>
                    </select>
                </div>
            </div>

            <?php
            // Kiểm tra biến result trước khi lặp
            if (isset($result) && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    include '../component/product-card-long.php';
                }
            } else {
                echo "<div class='alert alert-warning text-center'>
                        <i class='bi bi-search'></i> Không tìm thấy phòng trọ nào với tiêu chí trên.
                      </div>";
            }
            ?>

            <?php if (isset($total_pages) && $total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?keyword=<?php echo $keyword; ?>&district=<?php echo $district; ?>&page=<?php echo $current_page - 1; ?>&sort=<?php echo $sort_option; ?>">Trước</a>
                    </li>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                      <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?keyword=<?php echo $keyword; ?>&district=<?php echo $district; ?>&page=<?php echo $i; ?>&sort=<?php echo $sort_option; ?>">
                            <?php echo $i; ?>
                         </a>
                    </li>
                    <?php endfor; ?>

                    <li class="page-item <?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?keyword=<?php echo $keyword; ?>&district=<?php echo $district; ?>&page=<?php echo $current_page + 1; ?>&sort=<?php echo $sort_option; ?>">Sau</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

        </div>

        <div class="col-lg-3 col-12">
            <div class="card border-0 shadow-sm p-3 sticky-top" style="top: 80px; z-index: 1;">
                <div class="d-flex align-items-center mb-3 "style="color: #4A70A9">
                    <i class="bi bi-search me-2"></i>
                    <h6 class="fw-bold mb-0 text-uppercase">Bộ lọc tìm kiếm</h6>
                </div>

                <form action="find.php" method="GET" id="filterForm">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Từ khóa</label>
                        <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Nhập tên đường, phòng..." value="<?php echo $keyword_show; ?>">
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Khu vực</label>
                        <select name="district" class="form-select form-select-sm">
                            <option value="">-- Tất cả --</option>
                            <option value="Hải Châu" <?php if($district == 'Hải Châu') echo 'selected'; ?>>Hải Châu</option>
                            <option value="Thanh Khê" <?php if($district == 'Thanh Khê') echo 'selected'; ?>>Thanh Khê</option>
                            <option value="Liên Chiểu" <?php if($district == 'Liên Chiểu') echo 'selected'; ?>>Liên Chiểu</option>
                            <option value="Sơn Trà" <?php if($district == 'Sơn Trà') echo 'selected'; ?>>Sơn Trà</option>
                            <option value="Ngũ Hành Sơn" <?php if($district == 'Ngũ Hành Sơn') echo 'selected'; ?>>Ngũ Hành Sơn</option>
                            <option value="Cẩm Lệ" <?php if($district == 'Cẩm Lệ') echo 'selected'; ?>>Cẩm Lệ</option>
                        </select>
                    </div>

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
                        if (isset($res_all_amenities) && $res_all_amenities && $res_all_amenities->num_rows > 0) {
                            $res_all_amenities->data_seek(0); 
                            while($am = $res_all_amenities->fetch_assoc()) {
                                $isChecked = (is_array($amenities) && in_array($am['amenity_id'], $amenities)) ? 'checked' : '';
                                ?>
                                <div class="form-check d-flex align-items-center mb-2">
                                    <input class="form-check-input me-2" type="checkbox" name="amenity[]" 
                                           id="am<?php echo $am['amenity_id']; ?>" 
                                           value="<?php echo $am['amenity_id']; ?>" 
                                           <?php echo $isChecked; ?>>
                                    
                                    <label class="form-check-label small d-flex align-items-center" for="am<?php echo $am['amenity_id']; ?>">
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
                            <i class="bi bi-search me-1"></i> Tìm kiếm & Lọc
                        </button>
                        <a href="find.php" class="btn btn-outline-secondary btn-sm">
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