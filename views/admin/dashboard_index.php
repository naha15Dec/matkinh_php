<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Dashboard</h1>
        <p class="admin-page-subtitle">Tổng quan hoạt động quản trị cửa hàng mắt kính Karma Eyewear.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <?php if ($isAdmin || $isStaff): ?>
            <div class="row">
                <div class="col-lg-3 col-sm-6">
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-top">
                            <div>
                                <div class="dashboard-stat-label">Đơn hàng</div>
                                <div class="dashboard-stat-value">Vận hành</div>
                            </div>
                            <div class="dashboard-stat-icon icon-orders">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat-desc">
                            Kiểm tra đơn hàng đang chờ xác nhận, chuẩn bị và giao hàng.
                        </p>
                        <a href="index.php?controller=adminorder" class="dashboard-stat-link">
                            Xem chi tiết <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <?php if ($isAdmin): ?>
                    <div class="col-lg-3 col-sm-6">
                        <div class="dashboard-stat-card">
                            <div class="dashboard-stat-top">
                                <div>
                                    <div class="dashboard-stat-label">Doanh thu</div>
                                    <div class="dashboard-stat-value">Báo cáo</div>
                                </div>
                                <div class="dashboard-stat-icon icon-revenue">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                            <p class="dashboard-stat-desc">
                                Theo dõi hiệu quả kinh doanh và doanh thu bán hàng.
                            </p>
                            <a href="index.php?controller=adminrevenue" class="dashboard-stat-link">
                                Xem chi tiết <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6">
                        <div class="dashboard-stat-card">
                            <div class="dashboard-stat-top">
                                <div>
                                    <div class="dashboard-stat-label">Tài khoản</div>
                                    <div class="dashboard-stat-value">Quản lý</div>
                                </div>
                                <div class="dashboard-stat-icon icon-users">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <p class="dashboard-stat-desc">
                                Quản lý tài khoản nhân viên, shipper và người dùng hệ thống.
                            </p>
                            <a href="index.php?controller=adminaccount" class="dashboard-stat-link">
                                Xem chi tiết <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="col-lg-3 col-sm-6">
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-top">
                            <div>
                                <div class="dashboard-stat-label">Sản phẩm</div>
                                <div class="dashboard-stat-value">Kho hàng</div>
                            </div>
                            <div class="dashboard-stat-icon icon-products">
                                <i class="fas fa-glasses"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat-desc">
                            Xem danh sách sản phẩm, tình trạng tồn kho và cập nhật.
                        </p>
                        <a href="index.php?controller=adminsanpham" class="dashboard-stat-link">
                            Xem chi tiết <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isShipper): ?>
            <div class="row">
                <div class="col-lg-4 col-sm-6">
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-top">
                            <div>
                                <div class="dashboard-stat-label">Đơn được giao</div>
                                <div class="dashboard-stat-value">Shipper</div>
                            </div>
                            <div class="dashboard-stat-icon icon-orders">
                                <i class="fas fa-truck"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat-desc">
                            Theo dõi các đơn hàng được phân công cho bạn.
                        </p>
                        <a href="index.php?controller=adminorder" class="dashboard-stat-link">
                            Xem chi tiết <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 col-sm-6">
                    <div class="dashboard-stat-card">
                        <div class="dashboard-stat-top">
                            <div>
                                <div class="dashboard-stat-label">Hồ sơ</div>
                                <div class="dashboard-stat-value">Cá nhân</div>
                            </div>
                            <div class="dashboard-stat-icon icon-users">
                                <i class="fas fa-user-circle"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat-desc">
                            Xem và cập nhật thông tin tài khoản giao hàng.
                        </p>
                        <a href="index.php?controller=profile" class="dashboard-stat-link">
                            Xem chi tiết <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row mt-2">
            <div class="col-lg-8">
                <div class="card dashboard-panel">
                    <div class="card-header border-0 bg-transparent">
                        <h3 class="card-title dashboard-panel-title">Tổng quan quản trị</h3>
                    </div>
                    <div class="card-body">
                        <div class="dashboard-overview-grid">
                            <?php if ($isAdmin || $isStaff): ?>
                                <div class="dashboard-overview-item">
                                    <span class="overview-dot"></span>
                                    <div>
                                        <h4>Đơn hàng</h4>
                                        <p>Theo dõi đơn hàng mới, xử lý trạng thái và bàn giao cho shipper.</p>
                                    </div>
                                </div>
                                <div class="dashboard-overview-item">
                                    <span class="overview-dot"></span>
                                    <div>
                                        <h4>Sản phẩm</h4>
                                        <p>Quản lý sản phẩm, thương hiệu và tình trạng kho.</p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ($isShipper): ?>
                                <div class="dashboard-overview-item">
                                    <span class="overview-dot"></span>
                                    <div>
                                        <h4>Giao hàng</h4>
                                        <p>Cập nhật trạng thái đang giao và giao thành công cho khách.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card dashboard-panel">
                    <div class="card-header border-0 bg-transparent">
                        <h3 class="card-title dashboard-panel-title">Truy cập nhanh</h3>
                    </div>
                    <div class="card-body">
                        <div class="dashboard-quick-links">
                            <?php if ($isAdmin || $isStaff): ?>
                                <a href="index.php?controller=adminsanpham&action=create" class="dashboard-quick-link">
                                    <i class="fas fa-plus-circle text-primary"></i> <span>Thêm sản phẩm</span>
                                </a>
                                <a href="index.php?controller=adminorder" class="dashboard-quick-link">
                                    <i class="fas fa-receipt text-success"></i> <span>Đơn vận hành</span>
                                </a>
                            <?php endif; ?>

                            <?php if ($isAdmin): ?>
                                <a href="index.php?controller=adminsetting" class="dashboard-quick-link">
                                    <i class="fas fa-store text-warning"></i> <span>Thông tin cửa hàng</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>