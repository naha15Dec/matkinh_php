<?php
require_once BASE_PATH . '/app/models/AdminBlogModel.php';

class AdminBlogController
{
    private $model;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new AdminBlogModel($pdo);

        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if (!$sessionAccount || !in_array($roleCode, ['ADMIN', 'STAFF'])) {
            $_SESSION['error'] = "Bạn không có quyền truy cập module bài viết.";
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index()
    {
        $pdo = $this->pdo;

        $user = $_SESSION['LoginInformation'];
        $role = strtoupper(trim($user['MaVaiTro'] ?? ''));

        $status = $_GET['status'] ?? 'published';
        $keyword = trim($_GET['keyword'] ?? '');

        $userId = $role === 'ADMIN' ? null : (int)$user['TaiKhoanId'];
        $posts = $this->model->getBlogs($status, $keyword, $userId);

        $displayName = $user['HoTen'] ?? $user['TenDangNhap'] ?? 'Tài khoản';
        $isAdmin = $role === 'ADMIN';
        $isStaff = $role === 'STAFF';
        $isShipper = $role === 'SHIPPER';

        $title = $this->getHeaderTitle($status);
        $viewContent = BASE_PATH . '/views/admin/blog_index.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function edit()
    {
        $pdo = $this->pdo;

        $user = $_SESSION['LoginInformation'];
        $role = strtoupper(trim($user['MaVaiTro'] ?? ''));

        $id = (int)($_GET['id'] ?? 0);
        $post = null;

        if ($id > 0) {
            $post = $this->model->getBlogById($id);

            if (!$post) {
                $_SESSION['error'] = "Không tìm thấy bài viết.";
                header("Location: index.php?controller=adminblog");
                exit;
            }

            if ($role !== 'ADMIN' && (int)$post['CreatedById'] !== (int)$user['TaiKhoanId']) {
                $_SESSION['error'] = "Bạn không có quyền chỉnh sửa bài viết này.";
                header("Location: index.php?controller=adminblog");
                exit;
            }
        }

        $displayName = $user['HoTen'] ?? $user['TenDangNhap'] ?? 'Tài khoản';
        $isAdmin = $role === 'ADMIN';
        $isStaff = $role === 'STAFF';
        $isShipper = $role === 'SHIPPER';

        $title = $id > 0 ? "Chỉnh sửa bài viết" : "Viết bài mới";
        $viewContent = BASE_PATH . '/views/admin/blog_form.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=adminblog");
            exit;
        }

        $id = (int)($_POST['BaiVietId'] ?? 0);
        $user = $_SESSION['LoginInformation'];
        $role = strtoupper(trim($user['MaVaiTro'] ?? ''));

        $data = $_POST;
        $data['TieuDe'] = trim($data['TieuDe'] ?? '');
        $data['TomTat'] = trim($data['TomTat'] ?? '');
        $data['NoiDung'] = trim($data['NoiDung'] ?? '');

        if ($data['TieuDe'] === '') {
            $_SESSION['error'] = "Tiêu đề bài viết không được để trống.";
            header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=adminblog"));
            exit;
        }

        $post = null;

        if ($id > 0) {
            $post = $this->model->getBlogById($id);

            if (!$post) {
                $_SESSION['error'] = "Không tìm thấy bài viết.";
                header("Location: index.php?controller=adminblog");
                exit;
            }

            if ($role !== 'ADMIN' && (int)$post['CreatedById'] !== (int)$user['TaiKhoanId']) {
                $_SESSION['error'] = "Bạn không có quyền cập nhật bài viết này.";
                header("Location: index.php?controller=adminblog");
                exit;
            }
        } else {
            $data['CreatedById'] = (int)$user['TaiKhoanId'];
        }

        $currentImage = $_POST['CurrentAnhDaiDien'] ?? ($post['AnhDaiDien'] ?? '');
        $data['AnhDaiDien'] = $currentImage;

        $newImage = $this->uploadImage('imageAvatar');

        if ($newImage) {
            $data['AnhDaiDien'] = $newImage;

            if ($id > 0 && !empty($currentImage)) {
                @unlink(BASE_PATH . '/public/images/' . $currentImage);
            }
        }

        if ($id > 0) {
            $result = $this->model->updateBlog($id, $data);
            $message = "Cập nhật bài viết thành công.";
        } else {
            $result = $this->model->createBlog($data);
            $message = "Thêm bài viết thành công.";
        }

        $_SESSION[$result ? 'success' : 'error'] = $result
            ? $message
            : "Lưu bài viết thất bại.";

        header("Location: index.php?controller=adminblog");
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=adminblog");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $user = $_SESSION['LoginInformation'];
        $role = strtoupper(trim($user['MaVaiTro'] ?? ''));

        if ($id <= 0) {
            $_SESSION['error'] = "Bài viết không hợp lệ.";
            header("Location: index.php?controller=adminblog");
            exit;
        }

        $post = $this->model->getBlogById($id);

        if (!$post) {
            $_SESSION['error'] = "Không tìm thấy bài viết.";
            header("Location: index.php?controller=adminblog");
            exit;
        }

        if ($role !== 'ADMIN' && (int)$post['CreatedById'] !== (int)$user['TaiKhoanId']) {
            $_SESSION['error'] = "Bạn không có quyền xóa bài viết này.";
            header("Location: index.php?controller=adminblog");
            exit;
        }

        if ($this->model->deleteBlog($id)) {
            if (!empty($post['AnhDaiDien'])) {
                @unlink(BASE_PATH . '/public/images/' . $post['AnhDaiDien']);
            }

            $_SESSION['success'] = "Xóa bài viết thành công.";
        } else {
            $_SESSION['error'] = "Xóa bài viết thất bại.";
        }

        header("Location: index.php?controller=adminblog");
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
            $_SESSION['error'] = "Ảnh bài viết chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP.";
            return null;
        }

        $uploadDir = BASE_PATH . '/public/images/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'blog_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES[$fileField]['tmp_name'], $targetPath)) {
            return $fileName;
        }

        return null;
    }

    private function getHeaderTitle($status)
    {
        return match ($status) {
            'draft' => 'Bài viết nháp',
            'hidden' => 'Bài viết đã ẩn',
            default => 'Quản lý bài viết',
        };
    }
}