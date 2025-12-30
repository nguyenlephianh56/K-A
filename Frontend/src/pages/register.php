<?php
// KHỞI ĐỘNG SESSION 
if (session_status() === PHP_SESSION_NONE) session_start();

// LẤY ROLE TỪ URL (Mặc định là student)
$role = isset($_GET['role']) ? $_GET['role'] : 'student';

// XÁC ĐỊNH TIÊU ĐỀ
$role_title = ($role == 'owner') ? 'CHỦ TRỌ' : 'SINH VIÊN';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Tài Khoản - <?php echo $role_title; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
    <div class="card login-card">
        <div class="card-body p-4 p-md-5">
            <div class="login-header mb-4">
                <i class="fas fa-solid fa-key fa-3x mb-2 text-white" style="width: 80px;"></i>
                <h3 class="mb-1">Đăng ký: <?php echo $role_title; ?></h3>
                <p class="text-black ">Xin chào những vị khách mới!</p>
            </div>

            <form action="xuly_dangky.php" method="POST">
                
                <input type="hidden" name="role" value="<?php echo $role; ?>">

                <div class="form-floating mb-2">
                    <input type="email" class="form-control" id="dienemail" name="email" placeholder="Email" required>
                    <label>Email</label>
                </div>

                <div class="form-floating mb-2">
                    <input type="text" class="form-control" id="tenDangNhap" name="username" placeholder="Tên đăng nhập" required>
                    <label>Tên đăng nhập</label>
                </div>

                <div class="form-floating mb-2">
                    <input type="password" class="form-control" id="matKhau" name="password" placeholder="Mật khẩu" required>
                    <label>Mật khẩu</label>
                </div>

                <div class="form-floating mb-2">
                    <input type="text" class="form-control" id="hovaten" name="fullname" placeholder="Họ và Tên" required>
                    <label>Họ và tên</label>
                </div>

                <div class="form-floating mb-2">
                    <input type="text" class="form-control" id="SĐT" name="phonenumber" placeholder="Số điện thoại" required>
                    <label>Số điện thoại</label>
                </div>    
                
                <?php if ($role == 'student'): ?>
                <div class="form-floating mb-2">
                    <select class="form-select" id="floatingSelect" name="university_id" required>
                        <option selected disabled value="">Trường Đại Học?</option> 
                        <option value="1">Đại học Bách khoa - ĐHĐN</option>
                        <option value="2">Đại học CNTT & TT Việt - Hàn</option>
                        <option value="3">Đại học Đông Á</option>
                        <option value="4">Đại học Duy Tân</option>
                        <option value="5">Đại học FPT Đà Nẵng</option>
                        <option value="6">Đại học Kinh tế - ĐHĐN</option>
                        <option value="7">Đại học Kiến trúc Đà Nẵng</option>
                        <option value="8">Đại học Kỹ thuật Y - Dược ĐN</option>
                        <option value="9">Đại học Ngoại ngữ - ĐHĐN</option>
                        <option value="10">Đại học Sư phạm Kỹ thuật - ĐHĐN</option>
                        <option value="11">Đại học Sư phạm - ĐHĐN</option>
                        <option value="12">Đại học Thể dục Thể thao ĐN</option>
                        <option value="13">Đại học Xây dựng Miền Trung Phân hiệu Đà Nẵng</option>
                        <option value="14">Trường Y dược - Đại học Đà Nẵng</option>
                        <option value="15">Viện Nghiên cứu và Đào tạo Việt - Anh - Đại học Đà Nẵng</option>
                    </select>
                    <label for="floatingSelect">Chọn trường đại học của bạn</label>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-custom btn-primary w-100 mb-3 mt-2">
                    <i class="fas fa-sign-in-alt me-2"></i> Đăng Ký
                </button>
                
                <hr>

                <p class="text-center small mb-0">
                    Đã có tài khoản? <a href="login.php" class="text-decoration-none fw-bold">Đăng nhập nào!</a>
                </p>

            </form>
        </div>
    </div>

    <div class="modal fade" id="thongBaoModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fw-bold" id="modalTitle">Thông báo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body" id="modalBody"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="btnModalOK" data-bs-dismiss="modal">Đồng ý</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    


    <script>

        //reset form 

        document.addEventListener("DOMContentLoaded", function() {
        // Lấy tất cả các form trên trang
        let forms = document.querySelectorAll("form");

        forms.forEach(function(form) {
            //Gán thuộc tính tắt tự động điền
            form.setAttribute("autocomplete", "off");

            // Reset form ngay lập tức
            form.reset();

            // Quan trọng: Đợi 100ms sau để xóa lần nữa
            // (Khắc phục việc trình duyệt tự điền sau khi trang tải xong)
            setTimeout(function() {
                form.reset();
                
                // Xóa thủ công từng ô input để chắc chắn 100%
                let inputs = form.querySelectorAll("input");
                inputs.forEach(function(input) {
                    // Trừ các input hidden (như role) và nút submit
                    if (input.type !== "hidden" && input.type !== "submit" && input.type !== "button") {
                        input.value = "";
                    }
                });
            }, 100); 
        });
         });

        document.addEventListener('DOMContentLoaded', function() { //function js xử lý thông báo đăng ký (thành công/thất bại)
            <?php if (isset($_SESSION['notification'])): ?>
                const type = "<?php echo $_SESSION['notification']['type']; ?>";
                const title = "<?php echo $_SESSION['notification']['title']; ?>";
                const message = "<?php echo $_SESSION['notification']['message']; ?>";

                document.getElementById('modalTitle').innerText = title;
                document.getElementById('modalBody').innerText = message;
                
                //set màu cho khu vực thông báo nè
                const modalHeader = document.querySelector('#thongBaoModal .modal-header');
                modalHeader.classList.remove('bg-success', 'bg-danger', 'text-white');
                
                if (type === 'success') {           
                 modalHeader.style.backgroundColor = '#8FABD4'; 
                } else {
                modalHeader.style.backgroundColor = '#dc3545'; 
                }

                var myModal = new bootstrap.Modal(document.getElementById('thongBaoModal'));
                myModal.show();

                const btnOK = document.getElementById('btnModalOK');
                const myModalEl = document.getElementById('thongBaoModal');
                
                function handleRedirect() {
                    if (type === 'success') {
                        // Đăng ký thành công -> Chuyển sang trang LOGIN để đăng nhập
                        window.location.href = 'login.php';
                    }
                }
                btnOK.addEventListener('click', handleRedirect);
                myModalEl.addEventListener('hidden.bs.modal', handleRedirect);

                <?php unset($_SESSION['notification']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>