<?php
if (!function_exists('normalizeImg')) {
    function normalizeImg($path) {
        if (empty($path)) return "/BanMatKinh/public/images/no-image.png";
        if (strpos($path, 'http') === 0) return $path;
        if (strpos($path, '/BanMatKinh/') === 0) return $path;
        return "/BanMatKinh/public/images/" . ltrim($path, '/');
    }
}

if (!function_exists('formatMoney')) {
    function formatMoney($value) {
        return number_format((float)$value, 0, ',', '.') . 'đ';
    }
}

$product = $product ?? [];
$recommendedProducts = $recommendedProducts ?? [];
$relatedProducts = $relatedProducts ?? [];

$displayProducts = !empty($recommendedProducts) ? $recommendedProducts : $relatedProducts;

$productId = $product['SanPhamId'] ?? 0;
$tenSP = $product['TenSanPham'] ?? 'Sản phẩm';
$maSP = $product['MaSanPham'] ?? '—';
$giaGoc = (float)($product['GiaGoc'] ?? 0);
$giaBan = (float)($product['GiaBan'] ?? 0);
$soLuongTon = (int)($product['SoLuongTon'] ?? 0);

$isSale = $giaGoc > $giaBan;
$conHang = (($product['TrangThai'] ?? 0) == 1 && $soLuongTon > 0);
$mainImg = normalizeImg($product['HinhAnhChinh'] ?? '');
$fallbackImg = "/BanMatKinh/public/images/no-image.png";

$discountPercent = $isSale && $giaGoc > 0
    ? round((($giaGoc - $giaBan) / $giaGoc) * 100)
    : 0;
?>

<section class="detail-page-modern">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Product</span>
                <h1>Chi tiết sản phẩm</h1>
                <nav>
                    <a href="index.php?controller=home">Trang chủ</a>
                    <span>/</span>
                    <a href="index.php?controller=sanpham">Sản phẩm</a>
                    <span>/</span>
                    <span>Chi tiết</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="detail-main-section">
        <div class="container">
            <div class="detail-product-shell">
                <div class="row align-items-start">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="detail-gallery-modern">
                            <div class="detail-main-image">
                                <?php if ($isSale): ?>
                                    <span class="detail-sale-badge">-<?= $discountPercent ?>%</span>
                                <?php endif; ?>

                                <img 
                                    id="mainImage" 
                                    src="<?= $mainImg ?>" 
                                    alt="<?= htmlspecialchars($tenSP) ?>"
                                    onerror="this.onerror=null;this.src='<?= $fallbackImg ?>';"
                                >
                            </div>

                            <div class="detail-thumb-list">
                                <button type="button" class="detail-thumb active" data-src="<?= $mainImg ?>">
                                    <img src="<?= $mainImg ?>" alt="<?= htmlspecialchars($tenSP) ?>">
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="detail-info-modern">
                            <div class="detail-badge-row">
                                <span><?= htmlspecialchars($product['TenLoaiSanPham'] ?? 'Mắt kính') ?></span>
                                <span><?= htmlspecialchars($product['TenThuongHieu'] ?? 'Karma Eyewear') ?></span>
                                <span class="<?= $conHang ? 'in-stock' : 'out-stock' ?>">
                                    <?= $conHang ? 'Còn hàng' : 'Hết hàng' ?>
                                </span>
                            </div>

                            <h2><?= htmlspecialchars($tenSP) ?></h2>

                            <div class="detail-meta-modern">
                                <div>
                                    <strong>Mã sản phẩm</strong>
                                    <span><?= htmlspecialchars($maSP) ?></span>
                                </div>

                                <div>
                                    <strong>Tồn kho</strong>
                                    <span><?= $soLuongTon ?></span>
                                </div>
                            </div>

                            <div class="detail-price-modern">
                                <strong><?= formatMoney($giaBan) ?></strong>

                                <?php if ($isSale): ?>
                                    <del><?= formatMoney($giaGoc) ?></del>
                                    <span>Tiết kiệm <?= formatMoney($giaGoc - $giaBan) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="detail-short-desc">
                                <?= !empty($product['MoTaNgan'])
                                    ? nl2br(htmlspecialchars($product['MoTaNgan']))
                                    : "Thiết kế mắt kính hiện đại, thanh lịch, phù hợp sử dụng hằng ngày và nâng tầm phong cách cá nhân." ?>
                            </div>

                            <div class="detail-service-box">
                                <div>
                                    <i class="fas fa-shipping-fast"></i>
                                    <span>Giao hàng toàn quốc</span>
                                </div>

                                <div>
                                    <i class="fas fa-sync-alt"></i>
                                    <span>Đổi trả linh hoạt</span>
                                </div>

                                <div>
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Bảo hành rõ ràng</span>
                                </div>
                            </div>

                            <div class="detail-actions-modern">
                                <?php if ($conHang): ?>
                                    <form action="index.php?controller=giohang&action=add" method="POST" class="detail-cart-form">
                                        <input type="hidden" name="SanPhamId" value="<?= $productId ?>">

                                        <div class="quantity-control">
                                            <button type="button" class="qty-btn" data-type="minus">-</button>
                                            <input 
                                                type="number" 
                                                name="SoLuong" 
                                                id="quantityInput"
                                                value="1" 
                                                min="1" 
                                                max="<?= $soLuongTon ?>"
                                            >
                                            <button type="button" class="qty-btn" data-type="plus">+</button>
                                        </div>

                                        <button type="submit" class="btn-detail-cart">
                                            <i class="fas fa-shopping-bag"></i>
                                            Thêm vào giỏ hàng
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn-detail-cart disabled" disabled>
                                        <i class="fas fa-ban"></i>
                                        Sản phẩm đã hết hàng
                                    </button>
                                <?php endif; ?>

                                <a href="index.php?controller=sanpham" class="btn-detail-back">
                                    Xem thêm sản phẩm
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="detail-tab-shell">
                <ul class="nav nav-tabs detail-tabs" id="detailTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#desc-pane">
                            Mô tả chi tiết
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#spec-pane">
                            Thông số sản phẩm
                        </a>
                    </li>
                </ul>

                <div class="tab-content detail-tab-content">
                    <div class="tab-pane fade show active" id="desc-pane">
                        <?= !empty($product['MoTaChiTiet'])
                            ? $product['MoTaChiTiet']
                            : "<p>Thông tin chi tiết sản phẩm đang được cập nhật.</p>" ?>
                    </div>

                    <div class="tab-pane fade" id="spec-pane">
                        <div class="spec-grid">
                            <div>
                                <strong>Mã sản phẩm</strong>
                                <span><?= htmlspecialchars($maSP) ?></span>
                            </div>

                            <div>
                                <strong>Thương hiệu</strong>
                                <span><?= htmlspecialchars($product['TenThuongHieu'] ?? '—') ?></span>
                            </div>

                            <div>
                                <strong>Dòng sản phẩm</strong>
                                <span><?= htmlspecialchars($product['TenLoaiSanPham'] ?? '—') ?></span>
                            </div>

                            <div>
                                <strong>Tình trạng</strong>
                                <span><?= $conHang ? 'Còn hàng' : 'Hết hàng' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <section class="detail-recommend-modern">
                <div class="section-heading text-center">
                    <span class="eyebrow">Gợi ý dành cho bạn</span>
                    <h2>Có thể bạn sẽ thích</h2>
                    <p>Các mẫu kính tương tự phù hợp với phong cách bạn đang xem.</p>
                </div>

                <?php if (!empty($displayProducts)): ?>
                    <div class="row">
                        <?php foreach ($displayProducts as $item): ?>
                            <?php
                                $relatedId = $item['SanPhamId'] ?? 0;
                                $relatedGiaBan = $item['GiaBan'] ?? 0;
                            ?>

                            <div class="col-lg-3 col-md-6 mb-4">
                                <div class="related-product-card">
                                    <a href="index.php?controller=sanpham&action=detail&id=<?= $relatedId ?>" class="related-thumb">
                                        <img 
                                            src="<?= normalizeImg($item['HinhAnhChinh'] ?? '') ?>" 
                                            alt="<?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?>"
                                        >
                                    </a>

                                    <div class="related-body">
                                        <a href="index.php?controller=sanpham&action=detail&id=<?= $relatedId ?>">
                                            <?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?>
                                        </a>
                                        <strong><?= formatMoney($relatedGiaBan) ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="detail-empty-recommend">
                        Hệ thống đang cập nhật dữ liệu gợi ý sản phẩm.
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </section>
</section>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".detail-thumb").forEach(function (thumb) {
        thumb.addEventListener("click", function () {
            const mainImage = document.getElementById("mainImage");
            const src = this.getAttribute("data-src");

            if (!mainImage || !src) return;

            mainImage.src = src;

            document.querySelectorAll(".detail-thumb").forEach(item => item.classList.remove("active"));
            this.classList.add("active");
        });
    });

    document.querySelectorAll(".qty-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const input = document.getElementById("quantityInput");
            if (!input) return;

            let value = parseInt(input.value || "1");
            const min = parseInt(input.getAttribute("min") || "1");
            const max = parseInt(input.getAttribute("max") || "999");

            if (this.dataset.type === "plus" && value < max) value++;
            if (this.dataset.type === "minus" && value > min) value--;

            input.value = value;
        });
    });
});
</script>