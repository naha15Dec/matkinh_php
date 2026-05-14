<?php
$posts = $posts ?? [];
$baseUrl = $baseUrl ?? '';

$keyword = $_GET['keyword'] ?? '';
$status = $_GET['status'] ?? 'published';

$login = $_SESSION['LoginInformation'] ?? [];
$listRoleCode = strtoupper(trim($login['MaVaiTro'] ?? ''));
$isAdminBlogList = $listRoleCode === 'ADMIN';

$allowedStatus = ['published', 'draft', 'hidden', 'all'];
if (!in_array($status, $allowedStatus, true)) {
    $status = 'published';
}

function blogStatusBadge($status)
{
    $status = (int)$status;

    return match ($status) {
        1 => ['class' => 'success', 'text' => 'Đã đăng'],
        0 => ['class' => 'processing', 'text' => 'Nháp'],
        default => ['class' => 'cancel', 'text' => 'Ẩn'],
    };
}

function blogImageSrc($image, $baseUrl)
{
    $image = trim((string)$image);

    if ($image === '') {
        return $baseUrl . '/images/no-image.png';
    }

    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    return $baseUrl . '/images/' . ltrim($image, '/');
}

function blogTabUrl($baseUrl, $status, $keyword = '')
{
    $url = $baseUrl . '/index.php?controller=adminblog&status=' . urlencode($status);

    if (trim($keyword) !== '') {
        $url .= '&keyword=' . urlencode($keyword);
    }

    return $url;
}
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-newspaper mr-1"></i>
                Blog Management
            </span>

            <h1 class="admin-page-title mb-1">
                <?= htmlspecialchars($title ?? 'Quản lý bài viết', ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <p class="admin-page-subtitle mb-0">
                Quản lý nội dung tư vấn, xu hướng và câu chuyện thương hiệu của Karma Eyewear.
            </p>
        </div>

        <ol class="breadcrumb admin-breadcrumb mt-3 mt-md-0">
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Bài viết</li>
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

        <div class="premium-panel blog-panel">

            <div class="premium-panel-header blog-panel-header">
                <div>
                    <span class="admin-kicker">Karma Stories</span>
                    <h5 class="mb-0">Danh sách bài viết</h5>
                </div>

                <div class="blog-header-actions">
                    <span class="admin-card-count">
                        <?= count($posts) ?> bài viết
                    </span>

                    <a href="<?= $baseUrl ?>/index.php?controller=adminblog&action=edit" class="btn blog-create-btn">
                        <i class="fas fa-plus mr-1"></i>
                        Thêm bài viết
                    </a>
                </div>
            </div>

            <div class="blog-toolbar">
                <div class="blog-status-tabs">
                    <a href="<?= blogTabUrl($baseUrl, 'all', $keyword) ?>"
                       class="blog-tab <?= $status === 'all' ? 'active' : '' ?>">
                        Tất cả
                    </a>

                    <a href="<?= blogTabUrl($baseUrl, 'published', $keyword) ?>"
                       class="blog-tab <?= $status === 'published' ? 'active' : '' ?>">
                        Đã đăng
                    </a>

                    <a href="<?= blogTabUrl($baseUrl, 'draft', $keyword) ?>"
                       class="blog-tab <?= $status === 'draft' ? 'active warning' : '' ?>">
                        Nháp
                    </a>

                    <a href="<?= blogTabUrl($baseUrl, 'hidden', $keyword) ?>"
                       class="blog-tab <?= $status === 'hidden' ? 'active danger' : '' ?>">
                        Đã ẩn
                    </a>
                </div>

                <form action="<?= $baseUrl ?>/index.php" method="GET" class="blog-filter-form">
                    <input type="hidden" name="controller" value="adminblog">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="blog-search-box">
                        <i class="fas fa-search"></i>
                        <input
                            type="search"
                            name="keyword"
                            value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Tìm theo mã, tiêu đề hoặc tóm tắt..."
                        >
                    </div>

                    <button type="submit" class="btn blog-filter-btn">
                        <i class="fas fa-filter mr-1"></i>
                        Tìm
                    </button>

                    <?php if (!empty($keyword)): ?>
                        <a href="<?= $baseUrl ?>/index.php?controller=adminblog&status=<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>"
                           class="btn blog-reset-btn">
                            Xóa lọc
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="premium-panel-body p-0">
                <div class="table-responsive">
                    <table class="table blog-table mb-0">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Bài viết</th>
                                <th>Ngày tạo / đăng</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($posts)): ?>
                                <?php foreach ($posts as $item): ?>
                                    <?php
                                    $postId = (int)($item['BaiVietId'] ?? 0);
                                    $postCode = $item['MaBaiViet'] ?? $postId;
                                    $currentPostStatus = (int)($item['TrangThai'] ?? 0);
                                    $statusInfo = blogStatusBadge($currentPostStatus);

                                    $imageName = $item['AnhDaiDien'] ?? '';
                                    $imageSrc = blogImageSrc($imageName, $baseUrl);
                                    ?>

                                    <tr>
                                        <td>
                                            <span class="blog-code">
                                                #<?= htmlspecialchars($postCode, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="blog-title-cell">
                                                <div class="blog-thumb">
                                                    <img
                                                        src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>"
                                                        alt="Ảnh bài viết"
                                                        onerror="this.src='<?= $baseUrl ?>/images/no-image.png'"
                                                    >
                                                </div>

                                                <div>
                                                    <div class="blog-title">
                                                        <?= htmlspecialchars($item['TieuDe'] ?? 'Không có tiêu đề', ENT_QUOTES, 'UTF-8') ?>
                                                    </div>

                                                    <div class="blog-summary">
                                                        <?php
                                                        $summary = $item['TomTat'] ?? '';
                                                        echo htmlspecialchars(
                                                            mb_strlen($summary, 'UTF-8') > 90
                                                                ? mb_substr($summary, 0, 90, 'UTF-8') . '...'
                                                                : ($summary ?: 'Chưa có mô tả tóm tắt'),
                                                            ENT_QUOTES,
                                                            'UTF-8'
                                                        );
                                                        ?>
                                                    </div>

                                                    <div class="blog-author-line">
                                                        <i class="fas fa-user-edit mr-1"></i>
                                                        Người tạo:
                                                        <strong>
                                                            <?= htmlspecialchars($item['NguoiTao'] ?? $item['TenDangNhapNguoiTao'] ?? 'Không xác định', ENT_QUOTES, 'UTF-8') ?>
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <div class="blog-date">
                                                <strong>Tạo:</strong>
                                                <?= !empty($item['CreatedAt']) ? date('d/m/Y H:i', strtotime($item['CreatedAt'])) : 'Chưa có' ?>
                                            </div>

                                            <div class="blog-date muted">
                                                <strong>Đăng:</strong>
                                                <?= !empty($item['NgayDang']) ? date('d/m/Y H:i', strtotime($item['NgayDang'])) : 'Chưa đăng' ?>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="blog-status <?= $statusInfo['class'] ?>">
                                                <i class="fas fa-circle"></i>
                                                <?= htmlspecialchars($statusInfo['text'], ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>

                                        <td class="text-center">
                                            <div class="blog-action-group">
                                                <a href="<?= $baseUrl ?>/index.php?controller=adminblog&action=edit&id=<?= $postId ?>"
                                                   class="btn blog-btn blog-btn-edit">
                                                    <i class="fas fa-pen mr-1"></i>
                                                    Sửa
                                                </a>

                                               <?php if ($isAdminBlogList): ?>

                                                    <?php if ($currentPostStatus !== 1): ?>
                                                        <form action="<?= $baseUrl ?>/index.php?controller=adminblog&action=updateStatus"
                                                            method="POST"
                                                            class="d-inline">
                                                            <input type="hidden" name="id" value="<?= $postId ?>">
                                                            <input type="hidden" name="TrangThai" value="1">

                                                            <button type="submit"
                                                                    class="btn blog-btn blog-btn-edit"
                                                                    data-confirm
                                                                    data-confirm-title="Duyệt và đăng bài viết"
                                                                    data-confirm-ok="Đăng bài">
                                                                <i class="fas fa-paper-plane mr-1"></i>
                                                                Đăng
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($currentPostStatus !== 2): ?>
                                                        <form action="<?= $baseUrl ?>/index.php?controller=adminblog&action=updateStatus"
                                                            method="POST"
                                                            class="d-inline">
                                                            <input type="hidden" name="id" value="<?= $postId ?>">
                                                            <input type="hidden" name="TrangThai" value="2">

                                                            <button type="submit"
                                                                    class="btn blog-btn blog-btn-delete"
                                                                    data-confirm
                                                                    data-confirm-title="Ẩn bài viết"
                                                                    data-confirm-ok="Ẩn bài">
                                                                <i class="fas fa-eye-slash mr-1"></i>
                                                                Ẩn
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($currentPostStatus !== 0): ?>
                                                        <form action="<?= $baseUrl ?>/index.php?controller=adminblog&action=updateStatus"
                                                            method="POST"
                                                            class="d-inline">
                                                            <input type="hidden" name="id" value="<?= $postId ?>">
                                                            <input type="hidden" name="TrangThai" value="0">

                                                            <button type="submit"
                                                                    class="btn blog-btn blog-btn-edit"
                                                                    data-confirm
                                                                    data-confirm-title="Chuyển bài viết về nháp"
                                                                    data-confirm-ok="Chuyển nháp">
                                                                <i class="fas fa-file-alt mr-1"></i>
                                                                Nháp
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                <?php endif; ?>

                                                <?php if ($isAdminBlogList || (int)($item['TrangThai'] ?? 0) === 0): ?>
                                                    <form action="<?= $baseUrl ?>/index.php?controller=adminblog&action=delete"
                                                        method="POST"
                                                        class="d-inline">
                                                        <input type="hidden" name="id" value="<?= $postId ?>">

                                                        <button type="submit"
                                                                class="btn blog-btn blog-btn-delete"
                                                                data-confirm
                                                                data-confirm-title="Xóa bài viết"
                                                                data-confirm-ok="Xóa">
                                                            <i class="fas fa-trash-alt mr-1"></i>
                                                            Xóa
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="blog-empty-state">
                                            <div class="blog-empty-icon">
                                                <i class="fas fa-newspaper"></i>
                                            </div>
                                            <h6>Không có bài viết nào</h6>
                                            <p>Hãy thêm bài viết mới hoặc thay đổi bộ lọc tìm kiếm.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>

        </div>

    </div>
</section>