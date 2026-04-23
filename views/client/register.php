<section class="auth-page">
    <section class="optical-breadcrumb">
        <div class="container text-center">
            <span class="optical-breadcrumb__eyebrow">Karma Eyewear Account</span>
            <h1 class="text-white">Tạo tài khoản mới</h1>
            <nav class="mt-2">
                <a href="index.php" class="text-white-50">Trang chủ</a> 
                <span class="text-white-50 mx-2">/</span> 
                <span class="text-white">Đăng ký</span>
            </nav>
        </div>
    </section>

    <section class="auth-section" style="padding: 80px 0;">
        <div class="container">
            <div class="auth-shell bg-white shadow-lg overflow-hidden" style="border-radius: 20px;">
                <div class="row no-gutters">
                    <div class="col-lg-5 bg-dark text-white p-5 d-flex flex-column justify-content-center text-center">
                        <span class="text-uppercase small" style="color: var(--gold); letter-spacing: 2px;">Already a member?</span>
                        <h2 class="my-4">Đã có tài khoản?</h2>
                        <p class="mb-5 opacity-75">Tham gia cộng đồng Karma Eyewear để nhận các ưu đãi độc quyền và cập nhật xu hướng mắt kính mới nhất.</p>
                        <a href="index.php?controller=taikhoan&action=login" class="btn btn-outline-light py-3 px-5 mx-auto">Đăng nhập ngay</a>
                    </div>

                    <div class="col-lg-7 p-5 bg-white">
                        <div class="auth-form-head mb-4">
                            <span class="text-uppercase small text-muted font-weight-bold" style="letter-spacing: 1px;">Join us</span>
                            <h3 class="font-weight-bold" style="font-family: 'Playfair Display', serif; font-size: 28px;">Đăng ký thành viên</h3>
                        </div>

                        <div id="ajaxMessage">
                            <?php if (isset($errors['Global'])): ?>
                                <div class="alert alert-danger border-0 small"><?= implode('<br>', $errors['Global']) ?></div>
                            <?php endif; ?>
                        </div>

                        <form id="registerForm" action="index.php?controller=taikhoan&action=register" method="POST">
                            <div class="row">
                                <div class="col-12 form-group mb-4">
                                    <label class="auth-label">Tên đăng nhập *</label>
                                    <input type="text" name="Username" class="form-control auth-input" 
                                        placeholder="Ví dụ: nhatnguyen99" 
                                        value="<?= htmlspecialchars($rvm['Username'] ?? '') ?>" required>
                                    <small class="text-danger"><?= $errors['Username'] ?? '' ?></small>
                                </div>

                                <div class="col-md-6 form-group mb-4">
                                    <label class="auth-label">Mật khẩu *</label>
                                    <input type="password" name="Password" class="form-control auth-input" placeholder="******" required>
                                    <small class="text-danger"><?= $errors['Password'] ?? '' ?></small>
                                </div>

                                <div class="col-md-6 form-group mb-4">
                                    <label class="auth-label">Nhập lại mật khẩu *</label>
                                    <input type="password" name="ConfirmPassword" class="form-control auth-input" required>
                                    <small class="text-danger"><?= $errors['ConfirmPassword'] ?? '' ?></small>
                                </div>

                                <div class="col-md-6 form-group mb-4">
                                    <label class="auth-label">Họ</label>
                                    <input type="text" name="LastName" class="form-control auth-input" 
                                        placeholder="Nguyễn" value="<?= htmlspecialchars($rvm['LastName'] ?? '') ?>">
                                </div>

                                <div class="col-md-6 form-group mb-4">
                                    <label class="auth-label">Tên</label>
                                    <input type="text" name="FirstName" class="form-control auth-input" 
                                        placeholder="Văn A" value="<?= htmlspecialchars($rvm['FirstName'] ?? '') ?>">
                                </div>

                                <div class="col-md-6 form-group mb-4">
                                    <label class="auth-label">Số điện thoại *</label>
                                    <input type="tel" name="Mobile" class="form-control auth-input" 
                                        placeholder="09xxxxxxx" value="<?= htmlspecialchars($rvm['Mobile'] ?? '') ?>" required>
                                    <small class="text-danger"><?= $errors['Mobile'] ?? '' ?></small>
                                </div>

                                <div class="col-md-6 form-group mb-4">
                                    <label class="auth-label">Email</label>
                                    <input type="email" name="Email" class="form-control auth-input" 
                                        placeholder="email@example.com" value="<?= htmlspecialchars($rvm['Email'] ?? '') ?>">
                                    <small class="text-danger"><?= $errors['Email'] ?? '' ?></small>
                                </div>

                                <div class="col-md-6 form-group mb-4">
                                    <label class="auth-label">Ngày sinh</label>
                                    <input type="date" name="DateOfBirth" class="form-control auth-input" 
                                        value="<?= htmlspecialchars($rvm['DateOfBirth'] ?? '') ?>">
                                </div>

                                <div class="col-md-6 form-group mb-4">
                                    <label class="auth-label">Giới tính</label>
                                    <select name="Sex" class="form-control auth-input">
                                        <option value="">Chọn giới tính</option>
                                        <option value="Nam" <?= (isset($rvm['Sex']) && $rvm['Sex'] == 'Nam') ? 'selected' : '' ?>>Nam</option>
                                        <option value="Nữ" <?= (isset($rvm['Sex']) && $rvm['Sex'] == 'Nữ') ? 'selected' : '' ?>>Nữ</option>
                                    </select>
                                </div>

                                <div class="col-12 form-group mb-5">
                                    <label class="auth-label">Địa chỉ</label>
                                    <input type="text" name="Address" class="form-control auth-input" 
                                        placeholder="Số nhà, tên đường, Phường/Xã..." 
                                        value="<?= htmlspecialchars($rvm['Address'] ?? '') ?>">
                                </div>
                            </div>

                            <button type="submit" id="btnRegister" class="btn btn-dark btn-block py-3 font-weight-bold shadow">
                                TẠO TÀI KHOẢN NGAY
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>