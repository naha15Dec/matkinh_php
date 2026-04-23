<?php
// Tách tên để hiển thị theo logic bạn gửi
$fullName = trim($user['HoTen'] ?? '');
if (empty($fullName)) { $fullName = $user['TenDangNhap']; }

$nameParts = explode(' ', $fullName);
$firstName = count($nameParts) > 0 ? end($nameParts) : ""; // Tên
$lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 0, -1)) : ""; // Họ và tên đệm

// Lấy chữ cái đầu tiên để làm Avatar
$avatarText = strtoupper(mb_substr($fullName, 0, 1));
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Hồ sơ cá nhân</h1>
        <p class="admin-page-subtitle">Quản lý thông tin tài khoản và bảo mật cá nhân.</p>
    </div>
    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Hồ sơ</li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success shadow-sm border-0"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger shadow-sm border-0"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="admin-profile-avatar-circle shadow-sm">
                        <?= $avatarText ?>
                    </div>
                    <div class="ml-4">
                        <h2 class="font-weight-bold mb-1 text-dark"><?= htmlspecialchars($fullName) ?></h2>
                        <div class="d-flex align-items-center">
                            <span class="badge badge-pill badge-primary px-3 mr-2"><?= $user['TenVaiTro'] ?></span>
                            <small class="text-muted"><i class="fas fa-circle text-success mr-1" style="font-size: 8px;"></i> Đang hoạt động</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 mb-4 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h4 class="font-weight-bold mb-0">Cập nhật thông tin</h4>
                    </div>
                    <div class="card-body p-4">
                        <form action="index.php?controller=adminprofile&action=update" method="POST">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Họ & Tên đệm</label>
                                    <input type="text" name="LastName" class="form-control admin-input" value="<?= htmlspecialchars($lastName) ?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Tên</label>
                                    <input type="text" name="FirstName" class="form-control admin-input" value="<?= htmlspecialchars($firstName) ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="font-weight-bold">Email</label>
                                <input type="email" name="Email" class="form-control admin-input" value="<?= htmlspecialchars($user['Email']) ?>">
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Số điện thoại</label>
                                    <input type="text" name="SoDienThoai" class="form-control admin-input" value="<?= htmlspecialchars($user['SoDienThoai']) ?>">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold">Giới tính</label>
                                    <select name="GioiTinh" class="form-control admin-input">
                                        <option value="true" <?= $user['GioiTinh'] == 1 ? 'selected' : '' ?>>Nam</option>
                                        <option value="false" <?= $user['GioiTinh'] == 0 ? 'selected' : '' ?>>Nữ</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Địa chỉ</label>
                                <input type="text" name="DiaChi" class="form-control admin-input" value="<?= htmlspecialchars($user['DiaChi']) ?>">
                            </div>

                            <div class="mt-4 pt-3 border-top">
                                <button type="submit" class="btn btn-dark px-5 font-weight-bold" style="border-radius: 25px;">LƯU THAY ĐỔI</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h4 class="font-weight-bold mb-0">Bảo mật tài khoản</h4>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div class="mb-4">
                            <i class="fas fa-shield-alt text-primary mb-3" style="font-size: 40px;"></i>
                            <p class="text-muted small">Nên thay đổi mật khẩu định kỳ 3 tháng một lần để đảm bảo an toàn cho dữ liệu hệ thống.</p>
                        </div>
                        
                        <form action="index.php?controller=adminprofile&action=changePassword" method="POST" class="text-left">
                            <div class="form-group">
                                <label class="font-weight-bold small">MẬT KHẨU HIỆN TẠI</label>
                                <input type="password" name="CurrentPassword" class="form-control" placeholder="••••••••" required>
                            </div>
                            <div class="form-group">
                                <label class="font-weight-bold small">MẬT KHẨU MỚI</label>
                                <input type="password" name="NewPassword" class="form-control" placeholder="••••••••" required minlength="6">
                            </div>
                            <div class="form-group mb-4">
                                <label class="font-weight-bold small">XÁC NHẬN MẬT KHẨU MỚI</label>
                                <input type="password" name="ConfirmPassword" class="form-control" placeholder="••••••••" required>
                            </div>
                            <button type="submit" class="btn btn-outline-dark btn-block font-weight-bold py-2" style="border-radius: 25px;">CẬP NHẬT MẬT KHẨU</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .admin-profile-avatar-circle {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #212529 0%, #495057 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: bold;
        border-radius: 50%;
        border: 4px solid #fff;
    }
    .admin-input { border-radius: 8px; }
    .card { border-radius: 12px; }
</style>