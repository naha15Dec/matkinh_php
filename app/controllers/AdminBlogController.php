<?php
require_once BASE_PATH . '/app/models/AdminBlogModel.php';

class AdminBlogController
{
    private $model;
    private $pdo;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdo = $pdo;
        $this->model = new AdminBlogModel($pdo);

        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if (!$sessionAccount || !in_array($roleCode, ['ADMIN', 'STAFF'], true)) {
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

        $allowedStatus = ['published', 'draft', 'hidden', 'all'];

        if (!in_array($status, $allowedStatus, true)) {
            $status = 'published';
        }

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

            if (!$this->canManagePost($post, $user, $role)) {
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

        $post = null;

        if ($id > 0) {
            $post = $this->model->getBlogById($id);

            if (!$post) {
                $_SESSION['error'] = "Không tìm thấy bài viết.";
                header("Location: index.php?controller=adminblog");
                exit;
            }

            if (!$this->canManagePost($post, $user, $role)) {
                $_SESSION['error'] = "Bạn không có quyền cập nhật bài viết này.";
                header("Location: index.php?controller=adminblog");
                exit;
            }
        }

        $data = [];
        $data['BaiVietId'] = $id;
        $data['MaBaiViet'] = trim($_POST['MaBaiViet'] ?? '');
        $data['TieuDe'] = trim($_POST['TieuDe'] ?? '');
        $data['TomTat'] = trim($_POST['TomTat'] ?? '');
        $data['NoiDung'] = trim($_POST['NoiDung'] ?? '');

        /*
            Phân quyền trạng thái:
            ADMIN được chọn Đã đăng / Nháp / Ẩn.
            STAFF luôn lưu về Nháp / Chờ duyệt.
        */
        if ($role === 'ADMIN') {
            $data['TrangThai'] = (int)($_POST['TrangThai'] ?? 1);
        } else {
            $data['TrangThai'] = 0;
        }

        if ($data['MaBaiViet'] === '') {
            $data['MaBaiViet'] = 'BV' . date('ymdHis') . mt_rand(100, 999);
        }

        $error = $this->validateBlogData($data, $id);

        if ($error !== null) {
            $_SESSION['error'] = $error;
            $this->redirectToForm($id);
        }

        if ($id <= 0) {
            $data['CreatedById'] = (int)$user['TaiKhoanId'];
        }

        $currentImage = $id > 0 ? ($post['AnhDaiDien'] ?? '') : '';
        $data['AnhDaiDien'] = $currentImage;

        $uploadResult = $this->uploadImage('imageAvatar');

        if (!$uploadResult['success']) {
            $_SESSION['error'] = $uploadResult['message'];
            $this->redirectToForm($id);
        }

        if (!empty($uploadResult['file'])) {
            $data['AnhDaiDien'] = $uploadResult['file'];

            if ($id > 0 && $this->isLocalImage($currentImage)) {
                @unlink(BASE_PATH . '/public/images/' . $currentImage);
            }
        }

        if ($id > 0) {
            $result = $this->model->updateBlog($id, $data);

            if ($role === 'ADMIN') {
                $message = "Cập nhật bài viết thành công.";
            } else {
                $message = "Đã lưu bài viết. Bài viết đang chờ Quản trị viên kiểm duyệt.";
            }
        } else {
            $result = $this->model->createBlog($data);

            if ($role === 'ADMIN') {
                $message = "Thêm bài viết thành công.";
            } else {
                $message = "Đã tạo bài viết. Bài viết đang chờ Quản trị viên kiểm duyệt.";
            }
        }

        $_SESSION[$result ? 'success' : 'error'] = $result
            ? $message
            : "Lưu bài viết thất bại hoặc dữ liệu không thay đổi.";

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

        if (!$this->canManagePost($post, $user, $role)) {
            $_SESSION['error'] = "Bạn không có quyền xóa bài viết này.";
            header("Location: index.php?controller=adminblog");
            exit;
        }

        /*
            STAFF chỉ được xóa bài đang Nháp / Chờ duyệt.
            Bài đã đăng hoặc đã ẩn thì để Admin xử lý.
        */
        if ($role !== 'ADMIN' && (int)($post['TrangThai'] ?? 0) !== 0) {
            $_SESSION['error'] = "Nhân viên chỉ được xóa bài viết đang ở trạng thái nháp/chờ duyệt.";
            header("Location: index.php?controller=adminblog");
            exit;
        }

        if ($this->model->deleteBlog($id)) {
            if ($this->isLocalImage($post['AnhDaiDien'] ?? '')) {
                @unlink(BASE_PATH . '/public/images/' . $post['AnhDaiDien']);
            }

            $_SESSION['success'] = "Xóa bài viết thành công.";
        } else {
            $_SESSION['error'] = "Xóa bài viết thất bại.";
        }

        header("Location: index.php?controller=adminblog");
        exit;
    }

    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=adminblog");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $newStatus = (int)($_POST['TrangThai'] ?? -1);

        $user = $_SESSION['LoginInformation'];
        $role = strtoupper(trim($user['MaVaiTro'] ?? ''));

        if ($role !== 'ADMIN') {
            $_SESSION['error'] = "Chỉ Quản trị viên mới có quyền kiểm duyệt và cập nhật trạng thái bài viết.";
            header("Location: index.php?controller=adminblog");
            exit;
        }

        if ($id <= 0 || !in_array($newStatus, [0, 1, 2], true)) {
            $_SESSION['error'] = "Dữ liệu trạng thái bài viết không hợp lệ.";
            header("Location: index.php?controller=adminblog");
            exit;
        }

        $post = $this->model->getBlogById($id);

        if (!$post) {
            $_SESSION['error'] = "Không tìm thấy bài viết.";
            header("Location: index.php?controller=adminblog");
            exit;
        }

        $updatePublishDate = ((int)$post['TrangThai'] !== 1 && $newStatus === 1);

        if ($this->model->updateStatus($id, $newStatus, $updatePublishDate)) {
            if ($newStatus === 1) {
                $_SESSION['success'] = "Đã duyệt và đăng bài viết.";
            } elseif ($newStatus === 2) {
                $_SESSION['success'] = "Đã ẩn bài viết.";
            } else {
                $_SESSION['success'] = "Đã chuyển bài viết về nháp/chờ duyệt.";
            }
        } else {
            $_SESSION['error'] = "Trạng thái không thay đổi hoặc cập nhật thất bại.";
        }

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=adminblog"));
        exit;
    }

    private function validateBlogData($data, $id = 0)
    {
        if ($data['MaBaiViet'] === '') {
            return "Mã bài viết không được để trống.";
        }

        if (mb_strlen($data['MaBaiViet'], 'UTF-8') > 20) {
            return "Mã bài viết không được vượt quá 20 ký tự.";
        }

        if ($this->model->isBlogCodeExists($data['MaBaiViet'], $id)) {
            return "Mã bài viết đã tồn tại.";
        }

        if ($data['TieuDe'] === '') {
            return "Tiêu đề bài viết không được để trống.";
        }

        if (mb_strlen($data['TieuDe'], 'UTF-8') > 250) {
            return "Tiêu đề bài viết không được vượt quá 250 ký tự.";
        }

        if (mb_strlen($data['TomTat'], 'UTF-8') > 500) {
            return "Tóm tắt bài viết không được vượt quá 500 ký tự.";
        }

        if (!in_array((int)$data['TrangThai'], [0, 1, 2], true)) {
            return "Trạng thái bài viết không hợp lệ.";
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
                'message' => "Tải ảnh bài viết thất bại."
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
                'message' => "Ảnh bài viết chỉ hỗ trợ JPG, JPEG, PNG hoặc WEBP."
            ];
        }

        if ($size <= 0 || $size > 3 * 1024 * 1024) {
            return [
                'success' => false,
                'file' => null,
                'message' => "Dung lượng ảnh bài viết tối đa là 3MB."
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

        $fileName = 'blog_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (!move_uploaded_file($tmpName, $targetPath)) {
            return [
                'success' => false,
                'file' => null,
                'message' => "Không thể lưu ảnh bài viết."
            ];
        }

        return [
            'success' => true,
            'file' => $fileName,
            'message' => null
        ];
    }

    private function isLocalImage($image)
    {
        $image = trim((string)$image);

        if ($image === '') {
            return false;
        }

        if (preg_match('/^https?:\/\//i', $image)) {
            return false;
        }

        return basename($image) === $image;
    }

    private function canManagePost($post, $user, $role)
    {
        if ($role === 'ADMIN') {
            return true;
        }

        return (int)($post['CreatedById'] ?? 0) === (int)($user['TaiKhoanId'] ?? 0);
    }

    private function redirectToForm($id = 0)
    {
        if ($id > 0) {
            header("Location: index.php?controller=adminblog&action=edit&id=" . (int)$id);
        } else {
            header("Location: index.php?controller=adminblog&action=edit");
        }

        exit;
    }

    private function getHeaderTitle($status)
    {
        return match ($status) {
            'draft' => 'Bài viết nháp / chờ duyệt',
            'hidden' => 'Bài viết đã ẩn',
            'all' => 'Tất cả bài viết',
            default => 'Quản lý bài viết',
        };
    }
}