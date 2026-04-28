<section class="auth-page">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Account</span>
                <h1>Đăng nhập tài khoản</h1>

                <nav>
                    <a href="index.php?controller=home">Trang chủ</a>
                    <span>/</span>
                    <span>Đăng nhập</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="auth-section">
        <div class="container">
            <div class="auth-shell">
                <div class="row no-gutters">

                    <div class="col-lg-6 d-none d-lg-block">
                        <div class="auth-visual">
                            <img src="/BanMatKinh/public/images/auth/login.jpg" alt="Đăng nhập Karma Eyewear">

                            <div class="auth-visual-overlay"></div>

                            <div class="auth-visual-content">
                                <span>Premium Eyewear</span>
                                <h2>Chào mừng trở lại!</h2>
                                <p>Đăng nhập để theo dõi đơn hàng, lưu sản phẩm yêu thích và nhận ưu đãi riêng cho thành viên.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="auth-form-panel">
                            <div class="auth-form-head">
                                <span>Member access</span>
                                <h2>Đăng nhập</h2>
                                <p>Nhập thông tin tài khoản để tiếp tục mua sắm.</p>
                            </div>

                            <div id="authMessage">
                                <?php if (!empty($errors['Global'])): ?>
                                    <div class="alert alert-danger border-0 small">
                                        <?= implode('<br>', array_map('htmlspecialchars', $errors['Global'])) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['NotificationLogin'])): ?>
                                    <div class="alert alert-warning border-0 small">
                                        <?= htmlspecialchars($_SESSION['NotificationLogin']) ?>
                                        <?php unset($_SESSION['NotificationLogin']); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['success'])): ?>
                                    <div class="alert alert-success border-0 small">
                                        <?= htmlspecialchars($_SESSION['success']) ?>
                                        <?php unset($_SESSION['success']); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['error'])): ?>
                                    <div class="alert alert-danger border-0 small">
                                        <?= htmlspecialchars($_SESSION['error']) ?>
                                        <?php unset($_SESSION['error']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <form action="index.php?controller=taikhoan&action=login" method="POST" class="auth-form">
                                <div class="form-group">
                                    <label class="auth-label">Tên đăng nhập / Email / Số điện thoại</label>

                                    <div class="auth-input-wrap">
                                        <i class="far fa-user"></i>
                                        <input 
                                            type="text" 
                                            name="Username" 
                                            class="form-control auth-input"
                                            placeholder="Nhập tên đăng nhập, email hoặc số điện thoại"
                                            value="<?= htmlspecialchars($_POST['Username'] ?? '') ?>"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="auth-label">Mật khẩu</label>

                                    <div class="auth-input-wrap">
                                        <i class="fas fa-lock"></i>
                                        <input 
                                            type="password" 
                                            name="Password" 
                                            id="loginPassword"
                                            class="form-control auth-input"
                                            placeholder="Nhập mật khẩu"
                                            required
                                        >

                                        <button type="button" class="toggle-password" data-target="loginPassword">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="auth-options">
                                    <label>
                                        <input type="checkbox" name="RememberMe">
                                        <span>Ghi nhớ đăng nhập</span>
                                    </label>

                                    <a href="javascript:void(0);" data-toggle="modal" data-target="#forgotModal">
                                        Quên mật khẩu?
                                    </a>
                                </div>

                                <button type="submit" class="btn-auth-submit">
                                    Đăng nhập
                                </button>
                            </form>

                            <div class="auth-switch">
                                <span>Chưa có tài khoản?</span>
                                <a href="index.php?controller=taikhoan&action=register">Đăng ký ngay</a>
                            </div>

                            <div class="auth-note">
                                Bằng việc đăng nhập, bạn đồng ý với chính sách bảo mật và điều khoản sử dụng của Karma Eyewear.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
</section>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".toggle-password").forEach(function (button) {
        button.addEventListener("click", function () {
            const targetId = this.getAttribute("data-target");
            const input = document.getElementById(targetId);
            const icon = this.querySelector("i");

            if (!input) return;

            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });
    });
});
</script>