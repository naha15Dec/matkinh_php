<?php
require_once BASE_PATH . '/app/helpers/OrderConstants.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($pdo)) {
    global $pdo;
}

if (!$pdo) {
    die("Lỗi hệ thống: Kết nối cơ sở dữ liệu PDO bị mất tại layout_data.php.");
}

$sessionAccount = $_SESSION['LoginInformation'] ?? null;
$isLoggedIn = $sessionAccount !== null;

$roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

if (!$isLoggedIn || !in_array($roleCode, ['ADMIN', 'STAFF', 'SHIPPER'], true)) {
    $_SESSION['error'] = "Bạn không có quyền truy cập khu vực quản trị.";
    header("Location: /BanMatKinh/public/index.php?controller=taikhoan&action=login");
    exit();
}

$isAdmin = $roleCode === 'ADMIN';
$isStaff = $roleCode === 'STAFF';
$isShipper = $roleCode === 'SHIPPER';

$numberOfBlogWaitingApproval = 0;
$numberOfOrderProcessing = 0;
$numberOfAssignedOrders = 0;

$totalProducts = 0;
$totalRevenue = 0;

try {
    if ($isAdmin) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM baiviet
            WHERE TrangThai = ?
        ");
        $stmt->execute([0]);
        $numberOfBlogWaitingApproval = (int)$stmt->fetchColumn();
    }

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

        $stmt = $pdo->query("
            SELECT COUNT(*)
            FROM sanpham
            WHERE TrangThai = 1
        ");

        $totalProducts = (int)$stmt->fetchColumn();
    }

    if ($isShipper) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM donhang
            WHERE ShipperId = ?
              AND TrangThai IN (?, ?, ?)
        ");

        $stmt->execute([
            (int)($sessionAccount['TaiKhoanId'] ?? 0),
            OrderStatusConstants::ASSIGNED_TO_SHIPPER,
            OrderStatusConstants::DELIVERING,
            OrderStatusConstants::DELIVERY_FAILED
        ]);

        $numberOfAssignedOrders = (int)$stmt->fetchColumn();
    }

    if ($isAdmin) {
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
        ");

        $stmt->execute([
            OrderStatusConstants::DELIVERED,
            PaymentConstants::COD,
            PaymentConstants::VNPAY,
            PaymentConstants::PAID
        ]);

        $totalRevenue = (float)$stmt->fetchColumn();
    }
} catch (PDOException $e) {
    $numberOfBlogWaitingApproval = 0;
    $numberOfOrderProcessing = 0;
    $numberOfAssignedOrders = 0;
    $totalProducts = 0;
    $totalRevenue = 0;
}

$displayName = !empty($sessionAccount['HoTen'])
    ? $sessionAccount['HoTen']
    : ($sessionAccount['TenDangNhap'] ?? 'Tài khoản');

$displayUsername = $sessionAccount['TenDangNhap'] ?? '';
$roleName = $sessionAccount['TenVaiTro'] ?? $roleCode;

$rawAvatar = $sessionAccount['AnhDaiDien'] ?? '';

$avatarText = strtoupper(mb_substr($displayName ?: 'A', 0, 1, 'UTF-8'));

if (!empty($rawAvatar)) {
    if (preg_match('/^https?:\/\//i', $rawAvatar) || str_starts_with($rawAvatar, '/')) {
        $avatar = $rawAvatar;
    } else {
        $avatar = '/BanMatKinh/public/images/' . ltrim($rawAvatar, '/');
    }
} else {
    $avatar = '/BanMatKinh/public/images/admin/default-avatar.png';
}

$baseUrl = '/BanMatKinh/public';