<?php
//  KẾT NỐI BACKEND (CODE CỦA BẠN)
// Dùng __DIR__ để lấy đường dẫn hiện tại của file header.php
$auth_file = __DIR__ . '/../../../Backend/functions/auth.php';

if (file_exists($auth_file)) {
    require_once $auth_file;
} else {
    // Fallback: Nếu chưa có backend thật thì start session tạm để chạy giao diện
    if (session_status() === PHP_SESSION_NONE) session_start();
    // die("Lỗi: Không tìm thấy file auth.php..."); // Bỏ comment dòng này nếu muốn bắt buộc phải có file
}

// ĐỊNH NGHĨA ĐƯỜNG DẪN TÀI NGUYÊN (ASSETS)
$base_url = '/K&A/Frontend/'; 
$assets_path = $base_url . 'src/assets/'; // Trỏ vào folder chứa css/images

//  KIỂM TRA ĐĂNG NHẬP
$daDangNhap = isLoggedIn();

 $current_type = isset($_GET['type']) ? $_GET['type'] : '';

 // KHỞI TẠO MẢNG CHỨA ID CÁC PHÒNG ĐÃ THÍCH
$wishlist_ids = []; 
$wishlist_items = []; // Chứa thông tin chi tiết để hiện lên Dropdown

if (isset($_SESSION['user'])) {
    $uid = $_SESSION['user']['id'];
    
    // Lấy danh sách ID và Thông tin cơ bản để hiện lên Navbar
    $sql_fav = "SELECT f.room_id, r.title, r.price, 
                (SELECT photo_url FROM room_photos WHERE room_id = r.room_id LIMIT 1) as thumbnail
                FROM favourites f
                JOIN rooms r ON f.room_id = r.room_id
                WHERE f.user_id = $uid
                ORDER BY f.created_at DESC";
                
    $res_fav = $conn->query($sql_fav);
    if ($res_fav) {
        while ($row = $res_fav->fetch_assoc()) {
            $wishlist_ids[] = $row['room_id']; // Để kiểm tra tô đỏ nút tim
            $wishlist_items[] = $row;          // Để hiện lên Dropdown
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dịch vụ K&A</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?php echo $assets_path; ?>css/style.css?v=<?php echo time(); ?>"> 
    <link rel="stylesheet" media="screen and (max-width: 768px)" href="<?php echo $assets_path; ?>css/reponsivecss.css">
    <link rel="stylesheet" href="<?php echo $assets_path; ?>css/uploadroom.css">

    <link rel="icon" type="image/png" href="<?php echo $assets_path; ?>images/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

</head>
<body>
    
    <nav class="navbar navbar-expand-lg navbar-light fixed-top glass-nav">
        <div class="container-fluid">
            
            <a class="navbar-brand me-5" href="<?php echo $base_url; ?>/public/index.php">
                <img src="<?php echo $assets_path; ?>images/logo.png" alt="logo" style="height: 40px; width: auto"> 
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#centeredNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse w-100" id="centeredNav">
                
    <ul class="navbar-nav mx-auto mb-2 mb-lg-0 justify-content-center">
        <li class="nav-item me-4">
                <a class="nav-link text-dark fw-bold <?php echo ($current_type == 1) ? 'active' : ''; ?>" 
                href="<?php echo $base_url; ?>src/pages/listing.php?type=1">
                Kí Túc Xá
                </a>
            </li>
    
            <li class="nav-item me-4">
                <a class="nav-link text-dark fw-bold <?php echo ($current_type == 2) ? 'active' : ''; ?>" 
                href="<?php echo $base_url; ?>src/pages/listing.php?type=2">
                 Phòng trọ
                </a>
            </li>
    
            <li class="nav-item me-4">
                <a class="nav-link text-dark fw-bold <?php echo ($current_type == 3) ? 'active' : ''; ?>" 
                href="<?php echo $base_url; ?>src/pages/listing.php?type=3">
                Nguyên Căn
                </a>
            </li>
    
            <li class="nav-item me-4">
                <a class="nav-link text-dark fw-bold <?php echo ($current_type == 4) ? 'active' : ''; ?>" 
                href="<?php echo $base_url; ?>src/pages/listing.php?type=4">
                Chung Cư Mini
                </a>
            </li>

    </ul>
    
    <div class="d-flex align-items-center icon"> 

                    <a href="#" class="heart-icon fs-4 me-3 position-relative" id="favDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-heart-fill"></i>
                            <?php if(isset($wishlist_items) && count($wishlist_items) > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                            <?php echo count($wishlist_items); ?>
                        </span>
                            <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" 
                    aria-labelledby="favDropdown" style="width: 320px; max-height: 400px; overflow-y: auto; margin-top: 10px; margin-left:200px;">
        
        <li class="dropdown-header fw-bold border-bottom">Tin đã lưu</li>
        
        <?php if(isset($wishlist_items) && count($wishlist_items) > 0): ?>
            
            <?php foreach($wishlist_items as $item): 
                 // Xử lý ảnh nhỏ (tránh lỗi đường dẫn)
                 $thumb_fav = $assets_path . 'images/no-image.jpg';
                 if (!empty($item['thumbnail'])) {
                     $fname = basename($item['thumbnail']);
                     $thumb_fav = $base_url . 'src/uploads/' . $fname;
                 }
            ?>
            <li>
                <a class="dropdown-item d-flex align-items-center p-2 border-bottom" href="<?php echo $base_url; ?>src/pages/details.php?id=<?php echo $item['room_id']; ?>">
                    
                    <img src="<?php echo $thumb_fav; ?>" class="rounded me-3" width="50" height="50" style="object-fit: cover;">
                    
                    <div style="overflow: hidden;">
                        <div class="text-truncate fw-bold text-dark small" style="max-width: 200px;">
                            <?php echo $item['title']; ?>
                        </div>
                        <div class="text-danger small fw-bold">
                            <?php echo number_format($item['price'], 0, ',', '.'); ?>đ
                        </div>
                    </div>
                </a>
            </li>
            <?php endforeach; ?>
            
            <li>
                <a class="dropdown-item text-center small py-2 bg-light text-primary fw-bold" href="<?php echo $base_url; ?>src/pages/useraccount.php?tab=favorites">
                    Xem tất cả danh sách
                </a>
            </li>

        <?php else: ?>
            <li class="text-center p-4 text-muted">
                <i class="bi bi-heart-break fs-1 d-block mb-2"></i>
                Chưa có tin yêu thích
            </li>
        <?php endif; ?>
    </ul>

    </li>
                    
                    <div class="dropdown me-4 d-flex align-items-center">
                        <a href="#" class="text-secondary fs-4 position-relative" id="notiDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell-fill"></i>
                            
                            <span id="noti_badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                                  style="font-size: 0.6rem; display: none;">
                                0
                            </span>
                        </a>
                        
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" 
                            aria-labelledby="notiDropdown" 
                            id="noti_list"
                            style="width: 320px; max-height: 400px; overflow-y: auto;">
                            
                            <li><span class="dropdown-item text-center small text-muted py-3">Đang tải thông báo...</span></li>
                        </ul>
                    </div>
                    <?php if ($daDangNhap): ?>
    <div class="dropdown me-2">
        <button class="btn btn-custom px-3 rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person-fill"></i> <?php echo $_SESSION['user']['fullname']; ?>
        </button>
        <ul class="dropdown-menu">
            <li>
                <a class="dropdown-item" href="<?php echo $base_url; ?>src/pages/useraccount.php?tab=<?php echo (checkRole('owner')) ? 'myposts' : 'profile'; ?>">
                    Quản lý tài khoản
                </a>
            </li>

            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="<?php echo $base_url; ?>src/pages/admin_dashboard.php">
                        Quản lý trang web (ADMIN)
                    </a>
                </li>
            <?php endif; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?php echo $base_url; ?>public/logout.php">Đăng xuất</a></li>
        </ul>
    </div>
<?php else: ?>
    <a href="<?php echo $base_url; ?>src/pages/login.php">
        <button class="btn btn-custom me-2 px-3 text-nowrap rounded-pill">
            Đăng nhập <i class="bi bi-person-fill"></i>    
        </button> 
    </a>
<?php endif; ?>
                    
                    <button class="btn btn-custom me-2 px-3 text-nowrap rounded-pill" 
                        data-bs-toggle="modal" 
                        data-bs-target="<?php echo $daDangNhap ? '#postModal' : '#modalCanhBao'; ?>">
                        Đăng tin <i class="bi bi-pencil-square ms-1"></i>
                    </button>
                    
                </div>
            </div>
        </div>       
    </nav>

    <?php if ($daDangNhap): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Hàm lấy dữ liệu thông báo
            function fetchNotifications() {
                // Đường dẫn API (Đảm bảo đúng đường dẫn file bạn đã tạo ở bước trước)
                fetch('/K&A/Backend/api/get_notifications.php')
                    .then(response => response.json())
                    .then(data => {
                        // 1. Cập nhật số trên chuông
                        const badge = document.getElementById('noti_badge');
                        const list = document.getElementById('noti_list');

                        if (data.count > 0) {
                            badge.style.display = 'block'; // Hiện badge
                            badge.innerText = data.count > 99 ? '99+' : data.count; // Gán số (max 99+)
                        } else {
                            badge.style.display = 'none';  // Ẩn badge nếu không có tin
                        }

                        // Cập nhật danh sách thả xuống
                        list.innerHTML = data.html;
                    })
                    .catch(error => console.error('Lỗi lấy thông báo:', error));
            }

            // Gọi ngay khi tải trang
            fetchNotifications();

            // Cập nhật định kỳ 3 giây/lần
            setInterval(fetchNotifications, 3000);
        });
    </script>
    <?php endif; ?>

</body>
</html>