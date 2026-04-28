<?php
$brands = $brands ?? [];
$brandEdit = $brandEdit ?? null;
$baseUrl = $baseUrl ?? '';

$isEdit = !empty($brandEdit);
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-crown mr-1"></i>
                Brand Management
            </span>

            <h1 class="admin-page-title mb-1">Quản lý thương hiệu</h1>

            <p class="admin-page-subtitle mb-0">
                Quản lý các thương hiệu mắt kính đang kinh doanh tại Karma Eyewear.
            </p>
        </div>

        <ol class="breadcrumb admin-breadcrumb mt-3 mt-md-0">
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Thương hiệu</li>
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

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="premium-panel brand-panel">
                    <div class="premium-panel-header brand-panel-header">
                        <div>
                            <span class="admin-kicker">Eyewear Brands</span>
                            <h5 class="mb-0">Danh sách thương hiệu</h5>
                        </div>

                        <span class="admin-card-count">
                            <?= count($brands) ?> thương hiệu
                        </span>
                    </div>

                    <div class="premium-panel-body p-0">
                        <div class="table-responsive">
                            <table class="table brand-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Mã</th>
                                        <th>Thương hiệu</th>
                                        <th>Mô tả</th>
                                        <th>Trạng thái</th>
                                        <th class="text-center">Sản phẩm</th>
                                        <th class="text-center">Lệnh</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (!empty($brands)): ?>
                                        <?php foreach ($brands as $item): ?>
                                            <?php
                                            $id = (int)($item['ThuongHieuId'] ?? 0);
                                            $code = $item['MaThuongHieu'] ?? '';
                                            $name = $item['TenThuongHieu'] ?? '';
                                            $desc = $item['MoTa'] ?? '';
                                            $productCount = (int)($item['SoSanPham'] ?? 0);
                                            $active = !empty($item['IsActive']);
                                            ?>

                                            <tr>
                                                <td>
                                                    <span class="brand-code">
                                                        #<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <div class="brand-name-cell">
                                                        <div class="brand-avatar">
                                                            <?= strtoupper(mb_substr($name ?: 'B', 0, 1, 'UTF-8')) ?>
                                                        </div>

                                                        <div>
                                                            <div class="brand-name">
                                                                <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                                                            </div>
                                                            <div class="brand-meta">
                                                                ID: <?= $id ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <span class="brand-desc">
                                                        <?php
                                                        echo htmlspecialchars(
                                                            empty($desc)
                                                                ? 'Chưa có mô tả'
                                                                : (mb_strlen($desc, 'UTF-8') > 60
                                                                    ? mb_substr($desc, 0, 60, 'UTF-8') . '...'
                                                                    : $desc),
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        );
                                                        ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <?php if ($active): ?>
                                                        <span class="brand-status active">
                                                            <i class="fas fa-circle"></i>
                                                            Đang dùng
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="brand-status inactive">
                                                            <i class="fas fa-circle"></i>
                                                            Ngừng dùng
                                                        </span>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="text-center">
                                                    <span class="brand-product-count">
                                                        <?= $productCount ?>
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <div class="brand-action-group">
                                                        <a href="<?= $baseUrl ?>/index.php?controller=adminbrand&editId=<?= $id ?>"
                                                           class="btn brand-btn brand-btn-edit">
                                                            <i class="fas fa-pen mr-1"></i>
                                                            Sửa
                                                        </a>

                                                        <form action="<?= $baseUrl ?>/index.php?controller=adminbrand&action=delete"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('Bạn có chắc muốn xóa hoặc ngừng sử dụng thương hiệu này?')">
                                                            <input type="hidden" name="id" value="<?= $id ?>">

                                                            <button type="submit" class="btn brand-btn brand-btn-delete">
                                                                <i class="fas fa-trash-alt mr-1"></i>
                                                                Xóa
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6">
                                                <div class="brand-empty-state">
                                                    <div class="brand-empty-icon">
                                                        <i class="fas fa-crown"></i>
                                                    </div>
                                                    <h6>Chưa có thương hiệu nào</h6>
                                                    <p>Hãy thêm thương hiệu đầu tiên cho cửa hàng.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 mb-4">
                <form action="<?= $baseUrl ?>/index.php?controller=adminbrand&action=save" method="POST">
                    <input type="hidden" name="ThuongHieuId" value="<?= (int)($brandEdit['ThuongHieuId'] ?? 0) ?>">

                    <div class="premium-panel brand-form-panel">
                        <div class="premium-panel-header brand-form-header">
                            <div>
                                <span class="admin-kicker">
                                    <?= $isEdit ? 'Edit Brand' : 'New Brand' ?>
                                </span>

                                <h5 class="mb-0">
                                    <?= $isEdit ? 'Cập nhật thương hiệu' : 'Thêm thương hiệu' ?>
                                </h5>
                            </div>

                            <?php if ($isEdit): ?>
                                <a href="<?= $baseUrl ?>/index.php?controller=adminbrand" class="brand-cancel-edit">
                                    Hủy sửa
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="premium-panel-body">
                            <div class="brand-form-preview mb-4">
                                <div class="brand-form-logo">
                                    <?= strtoupper(mb_substr($brandEdit['TenThuongHieu'] ?? 'K', 0, 1, 'UTF-8')) ?>
                                </div>

                                <div>
                                    <h6><?= $isEdit ? 'Đang chỉnh sửa' : 'Tạo thương hiệu mới' ?></h6>
                                    <p>Thông tin thương hiệu sẽ hiển thị trong khu vực quản lý sản phẩm.</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="brand-label">Mã thương hiệu</label>
                                <input type="text"
                                       name="MaThuongHieu"
                                       class="form-control brand-input"
                                       value="<?= htmlspecialchars($brandEdit['MaThuongHieu'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="Ví dụ: RB, PRADA, GUCCI">
                                <small class="brand-help">Có thể để trống, hệ thống sẽ tự tạo từ tên thương hiệu.</small>
                            </div>

                            <div class="form-group">
                                <label class="brand-label">Tên thương hiệu</label>
                                <input type="text"
                                       name="TenThuongHieu"
                                       class="form-control brand-input"
                                       placeholder="Tên thương hiệu"
                                       value="<?= htmlspecialchars($brandEdit['TenThuongHieu'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       required>
                            </div>

                            <div class="form-group">
                                <label class="brand-label">Mô tả</label>
                                <textarea name="MoTa"
                                          class="form-control brand-input brand-textarea"
                                          rows="4"
                                          placeholder="Mô tả ngắn về phong cách, xuất xứ hoặc phân khúc thương hiệu..."><?= htmlspecialchars($brandEdit['MoTa'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="brand-label">Trạng thái</label>
                                <select name="IsActive" class="form-control brand-input">
                                    <option value="1" <?= (!isset($brandEdit['IsActive']) || (int)$brandEdit['IsActive'] === 1) ? 'selected' : '' ?>>
                                        Đang sử dụng
                                    </option>
                                    <option value="0" <?= (isset($brandEdit['IsActive']) && (int)$brandEdit['IsActive'] === 0) ? 'selected' : '' ?>>
                                        Ngừng sử dụng
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn brand-submit-btn btn-block mt-4">
                                <i class="fas fa-save mr-1"></i>
                                <?= $isEdit ? 'Cập nhật thương hiệu' : 'Lưu thương hiệu' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</section>