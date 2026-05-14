<?php
$storeInfo = $storeInfo ?? [];

$storeName = $storeInfo['TenCuaHang'] ?? 'Karma Eyewear';
$hotline   = $storeInfo['Hotline'] ?? '0123.456.789';
$email     = $storeInfo['Email'] ?? 'support@karmaeyewear.vn';
$address   = $storeInfo['DiaChi'] ?? 'Hệ thống cửa hàng mắt kính chính hãng';
$openTime  = '08:00 - 21:00';

$facebookUrl = $storeInfo['FacebookUrl'] ?? '';
$instagramUrl = $storeInfo['InstagramUrl'] ?? '';
$zaloUrl = $storeInfo['ZaloUrl'] ?? '';
?>

<section class="contact-page-modern">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Support</span>
                <h1>Liên hệ với chúng tôi</h1>

                <nav>
                    <a href="index.php?controller=home">Trang chủ</a>
                    <span>/</span>
                    <span>Liên hệ</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="contact-section-modern">
        <div class="container">

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success page-alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger page-alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="contact-hero-panel">
                <div>
                    <span class="eyebrow">Customer Care</span>
                    <h2><?= htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8') ?> luôn sẵn sàng hỗ trợ bạn.</h2>
                    <p>
                        Cần tư vấn kiểu dáng, chất liệu gọng, kính mát hay kính thời trang?
                        Đội ngũ Karma Eyewear sẽ hỗ trợ bạn nhanh chóng và tận tâm.
                    </p>
                </div>

                <a href="tel:<?= htmlspecialchars($hotline, ENT_QUOTES, 'UTF-8') ?>" class="contact-hotline-btn">
                    <i class="fas fa-phone-alt"></i>
                    <?= htmlspecialchars($hotline, ENT_QUOTES, 'UTF-8') ?>
                </a>
            </div>

            <div class="row contact-info-row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="contact-info-card">
                        <i class="fas fa-map-marker-alt"></i>
                        <h4>Địa chỉ</h4>
                        <p><?= htmlspecialchars($address, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="contact-info-card">
                        <i class="fas fa-phone-alt"></i>
                        <h4>Hotline</h4>
                        <p>
                            <a href="tel:<?= htmlspecialchars($hotline, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($hotline, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="contact-info-card">
                        <i class="far fa-envelope"></i>
                        <h4>Email</h4>
                        <p>
                            <a href="mailto:<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>
                            </a>
                        </p>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="contact-info-card">
                        <i class="far fa-clock"></i>
                        <h4>Giờ mở cửa</h4>
                        <p><?= htmlspecialchars($openTime, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            </div>

            <div class="contact-main-grid">
                <div class="contact-form-card">
                    <div class="contact-form-head">
                        <span>Send Message</span>
                        <h3>Gửi thông tin tư vấn</h3>
                        <p>Điền thông tin bên dưới, chúng tôi sẽ phản hồi sớm nhất có thể.</p>
                    </div>

                    <form action="index.php?controller=contact&action=send" method="POST" class="contact-form-modern">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Họ và tên *</label>
                                <div class="contact-input-wrap">
                                    <i class="far fa-user"></i>
                                    <input type="text"
                                           name="FullName"
                                           placeholder="Nhập họ tên"
                                           maxlength="150"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Số điện thoại *</label>
                                <div class="contact-input-wrap">
                                    <i class="fas fa-phone-alt"></i>
                                    <input type="tel"
                                           name="Phone"
                                           placeholder="09xxxxxxxx"
                                           maxlength="20"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Email</label>
                                <div class="contact-input-wrap">
                                    <i class="far fa-envelope"></i>
                                    <input type="email"
                                           name="Email"
                                           placeholder="email@example.com"
                                           maxlength="100">
                                </div>
                            </div>

                            <div class="col-md-6 form-group">
                                <label>Chủ đề</label>
                                <div class="contact-input-wrap">
                                    <i class="far fa-comment-dots"></i>
                                    <input type="text"
                                           name="Subject"
                                           placeholder="Tư vấn chọn kính"
                                           maxlength="200">
                                </div>
                            </div>

                            <div class="col-12 form-group">
                                <label>Nội dung *</label>
                                <div class="contact-input-wrap textarea">
                                    <i class="far fa-edit"></i>
                                    <textarea name="Message"
                                              rows="5"
                                              placeholder="Bạn cần tư vấn mẫu kính nào?"
                                              maxlength="1000"
                                              required></textarea>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-contact-submit">
                            Gửi liên hệ
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>

                    <?php if (!empty($facebookUrl) || !empty($instagramUrl) || !empty($zaloUrl)): ?>
                        <div class="contact-socials mt-4">
                            <?php if (!empty($facebookUrl)): ?>
                                <a href="<?= htmlspecialchars($facebookUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                    <i class="fab fa-facebook-f"></i>
                                    Facebook
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($instagramUrl)): ?>
                                <a href="<?= htmlspecialchars($instagramUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                    <i class="fab fa-instagram"></i>
                                    Instagram
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($zaloUrl)): ?>
                                <a href="<?= htmlspecialchars($zaloUrl, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                                    <i class="fas fa-comment"></i>
                                    Zalo
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="contact-map-card">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3916.7702007771913!2d106.67271417480792!3d10.980710389180723!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3174d1085e2b1c37%3A0x73bfa5616464d0ee!2sThu%20Dau%20Mot%20University!5e0!3m2!1sen!2s!4v1777378515863!5m2!1sen!2s"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>
    </section>
</section>