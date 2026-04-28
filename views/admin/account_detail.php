<?php
$account = $account ?? [];
$roles = $roles ?? [];
$baseUrl = $baseUrl ?? '';

$avatarLetter = strtoupper(substr($account['TenDangNhap'] ?? 'U', 0, 1));
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-user-shield mr-1"></i>
                Account Detail
            </span>

            <h1 class="admin-page-title mb-1">
                Chi tiết tài khoản
            </h1>

            <p class="admin-page-subtitle mb-0">
                Quản lý thông tin, mật khẩu và phân quyền hệ thống.
            </p>
        </div>

        <ol class="breadcrumb admin-breadcrumb mt-3 mt-md-0">
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=dashboard">
                    Dashboard
                </a>
            </li>

            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=admintaikhoan">
                    Tài khoản
                </a>
            </li>

            <li class="breadcrumb-item active">
                Chi tiết
            </li>
        </ol>
    </div>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success admin-alert">
                <i class="fas fa-check-circle mr-1"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger admin-alert">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row">

            <div class="col-lg-4 col-xl-3 mb-4">
                <div class="premium-panel admin-profile-sidebar">

                    <div class="admin-profile-cover"></div>

                    <div class="admin-profile-content">

                        <div class="admin-profile-avatar">
                            <?= $avatarLetter ?>
                        </div>

                        <h4 class="admin-profile-name">
                            <?= htmlspecialchars($account['HoTen'] ?: $account['TenDangNhap']) ?>
                        </h4>

                        <div class="admin-profile-username">
                            @<?= htmlspecialchars($account['TenDangNhap']) ?>
                        </div>

                        <div class="admin-profile-role">
                            <?= htmlspecialchars($account['TenVaiTro'] ?? 'Chưa phân quyền') ?>
                        </div>

                        <div class="admin-profile-divider"></div>

                        <div class="admin-profile-meta">

                            <div class="admin-profile-meta-item">
                                <span class="admin-profile-meta-label">
                                    Email
                                </span>

                                <span class="admin-profile-meta-value">
                                    <?= !empty($account['Email'])
                                        ? htmlspecialchars($account['Email'])
                                        : 'Chưa cập nhật' ?>
                                </span>
                            </div>

                            <div class="admin-profile-meta-item">
                                <span class="admin-profile-meta-label">
                                    Điện thoại
                                </span>

                                <span class="admin-profile-meta-value">
                                    <?= !empty($account['SoDienThoai'])
                                        ? htmlspecialchars($account['SoDienThoai'])
                                        : 'Chưa cập nhật' ?>
                                </span>
                            </div>

                            <div class="admin-profile-meta-item">
                                <span class="admin-profile-meta-label">
                                    Trạng thái
                                </span>

                                <span class="admin-profile-meta-value">
                                    <?php if (!empty($account['IsActive'])): ?>
                                        <span class="account-status active">
                                            <i class="fas fa-circle"></i>
                                            Hoạt động
                                        </span>
                                    <?php else: ?>
                                        <span class="account-status locked">
                                            <i class="fas fa-circle"></i>
                                            Đã khóa
                                        </span>
                                    <?php endif; ?>
                                </span>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-8 col-xl-9">
                <div class="premium-panel admin-tab-panel">

                    <div class="admin-tab-header">
                        <ul class="nav admin-tab-nav">

                            <li class="nav-item">
                                <a class="nav-link active"
                                   href="#profileInformationAccount"
                                   data-toggle="tab">
                                    Thông tin
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link"
                                   href="#changePassword"
                                   data-toggle="tab">
                                    Đổi mật khẩu
                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link"
                                   href="#changePermisstion"
                                   data-toggle="tab">
                                    Phân quyền
                                </a>
                            </li>

                        </ul>
                    </div>

                    <div class="admin-tab-body">
                        <div class="tab-content">

                            <!-- TAB THÔNG TIN -->

                            <div class="tab-pane active" id="profileInformationAccount">

                                <form action="index.php?controller=admintaikhoan&action=updateInfo"
                                      method="POST">

                                    <input type="hidden"
                                           name="TaiKhoanId"
                                           value="<?= $account['TaiKhoanId'] ?>">

                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tài khoản</label>

                                                <input class="form-control admin-input"
                                                       value="<?= htmlspecialchars($account['TenDangNhap']) ?>"
                                                       disabled>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Email</label>

                                                <input type="email"
                                                       name="Email"
                                                       class="form-control admin-input"
                                                       value="<?= htmlspecialchars($account['Email'] ?? '') ?>">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="form-group">
                                        <label>Họ tên</label>

                                        <input type="text"
                                               name="HoTen"
                                               class="form-control admin-input"
                                               value="<?= htmlspecialchars($account['HoTen'] ?? '') ?>">
                                    </div>

                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Số điện thoại</label>

                                                <input type="text"
                                                       name="SoDienThoai"
                                                       class="form-control admin-input"
                                                       value="<?= htmlspecialchars($account['SoDienThoai'] ?? '') ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Địa chỉ</label>

                                                <input type="text"
                                                       name="DiaChi"
                                                       class="form-control admin-input"
                                                       value="<?= htmlspecialchars($account['DiaChi'] ?? '') ?>">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="text-right mt-4">
                                        <button type="submit"
                                                class="btn admin-btn-save">
                                            <i class="fas fa-save mr-1"></i>
                                            Lưu thông tin
                                        </button>
                                    </div>

                                </form>
                            </div>

                            <!-- TAB PASSWORD -->

                            <div class="tab-pane" id="changePassword">

                                <form action="index.php?controller=admintaikhoan&action=changePassword"
                                      method="POST">

                                    <input type="hidden"
                                           name="TaiKhoanId"
                                           value="<?= $account['TaiKhoanId'] ?>">

                                    <div class="form-group">
                                        <label>Mật khẩu mới</label>

                                        <input type="password"
                                               name="NewPassword"
                                               class="form-control admin-input"
                                               required
                                               minlength="6">
                                    </div>

                                    <div class="form-group">
                                        <label>Xác nhận mật khẩu</label>

                                        <input type="password"
                                               name="ConfirmPassword"
                                               class="form-control admin-input"
                                               required
                                               minlength="6">
                                    </div>

                                    <div class="text-right mt-4">
                                        <button type="submit"
                                                class="btn admin-btn-save">
                                            <i class="fas fa-key mr-1"></i>
                                            Đổi mật khẩu
                                        </button>
                                    </div>

                                </form>
                            </div>

                            <!-- TAB ROLE -->

                            <div class="tab-pane" id="changePermisstion">

                                <form action="index.php?controller=admintaikhoan&action=updateRole"
                                      method="POST">

                                    <input type="hidden"
                                           name="TaiKhoanId"
                                           value="<?= $account['TaiKhoanId'] ?>">

                                    <div class="form-group">
                                        <label>Vai trò tài khoản</label>

                                        <select name="VaiTroId"
                                                class="form-control admin-input">

                                            <?php foreach ($roles as $role): ?>

                                                <option value="<?= $role['VaiTroId'] ?>"
                                                    <?= $role['VaiTroId'] == $account['VaiTroId']
                                                        ? 'selected'
                                                        : '' ?>>

                                                    <?= htmlspecialchars($role['TenVaiTro']) ?>

                                                </option>

                                            <?php endforeach; ?>

                                        </select>
                                    </div>

                                    <div class="text-right mt-4">
                                        <button type="submit"
                                                class="btn admin-btn-save">
                                            <i class="fas fa-user-cog mr-1"></i>
                                            Cập nhật quyền
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