<?php
require_once BASE_PATH . '/app/models/AdminSettingModel.php';

class AdminSettingController
{
    private $model;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new AdminSettingModel($pdo);

        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if (!$sessionAccount || $roleCode !== 'ADMIN') {
            $_SESSION['error'] = "Chỉ Quản trị viên mới có quyền truy cập cài đặt hệ thống.";
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index()
    {
        $pdo = $this->pdo;

        $currentInfo = $this->model->getLatestSetting();
        $history = $this->model->getHistory();

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $displayName = $sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Tài khoản';
        $isAdmin = $roleCode === 'ADMIN';
        $isStaff = $roleCode === 'STAFF';
        $isShipper = $roleCode === 'SHIPPER';

        $title = "Cài đặt Website";
        $viewContent = BASE_PATH . '/views/admin/setting_index.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=adminsetting");
            exit;
        }

        $currentInfo = $this->model->getLatestSetting();

        $data = $_POST;
        $data['UpdatedById'] = (int)($_SESSION['LoginInformation']['TaiKhoanId'] ?? 0);
        $data['IsActive'] = isset($_POST['IsActive']) ? 1 : 0;

        $data['TenCuaHang'] = trim($data['TenCuaHang'] ?? '');
        $data['Hotline'] = trim($data['Hotline'] ?? '');
        $data['Email'] = trim($data['Email'] ?? '');
        $data['DiaChi'] = trim($data['DiaChi'] ?? '');
        $data['MoTaNgan'] = trim($data['MoTaNgan'] ?? '');
        $data['GioiThieu'] = trim($data['GioiThieu'] ?? '');
        $data['FacebookUrl'] = trim($data['FacebookUrl'] ?? '');
        $data['InstagramUrl'] = trim($data['InstagramUrl'] ?? '');
        $data['ZaloUrl'] = trim($data['ZaloUrl'] ?? '');

        if ($data['TenCuaHang'] === '') {
            $_SESSION['error'] = "Tên cửa hàng không được để trống.";
            header("Location: index.php?controller=adminsetting");
            exit;
        }

        $data['Logo'] = $currentInfo['Logo'] ?? '';
        $data['Banner'] = $currentInfo['Banner'] ?? '';

        $newLogo = $this->uploadImage('LogoFile', 'logo');
        if ($newLogo) {
            $data['Logo'] = $newLogo;
        }

        $newBanner = $this->uploadImage('BannerFile', 'banner');
        if ($newBanner) {
            $data['Banner'] = $newBanner;
        }

        if ($this->model->saveSetting($data)) {
            $_SESSION['success'] = "Đã cập nhật cấu hình hệ thống.";
        } else {
            $_SESSION['error'] = "Cập nhật cấu hình thất bại.";
        }

        header("Location: index.php?controller=adminsetting");
        exit;
    }

    public function deleteHistory()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=adminsetting");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = "Bản ghi lịch sử không hợp lệ.";
            header("Location: index.php?controller=adminsetting");
            exit;
        }

        if ($this->model->deleteHistory($id)) {
            $_SESSION['success'] = "Đã xóa bản ghi lịch sử thành công.";
        } else {
            $_SESSION['error'] = "Không thể xóa bản ghi này.";
        }

        header("Location: index.php?controller=adminsetting");
        exit;
    }

    private function uploadImage($fileField, $prefix)
    {
        if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $originalName = $_FILES[$fileField]['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExt)) {
            $_SESSION['error'] = "File ảnh chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP.";
            return null;
        }

        $uploadDir = BASE_PATH . '/public/images/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = $prefix . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES[$fileField]['tmp_name'], $targetPath)) {
            return $fileName;
        }

        return null;
    }
}