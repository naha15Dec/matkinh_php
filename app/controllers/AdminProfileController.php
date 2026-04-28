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

        if (!isset($_SESSION['LoginInformation'])) {
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }
    }

    public function index()
    {
        $userId = $_SESSION['LoginInformation']['TaiKhoanId'];

        $user = $this->model->getAccountById($userId);

        if (!$user) {
            $_SESSION['error'] = "Không tìm thấy thông tin tài khoản.";
            header("Location: index.php?controller=dashboard");
            exit;
        }

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

        $userId = $_SESSION['LoginInformation']['TaiKhoanId'];

        $lastName = trim($_POST['LastName'] ?? '');
        $firstName = trim($_POST['FirstName'] ?? '');
        $fullName = trim($lastName . ' ' . $firstName);

        $data = [
            'HoTen' => $fullName,
            'Email' => trim($_POST['Email'] ?? ''),
            'SoDienThoai' => trim($_POST['Mobile'] ?? ''),
            'GioiTinh' => $_POST['Sex'] ?? null,
            'NgaySinh' => $_POST['NgaySinh'] ?? null,
            'DiaChi' => trim($_POST['Address'] ?? '')
        ];

        if ($fullName === '') {
            $_SESSION['error'] = "Họ tên không được để trống.";
            header("Location: index.php?controller=adminprofile");
            exit;
        }

        if ($this->model->updateInfo($userId, $data)) {
            $_SESSION['LoginInformation']['HoTen'] = $fullName;
            $_SESSION['LoginInformation']['Email'] = $data['Email'];
            $_SESSION['LoginInformation']['SoDienThoai'] = $data['SoDienThoai'];
            $_SESSION['LoginInformation']['GioiTinh'] = $data['GioiTinh'];
            $_SESSION['LoginInformation']['NgaySinh'] = $data['NgaySinh'];
            $_SESSION['LoginInformation']['DiaChi'] = $data['DiaChi'];

            $_SESSION['success'] = "Cập nhật thành công!";
        } else {
            $_SESSION['error'] = "Cập nhật thất bại.";
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

        $userId = $_SESSION['LoginInformation']['TaiKhoanId'];

        $currentPass = $_POST['CurrentPassword'] ?? '';
        $newPass = $_POST['NewPassword'] ?? '';
        $confirmPass = $_POST['ConfirmPassword'] ?? '';

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
        } else {
            $newHashed = HashPassword::hash($newPass);

            if ($this->model->updatePassword($userId, $newHashed)) {
                $_SESSION['success'] = "Đổi mật khẩu thành công.";
            } else {
                $_SESSION['error'] = "Đổi mật khẩu thất bại.";
            }
        }

        header("Location: index.php?controller=adminprofile");
        exit;
    }
}