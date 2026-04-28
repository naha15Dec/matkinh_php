<?php
$user = $user ?? [];

$fullName = trim($user['HoTen'] ?? '');
if ($fullName === '') {
    $fullName = $user['TenDangNhap'] ?? 'Tài khoản';
}

$nameParts = explode(' ', $fullName);
$firstName = count($nameParts) > 0 ? end($nameParts) : '';
$lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 0, -1)) : '';

$avatarText = strtoupper(mb_substr($fullName, 0, 1, 'UTF-8'));
?>

<link rel="stylesheet" href="/BanMatKinh/public/css/admin-profile.css">

<div class="eyewear-profile-page">

    <div class="profile-hero">
        <div>
            <span class="profile-kicker">Karma Eyewear Admin</span>
            <h1>Thông tin cá nhân</h1>
            <p>Quản lý hồ sơ, thông tin liên hệ và bảo mật tài khoản quản trị.</p>
        </div>

        <div class="profile-hero-icon">
            <i class="fas fa-glasses"></i>
        </div>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert profile-alert success">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert profile-alert error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="profile-grid">

        <aside class="profile-card profile-summary">
            <div class="profile-avatar">
                <?= htmlspecialchars($avatarText, ENT_QUOTES, 'UTF-8') ?>
            </div>

            <h2><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></h2>

            <p class="profile-username">
                @<?= htmlspecialchars($user['TenDangNhap'] ?? 'admin', ENT_QUOTES, 'UTF-8') ?>
            </p>

            <div class="profile-role">
                <i class="fas fa-user-shield"></i>
                <?= htmlspecialchars($user['TenVaiTro'] ?? $user['MaVaiTro'] ?? 'Quản trị', ENT_QUOTES, 'UTF-8') ?>
            </div>

            <div class="profile-line"></div>

            <div class="profile-mini-info">
                <div>
                    <span>Email</span>
                    <strong><?= htmlspecialchars($user['Email'] ?? 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>

                <div>
                    <span>Số điện thoại</span>
                    <strong><?= htmlspecialchars($user['SoDienThoai'] ?? 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>

                <div>
                    <span>Địa chỉ</span>
                    <strong><?= htmlspecialchars($user['DiaChi'] ?? 'Chưa cập nhật', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>
        </aside>

        <section class="profile-main">

            <div class="profile-card">
                <div class="profile-card-header">
                    <div>
                        <span class="section-label">Personal Details</span>
                        <h3>Cập nhật thông tin</h3>
                    </div>
                    <i class="fas fa-id-card"></i>
                </div>

                <form method="post" action="index.php?controller=adminprofile&action=update" class="profile-form">

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Họ</label>
                            <input type="text" name="LastName"
                                   value="<?= htmlspecialchars($lastName, ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="form-group">
                            <label>Tên</label>
                            <input type="text" name="FirstName"
                                   value="<?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="Email"
                                   value="<?= htmlspecialchars($user['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="form-group">
                            <label>Số điện thoại</label>
                            <input type="text" name="Mobile"
                                   value="<?= htmlspecialchars($user['SoDienThoai'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Giới tính</label>
                            <select name="Sex">
                                <option value="">-- Chọn giới tính --</option>
                                <option value="1" <?= (($user['GioiTinh'] ?? '') == 1) ? 'selected' : '' ?>>Nam</option>
                                <option value="0" <?= (($user['GioiTinh'] ?? '') === 0 || ($user['GioiTinh'] ?? '') === '0') ? 'selected' : '' ?>>Nữ</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Ngày sinh</label>
                            <input type="date" name="NgaySinh"
                                   value="<?= htmlspecialchars($user['NgaySinh'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ</label>
                        <textarea name="Address" rows="3"><?= htmlspecialchars($user['DiaChi'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-premium">
                            <i class="fas fa-save"></i>
                            Lưu thay đổi
                        </button>
                    </div>

                </form>
            </div>

            <div class="profile-card password-card">
                <div class="profile-card-header">
                    <div>
                        <span class="section-label">Security</span>
                        <h3>Đổi mật khẩu</h3>
                    </div>
                    <i class="fas fa-lock"></i>
                </div>

                <form method="post" action="index.php?controller=adminprofile&action=changePassword" class="profile-form">

                    <div class="form-group">
                        <label>Mật khẩu hiện tại</label>
                        <input type="password" name="CurrentPassword" required>
                    </div>

                    <div class="form-row-2">
                        <div class="form-group">
                            <label>Mật khẩu mới</label>
                            <input type="password" name="NewPassword" required>
                        </div>

                        <div class="form-group">
                            <label>Nhập lại mật khẩu mới</label>
                            <input type="password" name="ConfirmPassword" required>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-outline-premium">
                            <i class="fas fa-key"></i>
                            Cập nhật mật khẩu
                        </button>
                    </div>

                </form>
            </div>

        </section>

    </div>
</div>