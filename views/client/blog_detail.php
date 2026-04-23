<div class="container py-5">
    <div class="blog-layout">
        
        <div class="blog-content shadow-sm">
            <span class="blog-date">
                <i class="far fa-calendar-alt mr-2"></i> 
                <?= date('d/m/Y', strtotime($post['NgayDang'] ?? $post['CreatedAt'])) ?>
            </span>
            
            <h1 class="mt-2"><?= htmlspecialchars($post['TieuDe']) ?></h1>
            
            <hr class="my-4">
            
            <div class="full-content">
                <?= $post['NoiDung'] ?>
            </div>
            
            <div class="mt-5 pt-4 border-top">
                <a href="index.php?controller=blog" class="btn-back">
                    <i class="fas fa-arrow-left mr-2"></i> Trở về tạp chí
                </a>
            </div>
        </div>

        <aside class="blog-sidebar">
            <div class="sidebar-box mb-4">
                <h3>Mới cập nhật</h3>
                
                <?php if (!empty($listPostPopular)): ?>
                    <div class="popular-list">
                        <?php foreach ($listPostPopular as $pop): ?>
                            <div class="popular-item-detail d-flex gap-3 mb-4">
                                <a href="index.php?controller=blog&action=detail&id=<?= $pop['BaiVietId'] ?>" class="flex-shrink-0">
                                    <img src="public/images/blog/<?= htmlspecialchars($pop['HinhAnh'] ?? 'default-blog.jpg') ?>" 
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px;">
                                </a>
                                <div>
                                    <h4 class="mb-1" style="font-size: 15px; line-height: 1.4;">
                                        <a href="index.php?controller=blog&action=detail&id=<?= $pop['BaiVietId'] ?>" 
                                           class="text-dark font-weight-bold text-decoration-none">
                                            <?= htmlspecialchars($pop['TieuDe']) ?>
                                        </a>
                                    </h4>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($pop['NgayDang'] ?? $pop['CreatedAt'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted italic">Đang cập nhật...</p>
                <?php endif; ?>
            </div>
            
            <div class="consult-banner text-center text-white">
                <h4 class="text-gold mb-2">Bạn cần tư vấn?</h4>
                <p class="small opacity-75 mb-4">Chuyên gia của chúng tôi luôn sẵn sàng hỗ trợ bạn chọn mẫu gọng phù hợp nhất.</p>
                <a href="tel:<?= $hotline ?>" class="h4 text-white font-weight-bold text-decoration-none d-block">
                    <i class="fa fa-phone-alt mr-2"></i> <?= $hotline ?>
                </a>
            </div>
        </aside>

    </div>
</div>