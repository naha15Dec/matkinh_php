<section class="contact-page">
    <section class="optical-breadcrumb">
        <div class="container text-center">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Experience</span>
                <h1 class="text-white">Liên hệ với chúng tôi</h1>
                <nav class="mt-2">
                    <a href="index.php" class="text-white-50">Trang chủ</a> 
                    <span class="text-white-50 mx-2">/</span> 
                    <span class="text-white">Liên hệ</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="contact-page-area" style="padding: 80px 0; background: #fcfcfc;">
        <div class="container">
            <div class="contact-hero text-center mb-5">
                <span class="text-uppercase font-weight-bold small" style="letter-spacing: 3px; color: #c5a059;">Get in touch</span>
                <h2 class="mt-2" style="font-family: 'Playfair Display', serif; font-size: 36px; font-weight: 700;">Chúng tôi luôn lắng nghe bạn</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">
                    <?= !empty($shortDesc) ? htmlspecialchars($shortDesc) : "Hãy để lại thông tin hoặc liên hệ trực tiếp, chuyên gia của chúng tôi luôn sẵn sàng hỗ trợ bạn tìm kiếm mẫu mắt kính hoàn hảo nhất." ?>
                </p>
            </div>

            <div class="row">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="contact-info-card bg-white p-5 shadow-sm" style="border-radius: 20px; border: 1px solid #f0f0f0;">
                        <h3 style="font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; margin-bottom: 35px; border-left: 4px solid #c5a059; padding-left: 20px;">Thông tin cửa hàng</h3>
                        
                        <div class="info-item d-flex mb-4">
                            <div class="icon-wrap" style="width: 45px; height: 45px; background: rgba(197, 160, 89, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-map-marker-alt" style="color: #c5a059;"></i>
                            </div>
                            <div class="ml-3">
                                <strong class="d-block mb-1 text-uppercase small" style="letter-spacing: 1px;">Showroom chính</strong>
                                <p class="text-muted mb-0" style="font-size: 14px; line-height: 1.5;"><?= empty($address) ? "Đang cập nhật địa chỉ showroom." : htmlspecialchars($address) ?></p>
                            </div>
                        </div>

                        <div class="info-item d-flex mb-4">
                            <div class="icon-wrap" style="width: 45px; height: 45px; background: rgba(197, 160, 89, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-phone-alt" style="color: #c5a059;"></i>
                            </div>
                            <div class="ml-3">
                                <strong class="d-block mb-1 text-uppercase small" style="letter-spacing: 1px;">Đường dây nóng</strong>
                                <p class="mb-0">
                                    <?php if (!empty($hotline)): ?>
                                        <a href="tel:<?= $hotline ?>" class="text-dark font-weight-bold h5 text-decoration-none"><?= htmlspecialchars($hotline) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted small">Đang cập nhật...</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="info-item d-flex mb-4">
                            <div class="icon-wrap" style="width: 45px; height: 45px; background: rgba(197, 160, 89, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-envelope" style="color: #c5a059;"></i>
                            </div>
                            <div class="ml-3">
                                <strong class="d-block mb-1 text-uppercase small" style="letter-spacing: 1px;">Email hỗ trợ</strong>
                                <p class="mb-0">
                                    <?php if (!empty($email)): ?>
                                        <a href="mailto:<?= $email ?>" class="text-muted text-decoration-none small"><?= htmlspecialchars($email) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted small">Đang cập nhật...</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-top">
                            <p class="small text-muted mb-3">Thời gian phục vụ:<br><strong class="text-dark">08:00 – 22:00 (Hằng ngày kể cả lễ)</strong></p>
                            <div class="social-links-contact d-flex">
                                <?php if (!empty($facebookUrl)): ?>
                                    <a href="<?= $facebookUrl ?>" class="mr-2 shadow-sm" style="background: #3b5998; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none;"><i class="fab fa-facebook-f small"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($instagramUrl)): ?>
                                    <a href="<?= $instagramUrl ?>" class="shadow-sm" style="background: #e1306c; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; text-decoration: none;"><i class="fab fa-instagram small"></i></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="contact-map-wrap shadow-sm" style="height: 100%; min-height: 500px; border-radius: 20px; overflow: hidden; border: 8px solid #fff;">
                              <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3916.7704806485776!2d106.67178467480811!3d10.98068928918078!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3174d1085e2b1c37%3A0x73bfa5616464d0ee!2sThu%20Dau%20Mot%20University!5e0!3m2!1sen!2s!4v1773818804398!5m2!1sen!2s"
                                    width="100%"
                                    height="100%"
                                    style="border:0;"
                                    allowfullscreen=""
                                    loading="lazy">
                            </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>