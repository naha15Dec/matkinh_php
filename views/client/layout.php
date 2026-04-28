<?php
$sessionAccount = $_SESSION['LoginInformation'] ?? null;
$isLoggedIn = $sessionAccount !== null;

$brandName = !empty($storeInfo['TenCuaHang']) ? $storeInfo['TenCuaHang'] : "Karma Eyewear";
$hotline   = $storeInfo['Hotline'] ?? "0123.456.789";
$diaChi    = $storeInfo['DiaChi'] ?? "Hệ thống cửa hàng chính hãng";
$moTaNgan  = $storeInfo['MoTaNgan'] ?? "Mắt kính thời trang, tinh tế và chuẩn phong cách hiện đại.";

$roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));
$isAdminLike = in_array($roleCode, ['ADMIN', 'STAFF', 'SHIPPER']);

$accountDisplay = $isLoggedIn
    ? ($sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Tài khoản')
    : "Tài khoản";

$currentController = strtolower($_GET['controller'] ?? 'home');

function activeMenu($controller, $currentController) {
    return $controller === $currentController ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Trang chủ') ?> - <?= htmlspecialchars($brandName) ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link rel="stylesheet" href="/BanMatKinh/public/css/home_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/product_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/product_detail.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/blog_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/blog_detail.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/cart_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/checkout_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/checkout_success.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/contact_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/error_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/auth_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/profile_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/order-detail.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/site_modal.css?v=<?= time() ?>">

    <style>
        :root {
            --black: #111111;
            --dark: #1b1b1b;
            --gray: #666666;
            --light: #f7f4ef;
            --cream: #f3eadf;
            --gold: #b88a44;
            --border: #e8e2d8;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #fff;
            color: var(--black);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        a:hover {
            text-decoration: none;
            color: var(--gold);
        }

        .topbar {
            background: var(--black);
            color: #f5f5f5;
            font-size: 13px;
            padding: 9px 0;
        }

        .site-header {
            background: rgba(255,255,255,.96);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            z-index: 1000;
        }

        .brand-logo {
            line-height: 1;
            display: inline-block;
        }

        .brand-logo .name {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: .5px;
        }

        .brand-logo .sub {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--gold);
            margin-top: 4px;
        }

        .navbar-nav .nav-link {
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .7px;
            padding: 30px 14px !important;
            color: var(--dark) !important;
            position: relative;
        }

        .navbar-nav .nav-link.active::after,
        .navbar-nav .nav-link:hover::after {
            content: "";
            position: absolute;
            left: 14px;
            right: 14px;
            bottom: 22px;
            height: 2px;
            background: var(--gold);
        }

        .header-action {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid var(--border);
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 8px;
            transition: .25s;
        }

        .header-action:hover {
            background: var(--black);
            color: #fff;
            border-color: var(--black);
        }

        .account-chip {
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 600;
            background: #fff;
        }

        .dropdown-menu {
            border-radius: 16px;
            padding: 10px;
        }

        .dropdown-item {
            border-radius: 10px;
            font-size: 14px;
            padding: 10px 14px;
        }

        .search-panel {
            display: none;
            background: var(--light);
            border-top: 1px solid var(--border);
            padding: 18px 0;
        }

        .search-input {
            height: 48px;
            border-radius: 999px;
            border: 1px solid var(--border);
            padding: 0 20px;
        }

        .btn-luxury {
            border-radius: 999px;
            padding: 11px 24px;
            background: var(--black);
            color: #fff;
            border: none;
            font-weight: 700;
        }

        .btn-luxury:hover {
            background: var(--gold);
            color: #fff;
        }

        .page-alert {
            border-radius: 16px;
            border: 0;
            box-shadow: 0 10px 30px rgba(0,0,0,.06);
        }

        main {
            min-height: 60vh;
        }

        .site-footer {
            background: var(--black);
            color: #fff;
            padding: 60px 0 24px;
            margin-top: 60px;
        }

        .footer-brand {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 800;
        }

        .footer-title {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 18px;
            color: var(--cream);
        }

        .site-footer p,
        .site-footer a,
        .site-footer li {
            color: #bdbdbd;
            font-size: 14px;
            line-height: 1.9;
        }

        .site-footer a:hover {
            color: var(--gold);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,.1);
            margin-top: 35px;
            padding-top: 20px;
            color: #999;
            font-size: 13px;
        }

        @media (max-width: 991px) {
            .navbar-nav .nav-link {
                padding: 14px 0 !important;
            }

            .navbar-nav .nav-link.active::after,
            .navbar-nav .nav-link:hover::after {
                display: none;
            }

            .header-actions {
                padding: 15px 0;
            }
        }
    </style>
</head>

<body>

<div class="topbar d-none d-md-block">
    <div class="container d-flex justify-content-between">
        <span><i class="fas fa-shipping-fast mr-2"></i> Miễn phí giao hàng cho đơn từ 1.000.000đ</span>
        <span><i class="fas fa-phone-alt mr-2"></i> Hotline: <?= htmlspecialchars($hotline) ?></span>
    </div>
</div>

<header class="site-header sticky-top">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light p-0">
            <a class="brand-logo" href="index.php?controller=home">
                <span class="name"><?= htmlspecialchars($brandName) ?></span>
                <span class="sub">Premium Eyewear</span>
            </a>

            <button class="navbar-toggler ml-auto" type="button" data-toggle="collapse" data-target="#mainNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= activeMenu('home', $currentController) ?>" href="index.php?controller=home">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= activeMenu('sanpham', $currentController) ?>" href="index.php?controller=sanpham">Mắt kính</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= activeMenu('blog', $currentController) ?>" href="index.php?controller=blog">Tạp chí</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= activeMenu('contact', $currentController) ?>" href="index.php?controller=contact">Liên hệ</a>
                    </li>
                </ul>

                <div class="header-actions d-flex align-items-center">
                    <button type="button" class="header-action" id="btnOpenSearch">
                        <i class="fas fa-search"></i>
                    </button>

                    <a class="header-action" href="index.php?controller=giohang" title="Giỏ hàng">
                        <i class="fas fa-shopping-bag"></i>
                    </a>

                    <div class="dropdown ml-2">
                        <a href="#" class="account-chip dropdown-toggle" data-toggle="dropdown">
                            <i class="far fa-user mr-1"></i>
                            <?= htmlspecialchars($accountDisplay) ?>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right shadow border-0">
                            <?php if ($isLoggedIn): ?>
                                <a class="dropdown-item" href="index.php?controller=profile">
                                    <i class="far fa-id-card mr-2"></i> Hồ sơ cá nhân
                                </a>

                                <?php if ($isAdminLike): ?>
                                    <a class="dropdown-item" href="index.php?controller=dashboard">
                                        <i class="fas fa-user-shield mr-2"></i> Khu vực quản trị
                                    </a>
                                <?php endif; ?>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item text-danger" href="index.php?controller=taikhoan&action=logout">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Đăng xuất
                                </a>
                            <?php else: ?>
                                <a class="dropdown-item" href="index.php?controller=taikhoan&action=login">
                                    <i class="fas fa-sign-in-alt mr-2"></i> Đăng nhập
                                </a>
                                <a class="dropdown-item" href="index.php?controller=taikhoan&action=register">
                                    <i class="fas fa-user-plus mr-2"></i> Đăng ký
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <div class="search-panel" id="searchPanel">
        <div class="container">
            <form action="index.php" method="GET" class="d-flex">
                <input type="hidden" name="controller" value="sanpham">
                <input class="form-control search-input" type="text" name="Keyword" placeholder="Tìm kính râm, gọng kính, thương hiệu...">
                <button class="btn btn-luxury ml-2" type="submit">Tìm kiếm</button>
                <button class="btn btn-light ml-2 rounded-circle" type="button" id="btnCloseSearch">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        </div>
    </div>
</header>

<div class="container mt-3">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show page-alert" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show page-alert" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>
</div>

<main>
    <?php
    if (isset($viewContent) && file_exists($viewContent)) {
        include $viewContent;
    } else {
        echo "
            <div class='container py-5 text-center'>
                <h3>Nội dung đang được cập nhật</h3>
                <p class='text-muted'>Vui lòng quay lại sau.</p>
            </div>
        ";
    }
    ?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="footer-brand mb-3"><?= htmlspecialchars($brandName) ?></div>
                <p><?= htmlspecialchars($moTaNgan) ?></p>
            </div>

            <div class="col-lg-2 col-md-4 mb-4">
                <h6 class="footer-title">Danh mục</h6>
                <ul class="list-unstyled">
                    <li><a href="index.php?controller=home">Trang chủ</a></li>
                    <li><a href="index.php?controller=sanpham">Mắt kính</a></li>
                    <li><a href="index.php?controller=blog">Tạp chí</a></li>
                    <li><a href="index.php?controller=contact">Liên hệ</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-4 mb-4">
                <h6 class="footer-title">Hỗ trợ</h6>
                <ul class="list-unstyled">
                    <li><a href="index.php?controller=contact">Tư vấn chọn kính</a></li>
                    <li><a href="index.php?controller=contact">Chính sách đổi trả</a></li>
                    <li><a href="index.php?controller=contact">Bảo hành sản phẩm</a></li>
                    <li><a href="index.php?controller=contact">Hướng dẫn thanh toán</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-4 mb-4">
                <h6 class="footer-title">Liên hệ</h6>
                <p>
                    <i class="fas fa-map-marker-alt mr-2"></i> <?= htmlspecialchars($diaChi) ?><br>
                    <i class="fas fa-phone-alt mr-2"></i> <?= htmlspecialchars($hotline) ?><br>
                    <i class="fas fa-envelope mr-2"></i> support@karmaeyewear.vn
                </p>
            </div>
        </div>

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between">
            <span>© <?= date('Y') ?> <?= htmlspecialchars($brandName) ?>. All rights reserved.</span>
            <span>Designed for modern eyewear shopping experience.</span>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(function () {
        $('#btnOpenSearch').on('click', function () {
            $('#searchPanel').slideToggle(180);
        });

        $('#btnCloseSearch').on('click', function () {
            $('#searchPanel').slideUp(180);
        });

        setTimeout(function () {
            $('.alert').fadeTo(400, 0).slideUp(400, function () {
                $(this).remove();
            });
        }, 3000);
    });
</script>

<?php include BASE_PATH . '/views/components/confirm_modal.php'; ?>

<script src="/BanMatKinh/public/js/lux_confirm.js?v=<?= time() ?>"></script>

</body>
</html>