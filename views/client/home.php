<?php
// Thiết lập ngày kết thúc countdown (7 ngày kể từ hiện tại)
$countdownEnd = date("Y-m-d\TH:i:s", strtotime("+7 days"));

// Hàm định dạng tiền tệ
function formatMoney($value) {
    return number_format($value, 0, ',', '.');
}

// Hàm chuẩn hóa đường dẫn ảnh
function normalizeImg($path) {
    if (empty($path)) return "public/images/no-image.png";
    if (strpos($path, 'http') === 0) return $path;
    return "public/images/" . trim($path, '/');
}
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
                            Tôn phong cách riêng với <br /> những mẫu kính tinh tế.
                        </h1>
                        <p class="hero-desc">
                            <?= !empty($storeInfo['MoTaNgan']) ? htmlspecialchars($storeInfo['MoTaNgan']) : "Khám phá các thiết kế mắt kính thời trang, thanh lịch hằng ngày tại Karma Eyewear." ?>
                        </p>

                        <div class="hero-actions">
                            <a href="index.php?controller=sanpham" class="btn-luxury primary">Khám phá sản phẩm</a>
                            <a href="index.php?controller=blog" class="btn-luxury secondary">Xem xu hướng</a>
                        </div>

                        <div class="hero-metrics">
                            <div class="hero-metric-item">
                                <h4><?= count($listNewProduct) + count($listDiscountProduct) ?></h4>
                                <p>Sản phẩm nổi bật</p>
                            </div>
                            <div class="hero-metric-item">
                                <h4><?= !empty($storeInfo['Hotline']) ? "24/7" : "100%" ?></h4>
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
                                <img src="<?= !empty($storeInfo['Banner']) ? normalizeImg($storeInfo['Banner']) : 'public/images/banner/default-hero.png' ?>" alt="Banner">
                            </div>
                            <div class="floating-badge top">
                                <span>Xu hướng nổi bật</span>
                                <strong>Gọng tối giản cao cấp</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row benefit-grid" style="margin-top: 50px;">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="benefit-card">
                        <i class="fa fa-truck"></i>
                        <h5>Giao hàng toàn quốc</h5>
                        <p>Nhanh chóng và hỗ trợ kiểm tra hàng.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="benefit-card">
                        <i class="fa fa-refresh"></i>
                        <h5>Đổi trả linh hoạt</h5>
                        <p>Yên tâm mua sắm online với chính sách rõ ràng.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="benefit-card">
                        <i class="fa fa-comments-o"></i>
                        <h5>Tư vấn tận tâm</h5>
                        <p>Gợi ý mẫu kính phù hợp khuôn mặt.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="benefit-card">
                        <i class="fa fa-lock"></i>
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
            </div>

            <h3 class="mb-4">🔥 Đang giảm giá</h3>
            <div class="row mb-5">
                <?php if (!empty($listDiscountProduct)): ?>
                    <?php foreach ($listDiscountProduct as $item): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="product-card-modern">
                                <div class="product-thumb">
                                    <span class="product-badge">Sale</span>
                                    <img src="<?= normalizeImg($item['HinhAnhChinh']) ?>" alt="Product">
                                </div>
                                <div class="product-body">
                                    <div class="product-name"><?= htmlspecialchars($item['TenSanPham']) ?></div>
                                    <div class="product-price">
                                        <span class="sale"><?= formatMoney($item['GiaBan']) ?>đ</span>
                                        <span class="original"><?= formatMoney($item['GiaGoc']) ?>đ</span>
                                    </div>
                                    <div class="product-actions">
                                        <a href="index.php?controller=giohang&action=add&sanPhamId=<?= $item['SanPhamId'] ?>" class="btn-cart">Thêm giỏ</a>
                                        <a href="index.php?controller=sanpham&action=detail&id=<?= $item['SanPhamId'] ?>" class="btn-detail">Chi tiết</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12"><p class="alert alert-light">Chưa có sản phẩm giảm giá.</p></div>
                <?php endif; ?>
            </div>

            <h3 class="mb-4">✨ Sản phẩm mới</h3>
            <div class="row">
                <?php foreach ($listNewProduct as $item): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="product-card-modern">
                            <div class="product-thumb">
                                <span class="product-badge" style="background:#b08d57;">New</span>
                                <img src="<?= normalizeImg($item['HinhAnhChinh']) ?>" alt="Product">
                            </div>
                            <div class="product-body">
                                <div class="product-name"><?= htmlspecialchars($item['TenSanPham']) ?></div>
                                <div class="product-price">
                                    <span class="sale"><?= formatMoney($item['GiaBan']) ?>đ</span>
                                </div>
                                <div class="product-actions">
                                    <a href="index.php?controller=giohang&action=add&sanPhamId=<?= $item['SanPhamId'] ?>" class="btn-cart">Thêm giỏ</a>
                                    <a href="index.php?controller=sanpham&action=detail&id=<?= $item['SanPhamId'] ?>" class="btn-detail">Chi tiết</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section-space pt-0">
        <div class="container">
            <div class="promo-panel">
                <div class="row align-items-center">
                    <div class="col-lg-5">
                        <div class="promo-left">
                            <span class="eyebrow">Ưu đãi độc quyền</span>
                            <h2>Deal Hot trong tuần</h2>
                            <div class="countdown-modern" id="homeDealCountdown" data-end="<?= $countdownEnd ?>">
                                <div class="count-box"><h3 class="days">00</h3><span>Ngày</span></div>
                                <div class="count-box"><h3 class="hours">00</h3><span>Giờ</span></div>
                                <div class="count-box"><h3 class="minutes">00</h3><span>Phút</span></div>
                                <div class="count-box"><h3 class="seconds">00</h3><span>Giây</span></div>
                            </div>
                            <a href="index.php?controller=sanpham" class="btn-luxury primary mt-4">Mua ngay</a>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="active-exclusive-product-slider owl-carousel">
                            <?php foreach ($listDealHot as $item): ?>
                                <div class="promo-product-card">
                                    <img src="<?= normalizeImg($item['HinhAnhChinh']) ?>" alt="Deal">
                                    <h4><?= htmlspecialchars($item['TenSanPham']) ?></h4>
                                    <div class="price"><strong><?= formatMoney($item['GiaBan']) ?>đ</strong></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if (!empty($listLatestBlog)): ?>
    <section class="section-space pt-0">
        <div class="container">
            <div class="section-heading text-center">
                <span class="eyebrow">Bài viết mới</span>
                <h2>Cập nhật xu hướng</h2>
            </div>
            <div class="row">
                <?php foreach ($listLatestBlog as $blog): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="blog-card-modern h-100">
                            <img src="<?= normalizeImg($blog['AnhDaiDien']) ?>" class="w-100" style="height:200px; object-fit:cover;">
                            <div class="blog-card-modern__content p-3">
                                <small><?= date('d/m/Y', strtotime($blog['NgayDang'] ?? $blog['CreatedAt'])) ?></small>
                                <h4 class="mt-2"><?= htmlspecialchars($blog['TieuDe']) ?></h4>
                                <a href="index.php?controller=blog&action=detail&id=<?= $blog['BaiVietId'] ?>" class="btn-link">Đọc thêm</a>
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
    // Script xử lý Countdown
    document.addEventListener("DOMContentLoaded", function() {
        var countdown = document.getElementById("homeDealCountdown");
        if (countdown) {
            var endTime = new Date(countdown.getAttribute("data-end")).getTime();
            var timer = setInterval(function() {
                var now = new Date().getTime();
                var diff = endTime - now;
                if (diff <= 0) { clearInterval(timer); return; }
                
                countdown.querySelector(".days").textContent = Math.floor(diff / (1000 * 60 * 60 * 24));
                countdown.querySelector(".hours").textContent = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                countdown.querySelector(".minutes").textContent = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                countdown.querySelector(".seconds").textContent = Math.floor((diff % (1000 * 60)) / 1000);
            }, 1000);
        }
    });
</script>