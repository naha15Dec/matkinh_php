<?php
require_once BASE_PATH . '/app/models/AdminProfileModel.php';
// Giả sử class HashPassword bạn gửi nằm ở folder utils hoặc lib
require_once BASE_PATH . '/app/helpers/HashPassword.php'; 

class AdminProfileController {
    private $model;

    public function __construct($pdo) {
        $this->model = new AdminProfileModel($pdo);
        if (!isset($_SESSION['LoginInformation'])) {
            header("Location: index.php?controller=account&action=login");
            exit;
        }
    }

    public function index() {
        $userId = $_SESSION['LoginInformation']['TaiKhoanId'];
        $user = $this->model->getAccountById($userId);
        
        $title = "Thông tin cá nhân";
        $viewContent = BASE_PATH . '/views/admin/profile_index.php';
        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function update() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_SESSION['LoginInformation']['TaiKhoanId'];
        
        // Ghép Họ và Tên thành một chuỗi duy nhất
        $fullName = trim($_POST['LastName'] . ' ' . $_POST['FirstName']);
        
        // Chuẩn bị mảng dữ liệu khớp với tên cột trong DB hoặc tham số Model
        $data = [
            'HoTen' => $fullName,
            'Email' => $_POST['Email'],
            'SoDienThoai' => $_POST['Mobile'], // Đổi từ Mobile -> SoDienThoai
            'GioiTinh' => $_POST['Sex'],       // Đổi từ Sex -> GioiTinh
            'NgaySinh' => $_POST['NgaySinh'], 
            'DiaChi' => $_POST['Address']      // Đổi từ Address -> DiaChi
        ];

        if ($this->model->updateInfo($userId, $data)) {
            $_SESSION['LoginInformation']['HoTen'] = $fullName;
            $_SESSION['success'] = "Cập nhật thành công!";
        }
        header("Location: index.php?controller=adminprofile");
        exit;
    }
}

    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['LoginInformation']['TaiKhoanId'];
            $currentPass = $_POST['CurrentPassword'] ?? '';
            $newPass = $_POST['NewPassword'] ?? '';
            $confirmPass = $_POST['ConfirmPassword'] ?? '';

            if ($newPass !== $confirmPass) {
                $_SESSION['error'] = "Mật khẩu xác nhận không khớp.";
                header("Location: index.php?controller=adminprofile");
                exit;
            }

            $user = $this->model->getAccountById($userId);

            // SỬ DỤNG CLASS HASH BẠN GỬI
            if (!HashPassword::verify($currentPass, $user['MatKhauHash'])) {
                $_SESSION['error'] = "Mật khẩu hiện tại không chính xác.";
            } else {
                $newHashed = HashPassword::hash($newPass);
                $this->model->updatePassword($userId, $newHashed);
                $_SESSION['success'] = "Đổi mật khẩu thành công.";
            }
            header("Location: index.php?controller=adminprofile");
            exit;
        }
    }
}