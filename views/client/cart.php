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

$cart = $cart ?? [];

$totalOrder = 0;
$totalQuantity = 0;

foreach ($cart as $item) {
    $price = (float)($item['DonGia'] ?? 0);
    $qty = (int)($item['SoLuong'] ?? 0);

    $totalOrder += $price * $qty;
    $totalQuantity += $qty;
}

$shippingFee = $totalOrder >= 1000000 || $totalOrder == 0 ? 0 : 30000;
$grandTotal = $totalOrder + $shippingFee;
?>

<section class="cart-page-modern">
    <section class="optical-breadcrumb">
        <div class="container">
            <div class="optical-breadcrumb__inner">
                <span class="optical-breadcrumb__eyebrow">Karma Eyewear Cart</span>
                <h1>Túi hàng của bạn</h1>

                <nav>
                    <a href="index.php?controller=home">Trang chủ</a>
                    <span>/</span>
                    <span>Giỏ hàng</span>
                </nav>
            </div>
        </div>
    </section>

    <section class="cart-section-modern">
        <div class="container">

            <?php if (isset($_SESSION['CartError'])): ?>
                <div class="alert alert-danger page-alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['CartError']) ?>
                    <?php unset($_SESSION['CartError']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['CartSuccess'])): ?>
                <div class="alert alert-success page-alert">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?= htmlspecialchars($_SESSION['CartSuccess']) ?>
                    <?php unset($_SESSION['CartSuccess']); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($cart)): ?>
                <div class="cart-empty-modern">
                    <div class="cart-empty-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>

                    <h2>Giỏ hàng của bạn đang trống</h2>
                    <p>Khám phá bộ sưu tập mắt kính mới nhất và chọn cho mình một phong cách thật riêng.</p>

                    <a href="index.php?controller=sanpham">
                        Khám phá sản phẩm
                        <i class="fas fa-angle-right"></i>
                    </a>
                </div>
            <?php else: ?>

                <div class="cart-layout-modern">
                    <div class="cart-list-card">
                        <div class="cart-list-head">
                            <div>
                                <span>Shopping Bag</span>
                                <h2><?= count($cart) ?> sản phẩm trong giỏ</h2>
                            </div>

                            <a href="index.php?controller=sanpham">
                                <i class="fas fa-arrow-left"></i>
                                Tiếp tục mua sắm
                            </a>
                        </div>

                        <div class="cart-items">
                            <?php foreach ($cart as $item): ?>
                                <?php
                                    $productId = (int)($item['SanPhamId'] ?? 0);
                                    $qty = (int)($item['SoLuong'] ?? 1);
                                    $price = (float)($item['DonGia'] ?? 0);
                                    $giaGoc = (float)($item['GiaGoc'] ?? 0);
                                    $subtotal = $price * $qty;
                                ?>

                                <div class="cart-item-modern">
                                    <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="cart-item-img">
                                        <img 
                                            src="<?= normalizeImg($item['HinhAnh'] ?? '') ?>" 
                                            alt="<?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?>"
                                        >
                                    </a>

                                    <div class="cart-item-info">
                                        <div class="cart-item-meta">
                                            <?= htmlspecialchars($item['ThuongHieu'] ?? 'Karma Eyewear') ?>
                                        </div>

                                        <a href="index.php?controller=sanpham&action=detail&id=<?= $productId ?>" class="cart-item-name">
                                            <?= htmlspecialchars($item['TenSanPham'] ?? 'Sản phẩm') ?>
                                        </a>

                                        <div class="cart-item-type">
                                            <?= htmlspecialchars($item['LoaiSanPham'] ?? 'Mắt kính thời trang') ?>
                                        </div>

                                        <div class="cart-item-price">
                                            <strong><?= formatMoney($price) ?></strong>

                                            <?php if ($giaGoc > $price): ?>
                                                <del><?= formatMoney($giaGoc) ?></del>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="cart-item-qty">
                                        <form action="index.php?controller=giohang&action=update" method="POST" class="cart-qty-form">
                                            <input type="hidden" name="sanPhamId" value="<?= $productId ?>">

                                            <button type="button" class="cart-qty-btn" data-type="minus">-</button>

                                            <input 
                                                type="number" 
                                                name="soLuong" 
                                                value="<?= $qty ?>" 
                                                min="1"
                                                max="<?= (int)($item['SoLuongTon'] ?? 99) ?>"
                                            >

                                            <button type="button" class="cart-qty-btn" data-type="plus">+</button>

                                            <button type="submit" class="cart-save-btn">
                                                Lưu
                                            </button>
                                        </form>
                                    </div>

                                    <div class="cart-item-total">
                                        <span>Tạm tính</span>
                                        <strong><?= formatMoney($subtotal) ?></strong>
                                    </div>

                                    <a 
                                        href="javascript:void(0);"
                                        class="cart-remove-btn"
                                        data-confirm-url="index.php?controller=giohang&action=remove&sanPhamId=<?= $productId ?>"
                                        data-confirm-title="Xóa sản phẩm?"
                                        data-confirm-message="Bạn có chắc muốn xóa <strong><?= htmlspecialchars($item['TenSanPham'] ?? 'sản phẩm này') ?></strong> khỏi giỏ hàng không?"
                                        data-confirm-text="Xóa sản phẩm"
                                        data-confirm-icon="far fa-trash-alt"
                                        data-confirm-type="danger"
                                    >
                                        <i class="far fa-trash-alt"></i>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <aside class="cart-summary-modern">
                        <div class="cart-summary-card">
                            <span class="summary-eyebrow">Order Summary</span>
                            <h3>Tóm tắt đơn hàng</h3>

                            <div class="summary-line">
                                <span>Tổng sản phẩm</span>
                                <strong><?= $totalQuantity ?></strong>
                            </div>

                            <div class="summary-line">
                                <span>Tạm tính</span>
                                <strong><?= formatMoney($totalOrder) ?></strong>
                            </div>

                            <div class="summary-line">
                                <span>Phí vận chuyển</span>
                                <?php if ($shippingFee == 0): ?>
                                    <strong class="text-success">Miễn phí</strong>
                                <?php else: ?>
                                    <strong><?= formatMoney($shippingFee) ?></strong>
                                <?php endif; ?>
                            </div>

                            <?php if ($totalOrder < 1000000): ?>
                                <div class="free-ship-note">
                                    Mua thêm <strong><?= formatMoney(1000000 - $totalOrder) ?></strong> để được miễn phí vận chuyển.
                                </div>
                            <?php else: ?>
                                <div class="free-ship-note success">
                                    Bạn đã đạt điều kiện miễn phí vận chuyển.
                                </div>
                            <?php endif; ?>

                            <div class="summary-total">
                                <span>Tổng thanh toán</span>
                                <strong><?= formatMoney($grandTotal) ?></strong>
                            </div>

                            <a href="index.php?controller=thanhtoan" class="btn-checkout-modern">
                                Tiến hành đặt hàng
                                <i class="fas fa-arrow-right"></i>
                            </a>

                            <a 
                                href="javascript:void(0);"
                                class="btn-clear-cart"
                                data-confirm-url="index.php?controller=giohang&action=clear"
                                data-confirm-title="Xóa toàn bộ giỏ hàng?"
                                data-confirm-message="Toàn bộ sản phẩm trong giỏ sẽ bị xóa. Bạn có chắc muốn tiếp tục không?"
                                data-confirm-text="Xóa tất cả"
                                data-confirm-icon="fas fa-shopping-bag"
                                data-confirm-type="danger"
                            >
                                Xóa toàn bộ giỏ hàng
                            </a>
                        </div>

                        <div class="cart-support-card">
                            <i class="fas fa-headset"></i>
                            <div>
                                <strong>Cần tư vấn chọn kính?</strong>
                                <p>Đội ngũ Karma Eyewear luôn sẵn sàng hỗ trợ bạn.</p>
                            </div>
                        </div>
                    </aside>
                </div>

            <?php endif; ?>
        </div>
    </section>
</section>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".cart-qty-form").forEach(function (form) {
        const input = form.querySelector("input[name='soLuong']");
        const buttons = form.querySelectorAll(".cart-qty-btn");

        buttons.forEach(function (btn) {
            btn.addEventListener("click", function () {
                let value = parseInt(input.value || "1");
                const min = parseInt(input.getAttribute("min") || "1");
                const max = parseInt(input.getAttribute("max") || "99");

                if (this.dataset.type === "plus" && value < max) value++;
                if (this.dataset.type === "minus" && value > min) value--;

                input.value = value;
                form.submit();
            });
        });
    });
});
</script>