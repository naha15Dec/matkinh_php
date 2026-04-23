<?php
// 1. Helper Functions (Tương đương Func trong C#)
function normalizeImg($path) {
    if (empty($path)) return "public/images/no-image.png";
    if (strpos($path, 'http') === 0) return $path;
    return "public/images/" . trim($path, '/');
}

function formatMoney($value) {
    return number_format($value, 0, ',', '.') . ' ₫';
}

// 2. Trích xuất dữ liệu từ biến $product (truyền từ Controller)
$tenSP = $product['TenSanPham'] ?? "Sản phẩm";
$maSP = $product['MaSanPham'] ?? "";
$giaGoc = (float)($product['GiaGoc'] ?? 0);
$giaBan = (float)($product['GiaBan'] ?? 0);
$isSale = $giaGoc > $giaBan;
$conHang = ($product['TrangThai'] == 1 && $product['SoLuongTon'] > 0);
$mainImg = normalizeImg($product['HinhAnhChinh'] ?? '');
$fallbackImg = "public/images/no-image.png";

// 3. Ưu tiên hiển thị Recommended, nếu không có thì hiện Related
$displayProducts = !empty($recommendedProducts) ? $recommendedProducts : $relatedProducts;
?>

<section class="detail-page">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Product</span>
                <h1>Chi tiết sản phẩm</h1>
                <nav>
                    <a href="index.php">Trang chủ</a> <span>/</span>
                    <a href="index.php?controller=sanpham">Sản phẩm</a> <span>/</span>
                    <span>Chi tiết</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="detail-main-section" style="padding: 60px 0;">
        <div class="container">
            <div class="detail-card bg-white p-4 shadow-sm" style="border-radius: 12px;">
                <div class="row align-items-start">
                    <div class="col-lg-6 mb-4">
                        <div class="detail-gallery">
                            <div class="detail-gallery__main mb-3 text-center border" style="border-radius: 8px; overflow: hidden;">
                                <img id="mainImage" src="<?= $mainImg ?>" alt="<?= $tenSP ?>" 
                                     style="max-width: 100%; height: auto;"
                                     onerror="this.onerror=null;this.src='<?= $fallbackImg ?>';">
                            </div>
                            <div class="detail-gallery__thumbs d-flex gap-2">
                                <img class="thumb active border p-1" src="<?= $mainImg ?>" 
                                     data-src="<?= $mainImg ?>" onclick="swapImage(this)"
                                     style="width: 80px; height: 80px; cursor: pointer; object-fit: cover;">
                                </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="detail-info">
                            <div class="detail-badges mb-3">
                                <span class="badge bg-secondary"><?= htmlspecialchars($product['TenLoaiSanPham'] ?? '—') ?></span>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($product['TenThuongHieu'] ?? '—') ?></span>
                                <span class="badge <?= $conHang ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $conHang ? 'Còn hàng' : 'Hết hàng' ?>
                                </span>
                            </div>

                            <h2 class="detail-title" style="font-family: 'Playfair Display', serif; font-size: 32px; color: #2c3e50;">
                                <?= htmlspecialchars($tenSP) ?>
                            </h2>

                            <div class="detail-meta my-3 text-muted">
                                <p class="mb-1"><strong>Mã sản phẩm:</strong> <?= !empty($maSP) ? $maSP : "—" ?></p>
                                <p class="mb-1"><strong>Tồn kho:</strong> <?= $product['SoLuongTon'] ?? 0 ?></p>
                            </div>

                            <div class="detail-price-box my-4">
                                <?php if ($isSale): ?>
                                    <div class="h2 text-danger font-weight-bold mb-0"><?= formatMoney($giaBan) ?></div>
                                    <div class="d-flex align-items-center gap-3">
                                        <del class="text-muted"><?= formatMoney($giaGoc) ?></del>
                                        <span class="badge bg-danger">Tiết kiệm <?= formatMoney($giaGoc - $giaBan) ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="h2 text-dark font-weight-bold"><?= formatMoney($giaBan) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="detail-short-desc mb-4" style="line-height: 1.6; color: #666;">
                                <?= !empty($product['MoTaNgan']) ? nl2br(htmlspecialchars($product['MoTaNgan'])) : "Thiết kế mắt kính hiện đại, phong cách thanh lịch." ?>
                            </div>

                            <div class="detail-actions d-flex gap-3 mb-4">
                                <?php if ($conHang): ?>
                                    <form action="index.php?controller=giohang&action=add" method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="SanPhamId" value="<?= $product['SanPhamId'] ?>">
                                        <input type="number" name="SoLuong" value="1" min="1" max="<?= $product['SoLuongTon'] ?>" class="form-control" style="width: 80px;">
                                        <button type="submit" class="btn btn-dark px-4 py-2">
                                            <i class="fas fa-shopping-bag mr-2"></i> Thêm vào giỏ hàng
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled><i class="fas fa-ban mr-2"></i> Hết hàng</button>
                                <?php endif; ?>
                                <a href="index.php?controller=sanpham" class="btn btn-outline-dark px-4 py-2">Xem thêm</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="detail-tabs-section pb-5">
        <div class="container">
            <div class="bg-white p-4 shadow-sm" style="border-radius: 12px;">
                <ul class="nav nav-tabs mb-4" id="detailTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="desc-tab" data-toggle="tab" href="#desc-pane">Mô tả chi tiết</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="spec-tab" data-toggle="tab" href="#spec-pane">Thông số kĩ thuật</a>
                    </li>
                </ul>
                <div class="tab-content" id="detailTabContent">
                    <div class="tab-pane fade show active" id="desc-pane">
                        <?= !empty($product['MoTaChiTiet']) ? $product['MoTaChiTiet'] : "<p>Đang cập nhật nội dung...</p>" ?>
                    </div>
                    <div class="tab-pane fade" id="spec-pane">
                        <table class="table table-bordered w-50">
                            <tr><th class="bg-light">Mã sản phẩm</th><td><?= $maSP ?></td></tr>
                            <tr><th class="bg-light">Thương hiệu</th><td><?= $product['TenThuongHieu'] ?? '—' ?></td></tr>
                            <tr><th class="bg-light">Dòng sản phẩm</th><td><?= $product['TenLoaiSanPham'] ?? '—' ?></td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="detail-recommend-section pb-5">
        <div class="container">
            <div class="section-heading mb-4">
                <h2 style="font-family: 'Playfair Display', serif;">Có thể bạn sẽ thích</h2>
                <p class="text-muted">Các mẫu kính tương tự dựa trên sở thích của bạn.</p>
            </div>

            <?php if (!empty($displayProducts)): ?>
                <div class="row">
                    <?php foreach ($displayProducts as $item): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card h-100 border-0 shadow-sm text-center p-3" style="border-radius: 10px;">
                                <a href="index.php?controller=sanpham&action=detail&id=<?= $item['SanPhamId'] ?>" class="text-decoration-none text-dark">
                                    <img src="<?= normalizeImg($item['HinhAnhChinh']) ?>" class="card-img-top mb-3" alt="<?= $item['TenSanPham'] ?>">
                                    <h6 class="card-title text-truncate"><?= htmlspecialchars($item['TenSanPham']) ?></h6>
                                    <div class="text-danger font-weight-bold"><?= formatMoney($item['GiaBan']) ?></div>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-light border">Hệ thống đang cập nhật dữ liệu gợi ý...</div>
            <?php endif; ?>
        </div>
    </section>
</section>

<script>
    // Hàm chuyển đổi ảnh chính khi click thumb (JS thuần)
    function swapImage(el) {
        var main = document.getElementById('mainImage');
        if (!main || !el) return;
        main.src = el.getAttribute('data-src');
        
        // Cập nhật class active
        document.querySelectorAll('.detail-gallery__thumbs img').forEach(img => img.style.borderColor = "#ddd");
        el.style.borderColor = "#2c3e50";
    }
</script>