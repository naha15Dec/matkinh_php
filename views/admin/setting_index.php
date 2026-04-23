<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Thông tin cửa hàng</h1>
        <p class="admin-page-subtitle">Cập nhật thông tin liên hệ và theo dõi lịch sử thay đổi của hệ thống.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Thông tin cửa hàng</li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success shadow-sm"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-7">
                <div class="card admin-card shadow-sm border-0">
                    <div class="card-header admin-card-header border-0 bg-white">
                        <div class="admin-card-title-wrap">
                            <h3 class="admin-card-title">Lịch sử thay đổi</h3>
                            <span class="admin-card-count"><?= count($history) ?> lần cập nhật</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table admin-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Mã</th>
                                        <th>Người cập nhật</th>
                                        <th>Thời gian</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($history)): ?>
                                        <?php foreach ($history as $item): ?>
                                            <tr>
                                                <td><span class="admin-code-text">#<?= $item['ThongTinCuaHangId'] ?></span></td>
                                                <td>
                                                    <div class="admin-order-user d-flex align-items-center">
                                                        <div class="admin-order-user-icon mr-2">
                                                            <i class="fas fa-user-edit text-primary"></i>
                                                        </div>
                                                        <span><?= htmlspecialchars($item['HoTen'] ?? 'Admin') ?></span>
                                                    </div>
                                                </td>
                                                <td><span class="admin-date-text"><?= date('d/m/Y H:i', strtotime($item['UpdatedAt'])) ?></span></td>
                                                <td class="text-center">
                                                    <div class="admin-action-group">
                                                        <button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#historyModal<?= $item['ThongTinCuaHangId'] ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>

                                                        <form action="index.php?controller=adminsetting&action=deleteHistory" method="POST" class="d-inline" onsubmit="return confirm('Xóa bản ghi lịch sử này?')">
                                                            <input type="hidden" name="id" value="<?= $item['ThongTinCuaHangId'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    </div>

                                                    <div class="modal fade" id="historyModal<?= $item['ThongTinCuaHangId'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                                            <div class="modal-content text-left">
                                                                <div class="modal-header bg-light">
                                                                    <h5 class="modal-title font-weight-bold">Chi tiết bản ghi #<?= $item['ThongTinCuaHangId'] ?></h5>
                                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                </div>
                                                                <div class="modal-body small">
                                                                    <p><strong>Cửa hàng:</strong> <?= $item['TenCuaHang'] ?></p>
                                                                    <p><strong>Hotline:</strong> <?= $item['Hotline'] ?></p>
                                                                    <p><strong>Địa chỉ:</strong> <?= $item['DiaChi'] ?></p>
                                                                    <p><strong>Mô tả:</strong> <?= $item['MoTaNgan'] ?></p>
                                                                    <hr>
                                                                    <p class="mb-0 text-muted italic">Cập nhật lúc: <?= date('d/m/Y H:i:s', strtotime($item['UpdatedAt'])) ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center py-4 text-muted">Chưa có lịch sử thay đổi nào.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <form action="index.php?controller=adminsetting&action=save" method="POST">
                    <div class="card admin-form-card shadow-sm border-0">
                        <div class="card-header border-0 bg-white pt-4">
                            <h3 class="card-title font-weight-bold">Cập nhật thông tin hiện tại</h3>
                        </div>
                        <div class="card-body">
                            <div class="admin-store-meta-box p-2 bg-light rounded mb-3">
                                <small class="text-muted">Tài khoản thực hiện:</small>
                                <strong class="text-primary"><?= $_SESSION['LoginInformation']['TenDangNhap'] ?></strong>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Tên cửa hàng</label>
                                <input type="text" name="TenCuaHang" class="form-control admin-input" value="<?= $currentInfo['TenCuaHang'] ?? '' ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Hotline</label>
                                    <input type="text" name="Hotline" class="form-control admin-input" value="<?= $currentInfo['Hotline'] ?? '' ?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Email</label>
                                    <input type="email" name="Email" class="form-control admin-input" value="<?= $currentInfo['Email'] ?? '' ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Địa chỉ</label>
                                <input type="text" name="DiaChi" class="form-control admin-input" value="<?= $currentInfo['DiaChi'] ?? '' ?>">
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Mô tả ngắn (SEO)</label>
                                <textarea name="MoTaNgan" class="form-control admin-input" rows="2"><?= $currentInfo['MoTaNgan'] ?? '' ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Giới thiệu cửa hàng</label>
                                <textarea name="GioiThieu" class="form-control admin-input" rows="4"><?= $currentInfo['GioiThieu'] ?? '' ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Link Facebook</label>
                                <input type="text" name="FacebookUrl" class="form-control admin-input" value="<?= $currentInfo['FacebookUrl'] ?? '' ?>">
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Trạng thái hoạt động</label>
                                <select name="IsActive" class="form-control admin-input">
                                    <option value="1" <?= ($currentInfo['IsActive'] ?? 1) == 1 ? 'selected' : '' ?>>Đang sử dụng</option>
                                    <option value="0" <?= ($currentInfo['IsActive'] ?? 1) == 0 ? 'selected' : '' ?>>Tạm ngừng</option>
                                </select>
                            </div>

                            <div class="admin-form-actions mt-4">
                                <button type="submit" class="btn btn-primary btn-block py-2 font-weight-bold shadow-sm" style="border-radius: 25px;">
                                    <i class="fas fa-save mr-1"></i> LƯU THAY ĐỔI
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<style>
    .admin-profile-avatar-circle { width: 50px; height: 50px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .admin-input { border-radius: 8px; }
    .card { border-radius: 12px; }
</style>