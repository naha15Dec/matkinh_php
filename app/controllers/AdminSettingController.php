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

        $data = [];
        $data['UpdatedById'] = (int)($_SESSION['LoginInformation']['TaiKhoanId'] ?? 0);
        $data['IsActive'] = isset($_POST['IsActive']) ? 1 : 0;

        $data['TenCuaHang'] = trim($_POST['TenCuaHang'] ?? '');
        $data['Hotline'] = trim($_POST['Hotline'] ?? '');
        $data['Email'] = trim($_POST['Email'] ?? '');
        $data['DiaChi'] = trim($_POST['DiaChi'] ?? '');
        $data['MoTaNgan'] = trim($_POST['MoTaNgan'] ?? '');
        $data['GioiThieu'] = trim($_POST['GioiThieu'] ?? '');
        $data['FacebookUrl'] = trim($_POST['FacebookUrl'] ?? '');
        $data['InstagramUrl'] = trim($_POST['InstagramUrl'] ?? '');
        $data['ZaloUrl'] = trim($_POST['ZaloUrl'] ?? '');

        $error = $this->validateSettingData($data);

        if ($error !== null) {
            $_SESSION['error'] = $error;
            header("Location: index.php?controller=adminsetting");
            exit;
        }

        $data['Logo'] = $currentInfo['Logo'] ?? '';
        $data['Banner'] = $currentInfo['Banner'] ?? '';

        $logoResult = $this->uploadImage('LogoFile', 'logo');

        if (!$logoResult['success']) {
            $_SESSION['error'] = $logoResult['message'];
            header("Location: index.php?controller=adminsetting");
            exit;
        }

        if (!empty($logoResult['file'])) {
            $data['Logo'] = $logoResult['file'];
        }

        $bannerResult = $this->uploadImage('BannerFile', 'banner');

        if (!$bannerResult['success']) {
            $_SESSION['error'] = $bannerResult['message'];
            header("Location: index.php?controller=adminsetting");
            exit;
        }

        if (!empty($bannerResult['file'])) {
            $data['Banner'] = $bannerResult['file'];
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

        $setting = $this->model->getSettingById($id);

        if (!$setting) {
            $_SESSION['error'] = "Không tìm thấy bản ghi cấu hình.";
            header("Location: index.php?controller=adminsetting");
            exit;
        }

        if ((int)($setting['IsActive'] ?? 0) === 1) {
            $_SESSION['error'] = "Không thể xóa cấu hình đang được kích hoạt.";
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

    private function validateSettingData($data)
    {
        if ($data['TenCuaHang'] === '') {
            return "Tên cửa hàng không được để trống.";
        }

        if (mb_strlen($data['TenCuaHang'], 'UTF-8') > 200) {
            return "Tên cửa hàng không được vượt quá 200 ký tự.";
        }

        if (mb_strlen($data['Hotline'], 'UTF-8') > 20) {
            return "Hotline không được vượt quá 20 ký tự.";
        }

        if ($data['Email'] !== '' && !filter_var($data['Email'], FILTER_VALIDATE_EMAIL)) {
            return "Email cửa hàng không đúng định dạng.";
        }

        if (mb_strlen($data['Email'], 'UTF-8') > 100) {
            return "Email không được vượt quá 100 ký tự.";
        }

        if (mb_strlen($data['DiaChi'], 'UTF-8') > 255) {
            return "Địa chỉ không được vượt quá 255 ký tự.";
        }

        if (mb_strlen($data['MoTaNgan'], 'UTF-8') > 500) {
            return "Mô tả ngắn không được vượt quá 500 ký tự.";
        }

        foreach (['FacebookUrl', 'InstagramUrl', 'ZaloUrl'] as $urlField) {
            if ($data[$urlField] !== '' && !filter_var($data[$urlField], FILTER_VALIDATE_URL)) {
                return "Đường dẫn {$urlField} không đúng định dạng URL.";
            }

            if (mb_strlen($data[$urlField], 'UTF-8') > 255) {
                return "Đường dẫn {$urlField} không được vượt quá 255 ký tự.";
            }
        }

        return null;
    }

    private function uploadImage($fileField, $prefix)
    {
        if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] === UPLOAD_ERR_NO_FILE) {
            return [
                'success' => true,
                'file' => null,
                'message' => null
            ];
        }

        if ($_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'file' => null,
                'message' => "Tải ảnh thất bại."
            ];
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

        $originalName = $_FILES[$fileField]['name'] ?? '';
        $tmpName = $_FILES[$fileField]['tmp_name'] ?? '';
        $size = (int)($_FILES[$fileField]['size'] ?? 0);

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExt, true)) {
            return [
                'success' => false,
                'file' => null,
                'message' => "File ảnh chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP."
            ];
        }

        if ($size <= 0 || $size > 3 * 1024 * 1024) {
            return [
                'success' => false,
                'file' => null,
                'message' => "Dung lượng ảnh tối đa là 3MB."
            ];
        }

        $mimeType = function_exists('mime_content_type') ? mime_content_type($tmpName) : '';

        if ($mimeType !== '' && !in_array($mimeType, $allowedMime, true)) {
            return [
                'success' => false,
                'file' => null,
                'message' => "File tải lên không đúng định dạng ảnh."
            ];
        }

        $uploadDir = BASE_PATH . '/public/images/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $safePrefix = preg_replace('/[^a-zA-Z0-9_-]/', '', $prefix);
        $fileName = $safePrefix . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            return [
                'success' => false,
                'file' => null,
                'message' => "Không thể lưu ảnh tải lên."
            ];
        }

        return [
            'success' => true,
            'file' => $fileName,
            'message' => null
        ];
    }
}