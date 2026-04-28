<?php
$post = $post ?? [];
$baseUrl = $baseUrl ?? '';

$isEdit = !empty($post['BaiVietId']);
$pageTitle = $title ?? ($isEdit ? 'Cập nhật bài viết' : 'Thêm bài viết mới');

$imageName = $post['AnhDaiDien'] ?? '';
$imageSrc = $imageName
    ? $baseUrl . '/images/' . $imageName
    : $baseUrl . '/images/no-image.png';
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-pen-fancy mr-1"></i>
                Blog Editor
            </span>

            <h1 class="admin-page-title mb-1">
                <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <p class="admin-page-subtitle mb-0">
                Tạo nội dung tư vấn, xu hướng và câu chuyện thương hiệu cho Karma Eyewear.
            </p>
        </div>

        <ol class="breadcrumb admin-breadcrumb mt-3 mt-md-0">
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=adminblog">Bài viết</a>
            </li>
            <li class="breadcrumb-item active">
                <?= $isEdit ? 'Chỉnh sửa' : 'Thêm mới' ?>
            </li>
        </ol>
    </div>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success admin-alert">
                <i class="fas fa-check-circle mr-1"></i>
                <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger admin-alert">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form
            action="<?= $baseUrl ?>/index.php?controller=adminblog&action=save"
            method="POST"
            enctype="multipart/form-data"
            id="blogForm"
        >
            <input type="hidden" name="BaiVietId" value="<?= (int)($post['BaiVietId'] ?? 0) ?>">
            <input type="hidden" name="CurrentAnhDaiDien" value="<?= htmlspecialchars($imageName, ENT_QUOTES, 'UTF-8') ?>">

            <div class="row">

                <div class="col-lg-8 mb-4">
                    <div class="premium-panel blog-edit-panel mb-4">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Main Content</span>
                                <h5 class="mb-0">Thông tin bài viết</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <div class="form-group">
                                <label class="blog-edit-label">Tiêu đề bài viết</label>
                                <input
                                    type="text"
                                    name="TieuDe"
                                    class="form-control blog-edit-input"
                                    placeholder="Ví dụ: Cách chọn kính phù hợp với khuôn mặt..."
                                    value="<?= htmlspecialchars($post['TieuDe'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    required
                                >
                            </div>

                            <div class="form-group mb-0">
                                <label class="blog-edit-label">Nội dung tóm tắt</label>
                                <textarea
                                    name="TomTat"
                                    class="form-control blog-edit-input blog-edit-textarea"
                                    rows="4"
                                    placeholder="Nhập mô tả ngắn hiển thị ở danh sách bài viết..."
                                ><?= htmlspecialchars($post['TomTat'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="premium-panel blog-edit-panel">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Article Body</span>
                                <h5 class="mb-0">Nội dung chi tiết</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <textarea
                                name="NoiDung"
                                id="tinymce-editor"
                                class="blog-editor-textarea"
                            ><?= htmlspecialchars($post['NoiDung'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="premium-panel blog-edit-panel mb-4">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Cover Image</span>
                                <h5 class="mb-0">Ảnh đại diện</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <div id="imagePreviewSingle" class="blog-edit-image-preview">
                                <img
                                    src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>"
                                    alt="Ảnh bài viết"
                                    onerror="this.src='<?= $baseUrl ?>/images/no-image.png'"
                                >
                            </div>

                            <div class="custom-file mt-3">
                                <input
                                    type="file"
                                    name="imageAvatar"
                                    class="custom-file-input"
                                    id="imageAvatar"
                                    accept=".jpg,.jpeg,.png,.webp"
                                >
                                <label class="custom-file-label" for="imageAvatar">
                                    Chọn ảnh đại diện
                                </label>
                            </div>

                            <div class="blog-edit-note mt-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Nên dùng ảnh ngang, rõ sản phẩm hoặc phong cách eyewear boutique.
                            </div>
                        </div>
                    </div>

                    <div class="premium-panel blog-edit-panel">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Publish</span>
                                <h5 class="mb-0">Xuất bản</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <div class="blog-publish-note">
                                <i class="fas fa-clock mr-1"></i>
                                Sau khi lưu, bài viết sẽ được xử lý theo controller `adminblog&action=save`.
                            </div>

                            <button type="submit" class="btn blog-submit-btn btn-block mt-3">
                                <i class="fas fa-paper-plane mr-1"></i>
                                <?= $isEdit ? 'Cập nhật bài viết' : 'Tạo bài viết' ?>
                            </button>

                            <a href="<?= $baseUrl ?>/index.php?controller=adminblog" class="btn blog-cancel-btn btn-block mt-2">
                                Hủy bỏ
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>
</section>

<script src="https://cdn.tiny.cloud/1/zihqpwrk4mgc8xsa9hlg2hm0etuz7f7dh1ovyeioicuygk8v/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    tinymce.init({
        selector: '#tinymce-editor',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | removeformat',
        height: 500,
        branding: false,
        menubar: false,
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });

    const imageInput = document.getElementById('imageAvatar');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) {
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                document.getElementById('imagePreviewSingle').innerHTML =
                    '<img src="' + e.target.result + '" alt="preview">';
            };

            reader.readAsDataURL(file);

            if (this.nextElementSibling) {
                this.nextElementSibling.innerText = file.name;
            }
        });
    }

    document.getElementById('blogForm').addEventListener('submit', function () {
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }
    });
</script>