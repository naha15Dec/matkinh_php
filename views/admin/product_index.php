<?php
$products = $products ?? [];
$pagination = $pagination ?? [];
$baseUrl = $baseUrl ?? '/BanMatKinh/public';

$keyword = $_GET['keyword'] ?? '';
$currStatus = $_GET['statusProduct'] ?? 'stock';

$allowedStatus = ['all', 'stock', 'outofstock', 'inactive'];
if (!in_array($currStatus, $allowedStatus, true)) {
    $currStatus = 'stock';
}

$userRole = strtoupper($_SESSION['LoginInformation']['MaVaiTro'] ?? '');
$isAdmin = $userRole === 'ADMIN';
$isStaff = $userRole === 'STAFF';

$page = (int)($pagination['CurrentPage'] ?? 1);
$totalPages = (int)($pagination['TotalPages'] ?? 1);
$displayStart = (int)($pagination['DisplayStart'] ?? 1);
$displayEnd = (int)($pagination['DisplayEnd'] ?? 1);
$totalCount = (int)($pagination['TotalCount'] ?? count($products));

if (!function_exists('adminProductStatusText')) {
    function adminProductStatusText($status)
    {
        return ((int)$status === 1) ? 'Đang bán' : 'Ngừng bán';
    }
}

if (!function_exists('adminProductStatusClass')) {
    function adminProductStatusClass($status)
    {
        return ((int)$status === 1) ? 'active' : 'inactive';
    }
}

if (!function_exists('adminProductImageSrc')) {
    function adminProductImageSrc($image, $baseUrl)
    {
        $image = trim((string)$image);

        if ($image === '') {
            return $baseUrl . '/images/default.jpg';
        }

        if (preg_match('/^https?:\/\//i', $image)) {
            return $image;
        }

        if (str_starts_with($image, '/BanMatKinh/')) {
            return $image;
        }

        if (str_starts_with($image, '/')) {
            return $image;
        }

        if (str_starts_with($image, 'public/')) {
            return '/BanMatKinh/' . ltrim($image, '/');
        }

        return $baseUrl . '/images/' . ltrim($image, '/');
    }
}

if (!function_exists('adminProductTabUrl')) {
    function adminProductTabUrl($baseUrl, $status, $keyword = '')
    {
        $url = $baseUrl . '/index.php?controller=adminsanpham&statusProduct=' . urlencode($status);

        if (trim($keyword) !== '') {
            $url .= '&keyword=' . urlencode($keyword);
        }

        return $url;
    }
}

if (!function_exists('adminProductPageUrl')) {
    function adminProductPageUrl($baseUrl, $pageNum)
    {
        $params = $_GET;
        $params['controller'] = 'adminsanpham';
        $params['page'] = (int)$pageNum;

        return $baseUrl . '/index.php?' . http_build_query($params);
    }
}
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-glasses mr-1"></i>
                Product Management
            </span>

            <h1 class="admin-page-title mb-1">Danh sách sản phẩm</h1>

            <p class="admin-page-subtitle mb-0">
                Quản lý sản phẩm kính mắt, giá bán, tồn kho và trạng thái kinh doanh.
                <?php if ($isStaff): ?>
                    <br>
                    <small>Nhân viên chỉ quản lý các sản phẩm do mình tạo.</small>
                <?php endif; ?>
            </p>
        </div>

        <ol class="breadcrumb admin-breadcrumb mt-3 mt-md-0">
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Sản phẩm</li>
        </ol>
    </div>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success admin-alert">
                <i class="fas fa-check-circle mr-1"></i>
                <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger admin-alert">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="premium-panel product-panel">

            <div class="premium-panel-header product-panel-header">
                <div>
                    <span class="admin-kicker">Karma Catalog</span>
                    <h5 class="mb-0">Sản phẩm trong hệ thống</h5>
                </div>

                <div class="product-header-actions">
                    <span class="admin-card-count">
                        <?= number_format($totalCount, 0, ',', '.') ?> sản phẩm
                    </span>

                    <a href="<?= $baseUrl ?>/index.php?controller=adminsanpham&action=edit"
                       class="btn product-create-btn">
                        <i class="fas fa-plus mr-1"></i>
                        Thêm sản phẩm
                    </a>
                </div>
            </div>

            <div class="product-toolbar">
                <div class="product-status-tabs">
                    <a href="<?= adminProductTabUrl($baseUrl, 'all', $keyword) ?>"
                       class="product-tab <?= $currStatus === 'all' ? 'active' : '' ?>">
                        Tất cả
                    </a>

                    <a href="<?= adminProductTabUrl($baseUrl, 'stock', $keyword) ?>"
                       class="product-tab <?= $currStatus === 'stock' ? 'active' : '' ?>">
                        Còn hàng
                    </a>

                    <a href="<?= adminProductTabUrl($baseUrl, 'outofstock', $keyword) ?>"
                       class="product-tab <?= $currStatus === 'outofstock' ? 'active warning' : '' ?>">
                        Hết hàng
                    </a>

                    <a href="<?= adminProductTabUrl($baseUrl, 'inactive', $keyword) ?>"
                       class="product-tab <?= $currStatus === 'inactive' ? 'active danger' : '' ?>">
                        Ngừng bán
                    </a>
                </div>

                <form action="<?= $baseUrl ?>/index.php" method="GET" class="product-filter-form">
                    <input type="hidden" name="controller" value="adminsanpham">
                    <input type="hidden" name="statusProduct" value="<?= htmlspecialchars($currStatus, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="product-search-box">
                        <i class="fas fa-search"></i>
                        <input
                            type="search"
                            name="keyword"
                            value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Tìm theo mã, tên, thương hiệu hoặc loại sản phẩm..."
                        >
                    </div>

                    <button type="submit" class="btn product-filter-btn">
                        <i class="fas fa-search mr-1"></i>
                        Tìm
                    </button>

                    <?php if (!empty($keyword)): ?>
                        <a href="<?= $baseUrl ?>/index.php?controller=adminsanpham&statusProduct=<?= htmlspecialchars($currStatus, ENT_QUOTES, 'UTF-8') ?>"
                           class="btn product-reset-btn">
                            Xóa lọc
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="premium-panel-body p-0">
                <div class="table-responsive">
                    <table class="table product-table mb-0">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Sản phẩm</th>
                                <th>Phân loại</th>
                                <th class="text-right">Giá</th>
                                <th class="text-center">Tồn kho</th>
                                <th class="text-center">Nổi bật</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $item): ?>
                                    <?php
                                    $id = (int)($item['SanPhamId'] ?? 0);
                                    $code = $item['MaSanPham'] ?? $id;
                                    $image = $item['HinhAnhChinh'] ?? '';
                                    $imageSrc = adminProductImageSrc($image, $baseUrl);
                                    $status = (int)($item['TrangThai'] ?? 1);
                                    $stock = (int)($item['SoLuongTon'] ?? 0);
                                    $isFeatured = !empty($item['IsFeatured']);
                                    $creatorName = $item['NguoiTao'] ?? $item['TenDangNhapNguoiTao'] ?? '';
                                    ?>

                                    <tr>
                                        <td>
                                            <?php if ($status === 1): ?>
                                                <a href="<?= $baseUrl ?>/index.php?controller=sanpham&action=detail&id=<?= $id ?>"
                                                   target="_blank"
                                                   class="product-code">
                                                    #<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="product-code muted">
                                                    #<?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="product-cell">
                                                <div class="product-thumb">
                                                    <img src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>"
                                                         alt="<?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm', ENT_QUOTES, 'UTF-8') ?>"
                                                         onerror="this.src='<?= $baseUrl ?>/images/default.jpg'">
                                                </div>

                                                <div>
                                                    <div class="product-name">
                                                        <?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm', ENT_QUOTES, 'UTF-8') ?>
                                                    </div>

                                                    <div class="product-summary">
                                                        <?php
                                                        $summary = $item['MoTaNgan'] ?? '';
                                                        echo htmlspecialchars(
                                                            mb_strlen($summary, 'UTF-8') > 65
                                                                ? mb_substr($summary, 0, 65, 'UTF-8') . '...'
                                                                : ($summary ?: 'Chưa có mô tả'),
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        );
                                                        ?>
                                                    </div>

                                                    <?php if ($isAdmin && !empty($creatorName)): ?>
                                                        <div class="product-summary">
                                                            Người tạo:
                                                            <strong><?= htmlspecialchars($creatorName, ENT_QUOTES, 'UTF-8') ?></strong>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="product-category">
                                                <?= htmlspecialchars($item['TenLoaiSanPham'] ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8') ?>
                                            </span>

                                            <div class="product-brand">
                                                <?= htmlspecialchars($item['TenThuongHieu'] ?? 'Chưa có thương hiệu', ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        </td>

                                        <td class="text-right">
                                            <div class="product-price">
                                                <?= number_format((float)($item['GiaBan'] ?? 0), 0, ',', '.') ?> đ
                                            </div>

                                            <?php if (!empty($item['GiaGoc']) && (float)$item['GiaGoc'] > (float)($item['GiaBan'] ?? 0)): ?>
                                                <div class="product-origin-price">
                                                    <?= number_format((float)$item['GiaGoc'], 0, ',', '.') ?> đ
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <span class="product-stock <?= $stock > 0 ? 'stock' : 'out' ?>">
                                                <?= $stock ?>
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <?php if ($isAdmin): ?>
                                                <form action="<?= $baseUrl ?>/index.php?controller=adminsanpham&action=toggleFeatured"
                                                      method="POST"
                                                      class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $id ?>">

                                                    <button type="submit"
                                                            class="product-feature-btn"
                                                            title="<?= $isFeatured ? 'Tắt nổi bật' : 'Bật nổi bật' ?>"
                                                            data-confirm
                                                            data-confirm-title="<?= $isFeatured ? 'Tắt sản phẩm nổi bật' : 'Bật sản phẩm nổi bật' ?>"
                                                            data-confirm-ok="<?= $isFeatured ? 'Tắt nổi bật' : 'Bật nổi bật' ?>">
                                                        <i class="<?= $isFeatured ? 'fas fa-star' : 'far fa-star' ?>"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="product-feature-btn disabled"
                                                      title="Chỉ Admin được cập nhật nổi bật">
                                                    <i class="<?= $isFeatured ? 'fas fa-star' : 'far fa-star' ?>"></i>
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <span class="product-status <?= adminProductStatusClass($status) ?>">
                                                <i class="fas fa-circle"></i>
                                                <?= adminProductStatusText($status) ?>
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <div class="product-action-group">
                                                <a href="<?= $baseUrl ?>/index.php?controller=adminsanpham&action=edit&id=<?= $id ?>"
                                                   class="btn product-btn product-btn-edit">
                                                    <i class="fas fa-pen mr-1"></i>
                                                    Sửa
                                                </a>

                                                <form action="<?= $baseUrl ?>/index.php?controller=adminsanpham&action=delete"
                                                      method="POST"
                                                      class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $id ?>">

                                                    <button type="submit"
                                                            class="btn product-btn product-btn-delete"
                                                            data-confirm
                                                            data-confirm-title="<?= $isAdmin ? 'Xóa hoặc ngừng bán sản phẩm' : 'Ngừng bán sản phẩm' ?>"
                                                            data-confirm-ok="<?= $isAdmin ? 'Xác nhận' : 'Ngừng bán' ?>">
                                                        <i class="fas fa-trash-alt mr-1"></i>
                                                        <?= $isAdmin ? 'Xóa' : 'Ngừng' ?>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">
                                        <div class="product-empty-state">
                                            <div class="product-empty-icon">
                                                <i class="fas fa-glasses"></i>
                                            </div>

                                            <h6>Không có sản phẩm nào</h6>
                                            <p>Hãy thêm sản phẩm mới hoặc thay đổi bộ lọc tìm kiếm.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="admin-pagination-wrap">
                        <nav class="admin-pagination">
                            <?php if ($page > 1): ?>
                                <a href="<?= adminProductPageUrl($baseUrl, $page - 1) ?>" class="admin-page-btn">
                                    <i class="fas fa-angle-left"></i>
                                </a>
                            <?php else: ?>
                                <span class="admin-page-btn disabled">
                                    <i class="fas fa-angle-left"></i>
                                </span>
                            <?php endif; ?>

                            <?php for ($i = $displayStart; $i <= $displayEnd; $i++): ?>
                                <a href="<?= adminProductPageUrl($baseUrl, $i) ?>"
                                   class="admin-page-btn <?= $i === $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="<?= adminProductPageUrl($baseUrl, $page + 1) ?>" class="admin-page-btn">
                                    <i class="fas fa-angle-right"></i>
                                </a>
                            <?php else: ?>
                                <span class="admin-page-btn disabled">
                                    <i class="fas fa-angle-right"></i>
                                </span>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</section>