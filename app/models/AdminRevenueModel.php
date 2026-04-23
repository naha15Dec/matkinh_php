<?php
class AdminRevenueModel {
    private $db;
    public function __construct($pdo) { $this->db = $pdo; }

    public function getRevenueStats() {
        // Lấy các hằng số để dùng trong SQL cho chuẩn
        $statusDelivered = 6; // OrderStatusConstants::DELIVERED
        $payFailed = "Failed"; // PaymentConstants::FAILED
        $methodCod = "COD";
        $methodVnpay = "VNPAY";

        $sql = "SELECT 
            -- Tổng doanh thu (Đơn thành công)
            SUM(CASE WHEN TrangThai = $statusDelivered THEN TongThanhToan ELSE 0 END) as TotalRevenue,
            
            -- Doanh thu tháng này
            SUM(CASE WHEN TrangThai = $statusDelivered 
                     AND MONTH(NgayDat) = MONTH(NOW()) 
                     AND YEAR(NgayDat) = YEAR(NOW()) THEN TongThanhToan ELSE 0 END) as MonthRevenue,
            
            -- Doanh thu hôm nay
            SUM(CASE WHEN TrangThai = $statusDelivered 
                     AND DATE(NgayDat) = CURDATE() THEN TongThanhToan ELSE 0 END) as TodayRevenue,
            
            -- Đếm số đơn thành công
            COUNT(CASE WHEN TrangThai = $statusDelivered THEN DonHangId END) as TotalOrders,
            
            -- Đếm số đơn thanh toán thất bại (Thường là do lỗi VNPay)
            COUNT(CASE WHEN TrangThaiThanhToan = '$payFailed' THEN DonHangId END) as FailedPayments,
            
            -- Doanh thu theo phương thức
            SUM(CASE WHEN TrangThai = $statusDelivered AND PhuongThucThanhToan = '$methodCod' THEN TongThanhToan ELSE 0 END) as RevenueCOD,
            SUM(CASE WHEN TrangThai = $statusDelivered AND PhuongThucThanhToan = '$methodVnpay' THEN TongThanhToan ELSE 0 END) as RevenueVNPAY
            
        FROM donhang";
        
        $stats = $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);

        // Tính tổng sản phẩm đã bán từ các đơn thành công
        $sqlProduct = "SELECT SUM(ct.SoLuong) 
                       FROM chitietdonhang ct 
                       JOIN donhang dh ON ct.DonHangId = dh.DonHangId 
                       WHERE dh.TrangThai = $statusDelivered";
        $stats['TotalProductSold'] = $this->db->query($sqlProduct)->fetchColumn() ?: 0;

        return $stats;
    }
}