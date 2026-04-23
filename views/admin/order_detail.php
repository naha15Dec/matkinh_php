<?php
// Tái hiện logic phân quyền từ C#
$login = $_SESSION['LoginInformation'];
$roleCode = strtoupper(trim($login['MaVaiTro'] ?? ''));

$isAdmin = ($roleCode === 'ADMIN');
$isStaff = ($roleCode === 'STAFF');
$isShipper = ($roleCode === 'SHIPPER');

$canAssignShipper = $isAdmin || $isStaff;
$canUpdateStatus = $isAdmin || $isStaff || $isShipper;
?>

<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Chi tiết đơn hàng #<?= $order['MaDonHang'] ?></h1>
        <p class="admin-page-subtitle">Theo dõi xử lý đơn, giao hàng và lịch sử trạng thái.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?controller=admindonhang">Quản lý đơn hàng</a></li>
        <li class="breadcrumb-item active">#<?= $order['MaDonHang'] ?></li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">

                <div class="card admin-card mb-3">
                    <div class="card-header admin-card-header border-0">
                        <div class="admin-card-title-wrap">
                            <h3 class="admin-card-title">Tổng quan đơn hàng</h3>
                            <?php 
                                $status = $order['TrangThai'];
                                $badgeClass = "";
                                switch ($status) {
                                    case 1: $badgeClass = "warning"; break;
                                    case 2: $badgeClass = "info"; break;
                                    case 3: $badgeClass = "processing"; break;
                                    case 4: $badgeClass = "assigned"; break;
                                    case 5: $badgeClass = "shipping"; break;
                                    case 6: $badgeClass = "success"; break;
                                    case 7: $badgeClass = "danger"; break;
                                    case 8: $badgeClass = "muted"; break;
                                }
                            ?>
                            <span class="admin-status-badge <?= $badgeClass ?>"><?= OrderStatusConstants::getName($status) ?></span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row admin-detail-grid">
                            <div class="col-md-6 mb-3">
                                <div class="admin-detail-box">
                                    <div class="admin-detail-label">Mã đơn hàng</div>
                                    <div class="admin-detail-value">#<?= $order['MaDonHang'] ?></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="admin-detail-box">
                                    <div class="admin-detail-label">Khách hàng</div>
                                    <div class="admin-detail-value"><?= htmlspecialchars($order['TenKhachHang']) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="admin-detail-box">
                                    <div class="admin-detail-label">Ngày đặt</div>
                                    <div class="admin-detail-value"><?= date('d/m/Y H:i', strtotime($order['NgayDat'])) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="admin-detail-box">
                                    <div class="admin-detail-label">Người xác nhận</div>
                                    <div class="admin-detail-value"><?= !empty($order['ConfirmedByName']) ? htmlspecialchars($order['ConfirmedByName']) : "—" ?></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="admin-detail-box">
                                    <div class="admin-detail-label">Shipper</div>
                                    <div class="admin-detail-value"><?= !empty($order['ShipperName']) ? htmlspecialchars($order['ShipperName']) : "Chưa gán" ?></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="admin-detail-box">
                                    <div class="admin-detail-label">Ghi chú đơn</div>
                                    <div class="admin-detail-value"><?= !empty($order['GhiChu']) ? htmlspecialchars($order['GhiChu']) : "Không có ghi chú" ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card admin-card mb-3">
                    <div class="card-header admin-card-header border-0">
                        <h3 class="admin-card-title">Thông tin nhận hàng</h3>
                    </div>
                    <div class="card-body">
                        <div class="row admin-detail-grid">
                            <div class="col-md-6 mb-3">
                                <div class="admin-detail-box">
                                    <div class="admin-detail-label">Người nhận</div>
                                    <div class="admin-detail-value"><?= htmlspecialchars($order['HoTenNguoiNhan']) ?></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="admin-detail-box">
                                    <div class="admin-detail-label">Số điện thoại</div>
                                    <div class="admin-detail-value"><?= $order['SoDienThoaiNguoiNhan'] ?></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="admin-detail-box">
                                    <div class="admin-detail-label">Địa chỉ nhận hàng</div>
                                    <div class="admin-detail-value"><?= htmlspecialchars($order['DiaChiNhanHang']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card admin-card mb-3">
                    <div class="card-header admin-card-header border-0">
                        <h3 class="admin-card-title">Sản phẩm trong đơn</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table admin-table align-middle">
                                <thead>
                                    <tr>
                                        <th>Sản phẩm</th>
                                        <th class="text-right">Đơn giá</th>
                                        <th class="text-center">SL</th>
                                        <th class="text-right">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="admin-product-cell">
                                                <div class="admin-product-thumb">
                                                    <?php if(!empty($item['HinhAnhChinh'])): ?>
                                                        <img src="/BanMatKinh/public/images/<?= $item['HinhAnhChinh'] ?>" alt="">
                                                    <?php else: ?>
                                                        <div class="admin-product-thumb-placeholder"><i class="fas fa-image"></i></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="admin-product-name"><?= htmlspecialchars($item['TenSanPhamSnapshot']) ?></div>
                                                    <div class="admin-product-sub">#<?= $item['MaSanPham'] ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-right"><?= number_format($item['DonGiaSnapshot'], 0, ',', '.') ?> đ</td>
                                        <td class="text-center"><?= $item['SoLuong'] ?></td>
                                        <td class="text-right admin-price-strong"><?= number_format($item['ThanhTien'], 0, ',', '.') ?> đ</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card admin-card">
                    <div class="card-header admin-card-header border-0">
                        <h3 class="admin-card-title">Lịch sử trạng thái</h3>
                    </div>
                    <div class="card-body">
                        <div class="admin-timeline">
                            <?php foreach ($histories as $h): ?>
                            <div class="admin-timeline-item">
                                <div class="admin-timeline-dot"></div>
                                <div class="admin-timeline-content">
                                    <div class="admin-timeline-title">
                                        <?= OrderStatusConstants::getName($h['TrangThaiCu']) ?> → <?= OrderStatusConstants::getName($h['TrangThaiMoi']) ?>
                                    </div>
                                    <div class="admin-timeline-meta">
                                        <?= date('d/m/Y H:i', strtotime($h['CreatedAt'])) ?> • <?= htmlspecialchars($h['NguoiCapNhat']) ?>
                                    </div>
                                    <?php if(!empty($h['GhiChu'])): ?>
                                        <div class="admin-timeline-note"><?= htmlspecialchars($h['GhiChu']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card admin-card mb-3">
                    <div class="card-header admin-card-header border-0">
                        <h3 class="admin-card-title">Thanh toán</h3>
                    </div>
                    <div class="card-body">
                        <div class="admin-summary-row"><span>Tiền hàng</span><strong><?= number_format($order['TongTienHang'], 0, ',', '.') ?> đ</strong></div>
                        <div class="admin-summary-row"><span>Phí vận chuyển</span><strong><?= number_format($order['PhiVanChuyen'], 0, ',', '.') ?> đ</strong></div>
                        <div class="admin-summary-row"><span>Giảm giá</span><strong>- <?= number_format($order['GiamGia'], 0, ',', '.') ?> đ</strong></div>
                        <hr />
                        <div class="admin-summary-row total"><span>Tổng thanh toán</span><strong><?= number_format($order['TongThanhToan'], 0, ',', '.') ?> đ</strong></div>
                    </div>
                </div>

                <?php if ($canUpdateStatus): ?>
                <div class="card admin-card mb-3">
                    <div class="card-header admin-card-header border-0"><h3 class="admin-card-title">Xử lý đơn</h3></div>
                    <div class="card-body">
                        <form action="index.php?controller=admindonhang&action=updateStatus" method="POST">
                            <input type="hidden" name="DonHangId" value="<?= $order['DonHangId'] ?>">
                            <div class="form-group">
                                <label>Trạng thái mới</label>
                                <select name="TrangThaiMoi" class="form-control admin-input">
                                    <?php 
                                        $current = $order['TrangThai'];
                                        if ($isAdmin || $isStaff) {
                                            if ($current == 1) { echo '<option value="2">Đã xác nhận</option><option value="8">Đã hủy</option>'; }
                                            elseif ($current == 2) { echo '<option value="3">Đang chuẩn bị</option>'; }
                                            elseif ($current == 3) { echo '<option value="4">Đã giao shipper</option><option value="8">Đã hủy</option>'; }
                                        }
                                        if ($isAdmin || $isShipper) {
                                            if ($current == 4) { echo '<option value="5">Đang giao</option>'; }
                                            elseif ($current == 5) { echo '<option value="6">Giao thành công</option><option value="7">Giao thất bại</option>'; }
                                        }
                                        if ($isAdmin && $current < 6) { echo '<option value="8">Hủy đơn (Admin)</option>'; }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Ghi chú</label>
                                <textarea name="GhiChu" rows="2" class="form-control admin-input" placeholder="Lý do cập nhật..."></textarea>
                            </div>
                            <button type="submit" class="btn admin-btn admin-btn-save w-100">Cập nhật</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($canAssignShipper && ($order['TrangThai'] == 3 || $order['TrangThai'] == 4)): ?>
                <div class="card admin-card">
                    <div class="card-header admin-card-header border-0"><h3 class="admin-card-title">Gán shipper</h3></div>
                    <div class="card-body">
                        <form action="index.php?controller=admindonhang&action=assignShipper" method="POST">
                            <input type="hidden" name="DonHangId" value="<?= $order['DonHangId'] ?>">
                            <div class="form-group">
                                <select name="ShipperId" class="form-control admin-input">
                                    <option value="">-- Chọn shipper --</option>
                                    <?php foreach ($shippers as $s): ?>
                                        <option value="<?= $s['TaiKhoanId'] ?>" <?= ($order['ShipperId'] == $s['TaiKhoanId']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($s['HoTen']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn admin-btn admin-btn-detail w-100">Xác nhận gán</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<style>
    /* ... Copy toàn bộ phần <style> từ bản C# bạn gửi vào đây ... */
    .admin-detail-box { background: #fafafa; border: 1px solid #ececec; border-radius: 12px; padding: 14px 16px; height: 100%; }
    .admin-detail-label { font-size: 12px; color: #8a8f98; text-transform: uppercase; margin-bottom: 6px; }
    .admin-detail-value { font-size: 15px; color: #2f3542; font-weight: 600; }
    .admin-product-thumb { width: 58px; height: 58px; border-radius: 12px; overflow: hidden; border: 1px solid #ececec; }
    .admin-product-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .admin-timeline-item { position: relative; padding-left: 20px; margin-bottom: 20px; border-left: 2px solid #f0e7d6; }
    .admin-timeline-dot { position: absolute; left: -7px; top: 2px; width: 12px; height: 12px; background: #d8b67a; border-radius: 50%; border: 2px solid #fff; }
    .admin-timeline-note { font-size: 14px; color: #4b5563; background: #faf8f3; border: 1px solid #f0e7d6; border-radius: 10px; padding: 10px 12px; }
    .admin-summary-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #ececec; }
    .admin-summary-row.total { font-size: 16px; color: #2f3542; border-bottom: none; }
    /* ... (Các class badge khác giữ nguyên như trang index) ... */
</style>