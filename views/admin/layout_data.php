<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($baseUrl === '\\' || $baseUrl === '/') {
    $baseUrl = '';
}

if (!isset($pdo)) {
    global $pdo;
}

if (!$pdo) {
    die("Lỗi hệ thống: Kết nối cơ sở dữ liệu (PDO) bị mất tại layout_data.php. Vui lòng kiểm tra lại Controller.");
}

$sessionAccount = $_SESSION['LoginInformation'] ?? null;
$isLoggedIn = $sessionAccount !== null;

$roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

if (!$isLoggedIn || !in_array($roleCode, ['ADMIN', 'STAFF', 'SHIPPER'])) {
    $_SESSION['error'] = "Bạn không có quyền truy cập khu vực này.";
    header("Location: {$baseUrl}/index.php?controller=taikhoan&action=login");
    exit();
}

$numberOfBlogWaitingApproval = 0;
$numberOfOrderProcessing = 0;
$numberOfAssignedOrders = 0;

$totalProducts = 0;
$totalRevenue = 0;

try {
    if ($roleCode === 'ADMIN') {
        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM baiviet 
            WHERE TrangThai = 0
        ");
        $numberOfBlogWaitingApproval = (int)$stmt->fetchColumn();
    }

    if (in_array($roleCode, ['ADMIN', 'STAFF'])) {
        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM donhang 
            WHERE TrangThai NOT IN (4, 5)
        ");
        $numberOfOrderProcessing = (int)$stmt->fetchColumn();

        $stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM sanpham
        ");
        $totalProducts = (int)$stmt->fetchColumn();
    }

    if ($roleCode === 'SHIPPER') {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM donhang 
            WHERE ShipperId = ? 
            AND TrangThai NOT IN (4, 5)
        ");
        $stmt->execute([$sessionAccount['TaiKhoanId'] ?? 0]);
        $numberOfAssignedOrders = (int)$stmt->fetchColumn();
    }

    if ($roleCode === 'ADMIN') {
        $stmt = $pdo->query("
            SELECT COALESCE(SUM(TongThanhToan), 0) 
            FROM donhang 
            WHERE TrangThai = 4
        ");
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
$roleName = $sessionAccount['TenVaiTro'] ?? 'Nhân viên';

$rawAvatar = $sessionAccount['AnhDaiDien'] ?? '';

if (!empty($rawAvatar)) {
    if (str_starts_with($rawAvatar, 'http') || str_starts_with($rawAvatar, '/')) {
        $avatar = $rawAvatar;
    } else {
        $avatar = $baseUrl . "/images/" . ltrim($rawAvatar, '/');
    }
} else {
    $avatar = $baseUrl . "/assets/img/image_Account.jpg";
}
?>