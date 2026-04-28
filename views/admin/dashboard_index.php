<?php
$displayName = $displayName ?? 'Tài khoản';

$isAdmin = $isAdmin ?? false;
$isStaff = $isStaff ?? false;
$isShipper = $isShipper ?? false;

$countPendingOrders = $countPendingOrders ?? 0;
$todayRevenue = $todayRevenue ?? 0;
$lowStockCount = $lowStockCount ?? 0;
$ordersInDelivery = $ordersInDelivery ?? 0;

$baseUrl = $baseUrl ?? '';
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-gem mr-1"></i>
                Karma Eyewear Admin
            </span>

            <h1 class="admin-page-title mb-1">Bảng điều khiển</h1>

            <p class="admin-page-subtitle mb-0">
                Chào mừng trở lại,
                <strong><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></strong>.
                Đây là tổng quan vận hành hôm nay.
            </p>
        </div>

        <div class="admin-date-pill mt-3 mt-md-0">
            <i class="far fa-calendar-alt mr-1"></i>
            <?= date('d/m/Y') ?>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <?php if ($isShipper): ?>
            <div class="admin-hero-card mb-4">
                <div class="d-flex align-items-center">
                    <div class="admin-hero-icon mr-3">
                        <i class="fas fa-truck"></i>
                    </div>

                    <div>
                        <h5 class="mb-1">Lịch trình giao hàng</h5>
                        <p class="mb-0">
                            Bạn đang có
                            <strong><?= (int)$ordersInDelivery ?></strong>
                            đơn hàng cần xử lý.
                        </p>
                    </div>
                </div>

                <a href="<?= $baseUrl ?>/index.php?controller=admindonhang" class="admin-hero-link">
                    Xem đơn giao <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
        <?php endif; ?>

        <?php if ($isAdmin || $isStaff): ?>
            <div class="row">

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="dashboard-stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="dashboard-stat-label">Đơn hàng mới</div>
                                <div class="dashboard-stat-value">
                                    <?= (int)$countPendingOrders ?>
                                    <small>đơn</small>
                                </div>
                            </div>

                            <div class="stat-icon-circle accent-bg">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>

                        <p class="dashboard-stat-desc">
                            Đơn hàng đang chờ xác nhận và chuẩn bị kho.
                        </p>

                        <a href="<?= $baseUrl ?>/index.php?controller=admindonhang" class="dashboard-stat-link">
                            Xử lý ngay <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="dashboard-stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="dashboard-stat-label">Doanh thu hôm nay</div>
                                <div class="dashboard-stat-value">
                                    <?php if ($isAdmin): ?>
                                        <?= number_format((float)$todayRevenue, 0, ',', '.') ?>
                                        <small>₫</small>
                                    <?php else: ?>
                                        <span class="staff-hidden-revenue">Đang tăng</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="stat-icon-circle dark-bg">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>

                        <p class="dashboard-stat-desc">
                            Tổng tiền từ đơn giao thành công trong ngày.
                        </p>

                        <a href="<?= $baseUrl ?>/index.php?controller=adminrevenue" class="dashboard-stat-link">
                            Xem báo cáo <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="dashboard-stat-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="dashboard-stat-label">Kho hàng</div>
                                <div class="dashboard-stat-value <?= $lowStockCount > 0 ? 'text-danger' : '' ?>">
                                    <?= (int)$lowStockCount ?>
                                    <small>sắp hết</small>
                                </div>
                            </div>

                            <div class="stat-icon-circle accent-bg">
                                <i class="fas fa-glasses"></i>
                            </div>
                        </div>

                        <p class="dashboard-stat-desc">
                            Mẫu kính có tồn kho dưới 5 sản phẩm.
                        </p>

                        <a href="<?= $baseUrl ?>/index.php?controller=adminsanpham&statusProduct=outofstock" class="dashboard-stat-link">
                            Kiểm tra kho <i class="fas fa-chevron-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <?php if ($isAdmin): ?>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="dashboard-stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="dashboard-stat-label">Hệ thống</div>
                                    <div class="dashboard-stat-value text-word">
                                        Nhân sự
                                    </div>
                                </div>

                                <div class="stat-icon-circle dark-bg">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                            </div>

                            <p class="dashboard-stat-desc">
                                Quản lý nhân viên, tài khoản và phân quyền.
                            </p>

                            <a href="<?= $baseUrl ?>/index.php?controller=admintaikhoan" class="dashboard-stat-link">
                                Phân quyền <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="premium-panel h-100">
                    <div class="premium-panel-header">
                        <div>
                            <span class="admin-kicker">Operation Flow</span>
                            <h5 class="mb-0">Quy trình vận hành chuẩn</h5>
                        </div>
                    </div>

                    <div class="premium-panel-body">
                        <div class="row no-gutters process-row">
                            <div class="col-md-4">
                                <div class="process-step">
                                    <div class="process-step-icon">
                                        <i class="fas fa-clipboard-check"></i>
                                    </div>
                                    <h6>1. Xác nhận đơn</h6>
                                    <p>Kiểm tra thông tin khách hàng, thanh toán và tồn kho.</p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="process-step process-middle">
                                    <div class="process-step-icon">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                    <h6>2. Đóng gói</h6>
                                    <p>Vệ sinh mắt kính, kiểm tra phụ kiện và dán nhãn vận chuyển.</p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="process-step">
                                    <div class="process-step-icon">
                                        <i class="fas fa-paper-plane"></i>
                                    </div>
                                    <h6>3. Giao hàng</h6>
                                    <p>Bàn giao shipper, theo dõi trạng thái và hoàn tất đơn.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if ($isAdmin || $isStaff): ?>
                <div class="col-lg-4 mb-4">
                    <div class="premium-panel h-100">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Quick Actions</span>
                                <h5 class="mb-0">Lối tắt thao tác</h5>
                            </div>
                        </div>

                        <div class="quick-action-list">
                            <a href="<?= $baseUrl ?>/index.php?controller=adminsanpham&action=edit" class="quick-action-item">
                                <span class="quick-action-icon">
                                    <i class="fas fa-plus-circle"></i>
                                </span>
                                <span>
                                    <strong>Thêm sản phẩm mới</strong>
                                    <small>Đăng mẫu kính mới lên cửa hàng</small>
                                </span>
                            </a>

                            <a href="<?= $baseUrl ?>/index.php?controller=adminblog" class="quick-action-item">
                                <span class="quick-action-icon">
                                    <i class="fas fa-pen-fancy"></i>
                                </span>
                                <span>
                                    <strong>Quản lý bài viết</strong>
                                    <small>Tin tức, xu hướng và bài tư vấn</small>
                                </span>
                            </a>

                            <a href="<?= $baseUrl ?>/index.php?controller=adminsetting" class="quick-action-item">
                                <span class="quick-action-icon">
                                    <i class="fas fa-store"></i>
                                </span>
                                <span>
                                    <strong>Cấu hình cửa hàng</strong>
                                    <small>Cập nhật thông tin shop</small>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>