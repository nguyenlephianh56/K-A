<?php
// Backend/api/get_notifications.php
require_once '../config/db_connect.php'; // Đảm bảo đường dẫn đúng

header('Content-Type: application/json');

// 1. Đếm tổng số thông báo đang có
$sql_count = "SELECT COUNT(*) as total FROM notifications";
$res_count = $conn->query($sql_count);
$total = $res_count->fetch_assoc()['total'];

// 2. Lấy 5 thông báo mới nhất để hiện trong danh sách thả xuống
$sql_list = "SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5";
$res_list = $conn->query($sql_list);

$html = '';
if ($res_list && $res_list->num_rows > 0) {
    while ($row = $res_list->fetch_assoc()) {
        $html .= '
        <li>
            <a class="dropdown-item p-2 border-bottom" href="/K&A/Frontend/src/pages/notifications.php">
                <div class="fw-bold small text-primary">' . htmlspecialchars($row['title']) . '</div>
                <div class="text-muted small text-truncate" style="max-width: 250px;">' . htmlspecialchars($row['message']) . '</div>
                <div class="text-end text-secondary" style="font-size: 10px;">' . date('d/m H:i', strtotime($row['created_at'])) . '</div>
            </a>
        </li>';
    }
} else {
    $html = '<li><div class="dropdown-item text-center small text-muted py-2">Chưa có thông báo</div></li>';
}

echo json_encode(['count' => $total, 'html' => $html]);
?>