<?php include_once 'layout_data.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Dashboard' ?> - Karma Eyewear</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="/BanMatKinh/public/css/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="/BanMatKinh/public/css/adminlte.min.css">
    <link rel="stylesheet" href="/BanMatKinh/public/css/OverlayScrollbars.min.css">
    <link rel="stylesheet" href="/BanMatKinh/public/css/admin-layout.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="/BanMatKinh/index.php" class="nav-link"><i class="fas fa-globe mr-1"></i> Xem Website</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    <img src="<?= $avatar ?>" class="user-image img-circle elevation-2" alt="User">
                    <span class="d-none d-md-inline"><?= htmlspecialchars($displayName) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <li class="user-header bg-dark">
                        <img src="<?= $avatar ?>" class="img-circle elevation-2" alt="User">
                        <p><?= $displayName ?> <small><?= $roleName ?></small></p>
                    </li>
                    <li class="user-footer">
                        <a href="index.php?controller=profile" class="btn btn-default btn-flat">Hồ sơ</a>
                        <a href="../index.php?controller=taikhoan&action=logout" class="btn btn-default btn-flat float-right text-danger">Đăng xuất</a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="index.php" class="brand-link">
            <span class="brand-text font-weight-light">KARMA ADMIN</span>
        </a>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="index.php?controller=dashboard" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>Dashboard</p>
                        </a>
                    </li>

                    <?php if ($roleCode === 'ADMIN' || $roleCode === 'STAFF'): ?>
                    <li class="nav-header">VẬN HÀNH</li>
                    <li class="nav-item">
                        <a href="index.php?controller=adminsanpham" class="nav-link">
                            <i class="nav-icon fas fa-glasses"></i><p>Quản lý sản phẩm</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="index.php?controller=adminorder" class="nav-link">
                            <i class="nav-icon fas fa-shopping-cart"></i>
                            <p>Đơn hàng
                                <?php if ($numberOfOrderProcessing > 0): ?>
                                    <span class="badge badge-danger right"><?= $numberOfOrderProcessing ?></span>
                                <?php endif; ?>
                            </p>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if ($roleCode === 'ADMIN'): ?>
                    <li class="nav-header">HỆ THỐNG</li>
                    <li class="nav-item">
                        <a href="index.php?controller=adminaccount" class="nav-link">
                            <i class="nav-icon fas fa-users"></i><p>Quản lý tài khoản</p>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <div class="container-fluid pt-3">
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
            <div class="container-fluid">
                <?php 
                    if(isset($viewContent) && file_exists($viewContent)){
                        include $viewContent; 
                    }
                ?>
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>Copyright &copy; <?= date('Y') ?> Karma Eyewear.</strong>
    </footer>
</div>

<script src="/BanMatKinh/public/scripts/jquery.min.js"></script>
<script src="/BanMatKinh/public/scripts/bootstrap.bundle.min.js"></script>
<script src="/BanMatKinh/public/scripts/adminlte.min.js"></script>
<script>
    $(document).ready(function() {
        // Tự động ẩn thông báo sau 3 giây (Giống Client)
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){ $(this).remove(); });
        }, 3000);
    });
</script>
</body>
</html>