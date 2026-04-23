<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Danh sách bài viết của tôi</h1>
        <p class="admin-page-subtitle">Quản lý bài viết và trạng thái nội dung của tài khoản hiện tại.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Danh sách bài viết</li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">
        <div class="card admin-card shadow-sm">
            <div class="card-header admin-card-header border-0 d-flex justify-content-between align-items-center">
                <div class="admin-card-title-wrap">
                    <h3 class="admin-card-title">Danh sách bài viết</h3>
                    <span class="admin-card-count"><?= count($posts) ?> bài viết</span>
                </div>

                <div class="admin-card-tools">
                    <form action="index.php" method="GET" class="admin-search-form">
                        <input type="hidden" name="controller" value="adminblog">
                        <input type="hidden" name="status" value="<?= $_GET['status'] ?? 'published' ?>">
                        <div class="admin-search-box d-flex">
                            <i class="fas fa-search pt-2"></i>
                            <input class="form-control" type="search" name="keyword" 
                                   value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" placeholder="Nhập tiêu đề bài viết..." />
                            <button class="btn admin-search-btn" type="submit">Tìm</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body">
                <div class="mb-3 text-left">
                    <a href="index.php?controller=adminblog&action=edit" class="btn btn-success">
                        <i class="fas fa-plus mr-1"></i> Thêm bài viết
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table admin-table align-middle">
                        <thead>
                            <tr>
                                <th>Mã bài viết</th>
                                <th>Tên bài viết</th>
                                <th>Ngày tạo / đăng</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Lệnh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($posts)): ?>
                                <?php foreach ($posts as $item): ?>
                                    <tr>
                                        <td><span class="admin-code-text">#<?= $item['MaBaiViet'] ?></span></td>
                                        <td>
                                            <div class="admin-post-title font-weight-bold"><?= htmlspecialchars($item['TieuDe']) ?></div>
                                            <div class="admin-post-summary text-muted small">
                                                <?= empty($item['TomTat']) ? "Chưa có mô tả tóm tắt" : (mb_strlen($item['TomTat']) > 70 ? mb_substr($item['TomTat'], 0, 70) . "..." : $item['TomTat']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="admin-date-text small"><b>Tạo:</b> <?= date('d/m/Y H:i', strtotime($item['CreatedAt'])) ?></div>
                                            <div class="admin-post-summary small"><b>Đăng:</b> <?= $item['NgayDang'] ? date('d/m/Y H:i', strtotime($item['NgayDang'])) : 'Chưa đăng' ?></div>
                                        </td>
                                        <td>
                                            <?php 
                                                $badgeClass = ($item['TrangThai'] == 1) ? 'success' : (($item['TrangThai'] == 0) ? 'processing' : 'cancel');
                                                $statusText = ($item['TrangThai'] == 1) ? 'Đã đăng' : (($item['TrangThai'] == 0) ? 'Nháp' : 'Ẩn');
                                            ?>
                                            <span class="admin-status-badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="admin-action-group d-flex justify-content-center" style="gap:5px">
                                                
                                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#postModal-<?= $item['BaiVietId'] ?>">
                                                    <i class="fas fa-pen mr-1"></i> Sửa
                                                </button>

                                                <form action="index.php?controller=adminblog&action=delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa bài viết này?')">
                                                    <input type="hidden" name="id" value="<?= $item['BaiVietId'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt mr-1"></i> Xóa</button>
                                                </form>
                                            </div>

                                            <div class="modal fade" id="postModal-<?= $item['BaiVietId'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
                                                    <div class="modal-content text-left">
                                                        <form action="index.php?controller=adminblog&action=save" method="POST" enctype="multipart/form-data">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title font-weight-bold">Chỉnh sửa bài viết #<?= $item['MaBaiViet'] ?></h5>
                                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                            </div>
                                                            <div class="modal-body bg-light">
                                                                <input type="hidden" name="BaiVietId" value="<?= $item['BaiVietId'] ?>">
                                                                <input type="hidden" name="CurrentAnhDaiDien" value="<?= $item['AnhDaiDien'] ?>">
                                                                
                                                                <div class="row">
                                                                    <div class="col-lg-7">
                                                                        <div class="card p-3 mb-3">
                                                                            <div class="form-group">
                                                                                <label>Tên bài viết</label>
                                                                                <input type="text" name="TieuDe" class="form-control" value="<?= htmlspecialchars($item['TieuDe']) ?>" required>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label>Nội dung tóm tắt</label>
                                                                                <textarea name="TomTat" class="form-control" rows="3"><?= htmlspecialchars($item['TomTat']) ?></textarea>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label>Nội dung chi tiết</label>
                                                                                <textarea name="NoiDung" class="tinymce-editor"><?= $item['NoiDung'] ?></textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-lg-5">
                                                                        <div class="card p-3 mb-3 text-center">
                                                                            <label>Ảnh đại diện</label>
                                                                            <div id="imagePreview-<?= $item['BaiVietId'] ?>" class="mb-2">
                                                                                <img src="/BanMatKinh/public/images/<?= $item['AnhDaiDien'] ?: 'no-image.png' ?>" style="max-width: 100%; border-radius: 5px;">
                                                                            </div>
                                                                            <input type="file" name="imageAvatar" class="form-control-file post-image-input" data-postid="<?= $item['BaiVietId'] ?>">
                                                                        </div>
                                                                        <div class="alert alert-warning small">
                                                                            Lưu ý: Thay đổi nội dung sẽ cần được Admin duyệt lại nếu bạn đang sửa bài nháp.
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                                                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Lưu thay đổi</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4">Không có bài viết nào.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.tiny.cloud/1/zihqpwrk4mgc8xsa9hlg2hm0etuz7f7dh1ovyeioicuygk8v/tinymce/6/tinymce.min.js"></script>
<script>
$(document).ready(function () {
    // Preview ảnh cho Modal
    $(document).on('change', '.post-image-input', function () {
        var postId = $(this).data('postid');
        var input = this;
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#imagePreview-' + postId).html('<img src="' + e.target.result + '" style="max-width:100%;" />');
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    // Thêm vào trước khi khởi tạo tinymce.init
$(document).on('focusin', function(e) {
    if ($(e.target).closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root").length) {
        e.stopImmediatePropagation();
    }
});

// Đảm bảo dữ liệu editor được save vào textarea trước khi form submit
$('form').on('submit', function() {
    tinymce.triggerSave();
});

    // Khởi tạo TinyMCE cho class tinymce-editor
    tinymce.init({
        selector: '.tinymce-editor',
        plugins: 'link image lists table media',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist',
        height: 350
    });
});
</script>