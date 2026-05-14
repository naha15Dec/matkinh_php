<?php
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

if (!function_exists('formatMoney')) {
    function formatMoney($value) {
        return number_format((float)$value, 0, ',', '.') . 'đ';
    }
}

$products = $products ?? [];
$brandList = $brandList ?? [];
$typeProductList = $typeProductList ?? [];
$filter = $filter ?? [];
$pagination = $pagination ?? [];

$page = (int)($pagination['CurrentPage'] ?? 1);
$totalPages = (int)($pagination['TotalPages'] ?? 1);
$displayStart = (int)($pagination['DisplayStart'] ?? 1);
$displayEnd = (int)($pagination['DisplayEnd'] ?? 1);
$totalCount = (int)($totalCount ?? 0);

$prevPage = $page > 1 ? $page - 1 : 1;
$nextPage = $page < $totalPages ? $page + 1 : $totalPages;

if (!function_exists('buildProductRoute')) {
    function buildProductRoute($pageNum) {
        $params = $_GET;
        $params['controller'] = 'sanpham';
        $params['Page'] = (int)$pageNum;

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

$currentCategoryId = (int)($_GET['CategoryId'] ?? 0);
$currentBrandId = (int)($_GET['BrandId'] ?? 0);
$currentKeyword = trim($_GET['Keyword'] ?? '');
$currentPriceRange = trim($_GET['PriceRange'] ?? '');
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
                           class="category-pill <?= $currentCategoryId <= 0 ? 'active' : '' ?>">
                            <i class="fas fa-border-all"></i>
                            Tất cả sản phẩm
                        </a>

                        <?php foreach ($typeProductList as $cat): ?>
                            <?php
                            $catId = (int)($cat['LoaiSanPhamId'] ?? 0);
                            $catName = $cat['TenLoaiSanPham'] ?? 'Danh mục';
                            ?>

                            <?php if ($catId > 0): ?>
                                <a href="index.php?controller=sanpham&CategoryId=<?= $catId ?>"
                                   class="category-pill <?= $currentCategoryId === $catId ? 'active' : '' ?>">
                                    <i class="fas fa-glasses"></i>
                                    <?= htmlspecialchars($catName, ENT_QUOTES, 'UTF-8') ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-card">
                        <div class="filter-card__head">
                            <span>Filter</span>
                            <h3>Bộ lọc sản phẩm</h3>
                        </div>

                        <form action="index.php" method="GET" class="filter-form">
                            <input type="hidden" name="controller" value="sanpham">
                            <input type="hidden" name="CategoryId" value="<?= htmlspecialchars((string)$currentCategoryId, ENT_QUOTES, 'UTF-8') ?>">

                            <div class="filter-group">
                                <label>Từ khóa</label>
                                <div class="filter-input">
                                    <i class="fas fa-search"></i>
                                    <input type="text"
                                           name="Keyword"
                                           placeholder="Tìm kính, thương hiệu..."
                                           value="<?= htmlspecialchars($currentKeyword, ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                            </div>

                            <div class="filter-group">
                                <label>Thương hiệu</label>

                                <label class="radio-line">
                                    <input type="radio" name="BrandId" value="" <?= $currentBrandId <= 0 ? 'checked' : '' ?>>
                                    <span>Tất cả thương hiệu</span>
                                </label>

                                <?php foreach ($brandList as $brand): ?>
                                    <?php
                                    $brandId = (int)($brand['ThuongHieuId'] ?? 0);
                                    $brandName = $brand['TenThuongHieu'] ?? 'Thương hiệu';
                                    ?>

                                    <?php if ($brandId > 0): ?>
                                        <label class="radio-line">
                                            <input type="radio"
                                                   name="BrandId"
                                                   value="<?= $brandId ?>"
                                                <?= $currentBrandId === $brandId ? 'checked' : '' ?>>
                                            <span><?= htmlspecialchars($brandName, ENT_QUOTES, 'UTF-8') ?></span>
                                        </label>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>

                            <div class="filter-group">
                                <label>Khoảng giá</label>

                                <?php foreach ($priceOptions as $val => $label): ?>
                                    <label class="radio-line">
                                        <input type="radio"
                                               name="PriceRange"
                                               value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"
                                            <?= $currentPriceRange === (string)$val ? 'checked' : '' ?>>
                                        <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
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
                                <?php if ($totalCount > 0): ?>
                                    Tìm thấy <strong><?= number_format($totalCount, 0, ',', '.') ?></strong> sản phẩm
                                <?php else: ?>
                                    Không tìm thấy sản phẩm
                                <?php endif; ?>
                            </h2>
                        </div>

                        <form action="index.php" method="GET" class="catalog-search-box">
                            <input type="hidden" name="controller" value="sanpham">
                            <input type="hidden" name="CategoryId" value="<?= htmlspecialchars((string)$currentCategoryId, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="BrandId" value="<?= htmlspecialchars((string)$currentBrandId, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="PriceRange" value="<?= htmlspecialchars($currentPriceRange, ENT_QUOTES, 'UTF-8') ?>">

                            <input type="text"
                                   name="Keyword"
                                   placeholder="Tìm kiếm nhanh..."
                                   value="<?= htmlspecialchars($currentKeyword, ENT_QUOTES, 'UTF-8') ?>">

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
                                $productId = (int)($item['SanPhamId'] ?? 0);
                                $giaBan = (float)($item['GiaBan'] ?? 0);
                                $giaGoc = (float)($item['GiaGoc'] ?? 0);
                                $isSale = $giaGoc > $giaBan && $giaBan > 0;
                                $conHang = ((int)($item['TrangThai'] ?? 0) === 1) && ((int)($item['SoLuongTon'] ?? 0) > 0);
                                $productImage = normalizeImg($item['HinhAnhChinh'] ?? '');

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

                                            <img src="<?= htmlspecialchars($productImage, ENT_QUOTES, 'UTF-8') ?>"
                                                 alt="<?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm', ENT_QUOTES, 'UTF-8') ?>"
                                                 onerror="this.src='/BanMatKinh/public/images/no-image.png'">
                                        </a>

                                        <div class="catalog-product-body">
                                            <div class="catalog-product-meta">
                                                <?= htmlspecialchars($item['TenThuongHieu'] ?? 'Karma Eyewear', ENT_QUOTES, 'UTF-8') ?>
                                            </div>

                                            <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>"
                                               class="catalog-product-name">
                                                <?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm', ENT_QUOTES, 'UTF-8') ?>
                                            </a>

                                            <div class="catalog-product-type">
                                                <?= htmlspecialchars($item['TenLoaiSanPham'] ?? 'Mắt kính thời trang', ENT_QUOTES, 'UTF-8') ?>
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
                                <?php if ($page > 1): ?>
                                    <a class="page-btn" href="<?= buildProductRoute($prevPage) ?>">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="page-btn disabled">
                                        <i class="fas fa-angle-left"></i>
                                    </span>
                                <?php endif; ?>

                                <?php for ($i = $displayStart; $i <= $displayEnd; $i++): ?>
                                    <a class="page-btn <?= $i == $page ? 'active' : '' ?>"
                                       href="<?= buildProductRoute($i) ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <a class="page-btn" href="<?= buildProductRoute($nextPage) ?>">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="page-btn disabled">
                                        <i class="fas fa-angle-right"></i>
                                    </span>
                                <?php endif; ?>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </section>
</section>