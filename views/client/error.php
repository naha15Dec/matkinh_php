<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Truy cập bị từ chối | Karma Eyewear</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link href="/BanMatKinh/public/css/error_style.css?v=<?= time() ?>" rel="stylesheet">
</head>

<body class="error-page-luxury">

    <div class="error-background-glow glow-1"></div>
    <div class="error-background-glow glow-2"></div>

    <section class="error-wrapper">
        <div class="error-card-modern">

            <div class="error-logo">
                <span>Karma</span> Eyewear
            </div>

            <div class="error-status">
                <span>403</span>
            </div>

            <div class="error-icon-wrap">
                <i class="fas fa-lock"></i>
            </div>

            <h1>Quyền truy cập bị hạn chế</h1>

            <p>
                Xin lỗi, bạn hiện không có quyền truy cập vào khu vực này.
                Đây thường là trang dành cho quản trị viên, nhân viên hoặc tài khoản nội bộ của hệ thống.
            </p>

            <div class="error-info-box">
                <div>
                    <i class="fas fa-shield-alt"></i>
                    <span>Hệ thống đang bảo vệ nội dung riêng tư</span>
                </div>

                <div>
                    <i class="fas fa-user-check"></i>
                    <span>Vui lòng đăng nhập đúng quyền hạn</span>
                </div>
            </div>

            <div class="error-actions">
                <?php if (!empty($isAdminLike)): ?>

                    <a href="index.php?controller=dashboard" class="btn-error-primary">
                        <i class="fas fa-chart-line"></i>
                        Vào Dashboard
                    </a>

                <?php else: ?>

                    <a href="index.php" class="btn-error-primary">
                        <i class="fas fa-home"></i>
                        Về trang chủ
                    </a>

                <?php endif; ?>

                <a href="index.php?controller=sanpham" class="btn-error-secondary">
                    <i class="fas fa-glasses"></i>
                    Tiếp tục mua sắm
                </a>
            </div>

            <div class="error-footer">
                Nếu bạn nghĩ đây là lỗi hệ thống, vui lòng liên hệ quản trị viên hoặc bộ phận kỹ thuật.
            </div>

        </div>
    </section>

</body>
</html>