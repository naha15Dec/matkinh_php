<?php include_once 'layout_data.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Dashboard' ?> - Karma Eyewear</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/BanMatKinh/public/css/adminlte.min.css">
    <link rel="stylesheet" href="/BanMatKinh/public/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="/BanMatKinh/public/css/admin-layout.css">
    
    <style>
        .nav-header { font-weight: 700; color: #adb5bd !important; border-bottom: 1px solid #4b545c; margin-top: 10px; }
        .user-panel img { object-fit: cover; width: 34px; height: 34px; }
        .brand-link { border-bottom: 1px solid #4b545c !important; }
        .main-sidebar { background-color: #1a1d20 !important; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0 shadow-sm">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="/BanMatKinh/index.php" class="nav-link"><i class="fas fa-external-link-alt mr-1"></i> Xem Website</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mr-2 shadow-sm" style="width: 32px; height: 32px; font-weight: bold; font-size: 14px;">
                        <?= strtoupper(mb_substr($displayName, 0, 1)) ?>
                    </div>
                    <span class="d-none d-md-inline font-weight-bold"><?= htmlspecialchars($displayName) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow border-0">
                    <li class="user-header bg-dark">
                         <div class="rounded-circle bg-light text-dark d-inline-flex align-items-center justify-content-center mb-2" style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">
                            <?= strtoupper(mb_substr($displayName, 0, 1)) ?>
                        </div>
                        <p class="mb-0"><?= $displayName ?></p>
                        <small class="badge badge-warning"><?= $roleName ?></small>
                    </li>
                    <li class="user-footer bg-light">
                        <a href="index.php?controller=adminprofile" class="btn btn-default btn-flat rounded">Hồ sơ</a>
                        <a href="../index.php?controller=taikhoan&action=logout" class="btn btn-default btn-flat float-right text-danger rounded">Đăng xuất</a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index.php" class="brand-link text-center">
            <span class="brand-text font-weight-bold letter-spacing-1">KARMA <span class="text-primary">ADMIN</span></span>
        </a>

        <div class="sidebar">
            <nav class="mt-3">
                <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu">
                    
                    <li class="nav-item">
                        <a href="index.php?controller=dashboard" class="nav-link <?= ($_GET['controller'] ?? '') == 'dashboard' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>

                    <?php if ($roleCode === 'ADMIN' || $roleCode === 'STAFF'): ?>
                    <li class="nav-header text-uppercase small">Quản lý kho hàng</li>
                    <li class="nav-item">
                        <a href="index.php?controller=adminsanpham" class="nav-link <?= ($_GET['controller'] ?? '') == 'adminsanpham' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-glasses"></i><p>Sản phẩm</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?controller=adminorder" class="nav-link <?= ($_GET['controller'] ?? '') == 'adminorder' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Đơn hàng
                                <?php if (isset($numberOfOrderProcessing) && $numberOfOrderProcessing > 0): ?>
                                    <span class="badge badge-danger right"><?= $numberOfOrderProcessing ?></span>
                                <?php endif; ?>
                            </p>
                        </a>
                    </li>
                    
                    <li class="nav-header text-uppercase small">Nội dung & Tin tức</li>
                    <li class="nav-item">
                        <a href="index.php?controller=adminblog" class="nav-link <?= ($_GET['controller'] ?? '') == 'adminblog' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-newspaper"></i><p>Tạp chí / Bài viết</p>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($roleCode === 'ADMIN'): ?>
                    <li class="nav-header text-uppercase small">Báo cáo & Hệ thống</li>
                    <li class="nav-item">
                        <a href="index.php?controller=adminrevenue" class="nav-link <?= ($_GET['controller'] ?? '') == 'adminrevenue' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-chart-bar"></i><p>Báo cáo doanh thu</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?controller=adminaccount" class="nav-link <?= ($_GET['controller'] ?? '') == 'adminaccount' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-users-cog"></i><p>Quản lý tài khoản</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?controller=adminsetting" class="nav-link <?= ($_GET['controller'] ?? '') == 'adminsetting' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-cogs"></i><p>Cấu hình Website</p>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-header">CÁ NHÂN</li>
                    <li class="nav-item">
                        <a href="index.php?controller=adminprofile" class="nav-link <?= ($_GET['controller'] ?? '') == 'adminprofile' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-user-circle"></i><p>Thông tin của tôi</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper bg-light">
        <div class="container-fluid pt-3 px-4">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            <?php endif; ?>
        </div>

        <section class="content">
            <div class="container-fluid px-4">
                <?php 
                    if(isset($viewContent) && file_exists($viewContent)){
                        include $viewContent; 
                    } else {
                        echo "<div class='text-center py-5'><i class='fas fa-folder-open fa-3x text-muted mb-3'></i><p>Nội dung đang được cập nhật...</p></div>";
                    }
                ?>
            </div>
        </section>
    </div>

    <footer class="main-footer text-sm">
        <div class="float-right d-none d-sm-inline">
            Phiên bản 2.0
        </div>
        <strong>Copyright &copy; <?= date('Y') ?> <a href="#" class="text-primary">Karma Eyewear</a>.</strong> Bảo lưu mọi quyền.
    </footer>
</div>

<script src="/BanMatKinh/public/scripts/jquery.min.js"></script>
<script src="/BanMatKinh/public/scripts/bootstrap.bundle.min.js"></script>
<script src="/BanMatKinh/public/scripts/adminlte.min.js"></script>
<script>
    $(document).ready(function() {
        // Tự động ẩn thông báo sau 4 giây
        setTimeout(function() {
            $(".alert-success").fadeTo(500, 0).slideUp(500, function(){ $(this).remove(); });
        }, 4000);
    });
</script>
</body>
</html>