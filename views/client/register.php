<?php
$errors = $errors ?? [];
$rvm = $rvm ?? [];
?>

<section class="auth-page">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Account</span>
                <h1>Tạo tài khoản mới</h1>

                <nav>
                    <a href="index.php?controller=home">Trang chủ</a>
                    <span>/</span>
                    <span>Đăng ký</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="auth-section">
        <div class="container">
            <div class="auth-shell">
                <div class="row no-gutters">

                    <div class="col-lg-5 d-none d-lg-block">
                        <div class="auth-visual register-visual">
                            <img src="/BanMatKinh/public/images/auth/register.jpg" alt="Đăng ký Karma Eyewear">

                            <div class="auth-visual-overlay"></div>

                            <div class="auth-visual-content">
                                <span>Join Karma Eyewear</span>
                                <h2>Phong cách bắt đầu từ ánh nhìn.</h2>
                                <p>Tạo tài khoản để theo dõi đơn hàng, lưu thông tin mua sắm và nhận ưu đãi dành riêng cho thành viên.</p>

                                <a href="index.php?controller=taikhoan&action=login" class="auth-outline-btn">
                                    Đã có tài khoản? Đăng nhập
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="auth-form-panel">
                            <div class="auth-form-head">
                                <span>Join us</span>
                                <h2>Đăng ký thành viên</h2>
                                <p>Điền thông tin bên dưới để tạo tài khoản mua sắm.</p>
                            </div>

                            <div id="ajaxMessage">
                                <?php if (!empty($errors['Global'])): ?>
                                    <div class="alert alert-danger border-0 small">
                                        <?= implode('<br>', array_map('htmlspecialchars', $errors['Global'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <form id="registerForm" action="index.php?controller=taikhoan&action=register" method="POST">

                                <div class="form-group">
                                    <label class="auth-label">Tên đăng nhập *</label>
                                    <div class="auth-input-wrap">
                                        <i class="far fa-user"></i>
                                        <input 
                                            type="text" 
                                            name="Username" 
                                            class="form-control auth-input"
                                            placeholder="Ví dụ: nhatnguyen99"
                                            value="<?= htmlspecialchars($rvm['Username'] ?? '') ?>"
                                            required
                                        >
                                    </div>
                                    <?php if (!empty($errors['Username'])): ?>
                                        <small class="auth-error"><?= htmlspecialchars($errors['Username']) ?></small>
                                    <?php endif; ?>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="auth-label">Mật khẩu *</label>
                                        <div class="auth-input-wrap">
                                            <i class="fas fa-lock"></i>
                                            <input 
                                                type="password" 
                                                name="Password" 
                                                id="registerPassword"
                                                class="form-control auth-input"
                                                placeholder="Tối thiểu 6 ký tự"
                                                required
                                            >
                                            <button type="button" class="toggle-password" data-target="registerPassword">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                        <?php if (!empty($errors['Password'])): ?>
                                            <small class="auth-error"><?= htmlspecialchars($errors['Password']) ?></small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label class="auth-label">Nhập lại mật khẩu *</label>
                                        <div class="auth-input-wrap">
                                            <i class="fas fa-shield-alt"></i>
                                            <input 
                                                type="password" 
                                                name="ConfirmPassword" 
                                                id="registerConfirmPassword"
                                                class="form-control auth-input"
                                                placeholder="Nhập lại mật khẩu"
                                                required
                                            >
                                            <button type="button" class="toggle-password" data-target="registerConfirmPassword">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                        <?php if (!empty($errors['ConfirmPassword'])): ?>
                                            <small class="auth-error"><?= htmlspecialchars($errors['ConfirmPassword']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="auth-label">Họ</label>
                                        <div class="auth-input-wrap">
                                            <i class="far fa-id-card"></i>
                                            <input 
                                                type="text" 
                                                name="LastName" 
                                                class="form-control auth-input"
                                                placeholder="Nguyễn"
                                                value="<?= htmlspecialchars($rvm['LastName'] ?? '') ?>"
                                            >
                                        </div>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label class="auth-label">Tên</label>
                                        <div class="auth-input-wrap">
                                            <i class="far fa-id-card"></i>
                                            <input 
                                                type="text" 
                                                name="FirstName" 
                                                class="form-control auth-input"
                                                placeholder="Văn A"
                                                value="<?= htmlspecialchars($rvm['FirstName'] ?? '') ?>"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <?php if (!empty($errors['FullName'])): ?>
                                    <small class="auth-error d-block mb-3"><?= htmlspecialchars($errors['FullName']) ?></small>
                                <?php endif; ?>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="auth-label">Số điện thoại *</label>
                                        <div class="auth-input-wrap">
                                            <i class="fas fa-phone-alt"></i>
                                            <input 
                                                type="tel" 
                                                name="Mobile" 
                                                class="form-control auth-input"
                                                placeholder="09xxxxxxxx"
                                                value="<?= htmlspecialchars($rvm['Mobile'] ?? '') ?>"
                                                required
                                            >
                                        </div>
                                        <?php if (!empty($errors['Mobile'])): ?>
                                            <small class="auth-error"><?= htmlspecialchars($errors['Mobile']) ?></small>
                                        <?php endif; ?>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label class="auth-label">Email</label>
                                        <div class="auth-input-wrap">
                                            <i class="far fa-envelope"></i>
                                            <input 
                                                type="email" 
                                                name="Email" 
                                                class="form-control auth-input"
                                                placeholder="email@example.com"
                                                value="<?= htmlspecialchars($rvm['Email'] ?? '') ?>"
                                            >
                                        </div>
                                        <?php if (!empty($errors['Email'])): ?>
                                            <small class="auth-error"><?= htmlspecialchars($errors['Email']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label class="auth-label">Ngày sinh</label>
                                        <div class="auth-input-wrap">
                                            <i class="far fa-calendar-alt"></i>
                                            <input 
                                                type="date" 
                                                name="DateOfBirth" 
                                                class="form-control auth-input"
                                                value="<?= htmlspecialchars($rvm['DateOfBirth'] ?? '') ?>"
                                            >
                                        </div>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label class="auth-label">Giới tính</label>
                                        <div class="auth-input-wrap">
                                            <i class="fas fa-venus-mars"></i>
                                            <select name="Sex" class="form-control auth-input">
                                                <option value="">Chọn giới tính</option>
                                                <option value="Nam" <?= (($rvm['Sex'] ?? '') === 'Nam') ? 'selected' : '' ?>>Nam</option>
                                                <option value="Nữ" <?= (($rvm['Sex'] ?? '') === 'Nữ') ? 'selected' : '' ?>>Nữ</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="auth-label">Địa chỉ</label>
                                    <div class="auth-input-wrap">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <input 
                                            type="text" 
                                            name="Address" 
                                            class="form-control auth-input"
                                            placeholder="Số nhà, tên đường, Phường/Xã..."
                                            value="<?= htmlspecialchars($rvm['Address'] ?? '') ?>"
                                        >
                                    </div>
                                </div>

                                <button type="submit" id="btnRegister" class="btn-auth-submit">
                                    Tạo tài khoản ngay
                                </button>
                            </form>

                            <div class="auth-switch d-lg-none">
                                <span>Đã có tài khoản?</span>
                                <a href="index.php?controller=taikhoan&action=login">Đăng nhập ngay</a>
                            </div>

                            <div class="auth-note">
                                Thông tin của bạn được sử dụng để xử lý đơn hàng, hỗ trợ bảo hành và nâng cao trải nghiệm mua sắm tại Karma Eyewear.
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