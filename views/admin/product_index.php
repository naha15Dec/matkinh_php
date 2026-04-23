<?php
// Lấy thông tin role để hiển thị thông tin người tạo nếu là Admin
$userRole = strtoupper($_SESSION['LoginInformation']['MaVaiTro'] ?? '');
$isAdmin = ($userRole === 'ADMIN');
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Danh sách sản phẩm</h1>
        <p class="admin-page-subtitle">Quản lý thông tin kính mắt, giá bán, ảnh đại diện và trạng thái kho.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Danh sách sản phẩm</li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card admin-card mb-3 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="btn-group">
                    <?php $currStatus = $_GET['statusProduct'] ?? 'stock'; ?>
                    <a href="index.php?controller=adminsanpham&statusProduct=stock" class="btn <?= $currStatus == 'stock' ? 'btn-primary' : 'btn-outline-primary' ?>">Còn hàng</a>
                    <a href="index.php?controller=adminsanpham&statusProduct=outofstock" class="btn <?= $currStatus == 'outofstock' ? 'btn-warning' : 'btn-outline-warning' ?>">Hết hàng</a>
                    <a href="index.php?controller=adminsanpham&statusProduct=inactive" class="btn <?= $currStatus == 'inactive' ? 'btn-danger' : 'btn-outline-danger' ?>">Ngừng bán</a>
                </div>

                <form action="index.php" method="GET" class="admin-search-form d-flex">
                    <input type="hidden" name="controller" value="adminsanpham">
                    <input type="hidden" name="statusProduct" value="<?= $currStatus ?>">
                    <div class="admin-search-box">
                        <i class="fas fa-search"></i>
                        <input class="form-control" type="search" name="keyword" value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" placeholder="Tìm theo tên hoặc mã...">
                        <button class="btn admin-search-btn" type="submit">Tìm</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card admin-card shadow-sm">
            <div class="card-header admin-card-header border-0 bg-white d-flex justify-content-between align-items-center">
                <div class="admin-card-title-wrap">
                    <h3 class="admin-card-title">Sản phẩm trong hệ thống</h3>
                    <span class="admin-card-count"><?= count($products) ?> sản phẩm</span>
                </div>
                <a href="index.php?controller=adminsanpham&action=edit" class="btn admin-btn admin-btn-save">
                    <i class="fas fa-plus mr-1"></i> Thêm sản phẩm
                </a>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Sản phẩm</th>
                                <th>Loại / Thương hiệu</th>
                                <th class="text-right">Giá</th>
                                <th class="text-center">Tồn kho</th>
                                <th class="text-center">Nổi bật</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $item): ?>
                            <tr>
                                <td>
                                    <?php if($item['TrangThai'] == 1): ?>
                                        <a href="index.php?controller=product&action=detail&id=<?= $item['MaSanPham'] ?>" target="_blank" class="admin-code-link font-weight-bold">#<?= $item['MaSanPham'] ?></a>
                                    <?php else: ?>
                                        <span class="admin-code-text">#<?= $item['MaSanPham'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="admin-product-cell">
                                        <div class="admin-product-thumb mr-2">
                                            <img src="/BanMatKinh/public/images/<?= $item['HinhAnhChinh'] ?: 'default.jpg' ?>" style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
                                        </div>
                                        <div class="admin-product-info">
                                            <div class="admin-product-name font-weight-bold"><?= htmlspecialchars($item['TenSanPham']) ?></div>
                                            <small class="text-muted"><?= mb_strimwidth($item['MoTaNgan'], 0, 50, "...") ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light border"><?= $item['TenLoaiSanPham'] ?></span><br>
                                    <small class="text-secondary"><?= $item['TenThuongHieu'] ?></small>
                                </td>
                                <td class="text-right">
                                    <div class="admin-price-text text-primary font-weight-bold"><?= number_format($item['GiaBan'], 0, ',', '.') ?> ₫</div>
                                    <small class="text-muted" style="text-decoration: line-through;"><?= number_format($item['GiaGoc'], 0, ',', '.') ?> ₫</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $item['SoLuongTon'] > 0 ? 'badge-info' : 'badge-danger' ?>"><?= $item['SoLuongTon'] ?></span>
                                </td>
                                <td class="text-center">
                                    <form action="index.php?controller=adminsanpham&action=toggleFeatured" method="POST">
                                        <input type="hidden" name="id" value="<?= $item['SanPhamId'] ?>">
                                        <button type="submit" class="btn btn-link p-0">
                                            <i class="<?= $item['IsFeatured'] ? 'fas fa-star text-warning' : 'far fa-star text-secondary' ?>"></i>
                                        </button>
                                    </form>
                                </td>
                                <td class="text-center">
                                    <div class="admin-action-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modal-edit-<?= $item['SanPhamId'] ?>">
                                            <i class="fas fa-pen"></i> Sửa
                                        </button>

                                        <form action="index.php?controller=adminsanpham&action=delete" method="POST" class="d-inline" onsubmit="return confirm('Xóa/Ngừng bán sản phẩm này?')">
                                            <input type="hidden" name="id" value="<?= $item['SanPhamId'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>

                                    <div class="modal fade" id="modal-edit-<?= $item['SanPhamId'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                                            <form action="index.php?controller=adminsanpham&action=save" method="POST" enctype="multipart/form-data" class="modal-content text-left">
                                                <input type="hidden" name="SanPhamId" value="<?= $item['SanPhamId'] ?>">
                                                <div class="modal-header bg-light">
                                                    <h5 class="modal-title font-weight-bold">Chỉnh sửa: <?= $item['TenSanPham'] ?></h5>
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-lg-8">
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">Tên sản phẩm</label>
                                                                <input type="text" name="TenSanPham" class="form-control" value="<?= $item['TenSanPham'] ?>" required>
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">Mô tả ngắn</label>
                                                                <input type="text" name="MoTaNgan" class="form-control" value="<?= $item['MoTaNgan'] ?>">
                                                            </div>
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">Mô tả chi tiết</label>
                                                                <textarea name="MoTaChiTiet" class="admin-textarea"><?= $item['MoTaChiTiet'] ?></textarea>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    <label>Giá gốc</label>
                                                                    <input type="text" name="GiaGoc" class="form-control currency-input" value="<?= $item['GiaGoc'] ?>">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label>Giá bán</label>
                                                                    <input type="text" name="GiaBan" class="form-control currency-input" value="<?= $item['GiaBan'] ?>">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label>Tồn kho</label>
                                                                    <input type="number" name="SoLuongTon" class="form-control" value="<?= $item['SoLuongTon'] ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-lg-4 border-left">
                                                            <div class="form-group">
                                                                <label class="font-weight-bold">Ảnh đại diện</label>
                                                                <div id="preview-<?= $item['SanPhamId'] ?>" class="mb-2 border rounded p-1 text-center" style="height:200px;">
                                                                    <img src="/BanMatKinh/public/images/<?= $item['HinhAnhChinh'] ?: 'default.jpg' ?>" class="img-fluid h-100 object-fit-cover">
                                                                </div>
                                                                <input type="file" name="imageAvatar" class="single-image-input" data-productid="<?= $item['SanPhamId'] ?>">
                                                            </div>
                                                            <hr>
                                                            <div class="form-group">
                                                                <label>Trạng thái</label>
                                                                <select name="TrangThai" class="form-control">
                                                                    <option value="1" <?= $item['TrangThai'] == 1 ? 'selected' : '' ?>>Đang bán</option>
                                                                    <option value="2" <?= $item['TrangThai'] == 2 ? 'selected' : '' ?>>Ngừng bán</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-primary px-4">Lưu thay đổi</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
$(document).ready(function() {
    // Preview ảnh nhanh
    $('.single-image-input').on('change', function() {
        var pid = $(this).data('productid');
        var reader = new FileReader();
        reader.onload = function(e) {
            $('#preview-' + pid + ' img').attr('src', e.target.result);
        }
        reader.readAsDataURL(this.files[0]);
    });

    // Khởi tạo TinyMCE cho class textarea
    tinymce.init({
        selector: '.admin-textarea',
        height: 300,
        plugins: 'lists link image table',
        toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | bullist numlist',
        setup: function(editor) { editor.on('change', function() { editor.save(); }); }
    });

    // Xử lý định dạng tiền tệ khi nhập liệu
    const formatVND = (v) => v.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    
    $('.currency-input').on('input', function() {
        $(this).val(formatVND($(this).val()));
    }).each(function() {
        $(this).val(formatVND($(this).val()));
    });

    // Trước khi submit, xóa dấu chấm để gửi số thuần về server
    $('form').on('submit', function() {
        $(this).find('.currency-input').each(function() {
            $(this).val($(this).val().replace(/\./g, ''));
        });
    });
});
</script>