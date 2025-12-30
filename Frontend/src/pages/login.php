<?php
//KHỞI ĐỘNG SESSION ĐỂ NHẬN THÔNG BÁO TỪ XULY_LOGIN.PHP
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Hệ thống Tìm Trọ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="card login-card">
        <div class="card-body p-4 p-md-5">
            <div class="login-header mb-4">
                <i class="fas fa-lock fa-3x mb-2 text-white"></i>
                <h3 class="mb-1">Đăng Nhập Tài Khoản</h3>
                <p class="text-muted small">Chào mừng trở lại!</p>
            </div>

            <form action="xuly_login.php" method="POST">
                
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="tenDangNhap" name="username" placeholder="Tên đăng nhập hoặc Email" required>
                    <label for="tenDangNhap">Tên đăng nhập / Email</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="matKhau" name="password" placeholder="Mật khẩu" required>
                    <label for="matKhau">Mật khẩu</label>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="ghiNho">
                        <label class="form-check-label small" for="ghiNho">
                            Ghi nhớ đăng nhập
                        </label>
                    </div>
                    <a href="#" class="small text-decoration-none">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn btn-custom btn-primary w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i> Đăng Nhập
                </button>
                
                <hr>

                <p class="text-center small mb-0">
                    Chưa có tài khoản? <a href="identify.php" class="text-decoration-none fw-bold">Đăng ký ngay</a>
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
          <div class="modal-body" id="modalBody">
            </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-acp text-black text-bold" id="btnModalOK" data-bs-dismiss="modal" style="background-color: #8FABD4;">Đồng ý</button>
          </div>
        </div>
      </div>
    </div>

    <!-- js boostrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" 
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" 
    crossorigin="anonymous"></script>


    <script>
    //làm mới form
    document.addEventListener("DOMContentLoaded", function() {
        //Lấy tất cả các form trên trang
        let forms = document.querySelectorAll("form");

        forms.forEach(function(form) {
            //Gán thuộc tính tắt tự động điền
            form.setAttribute("autocomplete", "off");

            //Reset form ngay lập tức
            form.reset();

            //Đợi 100ms sau để xóa lần nữa
            //(Khắc phục việc trình duyệt tự điền sau khi trang tải xong)
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

    //js xử lý thông báo đăng nhập
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['notification'])): ?>
                
                //Lấy dữ liệu từ PHP
                const type = "<?php echo $_SESSION['notification']['type']; ?>";
                const title = "<?php echo $_SESSION['notification']['title']; ?>";
                const message = "<?php echo $_SESSION['notification']['message']; ?>";

                //Điền thông tin vào Modal HTML
                document.getElementById('modalTitle').innerText = title;
                document.getElementById('modalBody').innerText = message;
                
                //Đổi màu Header Modal
                const modalHeader = document.querySelector('#thongBaoModal .modal-header');
                modalHeader.classList.remove('bg-success', 'bg-danger', 'text-black'); // Reset class cũ
                if (type === 'success') {           
                 modalHeader.style.backgroundColor = '#8FABD4'; 
                } else {
                modalHeader.style.backgroundColor = '#dc3545'; 
                }

                //Hiển thị Modal
                var myModal = new bootstrap.Modal(document.getElementById('thongBaoModal'));
                myModal.show();

                //Xử lý khi bấm nút "Đồng ý" hoặc tắt Modal (có cái dấu x ở phía trên)
                //Nếu thành công -> Chuyển sang trang chủ (index.php, hoạt động bình thường)
                //Nếu thất bại -> Ở lại trang để nhập lại
                const btnOK = document.getElementById('btnModalOK');
                const myModalEl = document.getElementById('thongBaoModal');

                function handleRedirect() {
                    if (type === 'success') {
                        //CHUYỂN HƯỚNG VỀ TRANG CHỦ
                        window.location.href = '../../public/index.php';
                    }
                }

                btnOK.addEventListener('click', handleRedirect);
                myModalEl.addEventListener('hidden.bs.modal', handleRedirect);

                //Xóa session để không hiện lại khi F5
                <?php unset($_SESSION['notification']); ?>
                
            <?php endif; ?>
        });
    </script>
</body>
</html>