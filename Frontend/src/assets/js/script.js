document.addEventListener('DOMContentLoaded', function () {
    // --- KHAI BÁO CÁC BIẾN CẦN DÙNG ---
    const fileInput = document.getElementById('imgInput');
    const previewArea = document.getElementById('previewArea');
    const postModal = document.getElementById('postModal');
    const form = document.getElementById('listingForm');

    // Tạo "kho chứa ảnh" ảo để quản lý danh sách file
    const dt = new DataTransfer();

    // XỬ LÝ KHI CHỌN ẢNH (Sự kiện Change)
    if (fileInput) {
        // Mẹo: Click vào input thì reset giá trị để có thể chọn lại đúng file vừa chọn (nếu lỡ xóa nhầm)
        fileInput.addEventListener('click', function () {
            this.value = null;
        });

        fileInput.addEventListener('change', function (event) {
            const files = event.target.files;

            if (files && files.length > 0) {
                // Duyệt qua từng file mới chọn
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];

                    // Chỉ lấy file ảnh
                    if (file.type.startsWith('image/')) {
                        // Kiểm tra trùng lặp (Tùy chọn, ở đây tôi cho phép trùng để đơn giản)
                        dt.items.add(file);
                    }
                }

                // CẬP NHẬT INPUT THẬT: Nhét tất cả ảnh trong kho vào input
                fileInput.files = dt.files;

                // Vẽ lại giao diện
                renderPreview();
            }
        });
    }


    //HÀM VẼ GIAO DIỆN PREVIEW
    function renderPreview() {
        if (!previewArea) return;
        previewArea.innerHTML = ''; // Xóa trắng để vẽ lại từ đầu

        // Duyệt qua kho ảnh (dt) để vẽ
        for (let i = 0; i < dt.files.length; i++) {
            const file = dt.files[i];
            const reader = new FileReader();

            reader.onload = function (e) {
                const div = document.createElement('div');
                div.className = 'position-relative d-inline-block me-2 mb-2';
                
                // HTML hiển thị ảnh và nút X
                // Lưu ý: Nút X gọi hàm removeImage(i)
                div.innerHTML = `
                    <img src="${e.target.result}" class="rounded border shadow-sm object-fit-cover" style="width: 100px; height: 100px;">
                    <button type="button" class="btn-close position-absolute top-0 end-0 bg-white rounded-circle shadow" 
                            style="width: 20px; height: 20px; font-size: 10px; margin: 2px;" 
                            onclick="removeImage(${i})"></button>
                `;
                previewArea.appendChild(div);
            };

            reader.readAsDataURL(file);
        }
    }


    // HÀM XÓA ẢNH (Gắn vào Window để HTML gọi được)
    window.removeImage = function (index) {
        dt.items.remove(index); // Xóa khỏi kho
        fileInput.files = dt.files; // Cập nhật lại input thật ngay lập tức
        renderPreview(); // Vẽ lại giao diện
    };

    // RESET FORM KHI ĐÓNG MODAL
    if (postModal) {
        postModal.addEventListener('hidden.bs.modal', function () {
            console.log("Đóng modal -> Dọn dẹp sạch sẽ.");

            // 1. Reset các ô text, select
            if (form) form.reset();

            // 2. Xóa sạch kho ảnh
            dt.items.clear();
            
            // 3. Xóa input file và preview
            if (fileInput) {
                fileInput.value = '';
                fileInput.files = dt.files; // Gán list rỗng
            }
            if (previewArea) {
                previewArea.innerHTML = '';
            }
        });
    }
});






/* --- TYPEWRITER EFFECT CHO Ô TÌM KIẾM --- */
document.addEventListener('DOMContentLoaded', function() {
    // Tìm ô input trong form search
    const searchInput = document.querySelector('.card-advance-search input');
    
    if(searchInput) {
        const phrases = [
            "Đại học Bách Khoa...",
            "Phòng trọ VKU...",
            "Kí túc xá Kinh Tế...",
            "Tìm người ở ghép vui tính..."
        ];
        
        let i = 0;
        let j = 0; 
        let currentPhrase = [];
        let isDeleting = false;
        let isEnd = false;

        function loop () {
            isEnd = false;
            
            if (i < phrases.length) {
                
                if (!isDeleting && j <= phrases[i].length) {
                    currentPhrase.push(phrases[i][j]);
                    j++;
                    searchInput.setAttribute('placeholder', currentPhrase.join(''));
                }

                if(isDeleting && j <= phrases[i].length) {
                    currentPhrase.pop(phrases[i][j]);
                    j--;
                    searchInput.setAttribute('placeholder', currentPhrase.join(''));
                }

                if (j == phrases[i].length) {
                    isEnd = true;
                    isDeleting = true;
                }

                if (isDeleting && j === 0) {
                    currentPhrase = [];
                    isDeleting = false;
                    i++;
                    if (i === phrases.length) {
                        i = 0;
                    }
                }
            }
            
            const time = isEnd ? 2000 : (isDeleting ? 50 : 100);
            setTimeout(loop, time);
        }
        loop();
    }
});

/* --- SCRIPT NÚT BACK TO TOP --- */
document.addEventListener('DOMContentLoaded', () => {
    
    const mybutton = document.getElementById("btn-back-to-top");

    // 1. Lắng nghe sự kiện cuộn chuột
    window.onscroll = function () {
        scrollFunction();
    };

    function scrollFunction() {
        // Nếu cuộn quá 300px thì thêm class 'show' để hiện nút
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            mybutton.classList.add("show");
        } else {
            // Ngược lại thì bỏ class 'show' để ẩn nút
            mybutton.classList.remove("show");
        }
    }

    // 2. Xử lý sự kiện click
    if (mybutton) {
        mybutton.addEventListener("click", backToTop);
    }

    function backToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth' // Cuộn mượt mà thay vì nhảy bụp 1 cái
        });
    }
});