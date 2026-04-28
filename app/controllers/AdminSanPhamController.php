<?php
require_once BASE_PATH . '/app/models/AdminSanPhamModel.php';

class AdminSanPhamController
{
    private $model;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new AdminSanPhamModel($pdo);

        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if (!$sessionAccount || !in_array($roleCode, ['ADMIN', 'STAFF'])) {
            $_SESSION['error'] = "Bạn không có quyền truy cập module sản phẩm.";
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index()
    {
        $pdo = $this->pdo;

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $statusProduct = $_GET['statusProduct'] ?? 'stock';
        $keyword = trim($_GET['keyword'] ?? '');

        $userId = ($roleCode === 'ADMIN') ? null : (int)$sessionAccount['TaiKhoanId'];

        $products = $this->model->getProducts($statusProduct, $keyword, $userId);

        $displayName = $sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Tài khoản';
        $isAdmin = $roleCode === 'ADMIN';
        $isStaff = $roleCode === 'STAFF';
        $isShipper = $roleCode === 'SHIPPER';

        $title = "Quản lý sản phẩm";
        $viewContent = BASE_PATH . '/views/admin/product_index.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function edit()
    {
        $pdo = $this->pdo;

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $id = (int)($_GET['id'] ?? 0);
        $product = null;

        if ($id > 0) {
            $product = $this->model->getProductById($id);

            if (!$product) {
                $_SESSION['error'] = "Không tìm thấy sản phẩm.";
                header("Location: index.php?controller=adminsanpham");
                exit;
            }

            if ($roleCode !== 'ADMIN' && (int)$product['CreatedById'] !== (int)$sessionAccount['TaiKhoanId']) {
                $_SESSION['error'] = "Bạn không có quyền chỉnh sửa sản phẩm này.";
                header("Location: index.php?controller=adminsanpham");
                exit;
            }
        }

        $brands = $this->model->getAllBrands();
        $categories = $this->model->getAllCategories();

        $displayName = $sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Tài khoản';
        $isAdmin = $roleCode === 'ADMIN';
        $isStaff = $roleCode === 'STAFF';
        $isShipper = $roleCode === 'SHIPPER';

        $title = $id > 0 ? "Chỉnh sửa sản phẩm" : "Thêm sản phẩm mới";
        $viewContent = BASE_PATH . '/views/admin/product_form.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=adminsanpham");
            exit;
        }

        $id = (int)($_POST['SanPhamId'] ?? 0);
        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $data = $_POST;
        $product = null;

        if ($id > 0) {
            $product = $this->model->getProductById($id);

            if (!$product || ($roleCode !== 'ADMIN' && (int)$product['CreatedById'] !== (int)$sessionAccount['TaiKhoanId'])) {
                $_SESSION['error'] = "Bạn không có quyền chỉnh sửa sản phẩm này.";
                header("Location: index.php?controller=adminsanpham");
                exit;
            }
        } else {
            $data['CreatedById'] = (int)$sessionAccount['TaiKhoanId'];

            if (empty($data['MaSanPham'])) {
                $data['MaSanPham'] = "SP" . date("ymdHis");
            }
        }

        $data['GiaGoc'] = $this->normalizeMoney($data['GiaGoc'] ?? 0);
        $data['GiaBan'] = $this->normalizeMoney($data['GiaBan'] ?? 0);
        $data['SoLuongTon'] = (int)($data['SoLuongTon'] ?? 0);
        $data['IsFeatured'] = (int)($data['IsFeatured'] ?? 0);
        $data['TrangThai'] = (int)($data['TrangThai'] ?? 1);

        $newImage = $this->uploadImage('imageAvatar');

        if ($newImage) {
            $data['HinhAnhChinh'] = $newImage;

            if ($id > 0 && !empty($product['HinhAnhChinh'])) {
                @unlink(BASE_PATH . '/public/images/' . $product['HinhAnhChinh']);
            }
        }

        if ($id > 0) {
            $result = $this->model->updateProduct($id, $data);
            $message = "Cập nhật sản phẩm thành công.";
        } else {
            $result = $this->model->createProduct($data);
            $message = "Thêm sản phẩm mới thành công.";
        }

        $_SESSION[$result ? 'success' : 'error'] = $result
            ? $message
            : "Có lỗi xảy ra trong quá trình lưu dữ liệu.";

        header("Location: index.php?controller=adminsanpham");
        exit;
    }

    public function toggleFeatured()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=adminsanpham");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = "Sản phẩm không hợp lệ.";
            $this->redirectBack();
        }

        if ($this->model->toggleFeatured($id)) {
            $_SESSION['success'] = "Cập nhật trạng thái nổi bật thành công.";
        } else {
            $_SESSION['error'] = "Cập nhật trạng thái nổi bật thất bại.";
        }

        $this->redirectBack();
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=adminsanpham");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if ($id <= 0) {
            $_SESSION['error'] = "Sản phẩm không hợp lệ.";
            header("Location: index.php?controller=adminsanpham");
            exit;
        }

        $product = $this->model->getProductById($id);

        if (!$product || ($roleCode !== 'ADMIN' && (int)$product['CreatedById'] !== (int)$sessionAccount['TaiKhoanId'])) {
            $_SESSION['error'] = "Bạn không có quyền thực hiện thao tác này.";
            header("Location: index.php?controller=adminsanpham");
            exit;
        }

        $hasOrders = $this->model->checkProductInOrders($id);

        if ($hasOrders) {
            $this->model->softDelete($id);
            $_SESSION['success'] = "Sản phẩm đã phát sinh đơn hàng nên chỉ chuyển sang trạng thái ngừng bán.";
        } else {
            if (!empty($product['HinhAnhChinh'])) {
                @unlink(BASE_PATH . '/public/images/' . $product['HinhAnhChinh']);
            }

            $this->model->delete($id);
            $_SESSION['success'] = "Đã xóa sản phẩm vĩnh viễn khỏi hệ thống.";
        }

        header("Location: index.php?controller=adminsanpham");
        exit;
    }

    private function uploadImage($fileField)
    {
        if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $originalName = $_FILES[$fileField]['name'];
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExt)) {
            $_SESSION['error'] = "Ảnh sản phẩm chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP.";
            return null;
        }

        $uploadDir = BASE_PATH . '/public/images/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'product_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES[$fileField]['tmp_name'], $targetPath)) {
            return $fileName;
        }

        return null;
    }

    private function normalizeMoney($value)
    {
        return (float)str_replace(['.', ',', '₫', ' '], '', (string)$value);
    }

    private function redirectBack()
    {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=adminsanpham"));
        exit;
    }
}