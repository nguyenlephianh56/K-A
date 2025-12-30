<?php 
include '../../Backend/config/db_connect.php'; 
include '../src/partials/header.php';
?>

<section class="hero-section mt-5">
    <div class="hero-banner d-flex justify-content-center align-items-center">
        <img src="<?php echo $assets_path; ?>images/banner1.png" alt="banner-chinh">
    </div>
</section>

<div class="container my-5 sticky-top" style="top: 62px; z-index: 900;">
    <div class="card card-advance-search glass-search-card shadow-sm p-4">
        <form class="row g-3 align-items-center" action="../src/pages/find.php" method="GET">
            
            <div class="col-md-4 col-sm-12">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="keyword" class="form-control border-0" placeholder="Tìm tên phòng, tên đường..." aria-label="Tìm kiếm">
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-geo-alt"></i></span>
                    <select name="district" class="form-select border-0" style="cursor: pointer;">
                        <option selected value="">Tất cả khu vực</option>
                        <option value="Hải Châu">Q. Hải Châu</option>
                        <option value="Thanh Khê">Q. Thanh Khê</option>
                        <option value="Sơn Trà">Q. Sơn Trà</option>
                        <option value="Ngũ Hành Sơn">Q. Ngũ Hành Sơn</option>
                        <option value="Liên Chiểu">Q. Liên Chiểu</option>
                        <option value="Cẩm Lệ">Q. Cẩm Lệ</option>
                        <option value="Hòa Vang">H. Hòa Vang</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6">
                <div class="input-group input-group-lg">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-building"></i></span>
                    <select name="type" class="form-select border-0" style="cursor: pointer;">
                        <option selected value="">Tất cả loại hình</option>
                        <option value="1">Kí Túc Xá</option>
                        <option value="2">Phòng trọ</option>
                        <option value="3">Nguyên Căn</option>
                        <option value="4">Chung Cư Mini</option>
                    </select>
                </div>
            </div>
            
            <div class="col-md-2 col-sm-12">
                <button class="btn btn-custom btn-lg w-100" type="submit">
                    Tìm nhà
                </button>
            </div>
        </form>
    </div>
</div>

<div id="multiItemCarousel" class="carousel slide" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#multiItemCarousel" data-bs-slide-to="0" class="active"></button>
    <button type="button" data-bs-target="#multiItemCarousel" data-bs-slide-to="1"></button>
  </div>

  <div class="carousel-inner">
    <div class="carousel-item active">
      <div class="row g-3"> 
        <div class="col-12 col-md-4 shine-banner"><img src="<?php echo $assets_path; ?>images/banner 2.png" class="d-block w-100"></div>
        <div class="col-12 col-md-4 shine-banner"><img src="<?php echo $assets_path; ?>images/banner3.png" class="d-block w-100"></div>
        <div class="col-12 col-md-4 shine-banner"><img src="<?php echo $assets_path; ?>images/banner4.png" class="d-block w-100"></div>
      </div>
    </div>
    <div class="carousel-item">
      <div class="row g-3">
        <div class="col-12 col-md-4 shine-banner"><img src="<?php echo $assets_path; ?>images/banner5.png" class="d-block w-100"></div>
        <div class="col-12 col-md-4 shine-banner"><img src="<?php echo $assets_path; ?>images/banner3.png" class="d-block w-100"></div>
        <div class="col-12 col-md-4 shine-banner"><img src="<?php echo $assets_path; ?>images/banner6.png" class="d-block w-100"></div>
      </div>
    </div>
  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#multiItemCarousel" data-bs-slide-to="prev">
    <span class="carousel-control-prev-icon"></span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#multiItemCarousel" data-bs-slide-to="next">
    <span class="carousel-control-next-icon"></span>
  </button>
</div>

<!-- xây dựng lựa chọn gợi ý 
 (nếu user là student thì gợi ý trường gần với trường của user, nếu user là khách/owner thì đưa ra các lựa chọn mới được up lên hệ thống) -->
<section class="hot_pick my-4 pt-4 border" style="background-color:#8FABD4"> 
    <div class="container">
        
        <?php
        // KIỂM TRA TRƯỜNG ĐẠI HỌC CỦA USER
        // Mặc định là không có trường (0)
        $my_university_id = 0; 
        $section_title = "Lựa chọn mới nhất"; // Tiêu đề mặc định

        if (isset($_SESSION['user']['id'])) {
            $user_id = $_SESSION['user']['id'];
            // Truy vấn lại DB để lấy university_id mới nhất (cho chắc ăn)
            $sql_user = "SELECT university_id FROM users WHERE user_id = $user_id";
            $res_user = $conn->query($sql_user);
            if ($res_user && $u_data = $res_user->fetch_assoc()) {
                $my_university_id = $u_data['university_id'];
            }
        }

        // XÂY DỰNG CÂU SQL DỰA TRÊN KẾT QUẢ KIỂM TRA
        if (!empty($my_university_id)) {
            // --- TRƯỜNG HỢP A: USER CÓ TRƯỜNG ĐH -> LỌC THEO TRƯỜNG ---
            $section_title = "Gợi ý gần trường bạn";
            
            $sql = "SELECT 
                        r.*, 
                        rt.name AS type_name,
                        (SELECT photo_url FROM room_photos WHERE room_id = r.room_id LIMIT 1) AS thumbnail
                    FROM rooms r
                    JOIN room_types rt ON r.room_type_id = rt.room_type_id
                    JOIN room_nearby_universities rnu ON r.room_id = rnu.room_id
                    WHERE rnu.university_id = ? 
                    AND r.status = 'available'
                    ORDER BY r.created_at DESC 
                    LIMIT 8";
            
            // Chuẩn bị statement
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $my_university_id);
            $stmt->execute();
            $result = $stmt->get_result();

        } else {
            // --- TRƯỜNG HỢP B: KHÔNG CÓ TRƯỜNG HOẶC CHƯA LOGIN -> LẤY MỚI NHẤT ---
            
            $sql = "SELECT 
                        r.*, 
                        rt.name AS type_name,
                        (SELECT photo_url FROM room_photos WHERE room_id = r.room_id LIMIT 1) AS thumbnail
                    FROM rooms r
                    JOIN room_types rt ON r.room_type_id = rt.room_type_id
                    WHERE r.status = 'available'
                    ORDER BY r.created_at DESC 
                    LIMIT 8";
            
            $result = $conn->query($sql);
        }
        ?>

        <div class="text-center mb-4 col-12">
            <h2 class="text-black fw-bold">
                <?php echo $section_title; ?> 
                <i class="bi bi-fire" style="color: red;"></i>
            </h2>    
            <?php if(!empty($my_university_id)): ?>
                <p class="text-white small fst-italic">(Dựa trên hồ sơ học tập của bạn)</p>
            <?php endif; ?>
        </div>
        
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 gx-2 gy-3 justify-content-center pb-5 mt-3">
            <?php
            // Biến này để fix lỗi component (nếu component cần)
            $project_root = ''; 

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    
                    // XỬ LÝ ẢNH
                    $thumb = $assets_path . 'images/no-image.jpg'; // Ảnh mặc định
                    
                    if (!empty($row['thumbnail'])) {
                        // Logic lấy tên file chuẩn (dùng basename để tránh lỗi lặp đường dẫn)
                        $filename = basename($row['thumbnail']);
                        // Trỏ về đúng thư mục chứa ảnh thật (src/uploads)
                        $thumb = $base_url . 'src/uploads/' . $filename; 
                    }

                    // GỌI COMPONENT                 
                    include '../src/component/product-card.php'; 
                }
            } else {
                echo "<div class='col-12 text-center text-white py-5'><h5>Chưa có tin đăng nào phù hợp.</h5></div>";
            }
            ?>
        </div>

    </div>
</section>

<section class="bannerbig2">
    <div class="hero-banner2 shine-banner">
        <img src="<?php echo $assets_path; ?>images/bannerbig2.png" alt="Quảng cáo lớn">
    </div>
</section>


<!-- danh muc phong tro -->
<section class="phong_tro my-4 pt-4 border"> 
    
  <div class="container">
        <!-- text -->
        <div class="mb-4 col-12">
            <h2 class="text-primary fw-bold">Phòng trọ <i class="bi bi-house-fill" style="color: red;"></i></h2>    
        </div>
        
        
<div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 gx-2 gy-3 justify-content-center pb-5 mt-3">
    
    <?php
    //lọc phòng trọ theo type_id = 1
    $sql = "SELECT 
            r.*, 
            rt.name AS type_name,
            (SELECT photo_url FROM room_photos WHERE room_id = r.room_id LIMIT 1) AS thumbnail
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.room_type_id
        WHERE r.room_type_id = 2
        ORDER BY r.created_at DESC 
        LIMIT 8";

    $result = $conn->query($sql);
    $project_root = '/K&A/'; // Thay đổi theo tên thư mục gốc của bạn
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            
            // XỬ LÝ ẢNH
            // Vì ta đã lấy được link ảnh qua câu SQL (cột 'thumbnail') nên không cần json_decode nữa
            if (!empty($row['thumbnail'])) {
                // Nếu trong CSDL bạn lưu đường dẫn đầy đủ thì dùng luôn
                // Nếu chỉ lưu tên file thì nối thêm $assets_path
                $thumb = $row['thumbnail']; 
            } else {
                $thumb = $assets_path . 'images/no-image.jpg';
            }

            // Gọi Component hiển thị
            // Lưu ý: Đường dẫn tùy thuộc vị trí file index của bạn
            include '../src/component/product-card.php'; 
        }
    } else {
        echo "<div class='col-12 text-center text-muted'>Chưa có tin đăng nào.</div>";
    }
    ?>

</div>
</section>

<!-- cac phuong cua da nang cua da nang -->
<section class="outstanding-cities py-5 bg-white">
    <div class="container">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="chu_tieu_de fw-bold text-uppercase mb-0">Phường ở Đà Nẵng</h3>
            
            </div>

        <div id="wardCarousel" class="carousel carousel-dark slide" data-bs-ride="carousel">
            
            <div class="carousel-inner">
                
                <!-- 1 hang carousel -->
                <div class="carousel-item active">
                    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
                        
                        <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/anhai.jpg" alt="An Hải" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">An Hải</h6>
                                </div>
                            </a>
                        </div>
                        
                            <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/ankhe.jpg" alt="An Khê" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">An Khê</h6>
                                </div>
                            </a>
                        </div>

                            <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/thanhkhe.jpg" alt="Thanh Khê" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Thanh Khê</h6>
                                </div>
                            </a>
                        </div>

                            <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/haichau.jpg" alt="Hải Châu" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Hải Châu</h6>
                                </div>
                            </a>
                        </div>

                            <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/camle.jpeg" alt="Cẩm Lệ" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Cẩm Lệ</h6>
                                </div>
                            </a>
                        </div>

                            <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/bana.jpg" alt="Bà Nà" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Bà Nà</h6>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>

                <div class="carousel-item">
                    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
                        
                            <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/haivan.jpg" alt="Hải Vân" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Hải Vân</h6>
                                </div>
                            </a>
                        </div>

                           <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/hoacuong.jpg" alt="Hòa Cường" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Hòa Cường</h6>
                                </div>
                            </a>
                        </div>

                            <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/hoakhanh.webp" alt="Hòa Khánh" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Hòa Khánh</h6>
                                </div>
                            </a>
                        </div>

                            <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/hoatien.jpg" alt="Hòa tiến" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Hòa Tiến</h6>
                                </div>
                            </a>
                        </div>

                            <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/hoavang.jpg" alt="Hòa Vang" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Hòa Vang</h6>
                                </div>
                            </a>
                        </div>

                            <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/hoaxuan.jpg" alt="Hòa Xuân" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Hòa Xuân</h6>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>

                <div class="carousel-item">
                    <div class="row row-cols-2 row-cols-md-3 row-cols-lg-6 g-3">
                        
                           <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/lienchieu.jpg" alt="Liên Chiểu" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Liên Chiểu</h6>
                                </div>
                            </a>
                        </div>

                            <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/nguhanhson.jpg" alt="5hanhson" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Ngũ Hành Sơn</h6>
                                </div>
                            </a>
                        </div>
                        

                            <div class="col">
                            <a href="#" class="ward-card shadow-sm d-block text-decoration-none">
                                <div class="ward-img-container">
                                    <img src="..//src/assets/images/sontra.jpg" alt="HCM" class="img-fluid w-100">
                                </div>
                                <div class="ward-info p-2 bg-white text-center">
                                    <h6 class="fw-bold text-dark mb-0 small">Sơn Trà</h6>
                                </div>
                            </a>
                        </div>

                    </div>
                </div>

            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#wardCarousel" data-bs-slide="prev" style="width: 5%; margin-left: -5%;">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#wardCarousel" data-bs-slide="next" style="width: 5%; margin-right: -5%;">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

    </div>
</section>

<!-- danh muc phong nguyên căn -->
<section class="nha_nguyen_can my-4 pt-4 border"> 
    
  <div class="container">
        <!-- text -->
        <div class="mb-4 col-12">
            <h2 class="text-primary fw-bold">Nguyên căn <i class="bi bi-house-heart-fill" style="color: red;"></i></h2>    
        </div>
        
        
<div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 gx-2 gy-3 justify-content-center pb-5 mt-3">
    
    <?php
    //lọc nguyên căn theo type_id = 3
    $sql = "SELECT 
            r.*, 
            rt.name AS type_name,
            (SELECT photo_url FROM room_photos WHERE room_id = r.room_id LIMIT 1) AS thumbnail
        FROM rooms r
        JOIN room_types rt ON r.room_type_id = rt.room_type_id
        WHERE r.room_type_id = 3
        ORDER BY r.created_at DESC 
        LIMIT 8";

    $result = $conn->query($sql);
    $project_root = '/K&A/'; // Thay đổi theo tên thư mục gốc của bạn
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            
            // XỬ LÝ ẢNH
            // Vì ta đã lấy được link ảnh qua câu SQL (cột 'thumbnail') nên không cần json_decode nữa
            if (!empty($row['thumbnail'])) {
                // Nếu trong CSDL lưu đường dẫn đầy đủ thì dùng luôn
                // Nếu chỉ lưu tên file thì nối thêm $assets_path
                $thumb = $row['thumbnail']; 
            } else {
                $thumb = $assets_path . 'images/no-image.jpg';
            }

            // Gọi Component hiển thị
            include '../src/component/product-card.php'; 
        }
    } else {
        echo "<div class='col-12 text-center text-muted'>Chưa có tin đăng nào.</div>";
    }
    ?>

</div>
</section>


<script src="../src/assets/js/script.js"></script>
<?php include '../src/partials/footer.php'; ?>