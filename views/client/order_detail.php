<section class="order-detail-page" style="padding: 60px 0;">
    <div class="container">
        <div class="card p-5 shadow-sm border-0">
            <div class="d-flex justify-content-between align-items-center mb-5 pb-3 border-bottom">
                <div class="order-header">
                    <span class="text-uppercase small text-muted" style="letter-spacing: 2px;">Order Details</span>
                    <h2 class="mb-0 mt-1">Đơn hàng: #<?= $order['MaDonHang'] ?></h2>
                </div>
                <a href="index.php?controller=profile" class="btn btn-outline-dark btn-sm px-4 rounded-pill">
                   <i class="fas fa-arrow-left mr-2"></i> QUAY LẠI
                </a>
            </div>

            <div class="row mb-5">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h5>Thông tin người nhận</h5>
                    <p>
                        <strong>Họ tên:</strong> <?= htmlspecialchars($order['HoTenNguoiNhan']) ?><br>
                        <strong>Điện thoại:</strong> <?= htmlspecialchars($order['SoDienThoaiNguoiNhan']) ?><br>
                        <strong>Địa chỉ:</strong> <?= htmlspecialchars($order['DiaChiNhanHang']) ?>
                    </p>
                </div>
                <div class="col-md-6 text-md-right">
                    <h5>Thông tin vận chuyển</h5>
                    <p>
                        <strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['NgayDat'])) ?><br>
                        
                        <strong>Thanh toán:</strong> <?= $order['PhuongThucThanhToan'] ?> 
                        <span class="badge <?= ($order['TrangThaiThanhToan'] == PaymentConstants::PAID) ? 'badge-success' : 'badge-light border' ?> ml-1">
                            <?= ($order['TrangThaiThanhToan'] == PaymentConstants::PAID) ? 'Đã thanh toán' : 'Chưa thanh toán' ?>
                        </span><br>
                        
                        <strong>Tình trạng:</strong> 
                        <span class="badge <?= OrderStatusConstants::getBadgeClass($order['TrangThai']) ?> px-3 py-2 text-white ml-1">
                            <?= OrderStatusConstants::getName($order['TrangThai']) ?>
                        </span>
                    </p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-center">Đơn giá</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                            <tr>
                                <td class="font-weight-bold text-dark"><?= htmlspecialchars($item['TenSanPhamSnapshot']) ?></td>
                                <td class="text-center"><?= number_format($item['DonGiaSnapshot'], 0, ',', '.') ?>đ</td>
                                <td class="text-center"><?= $item['SoLuong'] ?></td>
                                <td class="text-right font-weight-bold"><?= number_format($item['ThanhTien'], 0, ',', '.') ?>đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-light-faint">
                        <tr>
                            <td colspan="3" class="text-right">Tiền hàng:</td>
                            <td class="text-right text-dark"><?= number_format($order['TongTienHang'], 0, ',', '.') ?>đ</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right">Phí vận chuyển:</td>
                            <td class="text-right text-dark"><?= number_format($order['PhiVanChuyen'], 0, ',', '.') ?>đ</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-right">Giảm giá:</td>
                            <td class="text-right text-danger">-<?= number_format($order['GiamGia'], 0, ',', '.') ?>đ</td>
                        </tr>
                        <tr class="font-weight-bold">
                            <td colspan="3" class="text-right text-dark h5 pt-4">TỔNG THANH TOÁN:</td>
                            <td class="text-right text-danger-custom h4 pt-4"><?= number_format($order['TongThanhToan'], 0, ',', '.') ?>đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</section>