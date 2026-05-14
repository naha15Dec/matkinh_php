<?php
$order = $order ?? [];
$items = $items ?? [];
$histories = $histories ?? [];
$shippers = $shippers ?? [];
$baseUrl = $baseUrl ?? '';

$login = $_SESSION['LoginInformation'] ?? [];
$roleCode = strtoupper(trim($login['MaVaiTro'] ?? ''));

$isAdmin = $roleCode === 'ADMIN';
$isStaff = $roleCode === 'STAFF';
$isShipper = $roleCode === 'SHIPPER';

$orderId = (int)($order['DonHangId'] ?? 0);
$orderCode = $order['MaDonHang'] ?? $orderId;
$currentStatus = (int)($order['TrangThai'] ?? 0);

$isFinalStatus = in_array($currentStatus, [
    OrderStatusConstants::DELIVERED,
    OrderStatusConstants::CANCELLED
], true);

$canAssignShipper = ($isAdmin || $isStaff)
    && !$isFinalStatus
    && $currentStatus !== OrderStatusConstants::PENDING;

$canUpdateStatus = ($isAdmin || $isStaff || $isShipper) && !$isFinalStatus;

function orderStatusThemeClass($status)
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

function orderDetailImageSrc($image, $baseUrl)
{
    $image = trim((string)$image);

    if ($image === '') {
        return $baseUrl . '/images/no-image.png';
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    return $baseUrl . '/images/' . ltrim($image, '/');
}

function orderNextStatusOptions($currentStatus, $roleCode)
{
    $currentStatus = (int)$currentStatus;
    $roleCode = strtoupper(trim($roleCode));

    $options = [];

    if ($roleCode === 'ADMIN' || $roleCode === 'STAFF') {
        $map = [
            OrderStatusConstants::PENDING => [
                OrderStatusConstants::CONFIRMED,
                OrderStatusConstants::CANCELLED
            ],
            OrderStatusConstants::CONFIRMED => [
                OrderStatusConstants::PREPARING,
                OrderStatusConstants::ASSIGNED_TO_SHIPPER,
                OrderStatusConstants::CANCELLED
            ],
            OrderStatusConstants::PREPARING => [
                OrderStatusConstants::ASSIGNED_TO_SHIPPER,
                OrderStatusConstants::CANCELLED
            ],
            OrderStatusConstants::ASSIGNED_TO_SHIPPER => [
                OrderStatusConstants::DELIVERING,
                OrderStatusConstants::DELIVERY_FAILED,
                OrderStatusConstants::CANCELLED
            ],
            OrderStatusConstants::DELIVERING => [
                OrderStatusConstants::DELIVERED,
                OrderStatusConstants::DELIVERY_FAILED
            ],
            OrderStatusConstants::DELIVERY_FAILED => [
                OrderStatusConstants::ASSIGNED_TO_SHIPPER,
                OrderStatusConstants::CANCELLED
            ],
        ];

        $options = $map[$currentStatus] ?? [];
    }

    if ($roleCode === 'SHIPPER') {
        $map = [
            OrderStatusConstants::ASSIGNED_TO_SHIPPER => [
                OrderStatusConstants::DELIVERING,
                OrderStatusConstants::DELIVERY_FAILED
            ],
            OrderStatusConstants::DELIVERING => [
                OrderStatusConstants::DELIVERED,
                OrderStatusConstants::DELIVERY_FAILED
            ],
            OrderStatusConstants::DELIVERY_FAILED => [
                OrderStatusConstants::ASSIGNED_TO_SHIPPER
            ],
        ];

        $options = $map[$currentStatus] ?? [];
    }

    return $options;
}

$nextStatusOptions = orderNextStatusOptions($currentStatus, $roleCode);
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-shopping-bag mr-1"></i>
                Order Detail
            </span>

            <h1 class="admin-page-title mb-1">
                Chi tiết đơn hàng #<?= htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <p class="admin-page-subtitle mb-0">
                Theo dõi trạng thái xử lý, thanh toán và giao hàng.
            </p>
        </div>

        <ol class="breadcrumb admin-breadcrumb mt-3 mt-md-0">
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=admindonhang">Đơn hàng</a>
            </li>
            <li class="breadcrumb-item active">
                #<?= htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8') ?>
            </li>
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
            <div class="col-lg-8 mb-4">

                <div class="premium-panel order-detail-panel mb-4">
                    <div class="premium-panel-header order-detail-header">
                        <div>
                            <span class="admin-kicker">Overview</span>
                            <h5 class="mb-0">Tổng quan đơn hàng</h5>
                        </div>

                        <span class="order-status <?= orderStatusThemeClass($currentStatus) ?>">
                            <i class="fas fa-circle"></i>
                            <?= htmlspecialchars(OrderStatusConstants::getName($currentStatus), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <div class="premium-panel-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="order-detail-box">
                                    <span>Khách nhận</span>
                                    <strong>
                                        <?= htmlspecialchars($order['HoTenNguoiNhan'] ?? $order['TenKhachHang'] ?? 'Khách hàng', ENT_QUOTES, 'UTF-8') ?>
                                    </strong>
                                    <small><?= htmlspecialchars($order['SoDienThoaiNguoiNhan'] ?? 'Chưa có SĐT', ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="order-detail-box">
                                    <span>Ngày đặt</span>
                                    <strong>
                                        <?= !empty($order['NgayDat']) ? date('d/m/Y H:i', strtotime($order['NgayDat'])) : 'Chưa có' ?>
                                    </strong>
                                    <small>Mã đơn: #<?= htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="order-detail-box">
                                    <span>Địa chỉ nhận hàng</span>
                                    <strong>
                                        <?= htmlspecialchars($order['DiaChiNhanHang'] ?? 'Chưa có địa chỉ', ENT_QUOTES, 'UTF-8') ?>
                                    </strong>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="order-detail-box">
                                    <span>Thanh toán</span>
                                    <strong><?= htmlspecialchars($order['PhuongThucThanhToan'] ?? PaymentConstants::COD, ENT_QUOTES, 'UTF-8') ?></strong>
                                    <small><?= htmlspecialchars($order['TrangThaiThanhToan'] ?? PaymentConstants::PENDING, ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="order-detail-box">
                                    <span>Shipper</span>
                                    <strong><?= htmlspecialchars($order['ShipperName'] ?? 'Chưa gán', ENT_QUOTES, 'UTF-8') ?></strong>
                                    <small>Nhân viên giao hàng phụ trách</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="premium-panel order-detail-panel mb-4">
                    <div class="premium-panel-header">
                        <div>
                            <span class="admin-kicker">Products</span>
                            <h5 class="mb-0">Sản phẩm trong đơn</h5>
                        </div>
                    </div>

                    <div class="premium-panel-body p-0">
                        <div class="table-responsive">
                            <table class="table order-detail-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th class="text-right">Đơn giá</th>
                                        <th class="text-center">SL</th>
                                        <th class="text-right">Thành tiền</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php if (!empty($items)): ?>
                                        <?php foreach ($items as $item): ?>
                                            <?php
                                            $image = $item['HinhAnhChinh'] ?? '';
                                            $imageSrc = orderDetailImageSrc($image, $baseUrl);

                                            $productName = $item['TenSanPhamSnapshot']
                                                ?? $item['TenSanPhamHienTai']
                                                ?? 'Sản phẩm';

                                            $price = (float)($item['DonGiaSnapshot'] ?? 0);
                                            $discount = (float)($item['GiamGiaSnapshot'] ?? 0);
                                            $qty = (int)($item['SoLuong'] ?? 0);
                                            $lineTotal = (float)($item['ThanhTien'] ?? (($price - $discount) * $qty));
                                            ?>

                                            <tr>
                                                <td>
                                                    <div class="order-product-cell">
                                                        <div class="order-product-thumb">
                                                            <img src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>"
                                                                 alt="<?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>"
                                                                 onerror="this.src='<?= $baseUrl ?>/images/no-image.png'">
                                                        </div>

                                                        <div>
                                                            <div class="order-product-name">
                                                                <?= htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') ?>
                                                            </div>
                                                            <div class="order-product-meta">
                                                                <?= htmlspecialchars($item['MaSanPham'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="text-right">
                                                    <?= number_format($price, 0, ',', '.') ?> đ

                                                    <?php if ($discount > 0): ?>
                                                        <div class="order-subtext">
                                                            Giảm: <?= number_format($discount, 0, ',', '.') ?> đ
                                                        </div>
                                                    <?php endif; ?>
                                                </td>

                                                <td class="text-center">
                                                    <span class="order-qty"><?= $qty ?></span>
                                                </td>

                                                <td class="text-right order-money">
                                                    <?= number_format($lineTotal, 0, ',', '.') ?> đ
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4">
                                                <div class="order-empty-state">
                                                    <div class="order-empty-icon">
                                                        <i class="fas fa-box-open"></i>
                                                    </div>
                                                    <h6>Không có sản phẩm</h6>
                                                    <p>Đơn hàng này chưa có dòng sản phẩm nào.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="order-total-panel">
                            <div>
                                <span>Tạm tính</span>
                                <strong><?= number_format((float)($order['TongTienHang'] ?? 0), 0, ',', '.') ?> đ</strong>
                            </div>

                            <div>
                                <span>Phí vận chuyển</span>
                                <strong><?= number_format((float)($order['PhiVanChuyen'] ?? 0), 0, ',', '.') ?> đ</strong>
                            </div>

                            <div>
                                <span>Giảm giá</span>
                                <strong>-<?= number_format((float)($order['GiamGia'] ?? 0), 0, ',', '.') ?> đ</strong>
                            </div>

                            <div class="order-total-final">
                                <span>Tổng thanh toán</span>
                                <strong>
                                    <?= number_format((float)($order['TongThanhToan'] ?? 0), 0, ',', '.') ?> đ
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="premium-panel order-detail-panel">
                    <div class="premium-panel-header">
                        <div>
                            <span class="admin-kicker">History</span>
                            <h5 class="mb-0">Lịch sử trạng thái</h5>
                        </div>
                    </div>

                    <div class="premium-panel-body">
                        <div class="order-timeline">
                            <?php if (!empty($histories)): ?>
                                <?php foreach ($histories as $history): ?>
                                    <div class="order-timeline-item">
                                        <div class="order-timeline-dot"></div>

                                        <div class="order-timeline-content">
                                            <div class="order-timeline-title">
                                                <?= htmlspecialchars(OrderStatusConstants::getName($history['TrangThaiCu'] ?? 0), ENT_QUOTES, 'UTF-8') ?>
                                                <i class="fas fa-arrow-right mx-1"></i>
                                                <?= htmlspecialchars(OrderStatusConstants::getName($history['TrangThaiMoi'] ?? 0), ENT_QUOTES, 'UTF-8') ?>
                                            </div>

                                            <div class="order-timeline-meta">
                                                <?= !empty($history['CreatedAt']) ? date('d/m/Y H:i', strtotime($history['CreatedAt'])) : '' ?>
                                                <?php if (!empty($history['NguoiCapNhat'])): ?>
                                                    • <?= htmlspecialchars($history['NguoiCapNhat'], ENT_QUOTES, 'UTF-8') ?>
                                                <?php endif; ?>

                                                <?php if (!empty($history['VaiTroNguoiCapNhat'])): ?>
                                                    • <?= htmlspecialchars($history['VaiTroNguoiCapNhat'], ENT_QUOTES, 'UTF-8') ?>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (!empty($history['GhiChu'])): ?>
                                                <div class="order-timeline-note">
                                                    <?= htmlspecialchars($history['GhiChu'], ENT_QUOTES, 'UTF-8') ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="order-empty-state">
                                    <div class="order-empty-icon">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <h6>Chưa có lịch sử</h6>
                                    <p>Đơn hàng chưa phát sinh lịch sử cập nhật.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

            <div class="col-lg-4 mb-4">

                <?php if ($canUpdateStatus): ?>
                    <div class="premium-panel order-action-panel mb-4">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Update</span>
                                <h5 class="mb-0">Cập nhật trạng thái</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <?php if (!empty($nextStatusOptions)): ?>
                                <form action="<?= $baseUrl ?>/index.php?controller=admindonhang&action=updateStatus"
                                      method="POST">
                                    <input type="hidden" name="DonHangId" value="<?= $orderId ?>">

                                    <div class="form-group">
                                        <label class="order-form-label">Trạng thái hiện tại</label>
                                        <div class="order-current-status">
                                            <?= htmlspecialchars(OrderStatusConstants::getName($currentStatus), ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="order-form-label">Trạng thái mới</label>
                                        <select name="TrangThaiMoi" class="form-control order-input" required>
                                            <option value="">-- Chọn trạng thái --</option>

                                            <?php foreach ($nextStatusOptions as $nextStatus): ?>
                                                <option value="<?= (int)$nextStatus ?>">
                                                    <?= htmlspecialchars(OrderStatusConstants::getName($nextStatus), ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="order-form-label">Ghi chú</label>
                                        <textarea name="GhiChu"
                                                  class="form-control order-input order-textarea"
                                                  rows="4"
                                                  placeholder="Nhập ghi chú xử lý đơn hàng..."></textarea>
                                    </div>

                                    <button type="submit"
                                            class="btn order-submit-btn btn-block"
                                            data-confirm
                                            data-confirm-title="Cập nhật trạng thái đơn hàng"
                                            data-confirm-ok="Cập nhật">
                                        <i class="fas fa-save mr-1"></i>
                                        Cập nhật trạng thái
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="order-empty-state">
                                    <div class="order-empty-icon">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <h6>Không có thao tác phù hợp</h6>
                                    <p>Trạng thái hiện tại không còn bước xử lý tiếp theo cho vai trò của bạn.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($canAssignShipper): ?>
                    <div class="premium-panel order-action-panel">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Shipping</span>
                                <h5 class="mb-0">Gán shipper</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <form action="<?= $baseUrl ?>/index.php?controller=admindonhang&action=assignShipper"
                                  method="POST">
                                <input type="hidden" name="DonHangId" value="<?= $orderId ?>">

                                <div class="form-group">
                                    <label class="order-form-label">Nhân viên giao hàng</label>
                                    <select name="ShipperId" class="form-control order-input" required>
                                        <option value="">-- Chọn shipper --</option>

                                        <?php foreach ($shippers as $shipper): ?>
                                            <?php
                                            $shipperId = (int)($shipper['TaiKhoanId'] ?? 0);
                                            $shipperName = $shipper['HoTen'] ?? $shipper['TenDangNhap'] ?? 'Shipper';
                                            ?>

                                            <?php if ($shipperId > 0): ?>
                                                <option value="<?= $shipperId ?>" <?= $shipperId === (int)($order['ShipperId'] ?? 0) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($shipperName, ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <button type="submit"
                                        class="btn order-submit-btn btn-block"
                                        data-confirm
                                        data-confirm-title="Gán shipper cho đơn hàng"
                                        data-confirm-ok="Gán shipper">
                                    <i class="fas fa-truck mr-1"></i>
                                    Gán shipper
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>

                <a href="<?= $baseUrl ?>/index.php?controller=admindonhang" class="btn order-back-btn btn-block mt-3">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Quay lại danh sách
                </a>

            </div>
        </div>

    </div>
</section>