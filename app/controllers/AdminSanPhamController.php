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

        $allowedStatus = ['stock', 'outofstock', 'inactive', 'all'];
        if (!in_array($statusProduct, $allowedStatus, true)) {
            $statusProduct = 'stock';
        }

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

            if (!$this->canManageProduct($product, $sessionAccount, $roleCode)) {
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

        $product = null;
        $data = $_POST;

        if ($id > 0) {
            $product = $this->model->getProductById($id);

            if (!$product) {
                $_SESSION['error'] = "Không tìm thấy sản phẩm cần cập nhật.";
                header("Location: index.php?controller=adminsanpham");
                exit;
            }

            if (!$this->canManageProduct($product, $sessionAccount, $roleCode)) {
                $_SESSION['error'] = "Bạn không có quyền chỉnh sửa sản phẩm này.";
                header("Location: index.php?controller=adminsanpham");
                exit;
            }
        } else {
            $data['CreatedById'] = (int)$sessionAccount['TaiKhoanId'];

            if (empty(trim($data['MaSanPham'] ?? ''))) {
                $data['MaSanPham'] = "SP" . date("ymdHis") . mt_rand(100, 999);
            }
        }

        $data['MaSanPham'] = trim($data['MaSanPham'] ?? '');
        $data['TenSanPham'] = trim($data['TenSanPham'] ?? '');
        $data['MoTaNgan'] = trim($data['MoTaNgan'] ?? '');
        $data['MoTaChiTiet'] = trim($data['MoTaChiTiet'] ?? '');

        $data['GiaGoc'] = $this->normalizeMoney($data['GiaGoc'] ?? 0);
        $data['GiaBan'] = $this->normalizeMoney($data['GiaBan'] ?? 0);
        $data['SoLuongTon'] = max(0, (int)($data['SoLuongTon'] ?? 0));
        $data['ThuongHieuId'] = (int)($data['ThuongHieuId'] ?? 0);
        $data['LoaiSanPhamId'] = (int)($data['LoaiSanPhamId'] ?? 0);
        $data['IsFeatured'] = !empty($data['IsFeatured']) ? 1 : 0;
        $data['TrangThai'] = (int)($data['TrangThai'] ?? 1);

        if ($data['TrangThai'] !== 1 && $data['TrangThai'] !== 2) {
            $data['TrangThai'] = 1;
        }

        $error = $this->validateProductData($data, $id);

        if ($error !== null) {
            $_SESSION['error'] = $error;
            $redirectId = $id > 0 ? "&id=" . $id : "";
            header("Location: index.php?controller=adminsanpham&action=edit" . $redirectId);
            exit;
        }

        $newImage = $this->uploadImage('imageAvatar');

        if ($newImage) {
            $data['HinhAnhChinh'] = $newImage;

            if ($id > 0 && !empty($product['HinhAnhChinh']) && $this->isLocalImage($product['HinhAnhChinh'])) {
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
            : "Có lỗi xảy ra trong quá trình lưu dữ liệu hoặc dữ liệu không thay đổi.";

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

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if ($id <= 0) {
            $_SESSION['error'] = "Sản phẩm không hợp lệ.";
            $this->redirectBack();
        }

        $product = $this->model->getProductById($id);

        if (!$product) {
            $_SESSION['error'] = "Không tìm thấy sản phẩm.";
            $this->redirectBack();
        }

        if (!$this->canManageProduct($product, $sessionAccount, $roleCode)) {
            $_SESSION['error'] = "Bạn không có quyền cập nhật sản phẩm này.";
            $this->redirectBack();
        }

        if ($this->model->toggleFeatured($id)) {
            $_SESSION['success'] = "Cập nhật trạng thái nổi bật thành công.";
        } else {
            $_SESSION['error'] = "Cập nhật trạng thái nổi bật thất bại hoặc dữ liệu không thay đổi.";
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

        if (!$product) {
            $_SESSION['error'] = "Không tìm thấy sản phẩm.";
            header("Location: index.php?controller=adminsanpham");
            exit;
        }

        if (!$this->canManageProduct($product, $sessionAccount, $roleCode)) {
            $_SESSION['error'] = "Bạn không có quyền thực hiện thao tác này.";
            header("Location: index.php?controller=adminsanpham");
            exit;
        }

        $hasOrders = $this->model->checkProductInOrders($id);
        $hasBehaviors = $this->model->checkProductInBehaviors($id);

        if ($hasOrders || $hasBehaviors) {
            if ($this->model->softDelete($id)) {
                $_SESSION['success'] = "Sản phẩm đã phát sinh dữ liệu liên quan nên chỉ chuyển sang trạng thái ngừng bán.";
            } else {
                $_SESSION['error'] = "Không thể chuyển sản phẩm sang trạng thái ngừng bán.";
            }
        } else {
            if ($this->model->delete($id)) {
                if (!empty($product['HinhAnhChinh']) && $this->isLocalImage($product['HinhAnhChinh'])) {
                    @unlink(BASE_PATH . '/public/images/' . $product['HinhAnhChinh']);
                }

                $_SESSION['success'] = "Đã xóa sản phẩm vĩnh viễn khỏi hệ thống.";
            } else {
                $_SESSION['error'] = "Xóa sản phẩm thất bại.";
            }
        }

        header("Location: index.php?controller=adminsanpham");
        exit;
    }

    private function validateProductData($data, $id = 0)
    {
        if ($data['MaSanPham'] === '') {
            return "Mã sản phẩm không được để trống.";
        }

        if ($this->model->isProductCodeExists($data['MaSanPham'], $id)) {
            return "Mã sản phẩm đã tồn tại.";
        }

        if ($data['TenSanPham'] === '') {
            return "Tên sản phẩm không được để trống.";
        }

        if ($data['ThuongHieuId'] <= 0 || !$this->model->brandExists($data['ThuongHieuId'])) {
            return "Thương hiệu không hợp lệ hoặc đã ngừng hoạt động.";
        }

        if ($data['LoaiSanPhamId'] <= 0 || !$this->model->categoryExists($data['LoaiSanPhamId'])) {
            return "Loại sản phẩm không hợp lệ hoặc đã ngừng hoạt động.";
        }

        if ($data['GiaGoc'] < 0 || $data['GiaBan'] < 0) {
            return "Giá sản phẩm không được nhỏ hơn 0.";
        }

        if ($data['GiaBan'] <= 0) {
            return "Giá bán phải lớn hơn 0.";
        }

        if ($data['GiaGoc'] > 0 && $data['GiaBan'] > $data['GiaGoc']) {
            return "Giá bán không nên lớn hơn giá gốc.";
        }

        if ($data['SoLuongTon'] < 0) {
            return "Số lượng tồn không được nhỏ hơn 0.";
        }

        return null;
    }

    private function uploadImage($fileField)
    {
        if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Tải ảnh sản phẩm thất bại.";
            return null;
        }

        $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

        $originalName = $_FILES[$fileField]['name'] ?? '';
        $tmpName = $_FILES[$fileField]['tmp_name'] ?? '';
        $size = (int)($_FILES[$fileField]['size'] ?? 0);

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExt, true)) {
            $_SESSION['error'] = "Ảnh sản phẩm chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP.";
            return null;
        }

        if ($size <= 0 || $size > 3 * 1024 * 1024) {
            $_SESSION['error'] = "Dung lượng ảnh sản phẩm tối đa là 3MB.";
            return null;
        }

        $mimeType = function_exists('mime_content_type') ? mime_content_type($tmpName) : '';

        if ($mimeType !== '' && !in_array($mimeType, $allowedMime, true)) {
            $_SESSION['error'] = "File tải lên không đúng định dạng ảnh.";
            return null;
        }

        $uploadDir = BASE_PATH . '/public/images/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'product_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            return $fileName;
        }

        $_SESSION['error'] = "Không thể lưu ảnh sản phẩm.";
        return null;
    }

    private function normalizeMoney($value)
    {
        $value = trim((string)$value);
        $value = str_replace(['₫', ' ', ','], '', $value);
        $value = str_replace('.', '', $value);

        return max(0, (float)$value);
    }

    private function canManageProduct($product, $sessionAccount, $roleCode)
    {
        if ($roleCode === 'ADMIN') {
            return true;
        }

        return (int)($product['CreatedById'] ?? 0) === (int)($sessionAccount['TaiKhoanId'] ?? 0);
    }

    private function isLocalImage($image)
    {
        $image = trim((string)$image);

        if ($image === '' || $image === 'default.jpg') {
            return false;
        }

        if (preg_match('/^https?:\/\//i', $image)) {
            return false;
        }

        return basename($image) === $image;
    }

    private function redirectBack()
    {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=adminsanpham"));
        exit;
    }
}