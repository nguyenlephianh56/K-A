<?php
require_once __DIR__ . '/../config/db_connect.php';

// Hàm xử lý ảnh riêng cho Ajax (Kiểm tra nếu hàm chưa tồn tại thì mới tạo để tránh lỗi trùng)
if (!function_exists('get_image_path_ajax')) {
    function get_image_path_ajax($path_from_db) {
        if (empty($path_from_db)) return 'https://via.placeholder.com/60?text=No+Img';
        if (strpos($path_from_db, 'http') === 0) return $path_from_db;
        // Đường dẫn tương đối từ Admin Dashboard đến thư mục uploads
        return '../uploads/' . basename($path_from_db);
    }
}

// Query lấy dữ liệu mới nhất
$sql_rooms = "SELECT r.*, u.full_name, 
              (SELECT photo_url FROM room_photos WHERE room_id = r.room_id LIMIT 1) as thumbnail 
              FROM rooms r 
              JOIN users u ON r.owner_id = u.user_id 
              ORDER BY r.created_at DESC";
$res_rooms = $conn->query($sql_rooms);

if ($res_rooms && $res_rooms->num_rows > 0) {
    while ($post = $res_rooms->fetch_assoc()) {
        $p_img = get_image_path_ajax($post['thumbnail']);
        
        $status_text = '';
        $badge_class = 'badge';
        $badge_style = '';

        switch ($post['status']) {
            case 'available':
                $status_text = 'Đang hiện';
                $badge_style = 'background-color: #4A70A9; color: white;';
                break;
            case 'occupied':
                $status_text = 'Đã thuê';
                $badge_class .= ' bg-danger text-white';
                break;
            case 'pending':
                $status_text = 'Tạm ẩn';
                $badge_class .= ' bg-secondary text-white';
                break;
            default:
                $status_text = $post['status'];
                $badge_class .= ' bg-light text-dark';
                break;
        }
?>
    <tr>
        <td class="ps-3"><img src="<?php echo $p_img; ?>" class="rounded border img-thumbnail-admin"></td>
        <td>
            <div class="fw-bold text-truncate" style="max-width: 200px;"><?php echo htmlspecialchars($post['title'] ?? ''); ?></div>
            <small class="text-muted"><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></small>
        </td>
        <td><?php echo number_format($post['price']); ?>đ<br><small><?php echo $post['area']; ?>m²</small></td>
        <td><?php echo htmlspecialchars($post['full_name']); ?></td>
        
        <td><span class="<?php echo $badge_class; ?>" style="<?php echo $badge_style; ?>"><?php echo $status_text; ?></span></td>
        
        <td>
            <button type="button" class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal" 
                    data-bs-target="#editPostModal"
                    data-id="<?php echo $post['room_id']; ?>"
                    data-title="<?php echo htmlspecialchars($post['title'] ?? ''); ?>"
                    data-price="<?php echo $post['price']; ?>"
                    data-area="<?php echo $post['area']; ?>"
                    data-street="<?php echo htmlspecialchars($post['street'] ?? ''); ?>"
                    data-ward="<?php echo htmlspecialchars($post['ward'] ?? ''); ?>"
                    data-city="<?php echo htmlspecialchars($post['city'] ?? ''); ?>"
                    data-status="<?php echo $post['status']; ?>">
                <i class="bi bi-pencil"></i>
            </button>

            <a href="../../../Backend/functions/delete_post.php?id=<?php echo $post['room_id']; ?>" 
               class="btn btn-sm btn-outline-danger"
               onclick="return confirm('CẢNH BÁO: Xóa tin đăng này sẽ xóa luôn CÁC ẢNH của nó. Bạn chắc chắn chứ?');">
                <i class="bi bi-trash"></i>
            </a>
        </td>
    </tr>
<?php 
    }
} else { 
    echo '<tr><td colspan="6" class="text-center py-4">Chưa có tin đăng nào.</td></tr>'; 
}
?>