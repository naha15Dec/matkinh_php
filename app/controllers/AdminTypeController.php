<?php
require_once BASE_PATH . '/app/models/AdminTypeModel.php';

class AdminTypeController
{
    private $model;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new AdminTypeModel($pdo);

        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if (!$sessionAccount || $roleCode !== 'ADMIN') {
            $_SESSION['error'] = "Chỉ Quản trị viên mới có quyền truy cập danh mục loại sản phẩm.";
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index()
    {
        $pdo = $this->pdo;

        $types = $this->model->getAllTypes();

        $editId = (int)($_GET['editId'] ?? 0);
        $typeEdit = $editId > 0 ? $this->model->getById($editId) : null;

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $displayName = $sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Tài khoản';
        $isAdmin = $roleCode === 'ADMIN';
        $isStaff = $roleCode === 'STAFF';
        $isShipper = $roleCode === 'SHIPPER';

        $title = "Loại sản phẩm";
        $viewContent = BASE_PATH . '/views/admin/type_index.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=admintype");
            exit;
        }

        $data = $_POST;
        $id = (int)($data['LoaiSanPhamId'] ?? 0);

        $data['MaLoaiSanPham'] = trim($data['MaLoaiSanPham'] ?? '');
        $data['TenLoaiSanPham'] = trim($data['TenLoaiSanPham'] ?? '');
        $data['IsActive'] = (int)($data['IsActive'] ?? 1);

        if ($data['TenLoaiSanPham'] === '') {
            $_SESSION['error'] = "Tên loại sản phẩm không được để trống.";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=admintype"));
            exit;
        }

        if ($this->model->save($data)) {
            $_SESSION['success'] = $id > 0
                ? "Cập nhật loại sản phẩm thành công."
                : "Thêm loại sản phẩm thành công.";
        } else {
            $_SESSION['error'] = "Lưu loại sản phẩm thất bại.";
        }

        header("Location: index.php?controller=admintype");
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=admintype");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = "Loại sản phẩm không hợp lệ.";
            header("Location: index.php?controller=admintype");
            exit;
        }

        $type = $this->model->getById($id);

        if (!$type) {
            $_SESSION['error'] = "Không tìm thấy loại sản phẩm.";
            header("Location: index.php?controller=admintype");
            exit;
        }

        $productCount = (int)($type['SoSanPham'] ?? 0);

        if ($productCount > 0) {
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