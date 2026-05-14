<?php
require_once BASE_PATH . '/app/models/AdminRevenueModel.php';
require_once BASE_PATH . '/app/helpers/OrderConstants.php';

class AdminRevenueController
{
    private $model;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new AdminRevenueModel($pdo);

        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if (!$sessionAccount || $roleCode !== 'ADMIN') {
            $_SESSION['error'] = "Chỉ Quản trị viên mới có quyền xem báo cáo doanh thu.";
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index()
    {
        $pdo = $this->pdo;

        $fromDate = trim($_GET['fromDate'] ?? '');
        $toDate = trim($_GET['toDate'] ?? '');

        if (!$this->isValidDate($fromDate)) {
            $fromDate = '';
        }

        if (!$this->isValidDate($toDate)) {
            $toDate = '';
        }

        $data = $this->model->getRevenueStats($fromDate, $toDate);
        $dailyRevenue = $this->model->getDailyRevenueLast7Days();
        $monthlyRevenue = $this->model->getMonthlyRevenueCurrentYear();
        $topProducts = $this->model->getTopSellingProducts(5);

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $displayName = $sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Tài khoản';
        $isAdmin = $roleCode === 'ADMIN';
        $isStaff = $roleCode === 'STAFF';
        $isShipper = $roleCode === 'SHIPPER';

        $title = "Báo cáo doanh thu";
        $viewContent = BASE_PATH . '/views/admin/revenue_index.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }

    private function isValidDate($date)
    {
        if ($date === '') {
            return false;
        }

        $dt = DateTime::createFromFormat('Y-m-d', $date);

        return $dt && $dt->format('Y-m-d') === $date;
    }
}