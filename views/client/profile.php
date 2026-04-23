<?php
// Logic PHP giữ nguyên như bạn cung cấp
$fullName = trim($account['HoTen'] ?? '');
if (empty($fullName)) { $fullName = $account['TenDangNhap']; }

$nameParts = explode(' ', $fullName);
$firstName = count($nameParts) > 0 ? end($nameParts) : "";
$lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 0, -1)) : "";
$avatarText = strtoupper(mb_substr($fullName, 0, 1));
?>

<section class="profile-page" style="padding: 60px 0;">
    <div class="container">
        <?php if (isset($_SESSION['ProfileSuccess'])): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4"><?= $_SESSION['ProfileSuccess']; unset($_SESSION['ProfileSuccess']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['PasswordError'])): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4"><?= $_SESSION['PasswordError']; unset($_SESSION['PasswordError']); ?></div>
        <?php endif; ?>

        <div class="profile-top-card d-flex align-items-center mb-5 shadow-sm">
            <div class="avatar">
                <?= $avatarText ?>
            </div>
            <div class="ml-4">
                <h2 class="mb-1"><?= htmlspecialchars($fullName) ?></h2>
                <span class="badge badge-pill badge-light border text-muted px-3">Tài khoản thành viên</span>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-5 col-md-12 mb-4">
                <div class="card p-4 shadow-sm">
                    <h3>Thông tin cá nhân</h3>
                    <form action="index.php?controller=profile&action=updateInfo" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Họ & Tên đệm</label>
                                <input type="text" name="LastName" class="form-control" value="<?= htmlspecialchars($lastName) ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tên</label>
                                <input type="text" name="FirstName" class="form-control" value="<?= htmlspecialchars($firstName) ?>">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="Mobile" class="form-control" value="<?= htmlspecialchars($account['SoDienThoai']) ?>">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Giới tính</label>
                            <select name="Sex" class="form-control">
                                <option value="Nam" <?= $account['GioiTinh'] == 1 ? 'selected' : '' ?>>Nam</option>
                                <option value="Nữ" <?= $account['GioiTinh'] == 0 ? 'selected' : '' ?>>Nữ</option>
                            </select>
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label">Địa chỉ giao hàng mặc định</label>
                            <input type="text" name="Address" class="form-control" value="<?= htmlspecialchars($account['DiaChi']) ?>">
                        </div>
                        <button type="submit" class="btn btn-dark w-100 py-3 font-weight-bold" style="border-radius: 30px;">LƯU THAY ĐỔI</button>
                    </form>

                    <div class="py-4"><hr></div>
                    
                    <h3>Đổi mật khẩu</h3>
                    <form action="index.php?controller=profile&action=changePassword" method="POST">
                        <div class="form-group mb-3">
                            <label class="form-label">Mật khẩu cũ</label>
                            <input type="password" name="passwdCurrent" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="form-group mb-4">
                            <label class="form-label">Mật khẩu mới</label>
                            <input type="password" name="PassWord" class="form-control" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-outline-dark w-100 py-2" style="border-radius: 30px;">CẬP NHẬT MẬT KHẨU</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-7 col-md-12">
                <div class="card p-4 shadow-sm h-100">
                    <h3>Lịch sử mua hàng</h3>
                    <?php if (empty($listOrderUser)): ?>
                        <div class="text-center py-5">
                            <i class="lnr lnr-calendar-full text-light mb-3 d-block" style="font-size: 48px;"></i>
                            <p class="text-muted">Bạn chưa thực hiện đơn hàng nào.</p>
                            <a href="index.php?controller=sanpham" class="btn btn-link text-gold font-weight-bold">Sắm kính ngay</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Ngày đặt</th>
                                        <th>Thành tiền</th>
                                        <th>Trạng thái</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($listOrderUser as $order): ?>
                                        <tr>
                                            <td><span class="font-weight-bold">#<?= $order['MaDonHang'] ?></span></td>
                                            <td><small class="text-muted"><?= date('d/m/Y', strtotime($order['NgayDat'])) ?></small></td>
                                            <td><span class="text-dark font-weight-bold"><?= number_format($order['TongThanhToan'], 0, ',', '.') ?>đ</span></td>
                                            <td>
                                                <span class="badge badge-pill <?= OrderStatusConstants::getBadgeClass($order['TrangThai']) ?> text-white px-2 py-1" style="font-size: 10px;">
                                                    <?= OrderStatusConstants::getName($order['TrangThai']) ?>
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <a href="index.php?controller=profile&action=orderDetail&maDonHang=<?= $order['MaDonHang'] ?>" class="btn btn-sm btn-light border px-3">Chi tiết</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <nav class="mt-auto">
                            <ul class="pagination pagination-sm justify-content-center pt-4">
                                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="index.php?controller=profile&page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>