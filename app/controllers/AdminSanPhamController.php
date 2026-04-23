<?php
require_once BASE_PATH . '/app/models/AdminSanPhamModel.php';

class AdminSanPhamController {
    private $model;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new AdminSanPhamModel($pdo);
        
        // Kiểm tra quyền truy cập (Admin hoặc Staff)
        if (!isset($_SESSION['LoginInformation']) || !in_array(strtoupper($_SESSION['LoginInformation']['MaVaiTro']), ['ADMIN', 'STAFF'])) {
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    // Hiển thị danh sách sản phẩm
    public function index() {
    $user = $_SESSION['LoginInformation'];
    $role = strtoupper($user['MaVaiTro']);

    $statusProduct = $_GET['statusProduct'] ?? 'stock';
    $keyword = $_GET['keyword'] ?? '';
    
    // Nếu là nhân viên (STAFF), chỉ lấy sản phẩm của họ
    $userId = ($role === 'ADMIN') ? null : $user['TaiKhoanId'];
    
    $products = $this->model->getProducts($statusProduct, $keyword, $userId);
    
    $title = "Quản lý sản phẩm";
    $viewContent = BASE_PATH . '/views/admin/product_index.php';
    require_once BASE_PATH . '/views/admin/layout.php';
}

    // Bật/Tắt trạng thái sản phẩm nổi bật (Featured)
    public function toggleFeatured() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            if ($this->model->toggleFeatured($id)) {
                $_SESSION['success'] = "Cập nhật trạng thái nổi bật thành công.";
            }
        }
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Xử lý Cập nhật hoặc Thêm mới sản phẩm
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['SanPhamId'] ?? 0;
            $user = $_SESSION['LoginInformation'];
            $role = strtoupper($user['MaVaiTro']);
            $data = $_POST;

            if ($id > 0) {
                // Kiểm tra quyền sửa
                $product = $this->model->getProductById($id);
                if (!$product || ($role !== 'ADMIN' && $product['CreatedById'] != $user['TaiKhoanId'])) {
                    $_SESSION['error'] = "Bạn không có quyền chỉnh sửa sản phẩm này.";
                    header("Location: index.php?controller=adminsanpham");
                    exit;
                }
            } else {
                // Khi thêm mới, phải gán ID người tạo
                $data['CreatedById'] = $user['TaiKhoanId'];
                // Tự tạo mã sản phẩm nếu để trống (Giống C#)
                if (empty($data['MaSanPham'])) {
                    $data['MaSanPham'] = date("mdHis");
                }
            }

            // Xử lý upload ảnh
            $newImage = $this->uploadImage('imageAvatar');
            if ($newImage) {
                $data['HinhAnhChinh'] = $newImage;
                // Xóa ảnh cũ trên server nếu là update
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

            if ($result) {
                $_SESSION['success'] = $message;
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra trong quá trình lưu dữ liệu.";
            }

            header("Location: index.php?controller=adminsanpham");
            exit;
        }
    }

    // Ngừng bán sản phẩm (Soft Delete)
    public function delete() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = $_POST['id'] ?? 0;
        $user = $_SESSION['LoginInformation'];
        $product = $this->model->getProductById($id);

        // 1. Kiểm tra quyền sở hữu
        if (!$product || (strtoupper($user['MaVaiTro']) !== 'ADMIN' && $product['CreatedById'] != $user['TaiKhoanId'])) {
            $_SESSION['error'] = "Bạn không có quyền thực hiện thao tác này.";
            header("Location: index.php?controller=adminsanpham");
            exit;
        }

        // 2. Kiểm tra đơn hàng
        $hasOrders = $this->model->checkProductInOrders($id);
        
        if ($hasOrders) {
            // Nếu có đơn hàng -> Soft Delete (Ngừng bán)
            $this->model->softDelete($id);
            $_SESSION['success'] = "Sản phẩm đã phát sinh đơn hàng nên hệ thống chỉ chuyển sang trạng thái 'Ngừng bán'.";
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
}

    // Helper: Xử lý upload ảnh vào thư mục thực tế
    private function uploadImage($fileField) {
        if (isset($_FILES[$fileField]) && $_FILES[$fileField]['error'] === UPLOAD_ERR_OK) {
            $uploadDir = BASE_PATH . '/public/images/';
            
            // Đảm bảo thư mục tồn tại
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Tạo tên file duy nhất tránh trùng lặp
            $fileName = time() . '_' . basename($_FILES[$fileField]['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES[$fileField]['tmp_name'], $targetPath)) {
                return $fileName; 
            }
        }
        return null;
    }

    public function edit() {
        $pdo = $this->pdo;

        $id = $_GET['id'] ?? 0;
        
        // Lấy dữ liệu sản phẩm để sửa (nếu id = 0 thì coi như thêm mới)
        $product = null;
        if ($id > 0) {
            $product = $this->model->getProductById($id);
        }

        // Lấy dữ liệu cho các thẻ select trong form
        $brands = $this->model->getAllBrands();
        $categories = $this->model->getAllCategories();

        $title = $id > 0 ? "Chỉnh sửa sản phẩm" : "Thêm sản phẩm mới";
        $viewContent = BASE_PATH . '/views/admin/product_form.php';
        require_once BASE_PATH . '/views/admin/layout.php';
    }
}