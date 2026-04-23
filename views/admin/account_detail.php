<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Chi tiết tài khoản</h1>
        <p class="admin-page-subtitle">Xem, chỉnh sửa thông tin và phân quyền tài khoản.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item">
            <a href="index.php?controller=dashboard">Dashboard</a>
        </li>
        <li class="breadcrumb-item">
            <a href="index.php?controller=admintaikhoan">Quản lý tài khoản</a>
        </li>
        <li class="breadcrumb-item active"><?= htmlspecialchars($account['TenDangNhap']) ?></li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">
        <div class="row">

            <div class="col-lg-4 col-xl-3">
                <div class="card admin-profile-card shadow-sm">
                    <div class="card-body text-center">
                        <div class="admin-profile-avatar-wrap">
                            <img class="admin-profile-avatar" 
                                 src="<?= !empty($account['AnhDaiDien']) ? '/BanMatKinh/public/images/'.$account['AnhDaiDien'] : '/BanMatKinh/public/assets/img/image_Account.jpg' ?>" 
                                 alt="Avatar" />
                        </div>

                        <h3 class="admin-profile-name"><?= htmlspecialchars($account['HoTen']) ?></h3>
                        <div class="admin-profile-username"><?= htmlspecialchars($account['TenDangNhap']) ?></div>

                        <div class="admin-profile-meta-list mt-3">
                            <div class="admin-profile-meta-item d-flex justify-content-between border-bottom py-2">
                                <span class="admin-profile-meta-label text-muted">Vai trò</span>
                                <span class="admin-profile-meta-value font-weight-bold text-primary"><?= htmlspecialchars($account['TenVaiTro']) ?></span>
                            </div>

                            <div class="admin-profile-meta-item d-flex justify-content-between py-2">
                                <span class="admin-profile-meta-label text-muted">Trạng thái</span>
                                <span class="admin-profile-meta-value">
                                    <?= $account['IsActive'] ? '<span class="badge badge-success">Hoạt động</span>' : '<span class="badge badge-danger">Đã khóa</span>' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8 col-xl-9">
                <div class="card admin-form-card admin-tab-card shadow-sm">

                    <div class="card-header border-0 pb-0 bg-transparent">
                        <ul class="nav nav-pills admin-tab-nav">
                            <li class="nav-item">
                                <a class="nav-link active" href="#profileInformationAccount" data-toggle="tab">Thông tin tài khoản</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#changePassword" data-toggle="tab">Đổi mật khẩu</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#changePermisstion" data-toggle="tab">Vai trò tài khoản</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content">

                            <div class="tab-pane active" id="profileInformationAccount">
                                <form action="index.php?controller=admintaikhoan&action=updateInfo" method="POST">
                                    <input type="hidden" name="TaiKhoanId" value="<?= $account['TaiKhoanId'] ?>">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tài khoản</label>
                                                <input class="form-control admin-input" value="<?= htmlspecialchars($account['TenDangNhap']) ?>" disabled />
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Email</label>
                                                <input type="email" name="Email" class="form-control admin-input" value="<?= htmlspecialchars($account['Email'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Họ tên</label>
                                        <input type="text" name="HoTen" class="form-control admin-input" value="<?= htmlspecialchars($account['HoTen'] ?? '') ?>">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Số điện thoại</label>
                                                <input type="text" name="SoDienThoai" class="form-control admin-input" value="<?= htmlspecialchars($account['SoDienThoai'] ?? '') ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Trạng thái</label>
                                                <input class="form-control admin-input" value="<?= $account['IsActive'] ? 'Hoạt động' : 'Đã khóa' ?>" disabled />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>Địa chỉ</label>
                                        <input type="text" name="DiaChi" class="form-control admin-input" value="<?= htmlspecialchars($account['DiaChi'] ?? '') ?>">
                                    </div>

                                    <div class="admin-form-actions mt-4 text-right">
                                        <button type="submit" class="btn btn-primary admin-btn-save">
                                            <i class="fas fa-save mr-1"></i> Lưu thông tin
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane" id="changePassword">
                                <form action="index.php?controller=admintaikhoan&action=changePassword" method="POST">
                                    <input type="hidden" name="TaiKhoanId" value="<?= $account['TaiKhoanId'] ?>">

                                    <div class="form-group">
                                        <label>Tài khoản</label>
                                        <input class="form-control admin-input" value="<?= htmlspecialchars($account['TenDangNhap']) ?>" disabled />
                                    </div>

                                    <div class="form-group">
                                        <label>Mật khẩu mới</label>
                                        <input type="password" name="NewPassword" class="form-control admin-input" required minlength="6">
                                    </div>

                                    <div class="form-group">
                                        <label>Nhập lại mật khẩu</label>
                                        <input type="password" name="ConfirmPassword" class="form-control admin-input" required minlength="6">
                                    </div>

                                    <div class="admin-form-actions mt-4 text-right">
                                        <button type="submit" class="btn btn-warning admin-btn-save">
                                            <i class="fas fa-key mr-1"></i> Lưu mật khẩu
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="tab-pane" id="changePermisstion">
                                <form action="index.php?controller=admintaikhoan&action=updateRole" method="POST">
                                    <input type="hidden" name="TaiKhoanId" value="<?= $account['TaiKhoanId'] ?>">

                                    <div class="form-group">
                                        <label>Tài khoản</label>
                                        <input class="form-control admin-input" value="<?= htmlspecialchars($account['TenDangNhap']) ?>" disabled />
                                    </div>

                                    <div class="form-group">
                                        <label>Vai trò</label>
                                        <select name="VaiTroId" class="form-control admin-input">
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?= $role['VaiTroId'] ?>" <?= $role['VaiTroId'] == $account['VaiTroId'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($role['TenVaiTro']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="admin-form-actions mt-4 text-right">
                                        <button type="submit" class="btn btn-success admin-btn-save">
                                            <i class="fas fa-user-shield mr-1"></i> Cập nhật vai trò
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</section>