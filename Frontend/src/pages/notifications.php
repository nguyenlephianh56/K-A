<?php
session_start();
// Sử dụng __DIR__ để định vị chính xác file config từ thư mục hiện tại
require_once __DIR__ . '/../../../Backend/config/db_connect.php';

// CHẶN TRUY CẬP: Yêu cầu đăng nhập
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// CẤU HÌNH PHÂN TRANG
$limit = 10;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// ĐẾM TỔNG SỐ THÔNG BÁO
$sql_count = "SELECT COUNT(*) as total FROM notifications";
$res_count = $conn->query($sql_count);
$total_notis = $res_count->fetch_assoc()['total'];
$total_pages = ceil($total_notis / $limit);

// LẤY DANH SÁCH THÔNG BÁO
$sql = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo - K&A</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        .noti-container { max-width: 900px; margin: 0 auto; min-height: 600px; }
        .noti-card {
            border: none; border-radius: 12px; background: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative; overflow: hidden;
        }
        .noti-card:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.08); }
        .noti-card::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0;
            width: 4px; background: #4A70A9;
        }
        .noti-icon-box {
            width: 45px; height: 45px;
            background-color: #fff5f5; color: #4A70A9;
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
        }
        .noti-date { font-size: 0.8rem; color: #999; white-space: nowrap; }
        .noti-title { font-weight: 700; color: #333; margin-bottom: 0.5rem; }
        .noti-body { color: #555; line-height: 1.6; font-size: 0.95rem; }
        .empty-state img { width: 120px; opacity: 0.5; margin-bottom: 1rem; }
    </style>
</head>
<body class="bg-light">

    <?php 
        // Dùng __DIR__ để đi ra thư mục cha (..) rồi vào components
       
        $header_path = __DIR__ . '/../partials/header.php'; 
            include $header_path;
    
    ?>

    <div class="container py-5 mt-5">
        <div class="noti-container">
            
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Thông báo hệ thống</h3>
                    <p class="text-muted mb-0">Cập nhật tin tức mới nhất từ K&A</p>
                </div>
                <span class="badge rounded-pill px-3 py-2 border text-black" style="background-color:#8FABD4;">
                    Tổng: <?php echo $total_notis; ?>
                </span>
            </div>

            <div class="d-flex flex-column gap-3">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="card noti-card p-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="noti-icon-box"><i class="bi bi-megaphone-fill"></i></div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h5 class="noti-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                        <span class="noti-date">
                                            <i class="bi bi-calendar3 me-1"></i>
                                            <?php echo date('d/m/Y - H:i', strtotime($row['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="noti-body">
                                        <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5 empty-state">
                        <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" alt="Empty">
                        <h5 class="text-muted fw-bold">Chưa có thông báo nào</h5>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 shadow-none text-dark" href="?page=<?php echo $page - 1; ?>"><i class="bi bi-arrow-left me-1"></i> Cũ hơn</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item">
                            <a class="page-link border-0 shadow-none rounded-circle mx-1 <?php echo ($i == $page) ? 'bg-danger text-white fw-bold' : 'text-dark'; ?>" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link border-0 shadow-none text-dark" href="?page=<?php echo $page + 1; ?>">Mới hơn <i class="bi bi-arrow-right ms-1"></i></a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>

        </div>
    </div>

    <?php 
        $footer_path = __DIR__ . '/../partials/footer.php';
        if (file_exists($footer_path)) include $footer_path;
    ?>
    <script src="../assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>