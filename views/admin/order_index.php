<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= $title ?></h1>
        <p class="admin-page-subtitle">Theo dõi trạng thái đơn hàng, xử lý vận hành và giao hàng.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item">
            <a href="index.php?controller=dashboard">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">Quản lý đơn hàng</li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="card admin-card mb-3 shadow-sm">
            <div class="card-header admin-card-header border-0 bg-transparent">
                <div class="admin-card-title-wrap">
                    <h3 class="admin-card-title">Bộ lọc đơn hàng</h3>
                    <span class="admin-card-count"><?= count($orders) ?> đơn hàng</span>
                </div>
            </div>

            <div class="card-body">
                <form action="index.php" method="GET" class="row">
                    <input type="hidden" name="controller" value="admindonhang">
                    <input type="hidden" name="action" value="index">
                    
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Tìm kiếm</label>
                            <input type="text" name="keyword" 
                                   value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>"
                                   class="form-control admin-input"
                                   placeholder="Mã đơn / người nhận / số điện thoại" />
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <select name="status" class="form-control admin-input">
                                <option value="">Tất cả trạng thái</option>
                                <?php for($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?= $i ?>" <?= (isset($_GET['status']) && $_GET['status'] == $i) ? 'selected' : '' ?>>
                                        <?= OrderStatusConstants::getName($i) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-group w-100">
                            <button type="submit" class="btn admin-btn admin-btn-save w-100">
                                <i class="fas fa-search mr-1"></i> Lọc đơn hàng
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card admin-card shadow-sm">
            <div class="card-header admin-card-header border-0 bg-transparent">
                <div class="admin-card-title-wrap">
                    <h3 class="admin-card-title">Danh sách đơn hàng</h3>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table admin-table align-middle mb-0">
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
                                    <tr>
                                        <td>
                                            <div class="admin-order-code">#<?= $item['MaDonHang'] ?></div>
                                            <div class="admin-order-subtext">
                                                <?= $item['SoLuongSanPham'] ?? 0 ?> sản phẩm
                                            </div>
                                        </td>

                                        <td>
                                            <div class="admin-account-cell">
                                                <div class="admin-account-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <div class="admin-account-name">
                                                        <?= htmlspecialchars(!empty($item['HoTenNguoiNhan']) ? $item['HoTenNguoiNhan'] : $item['TenKhachHang']) ?>
                                                    </div>
                                                    <div class="admin-account-sub">
                                                        <?= $item['SoDienThoaiNguoiNhan'] ?>
                                                    </div>
                                                    <div class="admin-order-subtext">
                                                        <?= htmlspecialchars($item['DiaChiNhanHang']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div><?= date('d/m/Y H:i', strtotime($item['NgayDat'])) ?></div>
                                            <?php if (!empty($item['NgayXacNhan'])): ?>
                                                <div class="admin-order-subtext">
                                                    XN: <?= date('d/m H:i', strtotime($item['NgayXacNhan'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-right">
                                            <div class="admin-price-strong">
                                                <?= number_format($item['TongThanhToan'], 0, ',', '.') ?> đ
                                            </div>
                                            <div class="admin-order-subtext">
                                                Gốc: <?= number_format($item['TongTienHang'], 0, ',', '.') ?> đ
                                            </div>
                                        </td>

                                        <td>
                                            <?php 
                                                $status = $item['TrangThai'];
                                                $statusText = OrderStatusConstants::getName($status);
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
                                            <span class="admin-status-badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                        </td>

                                        <td>
                                            <?php if (!empty($item['ShipperName'])): ?>
                                                <span class="admin-role-badge"><?= htmlspecialchars($item['ShipperName']) ?></span>
                                            <?php else: ?>
                                                <span class="admin-order-subtext">Chưa gán</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <a href="index.php?controller=admindonhang&action=detail&id=<?= $item['DonHangId'] ?>"
                                               class="btn admin-btn admin-btn-detail">
                                                <i class="fas fa-eye mr-1"></i> Chi tiết
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">Không có đơn hàng nào phù hợp.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .admin-order-code { font-weight: 700; color: #2f3542; }
    .admin-order-subtext { font-size: 12px; color: #7f8c8d; margin-top: 2px; line-height: 1.4; }
    .admin-price-strong { font-weight: 700; color: #2f3542; }
    .admin-status-badge.info { background: #eef6ff; color: #1d4ed8; }
    .admin-status-badge.warning { background: #fff8e6; color: #b7791f; }
    .admin-status-badge.processing { background: #fffdf3; color: #a16207; }
    .admin-status-badge.assigned { background: #f7f3ff; color: #7c3aed; }
    .admin-status-badge.shipping { background: #eefbf6; color: #0f766e; }
    .admin-status-badge.success { background: #edfdf3; color: #15803d; }
    .admin-status-badge.danger { background: #fff1f2; color: #dc2626; }
    .admin-status-badge.muted { background: #f3f4f6; color: #6b7280; }
</style>