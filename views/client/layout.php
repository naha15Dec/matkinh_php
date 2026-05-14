<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$storeInfo = $storeInfo ?? [];

$sessionAccount = $_SESSION['LoginInformation'] ?? null;
$isLoggedIn = $sessionAccount !== null;

$brandName = !empty($storeInfo['TenCuaHang']) ? $storeInfo['TenCuaHang'] : "Karma Eyewear";
$hotline   = !empty($storeInfo['Hotline']) ? $storeInfo['Hotline'] : "0123.456.789";
$email     = !empty($storeInfo['Email']) ? $storeInfo['Email'] : "support@karmaeyewear.vn";
$diaChi    = !empty($storeInfo['DiaChi']) ? $storeInfo['DiaChi'] : "Hệ thống cửa hàng chính hãng";
$moTaNgan  = !empty($storeInfo['MoTaNgan']) ? $storeInfo['MoTaNgan'] : "Mắt kính thời trang, tinh tế và chuẩn phong cách hiện đại.";

$facebookUrl = $storeInfo['FacebookUrl'] ?? '';
$instagramUrl = $storeInfo['InstagramUrl'] ?? '';
$zaloUrl = $storeInfo['ZaloUrl'] ?? '';

$roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));
$isAdminLike = in_array($roleCode, ['ADMIN', 'STAFF', 'SHIPPER'], true);

$accountDisplay = $isLoggedIn
    ? ($sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Tài khoản')
    : "Tài khoản";

$currentController = strtolower($_GET['controller'] ?? 'home');

$cartItems = $_SESSION['ShoppingCart'] ?? [];
$cartCount = 0;

if (is_array($cartItems)) {
    foreach ($cartItems as $cartItem) {
        $cartCount += (int)($cartItem['SoLuong'] ?? 0);
    }
}

if (!function_exists('activeMenu')) {
    function activeMenu($controller, $currentController) {
        return strtolower($controller) === strtolower($currentController) ? 'active' : '';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Trang chủ', ENT_QUOTES, 'UTF-8') ?> - <?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link rel="stylesheet" href="/BanMatKinh/public/css/client_layout.css?v=<?= time() ?>">

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
</head>

<body>

<div class="client-topbar d-none d-md-block">
    <div class="container">
        <div class="client-topbar__inner">
            <span class="client-topbar__item">
                <i class="fas fa-shipping-fast"></i>
                Miễn phí giao hàng cho đơn từ 1.000.000đ
            </span>

            <span class="client-topbar__item">
                <i class="fas fa-gem"></i>
                Chính hãng · Bảo hành · Tư vấn chọn kính
            </span>

            <span class="client-topbar__item">
                <i class="fas fa-phone-alt"></i>
                Hotline: <?= htmlspecialchars($hotline, ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
    </div>
</div>

<header class="client-header sticky-top">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light client-navbar p-0">
            <a class="client-brand" href="index.php?controller=home">
                <span class="client-brand__name"><?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="client-brand__sub">Premium Eyewear</span>
            </a>

            <button class="navbar-toggler client-toggler ml-auto" type="button" data-toggle="collapse" data-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Mở menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse client-navbar-collapse" id="mainNavbar">
                <ul class="navbar-nav client-menu mx-auto">
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

                <div class="client-header-actions">
                    <button type="button" class="client-header-action" id="btnOpenSearch" title="Tìm kiếm" aria-label="Tìm kiếm">
                        <i class="fas fa-search"></i>
                    </button>

                    <a class="client-header-action client-cart-link" href="index.php?controller=giohang" title="Giỏ hàng" aria-label="Giỏ hàng">
                        <i class="fas fa-shopping-bag"></i>

                        <?php if ($cartCount > 0): ?>
                            <span class="client-cart-count"><?= $cartCount > 99 ? '99+' : (int)$cartCount ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="dropdown client-account-dropdown">
                        <a href="#" class="client-account-chip dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="far fa-user"></i>
                            <span><?= htmlspecialchars($accountDisplay, ENT_QUOTES, 'UTF-8') ?></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right client-dropdown-menu shadow border-0">
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

    <div class="client-search-panel" id="searchPanel">
        <div class="container">
            <form action="index.php" method="GET" class="client-search-box">
                <input type="hidden" name="controller" value="sanpham">

                <span class="client-search-icon">
                    <i class="fas fa-search"></i>
                </span>

                <input class="form-control client-search-input"
                       type="text"
                       name="Keyword"
                       placeholder="Tìm kính râm, gọng kính, thương hiệu..."
                       autocomplete="off"
                       id="luxSearchInput">

                <button class="client-btn-luxury" type="submit">
                    Tìm kiếm
                </button>

                <button class="client-search-close" type="button" id="btnCloseSearch" aria-label="Đóng tìm kiếm">
                    <i class="fas fa-times"></i>
                </button>
            </form>
        </div>
    </div>
</header>

<div class="container mt-3 client-alert-wrap">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show client-page-alert" role="alert">
            <i class="fas fa-check-circle"></i>
            <span>
                <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
            </span>
            <?php unset($_SESSION['success']); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Đóng">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show client-page-alert" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <span>
                <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
            </span>
            <?php unset($_SESSION['error']); ?>

            <button type="button" class="close" data-dismiss="alert" aria-label="Đóng">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>
</div>

<main class="client-main">
    <?php
    if (isset($viewContent) && file_exists($viewContent)) {
        include $viewContent;
    } else {
        echo "
            <div class='container'>
                <div class='client-empty-content'>
                    <div class='client-empty-content__icon'>
                        <i class='far fa-file-alt'></i>
                    </div>
                    <h3>Nội dung đang được cập nhật</h3>
                    <p class='text-muted mb-0'>Vui lòng quay lại sau.</p>
                </div>
            </div>
        ";
    }
    ?>
</main>

<footer class="client-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="client-footer__brand"><?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?></div>

                <p class="client-footer__desc">
                    <?= htmlspecialchars($moTaNgan, ENT_QUOTES, 'UTF-8') ?>
                </p>

                <?php if (!empty($facebookUrl) || !empty($instagramUrl) || !empty($zaloUrl)): ?>
                    <div class="client-footer-socials">
                        <?php if (!empty($facebookUrl)): ?>
                            <a href="<?= htmlspecialchars($facebookUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" title="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($instagramUrl)): ?>
                            <a href="<?= htmlspecialchars($instagramUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" title="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($zaloUrl)): ?>
                            <a href="<?= htmlspecialchars($zaloUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" title="Zalo">
                                <i class="fas fa-comment"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-2 col-md-4 mb-4">
                <h6 class="client-footer__title">Danh mục</h6>

                <ul class="list-unstyled mb-0">
                    <li><a href="index.php?controller=home">Trang chủ</a></li>
                    <li><a href="index.php?controller=sanpham">Mắt kính</a></li>
                    <li><a href="index.php?controller=blog">Tạp chí</a></li>
                    <li><a href="index.php?controller=contact">Liên hệ</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-4 mb-4">
                <h6 class="client-footer__title">Hỗ trợ</h6>

                <ul class="list-unstyled mb-0">
                    <li><a href="index.php?controller=contact">Tư vấn chọn kính</a></li>
                    <li><a href="index.php?controller=contact">Chính sách đổi trả</a></li>
                    <li><a href="index.php?controller=contact">Bảo hành sản phẩm</a></li>
                    <li><a href="index.php?controller=contact">Hướng dẫn thanh toán</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-4 mb-4">
                <h6 class="client-footer__title">Liên hệ</h6>

                <div class="client-footer-contact">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?= htmlspecialchars($diaChi, ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="client-footer-contact">
                    <i class="fas fa-phone-alt"></i>
                    <span><?= htmlspecialchars($hotline, ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <div class="client-footer-contact">
                    <i class="fas fa-envelope"></i>
                    <span><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        </div>

        <div class="client-footer-bottom">
            <span>© <?= date('Y') ?> <?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?>. All rights reserved.</span>
            <span>Premium Eyewear.</span>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(function () {
        $('#btnOpenSearch').on('click', function () {
            $('#searchPanel').stop(true, true).slideToggle(180, function () {
                if ($('#searchPanel').is(':visible')) {
                    $('#luxSearchInput').trigger('focus');
                }
            });
        });

        $('#btnCloseSearch').on('click', function () {
            $('#searchPanel').stop(true, true).slideUp(180);
        });

        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                $('#searchPanel').stop(true, true).slideUp(180);
            }
        });

        setTimeout(function () {
            $('.client-page-alert').fadeTo(400, 0).slideUp(400, function () {
                $(this).remove();
            });
        }, 3200);
    });
</script>

<?php
$confirmModal = BASE_PATH . '/views/components/confirm_modal.php';

if (file_exists($confirmModal)) {
    include $confirmModal;
}
?>

<script src="/BanMatKinh/public/js/lux_confirm.js?v=<?= time() ?>"></script>

</body>
</html>