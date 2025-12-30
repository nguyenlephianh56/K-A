<div class="modal fade" id="modalCanhBao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" >
            <div class="modal-header " style="background-color: #8FABD4;">
                <h5 class="modal-title fw-bold">Thông báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <p>Bạn cần đăng nhập để thực hiện chức năng <b>Đăng tin</b>!</p>
                <a href="/K&A/Frontend/src/pages/login.php" class="btn btn-primary"style="background-color: #8FABD4;">Đăng nhập ngay</a>
            </div>
        </div>
    </div>
</div>


<?php
// LẤY DANH SÁCH LOẠI PHÒNG
$sql_types = "SELECT * FROM room_types ORDER BY name ASC";
$res_types = $conn->query($sql_types);
$room_types = [];
if ($res_types) {
    while ($row = $res_types->fetch_assoc()) $room_types[] = $row;
}

// LẤY DANH SÁCH TIỆN ÍCH TỪ CSDL
$sql_amenities = "SELECT * FROM amenities";
$res_amenities = $conn->query($sql_amenities);
$list_amenities = [];
if ($res_amenities) {
    while ($row = $res_amenities->fetch_assoc()) $list_amenities[] = $row;
}
?>

<div class="modal fade" id="postModal" tabindex="-1" aria-labelledby="postModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-center w-100 fs-4" id="postModalLabel">Tạo tin đăng mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body pt-2">
                <form id="listingForm" 
      action="<?php echo str_replace('Frontend/', '', $base_url); ?>Backend/functions/post_room_handler.php" 
      method="POST" 
      enctype="multipart/form-data"
      onsubmit="updateImagesBeforeSubmit()">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white me-2" style="width: 40px; height: 40px;">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark"><?php echo $_SESSION['user']['fullname'] ?? $_SESSION['user']['full_name'] ?? $_SESSION['user']['username'] ?? 'Bạn';?></div>
                            <div class="badge bg-light text-secondary border rounded-pill px-2">Công khai</div>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" name="title" class="form-control fw-bold fs-5 border-0 border-bottom rounded-0 shadow-none ps-0" id="postTitle" placeholder="Tiêu đề" required>
                        <label for="postTitle" class="ps-0">Tiêu đề bài đăng</label>
                    </div>

                    <div class="row g-2 mb-3">
                        
                        <div class="col-md-4">
                            <label class="fw-bold small text-muted">LOẠI PHÒNG</label>
                            <select name="room_type_id" class="form-select" required>
                                <?php foreach ($room_types as $type): ?>
                                    <option value="<?php echo $type['room_type_id']; ?>">
                                        <?php echo $type['name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="fw-bold small text-muted">GIÁ (VNĐ)</label>
                            <input type="number" name="price" class="form-control" placeholder="Ví dụ: 2000000" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold small text-muted">DIỆN TÍCH (m²)</label>
                            <input type="number" name="area" class="form-control" placeholder="Ví dụ: 25" min="1" required>
                        </div>
                    </div>

                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold small text-muted mb-3"><i class="bi bi-geo-alt-fill me-1"></i> ĐỊA CHỈ</h6>
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" name="city" class="form-control bg-white" value="TP. Đà Nẵng" readonly>
                                        <label>Thành phố</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <select name="ward" class="form-select" required>
                                            <option value="" selected disabled>Chọn Quận/Huyện</option>
                                            <option value="Hải Châu">Q. Hải Châu</option>
                                            <option value="Thanh Khê">Q. Thanh Khê</option>
                                            <option value="Sơn Trà">Q. Sơn Trà</option>
                                            <option value="Ngũ Hành Sơn">Q. Ngũ Hành Sơn</option>
                                            <option value="Liên Chiểu">Q. Liên Chiểu</option>
                                            <option value="Cẩm Lệ">Q. Cẩm Lệ</option>
                                            <option value="Hòa Vang">H. Hòa Vang</option>
                                        </select>
                                        <label>Quận/Huyện</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="text" name="street" class="form-control" placeholder="Số nhà, đường" required>
                                        <label>Số nhà, Tên đường</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small text-muted mb-2">TIỆN ÍCH CÓ SẴN</label>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($list_amenities as $am): ?>
                                <input type="checkbox" name="amenities[]" value="<?php echo $am['amenity_id']; ?>" 
                                       class="btn-check" id="am_<?php echo $am['amenity_id']; ?>">
                                
                                <label class="btn btn-outline-secondary btn-sm rounded-pill d-flex align-items-center" 
                                       for="am_<?php echo $am['amenity_id']; ?>">
                                    
                                    <?php if(!empty($am['icon_url']) && strpos($am['icon_url'], 'bi-') !== false): ?>
                                        <i class="<?php echo $am['icon_url']; ?> me-1"></i>
                                    <?php endif; ?>
                                    
                                    <?php echo $am['name']; ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold small text-muted mb-1">MÔ TẢ CHI TIẾT</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Mô tả chi tiết về phòng, giờ giấc, quy định..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
    <label class="fw-bold small text-muted mb-2">HÌNH ẢNH</label>
    <div class="border rounded-3 p-4 text-center bg-light">
        <label class="btn btn-primary rounded-pill fw-bold px-4 shadow-sm cursor-pointer">
            <i class="bi bi-camera-fill me-1"></i> Tải ảnh từ máy
            
            <input type="file" name="room_images[]" id="imgInput" multiple accept="image/*" class="d-none">
        </label>
    </div>
    <div class="d-flex flex-wrap gap-2 mt-3" id="previewArea"></div>
</div>

                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light flex-grow-1 fw-bold" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit"  class="btn btn-primary flex-grow-1 fw-bold">Đăng tin ngay</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<button type="button" class="btn btn-floating btn-lg" id="btn-back-to-top">
    <i class="bi bi-arrow-up"></i>
</button>

 <footer class="footer text-black mt-3 pt-5 pb-2"style="background-color: #D3E2F4;">
      <div class="container">
        <div class="row">
          <div class="col-md-4 mb-3">
            <h5>Web cho thuê phòng trọ K&A</h5>
            <p>
                Chúng tôi cam kết mang đến cho bạn những lựa chọn
                bất động sản tốt nhất với dịch vụ chuyên nghiệp và tận
                tâm.
            </p>
            <p>
              <i class="fas fa-map-marker-alt"></i> 470 Đ.Trần Đại Nghĩa, Hoà
              Hải, Ngũ Hành Sơn, Đà Nẵng <br />
              <i class="fas fa-phone"></i> 0773351082 <br />
              <i class="fas fa-envelope"></i> anhnlp@vku.udn.vn
            </p>
          </div>
          <div class="col-md-2 mb-3">
            <h5>Về Chúng Tôi</h5>
            <ul class="list-unstyled">
              <li>
                <a href="about.html" class="text-black text-decoration-none"
                  >Giới thiệu</a
                >
              </li>
              <li>
                <a href="#" class="text-black text-decoration-none"
                  >Tuyển dụng</a
                >
              </li>
              <li>
                <a href="#" class="text-black text-decoration-none"
                  >Điều khoản</a
                >
              </li>
              <li>
                <a href="#" class="text-black text-decoration-none"
                  >Chính sách bảo mật</a
                >
              </li>
            </ul>
          </div>
          <div class="col-md-3 mb-3">
            <h5>Hỗ Trợ Khách Hàng</h5>
            <ul class="list-unstyled">
              <li>
                <a href="contact.html" class="text-black text-decoration-none"
                  >Liên hệ</a
                >
              </li>
              <li>
                <a href="#" class="text-black text-decoration-none"
                  >Hướng dẫn mua hàng</a
                >
              </li>
              <li>
                <a href="#" class="text-black text-decoration-none"
                  >Chính sách đổi trả</a
                >
              </li>
              <li>
                <a href="#" class="text-black text-decoration-none"
                  >Câu hỏi thường gặp</a
                >
              </li>
            </ul>
          </div>
          <div class="col-md-3 mb-3">
            <h5>Kết Nối Với Chúng Tôi</h5>
    <a href="https://www.facebook.com/Pnh2k6" class="text-black me-2" target="_blank">
    <i class="bi bi-facebook fs-4"></i>
    </a>
    <a href="https://www.instagram.com/_nlp.a56/" class="text-black me-2" target="_blank">
    <i class="bi bi-instagram fs-4"></i>
    </a>
    <a href="#" class="text-black">
    <i class="bi bi-tiktok fs-4"></i>
    </a>
            <h5 class="mt-3">Đăng Ký Nhận Tin</h5>
            <form>
              <div class="input-group mb-3">
                <input
                  type="email"
                  class="form-control"
                  placeholder="Email của bạn"
                  aria-label="Email của bạn"
                />
                <button class="btn btn-primary" type="button">Đăng ký</button>
              </div>
            </form>
          </div>
        </div>
        <hr class="mt-2 mb-3" />
        <div class="row">
          <div class="col text-center">
            <p>
              &copy; 2025 Web tìm phòng trọ K&A <br />
              Uy tín, chất lượng hàng đầu miền Trung-Tây Nguyên.
            </p>
          </div>
        </div>
      </div>
    </footer>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Bắt sự kiện click trên toàn bộ body (Event Delegation)
    // Giúp code chạy đúng kể cả khi nội dung được tải bằng Ajax
    document.body.addEventListener('click', function(e) {
        
        // Kiểm tra xem cái được bấm có phải là nút .btn-toggle-fav không
        const btn = e.target.closest('.btn-toggle-fav');
        
        if (btn) {
            e.preventDefault(); // Không chuyển trang
            e.stopPropagation(); // Không đụng vào thẻ cha

            const roomId = btn.getAttribute('data-id');
            const icon = btn.querySelector('i');

            // TẠO ĐƯỜNG DẪN TUYỆT ĐỐI (Dùng PHP để in ra)
            // Biến đổi: .../Frontend/ -> .../Backend/function/ajax_favorite.php
            const ajaxUrl = "http://localhost/K&A/Backend/functions/ajax_favorite.php";
            const loginUrl = "<?php echo $base_url; ?>public/login.php";

            // Gửi dữ liệu
            const formData = new FormData();
            formData.append('room_id', roomId);

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'error') {
                    // Nếu lỗi (chưa đăng nhập)
                    alert(data.message);
                    if(data.message.includes('đăng nhập')) {
                        window.location.href = loginUrl;
                    }
                } else if (data.status === 'success') {
                    
                    // Cập nhật giao diện nút tim ngay lập tức
                    if (data.action === 'added') {
                        icon.className = 'bi bi-heart-fill text-danger fs-5';
                    } else {
                        icon.className = 'bi bi-heart text-secondary fs-5';
                    }

                    // QUAN TRỌNG: Tải lại trang để cập nhật danh sách trên Navbar
                    location.reload(); 
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                alert('Có lỗi xảy ra, vui lòng thử lại.');
            });
        }
    });
});
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>