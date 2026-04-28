<?php
$fullName = trim($account['HoTen'] ?? '');

if ($fullName === '') {
    $fullName = $account['TenDangNhap'] ?? 'Người dùng';
}

$nameParts = preg_split('/\s+/', $fullName);
$firstName = count($nameParts) > 0 ? end($nameParts) : '';
$lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 0, -1)) : '';

$avatarText = strtoupper(mb_substr($fullName, 0, 1, 'UTF-8'));

$listOrderUser = $listOrderUser ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;

if (!function_exists('formatMoney')) {
    function formatMoney($value) {
        return number_format((float)$value, 0, ',', '.') . 'đ';
    }
}
?>

<section class="profile-page-modern">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Member</span>
                <h1>Hồ sơ cá nhân</h1>

                <nav>
                    <a href="index.php?controller=home">Trang chủ</a>
                    <span>/</span>
                    <span>Tài khoản</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="profile-section-modern">
        <div class="container">

            <?php if (isset($_SESSION['ProfileSuccess'])): ?>
                <div class="alert alert-success page-alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['ProfileSuccess']) ?>
                    <?php unset($_SESSION['ProfileSuccess']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['PasswordError'])): ?>
                <div class="alert alert-danger page-alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['PasswordError']) ?>
                    <?php unset($_SESSION['PasswordError']); ?>
                </div>
            <?php endif; ?>

            <div class="profile-hero-card">
                <div class="profile-avatar">
                    <?= htmlspecialchars($avatarText) ?>
                </div>

                <div class="profile-hero-info">
                    <span>Member Account</span>
                    <h2><?= htmlspecialchars($fullName) ?></h2>
                    <p>
                        <?= htmlspecialchars($account['Email'] ?? 'Chưa cập nhật email') ?>
                    </p>
                </div>

                <div class="profile-hero-stats">
                    <div>
                        <strong><?= count($listOrderUser) ?></strong>
                        <span>Đơn hiển thị</span>
                    </div>

                    <div>
                        <strong><?= htmlspecialchars($account['MaVaiTro'] ?? 'USER') ?></strong>
                        <span>Vai trò</span>
                    </div>
                </div>
            </div>

            <div class="profile-layout-modern">

                <div class="profile-left">
                    <div class="profile-card-modern">
                        <div class="profile-card-head">
                            <span>Personal Information</span>
                            <h3>Thông tin cá nhân</h3>
                            <p>Cập nhật thông tin để quá trình mua hàng và giao nhận chính xác hơn.</p>
                        </div>

                        <form action="index.php?controller=profile&action=updateInfo" method="POST">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Họ & tên đệm</label>
                                    <div class="profile-input-wrap">
                                        <i class="far fa-user"></i>
                                        <input 
                                            type="text" 
                                            name="LastName" 
                                            value="<?= htmlspecialchars($lastName) ?>"
                                            placeholder="Nguyễn Văn"
                                        >
                                    </div>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label>Tên</label>
                                    <div class="profile-input-wrap">
                                        <i class="far fa-user"></i>
                                        <input 
                                            type="text" 
                                            name="FirstName" 
                                            value="<?= htmlspecialchars($firstName) ?>"
                                            placeholder="A"
                                        >
                                    </div>
                                </div>

                                <div class="col-12 form-group">
                                    <label>Số điện thoại</label>
                                    <div class="profile-input-wrap">
                                        <i class="fas fa-phone-alt"></i>
                                        <input 
                                            type="text" 
                                            name="Mobile" 
                                            value="<?= htmlspecialchars($account['SoDienThoai'] ?? '') ?>"
                                            placeholder="09xxxxxxxx"
                                        >
                                    </div>
                                </div>

                                <div class="col-12 form-group">
                                    <label>Giới tính</label>
                                    <div class="profile-input-wrap">
                                        <i class="fas fa-venus-mars"></i>
                                        <select name="Sex">
                                            <option value="">Chưa cập nhật</option>
                                            <option value="Nam" <?= (($account['GioiTinh'] ?? null) == 1) ? 'selected' : '' ?>>Nam</option>
                                            <option value="Nữ" <?= (($account['GioiTinh'] ?? null) === 0 || ($account['GioiTinh'] ?? null) === '0') ? 'selected' : '' ?>>Nữ</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 form-group">
                                    <label>Địa chỉ giao hàng mặc định</label>
                                    <div class="profile-input-wrap">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <input 
                                            type="text" 
                                            name="Address" 
                                            value="<?= htmlspecialchars($account['DiaChi'] ?? '') ?>"
                                            placeholder="Số nhà, đường, phường/xã..."
                                        >
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-profile-primary">
                                Lưu thay đổi
                            </button>
                        </form>
                    </div>

                    <div class="profile-card-modern">
                        <div class="profile-card-head">
                            <span>Security</span>
                            <h3>Đổi mật khẩu</h3>
                            <p>Nên dùng mật khẩu mạnh để bảo vệ tài khoản của bạn.</p>
                        </div>

                        <form action="index.php?controller=profile&action=changePassword" method="POST">
                            <div class="form-group">
                                <label>Mật khẩu hiện tại</label>
                                <div class="profile-input-wrap">
                                    <i class="fas fa-lock"></i>
                                    <input 
                                        type="password" 
                                        name="passwdCurrent" 
                                        placeholder="••••••••"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Mật khẩu mới</label>
                                <div class="profile-input-wrap">
                                    <i class="fas fa-shield-alt"></i>
                                    <input 
                                        type="password" 
                                        name="PassWord" 
                                        placeholder="Tối thiểu 6 ký tự"
                                        required
                                    >
                                </div>
                            </div>

                            <button type="submit" class="btn-profile-secondary">
                                Cập nhật mật khẩu
                            </button>
                        </form>
                    </div>
                </div>

                <div class="profile-right">
                    <div class="profile-card-modern orders-card">
                        <div class="profile-card-head order-head">
                            <div>
                                <span>Order History</span>
                                <h3>Lịch sử mua hàng</h3>
                            </div>

                            <a href="index.php?controller=sanpham">
                                Mua thêm kính
                            </a>
                        </div>

                        <?php if (empty($listOrderUser)): ?>
                            <div class="profile-empty-order">
                                <i class="fas fa-shopping-bag"></i>
                                <h4>Bạn chưa có đơn hàng nào</h4>
                                <p>Khám phá bộ sưu tập mắt kính mới nhất tại Karma Eyewear.</p>
                                <a href="index.php?controller=sanpham">Sắm kính ngay</a>
                            </div>
                        <?php else: ?>
                            <div class="order-list-modern">
                                <?php foreach ($listOrderUser as $order): ?>
                                    <?php
                                        $status = (int)($order['TrangThai'] ?? 1);
                                        $orderCode = $order['MaDonHang'] ?? '';
                                    ?>

                                    <div class="order-item-modern">
                                        <div class="order-main-info">
                                            <span>Mã đơn</span>
                                            <strong>#<?= htmlspecialchars($orderCode) ?></strong>
                                            <small>
                                                <?= !empty($order['NgayDat']) ? date('d/m/Y H:i', strtotime($order['NgayDat'])) : '' ?>
                                            </small>
                                        </div>

                                        <div class="order-money">
                                            <span>Tổng tiền</span>
                                            <strong><?= formatMoney($order['TongThanhToan'] ?? 0) ?></strong>
                                        </div>

                                        <div class="order-status-wrap">
                                            <span class="order-status <?= OrderStatusConstants::getBadgeClass($status) ?>">
                                                <?= OrderStatusConstants::getName($status) ?>
                                            </span>
                                        </div>

                                        <a 
                                            href="index.php?controller=profile&action=orderDetail&maDonHang=<?= urlencode($orderCode) ?>" 
                                            class="btn-order-detail"
                                        >
                                            Chi tiết
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ($totalPages > 1): ?>
                                <nav class="profile-pagination">
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <a 
                                            href="index.php?controller=profile&page=<?= $i ?>" 
                                            class="<?= $i == $page ? 'active' : '' ?>"
                                        >
                                            <?= $i ?>
                                        </a>
                                    <?php endfor; ?>
                                </nav>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </section>
</section>