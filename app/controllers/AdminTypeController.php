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

        if ($editId > 0 && !$typeEdit) {
            $_SESSION['error'] = "Không tìm thấy loại sản phẩm cần chỉnh sửa.";
            header("Location: index.php?controller=admintype");
            exit;
        }

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

        $id = (int)($_POST['LoaiSanPhamId'] ?? 0);

        if ($id > 0 && !$this->model->getById($id)) {
            $_SESSION['error'] = "Không tìm thấy loại sản phẩm cần cập nhật.";
            header("Location: index.php?controller=admintype");
            exit;
        }

        $data = [];
        $data['LoaiSanPhamId'] = $id;
        $data['MaLoaiSanPham'] = strtoupper(trim($_POST['MaLoaiSanPham'] ?? ''));
        $data['TenLoaiSanPham'] = trim($_POST['TenLoaiSanPham'] ?? '');
        $data['MoTa'] = trim($_POST['MoTa'] ?? '');
        $data['IsActive'] = !empty($_POST['IsActive']) ? 1 : 0;

        if ($data['TenLoaiSanPham'] === '') {
            $_SESSION['error'] = "Tên loại sản phẩm không được để trống.";
            $this->redirectBack();
        }

        if (mb_strlen($data['TenLoaiSanPham'], 'UTF-8') > 150) {
            $_SESSION['error'] = "Tên loại sản phẩm không được vượt quá 150 ký tự.";
            $this->redirectBack();
        }

        if (mb_strlen($data['MoTa'], 'UTF-8') > 500) {
            $_SESSION['error'] = "Mô tả loại sản phẩm không được vượt quá 500 ký tự.";
            $this->redirectBack();
        }

        $finalCode = $this->model->generateUniqueCode(
            $data['MaLoaiSanPham'],
            $data['TenLoaiSanPham'],
            $id
        );

        if (mb_strlen($finalCode, 'UTF-8') > 20) {
            $_SESSION['error'] = "Mã loại sản phẩm không được vượt quá 20 ký tự.";
            $this->redirectBack();
        }

        if ($this->model->checkDuplicateCode($finalCode, $id)) {
            $_SESSION['error'] = "Mã loại sản phẩm đã tồn tại trong hệ thống.";
            $this->redirectBack();
        }

        if ($this->model->checkDuplicateName($data['TenLoaiSanPham'], $id)) {
            $_SESSION['error'] = "Tên loại sản phẩm đã tồn tại trong hệ thống.";
            $this->redirectBack();
        }

        $data['MaLoaiSanPham'] = $finalCode;

        if ($this->model->save($data)) {
            $_SESSION['success'] = $id > 0
                ? "Cập nhật loại sản phẩm thành công."
                : "Thêm loại sản phẩm thành công.";
        } else {
            $_SESSION['error'] = "Thông tin không thay đổi hoặc lưu loại sản phẩm thất bại.";
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

        $type = $this->model->getTypeWithProductCount($id);

        if (!$type) {
            $_SESSION['error'] = "Không tìm thấy loại sản phẩm.";
            header("Location: index.php?controller=admintype");
            exit;
        }

        $productCount = (int)($type['SoSanPham'] ?? 0);

        if ($productCount > 0) {
            if ((int)($type['IsActive'] ?? 0) === 0) {
                $_SESSION['error'] = "Loại sản phẩm này đã ngừng sử dụng.";
            } elseif ($this->model->updateStatus($id, 0)) {
                $_SESSION['success'] = "Đã ngừng sử dụng loại sản phẩm vì đang có sản phẩm liên kết.";
            } else {
                $_SESSION['error'] = "Không thể ngừng sử dụng loại sản phẩm.";
            }
        } else {
            if ($this->model->hardDelete($id)) {
                $_SESSION['success'] = "Xóa loại sản phẩm thành công.";
            } else {
                $_SESSION['error'] = "Xóa loại sản phẩm thất bại.";
            }
        }

        header("Location: index.php?controller=admintype");
        exit;
    }

    public function toggleStatus()
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

        $newStatus = !empty($type['IsActive']) ? 0 : 1;

        if ($this->model->updateStatus($id, $newStatus)) {
            $_SESSION['success'] = $newStatus === 1
                ? "Đã kích hoạt loại sản phẩm."
                : "Đã ngừng sử dụng loại sản phẩm.";
        } else {
            $_SESSION['error'] = "Cập nhật trạng thái loại sản phẩm thất bại.";
        }

        header("Location: index.php?controller=admintype");
        exit;
    }

    private function redirectBack()
    {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=admintype"));
        exit;
    }
}