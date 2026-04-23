<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quyền truy cập bị từ chối - Karma Eyewear</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;700&display=swap" rel="stylesheet">
    
    <link href="/BanMatKinh/public/css/error_style.css?v=<?= time() ?>" rel="stylesheet" />
    
    <style>
        /* Biến màu sắc đồng bộ với layout chính */
        :root {
            --gold: #c5a059;
            --dark: #121212;
        }
    </style>
</head>

<body class="error-page-modern">
    <div class="error-container px-3">
        <div class="error-card shadow">
            <span class="error-code">403</span>

            <h1>Truy cập bị hạn chế</h1>

            <p>
                Rất tiếc, tài khoản của bạn không đủ quyền hạn để xem nội dung này. 
                Đây thường là khu vực dành riêng cho quản trị viên hoặc nhân viên hệ thống.
            </p>

            <div class="error-actions d-flex flex-column flex-md-row justify-content-center">
                <?php if (isset($isAdminLike) && $isAdminLike): ?>
                    <a href="index.php?area=admin&controller=dashboard" class="optical-btn optical-btn--dark mb-2 mb-md-0">
                        <i class="fas fa-user-shield mr-2"></i> Bảng điều khiển
                    </a>
                <?php else: ?>
                    <a href="index.php" class="optical-btn optical-btn--dark mb-2 mb-md-0">
                        <i class="fas fa-home mr-2"></i> Quay về trang chủ
                    </a>
                <?php endif; ?>

                <a href="index.php?controller=sanpham" class="optical-btn optical-btn--light">
                    Tiếp tục mua sắm
                </a>
            </div>
            
            <div class="mt-5 pt-4 border-top">
                <small class="text-muted">Bạn cho rằng đây là một lỗi? Hãy liên hệ bộ phận kỹ thuật.</small>
            </div>
        </div>
    </div>
</body>
</html>