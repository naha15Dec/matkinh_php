<?php
$data = $data ?? [];
$baseUrl = $baseUrl ?? '';

$totalRevenue = (float)($data['TotalRevenue'] ?? 0);
$monthRevenue = (float)($data['MonthRevenue'] ?? 0);
$todayRevenue = (float)($data['TodayRevenue'] ?? 0);

$revenueCOD = (float)($data['RevenueCOD'] ?? 0);
$revenueVNPAY = (float)($data['RevenueVNPAY'] ?? 0);

$totalPaymentRevenue = $revenueCOD + $revenueVNPAY;

$codPercent = $totalPaymentRevenue > 0 ? round(($revenueCOD / $totalPaymentRevenue) * 100) : 0;
$vnpayPercent = $totalPaymentRevenue > 0 ? round(($revenueVNPAY / $totalPaymentRevenue) * 100) : 0;

$deliveredOrders = (int)($data['DeliveredOrders'] ?? $data['TotalOrders'] ?? 0);
$cancelledOrders = (int)($data['CancelledOrders'] ?? 0);
$pendingOrders = (int)($data['PendingOrders'] ?? 0);
$allOrders = (int)($data['AllOrders'] ?? 0);

$paidPayments = (int)($data['PaidPayments'] ?? 0);
$pendingPayments = (int)($data['PendingPayments'] ?? 0);
$failedPayments = (int)($data['FailedPayments'] ?? 0);

$totalProductSold = (int)($data['TotalProductSold'] ?? 0);
$averageOrderValue = (float)($data['AverageOrderValue'] ?? 0);
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-chart-line mr-1"></i>
                Revenue Report
            </span>

            <h1 class="admin-page-title mb-1">Báo cáo doanh thu</h1>

            <p class="admin-page-subtitle mb-0">
                Theo dõi doanh thu đã ghi nhận từ các đơn hàng giao thành công.
            </p>
        </div>

        <ol class="breadcrumb admin-breadcrumb mt-3 mt-md-0">
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Doanh thu</li>
        </ol>
    </div>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success admin-alert">
                <i class="fas fa-check-circle mr-1"></i>
                <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger admin-alert">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row">

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="revenue-stat-card primary">
                    <div class="revenue-stat-top">
                        <div>
                            <span class="revenue-stat-label">Tổng doanh thu ghi nhận</span>
                            <h3><?= number_format($totalRevenue, 0, ',', '.') ?> ₫</h3>
                        </div>

                        <div class="revenue-stat-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>

                    <p>
                        Chỉ tính các đơn hàng đã giao thành công.
                        VNPAY phải ở trạng thái đã thanh toán.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="revenue-stat-card gold">
                    <div class="revenue-stat-top">
                        <div>
                            <span class="revenue-stat-label">Doanh thu tháng</span>
                            <h3><?= number_format($monthRevenue, 0, ',', '.') ?> ₫</h3>
                        </div>

                        <div class="revenue-stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                    </div>

                    <p>Doanh thu được ghi nhận trong tháng <?= date('m/Y') ?>.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-12 mb-4">
                <div class="revenue-stat-card dark">
                    <div class="revenue-stat-top">
                        <div>
                            <span class="revenue-stat-label">Doanh thu hôm nay</span>
                            <h3><?= number_format($todayRevenue, 0, ',', '.') ?> ₫</h3>
                        </div>

                        <div class="revenue-stat-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>

                    <p>Doanh thu từ các đơn giao thành công trong ngày hôm nay.</p>
                </div>
            </div>

        </div>

        <div class="row">

            <div class="col-lg-6 mb-4">
                <div class="premium-panel revenue-panel h-100">
                    <div class="premium-panel-header">
                        <div>
                            <span class="admin-kicker">Cash Payment</span>
                            <h5 class="mb-0">Doanh thu COD</h5>
                        </div>

                        <span class="revenue-percent-badge"><?= $codPercent ?>%</span>
                    </div>

                    <div class="premium-panel-body">
                        <div class="revenue-payment-value">
                            <?= number_format($revenueCOD, 0, ',', '.') ?> ₫
                        </div>

                        <p class="revenue-muted">
                            Chỉ tính đơn COD đã giao thành công. Đây là doanh thu được thu khi khách nhận hàng.
                        </p>

                        <div class="revenue-progress">
                            <div style="width: <?= $codPercent ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="premium-panel revenue-panel h-100">
                    <div class="premium-panel-header">
                        <div>
                            <span class="admin-kicker">Online Payment</span>
                            <h5 class="mb-0">Doanh thu VNPAY</h5>
                        </div>

                        <span class="revenue-percent-badge"><?= $vnpayPercent ?>%</span>
                    </div>

                    <div class="premium-panel-body">
                        <div class="revenue-payment-value">
                            <?= number_format($revenueVNPAY, 0, ',', '.') ?> ₫
                        </div>

                        <p class="revenue-muted">
                            Chỉ tính đơn VNPAY đã thanh toán thành công và đã giao thành công.
                            VNPAY đã thanh toán nhưng chưa giao chưa được cộng vào doanh thu.
                        </p>

                        <div class="revenue-progress">
                            <div style="width: <?= $vnpayPercent ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="premium-panel revenue-panel mb-4">
            <div class="premium-panel-header">
                <div>
                    <span class="admin-kicker">Business Summary</span>
                    <h5 class="mb-0">Tổng hợp nhanh</h5>
                </div>
            </div>

            <div class="premium-panel-body">
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="revenue-summary-box">
                            <span>Đơn giao thành công</span>
                            <strong><?= number_format($deliveredOrders, 0, ',', '.') ?></strong>
                            <small>Đơn được ghi nhận doanh thu</small>
                        </div>
                    </div>

                    <div class="col-md-3 col-6 mb-3">
                        <div class="revenue-summary-box">
                            <span>Sản phẩm đã bán</span>
                            <strong><?= number_format($totalProductSold, 0, ',', '.') ?></strong>
                            <small>Từ đơn giao thành công</small>
                        </div>
                    </div>

                    <div class="col-md-3 col-6 mb-3">
                        <div class="revenue-summary-box">
                            <span>Giá trị TB / đơn</span>
                            <strong><?= number_format($averageOrderValue, 0, ',', '.') ?> ₫</strong>
                            <small>Trên đơn đã ghi nhận</small>
                        </div>
                    </div>

                    <div class="col-md-3 col-6 mb-3">
                        <div class="revenue-summary-box">
                            <span>Tổng theo phương thức</span>
                            <strong><?= number_format($totalPaymentRevenue, 0, ',', '.') ?> ₫</strong>
                            <small>COD + VNPAY đã ghi nhận</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="premium-panel revenue-panel">
            <div class="premium-panel-header">
                <div>
                    <span class="admin-kicker">Order & Payment Status</span>
                    <h5 class="mb-0">Tình trạng đơn hàng và thanh toán</h5>
                </div>
            </div>

            <div class="premium-panel-body">
                <div class="row">
                    <div class="col-md-3 col-6 mb-3">
                        <div class="revenue-summary-box">
                            <span>Tổng đơn</span>
                            <strong><?= number_format($allOrders, 0, ',', '.') ?></strong>
                            <small>Tất cả trạng thái</small>
                        </div>
                    </div>

                    <div class="col-md-3 col-6 mb-3">
                        <div class="revenue-summary-box">
                            <span>Đơn chờ xử lý</span>
                            <strong><?= number_format($pendingOrders, 0, ',', '.') ?></strong>
                            <small>Chờ xác nhận</small>
                        </div>
                    </div>

                    <div class="col-md-3 col-6 mb-3">
                        <div class="revenue-summary-box">
                            <span>Đơn đã hủy</span>
                            <strong><?= number_format($cancelledOrders, 0, ',', '.') ?></strong>
                            <small>Không tính doanh thu</small>
                        </div>
                    </div>

                    <div class="col-md-3 col-6 mb-3">
                        <div class="revenue-summary-box">
                            <span>Thanh toán lỗi</span>
                            <strong><?= number_format($failedPayments, 0, ',', '.') ?></strong>
                            <small>Thường do VNPAY thất bại/hủy</small>
                        </div>
                    </div>

                    <div class="col-md-4 col-12 mb-3 mb-md-0">
                        <div class="revenue-summary-box">
                            <span>Tỷ trọng COD</span>
                            <strong><?= $codPercent ?>%</strong>
                            <small>Thanh toán khi nhận hàng</small>
                        </div>
                    </div>

                    <div class="col-md-4 col-12 mb-3 mb-md-0">
                        <div class="revenue-summary-box">
                            <span>Tỷ trọng VNPAY</span>
                            <strong><?= $vnpayPercent ?>%</strong>
                            <small>Online đã giao thành công</small>
                        </div>
                    </div>

                    <div class="col-md-4 col-12">
                        <div class="revenue-summary-box">
                            <span>Thanh toán đã ghi nhận</span>
                            <strong><?= number_format($paidPayments, 0, ',', '.') ?></strong>
                            <small>Số đơn có trạng thái Paid</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>