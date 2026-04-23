<?php
if (!function_exists('getStatusName')) {
    function getStatusName($status) {
        $map = [
            1 => "Chờ xác nhận", 
            2 => "Đã xác nhận", 
            3 => "Đang chuẩn bị hàng",
            4 => "Đã bàn giao shipper", 
            5 => "Đang giao hàng", 
            6 => "Đã giao hàng",
            7 => "Giao hàng thất bại", 
            8 => "Đã hủy"
        ];
        return $map[$status] ?? "Chờ xác nhận";
    }
}

// Các helper khác nếu cần
if (!function_exists('formatMoney')) {
    function formatMoney($value) {
        return number_format($value, 0, ',', '.') . ' ₫';
    }
}
?>

<section class="success-page">
    <section class="optical-breadcrumb">
        <div class="container text-center">
            <span class="optical-breadcrumb__eyebrow">Karma Eyewear Confirmation</span>
            <h1 class="text-white">Đặt hàng thành công</h1>
        </div>
    </section>

    <section class="success-section" style="padding: 80px 0;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    
                    <div class="success-hero-card text-center mb-5 shadow-sm">
                        <div class="icon-wrap mb-4">
                            <i class="fas fa-check-circle" style="font-size: 80px; color: #c5a059;"></i>
                        </div>
                        <h2 class="mb-3" style="font-family: 'Playfair Display', serif;">Cảm ơn quý khách!</h2>
                        <p class="lead text-muted">Chúng tôi đã nhận được đơn hàng của bạn.</p>
                        <div class="mt-4 p-3 d-inline-block rounded-pill" style="background: #fdf6e8; border: 1px solid #faebcc;">
                            <span class="text-dark font-weight-bold">Mã đơn hàng: #<?= htmlspecialchars($order['MaDonHang'] ?? 'KM-'.time()) ?></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card p-4 h-100 shadow-sm border-0">
                                <h4 class="pb-2 mb-4 border-bottom" style="font-family: 'Playfair Display', serif; color: #c5a059;">Thông tin đơn hàng</h4>
                                <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['NgayDat'] ?? 'now')) ?></p>
                                <p><strong>Trạng thái:</strong> <span class="badge badge-info px-3 py-2"><?= getStatusName($order['TrangThai'] ?? 1) ?></span></p>
                                <p><strong>Thanh toán:</strong> <?= ($order['PhuongThucThanhToan'] == 'VNPAY') ? 'Chuyển khoản Online' : 'Tiền mặt (COD)' ?></p>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card p-4 h-100 shadow-sm border-0">
                                <h4 class="pb-2 mb-4 border-bottom" style="font-family: 'Playfair Display', serif; color: #c5a059;">Thông tin giao nhận</h4>
                                <p><strong>Người nhận:</strong> <?= htmlspecialchars($order['HoTenNguoiNhan'] ?? '') ?></p>
                                <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['SoDienThoaiNguoiNhan'] ?? '') ?></p>
                                <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['DiaChiNhanHang'] ?? '') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="success-actions text-center mt-5">
                        <a href="index.php" class="btn btn-dark px-5 py-3 shadow-sm mr-md-3" style="border-radius: 30px; font-weight: bold;">TIẾP TỤC MUA SẮM</a>
                        <a href="index.php?controller=profile" class="btn btn-outline-dark px-5 py-3 mt-3 mt-md-0" style="border-radius: 30px; font-weight: bold;">KIỂM TRA ĐƠN HÀNG</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>