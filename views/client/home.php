<?php
$countdownEnd = date("Y-m-d\TH:i:s", strtotime("+7 days"));

if (!function_exists('formatMoney')) {
    function formatMoney($value) {
        return number_format((float)$value, 0, ',', '.');
    }
}

if (!function_exists('normalizeImg')) {
    function normalizeImg($path) {
        $path = trim((string)$path);

        if ($path === '') {
            return "/BanMatKinh/public/images/no-image.png";
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        if (str_starts_with($path, '/BanMatKinh/')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (str_starts_with($path, 'public/')) {
            return '/BanMatKinh/' . ltrim($path, '/');
        }

        return "/BanMatKinh/public/images/" . ltrim($path, '/');
    }
}

$listDiscountProduct = $listDiscountProduct ?? [];
$listNewProduct = $listNewProduct ?? [];
$listDealHot = $listDealHot ?? [];
$listLatestBlog = $listLatestBlog ?? [];
$storeInfo = $storeInfo ?? [];

$totalFeatured = count($listDiscountProduct) + count($listNewProduct);

$bannerSrc = !empty($storeInfo['Banner'])
    ? normalizeImg($storeInfo['Banner'])
    : '/BanMatKinh/public/images/banner/default-hero.png';
?>

<div class="home-page">

    <section class="home-hero">
        <div class="container">
            <div class="home-hero__shell">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="home-eyebrow">
                            <i class="far fa-star"></i>
                            Bộ sưu tập kính thời trang hiện đại
                        </div>

                        <h1 class="home-hero__title">
                            Tôn phong cách riêng với những mẫu kính tinh tế.
                        </h1>

                        <p class="home-hero__desc">
                            <?= !empty($storeInfo['MoTaNgan'])
                                ? htmlspecialchars($storeInfo['MoTaNgan'], ENT_QUOTES, 'UTF-8')
                                : "Khám phá các thiết kế mắt kính thời trang, thanh lịch hằng ngày tại Karma Eyewear." ?>
                        </p>

                        <form action="index.php" method="GET" class="home-quick-search">
                            <input type="hidden" name="controller" value="home">
                            <input type="hidden" name="action" value="findProductByID">

                            <span class="home-quick-search__icon">
                                <i class="fas fa-barcode"></i>
                            </span>

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

                        <div class="home-hero__actions">
                            <a href="index.php?controller=sanpham" class="home-btn home-btn--dark">
                                Khám phá sản phẩm
                                <i class="fas fa-arrow-right"></i>
                            </a>

                            <a href="index.php?controller=blog" class="home-btn home-btn--light">
                                Xem xu hướng
                            </a>
                        </div>

                        <div class="home-hero__metrics">
                            <div class="home-metric">
                                <strong><?= (int)$totalFeatured ?></strong>
                                <span>Sản phẩm nổi bật</span>
                            </div>

                            <div class="home-metric">
                                <strong>24/7</strong>
                                <span>Hỗ trợ tận tâm</span>
                            </div>

                            <div class="home-metric">
                                <strong><?= count($listLatestBlog) ?></strong>
                                <span>Bài viết mới</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="home-hero__visual-wrap">
                            <div class="home-hero__visual">
                                <img 
                                    src="<?= htmlspecialchars($bannerSrc, ENT_QUOTES, 'UTF-8') ?>" 
                                    alt="Karma Eyewear"
                                    onerror="this.src='/BanMatKinh/public/images/banner/default-hero.png'"
                                >
                            </div>

                            <div class="home-floating-card home-floating-card--top">
                                <span>Xu hướng nổi bật</span>
                                <strong>Gọng tối giản cao cấp</strong>
                            </div>

                            <div class="home-floating-card home-floating-card--bottom">
                                <span>Premium look</span>
                                <strong>Thanh lịch mỗi ngày</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="home-benefit-grid">
                <div class="home-benefit-card">
                    <i class="fas fa-truck"></i>
                    <div>
                        <h5>Giao hàng toàn quốc</h5>
                        <p>Nhanh chóng, đóng gói an toàn và hỗ trợ kiểm tra hàng.</p>
                    </div>
                </div>

                <div class="home-benefit-card">
                    <i class="fas fa-sync-alt"></i>
                    <div>
                        <h5>Đổi trả linh hoạt</h5>
                        <p>Yên tâm mua sắm online với chính sách rõ ràng.</p>
                    </div>
                </div>

                <div class="home-benefit-card">
                    <i class="far fa-comments"></i>
                    <div>
                        <h5>Tư vấn tận tâm</h5>
                        <p>Gợi ý mẫu kính phù hợp khuôn mặt và phong cách.</p>
                    </div>
                </div>

                <div class="home-benefit-card">
                    <i class="fas fa-lock"></i>
                    <div>
                        <h5>Thanh toán an toàn</h5>
                        <p>Bảo mật thông tin và giao dịch minh bạch.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section">
        <div class="container">
            <div class="home-section-heading text-center">
                <span class="home-eyebrow">Lựa chọn nổi bật</span>
                <h2>Sản phẩm được yêu thích</h2>
                <p>Những mẫu kính đang được khách hàng quan tâm nhiều nhất.</p>
            </div>

            <div class="home-section-title">
                <div>
                    <span>Sale collection</span>
                    <h3>Đang giảm giá</h3>
                </div>

                <a href="index.php?controller=sanpham">
                    Xem tất cả
                    <i class="fas fa-angle-right"></i>
                </a>
            </div>

            <div class="row home-product-row">
                <?php if (!empty($listDiscountProduct)): ?>
                    <?php foreach ($listDiscountProduct as $item): ?>
                        <?php
                        $productId = (int)($item['SanPhamId'] ?? 0);
                        $giaBan = (float)($item['GiaBan'] ?? 0);
                        $giaGoc = (float)($item['GiaGoc'] ?? 0);
                        $discountPercent = ($giaGoc > 0 && $giaGoc > $giaBan)
                            ? round((($giaGoc - $giaBan) / $giaGoc) * 100)
                            : 0;
                        $productImg = normalizeImg($item['HinhAnhChinh'] ?? '');
                        ?>

                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <article class="home-product-card">
                                <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="home-product-card__thumb">
                                    <?php if ($discountPercent > 0): ?>
                                        <span class="home-product-badge">-<?= $discountPercent ?>%</span>
                                    <?php else: ?>
                                        <span class="home-product-badge">Sale</span>
                                    <?php endif; ?>

                                    <img 
                                        src="<?= htmlspecialchars($productImg, ENT_QUOTES, 'UTF-8') ?>" 
                                        alt="<?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm', ENT_QUOTES, 'UTF-8') ?>"
                                        onerror="this.src='/BanMatKinh/public/images/no-image.png'"
                                    >
                                </a>

                                <div class="home-product-card__body">
                                    <div class="home-product-brand">
                                        <?= htmlspecialchars($item['TenThuongHieu'] ?? 'Karma Eyewear', ENT_QUOTES, 'UTF-8') ?>
                                    </div>

                                    <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="home-product-name">
                                        <?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm', ENT_QUOTES, 'UTF-8') ?>
                                    </a>

                                    <div class="home-product-price">
                                        <span><?= formatMoney($giaBan) ?>đ</span>

                                        <?php if ($giaGoc > $giaBan): ?>
                                            <del><?= formatMoney($giaGoc) ?>đ</del>
                                        <?php endif; ?>
                                    </div>

                                    <div class="home-product-actions">
                                        <a href="index.php?controller=giohang&action=add&sanPhamId=<?= $productId ?>" class="home-product-cart">
                                            Thêm giỏ
                                        </a>

                                        <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="home-product-detail">
                                            Chi tiết
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="home-empty-block">
                            <i class="far fa-folder-open"></i>
                            Chưa có sản phẩm giảm giá.
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="home-section-title home-section-title--second">
                <div>
                    <span>New arrivals</span>
                    <h3>Sản phẩm mới</h3>
                </div>

                <a href="index.php?controller=sanpham">
                    Xem tất cả
                    <i class="fas fa-angle-right"></i>
                </a>
            </div>

            <div class="row home-product-row">
                <?php if (!empty($listNewProduct)): ?>
                    <?php foreach ($listNewProduct as $item): ?>
                        <?php
                        $productId = (int)($item['SanPhamId'] ?? 0);
                        $giaBan = (float)($item['GiaBan'] ?? 0);
                        $productImg = normalizeImg($item['HinhAnhChinh'] ?? '');
                        ?>

                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <article class="home-product-card">
                                <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="home-product-card__thumb">
                                    <span class="home-product-badge home-product-badge--new">New</span>

                                    <img 
                                        src="<?= htmlspecialchars($productImg, ENT_QUOTES, 'UTF-8') ?>" 
                                        alt="<?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm', ENT_QUOTES, 'UTF-8') ?>"
                                        onerror="this.src='/BanMatKinh/public/images/no-image.png'"
                                    >
                                </a>

                                <div class="home-product-card__body">
                                    <div class="home-product-brand">
                                        <?= htmlspecialchars($item['TenThuongHieu'] ?? 'Karma Eyewear', ENT_QUOTES, 'UTF-8') ?>
                                    </div>

                                    <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="home-product-name">
                                        <?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm', ENT_QUOTES, 'UTF-8') ?>
                                    </a>

                                    <div class="home-product-price">
                                        <span><?= formatMoney($giaBan) ?>đ</span>
                                    </div>

                                    <div class="home-product-actions">
                                        <a href="index.php?controller=giohang&action=add&sanPhamId=<?= $productId ?>" class="home-product-cart">
                                            Thêm giỏ
                                        </a>

                                        <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="home-product-detail">
                                            Chi tiết
                                        </a>
                                    </div>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="home-empty-block">
                            <i class="far fa-folder-open"></i>
                            Chưa có sản phẩm mới.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php if (!empty($listDealHot)): ?>
        <section class="home-section home-section--deal">
            <div class="container">
                <div class="home-deal-panel">
                    <div class="row align-items-center">
                        <div class="col-lg-5 mb-4 mb-lg-0">
                            <div class="home-deal-content">
                                <span class="home-eyebrow">Ưu đãi độc quyền</span>

                                <h2>Deal hot trong tuần</h2>

                                <p>
                                    Cơ hội sở hữu những mẫu kính thời trang với mức giá tốt trong thời gian giới hạn.
                                </p>

                                <div 
                                    class="home-countdown" 
                                    id="homeDealCountdown" 
                                    data-end="<?= htmlspecialchars($countdownEnd, ENT_QUOTES, 'UTF-8') ?>"
                                >
                                    <div class="home-count-box">
                                        <strong class="days">00</strong>
                                        <span>Ngày</span>
                                    </div>

                                    <div class="home-count-box">
                                        <strong class="hours">00</strong>
                                        <span>Giờ</span>
                                    </div>

                                    <div class="home-count-box">
                                        <strong class="minutes">00</strong>
                                        <span>Phút</span>
                                    </div>

                                    <div class="home-count-box">
                                        <strong class="seconds">00</strong>
                                        <span>Giây</span>
                                    </div>
                                </div>

                                <a href="index.php?controller=sanpham" class="home-btn home-btn--dark mt-4">
                                    Mua ngay
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="row">
                                <?php foreach ($listDealHot as $item): ?>
                                    <?php
                                    $productId = (int)($item['SanPhamId'] ?? 0);
                                    $giaBan = (float)($item['GiaBan'] ?? 0);
                                    $productImg = normalizeImg($item['HinhAnhChinh'] ?? '');
                                    ?>

                                    <div class="col-md-6 mb-4">
                                        <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="home-deal-card">
                                            <div class="home-deal-card__img">
                                                <img 
                                                    src="<?= htmlspecialchars($productImg, ENT_QUOTES, 'UTF-8') ?>" 
                                                    alt="<?= htmlspecialchars($item['TenSanPham'] ?? 'Deal hot', ENT_QUOTES, 'UTF-8') ?>"
                                                    onerror="this.src='/BanMatKinh/public/images/no-image.png'"
                                                >
                                            </div>

                                            <div class="home-deal-card__info">
                                                <span>Deal hot</span>
                                                <h4><?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm', ENT_QUOTES, 'UTF-8') ?></h4>
                                                <strong><?= formatMoney($giaBan) ?>đ</strong>
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
        <section class="home-section home-section--blog">
            <div class="container">
                <div class="home-section-heading text-center">
                    <span class="home-eyebrow">Bài viết mới</span>
                    <h2>Cập nhật xu hướng</h2>
                    <p>Mẹo chọn kính, phối đồ và bảo quản kính đúng cách.</p>
                </div>

                <div class="row">
                    <?php foreach ($listLatestBlog as $blog): ?>
                        <?php
                        $blogId = (int)($blog['BaiVietId'] ?? 0);
                        $blogDate = $blog['NgayDang'] ?? $blog['CreatedAt'] ?? null;
                        $blogImg = normalizeImg($blog['AnhDaiDien'] ?? '');
                        ?>

                        <div class="col-lg-4 col-md-6 mb-4">
                            <article class="home-blog-card">
                                <a href="index.php?controller=blog&action=detail&id=<?= $blogId ?>" class="home-blog-card__img">
                                    <img 
                                        src="<?= htmlspecialchars($blogImg, ENT_QUOTES, 'UTF-8') ?>" 
                                        alt="<?= htmlspecialchars($blog['TieuDe'] ?? 'Bài viết', ENT_QUOTES, 'UTF-8') ?>"
                                        onerror="this.src='/BanMatKinh/public/images/no-image.png'"
                                    >
                                </a>

                                <div class="home-blog-card__body">
                                    <div class="home-blog-date">
                                        <i class="far fa-calendar-alt"></i>
                                        <?= $blogDate ? date('d/m/Y', strtotime($blogDate)) : date('d/m/Y') ?>
                                    </div>

                                    <h4>
                                        <a href="index.php?controller=blog&action=detail&id=<?= $blogId ?>">
                                            <?= htmlspecialchars($blog['TieuDe'] ?? 'Bài viết mới', ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                    </h4>

                                    <a href="index.php?controller=blog&action=detail&id=<?= $blogId ?>" class="home-readmore">
                                        Đọc thêm
                                        <i class="fa fa-angle-right"></i>
                                    </a>
                                </div>
                            </article>
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

  if (!countdown) {
    return;
  }

  const endTime = new Date(countdown.getAttribute("data-end")).getTime();

  function setCountdownValue(selector, value) {
    const element = countdown.querySelector(selector);

    if (element) {
      element.textContent = String(value).padStart(2, "0");
    }
  }

  function resetCountdown() {
    setCountdownValue(".days", 0);
    setCountdownValue(".hours", 0);
    setCountdownValue(".minutes", 0);
    setCountdownValue(".seconds", 0);
  }

  if (!endTime || Number.isNaN(endTime)) {
    resetCountdown();
    return;
  }

  const timer = setInterval(function () {
    const now = new Date().getTime();
    const diff = endTime - now;

    if (diff <= 0) {
      clearInterval(timer);
      resetCountdown();
      return;
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    setCountdownValue(".days", days);
    setCountdownValue(".hours", hours);
    setCountdownValue(".minutes", minutes);
    setCountdownValue(".seconds", seconds);
  }, 1000);
});

</script>