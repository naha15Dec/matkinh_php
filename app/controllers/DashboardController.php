<?php
require_once BASE_PATH . '/app/helpers/OrderConstants.php';

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

        if (!in_array($roleCode, ['ADMIN', 'STAFF', 'SHIPPER'], true)) {
            $_SESSION['error'] = "Bạn không có quyền truy cập khu vực quản trị.";
            header("Location: index.php?controller=error");
            exit();
        }
    }

    public function index()
    {
        $pdo = $this->pdo;

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $isAdmin = $roleCode === 'ADMIN';
        $isStaff = $roleCode === 'STAFF';
        $isShipper = $roleCode === 'SHIPPER';

        $displayName = $sessionAccount['HoTen']
            ?? $sessionAccount['TenDangNhap']
            ?? 'Tài khoản';

        $displayUsername = $sessionAccount['TenDangNhap'] ?? '';
        $roleName = $sessionAccount['TenVaiTro'] ?? $roleCode;

        $avatar = !empty($sessionAccount['AnhDaiDien'])
            ? $sessionAccount['AnhDaiDien']
            : '/BanMatKinh/public/img/default-avatar.png';

        $countPendingOrders = 0;
        $todayRevenue = 0;
        $lowStockCount = 0;
        $ordersInDelivery = 0;
        $numberOfBlogWaitingApproval = 0;
        $numberOfOrderProcessing = 0;
        $numberOfAssignedOrders = 0;

        $todayDeliveredOrders = 0;
        $cancelledOrders = 0;
        $outOfStockCount = 0;
        $totalActiveProducts = 0;

        try {
            if ($isAdmin || $isStaff) {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM donhang
                    WHERE TrangThai IN (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    OrderStatusConstants::PENDING,
                    OrderStatusConstants::CONFIRMED,
                    OrderStatusConstants::PREPARING,
                    OrderStatusConstants::ASSIGNED_TO_SHIPPER,
                    OrderStatusConstants::DELIVERING
                ]);

                $numberOfOrderProcessing = (int)$stmt->fetchColumn();

                $stmt = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM donhang
                    WHERE TrangThai = ?
                ");

                $stmt->execute([
                    OrderStatusConstants::PENDING
                ]);

                $countPendingOrders = (int)$stmt->fetchColumn();
            }

            if ($isShipper) {
                $accountId = (int)($sessionAccount['TaiKhoanId'] ?? 0);

                $stmt = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM donhang
                    WHERE ShipperId = ?
                      AND TrangThai IN (?, ?, ?)
                ");

                $stmt->execute([
                    $accountId,
                    OrderStatusConstants::ASSIGNED_TO_SHIPPER,
                    OrderStatusConstants::DELIVERING,
                    OrderStatusConstants::DELIVERY_FAILED
                ]);

                $numberOfAssignedOrders = (int)$stmt->fetchColumn();
                $ordersInDelivery = $numberOfAssignedOrders;
            }

            if ($isAdmin) {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM baiviet
                    WHERE TrangThai = ?
                ");

                $stmt->execute([0]);
                $numberOfBlogWaitingApproval = (int)$stmt->fetchColumn();

                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(
                        CASE 
                            WHEN TrangThai = ?
                             AND (
                                PhuongThucThanhToan = ?
                                OR (
                                    PhuongThucThanhToan = ?
                                    AND TrangThaiThanhToan = ?
                                )
                             )
                            THEN TongThanhToan
                            ELSE 0
                        END
                    ), 0)
                    FROM donhang
                    WHERE DATE(NgayDat) = CURDATE()
                ");

                $stmt->execute([
                    OrderStatusConstants::DELIVERED,
                    PaymentConstants::COD,
                    PaymentConstants::VNPAY,
                    PaymentConstants::PAID
                ]);

                $todayRevenue = (float)$stmt->fetchColumn();

                $stmt = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM donhang
                    WHERE TrangThai = ?
                      AND DATE(NgayDat) = CURDATE()
                ");

                $stmt->execute([OrderStatusConstants::DELIVERED]);
                $todayDeliveredOrders = (int)$stmt->fetchColumn();

                $stmt = $pdo->prepare("
                    SELECT COUNT(*)
                    FROM donhang
                    WHERE TrangThai = ?
                ");

                $stmt->execute([OrderStatusConstants::CANCELLED]);
                $cancelledOrders = (int)$stmt->fetchColumn();

                $stmt = $pdo->query("
                    SELECT COUNT(*)
                    FROM sanpham
                    WHERE TrangThai = 1
                      AND SoLuongTon > 0
                      AND SoLuongTon <= 5
                ");

                $lowStockCount = (int)$stmt->fetchColumn();

                $stmt = $pdo->query("
                    SELECT COUNT(*)
                    FROM sanpham
                    WHERE TrangThai = 1
                      AND SoLuongTon <= 0
                ");

                $outOfStockCount = (int)$stmt->fetchColumn();

                $stmt = $pdo->query("
                    SELECT COUNT(*)
                    FROM sanpham
                    WHERE TrangThai = 1
                ");

                $totalActiveProducts = (int)$stmt->fetchColumn();
            }
        } catch (PDOException $e) {
            $countPendingOrders = 0;
            $todayRevenue = 0;
            $lowStockCount = 0;
            $ordersInDelivery = 0;
            $numberOfBlogWaitingApproval = 0;
            $numberOfOrderProcessing = 0;
            $numberOfAssignedOrders = 0;
            $todayDeliveredOrders = 0;
            $cancelledOrders = 0;
            $outOfStockCount = 0;
            $totalActiveProducts = 0;
        }

        $title = "Dashboard";
        $viewContent = BASE_PATH . '/views/admin/dashboard_index.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }
}