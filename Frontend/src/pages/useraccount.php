<?php

require_once '../../../Backend/config/db_connect.php';
require_once '../partials/header.php'; 

// KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['user'])) {
    echo "<script>alert('Vui lòng đăng nhập!'); window.location.href='login.php';</script>";
    exit();
}

$user_id = $_SESSION['user']['id'];
$user_role = $_SESSION['user']['role'];

// XỬ LÝ CẬP NHẬT THÔNG TIN
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // --- Cập nhật Hồ sơ ---
    if (isset($_POST['update_profile'])) {
        $fullname = trim($_POST['fullname']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        
        // Mặc định university_id là null (cho owner)
        $university_id = null;

        // Nếu là STUDENT thì lấy university_id từ form
        if ($user_role == 'student' && isset($_POST['university_id'])) {
            $university_id = intval($_POST['university_id']);
        }
        
        // Câu lệnh SQL update (Có thêm university_id)
        $sql = "UPDATE users SET full_name = ?, phone = ?, email = ?, university_id = ? WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $fullname, $phone, $email, $university_id, $user_id);

        
        
        if ($stmt->execute()) {
            // Cập nhật lại Session
            $_SESSION['user']['fullname'] = $fullname;
            $_SESSION['user']['email'] = $email;
            
            $_SESSION['notification'] = ['type' => 'success', 'title' => 'Thành công', 'message' => 'Cập nhật thông tin thành công!'];
        } else {
            $_SESSION['notification'] = ['type' => 'error', 'title' => 'Lỗi', 'message' => 'Không thể cập nhật: ' . $conn->error];
        }
        echo "<script>window.location.href='useraccount.php?tab=profile';</script>";
        exit();
    }

    // --- Đổi Mật khẩu (Giữ nguyên code cũ) ---
    if (isset($_POST['change_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        $sql_get = "SELECT password FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sql_get);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $curr_user = $res->fetch_assoc();

        if (password_verify($old_pass, $curr_user['password'])) {
            if ($new_pass === $confirm_pass) {
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                $sql_up = "UPDATE users SET password = ? WHERE user_id = ?";
                $stmt_up = $conn->prepare($sql_up);
                $stmt_up->bind_param("si", $hashed, $user_id);
                $stmt_up->execute();
                $_SESSION['notification'] = ['type' => 'success', 'title' => 'Thành công', 'message' => 'Đổi mật khẩu thành công!'];
            } else {
                $_SESSION['notification'] = ['type' => 'error', 'title' => 'Lỗi', 'message' => 'Mật khẩu xác nhận không khớp.'];
            }
        } else {
            $_SESSION['notification'] = ['type' => 'error', 'title' => 'Lỗi', 'message' => 'Mật khẩu cũ không đúng.'];
        }
        echo "<script>window.location.href='useraccount.php?tab=password';</script>";
        exit();
    }
}

// --- C. XỬ LÝ QUẢN LÝ BÀI ĐĂNG (Dành cho Chủ trọ) ---
    //  Cập nhật trạng thái (available / occupied / pending)
    if (isset($_POST['update_status'])) {
        $room_id = intval($_POST['room_id']);
        $new_status = $_POST['status'];
        
        // Kiểm tra quyền sở hữu (Chỉ update nếu bài đó của mình)
        $sql_check = "UPDATE rooms SET status = ? WHERE room_id = ? AND owner_id = ?";
        $stmt = $conn->prepare($sql_check);
        $stmt->bind_param("sii", $new_status, $room_id, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['notification'] = ['type' => 'success', 'title' => 'Thành công', 'message' => 'Cập nhật trạng thái thành công!'];
        } else {
            $_SESSION['notification'] = ['type' => 'error', 'title' => 'Lỗi', 'message' => 'Lỗi cập nhật: ' . $conn->error];
        }
        echo "<script>window.location.href='useraccount.php?tab=myposts';</script>";
        exit();
    }

    // Xóa bài đăng
    if (isset($_POST['delete_post'])) {
        $room_id = intval($_POST['room_id']);

        //  Xóa ảnh trong thư mục uploads trước 
        $sql_get_photos = "SELECT photo_url FROM room_photos WHERE room_id = ?";
        $stmt_photos = $conn->prepare($sql_get_photos);
        $stmt_photos->bind_param("i", $room_id);
        $stmt_photos->execute();
        $res_photos = $stmt_photos->get_result();
        
        while($photo = $res_photos->fetch_assoc()) {
            $file_path = "../../Frontend/src/uploads/" . $photo['photo_url'];
            if (file_exists($file_path)) {
                unlink($file_path); // Hàm xóa file của PHP
            }
        }

        //  Xóa dữ liệu trong CSDL
        // (Do ràng buộc khóa ngoại, nên xóa rooms thì các bảng con photos/amenities sẽ tự mất nếu có Cascade, 
        $sql_del = "DELETE FROM rooms WHERE room_id = ? AND owner_id = ?";
        $stmt_del = $conn->prepare($sql_del);
        $stmt_del->bind_param("ii", $room_id, $user_id);

        if ($stmt_del->execute()) {
            $_SESSION['notification'] = ['type' => 'success', 'title' => 'Đã xóa', 'message' => 'Bài đăng đã được xóa vĩnh viễn.'];
        } else {
            $_SESSION['notification'] = ['type' => 'error', 'title' => 'Lỗi', 'message' => 'Không thể xóa bài đăng này.'];
        }
        echo "<script>window.location.href='useraccount.php?tab=myposts';</script>";
        exit();
    }

// LẤY THÔNG TIN USER HIỆN TẠI (Bao gồm cả tên trường ĐH nếu có)
// Sử dụng LEFT JOIN để lấy tên trường dù user có chọn trường hay chưa
$sql_info = "SELECT u.*, uni.name as uni_name 
             FROM users u 
             LEFT JOIN universities uni ON u.university_id = uni.university_id 
             WHERE u.user_id = ?";
$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("i", $user_id);
$stmt_info->execute();
$user_info = $stmt_info->get_result()->fetch_assoc();

// LẤY DANH SÁCH TẤT CẢ TRƯỜNG ĐẠI HỌC (Để đổ vào Select option)
$list_universities = [];
if ($user_role == 'student') {
    $res_uni = $conn->query("SELECT * FROM universities ORDER BY name ASC");
    if ($res_uni) {
        while ($row = $res_uni->fetch_assoc()) {
            $list_universities[] = $row;
        }
    }
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'profile';
?>

    <div class="container main-content" style="margin-top: 100px; margin-bottom: 50px;">
    
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold text-primary text-uppercase">Quản lý tài khoản</h3>
            <p class="text-muted">Xin chào, <strong><?php echo $user_info['full_name']; ?></strong> (<?php echo ($user_role == 'owner') ? 'Chủ trọ' : (
                                                                                                                ($user_role == 'admin') ? 'Quản trị viên' : 'Sinh viên'); ?>)</p>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-3 col-md-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px;">
                <div class="card-body text-center p-4" >
                    <img src="<?php echo $assets_path; ?>images/user-default.png" class="rounded-circle border mb-3" width="100" height="100" alt="Avatar">
                    <h5 class="fw-bold"><?php echo $user_info['username']; ?></h5>
                    
                    <?php if ($user_role == 'student' && !empty($user_info['uni_name'])): ?>
                        <p class="badge text-dark mb-1 text-wrap"style="background-color: #8FABD4;" >
                            <i class="bi bi-mortarboard-fill"></i> <?php echo $user_info['uni_name']; ?>
                        </p>
                    <?php endif; ?>
                    
                    <p class="text-muted small mt-2"><?php echo $user_info['email']; ?></p>
                </div>
                
                <div class="list-group list-group-flush">
                    <a href="?tab=profile" class="list-group-item list-group-item-action py-3 <?php echo ($active_tab == 'profile') ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-person-gear me-2"></i> Thông tin cá nhân
                    </a>
                    <a href="?tab=password" class="list-group-item list-group-item-action py-3 <?php echo ($active_tab == 'password') ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-shield-lock me-2"></i> Đổi mật khẩu
                    </a>
                    <a href="?tab=favorites" class="list-group-item list-group-item-action py-3 <?php echo ($active_tab == 'favorites') ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-heart me-2"></i> Tin đã lưu
                    </a>
                    <?php if ($user_role == 'owner'): ?>
                    <a href="?tab=myposts" class="list-group-item list-group-item-action py-3 <?php echo ($active_tab == 'myposts') ? 'active fw-bold' : ''; ?>">
                        <i class="bi bi-card-list me-2"></i> Quản lý bài đăng
                    </a>
                    <?php endif; ?>
                    <a href="<?php echo $base_url; ?>public/logout.php" class="list-group-item list-group-item-action py-3 text-danger">
                        <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-9 col-md-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body p-4">
                    
                    <?php if ($active_tab == 'profile'): ?>
                        <h4 class="fw-bold mb-4 border-bottom pb-2">Cập nhật thông tin</h4>
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Tên đăng nhập</label>
                                    <input type="text" class="form-control bg-light" value="<?php echo $user_info['username']; ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Vai trò</label>
                                    <input type="text" class="form-control bg-light" value="<?php echo ($user_role == 'owner') ? 'Chủ trọ' : 'Sinh viên'; ?>" readonly>
                                </div>
                            </div>
                            
                            <?php if ($user_role == 'student'): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold" style="color: black;"><i class="bi bi-mortarboard-fill me-1"></i> Trường Đại học của bạn</label>
                                <select class="form-select" name="university_id">
                                    <option value="" disabled>-- Chọn trường đại học --</option>
                                    <?php foreach ($list_universities as $uni): ?>
                                        <option value="<?php echo $uni['university_id']; ?>" 
                                            <?php echo ($user_info['university_id'] == $uni['university_id']) ? 'selected' : ''; ?>>
                                            <?php echo $uni['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text text-muted small">Cập nhật đúng trường để nhận gợi ý phòng trọ phù hợp nhất.</div>
                            </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Họ và tên</label>
                                <input type="text" name="fullname" class="form-control" value="<?php echo $user_info['full_name']; ?>" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo $user_info['email']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control" value="<?php echo $user_info['phone']; ?>" required>
                                </div>
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary px-4">
                                <i class="bi bi-save me-1"></i> Lưu thay đổi
                            </button>
                        </form>
                    
                    <?php elseif ($active_tab == 'password'): ?>
                        <h4 class="fw-bold mb-4 border-bottom pb-2">Đổi mật khẩu</h4>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mật khẩu hiện tại</label>
                                <input type="password" name="old_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mật khẩu mới</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Xác nhận mật khẩu mới</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-warning text-dark px-4">
                                <i class="bi bi-key me-1"></i> Cập nhật mật khẩu
                            </button>
                        </form>

                    <?php elseif ($active_tab == 'myposts' && $user_role == 'owner'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-2">
        <h4 class="fw-bold mb-0">Bài đăng của tôi</h4>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 40%;">Tin đăng</th>
                    <th style="width: 15%;">Giá</th>
                    <th style="width: 25%;">Trạng thái</th>
                    <th style="width: 20%;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql_my_rooms = "SELECT * FROM rooms WHERE owner_id = ? ORDER BY created_at DESC";
                $stmt = $conn->prepare($sql_my_rooms);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $res_my_rooms = $stmt->get_result();

                if ($res_my_rooms->num_rows > 0):
                    while($room = $res_my_rooms->fetch_assoc()):
                ?>
                <tr>
                    <td>
                        <a href="<?php echo $base_url?>src/pages/details.php?id=<?php echo $room['room_id']; ?>" class="fw-bold text-decoration-none text-dark d-block text-truncate" style="max-width: 250px;">
                            <?php echo $room['title']; ?> <i class="bi bi-box-arrow-up-right small text-muted ms-1"></i>
                        </a>
                        <small class="text-muted"><i class="bi bi-clock"></i> <?php echo date('d/m/Y', strtotime($room['created_at'])); ?></small>
                    </td>

                    <td class="text-danger fw-bold"><?php echo number_format($room['price']); ?>đ</td>

                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                            <input type="hidden" name="update_status" value="1">
                            
                            <?php 
                            $st_class = 'text-dark border-secondary'; 
                            $st_style = 'background-color: #f8f9fa;'; // Màu nền mặc định 

                             // XỬ LÝ MÀU THEO TRẠNG THÁI
                            if($room['status'] == 'available') {
                            $st_class = 'text-white border-success'; // (Chữ trắng)
                            $st_style = 'background-color: #4A70A9;'; 
                            }
                            elseif($room['status'] == 'occupied') {
                            $st_class = 'text-white border-secondary';
                            $st_style = 'background-color: red;'; // Màu đỏ
                            }
                            elseif($room['status'] == 'pending') {
                            $st_class = 'text-dark border-warning';
                            $st_style = 'background-color: #ffc107;'; // Màu vàng
                            }
                            ?>

                            <select name="status" 
                                class="form-select form-select-sm fw-bold <?php echo $st_class; ?>" 
                                style="width: 140px; cursor: pointer; <?php echo $st_style; ?>"
                                onchange="this.form.submit()">
                                
                                <option value="available" class="bg-white text-dark" <?php echo ($room['status'] == 'available') ? 'selected' : ''; ?>>
                                    Còn phòng
                                </option>
                                <option value="occupied" class="bg-white text-dark" <?php echo ($room['status'] == 'occupied') ? 'selected' : ''; ?>>
                                    Đã thuê
                                </option>
                                <option value="pending" class="bg-white text-dark" <?php echo ($room['status'] == 'pending') ? 'selected' : ''; ?>>
                                    Tạm ẩn
                                </option>
                            
                            </select>
                        </form>
                    </td>

                    <td>
                        <div class="d-flex">
                            <button class="btn btn-sm btn-outline-primary me-2" title="Chỉnh sửa">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" 
                class="btn btn-sm btn-outline-danger" 
                data-bs-toggle="modal" 
                data-bs-target="#deleteConfirmModal"
                data-room-id="<?php echo $room['room_id']; ?>"
                title="Xóa bài">
            <i class="bi bi-trash-fill"></i>
        </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x display-4 d-block mb-3 opacity-25"></i>
                            Bạn chưa có bài đăng nào. Hãy đăng tin ngay!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

                    <?php elseif ($active_tab == 'favorites'): ?>
                        <h4 class="fw-bold mb-4 border-bottom pb-2">Tin đã lưu</h4>
    
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php
            // TRUY VẤN CÁC PHÒNG ĐÃ TIM
            $sql_fav = "SELECT r.*, 
                    rt.name AS type_name, 
                    (SELECT photo_url FROM room_photos WHERE room_id = r.room_id LIMIT 1) as thumbnail 
                    FROM rooms r 
                    JOIN favourites f ON r.room_id = f.room_id 
                    JOIN room_types rt ON r.room_type_id = rt.room_type_id 
                    WHERE f.user_id = ? 
                    ORDER BY f.created_at DESC";
        
            $stmt = $conn->prepare($sql_fav);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res_fav = $stmt->get_result();

            if ($res_fav->num_rows > 0):
                while($row = $res_fav->fetch_assoc()):
                
                    // XỬ LÝ ẢNH THUMBNAIL (Logic chuẩn basename + base_url)
                    $thumb = $assets_path . 'images/no-image.jpg';
                    if (!empty($row['thumbnail'])) {
                        $filename = basename($row['thumbnail']);
                        $thumb = $base_url . 'src/uploads/' . $filename;
                    }
                
                    // BIẾN FIX LỖI COMPONENT
                    $project_root = ''; 

                    // GỌI COMPONENT CARD
                    // Lưu ý: useraccount.php nằm ở src/pages/, component ở src/component/
                    // Nên đường dẫn là ../component/...
        ?>
            <div class="col">
                <?php include '../component/product-card.php'; ?>
            </div>
            
        <?php 
            endwhile; 
        else: 
        ?>
            <div class="col-12  py-5 text-muted">
                <i class="bi bi-heart-break display-1 d-block mb-3 opacity-25"></i>
                <h5>Bạn chưa lưu tin đăng nào.</h5>
                <p>Hãy thả tim các phòng bạn thích để xem lại sau nhé!</p>
                
            </div>
        <?php endif; ?>
                        
                    <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- model thông báo xóa bài -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Xác nhận xóa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body text-center py-4">
                <p class="mb-1 fs-5">Bạn có chắc chắn muốn xóa bài đăng này?</p>
                <p class="text-danger small fw-bold fst-italic">Hành động này không thể hoàn tác!</p>
            </div>
            
            <div class="modal-footer justify-content-center border-top-0 pb-4">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Hủy bỏ</button>
                
                <form method="POST" action="">
                    <input type="hidden" name="room_id" id="modal_room_id_input">
                    
                    <button type="submit" name="delete_post" class="btn btn-danger px-4 fw-bold">
                        <i class="bi bi-trash me-1"></i> Xóa ngay
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
<!-- model thông báo đăng nhập -->
    <div class="modal fade" id="thongBaoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title fw-bold" id="modalTitle">Thông báo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body" id="modalBody"></div>
      <div class="modal-footer"><button type="button" class="btn btn-primary" data-bs-dismiss="modal">Đồng ý</button></div>
    </div>
  </div>
</div>
</div>
<!-- model hiển thị lỗi -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-danger">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill me-2"></i>Thông báo lỗi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <p class="fs-5 text-danger fw-bold" id="errorModalMessage">
                    </p>
                <button type="button" class="btn btn-secondary mt-3" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php
// Kiểm tra xem có lỗi từ bên handler gửi về không
if (isset($_SESSION['permission_error'])) {
    $error_msg = $_SESSION['permission_error'];
    // Xóa lỗi ngay sau khi lấy ra (để F5 không hiện lại)
    unset($_SESSION['permission_error']); 
    ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lấy nội dung lỗi từ PHP đưa vào JS
            var msg = "<?php echo $error_msg; ?>";
            
            // Gán nội dung vào Modal
            document.getElementById('errorModalMessage').innerText = msg;
            
            // Bật Modal lên
            var myModal = new bootstrap.Modal(document.getElementById('errorModal'));
            myModal.show();
        });
    </script>
    
<?php } ?>


<script>


    //script sử lý khi đăng bài thành công
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['notification'])): ?>
        const type = "<?php echo $_SESSION['notification']['type']; ?>";
        const title = "<?php echo $_SESSION['notification']['title']; ?>";
        const message = "<?php echo $_SESSION['notification']['message']; ?>";
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('modalBody').innerText = message;
        const modalHeader = document.querySelector('#thongBaoModal .modal-header');
        modalHeader.classList.remove('bg-success', 'bg-danger', 'text-white');
        if (type === 'success') modalHeader.classList.add('bg-success', 'text-white');
        else modalHeader.classList.add('bg-danger', 'text-white');
        var myModal = new bootstrap.Modal(document.getElementById('thongBaoModal'));
        myModal.show();
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>
});

// truyền room_id vào modal xóa bài
document.addEventListener('DOMContentLoaded', function() {
    var deleteModal = document.getElementById('deleteConfirmModal');
    
    // Lắng nghe sự kiện khi Modal bắt đầu mở
    deleteModal.addEventListener('show.bs.modal', function (event) {
        
        //Tìm nút đã kích hoạt Modal
        var button = event.relatedTarget; 
        
        //Lấy giá trị data-room-id từ nút đó
        var roomId = button.getAttribute('data-room-id');
        
        //Tìm ô input ẩn trong Modal và gán giá trị ID vào
        var modalInput = deleteModal.querySelector('#modal_room_id_input');
        modalInput.value = roomId;
    });
});
</script>

<?php include '../partials/footer.php'; ?>