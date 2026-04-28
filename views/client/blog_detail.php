<?php
if (!function_exists('normalizeImg')) {
    function normalizeImg($path) {
        if (empty($path)) return "/BanMatKinh/public/images/no-image.png";
        if (strpos($path, 'http') === 0) return $path;
        if (strpos($path, '/BanMatKinh/') === 0) return $path;
        return "/BanMatKinh/public/images/" . ltrim($path, '/');
    }
}

$post = $post ?? [];
$listPostPopular = $listPostPopular ?? [];
$storeInfo = $storeInfo ?? [];

$hotline = $storeInfo['Hotline'] ?? '0123.456.789';
$postDate = $post['NgayDang'] ?? $post['CreatedAt'] ?? date('Y-m-d');
$coverImage = $post['AnhDaiDien'] ?? $post['HinhAnh'] ?? '';
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
                            Karma Eyewear
                        </span>
                    </div>

                    <h1><?= htmlspecialchars($post['TieuDe'] ?? 'Bài viết') ?></h1>

                    <?php if (!empty($coverImage)): ?>
                        <div class="blog-detail-cover">
                            <img 
                                src="<?= normalizeImg($coverImage) ?>" 
                                alt="<?= htmlspecialchars($post['TieuDe'] ?? 'Bài viết') ?>"
                            >
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($post['TomTat']) || !empty($post['MoTaNgan'])): ?>
                        <div class="blog-detail-summary">
                            <?= htmlspecialchars($post['TomTat'] ?? $post['MoTaNgan']) ?>
                        </div>
                    <?php endif; ?>

                    <div class="blog-detail-body">
                        <?= $post['NoiDung'] ?? '<p>Nội dung bài viết đang được cập nhật.</p>' ?>
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
                                    $popDate = $pop['NgayDang'] ?? $pop['CreatedAt'] ?? date('Y-m-d');
                                    $popImg = $pop['AnhDaiDien'] ?? $pop['HinhAnh'] ?? '';
                                ?>

                                <a href="index.php?controller=blog&action=detail&id=<?= $pop['BaiVietId'] ?>" class="mini-post">
                                    <img 
                                        src="<?= normalizeImg($popImg) ?>" 
                                        alt="<?= htmlspecialchars($pop['TieuDe'] ?? 'Bài viết') ?>"
                                    >

                                    <div>
                                        <span><?= htmlspecialchars($pop['TieuDe'] ?? 'Bài viết') ?></span>
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

                        <a href="tel:<?= htmlspecialchars($hotline) ?>">
                            <i class="fas fa-phone-alt"></i>
                            <?= htmlspecialchars($hotline) ?>
                        </a>
                    </div>
                </aside>

            </div>
        </div>
    </section>
</section>