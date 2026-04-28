<?php
$countdownEnd = date("Y-m-d\TH:i:s", strtotime("+7 days"));

if (!function_exists('formatMoney')) {
    function formatMoney($value) {
        return number_format((float)$value, 0, ',', '.');
    }
}

if (!function_exists('normalizeImg')) {
    function normalizeImg($path) {
        if (empty($path)) {
            return "public/images/no-image.png";
        }

        if (strpos($path, 'http') === 0) {
            return $path;
        }

        if (strpos($path, 'public/') === 0) {
            return $path;
        }

        return "public/images/" . trim($path, '/');
    }
}

$listDiscountProduct = $listDiscountProduct ?? [];
$listNewProduct = $listNewProduct ?? [];
$listDealHot = $listDealHot ?? [];
$listLatestBlog = $listLatestBlog ?? [];
$storeInfo = $storeInfo ?? [];

$totalFeatured = count($listDiscountProduct) + count($listNewProduct);
?>

<div class="home-modern">

    <section class="hero-eyewear">
        <div class="container">
            <div class="hero-shell">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">

                        <div class="hero-tag">
                            <i class="fa fa-star-o"></i>
                            Bộ sưu tập kính thời trang hiện đại
                        </div>

                        <h1 class="hero-title">
                            Tôn phong cách riêng với <br>
                            những mẫu kính tinh tế.
                        </h1>

                        <p class="hero-desc">
                            <?= !empty($storeInfo['MoTaNgan'])
                                ? htmlspecialchars($storeInfo['MoTaNgan'])
                                : "Khám phá các thiết kế mắt kính thời trang, thanh lịch hằng ngày tại Karma Eyewear." ?>
                        </p>

                        <form action="index.php" method="GET" class="hero-search-box">
                            <input type="hidden" name="controller" value="home">
                            <input type="hidden" name="action" value="findProductByID">

                            <input 
                                type="text" 
                                name="idProduct" 
                                placeholder="Nhập mã sản phẩm hoặc ID..."
                                autocomplete="off"
                            >

                            <button type="submit">
                                <i class="fa fa-search"></i>
                                Tìm nhanh
                            </button>
                        </form>

                        <div class="hero-actions">
                            <a href="index.php?controller=sanpham" class="btn-luxury primary">
                                Khám phá sản phẩm
                            </a>

                            <a href="index.php?controller=blog" class="btn-luxury secondary">
                                Xem xu hướng
                            </a>
                        </div>

                        <div class="hero-metrics">
                            <div class="hero-metric-item">
                                <h4><?= $totalFeatured ?></h4>
                                <p>Sản phẩm nổi bật</p>
                            </div>

                            <div class="hero-metric-item">
                                <h4>24/7</h4>
                                <p>Hỗ trợ tận tâm</p>
                            </div>

                            <div class="hero-metric-item">
                                <h4><?= count($listLatestBlog) ?></h4>
                                <p>Bài viết mới</p>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-6">
                        <div class="position-relative">
                            <div class="hero-visual-card">
                                <img 
                                    src="<?= !empty($storeInfo['Banner']) 
                                        ? normalizeImg($storeInfo['Banner']) 
                                        : '/BanMatKinh/public/images/banner/default-hero.png' ?>" 
                                    alt="Karma Eyewear"
                                >
                            </div>

                            <div class="floating-badge top">
                                <span>Xu hướng nổi bật</span>
                                <strong>Gọng tối giản cao cấp</strong>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="row benefit-grid">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="benefit-card">
                        <i class="fas fa-truck"></i>
                        <h5>Giao hàng toàn quốc</h5>
                        <p>Nhanh chóng, đóng gói an toàn và hỗ trợ kiểm tra hàng.</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="benefit-card">
                        <i class="fas fa-sync-alt"></i>
                        <h5>Đổi trả linh hoạt</h5>
                        <p>Yên tâm mua sắm online với chính sách rõ ràng.</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="benefit-card">
                        <i class="far fa-comments"></i>
                        <h5>Tư vấn tận tâm</h5>
                        <p>Gợi ý mẫu kính phù hợp khuôn mặt và phong cách.</p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="benefit-card">
                        <i class="fas fa-lock"></i>
                        <h5>Thanh toán an toàn</h5>
                        <p>Bảo mật thông tin và giao dịch minh bạch.</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <section class="product-section-modern section-space">
        <div class="container">

            <div class="section-heading text-center">
                <span class="eyebrow">Lựa chọn nổi bật</span>
                <h2>Sản phẩm được yêu thích</h2>
                <p>Những mẫu kính đang được khách hàng quan tâm nhiều nhất.</p>
            </div>

            <div class="section-title-line">
                <h3>Đang giảm giá</h3>
                <a href="index.php?controller=sanpham">Xem tất cả</a>
            </div>

            <div class="row mb-5">
                <?php if (!empty($listDiscountProduct)): ?>
                    <?php foreach ($listDiscountProduct as $item): ?>
                        <?php
                            $productId = $item['SanPhamId'] ?? 0;
                            $giaBan = $item['GiaBan'] ?? 0;
                            $giaGoc = $item['GiaGoc'] ?? 0;
                            $discountPercent = ($giaGoc > 0 && $giaGoc > $giaBan)
                                ? round((($giaGoc - $giaBan) / $giaGoc) * 100)
                                : 0;
                        ?>

                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="product-card-modern">
                                <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="product-thumb">
                                    <?php if ($discountPercent > 0): ?>
                                        <span class="product-badge">-<?= $discountPercent ?>%</span>
                                    <?php else: ?>
                                        <span class="product-badge">Sale</span>
                                    <?php endif; ?>

                                    <img 
                                        src="<?= normalizeImg($item['HinhAnhChinh'] ?? '') ?>" 
                                        alt="<?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?>"
                                    >
                                </a>

                                <div class="product-body">
                                    <div class="product-meta">
                                        <?= htmlspecialchars($item['TenThuongHieu'] ?? 'Karma Eyewear') ?>
                                    </div>

                                    <a 
                                        href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" 
                                        class="product-name"
                                    >
                                        <?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?>
                                    </a>

                                    <div class="product-price">
                                        <span class="sale"><?= formatMoney($giaBan) ?>đ</span>

                                        <?php if ($giaGoc > $giaBan): ?>
                                            <span class="original"><?= formatMoney($giaGoc) ?>đ</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="product-actions">
                                        <a 
                                            href="index.php?controller=giohang&action=add&sanPhamId=<?= $productId ?>" 
                                            class="btn-cart"
                                        >
                                            Thêm giỏ
                                        </a>

                                        <a 
                                            href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" 
                                            class="btn-detail"
                                        >
                                            Chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-block">
                            Chưa có sản phẩm giảm giá.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="section-title-line">
                <h3>Sản phẩm mới</h3>
                <a href="index.php?controller=sanpham">Xem tất cả</a>
            </div>

            <div class="row">
                <?php if (!empty($listNewProduct)): ?>
                    <?php foreach ($listNewProduct as $item): ?>
                        <?php
                            $productId = $item['SanPhamId'] ?? 0;
                            $giaBan = $item['GiaBan'] ?? 0;
                        ?>

                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="product-card-modern">
                                <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="product-thumb">
                                    <span class="product-badge new">New</span>

                                    <img 
                                        src="<?= normalizeImg($item['HinhAnhChinh'] ?? '') ?>" 
                                        alt="<?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?>"
                                    >
                                </a>

                                <div class="product-body">
                                    <div class="product-meta">
                                        <?= htmlspecialchars($item['TenThuongHieu'] ?? 'Karma Eyewear') ?>
                                    </div>

                                    <a 
                                        href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" 
                                        class="product-name"
                                    >
                                        <?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?>
                                    </a>

                                    <div class="product-price">
                                        <span class="sale"><?= formatMoney($giaBan) ?>đ</span>
                                    </div>

                                    <div class="product-actions">
                                        <a 
                                            href="index.php?controller=giohang&action=add&sanPhamId=<?= $productId ?>" 
                                            class="btn-cart"
                                        >
                                            Thêm giỏ
                                        </a>

                                        <a 
                                            href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" 
                                            class="btn-detail"
                                        >
                                            Chi tiết
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-block">
                            Chưa có sản phẩm mới.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

    <?php if (!empty($listDealHot)): ?>
        <section class="section-space pt-0">
            <div class="container">
                <div class="promo-panel">
                    <div class="row align-items-center">
                        <div class="col-lg-5 mb-4 mb-lg-0">
                            <div class="promo-left">
                                <span class="eyebrow">Ưu đãi độc quyền</span>
                                <h2>Deal hot trong tuần</h2>
                                <p>
                                    Cơ hội sở hữu những mẫu kính thời trang với mức giá tốt trong thời gian giới hạn.
                                </p>

                                <div 
                                    class="countdown-modern" 
                                    id="homeDealCountdown" 
                                    data-end="<?= $countdownEnd ?>"
                                >
                                    <div class="count-box">
                                        <h3 class="days">00</h3>
                                        <span>Ngày</span>
                                    </div>

                                    <div class="count-box">
                                        <h3 class="hours">00</h3>
                                        <span>Giờ</span>
                                    </div>

                                    <div class="count-box">
                                        <h3 class="minutes">00</h3>
                                        <span>Phút</span>
                                    </div>

                                    <div class="count-box">
                                        <h3 class="seconds">00</h3>
                                        <span>Giây</span>
                                    </div>
                                </div>

                                <a href="index.php?controller=sanpham" class="btn-luxury primary mt-4">
                                    Mua ngay
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="row">
                                <?php foreach ($listDealHot as $item): ?>
                                    <?php
                                        $productId = $item['SanPhamId'] ?? 0;
                                        $giaBan = $item['GiaBan'] ?? 0;
                                    ?>

                                    <div class="col-md-6 mb-4">
                                        <a 
                                            href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" 
                                            class="promo-product-card"
                                        >
                                            <img 
                                                src="<?= normalizeImg($item['HinhAnhChinh'] ?? '') ?>" 
                                                alt="<?= htmlspecialchars($item['TenSanPham'] ?? 'Deal hot') ?>"
                                            >

                                            <div class="promo-product-info">
                                                <h4><?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?></h4>
                                                <div class="price">
                                                    <strong><?= formatMoney($giaBan) ?>đ</strong>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!empty($listLatestBlog)): ?>
        <section class="section-space pt-0">
            <div class="container">
                <div class="section-heading text-center">
                    <span class="eyebrow">Bài viết mới</span>
                    <h2>Cập nhật xu hướng</h2>
                    <p>Mẹo chọn kính, phối đồ và bảo quản kính đúng cách.</p>
                </div>

                <div class="row">
                    <?php foreach ($listLatestBlog as $blog): ?>
                        <?php
                            $blogId = $blog['BaiVietId'] ?? 0;
                            $blogDate = $blog['NgayDang'] ?? $blog['CreatedAt'] ?? null;
                        ?>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="blog-card-modern h-100">
                                <a href="index.php?controller=blog&action=detail&id=<?= $blogId ?>">
                                    <img 
                                        src="<?= normalizeImg($blog['AnhDaiDien'] ?? '') ?>" 
                                        alt="<?= htmlspecialchars($blog['TieuDe'] ?? 'Bài viết') ?>"
                                    >
                                </a>

                                <div class="blog-card-modern__content">
                                    <small>
                                        <?= $blogDate ? date('d/m/Y', strtotime($blogDate)) : date('d/m/Y') ?>
                                    </small>

                                    <h4>
                                        <a href="index.php?controller=blog&action=detail&id=<?= $blogId ?>">
                                            <?= htmlspecialchars($blog['TieuDe'] ?? 'Bài viết mới') ?>
                                        </a>
                                    </h4>

                                    <a 
                                        href="index.php?controller=blog&action=detail&id=<?= $blogId ?>" 
                                        class="btn-readmore"
                                    >
                                        Đọc thêm
                                        <i class="fa fa-angle-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const countdown = document.getElementById("homeDealCountdown");

    if (!countdown) return;

    const endTime = new Date(countdown.getAttribute("data-end")).getTime();

    const timer = setInterval(function () {
        const now = new Date().getTime();
        const diff = endTime - now;

        if (diff <= 0) {
            clearInterval(timer);

            countdown.querySelector(".days").textContent = "00";
            countdown.querySelector(".hours").textContent = "00";
            countdown.querySelector(".minutes").textContent = "00";
            countdown.querySelector(".seconds").textContent = "00";

            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        countdown.querySelector(".days").textContent = String(days).padStart(2, "0");
        countdown.querySelector(".hours").textContent = String(hours).padStart(2, "0");
        countdown.querySelector(".minutes").textContent = String(minutes).padStart(2, "0");
        countdown.querySelector(".seconds").textContent = String(seconds).padStart(2, "0");
    }, 1000);
});
</script>