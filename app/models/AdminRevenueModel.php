<?php
require_once BASE_PATH . '/app/helpers/OrderConstants.php';

class AdminRevenueModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getRevenueStats($fromDate = '', $toDate = '')
    {
        $where = "WHERE 1=1";
        $params = [];

        if ($fromDate !== '') {
            $where .= " AND DATE(NgayDat) >= ?";
            $params[] = $fromDate;
        }

        if ($toDate !== '') {
            $where .= " AND DATE(NgayDat) <= ?";
            $params[] = $toDate;
        }

        $delivered = OrderStatusConstants::DELIVERED;
        $cancelled = OrderStatusConstants::CANCELLED;
        $pending = OrderStatusConstants::PENDING;

        $cod = PaymentConstants::COD;
        $vnpay = PaymentConstants::VNPAY;
        $paid = PaymentConstants::PAID;
        $pendingPay = PaymentConstants::PENDING;
        $failed = PaymentConstants::FAILED;

        $sql = "
            SELECT 
                COALESCE(SUM(
                    CASE 
                        WHEN TrangThai = ?
                             AND (
                                PhuongThucThanhToan = ?
                                OR (PhuongThucThanhToan = ? AND TrangThaiThanhToan = ?)
                             )
                        THEN TongThanhToan 
                        ELSE 0 
                    END
                ), 0) AS TotalRevenue,

                COALESCE(SUM(
                    CASE 
                        WHEN TrangThai = ?
                             AND MONTH(NgayDat) = MONTH(NOW())
                             AND YEAR(NgayDat) = YEAR(NOW())
                             AND (
                                PhuongThucThanhToan = ?
                                OR (PhuongThucThanhToan = ? AND TrangThaiThanhToan = ?)
                             )
                        THEN TongThanhToan 
                        ELSE 0 
                    END
                ), 0) AS MonthRevenue,

                COALESCE(SUM(
                    CASE 
                        WHEN TrangThai = ?
                             AND DATE(NgayDat) = CURDATE()
                             AND (
                                PhuongThucThanhToan = ?
                                OR (PhuongThucThanhToan = ? AND TrangThaiThanhToan = ?)
                             )
                        THEN TongThanhToan 
                        ELSE 0 
                    END
                ), 0) AS TodayRevenue,

                COUNT(CASE WHEN TrangThai = ? THEN DonHangId END) AS DeliveredOrders,
                COUNT(CASE WHEN TrangThai = ? THEN DonHangId END) AS CancelledOrders,
                COUNT(CASE WHEN TrangThai = ? THEN DonHangId END) AS PendingOrders,

                COUNT(*) AS AllOrders,

                COUNT(CASE WHEN TrangThaiThanhToan = ? THEN DonHangId END) AS PaidPayments,
                COUNT(CASE WHEN TrangThaiThanhToan = ? THEN DonHangId END) AS PendingPayments,
                COUNT(CASE WHEN TrangThaiThanhToan = ? THEN DonHangId END) AS FailedPayments,

                COALESCE(SUM(
                    CASE 
                        WHEN TrangThai = ? AND PhuongThucThanhToan = ?
                        THEN TongThanhToan 
                        ELSE 0 
                    END
                ), 0) AS RevenueCOD,

                COALESCE(SUM(
                    CASE 
                        WHEN TrangThai = ?
                             AND PhuongThucThanhToan = ?
                             AND TrangThaiThanhToan = ?
                        THEN TongThanhToan 
                        ELSE 0 
                    END
                ), 0) AS RevenueVNPAY,

                COALESCE(AVG(
                    CASE 
                        WHEN TrangThai = ?
                             AND (
                                PhuongThucThanhToan = ?
                                OR (PhuongThucThanhToan = ? AND TrangThaiThanhToan = ?)
                             )
                        THEN TongThanhToan
                        ELSE NULL
                    END
                ), 0) AS AverageOrderValue

            FROM donhang
            {$where}
        ";

        $queryParams = [
            $delivered, $cod, $vnpay, $paid,
            $delivered, $cod, $vnpay, $paid,
            $delivered, $cod, $vnpay, $paid,

            $delivered,
            $cancelled,
            $pending,

            $paid,
            $pendingPay,
            $failed,

            $delivered, $cod,
            $delivered, $vnpay, $paid,

            $delivered, $cod, $vnpay, $paid
        ];

        $queryParams = array_merge($queryParams, $params);

        $stmt = $this->db->prepare($sql);
        $stmt->execute($queryParams);

        $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $stats['TotalProductSold'] = $this->getTotalProductSold($fromDate, $toDate);
        $stats['TotalRevenue'] = (float)($stats['TotalRevenue'] ?? 0);
        $stats['MonthRevenue'] = (float)($stats['MonthRevenue'] ?? 0);
        $stats['TodayRevenue'] = (float)($stats['TodayRevenue'] ?? 0);
        $stats['RevenueCOD'] = (float)($stats['RevenueCOD'] ?? 0);
        $stats['RevenueVNPAY'] = (float)($stats['RevenueVNPAY'] ?? 0);
        $stats['AverageOrderValue'] = (float)($stats['AverageOrderValue'] ?? 0);

        return $stats;
    }

    public function getTotalProductSold($fromDate = '', $toDate = '')
    {
        $where = "
            WHERE dh.TrangThai = ?
              AND (
                    dh.PhuongThucThanhToan = ?
                    OR (
                        dh.PhuongThucThanhToan = ?
                        AND dh.TrangThaiThanhToan = ?
                    )
              )
        ";

        $params = [
            OrderStatusConstants::DELIVERED,
            PaymentConstants::COD,
            PaymentConstants::VNPAY,
            PaymentConstants::PAID
        ];

        if ($fromDate !== '') {
            $where .= " AND DATE(dh.NgayDat) >= ?";
            $params[] = $fromDate;
        }

        if ($toDate !== '') {
            $where .= " AND DATE(dh.NgayDat) <= ?";
            $params[] = $toDate;
        }

        $sql = "
            SELECT COALESCE(SUM(ct.SoLuong), 0)
            FROM chitietdonhang ct
            JOIN donhang dh ON ct.DonHangId = dh.DonHangId
            {$where}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    public function getDailyRevenueLast7Days()
    {
        $sql = "
            SELECT 
                DATE(NgayDat) AS RevenueDate,
                COALESCE(SUM(
                    CASE 
                        WHEN TrangThai = ?
                             AND (
                                PhuongThucThanhToan = ?
                                OR (PhuongThucThanhToan = ? AND TrangThaiThanhToan = ?)
                             )
                        THEN TongThanhToan
                        ELSE 0
                    END
                ), 0) AS Revenue,
                COUNT(CASE WHEN TrangThai = ? THEN DonHangId END) AS OrderCount
            FROM donhang
            WHERE DATE(NgayDat) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY DATE(NgayDat)
            ORDER BY DATE(NgayDat) ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            OrderStatusConstants::DELIVERED,
            PaymentConstants::COD,
            PaymentConstants::VNPAY,
            PaymentConstants::PAID,
            OrderStatusConstants::DELIVERED
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $row) {
            $map[$row['RevenueDate']] = $row;
        }

        $result = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));

            $result[] = [
                'RevenueDate' => $date,
                'Label' => date('d/m', strtotime($date)),
                'Revenue' => isset($map[$date]) ? (float)$map[$date]['Revenue'] : 0,
                'OrderCount' => isset($map[$date]) ? (int)$map[$date]['OrderCount'] : 0
            ];
        }

        return $result;
    }

    public function getMonthlyRevenueCurrentYear()
    {
        $sql = "
            SELECT 
                MONTH(NgayDat) AS RevenueMonth,
                COALESCE(SUM(
                    CASE 
                        WHEN TrangThai = ?
                             AND (
                                PhuongThucThanhToan = ?
                                OR (PhuongThucThanhToan = ? AND TrangThaiThanhToan = ?)
                             )
                        THEN TongThanhToan
                        ELSE 0
                    END
                ), 0) AS Revenue,
                COUNT(CASE WHEN TrangThai = ? THEN DonHangId END) AS OrderCount
            FROM donhang
            WHERE YEAR(NgayDat) = YEAR(NOW())
            GROUP BY MONTH(NgayDat)
            ORDER BY MONTH(NgayDat) ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            OrderStatusConstants::DELIVERED,
            PaymentConstants::COD,
            PaymentConstants::VNPAY,
            PaymentConstants::PAID,
            OrderStatusConstants::DELIVERED
        ]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $row) {
            $map[(int)$row['RevenueMonth']] = $row;
        }

        $result = [];

        for ($month = 1; $month <= 12; $month++) {
            $result[] = [
                'RevenueMonth' => $month,
                'Label' => 'T' . $month,
                'Revenue' => isset($map[$month]) ? (float)$map[$month]['Revenue'] : 0,
                'OrderCount' => isset($map[$month]) ? (int)$map[$month]['OrderCount'] : 0
            ];
        }

        return $result;
    }

    public function getTopSellingProducts($limit = 5)
    {
        $sql = "
            SELECT 
                ct.SanPhamId,
                COALESCE(sp.TenSanPham, ct.TenSanPhamSnapshot) AS TenSanPham,
                sp.MaSanPham,
                sp.HinhAnhChinh,
                SUM(ct.SoLuong) AS TotalQty,
                SUM(ct.ThanhTien) AS TotalAmount
            FROM chitietdonhang ct
            JOIN donhang dh ON ct.DonHangId = dh.DonHangId
            LEFT JOIN sanpham sp ON ct.SanPhamId = sp.SanPhamId
            WHERE dh.TrangThai = ?
              AND (
                    dh.PhuongThucThanhToan = ?
                    OR (
                        dh.PhuongThucThanhToan = ?
                        AND dh.TrangThaiThanhToan = ?
                    )
              )
            GROUP BY 
                ct.SanPhamId,
                COALESCE(sp.TenSanPham, ct.TenSanPhamSnapshot),
                sp.MaSanPham,
                sp.HinhAnhChinh
            ORDER BY TotalQty DESC, TotalAmount DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, OrderStatusConstants::DELIVERED, PDO::PARAM_INT);
        $stmt->bindValue(2, PaymentConstants::COD);
        $stmt->bindValue(3, PaymentConstants::VNPAY);
        $stmt->bindValue(4, PaymentConstants::PAID);
        $stmt->bindValue(5, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}