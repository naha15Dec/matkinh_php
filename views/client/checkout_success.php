<?php
if (!function_exists('formatMoney')) {
    function formatMoney($value) {
        return number_format((float)$value, 0, ',', '.') . '₫';
    }
}

$order = $order ?? [];

$orderCode = $order['MaDonHang'] ?? ('KM' . time());
$status = (int)($order['TrangThai'] ?? OrderStatusConstants::PENDING);

$paymentMethod = $order['PhuongThucThanhToan'] ?? PaymentConstants::COD;
$paymentStatus = $order['TrangThaiThanhToan'] ?? PaymentConstants::PENDING;

$isVnpay = strtoupper($paymentMethod) === strtoupper(PaymentConstants::VNPAY);
$isPaid = strtoupper($paymentStatus) === strtoupper(PaymentConstants::PAID);

$totalPay = (float)($order['TongThanhToan'] ?? 0);
?>

<section class="order-success-page">

    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">
                    Karma Eyewear Confirmation
                </span>

                <h1><?= $isPaid ? 'Thanh toán thành công' : 'Đặt hàng thành công' ?></h1>

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

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success page-alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class="success-shell">

                <div class="success-hero">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>

                    <span class="success-eyebrow">
                        <?= $isPaid ? 'PAYMENT SUCCESSFUL' : 'ORDER SUCCESSFULLY PLACED' ?>
                    </span>

                    <h2>
                        Cảm ơn bạn đã mua sắm tại Karma Eyewear
                    </h2>

                    <p>
                        <?php if ($isVnpay && $isPaid): ?>
                            Đơn hàng của bạn đã được thanh toán qua VNPAY thành công. Chúng tôi sẽ nhanh chóng xác nhận và chuẩn bị giao hàng.
                        <?php elseif ($isVnpay): ?>
                            Đơn hàng của bạn đã được ghi nhận và đang chờ thanh toán. Nếu thanh toán thất bại, hệ thống sẽ tự hủy đơn và hoàn lại tồn kho.
                        <?php else: ?>
                            Đơn hàng của bạn đã được ghi nhận thành công. Chúng tôi sẽ nhanh chóng xác nhận và chuẩn bị giao hàng.
                        <?php endif; ?>
                    </p>

                    <div class="success-order-code">
                        Mã đơn hàng:
                        <strong>#<?= htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8') ?></strong>
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
                                <strong>#<?= htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>

                            <div class="success-info-item">
                                <span>Ngày đặt</span>
                                <strong>
                                    <?= date('d/m/Y H:i', strtotime($order['NgayDat'] ?? 'now')) ?>
                                </strong>
                            </div>

                            <div class="success-info-item">
                                <span>Trạng thái đơn</span>

                                <div class="success-status-badge <?= OrderStatusConstants::getBadgeClass($status) ?>">
                                    <?= htmlspecialchars(OrderStatusConstants::getName($status), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            </div>

                            <div class="success-info-item">
                                <span>Phương thức thanh toán</span>

                                <strong>
                                    <?= $isVnpay
                                        ? 'Thanh toán VNPAY'
                                        : 'Thanh toán khi nhận hàng (COD)' ?>
                                </strong>
                            </div>

                            <div class="success-info-item">
                                <span>Trạng thái thanh toán</span>

                                <?php if ($isPaid): ?>
                                    <strong class="text-success">Đã thanh toán</strong>
                                <?php elseif (strtoupper($paymentStatus) === strtoupper(PaymentConstants::FAILED)): ?>
                                    <strong class="text-danger">Thanh toán thất bại</strong>
                                <?php else: ?>
                                    <strong>Chờ thanh toán</strong>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($order['MaGiaoDichThanhToan'])): ?>
                                <div class="success-info-item">
                                    <span>Mã giao dịch</span>
                                    <strong><?= htmlspecialchars($order['MaGiaoDichThanhToan'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </div>
                            <?php endif; ?>

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
                                    <?= htmlspecialchars($order['HoTenNguoiNhan'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </h4>

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

                    <a href="index.php?controller=profile&action=orderDetail&maDonHang=<?= urlencode($orderCode) ?>" class="btn-success-secondary">
                        Xem chi tiết đơn hàng
                    </a>
                </div>

            </div>

        </div>
    </section>

</section>