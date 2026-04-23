<?php
// Kiểm tra xem có đang ở chế độ chỉnh sửa hay không
$isEdit = isset($brandEdit) && $brandEdit !== null;
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Thương hiệu</h1>
        <p class="admin-page-subtitle">Quản lý danh sách thương hiệu kính mắt trong hệ thống.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Thương hiệu</li>
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

        <div class="row">
            <div class="col-lg-7">
                <div class="card admin-card shadow-sm">
                    <div class="card-header admin-card-header border-0">
                        <div class="admin-card-title-wrap">
                            <h3 class="admin-card-title">Danh sách thương hiệu</h3>
                            <span class="admin-card-count"><?= count($brands) ?> thương hiệu</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table admin-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Mã thương hiệu</th>
                                        <th>Tên</th>
                                        <th>Mô tả</th>
                                        <th>Trạng thái</th>
                                        <th>Sản phẩm</th>
                                        <th class="text-center">Lệnh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($brands)): ?>
                                        <?php foreach ($brands as $item): ?>
                                            <tr>
                                                <td><span class="admin-code-text">#<?= $item['MaThuongHieu'] ?></span></td>
                                                <td><div class="admin-brand-name font-weight-bold"><?= htmlspecialchars($item['TenThuongHieu']) ?></div></td>
                                                <td>
                                                    <span class="admin-post-summary small text-muted">
                                                        <?= empty($item['MoTa']) ? "Chưa có mô tả" : (mb_strlen($item['MoTa']) > 60 ? mb_substr($item['MoTa'], 0, 60) . "..." : $item['MoTa']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($item['IsActive']): ?>
                                                        <span class="badge badge-success admin-status-badge">Đang sử dụng</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary admin-status-badge">Ngừng sử dụng</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="admin-origin-text font-weight-bold text-primary"><?= $item['SoSanPham'] ?></span></td>
                                                <td class="text-center">
                                                    <div class="admin-action-group">
                                                        <a href="index.php?controller=adminbrand&editId=<?= $item['ThuongHieuId'] ?>" class="btn btn-sm btn-outline-primary admin-btn-edit">
                                                            <i class="fas fa-pen mr-1"></i> Sửa
                                                        </a>

                                                        <form action="index.php?controller=adminbrand&action=delete" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa hoặc ngừng sử dụng thương hiệu này?')">
                                                            <input type="hidden" name="id" value="<?= $item['ThuongHieuId'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger admin-btn-delete">
                                                                <i class="fas fa-trash-alt mr-1"></i> Xóa
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center py-4">Không có thương hiệu nào.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <form action="index.php?controller=adminbrand&action=save" method="POST">
                    <input type="hidden" name="ThuongHieuId" value="<?= $brandEdit['ThuongHieuId'] ?? 0 ?>">

                    <div class="card admin-form-card shadow-sm">
                        <div class="card-header border-0 d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0"><?= $isEdit ? "Cập nhật thương hiệu" : "Thêm thương hiệu" ?></h3>
                            <?php if ($isEdit): ?>
                                <a href="index.php?controller=adminbrand" class="btn btn-sm btn-light">Hủy sửa</a>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="form-group">
                                <label>Mã thương hiệu (Để trống sẽ tự tạo)</label>
                                <input type="text" name="MaThuongHieu" class="form-control admin-input" 
                                    value="<?= $brandEdit['MaThuongHieu'] ?? '' ?>" placeholder="Ví dụ: RB">
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Tên thương hiệu</label>
                                <input type="text" name="TenThuongHieu" class="form-control admin-input" 
                                       placeholder="Tên thương hiệu" value="<?= htmlspecialchars($brandEdit['TenThuongHieu'] ?? '') ?>" required>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Mô tả</label>
                                <textarea name="MoTa" class="form-control admin-input" rows="4" 
                                          placeholder="Mô tả thương hiệu..."><?= htmlspecialchars($brandEdit['MoTa'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Trạng thái</label>
                                <select name="IsActive" class="form-control admin-input">
                                    <option value="1" <?= (isset($brandEdit) && $brandEdit['IsActive'] == 1) ? 'selected' : '' ?>>Đang sử dụng</option>
                                    <option value="0" <?= (isset($brandEdit) && $brandEdit['IsActive'] == 0) ? 'selected' : '' ?>>Ngừng sử dụng</option>
                                </select>
                            </div>

                            <div class="admin-form-actions mt-4">
                                <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                    <i class="fas fa-save mr-1"></i>
                                    <?= $isEdit ? "Cập nhật thương hiệu" : "Lưu thương hiệu" ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>