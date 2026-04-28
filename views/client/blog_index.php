<?php
if (!function_exists('normalizeImg')) {
    function normalizeImg($path) {
        if (empty($path)) return "/BanMatKinh/public/images/no-image.png";
        if (strpos($path, 'http') === 0) return $path;
        if (strpos($path, '/BanMatKinh/') === 0) return $path;
        return "/BanMatKinh/public/images/" . ltrim($path, '/');
    }
}

$listPost = $listPost ?? [];
$listPostPopular = $listPostPopular ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$keyword = $keyword ?? '';
?>

<section class="blog-page-modern">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Journal</span>
                <h1>Tin tức & xu hướng mắt kính</h1>
                <nav>
                    <a href="index.php?controller=home">Trang chủ</a>
                    <span>/</span>
                    <span>Blog</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="blog-section-modern">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <?php if (!empty($listPost)): ?>
                        <div class="row">
                            <?php foreach ($listPost as $post): ?>
                                <?php
                                    $blogId = $post['BaiVietId'] ?? 0;
                                    $date = $post['NgayDang'] ?? $post['CreatedAt'] ?? null;
                                ?>

                                <div class="col-md-6 mb-4">
                                    <article class="blog-card-modern h-100">
                                        <a href="index.php?controller=blog&action=detail&id=<?= $blogId ?>">
                                            <img src="<?= normalizeImg($post['AnhDaiDien'] ?? '') ?>"
                                                 alt="<?= htmlspecialchars($post['TieuDe'] ?? 'Bài viết') ?>">
                                        </a>

                                        <div class="blog-card-modern__content">
                                            <small>
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                <?= $date ? date('d/m/Y', strtotime($date)) : date('d/m/Y') ?>
                                            </small>

                                            <h4>
                                                <a href="index.php?controller=blog&action=detail&id=<?= $blogId ?>">
                                                    <?= htmlspecialchars($post['TieuDe'] ?? 'Bài viết') ?>
                                                </a>
                                            </h4>

                                            <p>
                                                <?= htmlspecialchars(mb_substr(strip_tags($post['TomTat'] ?? $post['MoTaNgan'] ?? ''), 0, 120)) ?>...
                                            </p>

                                            <a href="index.php?controller=blog&action=detail&id=<?= $blogId ?>" class="btn-readmore">
                                                Đọc thêm <i class="fas fa-angle-right"></i>
                                            </a>
                                        </div>
                                    </article>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($totalPages > 1): ?>
                            <nav class="blog-pagination">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <a class="<?= $i == $page ? 'active' : '' ?>"
                                       href="index.php?controller=blog&page=<?= $i ?>&keyword=<?= urlencode($keyword) ?>">
                                        <?= $i ?>
                                    </a>
                                <?php endfor; ?>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="blog-empty">
                            <i class="fas fa-search"></i>
                            <h3>Không tìm thấy bài viết</h3>
                            <p>Hãy thử tìm kiếm bằng từ khóa khác.</p>
                            <a href="index.php?controller=blog">Xem tất cả bài viết</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <aside class="blog-sidebar-modern">
                        <div class="blog-widget">
                            <h3>Tìm kiếm</h3>

                            <form action="index.php" method="GET" class="blog-search">
                                <input type="hidden" name="controller" value="blog">
                                <input type="text" name="keyword" placeholder="Tìm bài viết..."
                                       value="<?= htmlspecialchars($keyword) ?>">
                                <button type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>

                        <div class="blog-widget">
                            <h3>Bài viết mới</h3>

                            <?php foreach ($listPostPopular as $item): ?>
                                <a href="index.php?controller=blog&action=detail&id=<?= $item['BaiVietId'] ?>" class="mini-post">
                                    <img src="<?= normalizeImg($item['AnhDaiDien'] ?? '') ?>" alt="Blog">
                                    <span><?= htmlspecialchars($item['TieuDe'] ?? 'Bài viết') ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </section>
</section>