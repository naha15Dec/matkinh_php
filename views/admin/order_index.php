<?php
$orders = $orders ?? [];
$baseUrl = $baseUrl ?? '';

$keyword = $_GET['keyword'] ?? '';
$status = $_GET['status'] ?? '';

function orderIndexStatusClass($status)
{
    return match ((int)$status) {
        OrderStatusConstants::PENDING => 'pending',
        OrderStatusConstants::CONFIRMED => 'confirmed',
        OrderStatusConstants::PREPARING => 'processing',
        OrderStatusConstants::ASSIGNED_TO_SHIPPER => 'assigned',
        OrderStatusConstants::DELIVERING => 'shipping',
        OrderStatusConstants::DELIVERED => 'success',
        OrderStatusConstants::DELIVERY_FAILED => 'failed',
        OrderStatusConstants::CANCELLED => 'cancelled',
        default => 'muted',
    };
}
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-shopping-bag mr-1"></i>
                Order Management
            </span>

            <h1 class="admin-page-title mb-1">
                <?= htmlspecialchars($title ?? 'Quản lý đơn hàng', ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <p class="admin-page-subtitle mb-0">
                Theo dõi trạng thái đơn hàng, xử lý vận hành và giao hàng.
            </p>
        </div>

        <ol class="breadcrumb admin-breadcrumb mt-3 mt-md-0">
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Quản lý đơn hàng</li>
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

        <div class="premium-panel order-panel">

            <div class="premium-panel-header order-panel-header">
                <div>
                    <span class="admin-kicker">Karma Orders</span>
                    <h5 class="mb-0">Danh sách đơn hàng</h5>
                </div>

                <span class="admin-card-count">
                    <?= count($orders) ?> đơn hàng
                </span>
            </div>

            <div class="order-toolbar">
                <form action="<?= $baseUrl ?>/index.php" method="GET" class="order-filter-form">
                    <input type="hidden" name="controller" value="admindonhang">
                    <input type="hidden" name="action" value="index">

                    <div class="order-search-box">
                        <i class="fas fa-search"></i>
                        <input
                            type="search"
                            name="keyword"
                            value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Mã đơn / người nhận / số điện thoại..."
                        >
                    </div>

                    <select name="status" class="order-select">
                        <option value="">Tất cả trạng thái</option>

                        <?php for ($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>" <?= ((string)$status === (string)$i) ? 'selected' : '' ?>>
                                <?= OrderStatusConstants::getName($i) ?>
                            </option>
                        <?php endfor; ?>
                    </select>

                    <button type="submit" class="btn order-filter-btn">
                        <i class="fas fa-filter mr-1"></i>
                        Lọc đơn hàng
                    </button>

                    <?php if (!empty($keyword) || $status !== ''): ?>
                        <a href="<?= $baseUrl ?>/index.php?controller=admindonhang" class="btn order-reset-btn">
                            Xóa lọc
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="premium-panel-body p-0">
                <div class="table-responsive">
                    <table class="table order-table mb-0">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách / Người nhận</th>
                                <th>Ngày đặt</th>
                                <th class="text-right">Thanh toán</th>
                                <th>Trạng thái</th>
                                <th>Shipper</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($orders)): ?>
                                <?php foreach ($orders as $item): ?>
                                    <?php
                                    $orderId = (int)($item['DonHangId'] ?? 0);
                                    $orderCode = $item['MaDonHang'] ?? $orderId;

                                    $receiverName = !empty($item['HoTenNguoiNhan'])
                                        ? $item['HoTenNguoiNhan']
                                        : ($item['TenKhachHang'] ?? 'Khách hàng');

                                    $phone = $item['SoDienThoaiNguoiNhan'] ?? $item['SoDienThoai'] ?? 'Chưa có SĐT';
                                    $address = $item['DiaChiNhanHang'] ?? $item['DiaChiNguoiNhan'] ?? $item['DiaChi'] ?? 'Chưa có địa chỉ';

                                    $createdAt = $item['NgayDat'] ?? null;
                                    $confirmedAt = $item['NgayXacNhan'] ?? null;

                                    $quantity = (int)($item['SoLuongSanPham'] ?? 0);
                                    $totalPayment = (float)($item['TongThanhToan'] ?? $item['TongTien'] ?? 0);
                                    $totalProduct = (float)($item['TongTienHang'] ?? 0);

                                    $currentStatus = (int)($item['TrangThai'] ?? 0);
                                    $statusText = OrderStatusConstants::getName($currentStatus);
                                    $statusClass = orderIndexStatusClass($currentStatus);

                                    $shipperName = $item['ShipperName'] ?? $item['TenShipper'] ?? '';
                                    ?>

                                    <tr>
                                        <td>
                                            <div class="order-code">
                                                #<?= htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8') ?>
                                            </div>

                                            <div class="order-subtext">
                                                <?= $quantity ?> sản phẩm
                                            </div>
                                        </td>

                                        <td>
                                            <div class="order-customer-cell">
                                                <div class="order-customer-avatar">
                                                    <?= strtoupper(mb_substr($receiverName, 0, 1, 'UTF-8')) ?>
                                                </div>

                                                <div>
                                                    <div class="order-customer-name">
                                                        <?= htmlspecialchars($receiverName, ENT_QUOTES, 'UTF-8') ?>
                                                    </div>

                                                    <div class="order-customer-phone">
                                                        <?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>
                                                    </div>

                                                    <div class="order-address">
                                                        <?= htmlspecialchars(
                                                            mb_strlen($address, 'UTF-8') > 60
                                                                ? mb_substr($address, 0, 60, 'UTF-8') . '...'
                                                                : $address,
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        ) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="order-date">
                                                <?= !empty($createdAt) ? date('d/m/Y H:i', strtotime($createdAt)) : 'Chưa có' ?>
                                            </div>

                                            <?php if (!empty($confirmedAt)): ?>
                                                <div class="order-subtext">
                                                    XN: <?= date('d/m H:i', strtotime($confirmedAt)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-right">
                                            <div class="order-total">
                                                <?= number_format($totalPayment, 0, ',', '.') ?> đ
                                            </div>

                                            <?php if ($totalProduct > 0): ?>
                                                <div class="order-subtext">
                                                    Gốc: <?= number_format($totalProduct, 0, ',', '.') ?> đ
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <span class="order-status <?= $statusClass ?>">
                                                <i class="fas fa-circle"></i>
                                                <?= htmlspecialchars($statusText, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php if (!empty($shipperName)): ?>
                                                <span class="order-shipper-badge">
                                                    <i class="fas fa-truck mr-1"></i>
                                                    <?= htmlspecialchars($shipperName, ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="order-no-shipper">Chưa gán</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <div class="order-action-group">
                                                <a href="<?= $baseUrl ?>/index.php?controller=admindonhang&action=detail&id=<?= $orderId ?>"
                                                   class="btn order-btn order-btn-detail">
                                                    <i class="fas fa-eye mr-1"></i>
                                                    Chi tiết
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="order-empty-state">
                                            <div class="order-empty-icon">
                                                <i class="fas fa-shopping-bag"></i>
                                            </div>
                                            <h6>Không có đơn hàng nào</h6>
                                            <p>Hiện chưa có đơn hàng phù hợp với bộ lọc đang chọn.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>

        </div>

    </div>
</section>