<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($baseUrl === '\\' || $baseUrl === '/') {
    $baseUrl = '';
}

$title = $title ?? "Admin";
$viewContent = $viewContent ?? null;

$account = $_SESSION['LoginInformation'] ?? null;

$displayName = $account['HoTen'] ?? "Tài khoản";
$displayUsername = $account['TenDangNhap'] ?? "";
$roleCode = strtoupper(trim($account['MaVaiTro'] ?? ""));
$roleName = $account['TenVaiTro'] ?? $roleCode;

$isAdmin = $roleCode === 'ADMIN';
$isStaff = $roleCode === 'STAFF';
$isShipper = $roleCode === 'SHIPPER';

$currentController = strtolower($_GET['controller'] ?? 'dashboard');

$numberOfOrderProcessing = $numberOfOrderProcessing ?? 0;
$numberOfAssignedOrders = $numberOfAssignedOrders ?? 0;
$numberOfBlogWaitingApproval = $numberOfBlogWaitingApproval ?? 0;

function isActive($controller, $currentController) {
    return strtolower($controller) === strtolower($currentController) ? 'active' : '';
}

function isMenuOpen($controllers, $currentController) {
    return in_array(strtolower($currentController), array_map('strtolower', $controllers)) ? 'menu-open' : '';
}

function isGroupActive($controllers, $currentController) {
    return in_array(strtolower($currentController), array_map('strtolower', $controllers)) ? 'active' : '';
}

function assetPath($path, $baseUrl) {
    return $baseUrl . '/' . ltrim($path, '/');
}

$rawAvatar = $account['AnhDaiDien'] ?? '';

if (!empty($rawAvatar)) {
    if (preg_match('/^(http|https):\/\//', $rawAvatar) || str_starts_with($rawAvatar, '/')) {
        $avatar = $rawAvatar;
    } else {
        $avatar = assetPath($rawAvatar, $baseUrl);
    }
} else {
    $avatar = assetPath('img/default-avatar.png', $baseUrl);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> - Karma Eyewear Admin</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,500,600,700&display=fallback">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="<?= assetPath('css/admin-layout.css', $baseUrl) ?>">
    <link rel="stylesheet" href="<?= assetPath('css/admin-dashboard.css', $baseUrl) ?>">
    <link rel="stylesheet" href="<?= assetPath('css/admin-account.css', $baseUrl) ?>">
    <link rel="stylesheet" href="<?= assetPath('css/admin-blog.css', $baseUrl) ?>">
    <link rel="stylesheet" href="<?= assetPath('css/admin-brand.css', $baseUrl) ?>">
    <link rel="stylesheet" href="<?= assetPath('css/admin-order.css', $baseUrl) ?>">
    <link rel="stylesheet" href="<?= assetPath('css/admin-product.css', $baseUrl) ?>">
    <link rel="stylesheet" href="<?= assetPath('css/admin-type.css', $baseUrl) ?>">
    <link rel="stylesheet" href="<?= assetPath('css/admin-setting.css', $baseUrl) ?>">
    <link rel="stylesheet" href="<?= assetPath('css/admin-revenue.css', $baseUrl) ?>">
</head>

<body class="hold-transition sidebar-mini layout-fixed admin-eyewear-body">
<div class="wrapper">

    <nav class="main-header navbar navbar-expand admin-topbar">
        <ul class="navbar-nav align-items-center">
            <li class="nav-item">
                <a class="nav-link admin-icon-btn" data-widget="pushmenu" href="#">
                    <i class="fas fa-bars"></i>
                </a>
            </li>

            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= assetPath('index.php', $baseUrl) ?>" class="nav-link admin-home-link">
                    <i class="fas fa-globe-asia mr-1"></i>
                    Trang chủ website
                </a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto align-items-center">
            <li class="nav-item d-none d-md-block mr-3">
                <span class="admin-page-badge">
                    <i class="fas fa-glasses mr-1"></i>
                    Premium Eyewear Boutique
                </span>
            </li>

            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle admin-user-toggle" data-toggle="dropdown">
                    <img src="<?= htmlspecialchars($avatar) ?>" class="user-image img-circle elevation-2" alt="User">
                    <span class="d-none d-md-inline"><?= htmlspecialchars($displayName) ?></span>
                </a>

                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right admin-user-dropdown">
                    <li class="user-header">
                        <img src="<?= htmlspecialchars($avatar) ?>" class="img-circle elevation-2" alt="User">

                        <p>
                            <?= htmlspecialchars($displayName) ?>
                            <small><?= htmlspecialchars($displayUsername) ?></small>
                            <small><?= htmlspecialchars($roleName) ?></small>
                        </p>
                    </li>

                    <li class="user-footer">
                        <a href="<?= assetPath('index.php?controller=adminprofile', $baseUrl) ?>" class="btn btn-outline-dark btn-sm">
                            Hồ sơ
                        </a>

                        <a href="<?= assetPath('index.php?controller=taikhoan&action=logout', $baseUrl) ?>" class="btn btn-dark btn-sm float-right">
                            Đăng xuất
                        </a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link admin-icon-btn" data-widget="fullscreen" href="#">
                    <i class="fas fa-expand-arrows-alt"></i>
                </a>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar admin-sidebar elevation-4">
        <a href="<?= assetPath('index.php?controller=dashboard', $baseUrl) ?>" class="brand-link admin-brand">
            <span class="brand-mark">K</span>
            <span class="brand-text">Karma Eyewear</span>
        </a>

        <div class="sidebar">
            <div class="admin-sidebar-user">
                <img src="<?= htmlspecialchars($avatar) ?>" class="admin-sidebar-avatar" alt="Avatar">

                <div>
                    <div class="admin-sidebar-name"><?= htmlspecialchars($displayName) ?></div>
                    <div class="admin-sidebar-role"><?= htmlspecialchars($roleName) ?></div>
                </div>
            </div>

            <nav class="mt-3">
                <ul class="nav nav-pills nav-sidebar flex-column admin-menu" data-widget="treeview" role="menu" data-accordion="false">

                    <li class="nav-item">
                        <a href="<?= assetPath('index.php?controller=dashboard', $baseUrl) ?>" class="nav-link <?= isActive('dashboard', $currentController) ?>">
                            <i class="nav-icon fas fa-th-large"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?= assetPath('index.php?controller=adminprofile', $baseUrl) ?>" class="nav-link <?= isActive('adminprofile', $currentController) ?>">
                            <i class="nav-icon fas fa-user-circle"></i>
                            <p>Hồ sơ cá nhân</p>
                        </a>
                    </li>

                    <?php if ($isAdmin || $isStaff): ?>
                        <li class="nav-header">TÁC NGHIỆP</li>

                        <li class="nav-item">
                            <a href="<?= assetPath('index.php?controller=admindonhang', $baseUrl) ?>" class="nav-link <?= isActive('admindonhang', $currentController) ?>">
                                <i class="nav-icon fas fa-shopping-bag"></i>
                                <p>
                                    Đơn hàng
                                    <?php if ($numberOfOrderProcessing > 0): ?>
                                        <span class="badge badge-danger right"><?= (int)$numberOfOrderProcessing ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>

                        <li class="nav-item has-treeview <?= isMenuOpen(['adminsanpham'], $currentController) ?>">
                            <a href="#" class="nav-link <?= isActive('adminsanpham', $currentController) ?>">
                                <i class="nav-icon fas fa-glasses"></i>
                                <p>Sản phẩm <i class="right fas fa-angle-left"></i></p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= assetPath('index.php?controller=adminsanpham&action=edit', $baseUrl) ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thêm sản phẩm</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?= assetPath('index.php?controller=adminsanpham', $baseUrl) ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách sản phẩm</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item has-treeview <?= isMenuOpen(['adminblog'], $currentController) ?>">
                            <a href="#" class="nav-link <?= isActive('adminblog', $currentController) ?>">
                                <i class="nav-icon fas fa-newspaper"></i>
                                <p>Bài viết <i class="right fas fa-angle-left"></i></p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= assetPath('index.php?controller=adminblog&action=edit', $baseUrl) ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thêm bài viết</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?= assetPath('index.php?controller=adminblog', $baseUrl) ?>" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Danh sách bài viết</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if ($isShipper): ?>
                        <li class="nav-header">GIAO HÀNG</li>

                        <li class="nav-item">
                            <a href="<?= assetPath('index.php?controller=admindonhang', $baseUrl) ?>" class="nav-link <?= isActive('admindonhang', $currentController) ?>">
                                <i class="nav-icon fas fa-truck"></i>
                                <p>
                                    Đơn được giao
                                    <?php if ($numberOfAssignedOrders > 0): ?>
                                        <span class="badge badge-danger right"><?= (int)$numberOfAssignedOrders ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if ($isAdmin): ?>
                        <li class="nav-header">QUẢN TRỊ</li>

                        <li class="nav-item">
                            <a href="<?= assetPath('index.php?controller=adminblog&status=draft', $baseUrl) ?>" class="nav-link <?= isActive('adminblog', $currentController) ?>">
                                <i class="nav-icon fas fa-check-circle"></i>
                                <p>
                                    Kiểm duyệt bài viết
                                    <?php if ($numberOfBlogWaitingApproval > 0): ?>
                                        <span class="badge badge-danger right"><?= (int)$numberOfBlogWaitingApproval ?></span>
                                    <?php endif; ?>
                                </p>
                            </a>
                        </li>

                        <li class="nav-item has-treeview <?= isMenuOpen(['admintype', 'adminbrand'], $currentController) ?>">
                            <a href="#" class="nav-link <?= isGroupActive(['admintype', 'adminbrand'], $currentController) ?>">
                                <i class="nav-icon fas fa-tags"></i>
                                <p>Danh mục hệ thống <i class="right fas fa-angle-left"></i></p>
                            </a>

                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="<?= assetPath('index.php?controller=admintype', $baseUrl) ?>" class="nav-link <?= isActive('admintype', $currentController) ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Loại sản phẩm</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?= assetPath('index.php?controller=adminbrand', $baseUrl) ?>" class="nav-link <?= isActive('adminbrand', $currentController) ?>">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Thương hiệu</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="<?= assetPath('index.php?controller=admintaikhoan', $baseUrl) ?>" class="nav-link <?= isActive('admintaikhoan', $currentController) ?>">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>Quản lý tài khoản</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= assetPath('index.php?controller=adminrevenue', $baseUrl) ?>" class="nav-link <?= isActive('adminrevenue', $currentController) ?>">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p>Doanh thu</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= assetPath('index.php?controller=adminsetting', $baseUrl) ?>" class="nav-link <?= isActive('adminsetting', $currentController) ?>">
                                <i class="nav-icon fas fa-store"></i>
                                <p>Thông tin cửa hàng</p>
                            </a>
                        </li>
                    <?php endif; ?>

                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper admin-content-wrapper">
        <section class="content pt-3">
            <div class="container-fluid">
                <?php
                if ($viewContent && file_exists($viewContent)) {
                    include $viewContent;
                } else {
                    echo '<div class="alert alert-warning">Không tìm thấy nội dung trang.</div>';
                }
                ?>
            </div>
        </section>
    </div>

    <footer class="main-footer admin-footer">
        <strong>&copy; <?= date('Y') ?> Karma Eyewear.</strong>
        <div class="float-right d-none d-sm-inline-block">
            Admin Boutique Panel
        </div>
    </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>