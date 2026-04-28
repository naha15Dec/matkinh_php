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

$canAssignShipper = $isAdmin || $isStaff;
$canUpdateStatus = $isAdmin || $isStaff || $isShipper;

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
                            <?= OrderStatusConstants::getName($currentStatus) ?>
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
                                        <?= htmlspecialchars($order['DiaChiNguoiNhan'] ?? $order['DiaChiNhanHang'] ?? $order['DiaChi'] ?? 'Chưa có địa chỉ', ENT_QUOTES, 'UTF-8') ?>
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
                                    <strong><?= htmlspecialchars($order['ShipperName'] ?? $order['TenShipper'] ?? 'Chưa gán', ENT_QUOTES, 'UTF-8') ?></strong>
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
                                            $image = $item['AnhDaiDien'] ?? $item['HinhAnh'] ?? '';
                                            $imageSrc = $image ? $baseUrl . '/images/' . $image : $baseUrl . '/images/no-image.png';
                                            $price = (float)($item['DonGia'] ?? $item['GiaBan'] ?? 0);
                                            $qty = (int)($item['SoLuong'] ?? 0);
                                            $lineTotal = (float)($item['ThanhTien'] ?? ($price * $qty));
                                            ?>

                                            <tr>
                                                <td>
                                                    <div class="order-product-cell">
                                                        <div class="order-product-thumb">
                                                            <img src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>"
                                                                 alt="product"
                                                                 onerror="this.src='<?= $baseUrl ?>/images/no-image.png'">
                                                        </div>

                                                        <div>
                                                            <div class="order-product-name">
                                                                <?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm', ENT_QUOTES, 'UTF-8') ?>
                                                            </div>
                                                            <div class="order-product-meta">
                                                                <?= htmlspecialchars($item['MaSanPham'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="text-right">
                                                    <?= number_format($price, 0, ',', '.') ?> đ
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
                            <span>Tổng thanh toán</span>
                            <strong>
                                <?= number_format((float)($order['TongTien'] ?? $order['ThanhTien'] ?? 0), 0, ',', '.') ?> đ
                            </strong>
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
                                                <?= OrderStatusConstants::getName($history['TrangThaiCu'] ?? 0) ?>
                                                <i class="fas fa-arrow-right mx-1"></i>
                                                <?= OrderStatusConstants::getName($history['TrangThaiMoi'] ?? 0) ?>
                                            </div>

                                            <div class="order-timeline-meta">
                                                <?= !empty($history['CreatedAt']) ? date('d/m/Y H:i', strtotime($history['CreatedAt'])) : '' ?>
                                                <?php if (!empty($history['NguoiCapNhat'])): ?>
                                                    • <?= htmlspecialchars($history['NguoiCapNhat'], ENT_QUOTES, 'UTF-8') ?>
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
                            <form action="<?= $baseUrl ?>/index.php?controller=admindonhang&action=updateStatus" method="POST">
                                <input type="hidden" name="DonHangId" value="<?= $orderId ?>">

                                <div class="form-group">
                                    <label class="order-form-label">Trạng thái mới</label>
                                    <select name="TrangThaiMoi" class="form-control order-input" required>
                                        <option value="">-- Chọn trạng thái --</option>
                                        <option value="<?= OrderStatusConstants::CONFIRMED ?>">Đã xác nhận</option>
                                        <option value="<?= OrderStatusConstants::PREPARING ?>">Đang chuẩn bị</option>
                                        <option value="<?= OrderStatusConstants::DELIVERING ?>">Đang giao</option>
                                        <option value="<?= OrderStatusConstants::DELIVERED ?>">Giao thành công</option>
                                        <option value="<?= OrderStatusConstants::DELIVERY_FAILED ?>">Giao thất bại</option>
                                        <option value="<?= OrderStatusConstants::CANCELLED ?>">Đã hủy</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="order-form-label">Ghi chú</label>
                                    <textarea name="GhiChu"
                                              class="form-control order-input order-textarea"
                                              rows="4"
                                              placeholder="Nhập ghi chú xử lý đơn hàng..."></textarea>
                                </div>

                                <button type="submit" class="btn order-submit-btn btn-block">
                                    <i class="fas fa-save mr-1"></i>
                                    Cập nhật trạng thái
                                </button>
                            </form>
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
                            <form action="<?= $baseUrl ?>/index.php?controller=admindonhang&action=assignShipper" method="POST">
                                <input type="hidden" name="DonHangId" value="<?= $orderId ?>">

                                <div class="form-group">
                                    <label class="order-form-label">Nhân viên giao hàng</label>
                                    <select name="ShipperId" class="form-control order-input" required>
                                        <option value="">-- Chọn shipper --</option>

                                        <?php foreach ($shippers as $shipper): ?>
                                            <option value="<?= (int)$shipper['TaiKhoanId'] ?>">
                                                <?= htmlspecialchars($shipper['HoTen'] ?? $shipper['TenDangNhap'] ?? 'Shipper', ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <button type="submit" class="btn order-submit-btn btn-block">
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