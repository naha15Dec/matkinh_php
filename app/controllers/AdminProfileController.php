<?php
require_once BASE_PATH . '/app/models/AdminProfileModel.php';
require_once BASE_PATH . '/app/helpers/HashPassword.php';

class AdminProfileController
{
    private $model;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->model = new AdminProfileModel($pdo);

        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if (!$sessionAccount) {
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }

        if (!in_array($roleCode, ['ADMIN', 'STAFF', 'SHIPPER'], true)) {
            $_SESSION['error'] = "Bạn không có quyền truy cập khu vực quản trị.";
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index()
    {
        $userId = (int)($_SESSION['LoginInformation']['TaiKhoanId'] ?? 0);

        if ($userId <= 0) {
            $_SESSION['error'] = "Phiên đăng nhập không hợp lệ.";
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }

        $user = $this->model->getAccountById($userId);

        if (!$user) {
            $_SESSION['error'] = "Không tìm thấy thông tin tài khoản.";
            header("Location: index.php?controller=dashboard");
            exit;
        }

        if (empty($user['IsActive'])) {
            unset($_SESSION['LoginInformation']);
            $_SESSION['error'] = "Tài khoản của bạn đã bị khóa.";
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $displayName = $sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Tài khoản';
        $isAdmin = $roleCode === 'ADMIN';
        $isStaff = $roleCode === 'STAFF';
        $isShipper = $roleCode === 'SHIPPER';

        $title = "Thông tin cá nhân";
        $viewContent = BASE_PATH . '/views/admin/profile_index.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        $userId = (int)($_SESSION['LoginInformation']['TaiKhoanId'] ?? 0);

        if ($userId <= 0) {
            $_SESSION['error'] = "Phiên đăng nhập không hợp lệ.";
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }

        $user = $this->model->getAccountById($userId);

        if (!$user) {
            $_SESSION['error'] = "Không tìm thấy tài khoản.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        $lastName = trim($_POST['LastName'] ?? '');
        $firstName = trim($_POST['FirstName'] ?? '');
        $fullName = trim($lastName . ' ' . $firstName);

        $email = trim($_POST['Email'] ?? '');
        $phone = trim($_POST['Mobile'] ?? '');
        $address = trim($_POST['Address'] ?? '');
        $gender = $this->parseGender($_POST['Sex'] ?? '');
        $birthday = trim($_POST['NgaySinh'] ?? '');

        $email = $email !== '' ? $email : null;
        $phone = $phone !== '' ? $phone : null;
        $address = $address !== '' ? $address : null;
        $birthday = $birthday !== '' ? $birthday : null;

        if ($fullName === '') {
            $_SESSION['error'] = "Họ tên không được để trống.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Email không đúng định dạng.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        if ($email !== null && $this->model->isEmailExists($email, $userId)) {
            $_SESSION['error'] = "Email đã được sử dụng bởi tài khoản khác.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        if ($phone !== null && $this->model->isPhoneExists($phone, $userId)) {
            $_SESSION['error'] = "Số điện thoại đã được sử dụng bởi tài khoản khác.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        if ($birthday !== null && !$this->isValidDate($birthday)) {
            $_SESSION['error'] = "Ngày sinh không hợp lệ.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        $data = [
            'HoTen' => $fullName,
            'Email' => $email,
            'SoDienThoai' => $phone,
            'GioiTinh' => $gender,
            'NgaySinh' => $birthday,
            'DiaChi' => $address
        ];

        if ($this->model->updateInfo($userId, $data)) {
            $updatedUser = $this->model->getAccountById($userId);

            if ($updatedUser) {
                $_SESSION['LoginInformation'] = $updatedUser;
            }

            $_SESSION['success'] = "Cập nhật thông tin cá nhân thành công.";
        } else {
            $_SESSION['error'] = "Thông tin không thay đổi hoặc cập nhật thất bại.";
        }

        header("Location: index.php?controller=adminprofile");
        exit;
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        $userId = (int)($_SESSION['LoginInformation']['TaiKhoanId'] ?? 0);

        if ($userId <= 0) {
            $_SESSION['error'] = "Phiên đăng nhập không hợp lệ.";
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }

        $currentPass = $_POST['CurrentPassword'] ?? '';
        $newPass = $_POST['NewPassword'] ?? '';
        $confirmPass = $_POST['ConfirmPassword'] ?? '';

        if ($currentPass === '') {
            $_SESSION['error'] = "Vui lòng nhập mật khẩu hiện tại.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        if ($newPass === '' || strlen($newPass) < 6) {
            $_SESSION['error'] = "Mật khẩu mới phải có ít nhất 6 ký tự.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        if ($newPass !== $confirmPass) {
            $_SESSION['error'] = "Mật khẩu xác nhận không khớp.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        $user = $this->model->getAccountById($userId);

        if (!$user || empty($user['MatKhauHash'])) {
            $_SESSION['error'] = "Không tìm thấy tài khoản.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        if (!HashPassword::verify($currentPass, $user['MatKhauHash'])) {
            $_SESSION['error'] = "Mật khẩu hiện tại không chính xác.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        if (HashPassword::verify($newPass, $user['MatKhauHash'])) {
            $_SESSION['error'] = "Mật khẩu mới không được trùng với mật khẩu hiện tại.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        $newHashed = HashPassword::hash($newPass);

        if ($this->model->updatePassword($userId, $newHashed)) {
            $_SESSION['success'] = "Đổi mật khẩu thành công.";
        } else {
            $_SESSION['error'] = "Đổi mật khẩu thất bại.";
        }

        header("Location: index.php?controller=adminprofile");
        exit;
    }

    private function parseGender($value)
    {
        $value = strtolower(trim((string)$value));

        if ($value === '') {
            return null;
        }

        if (in_array($value, ['1', 'true', 'nam', 'male'], true)) {
            return 1;
        }

        if (in_array($value, ['0', 'false', 'nữ', 'nu', 'female'], true)) {
            return 0;
        }

        return null;
    }

    private function isValidDate($date)
    {
        $dt = DateTime::createFromFormat('Y-m-d', $date);

        return $dt && $dt->format('Y-m-d') === $date;
    }
}