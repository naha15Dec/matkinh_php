<?php
if (!function_exists('formatMoney')) {
    function formatMoney($value) {
        return number_format((float)$value, 0, ',', '.') . 'đ';
    }
}

$order = $order ?? [];
$items = $order['items'] ?? [];

$status = (int)($order['TrangThai'] ?? 1);
$paymentStatus = strtoupper($order['TrangThaiThanhToan'] ?? 'PENDING');
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
            <div class="order-detail-shell">

                <div class="order-detail-header">
                    <div>
                        <span>Order Details</span>
                        <h2>#<?= htmlspecialchars($order['MaDonHang'] ?? '') ?></h2>
                        <p>Thông tin chi tiết về đơn hàng và trạng thái xử lý.</p>
                    </div>

                    <a href="index.php?controller=profile" class="btn-order-back">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại
                    </a>
                </div>

                <div class="order-detail-grid">
                    <div class="order-info-card">
                        <div class="order-info-icon">
                            <i class="far fa-user"></i>
                        </div>

                        <div>
                            <span>Người nhận</span>
                            <h3><?= htmlspecialchars($order['HoTenNguoiNhan'] ?? '') ?></h3>

                            <p>
                                <i class="fas fa-phone-alt"></i>
                                <?= htmlspecialchars($order['SoDienThoaiNguoiNhan'] ?? '') ?>
                            </p>

                            <p>
                                <i class="fas fa-map-marker-alt"></i>
                                <?= htmlspecialchars($order['DiaChiNhanHang'] ?? '') ?>
                            </p>
                        </div>
                    </div>

                    <div class="order-info-card">
                        <div class="order-info-icon">
                            <i class="fas fa-truck"></i>
                        </div>

                        <div>
                            <span>Trạng thái đơn hàng</span>
                            <h3><?= OrderStatusConstants::getName($status) ?></h3>

                            <p>
                                <i class="far fa-calendar-alt"></i>
                                <?= !empty($order['NgayDat']) ? date('d/m/Y H:i', strtotime($order['NgayDat'])) : '' ?>
                            </p>

                            <div class="order-status <?= OrderStatusConstants::getBadgeClass($status) ?>">
                                <?= OrderStatusConstants::getName($status) ?>
                            </div>
                        </div>
                    </div>

                    <div class="order-info-card">
                        <div class="order-info-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>

                        <div>
                            <span>Thanh toán</span>
                            <h3><?= htmlspecialchars($order['PhuongThucThanhToan'] ?? 'COD') ?></h3>

                            <?php if ($paymentStatus === PaymentConstants::PAID): ?>
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
                        <?php foreach ($items as $item): ?>
                            <div class="order-product-row">
                                <div class="order-product-name">
                                    <strong><?= htmlspecialchars($item['TenSanPhamSnapshot'] ?? 'Sản phẩm') ?></strong>
                                </div>

                                <div class="order-product-price">
                                    <span>Đơn giá</span>
                                    <strong><?= formatMoney($item['DonGiaSnapshot'] ?? 0) ?></strong>
                                </div>

                                <div class="order-product-qty">
                                    <span>Số lượng</span>
                                    <strong><?= (int)($item['SoLuong'] ?? 0) ?></strong>
                                </div>

                                <div class="order-product-total">
                                    <span>Thành tiền</span>
                                    <strong><?= formatMoney($item['ThanhTien'] ?? 0) ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
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