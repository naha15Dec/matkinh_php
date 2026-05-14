<?php
$post = $post ?? [];
$baseUrl = $baseUrl ?? '';

$isEdit = !empty($post['BaiVietId']);
$pageTitle = $title ?? ($isEdit ? 'Cập nhật bài viết' : 'Thêm bài viết mới');

$autoCode = 'BV' . date('ymdHis');
$currentCode = !empty($post['MaBaiViet']) ? $post['MaBaiViet'] : $autoCode;

$imageName = $post['AnhDaiDien'] ?? '';

$login = $_SESSION['LoginInformation'] ?? [];
$formRoleCode = strtoupper(trim($login['MaVaiTro'] ?? ''));
$isAdminBlogForm = $formRoleCode === 'ADMIN';

function blogFormImageSrc($image, $baseUrl)
{
    $image = trim((string)$image);

    if ($image === '') {
        return $baseUrl . '/images/no-image.png';
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    return $baseUrl . '/images/' . ltrim($image, '/');
}

$imageSrc = blogFormImageSrc($imageName, $baseUrl);
$currentStatus = (int)($post['TrangThai'] ?? 1);
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
                                <label class="blog-edit-label">Mã bài viết</label>
                                <input
                                    type="text"
                                    name="MaBaiViet"
                                    class="form-control blog-edit-input"
                                    value="<?= htmlspecialchars($currentCode, ENT_QUOTES, 'UTF-8') ?>"
                                    maxlength="20"
                                    readonly
                                >
                                <small class="text-muted">Mã bài viết dùng để quản lý nội bộ, không được trùng.</small>
                            </div>

                            <div class="form-group">
                                <label class="blog-edit-label">Tiêu đề bài viết</label>
                                <input
                                    type="text"
                                    name="TieuDe"
                                    class="form-control blog-edit-input"
                                    placeholder="Ví dụ: Cách chọn kính phù hợp với khuôn mặt..."
                                    value="<?= htmlspecialchars($post['TieuDe'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    maxlength="250"
                                    required
                                >
                            </div>

                            <div class="form-group mb-0">
                                <label class="blog-edit-label">Nội dung tóm tắt</label>
                                <textarea
                                    name="TomTat"
                                    class="form-control blog-edit-input blog-edit-textarea"
                                    rows="4"
                                    maxlength="500"
                                    placeholder="Nhập mô tả ngắn hiển thị ở danh sách bài viết..."
                                ><?= htmlspecialchars($post['TomTat'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                <small class="text-muted">Tối đa 500 ký tự.</small>
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
                                Hỗ trợ JPG, JPEG, PNG, WEBP. Dung lượng tối đa 3MB.
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

                        <?php
                        $login = $_SESSION['LoginInformation'] ?? [];
                        $formRoleCode = strtoupper(trim($login['MaVaiTro'] ?? ''));
                        $isAdminBlogForm = $formRoleCode === 'ADMIN';
                        ?>

                        <div class="premium-panel-body">

                            <?php if ($isAdminBlogForm): ?>
                                <div class="form-group">
                                    <label class="blog-edit-label">Trạng thái bài viết</label>

                                    <select name="TrangThai" class="form-control blog-edit-input">
                                        <option value="1" <?= $currentStatus === 1 ? 'selected' : '' ?>>
                                            Đã đăng
                                        </option>

                                        <option value="0" <?= $currentStatus === 0 ? 'selected' : '' ?>>
                                            Nháp / Chờ duyệt
                                        </option>

                                        <option value="2" <?= $currentStatus === 2 ? 'selected' : '' ?>>
                                            Ẩn
                                        </option>
                                    </select>
                                </div>

                                <div class="blog-publish-note">
                                    <i class="fas fa-clock mr-1"></i>
                                    Quản trị viên có thể đăng, ẩn hoặc chuyển bài viết về trạng thái nháp/chờ duyệt.
                                </div>
                            <?php else: ?>
                                <input type="hidden" name="TrangThai" value="0">

                                <div class="blog-publish-note">
                                    <i class="fas fa-clock mr-1"></i>
                                    Bài viết của nhân viên sẽ được lưu ở trạng thái nháp/chờ duyệt.
                                    Quản trị viên sẽ kiểm duyệt trước khi đăng.
                                </div>
                            <?php endif; ?>

                            <button type="submit"
                                    class="btn blog-submit-btn btn-block mt-3"
                                    data-confirm
                                    data-confirm-title="<?= $isEdit ? 'Cập nhật bài viết' : 'Tạo bài viết' ?>"
                                    data-confirm-ok="<?= $isEdit ? 'Cập nhật' : 'Tạo bài' ?>">
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
    if (typeof tinymce !== 'undefined') {
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
    }

    const imageInput = document.getElementById('imageAvatar');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) {
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

            if (!allowedTypes.includes(file.type)) {
                alert('Ảnh bài viết chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP.');
                this.value = '';
                return;
            }

            if (file.size > 3 * 1024 * 1024) {
                alert('Dung lượng ảnh bài viết tối đa là 3MB.');
                this.value = '';
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

    const blogForm = document.getElementById('blogForm');

    if (blogForm) {
        blogForm.addEventListener('submit', function () {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }
        });
    }
</script>