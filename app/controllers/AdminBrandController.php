<?php
require_once BASE_PATH . '/app/models/AdminBrandModel.php';

class AdminBrandController {
    private $model;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new AdminBrandModel($pdo);
        if (!isset($_SESSION['LoginInformation']) || strtoupper($_SESSION['LoginInformation']['MaVaiTro']) !== 'ADMIN') {
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index() {
        $pdo = $this->pdo;
        $brands = $this->model->getAllBrands();
        
        // Nếu có editId trên URL thì lấy dữ liệu để đổ vào Form
        $editId = $_GET['editId'] ?? 0;
        $brandEdit = ($editId > 0) ? $this->model->getBrandById($editId) : null;

        $title = "Quản lý Thương hiệu";
        $viewContent = BASE_PATH . '/views/admin/brand_index.php';
        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $id = $data['ThuongHieuId'] ?? 0;

            // Kiểm tra trùng mã
            if ($this->model->checkDuplicateCode($data['MaThuongHieu'], $id)) {
                $_SESSION['error'] = "Mã thương hiệu đã tồn tại trong hệ thống.";
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }

            if ($this->model->save($data)) {
                $_SESSION['success'] = ($id > 0) ? "Cập nhật thành công." : "Thêm mới thành công.";
            }
            header("Location: index.php?controller=adminbrand");
            exit;
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            // Lấy thông tin để check số sản phẩm
            $all = $this->model->getAllBrands();
            $current = array_filter($all, fn($b) => $b['ThuongHieuId'] == $id);
            $brand = reset($current);

            if ($brand && $brand['SoSanPham'] > 0) {
                // Có sản phẩm -> Chỉ ẩn (Soft delete)
                $this->model->updateStatus($id, 0);
                $_SESSION['success'] = "Đã ngừng kích hoạt thương hiệu vì đang có sản phẩm liên kết.";
            } else {
                // Không có sản phẩm -> Xóa thật
                $this->model->delete($id);
                $_SESSION['success'] = "Xóa thương hiệu thành công.";
            }
            header("Location: index.php?controller=adminbrand");
            exit;
        }
    }
}