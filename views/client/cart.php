<div class="container py-5">
    <h2 class="cart-page-title">Túi hàng của bạn</h2>

    <?php if (isset($_SESSION['CartError'])): ?>
        <div class="alert alert-danger shadow-sm border-0"><?= $_SESSION['CartError']; unset($_SESSION['CartError']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['CartSuccess'])): ?>
        <div class="alert alert-success shadow-sm border-0"><?= $_SESSION['CartSuccess']; unset($_SESSION['CartSuccess']); ?></div>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
        <div class="text-center py-5 bg-white rounded shadow-sm">
            <i class="lnr lnr-cart" style="font-size: 60px; color: #eee; margin-bottom: 20px; display: block;"></i>
            <p class="text-muted">Giỏ hàng của bạn đang trống.</p>
            <a href="index.php?controller=sanpham" class="btn btn-dark px-4 py-2 mt-2">KHÁM PHÁ SẢN PHẨM</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <table class="table cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-right">Tổng</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalOrder = 0;
                        foreach ($cart as $item): 
                            $price = $item['DonGia'] - $item['GiamGia'];
                            $subtotal = $price * $item['SoLuong'];
                            $totalOrder += $subtotal;
                        ?>
                        <tr class="cart-item-row">
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="public/images/<?= $item['HinhAnh'] ?>" class="cart-product-img">
                                    <a href="index.php?controller=sanpham&action=detail&id=<?= $item['SanPhamId'] ?>" class="cart-product-name">
                                        <?= htmlspecialchars($item['TenSanPham']) ?>
                                    </a>
                                </div>
                            </td>
                            <td class="font-weight-bold"><?= number_format($price, 0, ',', '.') ?>đ</td>
                            <td>
                                <form action="index.php?controller=giohang&action=update" method="POST" class="quantity-control mx-auto">
                                    <input type="hidden" name="sanPhamId" value="<?= $item['SanPhamId'] ?>">
                                    <input type="number" name="soLuong" value="<?= $item['SoLuong'] ?>" min="1" onchange="this.form.submit()">
                                    <button type="submit" class="small text-muted border-0 bg-transparent">Lưu</button>
                                </form>
                            </td>
                            <td class="text-right font-weight-bold text-dark"><?= number_format($subtotal, 0, ',', '.') ?>đ</td>
                            <td class="text-center">
                                <a href="index.php?controller=giohang&action=remove&sanPhamId=<?= $item['SanPhamId'] ?>" class="text-muted">
                                    <i class="lnr lnr-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="mt-4">
                    <a href="index.php?controller=sanpham" class="text-dark font-weight-bold">
                        <i class="fas fa-arrow-left mr-2"></i> Tiếp tục mua sắm
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="cart-summary shadow-lg">
                    <h4 class="mb-4" style="font-family: 'Playfair Display', serif;">Tạm tính</h4>
                    <div class="summary-line">
                        <span>Số lượng sản phẩm:</span>
                        <span><?= count($cart) ?></span>
                    </div>
                    <div class="summary-line">
                        <span>Phí vận chuyển:</span>
                        <span class="text-success">Miễn phí</span>
                    </div>
                    <div class="summary-total">
                        <span>Tổng tiền:</span>
                        <span><?= number_format($totalOrder, 0, ',', '.') ?>đ</span>
                    </div>
                    
                    <a href="index.php?controller=thanhtoan" class="btn btn-checkout text-decoration-none d-block text-center mt-4">
                        Tiến hành đặt hàng
                    </a>
                    
                    <div class="text-center mt-3">
                        <a href="index.php?controller=giohang&action=clear" class="text-white-50 small" onclick="return confirm('Bạn muốn xóa hết giỏ hàng?')">Xóa toàn bộ giỏ hàng</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>