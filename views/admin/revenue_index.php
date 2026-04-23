<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Quản lý doanh thu</h1>
        <p class="admin-page-subtitle">Theo dõi hiệu quả kinh doanh và các chỉ số bán hàng quan trọng của Karma Eyewear.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Quản lý doanh thu</li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-lg overflow-hidden h-100 bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-white-50 small font-weight-bold mb-1">TỔNG DOANH THU</div>
                                <div class="h3 font-weight-bold"><?= number_format($data['TotalRevenue'] ?? 0, 0, ',', '.') ?> ₫</div>
                            </div>
                            <div class="opacity-5 h2"><i class="fas fa-wallet text-white-50"></i></div>
                        </div>
                        <p class="mt-3 mb-0 small opacity-7">Dữ liệu từ tất cả đơn hàng thành công.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-lg overflow-hidden h-100 bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-white-50 small font-weight-bold mb-1">DOANH THU THÁNG</div>
                                <div class="h3 font-weight-bold"><?= number_format($data['MonthRevenue'] ?? 0, 0, ',', '.') ?> ₫</div>
                            </div>
                            <div class="opacity-5 h2"><i class="fas fa-chart-line text-white-50"></i></div>
                        </div>
                        <p class="mt-3 mb-0 small opacity-7">Số tiền thu được trong tháng <?= date('m/Y') ?>.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12 mb-4">
                <div class="card border-0 shadow-sm rounded-lg overflow-hidden h-100 bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-white-50 small font-weight-bold mb-1">HÔM NAY</div>
                                <div class="h3 font-weight-bold"><?= number_format($data['TodayRevenue'] ?? 0, 0, ',', '.') ?> ₫</div>
                            </div>
                            <div class="opacity-5 h2"><i class="fas fa-coins text-white-50"></i></div>
                        </div>
                        <p class="mt-3 mb-0 small opacity-7">Doanh thu chốt trong ngày hôm nay.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body border-left-cod">
                        <div class="small text-muted font-weight-bold">THANH TOÁN COD (Tiền mặt)</div>
                        <div class="h4 font-weight-bold mt-1 text-dark"><?= number_format($data['RevenueCOD'] ?? 0, 0, ',', '.') ?> ₫</div>
                        <div class="progress mt-3" style="height: 5px;">
                            <div class="progress-bar bg-secondary" style="width: 100%"></div>
                        </div>
                        <p class="mt-2 mb-0 small text-muted">Hỗ trợ bởi đơn vị vận chuyển.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body border-left-vnpay">
                        <div class="small text-muted font-weight-bold text-uppercase">Thanh toán Online (VNPay)</div>
                        <div class="h4 font-weight-bold mt-1 text-primary"><?= number_format($data['RevenueVNPAY'] ?? 0, 0, ',', '.') ?> ₫</div>
                        <div class="progress mt-3" style="height: 5px;">
                            <div class="progress-bar bg-primary" style="width: 100%"></div>
                        </div>
                        <p class="mt-2 mb-0 small text-muted">Cổng thanh toán điện tử VNPay.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-0 pt-4">
                <h5 class="card-title font-weight-bold">Chỉ số bán hàng chi tiết</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 border-right">
                        <div class="p-3">
                            <div class="text-muted small">Tổng sản phẩm đã bán</div>
                            <div class="h4 font-weight-bold"><?= $data['TotalProductSold'] ?? 0 ?> <small>SP</small></div>
                        </div>
                    </div>
                    <div class="col-md-4 border-right">
                        <div class="p-3">
                            <div class="text-muted small">Đơn hàng thành công</div>
                            <div class="h4 font-weight-bold text-success"><?= $data['TotalOrders'] ?? 0 ?> <small>Đơn</small></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3">
                            <div class="text-muted small">Thanh toán thất bại</div>
                            <div class="h4 font-weight-bold text-danger"><?= $data['FailedPayments'] ?? 0 ?> <small>Đơn</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<style>
    .rounded-lg { border-radius: 12px !important; }
    .opacity-5 { opacity: 0.5; }
    .opacity-7 { opacity: 0.7; }
    .border-left-cod { border-left: 5px solid #6c757d; }
    .border-left-vnpay { border-left: 5px solid #007bff; }
</style>