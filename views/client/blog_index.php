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

if (!function_exists('shortText')) {
    function shortText($text, $limit = 120) {
        $text = trim(strip_tags((string)$text));

        if ($text === '') {
            return 'Bài viết đang được cập nhật nội dung tóm tắt.';
        }

        if (mb_strlen($text, 'UTF-8') <= $limit) {
            return $text;
        }

        return mb_substr($text, 0, $limit, 'UTF-8') . '...';
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

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger page-alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <?php if (!empty($listPost)): ?>
                        <div class="row">
                            <?php foreach ($listPost as $post): ?>
                                <?php
                                $blogId = (int)($post['BaiVietId'] ?? 0);
                                $date = $post['NgayDang'] ?? $post['CreatedAt'] ?? null;
                                $imageSrc = normalizeImg($post['AnhDaiDien'] ?? '');
                                $title = $post['TieuDe'] ?? 'Bài viết';
                                $summary = $post['TomTat'] ?? $post['MoTaNgan'] ?? '';
                                ?>

                                <div class="col-md-6 mb-4">
                                    <article class="blog-card-modern h-100">
                                        <a href="index.php?controller=blog&action=detail&id=<?= $blogId ?>">
                                            <img src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>"
                                                 alt="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>"
                                                 onerror="this.src='/BanMatKinh/public/images/no-image.png'">
                                        </a>

                                        <div class="blog-card-modern__content">
                                            <small>
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                <?= $date ? date('d/m/Y', strtotime($date)) : date('d/m/Y') ?>
                                            </small>

                                            <h4>
                                                <a href="index.php?controller=blog&action=detail&id=<?= $blogId ?>">
                                                    <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
                                                </a>
                                            </h4>

                                            <p>
                                                <?= htmlspecialchars(shortText($summary, 120), ENT_QUOTES, 'UTF-8') ?>
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
                                <input type="text"
                                       name="keyword"
                                       placeholder="Tìm bài viết..."
                                       value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>">
                                <button type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>

                        <div class="blog-widget">
                            <h3>Bài viết mới</h3>

                            <?php if (!empty($listPostPopular)): ?>
                                <?php foreach ($listPostPopular as $item): ?>
                                    <?php
                                    $popularId = (int)($item['BaiVietId'] ?? 0);
                                    $popularTitle = $item['TieuDe'] ?? 'Bài viết';
                                    $popularImg = normalizeImg($item['AnhDaiDien'] ?? '');
                                    ?>

                                    <a href="index.php?controller=blog&action=detail&id=<?= $popularId ?>" class="mini-post">
                                        <img src="<?= htmlspecialchars($popularImg, ENT_QUOTES, 'UTF-8') ?>"
                                             alt="<?= htmlspecialchars($popularTitle, ENT_QUOTES, 'UTF-8') ?>"
                                             onerror="this.src='/BanMatKinh/public/images/no-image.png'">
                                        <span><?= htmlspecialchars($popularTitle, ENT_QUOTES, 'UTF-8') ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted mb-0">Chưa có bài viết mới.</p>
                            <?php endif; ?>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </section>
</section>