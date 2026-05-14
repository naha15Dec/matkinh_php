<?php
require_once BASE_PATH . '/app/models/ProfileModel.php';
require_once BASE_PATH . '/app/models/HomeModel.php';
require_once BASE_PATH . '/app/helpers/HashPassword.php';
require_once BASE_PATH . '/app/helpers/OrderConstants.php';

class ProfileController
{
    private $pdo;
    private $model;
    private $homeModel;
    private const PAGE_SIZE = 5;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new ProfileModel($pdo);
        $this->homeModel = new HomeModel($pdo);
    }

    private function checkAuth()
    {
        if (!isset($_SESSION['LoginInformation'])) {
            $_SESSION['NotificationLogin'] = "Vui lòng đăng nhập để xem thông tin cá nhân.";
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }

        return $_SESSION['LoginInformation'];
    }

    public function index()
    {
        $userSession = $this->checkAuth();

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

        $account = $this->model->getAccountById($userSession['TaiKhoanId']);

        if (!$account || empty($account['IsActive'])) {
            unset($_SESSION['LoginInformation']);
            $_SESSION['NotificationLogin'] = "Tài khoản không tồn tại hoặc đã bị khóa.";
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }

        $orderResult = $this->model->getOrdersByUser(
            (int)$userSession['TaiKhoanId'],
            $page,
            self::PAGE_SIZE
        );

        $listOrderUser = $orderResult['data'];
        $totalCount = $orderResult['totalCount'];
        $totalPages = max(1, (int)ceil($totalCount / self::PAGE_SIZE));

        $storeInfo = $this->homeModel->getStoreInfo();
        $title = "Hồ sơ cá nhân - " . ($account['HoTen'] ?? $account['TenDangNhap']);
        $viewContent = BASE_PATH . '/views/client/profile.php';

        include BASE_PATH . '/views/client/layout.php';
    }

    public function updateInfo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=profile");
            exit;
        }

        $userSession = $this->checkAuth();
        $userId = (int)$userSession['TaiKhoanId'];

        $lastName = trim($_POST['LastName'] ?? '');
        $firstName = trim($_POST['FirstName'] ?? '');
        $fullName = trim($lastName . ' ' . $firstName);

        if ($fullName === '') {
            $fullName = "Người dùng";
        }

        $phone = trim($_POST['Mobile'] ?? '');
        $address = trim($_POST['Address'] ?? '');
        $gender = $this->parseGender($_POST['Sex'] ?? '');

        $phone = $phone !== '' ? $phone : null;
        $address = $address !== '' ? $address : null;

        if ($phone !== null && $this->model->isPhoneExists($phone, $userId)) {
            $_SESSION['ProfileError'] = "Số điện thoại đã được sử dụng bởi tài khoản khác.";
            header("Location: index.php?controller=profile");
            exit;
        }

        $data = [
            'name' => $fullName,
            'phone' => $phone,
            'address' => $address,
            'gender' => $gender
        ];

        if ($this->model->updateProfile($userId, $data)) {
            $_SESSION['ProfileSuccess'] = "Cập nhật thông tin thành công.";
            $_SESSION['LoginInformation'] = $this->model->getAccountById($userId);
        } else {
            $_SESSION['ProfileError'] = "Thông tin không thay đổi hoặc cập nhật thất bại.";
        }

        header("Location: index.php?controller=profile");
        exit;
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=profile");
            exit;
        }

        $userSession = $this->checkAuth();
        $userId = (int)$userSession['TaiKhoanId'];

        $account = $this->model->getAccountById($userId);

        if (!$account) {
            $_SESSION['PasswordError'] = "Không tìm thấy tài khoản.";
            header("Location: index.php?controller=profile");
            exit;
        }

        $currentPass = $_POST['passwdCurrent'] ?? '';
        $newPass = $_POST['PassWord'] ?? '';
        $confirmPass = $_POST['ConfirmPassWord'] ?? ($_POST['ConfirmPassword'] ?? '');

        if (!HashPassword::verify($currentPass, $account['MatKhauHash'])) {
            $_SESSION['PasswordError'] = "Mật khẩu hiện tại không đúng.";
        } elseif (strlen($newPass) < 6) {
            $_SESSION['PasswordError'] = "Mật khẩu mới phải từ 6 ký tự.";
        } elseif ($newPass !== $confirmPass) {
            $_SESSION['PasswordError'] = "Xác nhận mật khẩu mới không khớp.";
        } else {
            $newHash = HashPassword::hash($newPass);

            if ($this->model->updatePassword($userId, $newHash)) {
                $_SESSION['ProfileSuccess'] = "Đổi mật khẩu thành công.";
            } else {
                $_SESSION['PasswordError'] = "Đổi mật khẩu thất bại hoặc dữ liệu không thay đổi.";
            }
        }

        header("Location: index.php?controller=profile");
        exit;
    }

    public function orderDetail()
    {
        $userSession = $this->checkAuth();

        $maDonHang = trim($_GET['maDonHang'] ?? '');

        if ($maDonHang === '') {
            $_SESSION['error'] = "Mã đơn hàng không hợp lệ.";
            header("Location: index.php?controller=profile");
            exit;
        }

        $order = $this->model->getOrderDetail($maDonHang, (int)$userSession['TaiKhoanId']);

        if (!$order) {
            $_SESSION['error'] = "Không tìm thấy đơn hàng.";
            header("Location: index.php?controller=profile");
            exit;
        }

        $storeInfo = $this->homeModel->getStoreInfo();
        $title = "Chi tiết đơn hàng " . $maDonHang;
        $viewContent = BASE_PATH . '/views/client/order_detail.php';

        include BASE_PATH . '/views/client/layout.php';
    }

    public function cancelOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=profile");
            exit;
        }

        $userSession = $this->checkAuth();
        $userId = (int)$userSession['TaiKhoanId'];

        $maDonHang = trim($_POST['MaDonHang'] ?? '');

        if ($maDonHang === '') {
            $_SESSION['error'] = "Mã đơn hàng không hợp lệ.";
            header("Location: index.php?controller=profile");
            exit;
        }

        $order = $this->model->getOrderDetail($maDonHang, $userId);

        if (!$order) {
            $_SESSION['error'] = "Không tìm thấy đơn hàng.";
            header("Location: index.php?controller=profile");
            exit;
        }

        $currentStatus = (int)($order['TrangThai'] ?? 0);

        if ($currentStatus !== OrderStatusConstants::PENDING) {
            $_SESSION['error'] = "Chỉ có thể hủy đơn hàng khi đơn đang ở trạng thái chờ xác nhận.";
            header("Location: index.php?controller=profile&action=orderDetail&maDonHang=" . urlencode($maDonHang));
            exit;
        }

        $paymentStatus = $order['TrangThaiThanhToan'] ?? PaymentConstants::PENDING;

        if (strtoupper($paymentStatus) === strtoupper(PaymentConstants::PAID)) {
            $_SESSION['error'] = "Đơn hàng đã thanh toán, vui lòng liên hệ cửa hàng để được hỗ trợ hủy và hoàn tiền.";
            header("Location: index.php?controller=profile&action=orderDetail&maDonHang=" . urlencode($maDonHang));
            exit;
        }

        $result = $this->model->cancelPendingOrder(
            (int)$order['DonHangId'],
            $userId,
            "Khách hàng tự hủy đơn khi đơn đang chờ xác nhận."
        );

        if ($result) {
            $_SESSION['ProfileSuccess'] = "Đã hủy đơn hàng thành công.";
        } else {
            $_SESSION['error'] = "Hủy đơn hàng thất bại hoặc đơn hàng không còn ở trạng thái chờ xác nhận.";
        }

        header("Location: index.php?controller=profile&action=orderDetail&maDonHang=" . urlencode($maDonHang));
        exit;
    }

    private function parseGender($sex)
    {
        $v = strtolower(trim($sex));

        if ($v === 'nam') {
            return 1;
        }

        if ($v === 'nữ' || $v === 'nu') {
            return 0;
        }

        return null;
    }
}