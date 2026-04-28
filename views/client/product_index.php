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

$products = $products ?? [];
$brandList = $brandList ?? [];
$typeProductList = $typeProductList ?? [];
$filter = $filter ?? [];

$page = $pagination['CurrentPage'] ?? 1;
$totalPages = $pagination['TotalPages'] ?? 1;
$displayStart = $pagination['DisplayStart'] ?? 1;
$displayEnd = $pagination['DisplayEnd'] ?? 1;
$totalCount = $totalCount ?? 0;

$prevPage = $page > 1 ? $page - 1 : 1;
$nextPage = $page < $totalPages ? $page + 1 : $totalPages;

if (!function_exists('buildProductRoute')) {
    function buildProductRoute($pageNum) {
        $params = $_GET;
        $params['controller'] = 'sanpham';
        $params['Page'] = $pageNum;
        return "index.php?" . http_build_query($params);
    }
}

$priceOptions = [
    "" => "Tất cả mức giá",
    "500000" => "Dưới 500.000đ",
    "3000000" => "500.000đ - 3.000.000đ",
    "5000000" => "3.000.000đ - 5.000.000đ",
    "10000000" => "Trên 10.000.000đ"
];
?>

<section class="product-page-modern">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Collection</span>
                <h1>Khám phá bộ sưu tập mắt kính</h1>
                <nav>
                    <a href="index.php?controller=home">Trang chủ</a>
                    <span>/</span>
                    <span>Sản phẩm</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="catalog-section">
        <div class="container">
            <div class="catalog-layout">

                <aside class="catalog-sidebar">
                    <div class="filter-card">
                        <div class="filter-card__head">
                            <span>Collection</span>
                            <h3>Danh mục</h3>
                        </div>

                        <a href="index.php?controller=sanpham"
                           class="category-pill <?= empty($_GET['CategoryId']) ? 'active' : '' ?>">
                            <i class="fas fa-border-all"></i>
                            Tất cả sản phẩm
                        </a>

                        <?php foreach ($typeProductList as $cat): ?>
                            <a href="index.php?controller=sanpham&CategoryId=<?= $cat['LoaiSanPhamId'] ?>"
                               class="category-pill <?= ($_GET['CategoryId'] ?? 0) == $cat['LoaiSanPhamId'] ? 'active' : '' ?>">
                                <i class="fas fa-glasses"></i>
                                <?= htmlspecialchars($cat['TenLoaiSanPham']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-card">
                        <div class="filter-card__head">
                            <span>Filter</span>
                            <h3>Bộ lọc sản phẩm</h3>
                        </div>

                        <form action="index.php" method="GET" class="filter-form">
                            <input type="hidden" name="controller" value="sanpham">
                            <input type="hidden" name="CategoryId" value="<?= htmlspecialchars($_GET['CategoryId'] ?? '') ?>">

                            <div class="filter-group">
                                <label>Từ khóa</label>
                                <div class="filter-input">
                                    <i class="fas fa-search"></i>
                                    <input type="text"
                                           name="Keyword"
                                           placeholder="Tìm kính, thương hiệu..."
                                           value="<?= htmlspecialchars($_GET['Keyword'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="filter-group">
                                <label>Thương hiệu</label>

                                <label class="radio-line">
                                    <input type="radio" name="BrandId" value="" <?= empty($_GET['BrandId']) ? 'checked' : '' ?>>
                                    <span>Tất cả thương hiệu</span>
                                </label>

                                <?php foreach ($brandList as $brand): ?>
                                    <label class="radio-line">
                                        <input type="radio"
                                               name="BrandId"
                                               value="<?= $brand['ThuongHieuId'] ?>"
                                               <?= ($_GET['BrandId'] ?? 0) == $brand['ThuongHieuId'] ? 'checked' : '' ?>>
                                        <span><?= htmlspecialchars($brand['TenThuongHieu']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="filter-group">
                                <label>Khoảng giá</label>

                                <?php foreach ($priceOptions as $val => $label): ?>
                                    <label class="radio-line">
                                        <input type="radio"
                                               name="PriceRange"
                                               value="<?= $val ?>"
                                               <?= ($_GET['PriceRange'] ?? '') == $val ? 'checked' : '' ?>>
                                        <span><?= $label ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <button type="submit" class="btn-filter-submit">
                                Áp dụng bộ lọc
                            </button>

                            <a href="index.php?controller=sanpham" class="btn-filter-reset">
                                Xóa bộ lọc
                            </a>
                        </form>
                    </div>
                </aside>

                <div class="catalog-content">
                    <div class="catalog-topbar-modern">
                        <div>
                            <span class="catalog-eyebrow">Eyewear Catalog</span>
                            <h2>
                                <?= $totalCount > 0
                                    ? "Tìm thấy <strong>{$totalCount}</strong> sản phẩm"
                                    : "Không tìm thấy sản phẩm" ?>
                            </h2>
                        </div>

                        <form action="index.php" method="GET" class="catalog-search-box">
                            <input type="hidden" name="controller" value="sanpham">
                            <input type="hidden" name="CategoryId" value="<?= htmlspecialchars($_GET['CategoryId'] ?? '') ?>">
                            <input type="hidden" name="BrandId" value="<?= htmlspecialchars($_GET['BrandId'] ?? '') ?>">
                            <input type="hidden" name="PriceRange" value="<?= htmlspecialchars($_GET['PriceRange'] ?? '') ?>">

                            <input type="text"
                                   name="Keyword"
                                   placeholder="Tìm kiếm nhanh..."
                                   value="<?= htmlspecialchars($_GET['Keyword'] ?? '') ?>">

                            <button type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>

                    <?php if (empty($products)): ?>
                        <div class="catalog-empty-modern">
                            <i class="fas fa-search"></i>
                            <h3>Không có sản phẩm phù hợp</h3>
                            <p>Hãy thử thay đổi từ khóa, danh mục hoặc khoảng giá.</p>
                            <a href="index.php?controller=sanpham">Xem tất cả sản phẩm</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($products as $item): ?>
                                <?php
                                    $productId = $item['SanPhamId'] ?? 0;
                                    $giaBan = $item['GiaBan'] ?? 0;
                                    $giaGoc = $item['GiaGoc'] ?? 0;
                                    $isSale = $giaGoc > $giaBan;
                                    $conHang = (($item['TrangThai'] ?? 0) == 1) && (($item['SoLuongTon'] ?? 0) > 0);

                                    $discountPercent = $isSale && $giaGoc > 0
                                        ? round((($giaGoc - $giaBan) / $giaGoc) * 100)
                                        : 0;
                                ?>

                                <div class="col-xl-4 col-md-6 mb-4">
                                    <div class="catalog-product-card">
                                        <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="catalog-product-thumb">
                                            <?php if (!$conHang): ?>
                                                <span class="catalog-badge soldout">Hết hàng</span>
                                            <?php elseif ($isSale): ?>
                                                <span class="catalog-badge sale">-<?= $discountPercent ?>%</span>
                                            <?php else: ?>
                                                <span class="catalog-badge new">New</span>
                                            <?php endif; ?>

                                            <img src="<?= normalizeImg($item['HinhAnhChinh'] ?? '') ?>"
                                                 alt="<?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?>">
                                        </a>

                                        <div class="catalog-product-body">
                                            <div class="catalog-product-meta">
                                                <?= htmlspecialchars($item['TenThuongHieu'] ?? 'Karma Eyewear') ?>
                                            </div>

                                            <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>"
                                               class="catalog-product-name">
                                                <?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?>
                                            </a>

                                            <div class="catalog-product-type">
                                                <?= htmlspecialchars($item['TenLoaiSanPham'] ?? 'Mắt kính thời trang') ?>
                                            </div>

                                            <div class="catalog-product-price">
                                                <span><?= formatMoney($giaBan) ?></span>

                                                <?php if ($isSale): ?>
                                                    <del><?= formatMoney($giaGoc) ?></del>
                                                <?php endif; ?>
                                            </div>

                                            <div class="catalog-product-actions">
                                                <?php if ($conHang): ?>
                                                    <a href="index.php?controller=giohang&action=add&sanPhamId=<?= $productId ?>"
                                                       class="btn-add-cart">
                                                        <i class="fas fa-shopping-bag"></i>
                                                        Thêm giỏ
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn-add-cart disabled" disabled>
                                                        Hết hàng
                                                    </button>
                                                <?php endif; ?>

                                                <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>"
                                                   class="btn-view-detail">
                                                    Chi tiết
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </div>

                        <?php if ($totalPages > 1): ?>
                            <nav class="catalog-pagination">
                                <a class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>"
                                   href="<?= buildProductRoute($prevPage) ?>">
                                    <i class="fas fa-angle-left"></i>
                                </a>

                                <?php for ($i = $displayStart; $i <= $displayEnd; $i++): ?>
                                    <a class="page-btn <?= $i == $page ? 'active' : '' ?>"
                                       href="<?= buildProductRoute($i) ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>

                                <a class="page-btn <?= $page >= $totalPages ? 'disabled' : '' ?>"
                                   href="<?= buildProductRoute($nextPage) ?>">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>
</section>