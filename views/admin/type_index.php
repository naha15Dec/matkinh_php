<?php $isEdit = isset($typeEdit) && $typeEdit !== null; ?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Loại sản phẩm</h1>
        <p class="admin-page-subtitle">Quản lý danh mục loại kính trong hệ thống.</p>
    </div>
    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Loại sản phẩm</li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-lg-7">
                <div class="card admin-card">
                    <div class="card-header admin-card-header border-0 d-flex justify-content-between">
                        <h3 class="admin-card-title">Danh sách loại sản phẩm</h3>
                        <span class="admin-card-count"><?= count($types) ?> loại</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table admin-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Mã loại</th>
                                        <th>Tên loại</th>
                                        <th>Trạng thái</th>
                                        <th>Sản phẩm</th>
                                        <th class="text-center">Lệnh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($types as $item): ?>
                                    <tr>
                                        <td><span class="admin-code-text">#<?= $item['MaLoaiSanPham'] ?></span></td>
                                        <td><div class="font-weight-bold text-dark"><?= $item['TenLoaiSanPham'] ?></div></td>
                                        <td><span class="badge badge-<?= $item['IsActive'] ? 'success' : 'danger' ?>"><?= $item['IsActive'] ? 'Đang dùng' : 'Ngừng dùng' ?></span></td>
                                        <td><span class="text-primary font-weight-bold"><?= $item['SoSanPham'] ?></span> SP</td>
                                        <td class="text-center">
                                            <a href="index.php?controller=admintype&editId=<?= $item['LoaiSanPhamId'] ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                                            <form action="index.php?controller=admintype&action=delete" method="POST" class="d-inline" onsubmit="return confirm('Xóa loại sản phẩm này?')">
                                                <input type="hidden" name="id" value="<?= $item['LoaiSanPhamId'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <form action="index.php?controller=admintype&action=save" method="POST">
                    <input type="hidden" name="LoaiSanPhamId" value="<?= $typeEdit['LoaiSanPhamId'] ?? 0 ?>">
                    <div class="card admin-form-card">
                        <div class="card-header border-0 d-flex justify-content-between">
                            <h3 class="card-title"><?= $isEdit ? "Cập nhật loại" : "Thêm loại" ?></h3>
                            <?php if($isEdit): ?> <a href="index.php?controller=admintype" class="btn btn-sm btn-light">Hủy</a> <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Mã loại sản phẩm</label>
                                <input type="text" name="MaLoaiSanPham" class="form-control admin-input" 
                                    placeholder="Để trống sẽ tự tạo (Ví dụ: KIN)" 
                                    value="<?= htmlspecialchars($typeEdit['MaLoaiSanPham'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Tên loại sản phẩm</label>
                                <input type="text" name="TenLoaiSanPham" class="form-control admin-input" value="<?= $typeEdit['TenLoaiSanPham'] ?? '' ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Trạng thái</label>
                                <select name="IsActive" class="form-control admin-input">
                                    <option value="1" <?= (isset($typeEdit) && $typeEdit['IsActive']) ? 'selected' : '' ?>>Đang sử dụng</option>
                                    <option value="0" <?= (isset($typeEdit) && !$typeEdit['IsActive']) ? 'selected' : '' ?>>Ngừng sử dụng</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block shadow-sm">Lưu loại sản phẩm</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>