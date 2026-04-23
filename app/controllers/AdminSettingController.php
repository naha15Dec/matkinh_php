<?php
require_once BASE_PATH . '/app/models/AdminSettingModel.php';

class AdminSettingController {
    private $model;

    public function __construct($pdo) {
        $this->model = new AdminSettingModel($pdo);
        // Chỉ ADMIN mới được vào cài đặt hệ thống
        if (!isset($_SESSION['LoginInformation']) || strtoupper($_SESSION['LoginInformation']['MaVaiTro']) !== 'ADMIN') {
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index() {
        $currentInfo = $this->model->getLatestSetting();
        $history = $this->model->getHistory();
        
        $title = "Cài đặt Website";
        $viewContent = BASE_PATH . '/views/admin/setting_index.php';
        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['UpdatedById'] = $_SESSION['LoginInformation']['TaiKhoanId'];
            $data['IsActive'] = isset($_POST['IsActive']) ? 1 : 0;

            // Xử lý upload Logo/Banner nếu có (giống logic upload SP)
            // ...

            $this->model->saveSetting($data);
            $_SESSION['success'] = "Đã cập nhật cấu hình hệ thống.";
            header("Location: index.php?controller=adminsetting");
            exit;
        }
    }

    public function deleteHistory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Lấy ID từ form gửi lên (name="id")
            $id = $_POST['id'] ?? 0;
            
            if ($id > 0) {
                // Gọi hàm từ model bạn đã viết
                $result = $this->model->deleteHistory($id);
                
                if ($result) {
                    $_SESSION['success'] = "Đã xóa bản ghi lịch sử thành công.";
                } else {
                    $_SESSION['error'] = "Không thể xóa bản ghi này.";
                }
            }
            
            // Quay lại trang danh sách
            header("Location: index.php?controller=adminsetting");
            exit;
        }
    }
}