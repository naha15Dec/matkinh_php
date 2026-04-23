<?php
// 1. Khởi tạo các hàm helper tương đương với Func trong C#
function normalizeImg($path) {
    if (empty($path)) return "public/images/no-image.png";
    if (strpos($path, 'http') === 0) return $path;
    return "public/images/" . trim($path, '/');
}

function formatMoney($value) {
    return number_format($value, 0, ',', '.') . ' ₫';
}

// 2. Chuẩn bị dữ liệu phân trang từ mảng $pagination (truyền từ Controller)
$page = $pagination['CurrentPage'];
$totalPages = $pagination['TotalPages'];
$displayStart = $pagination['DisplayStart'];
$displayEnd = $pagination['DisplayEnd'];

$prevPage = $page > 1 ? $page - 1 : 1;
$nextPage = $page < $totalPages ? $page + 1 : $totalPages;

// Hàm build URL để giữ lại các filter khi chuyển trang
function buildRoute($pageNum, $filter) {
    $params = $_GET; // Lấy tất cả params hiện tại
    $params['Page'] = $pageNum;
    return "index.php?" . http_build_query($params);
}
?>

<section class="product-page">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Collection</span>
                <h1>Khám phá bộ sưu tập mắt kính</h1>
                <nav>
                    <a href="index.php">Trang chủ</a>
                    <span>/</span>
                    <span>Sản phẩm</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="product-catalog-section" style="padding: 60px 0;">
        <div class="container">
            <div class="row">
                <div class="col-xl-3 col-lg-4">
                    <aside class="optical-sidebar">
                        <div class="sidebar-card mb-4">
                            <div class="sidebar-card__title" style="font-weight:bold; margin-bottom:15px;">Danh mục mắt kính</div>
                            <ul class="optical-category-list" style="list-style:none; padding:0;">
                                <li class="mb-2">
                                    <a href="index.php?controller=sanpham" class="<?= empty($_GET['CategoryId']) ? 'text-primary font-weight-bold' : 'text-dark' ?>">
                                        Tất cả sản phẩm
                                    </a>
                                </li>
                                <?php foreach ($typeProductList as $cat): ?>
                                    <li class="mb-2">
                                        <a href="index.php?controller=sanpham&CategoryId=<?= $cat['LoaiSanPhamId'] ?>" 
                                           class="<?= ($_GET['CategoryId'] ?? 0) == $cat['LoaiSanPhamId'] ? 'text-primary font-weight-bold' : 'text-dark' ?>">
                                            <?= htmlspecialchars($cat['TenLoaiSanPham']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="sidebar-card">
                            <div class="sidebar-card__title" style="font-weight:bold; margin-bottom:15px;">Bộ lọc sản phẩm</div>
                            <form action="index.php" method="GET" class="optical-filter-form">
                                <input type="hidden" name="controller" value="sanpham">
                                <input type="hidden" name="CategoryId" value="<?= $_GET['CategoryId'] ?? '' ?>">

                                <div class="filter-group mb-4">
                                    <div class="filter-group__title small text-muted mb-2">Thương hiệu</div>
                                    <?php foreach ($brandList as $brand): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="BrandId" 
                                                   id="br-<?= $brand['ThuongHieuId'] ?>" value="<?= $brand['ThuongHieuId'] ?>"
                                                   <?= ($_GET['BrandId'] ?? 0) == $brand['ThuongHieuId'] ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="br-<?= $brand['ThuongHieuId'] ?>">
                                                <?= htmlspecialchars($brand['TenThuongHieu']) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="filter-group mb-4">
                                    <div class="filter-group__title small text-muted mb-2">Khoảng giá</div>
                                    <?php 
                                        $prices = [
                                            "" => "Tất cả mức giá",
                                            "500000" => "Dưới 500.000đ",
                                            "3000000" => "500k - 3 triệu",
                                            "5000000" => "3 triệu - 5 triệu",
                                            "10000000" => "Trên 10 triệu"
                                        ];
                                        foreach ($prices as $val => $label):
                                    ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="PriceRange" 
                                                   id="pr-<?= $val ?>" value="<?= $val ?>"
                                                   <?= ($_GET['PriceRange'] ?? '') == $val ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="pr-<?= $val ?>"><?= $label ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <button type="submit" class="btn btn-dark btn-block">Áp dụng bộ lọc</button>
                            </form>
                        </div>
                    </aside>
                </div>

                <div class="col-xl-9 col-lg-8">
                    <div class="catalog-topbar d-flex justify-content-between align-items-center mb-4 p-3 bg-white shadow-sm">
                        <div class="catalog-result">
                            <?= $totalCount > 0 ? "Tìm thấy <strong>$totalCount</strong> sản phẩm" : "Không tìm thấy sản phẩm" ?>
                        </div>
                        <div class="catalog-search">
                            <form action="index.php" method="GET" class="form-inline">
                                <input type="hidden" name="controller" value="sanpham">
                                <input type="text" name="Keyword" class="form-control form-control-sm" 
                                       placeholder="Tìm kiếm..." value="<?= htmlspecialchars($_GET['Keyword'] ?? '') ?>">
                                <button type="submit" class="btn btn-sm btn-outline-dark ml-2">Tìm</button>
                            </form>
                        </div>
                    </div>

                    <?php if (empty($products)): ?>
                        <div class="catalog-empty text-center py-5 bg-white">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h3>Không có sản phẩm phù hợp</h3>
                            <p>Hãy thử chọn lại bộ lọc hoặc từ khóa khác.</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($products as $item): 
                                $isSale = $item['GiaGoc'] > $item['GiaBan'];
                                $conHang = $item['TrangThai'] == 1 && $item['SoLuongTon'] > 0;
                            ?>
                                <div class="col-xl-4 col-md-6 mb-4">
                                    <div class="card h-100 optical-product-card shadow-sm border-0">
                                        <div class="position-relative">
                                            <?php if (!$conHang): ?>
                                                <span class="badge bg-danger position-absolute p-2 m-2" style="z-index:1;">Hết hàng</span>
                                            <?php elseif ($isSale): ?>
                                                <span class="badge bg-warning position-absolute p-2 m-2" style="z-index:1;">Sale</span>
                                            <?php endif; ?>
                                            
                                            <a href="index.php?controller=sanpham&action=detail&id=<?= $item['SanPhamId'] ?>">
                                                <img src="<?= normalizeImg($item['HinhAnhChinh']) ?>" class="card-img-top p-3" alt="Product">
                                            </a>
                                        </div>
                                        <div class="card-body text-center d-flex flex-column">
                                            <h5 class="card-title" style="font-size: 16px; height: 40px; overflow: hidden;">
                                                <?= htmlspecialchars($item['TenSanPham']) ?>
                                            </h5>
                                            <div class="product-price mb-3">
                                                <span class="text-danger font-weight-bold"><?= formatMoney($item['GiaBan']) ?></span>
                                                <?php if ($isSale): ?>
                                                    <br><small class="text-muted"><del><?= formatMoney($item['GiaGoc']) ?></del></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mt-auto">
                                                <?php if ($conHang): ?>
                                                    <a href="index.php?controller=giohang&action=add&sanPhamId=<?= $item['SanPhamId'] ?>" 
                                                       class="btn btn-sm btn-dark"><i class="fas fa-shopping-cart"></i></a>
                                                <?php endif; ?>
                                                <a href="index.php?controller=sanpham&action=detail&id=<?= $item['SanPhamId'] ?>" 
                                                   class="btn btn-sm btn-outline-dark">Chi tiết</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= buildRoute($prevPage, $filter) ?>">&laquo;</a>
                                </li>
                                
                                <?php for($i = $displayStart; $i <= $displayEnd; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= buildRoute($i, $filter) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= buildRoute($nextPage, $filter) ?>">&raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</section>