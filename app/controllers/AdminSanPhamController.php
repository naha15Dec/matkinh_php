<?php
require_once BASE_PATH . '/app/models/AdminSanPhamModel.php';

class AdminSanPhamController
{
    private $model;
    private $pdo;

    private const PAGE_SIZE = 10;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdo = $pdo;
        $this->model = new AdminSanPhamModel($pdo);

        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if (!$sessionAccount || !in_array($roleCode, ['ADMIN', 'STAFF'], true)) {
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
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

        $allowedStatus = ['stock', 'outofstock', 'inactive', 'all'];

        if (!in_array($statusProduct, $allowedStatus, true)) {
            $statusProduct = 'stock';
        }

        $userId = ($roleCode === 'ADMIN') ? null : (int)$sessionAccount['TaiKhoanId'];

        $result = $this->model->getProducts($statusProduct, $keyword, $userId, $page, self::PAGE_SIZE);

        $products = $result['data'];
        $totalCount = (int)$result['totalCount'];

        $totalPages = max(1, (int)ceil($totalCount / self::PAGE_SIZE));

        if ($page > $totalPages) {
            $page = $totalPages;

            $result = $this->model->getProducts($statusProduct, $keyword, $userId, $page, self::PAGE_SIZE);
            $products = $result['data'];
        }

        $pagination = [
            'CurrentPage' => $page,
            'PageSize' => self::PAGE_SIZE,
            'TotalCount' => $totalCount,
            'TotalPages' => $totalPages,
            'DisplayStart' => max(1, $page - 2),
            'DisplayEnd' => min($totalPages, $page + 2)
        ];

        if (($pagination['DisplayEnd'] - $pagination['DisplayStart']) < 4) {
            if ($pagination['DisplayStart'] === 1) {
                $pagination['DisplayEnd'] = min($totalPages, $pagination['DisplayStart'] + 4);
            } elseif ($pagination['DisplayEnd'] === $totalPages) {
                $pagination['DisplayStart'] = max(1, $pagination['DisplayEnd'] - 4);
            }
        }

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

        // STAFF không được tự bật nổi bật. ADMIN mới được.
        if ($roleCode === 'ADMIN') {
            $data['IsFeatured'] = !empty($data['IsFeatured']) ? 1 : 0;
        } else {
            $data['IsFeatured'] = $id > 0 ? (int)($product['IsFeatured'] ?? 0) : 0;
        }

        $data['TrangThai'] = (int)($data['TrangThai'] ?? 1);

        if (!in_array($data['TrangThai'], [1, 2], true)) {
            $data['TrangThai'] = 1;
        }

        $error = $this->validateProductData($data, $id);

        if ($error !== null) {
            $_SESSION['error'] = $error;
            $redirectId = $id > 0 ? "&id=" . $id : "";
            header("Location: index.php?controller=adminsanpham&action=edit" . $redirectId);
            exit;
        }

        $uploadResult = $this->uploadImage('imageAvatar');

        if (!$uploadResult['success']) {
            $_SESSION['error'] = $uploadResult['message'];
            $redirectId = $id > 0 ? "&id=" . $id : "";
            header("Location: index.php?controller=adminsanpham&action=edit" . $redirectId);
            exit;
        }

        if (!empty($uploadResult['file'])) {
            $data['HinhAnhChinh'] = $uploadResult['file'];

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

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if ($roleCode !== 'ADMIN') {
            $_SESSION['error'] = "Chỉ Quản trị viên mới có quyền cập nhật sản phẩm nổi bật.";
            $this->redirectBack();
        }

        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = "Sản phẩm không hợp lệ.";
            $this->redirectBack();
        }

        $product = $this->model->getProductById($id);

        if (!$product) {
            $_SESSION['error'] = "Không tìm thấy sản phẩm.";
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

        /*
            STAFF chỉ được ngừng bán sản phẩm của mình.
            Không được xóa cứng.
        */
        if ($roleCode !== 'ADMIN') {
            if ($this->model->softDelete($id)) {
                $_SESSION['success'] = "Nhân viên chỉ được chuyển sản phẩm sang trạng thái ngừng bán.";
            } else {
                $_SESSION['error'] = "Không thể chuyển sản phẩm sang trạng thái ngừng bán.";
            }

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

        if (mb_strlen($data['MaSanPham'], 'UTF-8') > 30) {
            return "Mã sản phẩm không được vượt quá 30 ký tự.";
        }

        if ($this->model->isProductCodeExists($data['MaSanPham'], $id)) {
            return "Mã sản phẩm đã tồn tại.";
        }

        if ($data['TenSanPham'] === '') {
            return "Tên sản phẩm không được để trống.";
        }

        if (mb_strlen($data['TenSanPham'], 'UTF-8') > 200) {
            return "Tên sản phẩm không được vượt quá 200 ký tự.";
        }

        if (mb_strlen($data['MoTaNgan'], 'UTF-8') > 500) {
            return "Mô tả ngắn không được vượt quá 500 ký tự.";
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
                'message' => "Tải ảnh sản phẩm thất bại."
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
                'message' => "Ảnh sản phẩm chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP."
            ];
        }

        if ($size <= 0 || $size > 3 * 1024 * 1024) {
            return [
                'success' => false,
                'file' => null,
                'message' => "Dung lượng ảnh sản phẩm tối đa là 3MB."
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

        $fileName = 'product_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            return [
                'success' => false,
                'file' => null,
                'message' => "Không thể lưu ảnh sản phẩm."
            ];
        }

        return [
            'success' => true,
            'file' => $fileName,
            'message' => null
        ];
    }

    private function normalizeMoney($value)
    {
        $value = trim((string)$value);

        if (preg_match('/^\d+\.00$/', $value)) {
            $value = str_replace('.00', '', $value);
        }

        $value = str_replace(['₫', ' ', ','], '', $value);

        if (preg_match('/^\d+\.\d{2}$/', $value)) {
            $value = substr($value, 0, -3);
        } else {
            $value = str_replace('.', '', $value);
        }

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