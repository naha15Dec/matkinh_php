<?php
class ErrorController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function index() {
        // 1. Lấy thông tin session
        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = "";

        if ($sessionAccount) {
            // Truy vấn lại DB để lấy mã vai trò
            $sql = "SELECT v.MaVaiTro 
                    FROM taikhoan t 
                    JOIN vaitro v ON t.VaiTroId = v.VaiTroId 
                    WHERE t.TaiKhoanId = :id AND t.IsActive = 1 LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['id' => $sessionAccount['TaiKhoanId']]);
            $account = $stmt->fetch();

            if ($account) {
                $roleCode = strtoupper(trim($account['MaVaiTro']));
            }
        }

        // 2. Kiểm tra xem có phải là nhân viên/quản trị không
        $isAdminLike = in_array($roleCode, ['ADMIN', 'STAFF', 'SHIPPER']);

        // 3. NẠP GIAO DIỆN LỖI (Độc lập, không qua layout.php)
        // Vì C# của bạn để Layout = null, nên chúng ta include trực tiếp file view
        // File view này phải có đầy đủ <html>, <head>, <body>
        include BASE_PATH . '/views/client/error.php';
    }
}