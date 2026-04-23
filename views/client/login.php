<section class="auth-page">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Account</span>
                <h1>Đăng nhập tài khoản</h1>
                <nav>
                    <a href="index.php">Trang chủ</a> <span>/</span> <span>Đăng nhập</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="auth-section" style="padding: 60px 0;">
        <div class="container">
            <div class="auth-shell bg-white shadow-lg overflow-hidden" style="border-radius: 15px;">
                <div class="row no-gutters">
                    <div class="col-lg-6 d-none d-lg-block position-relative">
                        <img src="public/images/login.jpg" class="w-100 h-100" style="object-fit: cover;" alt="Login">
                        <div class="position-absolute w-100 h-100" style="top:0; left:0; background: rgba(0,0,0,0.3);"></div>
                        <div class="position-absolute text-white p-5" style="bottom:0;">
                            <h2>Chào mừng trở lại!</h2>
                            <p>Đăng nhập để nhận ưu đãi riêng cho thành viên.</p>
                        </div>
                    </div>

                    <div class="col-lg-6 p-5">
                        <div class="auth-form-head mb-4">
                            <span class="text-uppercase small text-muted">Member access</span>
                            <h2 class="font-weight-bold">Đăng nhập</h2>
                        </div>

                        <div id="authMessage">
                            <?php if (isset($errors['Global'])): ?>
                                <div class="alert alert-danger border-0 small">
                                    <?= implode('<br>', $errors['Global']) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['NotificationLogin'])): ?>
                                <div class="alert alert-warning border-0 small">
                                    <?= $_SESSION['NotificationLogin']; unset($_SESSION['NotificationLogin']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <form action="index.php?controller=taikhoan&action=login" method="POST" class="auth-form">
                            <div class="form-group mb-3">
                                <label class="auth-label">Tên đăng nhập</label>
                                <input type="text" name="Username" class="form-control auth-input" 
                                       placeholder="Nhập tên đăng nhập" 
                                       value="<?= htmlspecialchars($_POST['Username'] ?? '') ?>" required>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label class="auth-label">Mật khẩu</label>
                                <input type="password" name="Password" class="form-control auth-input" 
                                       placeholder="Nhập mật khẩu" required>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <label class="small m-0" style="cursor: pointer;">
                                    <input type="checkbox" name="RememberMe"> Ghi nhớ đăng nhập
                                </label>
                                <a href="javascript:void(0);" class="small text-dark font-weight-bold" 
                                   data-toggle="modal" data-target="#forgotModal">Quên mật khẩu?</a>
                            </div>

                            <button type="submit" class="btn btn-dark btn-block py-3 font-weight-bold" 
                                    style="border-radius: 30px; letter-spacing: 1px;">ĐĂNG NHẬP</button>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted small">Chưa có tài khoản?</span>
                            <a href="index.php?controller=taikhoan&action=register" 
                               class="small font-weight-bold text-primary ml-1">Đăng ký ngay</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>