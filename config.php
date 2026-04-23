<?php
// 1. Cấu hình các thông số kết nối
define('DB_HOST', 'localhost');
define('DB_PORT', '3307'); 
define('DB_USER', 'root'); 
define('DB_PASS', '');     
define('DB_NAME', 'banmatkinh');

// Khởi tạo kết nối PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Lỗi kết nối Cơ sở dữ liệu: " . $e->getMessage());
}

// 2. Thông số VNPay
define('VNP_TMNCODE', 'PA5Y9318'); 
define('VNP_HASHSECRET', 'SVJ6GFPH8K3AII7K29A2XNTJJCT2R0N4'); 
define('VNP_URL', "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html");

// Lưu ý: Kiểm tra kỹ đường dẫn này có khớp với thư mục XAMPP của bạn không
define('VNP_RETURNURL', "http://localhost/BanMatKinh/public/index.php?controller=thanhtoan&action=vnpay_return");