<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= $title ?></h1>
        <p class="admin-page-subtitle">Tạo nội dung bài viết mới cho website.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?controller=adminblog">Quản lý bài viết</a></li>
        <li class="breadcrumb-item active"><?= $title ?></li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">
        <form action="index.php?controller=adminblog&action=save" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="BaiVietId" value="<?= $post['BaiVietId'] ?? 0 ?>">
            <input type="hidden" name="CurrentAnhDaiDien" value="<?= $post['AnhDaiDien'] ?? '' ?>">

            <div class="row">
                <div class="col-lg-7">
                    <div class="card admin-form-card mb-4 shadow-sm">
                        <div class="card-header border-0 bg-white">
                            <h3 class="card-title font-weight-bold">Thông tin chính</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Tiêu đề bài viết</label>
                                <input type="text" name="TieuDe" class="form-control admin-input" 
                                       placeholder="Nhập tiêu đề..." value="<?= htmlspecialchars($post['TieuDe'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label>Nội dung tóm tắt</label>
                                <textarea name="TomTat" class="form-control admin-input" rows="3" 
                                          placeholder="Tóm tắt ngắn..."><?= htmlspecialchars($post['TomTat'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card admin-form-card shadow-sm">
                        <div class="card-header border-0 bg-white">
                            <h3 class="card-title font-weight-bold">Nội dung chi tiết bài viết</h3>
                        </div>
                        <div class="card-body">
                            <textarea name="NoiDung" id="tinymce-editor" class="admin-textarea">
                                <?= $post['NoiDung'] ?? '' ?>
                            </textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card admin-form-card mb-4 shadow-sm">
                        <div class="card-header border-0 bg-white">
                            <h3 class="card-title font-weight-bold">Ảnh đại diện bài viết</h3>
                        </div>
                        <div class="card-body">
                            <div id="imagePreviewSingle" class="admin-image-preview-single border rounded d-flex align-items-center justify-content-center" style="height: 250px; overflow: hidden; background: #f8f9fa;">
                                <?php if(!empty($post['AnhDaiDien'])): ?>
                                    <img src="/BanMatKinh/public/images/<?= $post['AnhDaiDien'] ?>" style="width: 100%; height: 100%; object-fit: cover;" />
                                <?php else: ?>
                                    <span class="admin-image-placeholder text-muted">Xem trước ảnh đại diện</span>
                                <?php endif; ?>
                            </div>

                            <div class="input-group mt-3">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="inputSingleFile" name="imageAvatar" onchange="previewImage(this)">
                                    <label class="custom-file-label" for="inputSingleFile">Chọn ảnh</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card admin-form-card shadow-sm">
                        <div class="card-header border-0 bg-white">
                            <h3 class="card-title font-weight-bold">Xuất bản</h3>
                        </div>
                        <div class="card-body">
                            <div class="admin-publish-note p-2 mb-3 rounded" style="background: #fff3cd; border-left: 4px solid #ffc107;">
                                <small>Bài viết sẽ được lưu dưới dạng <b>nháp</b> và chờ admin duyệt.</small>
                            </div>

                            <div class="admin-form-actions mt-3">
                                <button type="submit" class="btn btn-primary btn-block btn-lg">
                                    <i class="fas fa-paper-plane mr-1"></i> <?= isset($post) ? 'Cập nhật bài viết' : 'Tạo bài viết' ?>
                                </button>
                                <a href="index.php?controller=adminblog" class="btn btn-link btn-block text-muted">Hủy bỏ</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script src="https://cdn.tiny.cloud/1/zihqpwrk4mgc8xsa9hlg2hm0etuz7f7dh1ovyeioicuygk8v/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    // Khởi tạo trình soạn thảo TinyMCE
    tinymce.init({
        selector: '#tinymce-editor',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 500,
        language: 'vi', // Nếu muốn tiếng Việt
        setup: function (editor) {
            editor.on('change', function () {
                editor.save(); // Tự động đồng bộ dữ liệu sang textarea khi có thay đổi
            });
        }
    });

    $('form').on('submit', function() {
        tinymce.triggerSave();
    })

    // Hàm preview ảnh khi chọn file
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreviewSingle').innerHTML = 
                    '<img src="' + e.target.result + '" style="width: 100%; height: 100%; object-fit: cover;" />';
                // Cập nhật tên file vào label
                $(input).next('.custom-file-label').html(input.files[0].name);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>