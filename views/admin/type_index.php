<?php
$types = $types ?? [];
$typeEdit = $typeEdit ?? null;
$baseUrl = $baseUrl ?? '';

$isEdit = !empty($typeEdit);
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-tags mr-1"></i>
                Category Management
            </span>

            <h1 class="admin-page-title mb-1">Loại sản phẩm</h1>

            <p class="admin-page-subtitle mb-0">
                Quản lý nhóm sản phẩm kính mắt, trạng thái hiển thị và số lượng sản phẩm liên kết.
            </p>
        </div>

        <ol class="breadcrumb admin-breadcrumb mt-3 mt-md-0">
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Loại sản phẩm</li>
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
                <div class="premium-panel type-panel">
                    <div class="premium-panel-header type-panel-header">
                        <div>
                            <span class="admin-kicker">Eyewear Categories</span>
                            <h5 class="mb-0">Danh sách loại sản phẩm</h5>
                        </div>

                        <span class="admin-card-count">
                            <?= count($types) ?> loại
                        </span>
                    </div>

                    <div class="premium-panel-body p-0">
                        <div class="table-responsive">
                            <table class="table type-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Mã loại</th>
                                        <th>Tên loại</th>
                                        <th>Trạng thái</th>
                                        <th class="text-center">Sản phẩm</th>
                                        <th class="text-center">Lệnh</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (!empty($types)): ?>
                                        <?php foreach ($types as $item): ?>
                                            <?php
                                            $id = (int)($item['LoaiSanPhamId'] ?? 0);
                                            $code = $item['MaLoaiSanPham'] ?? '';
                                            $name = $item['TenLoaiSanPham'] ?? '';
                                            $active = !empty($item['IsActive']);
                                            $productCount = (int)($item['SoSanPham'] ?? 0);
                                            ?>

                                            <tr>
                                                <td>
                                                    <span class="type-code">
                                                        #<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </td>

                                                <td>
                                                    <div class="type-name-cell">
                                                        <div class="type-avatar">
                                                            <?= strtoupper(mb_substr($name ?: 'L', 0, 1, 'UTF-8')) ?>
                                                        </div>

                                                        <div>
                                                            <div class="type-name">
                                                                <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                                                            </div>
                                                            <div class="type-meta">
                                                                ID: <?= $id ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <?php if ($active): ?>
                                                        <span class="type-status active">
                                                            <i class="fas fa-circle"></i>
                                                            Đang dùng
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="type-status inactive">
                                                            <i class="fas fa-circle"></i>
                                                            Ngừng dùng
                                                        </span>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="text-center">
                                                    <span class="type-product-count">
                                                        <?= $productCount ?>
                                                    </span>
                                                </td>

                                                <td class="text-center">
                                                    <div class="type-action-group">
                                                        <a href="<?= $baseUrl ?>/index.php?controller=admintype&editId=<?= $id ?>"
                                                           class="btn type-btn type-btn-edit">
                                                            <i class="fas fa-pen mr-1"></i>
                                                            Sửa
                                                        </a>

                                                        <form action="<?= $baseUrl ?>/index.php?controller=admintype&action=delete"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('Bạn có chắc muốn xóa hoặc ngừng sử dụng loại sản phẩm này?')">
                                                            <input type="hidden" name="id" value="<?= $id ?>">

                                                            <button type="submit" class="btn type-btn type-btn-delete">
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
                                            <td colspan="5">
                                                <div class="type-empty-state">
                                                    <div class="type-empty-icon">
                                                        <i class="fas fa-tags"></i>
                                                    </div>
                                                    <h6>Chưa có loại sản phẩm nào</h6>
                                                    <p>Hãy thêm loại sản phẩm đầu tiên cho cửa hàng.</p>
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
                <form action="<?= $baseUrl ?>/index.php?controller=admintype&action=save" method="POST">
                    <input type="hidden" name="LoaiSanPhamId" value="<?= (int)($typeEdit['LoaiSanPhamId'] ?? 0) ?>">

                    <div class="premium-panel type-form-panel">
                        <div class="premium-panel-header type-form-header">
                            <div>
                                <span class="admin-kicker">
                                    <?= $isEdit ? 'Edit Category' : 'New Category' ?>
                                </span>

                                <h5 class="mb-0">
                                    <?= $isEdit ? 'Cập nhật loại sản phẩm' : 'Thêm loại sản phẩm' ?>
                                </h5>
                            </div>

                            <?php if ($isEdit): ?>
                                <a href="<?= $baseUrl ?>/index.php?controller=admintype" class="type-cancel-edit">
                                    Hủy sửa
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="premium-panel-body">
                            <div class="type-form-preview mb-4">
                                <div class="type-form-logo">
                                    <?= strtoupper(mb_substr($typeEdit['TenLoaiSanPham'] ?? 'K', 0, 1, 'UTF-8')) ?>
                                </div>

                                <div>
                                    <h6><?= $isEdit ? 'Đang chỉnh sửa' : 'Tạo loại sản phẩm mới' ?></h6>
                                    <p>Loại sản phẩm dùng để phân nhóm kính trong danh mục bán hàng.</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="type-label">Mã loại sản phẩm</label>
                                <input type="text"
                                       name="MaLoaiSanPham"
                                       class="form-control type-input"
                                       value="<?= htmlspecialchars($typeEdit['MaLoaiSanPham'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="Để trống sẽ tự tạo, ví dụ: KIN">
                                <small class="type-help">Có thể để trống nếu model tự sinh mã.</small>
                            </div>

                            <div class="form-group">
                                <label class="type-label">Tên loại sản phẩm</label>
                                <input type="text"
                                       name="TenLoaiSanPham"
                                       class="form-control type-input"
                                       placeholder="Ví dụ: Kính mát, Kính cận, Gọng kính..."
                                       value="<?= htmlspecialchars($typeEdit['TenLoaiSanPham'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       required>
                            </div>

                            <div class="form-group">
                                <label class="type-label">Trạng thái</label>
                                <select name="IsActive" class="form-control type-input">
                                    <option value="1" <?= (!isset($typeEdit['IsActive']) || (int)$typeEdit['IsActive'] === 1) ? 'selected' : '' ?>>
                                        Đang sử dụng
                                    </option>

                                    <option value="0" <?= (isset($typeEdit['IsActive']) && (int)$typeEdit['IsActive'] === 0) ? 'selected' : '' ?>>
                                        Ngừng sử dụng
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn type-submit-btn btn-block mt-4">
                                <i class="fas fa-save mr-1"></i>
                                <?= $isEdit ? 'Cập nhật loại sản phẩm' : 'Lưu loại sản phẩm' ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>
</section>