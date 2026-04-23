<?php
class DashboardController {
    private $pdo; // Khai báo thuộc tính để lưu kết nối

    public function __construct($pdo) {
        $this->pdo = $pdo; // Nhận biến $pdo từ router truyền vào

        if (!isset($_SESSION['LoginInformation'])) {
            header("Location: index.php?controller=taikhoan&action=login");
            exit();
        }
        // ... giữ nguyên phần check role ...
    }

    public function index() {
        // ĐƯA BIẾN $pdo RA PHẠM VI CỦA VIEW
        $pdo = $this->pdo; 

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));
        $isAdmin = ($roleCode === 'ADMIN');
        $isStaff = ($roleCode === 'STAFF');
        $isShipper = ($roleCode === 'SHIPPER');

        $title = "Dashboard";
        $viewContent = BASE_PATH . '/views/admin/dashboard_index.php'; 
        require_once BASE_PATH . '/views/admin/layout.php';
    }
}