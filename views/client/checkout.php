<?php
if (!function_exists('normalizeImg')) {
    function normalizeImg($path) {
        if (empty($path)) return "/BanMatKinh/public/images/no-image.png";
        if (strpos($path, 'http') === 0) return $path;
        if (strpos($path, '/BanMatKinh/') === 0) return $path;
        return "/BanMatKinh/public/images/" . ltrim($path, '/');
    }
}

if (!function_exists('formatMoney')) {
    function formatMoney($value) {
        return number_format((float)$value, 0, ',', '.') . 'đ';
    }
}

$user = $user ?? ($_SESSION['LoginInformation'] ?? []);
$cartItems = $_SESSION['ShoppingCart'] ?? [];

$totalHang = 0;
$totalDiscount = 0;
$totalQuantity = 0;

foreach ($cartItems as $item) {
    $qty = (int)($item['SoLuong'] ?? 0);
    $price = (float)($item['DonGia'] ?? 0);
    $original = (float)($item['GiaGoc'] ?? $price);

    $totalHang += $price * $qty;
    $totalQuantity += $qty;

    if ($original > $price) {
        $totalDiscount += ($original - $price) * $qty;
    }
}

$shippingFee = $totalHang >= 1000000 ? 0 : 30000;
$totalPay = $totalHang + $shippingFee;
?>

<section class="checkout-page-modern">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Checkout</span>
                <h1>Thanh toán đơn hàng</h1>

                <nav>
                    <a href="index.php?controller=home">Trang chủ</a>
                    <span>/</span>
                    <a href="index.php?controller=giohang">Giỏ hàng</a>
                    <span>/</span>
                    <span>Thanh toán</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="checkout-section-modern">
        <div class="container">

            <?php if (empty($cartItems)): ?>
                <div class="checkout-empty">
                    <i class="fas fa-shopping-bag"></i>
                    <h2>Giỏ hàng đang trống</h2>
                    <p>Bạn cần có sản phẩm trong giỏ hàng trước khi thanh toán.</p>
                    <a href="index.php?controller=sanpham">Tiếp tục mua sắm</a>
                </div>
            <?php else: ?>

                <form action="index.php?controller=thanhtoan&action=process" method="POST" class="checkout-form">
                    <div class="checkout-layout-modern">

                        <div class="checkout-left">
                            <div class="checkout-card">
                                <div class="checkout-card-head">
                                    <span>Shipping Information</span>
                                    <h2>Thông tin giao hàng</h2>
                                    <p>Vui lòng nhập chính xác thông tin để Karma Eyewear giao hàng thuận tiện.</p>
                                </div>

                                <div class="row">
                                    <div class="col-12 form-group">
                                        <label>Họ tên người nhận *</label>
                                        <div class="checkout-input-wrap">
                                            <i class="far fa-user"></i>
                                            <input 
                                                type="text" 
                                                name="HoTenNguoiNhan"
                                                placeholder="Nhập đầy đủ họ tên"
                                                value="<?= htmlspecialchars($user['HoTen'] ?? '') ?>"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Số điện thoại *</label>
                                        <div class="checkout-input-wrap">
                                            <i class="fas fa-phone-alt"></i>
                                            <input 
                                                type="tel" 
                                                name="SoDienThoaiNguoiNhan"
                                                placeholder="VD: 0912345xxx"
                                                value="<?= htmlspecialchars($user['SoDienThoai'] ?? '') ?>"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Email nhận thông báo</label>
                                        <div class="checkout-input-wrap">
                                            <i class="far fa-envelope"></i>
                                            <input 
                                                type="email" 
                                                name="Email"
                                                placeholder="email@vi-du.com"
                                                value="<?= htmlspecialchars($user['Email'] ?? '') ?>"
                                            >
                                        </div>
                                    </div>

                                    <div class="col-12 form-group">
                                        <label>Địa chỉ nhận hàng *</label>
                                        <div class="checkout-input-wrap textarea">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <textarea 
                                                name="DiaChiNhanHang"
                                                rows="4"
                                                placeholder="Số nhà, tên đường, Phường/Xã, Quận/Huyện, Tỉnh/TP"
                                                required
                                            ><?= htmlspecialchars($user['DiaChi'] ?? '') ?></textarea>
                                        </div>
                                    </div>

                                    <div class="col-12 form-group">
                                        <label>Ghi chú đơn hàng</label>
                                        <div class="checkout-input-wrap textarea">
                                            <i class="far fa-sticky-note"></i>
                                            <textarea 
                                                name="GhiChu"
                                                rows="3"
                                                placeholder="Ví dụ: giao giờ hành chính, gọi trước khi giao..."
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="checkout-card">
                                <div class="checkout-card-head">
                                    <span>Payment Method</span>
                                    <h2>Phương thức thanh toán</h2>
                                </div>

                                <div class="payment-method-list">
                                    <label class="payment-method-card active">
                                        <input type="radio" name="PhuongThucThanhToan" value="COD" checked>
                                        <div class="payment-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div>
                                            <strong>Thanh toán khi nhận hàng</strong>
                                            <small>Nhận hàng rồi mới thanh toán tiền mặt.</small>
                                        </div>
                                    </label>

                                    <label class="payment-method-card">
                                        <input type="radio" name="PhuongThucThanhToan" value="VNPAY">
                                        <div class="payment-icon">
                                            <i class="fas fa-qrcode"></i>
                                        </div>
                                        <div>
                                            <strong>Thanh toán qua VNPAY</strong>
                                            <small>Thanh toán nhanh qua QR Code, ATM hoặc ngân hàng.</small>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <aside class="checkout-summary-modern">
                            <div class="checkout-summary-card">
                                <span class="summary-eyebrow">Order Summary</span>
                                <h3>Tóm tắt đơn hàng</h3>

                                <div class="checkout-items">
                                    <?php foreach ($cartItems as $item): ?>
                                        <?php
                                            $productId = (int)($item['SanPhamId'] ?? 0);
                                            $qty = (int)($item['SoLuong'] ?? 1);
                                            $price = (float)($item['DonGia'] ?? 0);
                                            $lineTotal = $price * $qty;
                                        ?>

                                        <div class="checkout-item">
                                            <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="checkout-item-img">
                                                <img src="<?= normalizeImg($item['HinhAnh'] ?? '') ?>" alt="<?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?>">
                                                <span><?= $qty ?></span>
                                            </a>

                                            <div class="checkout-item-info">
                                                <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>">
                                                    <?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?>
                                                </a>
                                                <small><?= htmlspecialchars($item['ThuongHieu'] ?? 'Karma Eyewear') ?></small>
                                            </div>

                                            <strong><?= formatMoney($lineTotal) ?></strong>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="summary-line">
                                    <span>Tổng sản phẩm</span>
                                    <strong><?= $totalQuantity ?></strong>
                                </div>

                                <div class="summary-line">
                                    <span>Tạm tính</span>
                                    <strong><?= formatMoney($totalHang) ?></strong>
                                </div>

                                <?php if ($totalDiscount > 0): ?>
                                    <div class="summary-line discount">
                                        <span>Đã giảm so với giá gốc</span>
                                        <strong>-<?= formatMoney($totalDiscount) ?></strong>
                                    </div>
                                <?php endif; ?>

                                <div class="summary-line">
                                    <span>Phí vận chuyển</span>
                                    <?php if ($shippingFee == 0): ?>
                                        <strong class="text-success">Miễn phí</strong>
                                    <?php else: ?>
                                        <strong><?= formatMoney($shippingFee) ?></strong>
                                    <?php endif; ?>
                                </div>

                                <div class="checkout-secure-note">
                                    <i class="fas fa-shield-alt"></i>
                                    Thông tin thanh toán và đơn hàng được bảo mật.
                                </div>

                                <div class="summary-total">
                                    <span>Tổng thanh toán</span>
                                    <strong><?= formatMoney($totalPay) ?></strong>
                                </div>

                                <button type="submit" class="btn-place-order">
                                    Hoàn tất đặt hàng
                                    <i class="fas fa-arrow-right"></i>
                                </button>

                                <a href="index.php?controller=giohang" class="btn-back-cart">
                                    Quay lại giỏ hàng
                                </a>
                            </div>
                        </aside>

                    </div>
                </form>

            <?php endif; ?>

        </div>
    </section>
</section>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".payment-method-card").forEach(function (card) {
        card.addEventListener("click", function () {
            document.querySelectorAll(".payment-method-card").forEach(item => item.classList.remove("active"));
            this.classList.add("active");
            const input = this.querySelector("input[type='radio']");
            if (input) input.checked = true;
        });
    });
});
</script>