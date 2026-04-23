<section class="checkout-page" style="padding: 60px 0;">
    <div class="container">
        <nav class="mb-4 small">
            <a href="index.php?controller=giohang" class="text-muted text-decoration-none">Giỏ hàng</a> 
            <span class="mx-2">/</span> 
            <span class="font-weight-bold">Thanh toán</span>
        </nav>

        <form action="index.php?controller=thanhtoan&action=process" method="POST">
            <div class="row">
                <div class="col-lg-7">
                    <div class="card p-4 mb-4">
                        <h3 class="mb-4" style="font-family: 'Playfair Display', serif;">Thông tin giao hàng</h3>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="small font-weight-bold">Họ tên người nhận *</label>
                                <input type="text" name="HoTenNguoiNhan" class="form-control" placeholder="Nhập đầy đủ họ tên" value="<?= htmlspecialchars($user['HoTen'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold">Số điện thoại *</label>
                                <input type="tel" name="SoDienThoaiNguoiNhan" class="form-control" placeholder="VD: 0912345xxx" value="<?= htmlspecialchars($user['SoDienThoai'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold">Email nhận thông báo</label>
                                <input type="email" name="Email" class="form-control" placeholder="email@vi-du.com" value="<?= htmlspecialchars($user['Email'] ?? '') ?>">
                            </div>
                            <div class="col-12 mb-3">
                                <label class="small font-weight-bold">Địa chỉ nhận hàng *</label>
                                <textarea name="DiaChiNhanHang" class="form-control" rows="3" placeholder="Số nhà, tên đường, Phường/Xã, Quận/Huyện, Tỉnh/TP" required><?= htmlspecialchars($user['DiaChi'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <h3 class="mt-4 mb-3" style="font-family: 'Playfair Display', serif;">Phương thức thanh toán</h3>
                        <div class="payment-methods">
                            <label class="form-check border p-3 rounded mb-2 d-flex align-items-center">
                                <input class="form-check-input mt-0" type="radio" name="PhuongThucThanhToan" id="cod" value="COD" checked>
                                <span class="ml-4">
                                    <strong>Thanh toán khi nhận hàng (COD)</strong>
                                    <small class="d-block text-muted">Nhận hàng rồi mới thanh toán tiền mặt</small>
                                </span>
                            </label>
                            <label class="form-check border p-3 rounded d-flex align-items-center">
                                <input class="form-check-input mt-0" type="radio" name="PhuongThucThanhToan" id="vnpay" value="VNPAY">
                                <span class="ml-4">
                                    <strong>Thanh toán qua VNPAY</strong>
                                    <small class="d-block text-muted">Thanh toán nhanh chóng qua QR Code hoặc thẻ ATM</small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card p-4 sticky-top" style="top: 100px;">
                        <h3 class="mb-4" style="font-family: 'Playfair Display', serif;">Tóm tắt đơn hàng</h3>
                        
                        <div class="order-items mb-4 pr-2">
                            <?php 
                            $subtotal = 0; $totalDiscount = 0;
                            // Kiểm tra giỏ hàng từ Session
                            $cartItems = $_SESSION['ShoppingCart'] ?? [];
                            foreach ($cartItems as $item): 
                                $itemPrice = $item['DonGia'] - $item['GiamGia'];
                                $subtotal += $item['DonGia'] * $item['SoLuong'];
                                $totalDiscount += $item['GiamGia'] * $item['SoLuong'];
                            ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div style="flex: 1;">
                                        <h6 class="mb-0 font-weight-bold text-truncate" style="max-width: 200px;"><?= htmlspecialchars($item['TenSanPham']) ?></h6>
                                        <small class="text-muted">Số lượng: <?= $item['SoLuong'] ?></small>
                                    </div>
                                    <div class="text-right">
                                        <span class="small font-weight-bold"><?= number_format($itemPrice * $item['SoLuong'], 0, ',', '.') ?>đ</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="price-details pt-3 border-top">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tạm tính:</span>
                                <span><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                            </div>
                            <?php if ($totalDiscount > 0): ?>
                            <div class="d-flex justify-content-between mb-2 text-danger">
                                <span class="small">Giảm giá sản phẩm:</span>
                                <span>-<?= number_format($totalDiscount, 0, ',', '.') ?>đ</span>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Phí vận chuyển:</span>
                                <span>30.000đ</span>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                                <strong class="h5">TỔNG CỘNG</strong>
                                <strong class="h4 text-primary"><?= number_format($subtotal - $totalDiscount + 30000, 0, ',', '.') ?>đ</strong>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark btn-block py-3 mt-4">
                            HOÀN TẤT ĐẶT HÀNG
                        </button>
                        
                        <p class="text-center small text-muted mt-3">
                            <i class="fas fa-shield-alt mr-1"></i> Thanh toán an toàn & Bảo mật
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>