<?php
if (!function_exists('formatMoney')) {
    function formatMoney($value) {
        return number_format((float)$value, 0, ',', '.') . 'đ';
    }
}

if (!function_exists('clientOrderImageSrc')) {
    function clientOrderImageSrc($image, $baseUrl) {
        $image = trim((string)$image);

        if ($image === '') {
            return $baseUrl . '/images/no-image.png';
        }

        if (preg_match('/^https?:\/\//i', $image)) {
            return $image;
        }

        return $baseUrl . '/images/' . ltrim($image, '/');
    }
}

$order = $order ?? [];
$items = $order['items'] ?? [];
$baseUrl = $baseUrl ?? '';

$status = (int)($order['TrangThai'] ?? 1);
$paymentStatus = $order['TrangThaiThanhToan'] ?? PaymentConstants::PENDING;
$isPaid = strtoupper($paymentStatus) === strtoupper(PaymentConstants::PAID);
?>

<section class="order-detail-page-modern">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Order</span>
                <h1>Chi tiết đơn hàng</h1>

                <nav>
                    <a href="index.php?controller=home">Trang chủ</a>
                    <span>/</span>
                    <a href="index.php?controller=profile">Tài khoản</a>
                    <span>/</span>
                    <span>Chi tiết đơn hàng</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="order-detail-section">
        <div class="container">

            <?php if (isset($_SESSION['ProfileSuccess'])): ?>
                <div class="alert alert-success page-alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['ProfileSuccess'], ENT_QUOTES, 'UTF-8') ?>
                    <?php unset($_SESSION['ProfileSuccess']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger page-alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="order-detail-shell">

                <div class="order-detail-header">
                    <div>
                        <span>Order Details</span>
                        <h2>#<?= htmlspecialchars($order['MaDonHang'] ?? '', ENT_QUOTES, 'UTF-8') ?></h2>
                        <p>Thông tin chi tiết về đơn hàng và trạng thái xử lý.</p>
                    </div>

                    <div class="order-detail-actions">
                        <?php if ($status === OrderStatusConstants::PENDING): ?>
                            <form action="index.php?controller=profile&action=cancelOrder"
                                  method="POST"
                                  onsubmit="return confirm('Bạn có chắc muốn hủy đơn hàng này không?');">
                                <input type="hidden"
                                       name="MaDonHang"
                                       value="<?= htmlspecialchars($order['MaDonHang'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                                <button type="submit" class="btn-order-cancel-detail">
                                    <i class="fas fa-times-circle"></i>
                                    Hủy đơn hàng
                                </button>
                            </form>
                        <?php endif; ?>

                        <a href="index.php?controller=profile" class="btn-order-back">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại
                        </a>
                    </div>
                </div>

                <div class="order-detail-grid">
                    <div class="order-info-card">
                        <div class="order-info-icon">
                            <i class="far fa-user"></i>
                        </div>

                        <div>
                            <span>Người nhận</span>
                            <h3><?= htmlspecialchars($order['HoTenNguoiNhan'] ?? '', ENT_QUOTES, 'UTF-8') ?></h3>

                            <p>
                                <i class="fas fa-phone-alt"></i>
                                <?= htmlspecialchars($order['SoDienThoaiNguoiNhan'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </p>

                            <p>
                                <i class="fas fa-map-marker-alt"></i>
                                <?= htmlspecialchars($order['DiaChiNhanHang'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    </div>

                    <div class="order-info-card">
                        <div class="order-info-icon">
                            <i class="fas fa-truck"></i>
                        </div>

                        <div>
                            <span>Trạng thái đơn hàng</span>
                            <h3><?= htmlspecialchars(OrderStatusConstants::getName($status), ENT_QUOTES, 'UTF-8') ?></h3>

                            <p>
                                <i class="far fa-calendar-alt"></i>
                                <?= !empty($order['NgayDat']) ? date('d/m/Y H:i', strtotime($order['NgayDat'])) : '' ?>
                            </p>

                            <div class="order-status <?= OrderStatusConstants::getBadgeClass($status) ?>">
                                <?= htmlspecialchars(OrderStatusConstants::getName($status), ENT_QUOTES, 'UTF-8') ?>
                            </div>
                        </div>
                    </div>

                    <div class="order-info-card">
                        <div class="order-info-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>

                        <div>
                            <span>Thanh toán</span>
                            <h3><?= htmlspecialchars($order['PhuongThucThanhToan'] ?? PaymentConstants::COD, ENT_QUOTES, 'UTF-8') ?></h3>

                            <?php if ($isPaid): ?>
                                <div class="payment-badge paid">
                                    <i class="fas fa-check-circle"></i>
                                    Đã thanh toán
                                </div>
                            <?php else: ?>
                                <div class="payment-badge pending">
                                    <i class="far fa-clock"></i>
                                    Chưa thanh toán
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($order['NgayThanhToan'])): ?>
                                <p>
                                    <i class="far fa-calendar-check"></i>
                                    <?= date('d/m/Y H:i', strtotime($order['NgayThanhToan'])) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="order-products-card">
                    <div class="order-products-head">
                        <div>
                            <span>Purchased Items</span>
                            <h3>Sản phẩm đã mua</h3>
                        </div>
                    </div>

                    <div class="order-product-list">
                        <?php if (!empty($items)): ?>
                            <?php foreach ($items as $item): ?>
                                <?php
                                $productName = $item['TenSanPhamSnapshot']
                                    ?? $item['TenSanPhamHienTai']
                                    ?? 'Sản phẩm';

                                $imageSrc = clientOrderImageSrc($item['HinhAnhChinh'] ?? '', $baseUrl);
                                $price = (float)($item['DonGiaSnapshot'] ?? 0);
                                $discount = (float)($item['GiamGiaSnapshot'] ?? 0);
                                $qty = (int)($item['SoLuong'] ?? 0);
                                $lineTotal = (float)($item['ThanhTien'] ?? (($price - $discount) * $qty));
                                ?>

                                <div class="order-product-row">
                                    <div class="order-product-name">
                                        <div class="order-product-client-thumb">
                                            <img src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>"
                                                 alt="<?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>"
                                                 onerror="this.src='<?= $baseUrl ?>/images/no-image.png'">
                                        </div>

                                        <div>
                                            <strong><?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?></strong>

                                            <?php if (!empty($item['MaSanPham'])): ?>
                                                <small><?= htmlspecialchars($item['MaSanPham'], ENT_QUOTES, 'UTF-8') ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="order-product-price">
                                        <span>Đơn giá</span>
                                        <strong><?= formatMoney($price) ?></strong>

                                        <?php if ($discount > 0): ?>
                                            <small>Giảm <?= formatMoney($discount) ?></small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="order-product-qty">
                                        <span>Số lượng</span>
                                        <strong><?= $qty ?></strong>
                                    </div>

                                    <div class="order-product-total">
                                        <span>Thành tiền</span>
                                        <strong><?= formatMoney($lineTotal) ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="profile-empty-order">
                                <i class="fas fa-box-open"></i>
                                <h4>Không có sản phẩm</h4>
                                <p>Đơn hàng này chưa có sản phẩm nào.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="order-total-card">
                    <div class="order-total-line">
                        <span>Tiền hàng</span>
                        <strong><?= formatMoney($order['TongTienHang'] ?? 0) ?></strong>
                    </div>

                    <div class="order-total-line">
                        <span>Phí vận chuyển</span>
                        <strong><?= formatMoney($order['PhiVanChuyen'] ?? 0) ?></strong>
                    </div>

                    <div class="order-total-line discount">
                        <span>Giảm giá</span>
                        <strong>-<?= formatMoney($order['GiamGia'] ?? 0) ?></strong>
                    </div>

                    <div class="order-total-line grand">
                        <span>Tổng thanh toán</span>
                        <strong><?= formatMoney($order['TongThanhToan'] ?? 0) ?></strong>
                    </div>
                </div>

            </div>
        </div>
    </section>
</section>