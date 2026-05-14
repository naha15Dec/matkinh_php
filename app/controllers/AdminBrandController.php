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

        $id = (int)($_POST['ThuongHieuId'] ?? 0);

        if ($id > 0 && !$this->model->getBrandById($id)) {
            $_SESSION['error'] = "Không tìm thấy thương hiệu cần cập nhật.";
            header("Location: index.php?controller=adminbrand");
            exit;
        }

        $data = [];
        $data['ThuongHieuId'] = $id;
        $data['MaThuongHieu'] = strtoupper(trim($_POST['MaThuongHieu'] ?? ''));
        $data['TenThuongHieu'] = trim($_POST['TenThuongHieu'] ?? '');
        $data['MoTa'] = trim($_POST['MoTa'] ?? '');
        $data['IsActive'] = !empty($_POST['IsActive']) ? 1 : 0;

        if ($data['TenThuongHieu'] === '') {
            $_SESSION['error'] = "Tên thương hiệu không được để trống.";
            $this->redirectBack();
        }

        if (mb_strlen($data['TenThuongHieu'], 'UTF-8') > 150) {
            $_SESSION['error'] = "Tên thương hiệu không được vượt quá 150 ký tự.";
            $this->redirectBack();
        }

        if (mb_strlen($data['MoTa'], 'UTF-8') > 500) {
            $_SESSION['error'] = "Mô tả thương hiệu không được vượt quá 500 ký tự.";
            $this->redirectBack();
        }

        $finalCode = $this->model->generateUniqueCode(
            $data['MaThuongHieu'],
            $data['TenThuongHieu'],
            $id
        );

        if (mb_strlen($finalCode, 'UTF-8') > 20) {
            $_SESSION['error'] = "Mã thương hiệu không được vượt quá 20 ký tự.";
            $this->redirectBack();
        }

        if ($this->model->checkDuplicateCode($finalCode, $id)) {
            $_SESSION['error'] = "Mã thương hiệu đã tồn tại trong hệ thống.";
            $this->redirectBack();
        }

        if ($this->model->checkDuplicateName($data['TenThuongHieu'], $id)) {
            $_SESSION['error'] = "Tên thương hiệu đã tồn tại trong hệ thống.";
            $this->redirectBack();
        }

        $data['MaThuongHieu'] = $finalCode;

        if ($this->model->save($data)) {
            $_SESSION['success'] = $id > 0
                ? "Cập nhật thương hiệu thành công."
                : "Thêm thương hiệu thành công.";
        } else {
            $_SESSION['error'] = "Thông tin không thay đổi hoặc lưu thương hiệu thất bại.";
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
            if ((int)($brand['IsActive'] ?? 0) === 0) {
                $_SESSION['error'] = "Thương hiệu này đã ngừng kích hoạt.";
            } elseif ($this->model->updateStatus($id, 0)) {
                $_SESSION['success'] = "Đã ngừng kích hoạt thương hiệu vì đang có sản phẩm liên kết.";
            } else {
                $_SESSION['error'] = "Không thể ngừng kích hoạt thương hiệu.";
            }
        } else {
            if ($this->model->delete($id)) {
                $_SESSION['success'] = "Xóa thương hiệu thành công.";
            } else {
                $_SESSION['error'] = "Xóa thương hiệu thất bại.";
            }
        }

        header("Location: index.php?controller=adminbrand");
        exit;
    }

    public function toggleStatus()
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

        $brand = $this->model->getBrandById($id);

        if (!$brand) {
            $_SESSION['error'] = "Không tìm thấy thương hiệu.";
            header("Location: index.php?controller=adminbrand");
            exit;
        }

        $newStatus = !empty($brand['IsActive']) ? 0 : 1;

        if ($this->model->updateStatus($id, $newStatus)) {
            $_SESSION['success'] = $newStatus === 1
                ? "Đã kích hoạt thương hiệu."
                : "Đã ngừng kích hoạt thương hiệu.";
        } else {
            $_SESSION['error'] = "Cập nhật trạng thái thương hiệu thất bại.";
        }

        header("Location: index.php?controller=adminbrand");
        exit;
    }

    private function redirectBack()
    {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=adminbrand"));
        exit;
    }
}