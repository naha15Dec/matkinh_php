<?php
// Nếu biến $pdo không tồn tại cục bộ, thử lấy từ biến toàn cục hoặc class
if (!isset($pdo)) {
    global $pdo; 
}

// Nếu vẫn null (do Controller không truyền), báo lỗi rõ ràng thay vì văng Fatal Error
if (!$pdo) {
    die("Lỗi hệ thống: Kết nối cơ sở dữ liệu (PDO) bị mất tại layout_data.php. Vui lòng kiểm tra lại Controller.");
}

// 1. Lấy thông tin tài khoản từ Session
$sessionAccount = $_SESSION['LoginInformation'] ?? null;
$isLoggedIn = $sessionAccount !== null;

// Kiểm tra quyền truy cập Admin cơ bản
$roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));
if (!$isLoggedIn || !in_array($roleCode, ['ADMIN', 'STAFF', 'SHIPPER'])) {
    $_SESSION['error'] = "Bạn không có quyền truy cập khu vực này.";
    header("Location: ../index.php?controller=taikhoan&action=login");
    exit();
}

// 2. Khởi tạo các số liệu thông báo (Logic từ C#)
$numberOfBlogWaitingApproval = 0;
$numberOfOrderProcessing = 0;
$numberOfAssignedOrders = 0;

// Đếm bài viết chờ duyệt (ADMIN)
if ($roleCode === 'ADMIN') {
    $stmt = $pdo->query("SELECT COUNT(*) FROM baiviet WHERE TrangThai = 0"); // Giả sử 0 là Draft
    $numberOfBlogWaitingApproval = $stmt->fetchColumn();
}

// Đếm đơn hàng vận hành (ADMIN & STAFF)
if (in_array($roleCode, ['ADMIN', 'STAFF'])) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM donhang WHERE TrangThai NOT IN (4, 5)"); // 4: Delivered, 5: Cancelled
    $numberOfOrderProcessing = $stmt->fetchColumn();
}

// Đếm đơn được giao (SHIPPER)
if ($roleCode === 'SHIPPER') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM donhang WHERE ShipperId = ? AND TrangThai NOT IN (4, 5)");
    $stmt->execute([$sessionAccount['TaiKhoanId']]);
    $numberOfAssignedOrders = $stmt->fetchColumn();
}

// Thống kê bổ sung cho Dashboard
$totalProducts = 0;
if (in_array($roleCode, ['ADMIN', 'STAFF'])) {
    // Đếm tổng sản phẩm
    $totalProducts = $pdo->query("SELECT COUNT(*) FROM sanpham")->fetchColumn();
}

// Nếu là ADMIN thì tính thêm doanh thu (Trạng thái 4 = Hoàn tất)
$totalRevenue = 0;
if ($roleCode === 'ADMIN') {
    $totalRevenue = $pdo->query("SELECT SUM(TongThanhToan) FROM donhang WHERE TrangThai = 4")->fetchColumn() ?? 0;
}

$displayName = !empty($sessionAccount['HoTen']) ? $sessionAccount['HoTen'] : $sessionAccount['TenDangNhap'];
$roleName = $sessionAccount['TenVaiTro'] ?? 'Nhân viên';
$avatar = !empty($sessionAccount['AnhDaiDien']) 
    ? "/BanMatKinh/public/images/" . $sessionAccount['AnhDaiDien'] 
    : "/BanMatKinh/public/assets/img/image_Account.jpg";
?>