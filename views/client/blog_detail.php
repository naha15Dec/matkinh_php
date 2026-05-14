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

$post = $post ?? [];
$listPostPopular = $listPostPopular ?? [];
$storeInfo = $storeInfo ?? [];

$hotline = $storeInfo['Hotline'] ?? '0123.456.789';
$postDate = $post['NgayDang'] ?? $post['CreatedAt'] ?? date('Y-m-d');
$coverImage = $post['AnhDaiDien'] ?? $post['HinhAnh'] ?? '';
$coverSrc = normalizeImg($coverImage);
$postTitle = $post['TieuDe'] ?? 'Bài viết';
$author = $post['NguoiTao'] ?? $post['TenDangNhapNguoiTao'] ?? 'Karma Eyewear';
?>

<section class="blog-detail-page">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Journal</span>
                <h1>Chi tiết bài viết</h1>
                <nav>
                    <a href="index.php?controller=home">Trang chủ</a>
                    <span>/</span>
                    <a href="index.php?controller=blog">Blog</a>
                    <span>/</span>
                    <span>Chi tiết</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="blog-detail-section">
        <div class="container">
            <div class="blog-detail-layout">

                <article class="blog-detail-content">
                    <div class="blog-detail-meta">
                        <span>
                            <i class="far fa-calendar-alt"></i>
                            <?= date('d/m/Y', strtotime($postDate)) ?>
                        </span>

                        <span>
                            <i class="far fa-newspaper"></i>
                            <?= htmlspecialchars($author, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <h1><?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?></h1>

                    <?php if (!empty($coverImage)): ?>
                        <div class="blog-detail-cover">
                            <img 
                                src="<?= htmlspecialchars($coverSrc, ENT_QUOTES, 'UTF-8') ?>" 
                                alt="<?= htmlspecialchars($postTitle, ENT_QUOTES, 'UTF-8') ?>"
                                onerror="this.src='/BanMatKinh/public/images/no-image.png'"
                            >
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($post['TomTat']) || !empty($post['MoTaNgan'])): ?>
                        <div class="blog-detail-summary">
                            <?= htmlspecialchars($post['TomTat'] ?? $post['MoTaNgan'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <div class="blog-detail-body">
                        <?= !empty($post['NoiDung'])
                            ? $post['NoiDung']
                            : '<p>Nội dung bài viết đang được cập nhật.</p>' ?>
                    </div>

                    <div class="blog-detail-footer">
                        <a href="index.php?controller=blog" class="btn-back-blog">
                            <i class="fas fa-arrow-left"></i>
                            Trở về tạp chí
                        </a>

                        <a href="index.php?controller=sanpham" class="btn-shop-blog">
                            Xem bộ sưu tập kính
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </div>
                </article>

                <aside class="blog-detail-sidebar">
                    <div class="blog-widget">
                        <h3>Mới cập nhật</h3>

                        <?php if (!empty($listPostPopular)): ?>
                            <?php foreach ($listPostPopular as $pop): ?>
                                <?php
                                $popId = (int)($pop['BaiVietId'] ?? 0);
                                $popDate = $pop['NgayDang'] ?? $pop['CreatedAt'] ?? date('Y-m-d');
                                $popImg = normalizeImg($pop['AnhDaiDien'] ?? $pop['HinhAnh'] ?? '');
                                $popTitle = $pop['TieuDe'] ?? 'Bài viết';
                                ?>

                                <a href="index.php?controller=blog&action=detail&id=<?= $popId ?>" class="mini-post">
                                    <img 
                                        src="<?= htmlspecialchars($popImg, ENT_QUOTES, 'UTF-8') ?>" 
                                        alt="<?= htmlspecialchars($popTitle, ENT_QUOTES, 'UTF-8') ?>"
                                        onerror="this.src='/BanMatKinh/public/images/no-image.png'"
                                    >

                                    <div>
                                        <span><?= htmlspecialchars($popTitle, ENT_QUOTES, 'UTF-8') ?></span>
                                        <small><?= date('d/m/Y', strtotime($popDate)) ?></small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted mb-0">Đang cập nhật...</p>
                        <?php endif; ?>
                    </div>

                    <div class="consult-banner">
                        <span>Eyewear Consultant</span>
                        <h4>Bạn cần tư vấn?</h4>
                        <p>Chuyên gia của chúng tôi luôn sẵn sàng hỗ trợ bạn chọn mẫu gọng phù hợp nhất.</p>

                        <a href="tel:<?= htmlspecialchars($hotline, ENT_QUOTES, 'UTF-8') ?>">
                            <i class="fas fa-phone-alt"></i>
                            <?= htmlspecialchars($hotline, ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </div>
                </aside>

            </div>
        </div>
    </section>
</section>