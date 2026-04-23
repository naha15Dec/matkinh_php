<?php
// 1. Lấy thông tin tài khoản từ Session
$sessionAccount = $_SESSION['LoginInformation'] ?? null;
$isLoggedIn = $sessionAccount !== null;

// 2. Thông tin cửa hàng (Mặc định nếu chưa có data)
$brandName = !empty($storeInfo['TenCuaHang']) ? $storeInfo['TenCuaHang'] : "Karma Eyewear";
$hotline   = $storeInfo['Hotline'] ?? "0123.456.789";
$diaChi    = $storeInfo['DiaChi'] ?? "Hệ thống cửa hàng chính hãng";
$moTaNgan  = $storeInfo['MoTaNgan'] ?? "Chúng tôi mang đến những thiết kế mắt kính thời trang, tinh tế.";

// 3. Xác định quyền
$roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));
$isAdminLike = in_array($roleCode, ['ADMIN', 'STAFF', 'SHIPPER']);

$accountDisplay = $isLoggedIn 
    ? (!empty($sessionAccount['HoTen']) ? $sessionAccount['HoTen'] : $sessionAccount['TenDangNhap']) 
    : "Thành viên";
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Kính Mắt Cao Cấp' ?> - <?= $brandName ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.linearicons.com/free/1.0.0/icon-font.min.css">

    <link rel="stylesheet" href="/BanMatKinh/public/css/custom_layout.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/home_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/product_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/product_detail.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/blog_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/blog_detail.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/cart_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/checkout_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/contact_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/error_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/auth_style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="/BanMatKinh/public/css/profile_style.css?v=<?= time() ?>">
</head>
<body>

    <div class="topbar-eyewear d-none d-md-block">
        <div class="container d-flex justify-content-between">
            <span><i class="fas fa-truck mr-2"></i> Miễn phí giao hàng đơn từ 1.000.000đ</span>
            <span><i class="fas fa-phone-alt mr-2"></i> Hotline: <?= $hotline ?></span>
        </div>
    </div>

    <header class="main-header sticky-top">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light p-0">
                <a class="brand-logo" href="index.php">
                    <span class="brand-name d-block"><?= $brandName ?></span>
                    <span class="brand-sub">Premium Eyewear</span>
                </a>

                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?controller=sanpham">Mắt kính</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?controller=blog">Tạp chí</a></li>
                        <li class="nav-item"><a class="nav-link" href="index.php?controller=contact">Liên hệ</a></li>
                    </ul>

                    <div class="d-flex align-items-center">
                        <button class="action-btn" id="btnSearch"><i class="lnr lnr-magnifier"></i></button>
                        
                        <a href="index.php?controller=giohang" class="action-btn">
                            <i class="lnr lnr-cart"></i>
                        </a>

                        <div class="dropdown ml-3">
                            <a href="#" class="account-chip dropdown-toggle" data-toggle="dropdown">
                                <i class="far fa-user mr-1"></i> <?= htmlspecialchars($accountDisplay) ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow border-0">
                                <?php if ($isLoggedIn): ?>
                                    <a class="dropdown-item" href="index.php?controller=profile">Hồ sơ</a>
                                    <?php if ($isAdminLike): ?>
                                        <a class="dropdown-item" href="admin/index.php">Quản trị</a>
                                    <?php endif; ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="index.php?controller=taikhoan&action=logout">Đăng xuất</a>
                                <?php else: ?>
                                    <a class="dropdown-item" href="index.php?controller=taikhoan&action=login">Đăng nhập</a>
                                    <a class="dropdown-item" href="index.php?controller=taikhoan&action=register">Đăng ký</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <div id="searchPanel">
            <div class="container">
                <form action="index.php" method="GET" class="d-flex">
                    <input type="hidden" name="controller" value="sanpham">
                    <input type="text" name="Keyword" class="form-control" placeholder="Tìm kiếm mắt kính...">
                    <button type="submit" class="btn btn-dark ml-2">Tìm</button>
                    <button type="button" class="btn btn-light ml-1" id="btnCloseSearch"><i class="fa fa-times"></i></button>
                </form>
            </div>
        </div>
    </header>
    <div class="container mt-3">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 10px;">
                <i class="fas fa-check-circle mr-2"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 10px;">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <main>
        <?php 
            if(isset($viewContent) && file_exists($viewContent)){
                include $viewContent; 
            } else {
                echo "<div class='container py-5 text-center'><h3>Hệ thống đang cập nhật nội dung...</h3></div>";
            }
        ?>
    </main>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="brand-name text-white mb-3" style="font-size: 24px;"><?= $brandName ?></div>
                    <p style="color: #888; font-size: 14px;"><?= $moTaNgan ?></p>
                </div>
                <div class="col-md-4 mb-4">
                    <h6>LIÊN KẾT</h6>
                    <ul class="list-unstyled">
                        <li><a href="index.php?controller=sanpham">Sản phẩm mới</a></li>
                        <li><a href="#">Chính sách đổi trả</a></li>
                        <li><a href="#">Hướng dẫn chọn gọng kính</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h6>LIÊN HỆ</h6>
                    <p style="color: #888; font-size: 14px;">
                        <i class="fa fa-map-marker-alt mr-2"></i> <?= $diaChi ?><br>
                        <i class="fa fa-phone-alt mr-2 mt-2"></i> <?= $hotline ?>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $("#btnSearch").click(function () { $("#searchPanel").slideDown(); });
            $("#btnCloseSearch").click(function () { $("#searchPanel").slideUp(); });
        });
    </script>
</body>
</html>

<script>
    $(document).ready(function() {
        // Tự động ẩn thông báo sau 3 giây
        window.setTimeout(function() {
            $(".alert").fadeTo(500, 0).slideUp(500, function(){
                $(this).remove(); 
            });
        }, 3000);
    });
</script>