<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Dashboard</h1>
        <p class="admin-page-subtitle">Chào mừng trở lại, <strong><?= htmlspecialchars($sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap']) ?></strong>. Đây là tổng quan hoạt động của Karma Eyewear hôm nay.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <?php if ($isShipper): ?>
            <div class="alert alert-info border-0 shadow-sm mb-4" style="border-left: 5px solid #17a2b8 !important;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-truck-moving fa-2x mr-3"></i>
                    <div>
                        <h5 class="mb-1 font-weight-bold">Lịch trình giao hàng</h5>
                        <p class="mb-0">Bạn đang có <strong><?= $ordersInDelivery ?? 0 ?></strong> đơn hàng cần giao. Hãy cập nhật trạng thái ngay sau khi giao xong nhé!</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isAdmin || $isStaff): ?>
            <div class="row">
                <div class="col-lg-3 col-sm-6">
                    <div class="dashboard-stat-card border-0 shadow-sm">
                        <div class="dashboard-stat-top">
                            <div>
                                <div class="dashboard-stat-label">Đơn hàng mới</div>
                                <div class="dashboard-stat-value text-danger">
                                    <?= $countPendingOrders ?? 0 ?> <small style="font-size: 14px;">đơn</small>
                                </div>
                            </div>
                            <div class="dashboard-stat-icon icon-orders bg-light-danger">
                                <i class="fas fa-shopping-bag text-danger"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat-desc">Đơn hàng đang chờ bạn xác nhận và chuẩn bị kho.</p>
                        <a href="index.php?controller=adminorder" class="dashboard-stat-link">
                            Xử lý ngay <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="dashboard-stat-card border-0 shadow-sm">
                        <div class="dashboard-stat-top">
                            <div>
                                <div class="dashboard-stat-label">Doanh thu nay</div>
                                <div class="dashboard-stat-value text-success">
                                    <?php if ($isAdmin): ?>
                                        <?= number_format($todayRevenue ?? 0, 0, ',', '.') ?> <small style="font-size: 12px;">₫</small>
                                    <?php else: ?>
                                        Ổn định
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="dashboard-stat-icon icon-revenue bg-light-success">
                                <i class="fas fa-chart-line text-success"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat-desc">Tổng tiền thu được từ các đơn giao thành công hôm nay.</p>
                        <a href="index.php?controller=adminrevenue" class="dashboard-stat-link">
                            Xem báo cáo <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="dashboard-stat-card border-0 shadow-sm">
                        <div class="dashboard-stat-top">
                            <div>
                                <div class="dashboard-stat-label">Kho hàng</div>
                                <div class="dashboard-stat-value <?= ($lowStockCount ?? 0) > 0 ? 'text-warning' : '' ?>">
                                    <?= $lowStockCount ?? 0 ?> <small style="font-size: 14px;">sắp hết</small>
                                </div>
                            </div>
                            <div class="dashboard-stat-icon icon-products bg-light-warning">
                                <i class="fas fa-glasses text-warning"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat-desc">Số lượng mẫu kính mắt có tồn kho dưới 5 sản phẩm.</p>
                        <a href="index.php?controller=adminsanpham&statusProduct=outofstock" class="dashboard-stat-link">
                            Nhập hàng <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="dashboard-stat-card border-0 shadow-sm">
                        <div class="dashboard-stat-top">
                            <div>
                                <div class="dashboard-stat-label">Nhân sự</div>
                                <div class="dashboard-stat-value">Hệ thống</div>
                            </div>
                            <div class="dashboard-stat-icon icon-users bg-light-primary">
                                <i class="fas fa-users text-primary"></i>
                            </div>
                        </div>
                        <p class="dashboard-stat-desc">Quản lý danh sách nhân viên, cộng tác viên và shipper.</p>
                        <a href="index.php?controller=adminaccount" class="dashboard-stat-link">
                            Phân quyền <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="row mt-4">
            <div class="col-lg-8 mb-4">
                <div class="card dashboard-panel shadow-sm border-0">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title font-weight-bold mb-0">Quy trình vận hành chuẩn</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center p-3">
                                <div class="mb-2 text-primary"><i class="fas fa-check-double fa-2x"></i></div>
                                <h6 class="font-weight-bold">1. Xác nhận đơn</h6>
                                <p class="small text-muted">Kiểm tra thông tin khách hàng và kho hàng.</p>
                            </div>
                            <div class="col-md-4 text-center p-3 border-left border-right">
                                <div class="mb-2 text-warning"><i class="fas fa-box-open fa-2x"></i></div>
                                <h6 class="font-weight-bold">2. Đóng gói</h6>
                                <p class="small text-muted">Vệ sinh kính, đóng hộp và dán nhãn vận chuyển.</p>
                            </div>
                            <div class="col-md-4 text-center p-3">
                                <div class="mb-2 text-success"><i class="fas fa-shipping-fast fa-2x"></i></div>
                                <h6 class="font-weight-bold">3. Giao hàng</h6>
                                <p class="small text-muted">Bàn giao cho Shipper và theo dõi hành trình.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card dashboard-panel shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h5 class="card-title font-weight-bold mb-0">Lối tắt thao tác</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <a href="index.php?controller=adminsanpham&action=edit" class="list-group-item list-group-item-action border-0 d-flex align-items-center px-0">
                                <div class="bg-primary-soft p-2 rounded mr-3"><i class="fas fa-plus-circle text-primary"></i></div>
                                <span>Thêm sản phẩm mới</span>
                            </a>
                            <a href="index.php?controller=adminblog" class="list-group-item list-group-item-action border-0 d-flex align-items-center px-0">
                                <div class="bg-success-soft p-2 rounded mr-3"><i class="fas fa-pen-nib text-success"></i></div>
                                <span>Viết bài tin tức (Blog)</span>
                            </a>
                            <a href="index.php?controller=adminsetting" class="list-group-item list-group-item-action border-0 d-flex align-items-center px-0">
                                <div class="bg-warning-soft p-2 rounded mr-3"><i class="fas fa-store text-warning"></i></div>
                                <span>Cấu hình thông tin Shop</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
    /* Custom Styles cho Dashboard sống động hơn */
    .bg-light-danger { background-color: #f8d7da; }
    .bg-light-success { background-color: #d4edda; }
    .bg-light-warning { background-color: #fff3cd; }
    .bg-light-primary { background-color: #cce5ff; }
    
    .bg-primary-soft { background-color: rgba(0,123,255,0.1); }
    .bg-success-soft { background-color: rgba(40,167,69,0.1); }
    .bg-warning-soft { background-color: rgba(255,193,7,0.1); }
    
    .dashboard-stat-card {
        padding: 20px;
        border-radius: 12px;
        background: #fff;
        height: 100%;
        transition: transform 0.2s;
    }
    .dashboard-stat-card:hover { transform: translateY(-5px); }
    
    .dashboard-stat-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 20px;
    }
    
    .dashboard-stat-value {
        font-size: 24px;
        font-weight: 800;
        margin: 5px 0;
    }
    
    .dashboard-stat-label {
        font-size: 13px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
    }
    
    .dashboard-stat-desc {
        font-size: 13px;
        color: #888;
        margin-top: 15px;
        min-height: 40px;
    }
    
    .dashboard-stat-link {
        font-size: 13px;
        font-weight: 700;
        color: #007bff;
        text-decoration: none !important;
    }
</style>