<?php
session_start();
// Đảm bảo đường dẫn này đúng
require_once '../../../Backend/config/db_connect.php';

//CHẶN TRUY CẬP: Chỉ Admin mới được vào
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

//LẤY TAB HIỆN TẠI
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

// ============================================================
// HÀM XỬ LÝ ẢNH
// ============================================================
function get_image_path($path_from_db) {
    if (empty($path_from_db)) return 'https://via.placeholder.com/60?text=No+Img';
    if (strpos($path_from_db, 'http') === 0) return $path_from_db;
    return '../uploads/' . basename($path_from_db);
}

// THỐNG KÊ SỐ LIỆU
$sql_users = "SELECT COUNT(*) as total FROM users WHERE role != 'admin'"; 
$res_users = $conn->query($sql_users);
$total_users = $res_users->fetch_assoc()['total'];

$sql_posts = "SELECT COUNT(*) as total FROM rooms";
$res_posts = $conn->query($sql_posts);
$total_posts = $res_posts->fetch_assoc()['total'];

$total_reports = 0;
$check_report_table = $conn->query("SHOW TABLES LIKE 'reports'");
if($check_report_table && $check_report_table->num_rows > 0) {
    $sql_reports = "SELECT COUNT(DISTINCT room_id) as total FROM reports WHERE status = 'pending'";
    $res_reports = $conn->query($sql_reports);
    if ($res_reports) $total_reports = $res_reports->fetch_assoc()['total'];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Quản lý hệ thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="../../src/assets/images/favicon.png">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/style.css">

</head>
<body class="bg-light">

    <nav class="navbar navbar-dark shadow-sm mb-4" style="background-color: #8FABD4;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <img src="../../src/assets/images/logo_admin.png" alt="" style="height: 55px; width: 140%">
            </a>
            <a href="../../public/index.php" class="btn btn-sm btn-light fw-bold">Về trang chủ</a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row">
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body text-center py-4">
                        <?php 
                            $admin_db_avatar = $_SESSION['user']['avatar'] ?? '';
                            $admin_avatar = "../assets/images/admin_icon.png";
                        ?>
                        <img src="<?php echo $admin_avatar; ?>" class="rounded-circle mb-3 border border-3 p-1" 
                        width="100" height="100" 
                        style="object-fit:cover; border-color: #8FABD4 !important;">

                        <h5 class="fw-bold mb-0"><?php echo $_SESSION['user']['full_name'] ?? $_SESSION['user']['fullname']; ?></h5>
                        <small class="text-muted">Super Admin</small>
                    </div>
                    <div class="list-group list-group-flush admin-sidebar">
                        <a href="?tab=dashboard" class="list-group-item list-group-item-action <?php echo $tab == 'dashboard' ? 'active' : ''; ?>">
                            <i class="bi bi-grid-fill me-2"></i>Tổng quan
                        </a>
                        <a href="?tab=users" class="list-group-item list-group-item-action <?php echo $tab == 'users' ? 'active' : ''; ?>">
                            <i class="bi bi-people-fill me-2"></i>Quản lý Thành viên
                        </a>
                        <a href="?tab=posts" class="list-group-item list-group-item-action <?php echo $tab == 'posts' ? 'active' : ''; ?>">
                            <i class="bi bi-house-door-fill me-2"></i>Quản lý Tin đăng
                        </a>
                        <a href="?tab=amenities" class="list-group-item list-group-item-action <?php echo $tab == 'amenities' ? 'active' : ''; ?>">
                            <i class="bi bi-sliders me-2"></i>Quản lý Tiện ích
                        </a>
                        <a href="?tab=notifications" class="list-group-item list-group-item-action <?php echo $tab == 'notifications' ? 'active' : ''; ?>">
                            <i class="bi bi-bell-fill me-2"></i>Gửi thông báo
                        </a>
                        
                        <a href="../public/logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 col-md-8">
                
                <?php if ($tab == 'dashboard'): ?>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card text-white bg-primary shadow-sm h-100 border-0">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div><h6 class="card-title mb-0">Thành viên</h6><h2 class="my-2 fw-bold"><?php echo number_format($total_users); ?></h2></div>
                                    <i class="bi bi-people fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-success shadow-sm h-100 border-0">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div><h6 class="card-title mb-0">Tổng bài đăng</h6><h2 class="my-2 fw-bold"><?php echo number_format($total_posts); ?></h2></div>
                                    <i class="bi bi-collection-fill fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white bg-danger shadow-sm h-100 border-0">
                                <div class="card-body d-flex justify-content-between align-items-center">
                                    <div><h6 class="card-title mb-0">Tin bị báo cáo</h6><h2 class="my-2 fw-bold"><?php echo number_format($total_reports); ?></h2></div>
                                    <i class="bi bi-exclamation-triangle-fill fs-1 opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow-sm border-0">
                        <div class="card-header card-header-admin bg-white"><i class="bi bi-activity me-2"></i>Hoạt động</div>
                        <div class="card-body"><p class="text-muted text-center py-5">Chào mừng quay trở lại, Admin!</p></div>
                    </div>
                <?php endif; ?>

                <?php if ($tab == 'users'): ?>
                    <div class="card shadow-sm border-0">
                        <div class="card-header card-header-admin bg-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-people-fill me-2"></i>Danh sách người dùng</span>
                            <div class="input-group input-group-sm w-auto">
                                <input type="text" class="form-control" placeholder="Tìm kiếm...">
                                <button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr><th class="ps-3">ID</th><th>Người dùng</th><th>Email / SĐT</th><th>Vai trò</th><th>Hành động</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $current_admin_id = $_SESSION['user']['user_id'] ?? $_SESSION['user']['id'];
                                        $sql_user_list = "SELECT * FROM users WHERE user_id != $current_admin_id ORDER BY user_id DESC";
                                        $result_users = $conn->query($sql_user_list);

                                        if ($result_users && $result_users->num_rows > 0) {
                                            while ($row = $result_users->fetch_assoc()) {
                                                $role_badge = ($row['role'] == 'owner') ? 'bg-success bg-gradient text-white' : (($row['role'] == 'admin') ? 'bg-danger' : 'bg-secondary');
                                                $role_name = ($row['role'] == 'owner') ? 'Chủ trọ' : (($row['role'] == 'admin') ? 'Admin' : 'Người tìm phòng');
                                                $u_avatar = !empty($row['avatar']) ? get_image_path($row['avatar']) : 'https://ui-avatars.com/api/?name=' . urlencode($row['full_name']) . '&background=random';
                                        ?>
                                            <tr>
                                                <td class="ps-3">#<?php echo $row['user_id']; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo $u_avatar; ?>" class="rounded-circle me-2 avatar-small">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($row['username']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['email']); ?><br><small class="text-muted"><?php echo htmlspecialchars($row['phone'] ?? ''); ?></small></td>
                                                <td><span class="badge <?php echo $role_badge; ?>"><?php echo $role_name; ?></span></td>
                                                <td>
                                                    
                                                <button type="button" class="btn btn-sm btn-outline-primary edit-user-btn"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editUserModal"
                                                    data-id="<?php echo $row['user_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($row['full_name']); ?>"
                                                    data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                    data-phone="<?php echo htmlspecialchars($row['phone'] ?? ''); ?>"
                                                    data-role="<?php echo $row['role']; ?>"
                                                    data-username="<?php echo htmlspecialchars($row['username']); ?>"> <i class="bi bi-pencil"></i>
                                                </button>
                                                    <a href="../../../Backend/functions/delete_user.php?id=<?php echo $row['user_id']; ?>" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('CẢNH BÁO: Xóa người dùng này?');">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } } else { echo '<tr><td colspan="5" class="text-center py-4">Chưa có thành viên nào.</td></tr>'; } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tab == 'posts'): ?>
                    <div class="card shadow-sm border-0">
                        <div class="card-header card-header-admin bg-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-house-door-fill me-2"></i>Danh sách tin đăng</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr><th class="ps-3">Ảnh</th><th style="width: 30%">Tiêu đề</th><th>Giá / Diện tích</th><th>Người đăng</th><th>Trạng thái</th><th>Hành động</th></tr>
                                    </thead>
                                    
                                    <tbody id="post_data_container">
                                        <?php 
                                            $ajax_file_path = __DIR__ . '/../../../Backend/functions/get_rooms_list.php';
                                            if (file_exists($ajax_file_path)) {
                                                include $ajax_file_path; 
                                            } else {
                                                echo '<tr><td colspan="6" class="text-danger text-center p-3">Lỗi: Không tìm thấy file get_rooms_list.php</td></tr>';
                                            }
                                        ?>
                                    </tbody>
                                    
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tab == 'amenities'): ?>
                    <div class="row">
                        <div class="col-md-7 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header card-header-admin bg-white"><i class="bi bi-list-check me-2"></i>Danh sách Tiện ích</div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <?php
                                        $sql_am = "SELECT * FROM amenities ORDER BY amenity_id DESC"; 
                                        $res_am = $conn->query($sql_am);
                                        if ($res_am && $res_am->num_rows > 0) {
                                            while($am = $res_am->fetch_assoc()) {
                                                $icon_html = '';
                                                $path_to_images = '../../src/assets/images/'; 
                                                if (!empty($am['icon_url'])) {
                                                    if(strpos($am['icon_url'], 'fa-') !== false || strpos($am['icon_url'], 'bi-') !== false) {
                                                        $icon_html = "<i class='{$am['icon_url']} text-primary me-3 fs-5'></i>";
                                                    } else {
                                                        if (strpos($am['icon_url'], 'http') === 0) {
                                                            $src = $am['icon_url'];
                                                        } else {
                                                            $icon_name = basename($am['icon_url']);
                                                            $src = $path_to_images . $icon_name;
                                                        }
                                                        $icon_html = "<img src='$src' class='me-3' width='24' height='24' style='object-fit: contain;'>";
                                                    }
                                                } else { $icon_html = "<i class='bi bi-check-circle text-muted me-3 fs-5'></i>"; }
                                        ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                <div class="d-flex align-items-center"><?php echo $icon_html; ?><span class="fw-medium"><?php echo htmlspecialchars($am['name']); ?></span></div>
                                                <a href="../../../Backend/functions/delete_amenity.php?id=<?php echo $am['amenity_id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill border-0" onclick="return confirm('Xóa tiện ích này?');"><i class="bi bi-trash"></i></a>
                                            </li>
                                        <?php } } else { echo '<div class="p-4 text-center text-muted">Chưa có tiện ích nào.</div>'; } ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 mb-4">
                            <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 1;">
                                <div class="card-header text-black" style="background-color: #8FABD4;"><h6 class="mb-0"><i class="bi bi-plus-circle-fill me-2"></i>Thêm Mới</h6></div>
                                <div class="card-body">
                                    <form action="../../../Backend/functions/process_add_amenity.php" method="POST">
                                        <div class="mb-3"><label class="form-label fw-bold small text-muted">Tên tiện ích</label><input type="text" class="form-control" name="name" required></div>
                                        <div class="mb-3"><label class="form-label fw-bold small text-muted">Icon</label><input type="text" class="form-control" name="icon_url" id="input_icon_url" required></div>
                                        <div class="d-grid mt-4"><button type="submit" class="btn btn-custom">Lưu Tiện Ích</button></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($tab == 'notifications'): ?>
                    <div class="row">
                        <div class="col-md-5 mb-4">
                            <div class="card shadow-sm border-0 sticky-top" style="top: 20px;">
                                <div class="card-header text-black" style="background-color: #8FABD4;">
                                    <h6 class="mb-0"><i class="bi bi-send-fill me-2"></i>Soạn thông báo mới</h6>
                                </div>
                                <div class="card-body">
                                    <form action="../../../Backend/functions/process_send_notification.php" method="POST">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Tiêu đề thông báo</label>
                                            <input type="text" class="form-control" name="title" placeholder="VD: Bảo trì hệ thống..." required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Nội dung chi tiết</label>
                                            <textarea class="form-control" name="message" rows="5" placeholder="Nhập nội dung thông báo gửi đến toàn bộ thành viên..." required></textarea>
                                        </div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-custom fw-bold"style="background-color: #8FABD4;">
                                                <i class="bi bi-envelope-paper-fill me-2"></i>Gửi ngay
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header card-header-admin bg-white">
                                    <i class="bi bi-clock-history me-2"></i>Lịch sử thông báo
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <?php
                                        // Kiểm tra bảng notifications có tồn tại không trước khi query
                                        $check_noti = $conn->query("SHOW TABLES LIKE 'notifications'");
                                        if($check_noti && $check_noti->num_rows > 0) {
                                            $sql_noti = "SELECT * FROM notifications ORDER BY created_at DESC";
                                            $res_noti = $conn->query($sql_noti);

                                            if ($res_noti && $res_noti->num_rows > 0) {
                                                while($noti = $res_noti->fetch_assoc()) {
                                        ?>
                                            <li class="list-group-item py-3">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1 fw-bold " style="color: #4A70A9;"><?php echo htmlspecialchars($noti['title']); ?></h6>
                                                        <p class="mb-1 text-muted small" style="white-space: pre-line;"><?php echo htmlspecialchars($noti['message']); ?></p>
                                                    </div>
                                                    <span class="badge bg-light text-secondary border">
                                                        <?php echo date('d/m/Y H:i', strtotime($noti['created_at'])); ?>
                                                    </span>
                                                </div>
                                            </li>
                                        <?php
                                                }
                                            } else {
                                                echo '<div class="p-5 text-center text-muted"><i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>Chưa có thông báo nào được gửi.</div>';
                                            }
                                        } else {
                                            echo '<div class="alert alert-warning m-3">Chưa tạo bảng notifications trong CSDL. Vui lòng chạy lệnh SQL ở Bước 1.</div>';
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- model chỉnh sửa user -->
   <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header text-black" style="background-color: #8FABD4;">
        <h5 class="modal-title">Chỉnh sửa thành viên</h5>
        <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form action="../../../Backend/functions/process_edit_user.php" method="POST">
            <input type="hidden" name="user_id" id="edit_user_id">
            
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username_display" id="edit_username" readonly>
            </div>

            <div class="mb-3"><label class="form-label">Họ và tên</label><input type="text" class="form-control" name="full_name" id="edit_full_name" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" id="edit_email" required></div>
            <div class="mb-3"><label class="form-label">Số điện thoại</label><input type="text" class="form-control" name="phone" id="edit_phone"></div>

            <div class="mb-3">
                <label class="form-label">Mật khẩu mới (Bỏ trống nếu không đổi)</label>
                <input type="password" class="form-control" name="password" id="edit_password" placeholder="Nhập mật khẩu mới nếu muốn thay đổi">
            </div>
            
            <div class="mb-3"><label class="form-label">Vai trò</label>
                <select class="form-select" name="role" id="edit_role">
                    <option value="user">Người tìm phòng (User)</option><option value="owner">Chủ trọ (Owner)</option><option value="admin">Quản trị viên (Admin)</option>
                </select>
            </div>
            <div class="text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="margin-left: 15px;">Hủy</button>
                <button type="submit" class="btn btn-custom">Lưu thay đổi</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

    <!-- model chỉnh sửa bài đăng -->
    <div class="modal fade" id="editPostModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header text-black" style="background-color: #8FABD4;"><h5 class="modal-title">Chỉnh sửa tin đăng</h5>
          <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <form action="../../../Backend/functions/process_edit_post.php" method="POST">
                <input type="hidden" name="room_id" id="edit_post_id">
                <div class="mb-3"><label class="form-label fw-bold">Tiêu đề</label><input type="text" class="form-control" name="title" id="edit_post_title" required></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label fw-bold">Giá</label><input type="number" class="form-control" name="price" id="edit_post_price" required></div>
                    <div class="col-md-6 mb-3"><label class="form-label fw-bold">Diện tích</label><input type="number" step="0.1" class="form-control" name="area" id="edit_post_area" required></div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Địa chỉ</label>
                    <div class="row g-2">
                        <div class="col-md-4"><label class="small text-muted">Số nhà/Đường</label><input type="text" class="form-control" name="street" id="edit_post_street"></div>
                        <div class="col-md-4"><label class="small text-muted">Quận/Huyện</label>
                             <select class="form-select" name="ward" id="edit_post_ward">
                                <option value="">-- Chọn --</option><option value="Hải Châu">Q. Hải Châu</option><option value="Thanh Khê">Q. Thanh Khê</option><option value="Sơn Trà">Q. Sơn Trà</option><option value="Ngũ Hành Sơn">Q. Ngũ Hành Sơn</option><option value="Liên Chiểu">Q. Liên Chiểu</option><option value="Cẩm Lệ">Q. Cẩm Lệ</option><option value="Hòa Vang">H. Hòa Vang</option>
                             </select>
                        </div>
                        <div class="col-md-4"><label class="small text-muted">Thành phố</label><input type="text" class="form-control" name="city" id="edit_post_city"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Trạng thái</label>
                    <select class="form-select" name="status" id="edit_post_status">
                        <option value="available">Đang hiện</option>
                        <option value="occupied">Đã thuê</option>
                        <option value="pending">Tạm ẩn</option>
                    </select>
                </div>
                <div class="text-end"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="margin-left: 15px;">Hủy</button>
                <button type="submit" class="btn btn-custom">Cập nhật tin</button>
                </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Modal User
        var editUserModal = document.getElementById('editUserModal');
        if (editUserModal) {
            editUserModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                
                // Lấy dữ liệu cũ
                var id = button.getAttribute('data-id');
                var name = button.getAttribute('data-name');
                var email = button.getAttribute('data-email');
                var phone = button.getAttribute('data-phone');
                var role = button.getAttribute('data-role');
                var username = button.getAttribute('data-username'); // TRƯỜNG MỚI: Lấy username

                // Gán vào form
                document.getElementById('edit_user_id').value = id;
                document.getElementById('edit_full_name').value = name;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_phone').value = phone;
                document.getElementById('edit_role').value = role;
                document.getElementById('edit_username').value = username; // TRƯỜNG MỚI: Gán username
                document.getElementById('edit_password').value = ''; // Luôn xóa trường password khi mở
            });
        }

        // Modal Post
        var editPostModal = document.getElementById('editPostModal');
        if (editPostModal) {
            editPostModal.addEventListener('show.bs.modal', function (event) {
                var btn = event.relatedTarget;
                document.getElementById('edit_post_id').value = btn.getAttribute('data-id');
                document.getElementById('edit_post_title').value = btn.getAttribute('data-title');
                document.getElementById('edit_post_price').value = btn.getAttribute('data-price');
                document.getElementById('edit_post_area').value = btn.getAttribute('data-area');
                document.getElementById('edit_post_street').value = btn.getAttribute('data-street');
                document.getElementById('edit_post_ward').value = btn.getAttribute('data-ward');
                document.getElementById('edit_post_city').value = btn.getAttribute('data-city');
                document.getElementById('edit_post_status').value = btn.getAttribute('data-status');
            });
        }

        // --- REALTIME UPDATE (TỰ ĐỘNG CẬP NHẬT) ---
        function fetchRealtimeData() {
            const urlParams = new URLSearchParams(window.location.search);
            const currentTab = urlParams.get('tab');
            if (currentTab === 'posts') {
                fetch('../../../Backend/functions/get_rooms_list.php')
                    .then(response => response.text())
                    .then(data => {
                        var container = document.getElementById('post_data_container');
                        if(container) container.innerHTML = data;
                    })
                    .catch(error => console.error('Lỗi cập nhật:', error));
            }
        }
        setInterval(fetchRealtimeData, 2000); // 2 giây cập nhật 1 lần
    </script>
</body>
</html>