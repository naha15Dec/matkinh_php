<?php
require_once BASE_PATH . '/app/models/AdminTypeModel.php';

class AdminTypeController {
    private $model;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new AdminTypeModel($pdo);
        // Bảo mật: Chỉ Admin mới có quyền vào danh mục
        if (!isset($_SESSION['LoginInformation']) || strtoupper($_SESSION['LoginInformation']['MaVaiTro']) !== 'ADMIN') {
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index() {
        $pdo = $this->pdo;
        $types = $this->model->getAllTypes();
        
        $editId = $_GET['editId'] ?? 0;
        $typeEdit = ($editId > 0) ? $this->model->getById($editId) : null;

        $title = "Loại sản phẩm";
        $viewContent = BASE_PATH . '/views/admin/type_index.php';
        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $this->model->save($data);
            $_SESSION['success'] = "Cập nhật loại sản phẩm thành công.";
            header("Location: index.php?controller=admintype");
            exit;
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            // Logic giống Brand: Nếu có SP thì ẩn, không có SP thì xóa cứng
            $type = $this->model->getById($id); 
            // Giả sử model getById đã trả về số sản phẩm liên kết
            if ($type['SoSanPham'] > 0) {
                $this->model->updateStatus($id, 0);
                $_SESSION['success'] = "Loại sản phẩm đã được ẩn vì đang chứa sản phẩm.";
            } else {
                $this->model->hardDelete($id);
                $_SESSION['success'] = "Xóa loại sản phẩm thành công.";
            }
            header("Location: index.php?controller=admintype");
            exit;
        }
    }
}