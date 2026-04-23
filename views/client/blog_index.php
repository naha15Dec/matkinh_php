<?php
// 1. Helper Functions (Đảm bảo đồng bộ với các trang khác)
if (!function_exists('normalizeImg')) {
    function normalizeImg($path) {
        if (empty($path)) return "public/images/no-image.png";
        if (strpos($path, 'http') === 0) return $path;
        return "public/images/" . trim($path, '/');
    }
}
?>

<section class="blog-page">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner text-center">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Editorial</span>
                <h1>Tạp chí & Xu hướng mắt kính</h1>
                <nav>
                    <a href="index.php">Trang chủ</a>
                    <span>/</span>
                    <span>Blog</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="blog-catalog-section" style="padding: 60px 0; background: #fdfdfd;">
        <div class="container">
            <div class="row">
                <div class="col-xl-9 col-lg-8">
                    <div class="search-box-blog mb-5 bg-white p-4 shadow-sm" style="border-radius: 12px;">
                        <form action="index.php" method="GET" class="d-flex gap-2">
                            <input type="hidden" name="controller" value="blog">
                            <div class="input-group">
                                <input type="text" name="keyword" class="form-control border-right-0" 
                                       placeholder="Bạn đang tìm cảm hứng nào?" 
                                       value="<?= htmlspecialchars($keyword ?? '') ?>" style="height: 50px; border-radius: 8px 0 0 8px;">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-dark px-4" style="border-radius: 0 8px 8px 0;">
                                        <i class="fa fa-search mr-2"></i> TÌM KIẾM
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <h2 class="section-title mb-4" style="font-family: 'Playfair Display', serif; font-weight: 700;">
                        <?= !empty($keyword) ? "Kết quả cho: '" . htmlspecialchars($keyword) . "'" : "🔥 Bài viết mới nhất" ?>
                    </h2>

                    <?php if (empty($listPost)): ?>
                        <div class="text-center py-5 bg-white rounded-lg shadow-sm border">
                            <img src="public/images/no-blog.png" alt="No data" style="width: 100px; opacity: 0.5;">
                            <p class="mt-4 text-muted">Chúng tôi chưa có bài viết cho chủ đề này.</p>
                            <a href="index.php?controller=blog" class="btn btn-outline-dark btn-sm rounded-pill px-4">Xem tất cả bài viết</a>
                        </div>
                    <?php else: ?>
                        <div class="post-list">
                            <?php foreach ($listPost as $p): ?>
                                <div class="post-card d-md-flex align-items-center gap-4 bg-white p-3 mb-4 shadow-sm border-0" style="border-radius: 16px; transition: 0.3s;">
                                    <div class="post-img flex-shrink-0" style="width: 100%; max-width: 300px;">
                                        <a href="index.php?controller=blog&action=detail&id=<?= $p['BaiVietId'] ?>">
                                            <img src="<?= normalizeImg($p['HinhAnh'] ?? 'blog/default-blog.jpg') ?>" 
                                                 class="img-fluid rounded" style="width: 100%; height: 200px; object-fit: cover;">
                                        </a>
                                    </div>
                                    
                                    <div class="post-info py-3 pr-3">
                                        <small class="text-gold font-weight-bold text-uppercase mb-2 d-block" style="letter-spacing: 1px; color: #c5a059;">Lifestyle & Trends</small>
                                        <h3 style="font-family: 'Playfair Display', serif; font-weight: 700; line-height: 1.3;">
                                            <a href="index.php?controller=blog&action=detail&id=<?= $p['BaiVietId'] ?>" class="text-dark text-decoration-none">
                                                <?= htmlspecialchars($p['TieuDe']) ?>
                                            </a>
                                        </h3>
                                        <div class="text-muted small mb-3">
                                            <i class="far fa-calendar-alt mr-1"></i> <?= date('d/m/Y', strtotime($p['NgayDang'] ?? $p['CreatedAt'])) ?>
                                        </div>
                                        <p class="text-secondary small mb-3">
                                            <?= mb_substr(strip_tags($p['NoiDung']), 0, 150) ?>...
                                        </p>
                                        <a href="index.php?controller=blog&action=detail&id=<?= $p['BaiVietId'] ?>" class="btn-readmore text-dark font-weight-bold text-decoration-none small">
                                            ĐỌC TIẾP <i class="fas fa-arrow-right ml-2" style="font-size: 10px; color: #c5a059;"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="pagination mt-5 justify-content-center d-flex gap-2">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="index.php?controller=blog&page=<?= $i ?><?= !empty($keyword) ? '&keyword='.urlencode($keyword) : '' ?>" 
                                   class="page-link shadow-sm <?= $i == $page ? 'active bg-dark text-white border-dark' : 'bg-white text-dark' ?>" 
                                   style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 50%; text-decoration: none;">
                                   <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <aside class="col-xl-3 col-lg-4 mt-5 mt-lg-0">
                    <div class="sticky-top" style="top: 100px;">
                        <h4 class="mb-4" style="font-family: 'Playfair Display', serif; border-left: 4px solid #c5a059; padding-left: 15px;">MỚI CẬP NHẬT</h4>
                        <div class="bg-white p-3 rounded-lg shadow-sm border">
                            <?php foreach ($listPostPopular as $pop): ?>
                                <a href="index.php?controller=blog&action=detail&id=<?= $pop['BaiVietId'] ?>" class="popular-item text-decoration-none d-flex align-items-center gap-3 mb-4 pb-3 border-bottom last-child-border-0">
                                    <img src="<?= normalizeImg($pop['HinhAnh'] ?? 'blog/default-blog.jpg') ?>" 
                                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" class="flex-shrink-0">
                                    <div style="overflow: hidden;">
                                        <h5 class="text-dark m-0 text-truncate" style="font-size: 13px; font-weight: 700;">
                                            <?= htmlspecialchars($pop['TieuDe']) ?>
                                        </h5>
                                        <small class="text-muted" style="font-size: 11px;"><?= date('d/m/Y', strtotime($pop['NgayDang'] ?? $pop['CreatedAt'])) ?></small>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <div class="mt-4 p-4 text-center text-white rounded-lg shadow" style="background: #121212;">
                            <h5 style="color: #c5a059;">Đăng ký nhận tin</h5>
                            <p class="small opacity-75">Nhận thông báo về bộ sưu tập kính mới nhất.</p>
                            <div class="input-group input-group-sm">
                                <input type="email" class="form-control border-0" placeholder="Email của bạn">
                                <button class="btn btn-gold" style="background: #c5a059; color: #fff;">GỬI</button>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</section>

<style>
    /* Bổ sung một số CSS inline để đảm bảo hiển thị đúng cấu trúc Flex */
    .gap-2 { gap: 0.5rem; }
    .gap-3 { gap: 1rem; }
    .gap-4 { gap: 1.5rem; }
    .last-child-border-0:last-child { border-bottom: none !important; margin-bottom: 0 !important; padding-bottom: 0 !important; }
    .post-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
    .btn-readmore:hover { color: #c5a059 !important; }
    .page-link.active { background-color: #121212 !important; border-color: #121212 !important; color: #fff !important; }
</style>