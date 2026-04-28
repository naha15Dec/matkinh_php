<?php
if (!function_exists('formatMoney')) {
    function formatMoney($value) {
        return number_format((float)$value, 0, ',', '.') . '₫';
    }
}

$orderCode = $order['MaDonHang'] ?? ('KM' . time());

$status = (int)($order['TrangThai'] ?? 1);

$paymentMethod = $order['PhuongThucThanhToan'] ?? 'COD';

$totalPay = (float)($order['TongThanhToan'] ?? 0);
?>

<section class="order-success-page">

    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">
                    Karma Eyewear Confirmation
                </span>

                <h1>Đặt hàng thành công</h1>

                <nav>
                    <a href="index.php">Trang chủ</a>
                    <span>/</span>
                    <span>Hoàn tất đơn hàng</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="order-success-section">
        <div class="container">

            <div class="success-shell">

                <div class="success-hero">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>

                    <span class="success-eyebrow">
                        ORDER SUCCESSFULLY PLACED
                    </span>

                    <h2>
                        Cảm ơn bạn đã mua sắm tại Karma Eyewear
                    </h2>

                    <p>
                        Đơn hàng của bạn đã được ghi nhận thành công. 
                        Chúng tôi sẽ nhanh chóng xác nhận và chuẩn bị giao hàng.
                    </p>

                    <div class="success-order-code">
                        Mã đơn hàng:
                        <strong>#<?= htmlspecialchars($orderCode) ?></strong>
                    </div>
                </div>

                <div class="success-grid">

                    <div class="success-card">
                        <div class="success-card-head">
                            <span>Order Information</span>
                            <h3>Thông tin đơn hàng</h3>
                        </div>

                        <div class="success-info-list">

                            <div class="success-info-item">
                                <span>Mã đơn hàng</span>
                                <strong>#<?= htmlspecialchars($orderCode) ?></strong>
                            </div>

                            <div class="success-info-item">
                                <span>Ngày đặt</span>
                                <strong>
                                    <?= date('d/m/Y H:i', strtotime($order['NgayDat'] ?? 'now')) ?>
                                </strong>
                            </div>

                            <div class="success-info-item">
                                <span>Trạng thái</span>

                                <div class="success-status-badge <?= OrderStatusConstants::getBadgeClass($status) ?>">
                                    <?= OrderStatusConstants::getName($status) ?>
                                </div>
                            </div>

                            <div class="success-info-item">
                                <span>Thanh toán</span>

                                <strong>
                                    <?= $paymentMethod === 'VNPAY'
                                        ? 'Thanh toán VNPAY'
                                        : 'Thanh toán khi nhận hàng (COD)' ?>
                                </strong>
                            </div>

                            <div class="success-info-item total">
                                <span>Tổng thanh toán</span>
                                <strong><?= formatMoney($totalPay) ?></strong>
                            </div>

                        </div>
                    </div>

                    <div class="success-card">
                        <div class="success-card-head">
                            <span>Shipping Information</span>
                            <h3>Thông tin giao nhận</h3>
                        </div>

                        <div class="success-customer-box">

                            <div class="customer-avatar">
                                <i class="far fa-user"></i>
                            </div>

                            <div class="customer-info">
                                <h4>
                                    <?= htmlspecialchars($order['HoTenNguoiNhan'] ?? '') ?>
                                </h4>

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

                        <div class="success-note">
                            <i class="fas fa-shield-alt"></i>

                            Karma Eyewear sẽ liên hệ xác nhận đơn hàng trong thời gian sớm nhất.
                        </div>
                    </div>

                </div>

                <div class="success-actions">
                    <a href="index.php?controller=sanpham" class="btn-success-primary">
                        Tiếp tục mua sắm
                    </a>

                    <a href="index.php?controller=profile" class="btn-success-secondary">
                        Kiểm tra đơn hàng
                    </a>
                </div>

            </div>

        </div>
    </section>

</section>