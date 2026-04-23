<?php
require_once BASE_PATH . '/app/models/AdminRevenueModel.php';

class AdminRevenueController {
    private $model;

    public function __construct($pdo) {
        $this->model = new AdminRevenueModel($pdo);
        // Bảo mật: Chỉ ADMIN mới được xem doanh thu
        if (!isset($_SESSION['LoginInformation']) || strtoupper($_SESSION['LoginInformation']['MaVaiTro']) !== 'ADMIN') {
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index() {
        $data = $this->model->getRevenueStats();
        $title = "Báo cáo doanh thu";
        $viewContent = BASE_PATH . '/views/admin/revenue_index.php';
        require_once BASE_PATH . '/views/admin/layout.php';
    }
}