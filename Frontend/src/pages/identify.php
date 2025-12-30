<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực - Hệ thống Tìm Trọ</title>
    <link rel="stylesheet" href="../assets/css/identifyuser.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>

    <div class="container role-selection-container">
        <a href="../../public/index.php">
             <div class="logo justify-content-center mb-4 ">
                <img src="../assets/images/logo.png" alt="">
            </div>
        </a>

        <div class="text-center">
            <h2 class="page-title">Đăng Ký Hệ Thống</h2>
            <p class="page-subtitle">Vui lòng chọn vai trò của bạn để tiếp tục truy cập</p>
        </div>

        <div class="row justify-content-center g-4">
            
            <div class="col-12 col-md-5 col-lg-4">
                <a href="register.php?role=student" class="text-decoration-none">
                    <div class="role-card student-card">
                        <div class="role-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h5 class="role-title">BẠN LÀ SINH VIÊN</h5>
                        <p class="role-desc">
                            Dành cho người có nhu cầu tìm kiếm phòng trọ, ghép ở và xem thông tin nhà ở.
                        </p>
                        <button class="btn btn-outline-primary btn-sm mt-3">Đăng ký Sinh viên</button>
                    </div>
                </a>
            </div>

            <div class="col-12 col-md-5 col-lg-4">
                <a href="register.php?role=owner" class="text-decoration-none">
                    <div class="role-card landlord-card">
                        <div class="role-icon">
                            <i class="fas fa-house-user"></i>
                        </div>
                        <h5 class="role-title">BẠN LÀ CHỦ TRỌ</h5>
                        <p class="role-desc">
                            Dành cho chủ nhà, người quản lý muốn đăng tin cho thuê và quản lý phòng trọ.
                        </p>
                        <button class="btn btn-outline-warning text-dark btn-sm mt-3">Đăng ký Chủ trọ</button>
                    </div>
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="info-box">
                    <div class="info-title">THÔNG BÁO TỪ HỆ THỐNG:</div>
                    <p class="mb-0 small">
                        Khi đăng nhập, hệ thống sẽ xác thực thông tin dựa trên loại tài khoản bạn chọn. 
                    </p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
             <a href="../../public/index.php" class="text-decoration-none text-muted small">Quay lại trang chủ</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>