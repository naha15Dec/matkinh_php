<?php
require_once BASE_PATH . '/app/models/ProfileModel.php';
require_once BASE_PATH . '/app/models/HomeModel.php';
require_once BASE_PATH . '/app/helpers/HashPassword.php';
require_once BASE_PATH . '/app/helpers/OrderConstants.php';

class ProfileController {
    private $pdo;
    private $model;
    private $homeModel;
    private const PAGE_SIZE = 5;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new ProfileModel($pdo);
        $this->homeModel = new HomeModel($pdo);
    }

    private function checkAuth() {
        if (!isset($_SESSION['LoginInformation'])) {
            $_SESSION['NotificationLogin'] = "Vui lòng đăng nhập để xem thông tin cá nhân.";
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }
        return $_SESSION['LoginInformation'];
    }

    // ========================= TRANG HỒ SƠ & ĐƠN HÀNG =========================
    public function index() {
        $userSession = $this->checkAuth();
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

        $account = $this->model->getAccountById($userSession['TaiKhoanId']);
        $orderResult = $this->model->getOrdersByUser($userSession['TaiKhoanId'], $page, self::PAGE_SIZE);
        
        $listOrderUser = $orderResult['data'];
        $totalCount = $orderResult['totalCount'];
        $totalPages = ceil($totalCount / self::PAGE_SIZE);

        $storeInfo = $this->homeModel->getStoreInfo();
        $title = "Hồ sơ cá nhân - " . ($account['HoTen'] ?? $account['TenDangNhap']);
        $viewContent = BASE_PATH . '/views/client/profile.php';
        
        include BASE_PATH . '/views/client/layout.php';
    }

    // ========================= CẬP NHẬT THÔNG TIN =========================
    public function updateInfo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $userSession = $this->checkAuth();

        $fullName = trim(($_POST['LastName'] ?? '') . ' ' . ($_POST['FirstName'] ?? ''));
        if (empty($fullName)) $fullName = "Người dùng";

        $data = [
            'name' => $fullName,
            'phone' => trim($_POST['Mobile'] ?? ''),
            'address' => trim($_POST['Address'] ?? ''),
            'gender' => $this->parseGender($_POST['Sex'] ?? '')
        ];

        if ($this->model->updateProfile($userSession['TaiKhoanId'], $data)) {
            $_SESSION['ProfileSuccess'] = "Cập nhật thông tin thành công.";
            // Cập nhật lại session để UI đổi tên ngay lập tức
            $_SESSION['LoginInformation'] = $this->model->getAccountById($userSession['TaiKhoanId']);
        }

        header("Location: index.php?controller=profile");
        exit;
    }

    // ========================= ĐỔI MẬT KHẨU (ĐÃ SỬA SANG BCRYPT) =========================
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $userSession = $this->checkAuth();
        
        // Lấy lại dữ liệu mới nhất từ DB để có MatKhauHash chuẩn
        $account = $this->model->getAccountById($userSession['TaiKhoanId']);

        $currentPass = $_POST['passwdCurrent'] ?? '';
        $newPass = $_POST['PassWord'] ?? '';

        // SỬA LỖI: Dùng password_verify thay vì SHA512
        if (!HashPassword::verify($currentPass, $account['MatKhauHash'])) {
            $_SESSION['PasswordError'] = "Mật khẩu hiện tại không đúng.";
        } elseif (strlen($newPass) < 6) {
            $_SESSION['PasswordError'] = "Mật khẩu mới phải từ 6 ký tự.";
        } else {
            // SỬA LỖI: Dùng HashPassword::hash (Bcrypt)
            $newHash = HashPassword::hash($newPass);
            $this->model->updatePassword($userSession['TaiKhoanId'], $newHash);
            $_SESSION['ProfileSuccess'] = "Đổi mật khẩu thành công.";
        }

        header("Location: index.php?controller=profile");
        exit;
    }

    // ========================= CHI TIẾT ĐƠN HÀNG =========================
    public function orderDetail() {
        $userSession = $this->checkAuth();
        $maDonHang = $_GET['maDonHang'] ?? '';
        
        $order = $this->model->getOrderDetail($maDonHang, $userSession['TaiKhoanId']);
        
        if (!$order) {
            $_SESSION['error'] = "Không tìm thấy đơn hàng.";
            header("Location: index.php?controller=profile");
            exit;
        }

        // QUAN TRỌNG: Lấy danh sách sản phẩm trong đơn hàng này
        $order['items'] = $this->model->getOrderItems($order['DonHangId']);

        $storeInfo = $this->homeModel->getStoreInfo();
        $title = "Chi tiết đơn hàng " . $maDonHang;
        $viewContent = BASE_PATH . '/views/client/order_detail.php';
        
        include BASE_PATH . '/views/client/layout.php';
    }

    private function parseGender($sex) {
        $v = strtolower(trim($sex));
        if ($v == 'nam') return 1;
        if ($v == 'nữ' || $v == 'nu') return 0;
        return null;
    }
}