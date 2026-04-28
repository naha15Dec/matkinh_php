<?php
require_once BASE_PATH . '/app/models/AdminBrandModel.php';

class AdminBrandController
{
    private $model;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new AdminBrandModel($pdo);

        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if (!$sessionAccount || $roleCode !== 'ADMIN') {
            $_SESSION['error'] = "Chỉ Quản trị viên mới có quyền truy cập thương hiệu.";
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index()
    {
        $pdo = $this->pdo;

        $brands = $this->model->getAllBrands();

        $editId = (int)($_GET['editId'] ?? 0);
        $brandEdit = $editId > 0 ? $this->model->getBrandById($editId) : null;

        if ($editId > 0 && !$brandEdit) {
            $_SESSION['error'] = "Không tìm thấy thương hiệu cần chỉnh sửa.";
            header("Location: index.php?controller=adminbrand");
            exit;
        }

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $displayName = $sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Tài khoản';
        $isAdmin = $roleCode === 'ADMIN';
        $isStaff = $roleCode === 'STAFF';
        $isShipper = $roleCode === 'SHIPPER';

        $title = "Quản lý thương hiệu";
        $viewContent = BASE_PATH . '/views/admin/brand_index.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=adminbrand");
            exit;
        }

        $data = $_POST;

        $id = (int)($data['ThuongHieuId'] ?? 0);
        $data['MaThuongHieu'] = trim($data['MaThuongHieu'] ?? '');
        $data['TenThuongHieu'] = trim($data['TenThuongHieu'] ?? '');
        $data['MoTa'] = trim($data['MoTa'] ?? '');
        $data['IsActive'] = (int)($data['IsActive'] ?? 1);

        if ($data['TenThuongHieu'] === '') {
            $_SESSION['error'] = "Tên thương hiệu không được để trống.";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=adminbrand"));
            exit;
        }

        $finalCode = $this->model->generateCodeIfEmpty($data['MaThuongHieu'], $data['TenThuongHieu']);

        if ($this->model->checkDuplicateCode($finalCode, $id)) {
            $_SESSION['error'] = "Mã thương hiệu đã tồn tại trong hệ thống.";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=adminbrand"));
            exit;
        }

        $data['MaThuongHieu'] = $finalCode;

        if ($this->model->save($data)) {
            $_SESSION['success'] = $id > 0
                ? "Cập nhật thương hiệu thành công."
                : "Thêm thương hiệu thành công.";
        } else {
            $_SESSION['error'] = "Lưu thương hiệu thất bại.";
        }

        header("Location: index.php?controller=adminbrand");
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=adminbrand");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = "Thương hiệu không hợp lệ.";
            header("Location: index.php?controller=adminbrand");
            exit;
        }

        $brand = $this->model->getBrandWithProductCount($id);

        if (!$brand) {
            $_SESSION['error'] = "Không tìm thấy thương hiệu.";
            header("Location: index.php?controller=adminbrand");
            exit;
        }

        if ((int)($brand['SoSanPham'] ?? 0) > 0) {
            $this->model->updateStatus($id, 0);
            $_SESSION['success'] = "Đã ngừng kích hoạt thương hiệu vì đang có sản phẩm liên kết.";
        } else {
            $this->model->delete($id);
            $_SESSION['success'] = "Xóa thương hiệu thành công.";
        }

        header("Location: index.php?controller=adminbrand");
        exit;
    }
}