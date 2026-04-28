<?php

class DashboardController
{
    private $pdo;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdo = $pdo;

        if (!isset($_SESSION['LoginInformation'])) {
            header("Location: index.php?controller=taikhoan&action=login");
            exit();
        }

        $roleCode = strtoupper(trim($_SESSION['LoginInformation']['MaVaiTro'] ?? ''));

        if (!in_array($roleCode, ['ADMIN', 'STAFF', 'SHIPPER'])) {
            header("Location: index.php");
            exit();
        }
    }

    public function index()
    {
        $pdo = $this->pdo;

        $sessionAccount = $_SESSION['LoginInformation'];

        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $isAdmin = ($roleCode === 'ADMIN');
        $isStaff = ($roleCode === 'STAFF');
        $isShipper = ($roleCode === 'SHIPPER');

        $displayName = $sessionAccount['HoTen']
            ?? $sessionAccount['TenDangNhap']
            ?? 'Tài khoản';

        $displayUsername = $sessionAccount['TenDangNhap'] ?? '';
        $roleName = $sessionAccount['TenVaiTro'] ?? $roleCode;

        $avatar = !empty($sessionAccount['AnhDaiDien'])
            ? $sessionAccount['AnhDaiDien']
            : '/BanMatKinh/public/img/default-avatar.png';

        // Giá trị mặc định để view không bị lỗi undefined variable
        $countPendingOrders = 0;
        $todayRevenue = 0;
        $lowStockCount = 0;
        $ordersInDelivery = 0;
        $numberOfBlogWaitingApproval = 0;
        $numberOfOrderProcessing = 0;
        $numberOfAssignedOrders = 0;

        try {
            if ($isAdmin || $isStaff) {
                $stmt = $pdo->query("
                    SELECT COUNT(*) 
                    FROM donhang 
                    WHERE TrangThai NOT IN ('DELIVERED', 'CANCELLED')
                ");
                $numberOfOrderProcessing = (int)$stmt->fetchColumn();
                $countPendingOrders = $numberOfOrderProcessing;
            }

            if ($isShipper) {
                $accountId = $sessionAccount['TaiKhoanId'] ?? 0;

                $stmt = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM donhang 
                    WHERE ShipperId = ? 
                    AND TrangThai NOT IN ('DELIVERED', 'CANCELLED')
                ");
                $stmt->execute([$accountId]);
                $numberOfAssignedOrders = (int)$stmt->fetchColumn();
                $ordersInDelivery = $numberOfAssignedOrders;
            }

            if ($isAdmin) {
                $stmt = $pdo->query("
                    SELECT COUNT(*) 
                    FROM baiviet 
                    WHERE TrangThai = 'DRAFT'
                ");
                $numberOfBlogWaitingApproval = (int)$stmt->fetchColumn();

                $stmt = $pdo->query("
                    SELECT COALESCE(SUM(TongTien), 0)
                    FROM donhang
                    WHERE DATE(NgayDat) = CURDATE()
                    AND TrangThai = 'DELIVERED'
                ");
                $todayRevenue = (float)$stmt->fetchColumn();

                $stmt = $pdo->query("
                    SELECT COUNT(*)
                    FROM sanpham
                    WHERE SoLuong <= 5
                ");
                $lowStockCount = (int)$stmt->fetchColumn();
            }
        } catch (PDOException $e) {
            // Tạm thời để dashboard vẫn chạy nếu tên bảng/cột chưa khớp DB
            $countPendingOrders = 0;
            $todayRevenue = 0;
            $lowStockCount = 0;
            $ordersInDelivery = 0;
            $numberOfBlogWaitingApproval = 0;
            $numberOfOrderProcessing = 0;
            $numberOfAssignedOrders = 0;
        }

        $title = "Dashboard";
        $viewContent = BASE_PATH . '/views/admin/dashboard_index.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }
}