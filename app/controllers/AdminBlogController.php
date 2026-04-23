<?php
require_once BASE_PATH . '/app/models/AdminBlogModel.php';

class AdminBlogController {
    private $model;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new AdminBlogModel($pdo);
        
        // Kiểm tra đăng nhập và quyền (Admin hoặc Staff mới được vào)
        if (!isset($_SESSION['LoginInformation']) || !in_array(strtoupper($_SESSION['LoginInformation']['MaVaiTro']), ['ADMIN', 'STAFF'])) {
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index() {
        $pdo = $this->pdo; 
        $user = $_SESSION['LoginInformation'];
        $role = strtoupper($user['MaVaiTro']);

        $status = $_GET['status'] ?? 'published';
        $keyword = $_GET['keyword'] ?? '';
        
        // LOGIC PHÂN QUYỀN: Admin truyền null (thấy hết), Staff truyền TaiKhoanId (chỉ thấy bài mình)
        $userId = ($role === 'ADMIN') ? null : $user['TaiKhoanId'];
        $posts = $this->model->getBlogs($status, $keyword, $userId);
        
        $title = $this->getHeaderTitle($status);
        $viewContent = BASE_PATH . '/views/admin/blog_index.php';
        require_once BASE_PATH . '/views/admin/layout.php';
    }

    // HIỂN THỊ FORM VIẾT BÀI / SỬA BÀI
    public function edit() {
        $pdo = $this->pdo;
        $id = $_GET['id'] ?? 0;
        
        $post = null;
        if ($id > 0) {
            $post = $this->model->getBlogById($id);
            // Bảo mật: Nếu không phải Admin thì không được sửa bài của người khác
            if (!$post || (strtoupper($_SESSION['LoginInformation']['MaVaiTro']) !== 'ADMIN' && $post['CreatedById'] != $_SESSION['LoginInformation']['TaiKhoanId'])) {
                $_SESSION['error'] = "Bạn không có quyền chỉnh sửa bài viết này.";
                header("Location: index.php?controller=adminblog");
                exit;
            }
        }

        $title = $id > 0 ? "Chỉnh sửa bài viết" : "Viết bài mới";
        $viewContent = BASE_PATH . '/views/admin/blog_form.php';
        require_once BASE_PATH . '/views/admin/layout.php';
    }

    // XỬ LÝ LƯU DỮ LIỆU (ADD & UPDATE)
    public function save() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['BaiVietId'] ?? 0;
            $user = $_SESSION['LoginInformation'];
            $role = strtoupper($user['MaVaiTro']);

            // Kiểm tra bài viết tồn tại nếu là trường hợp Update
            if ($id > 0) {
                $post = $this->model->getBlogById($id);
                if (!$post) {
                    $_SESSION['error'] = "Không tìm thấy bài viết.";
                    header("Location: index.php?controller=adminblog");
                    exit;
                }
                // Bảo mật: STAFF chỉ được sửa bài của chính mình
                if ($role !== 'ADMIN' && $post['CreatedById'] != $user['TaiKhoanId']) {
                    $_SESSION['error'] = "Bạn không có quyền chỉnh sửa bài viết này.";
                    header("Location: index.php?controller=adminblog");
                    exit;
                }
            }

            $data = [
                'TieuDe' => $_POST['TieuDe'] ?? '',
                'TomTat' => $_POST['TomTat'] ?? '',
                'NoiDung' => $_POST['NoiDung'] ?? '',
                'AnhDaiDien' => $_POST['CurrentAnhDaiDien'] ?? '' 
            ];

            // Xử lý Upload ảnh
            if (isset($_FILES['imageAvatar']) && $_FILES['imageAvatar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = BASE_PATH . '/public/images/';
                // Tạo tên file duy nhất để tránh trùng
                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9.]/', '_', $_FILES['imageAvatar']['name']);
                if (move_uploaded_file($_FILES['imageAvatar']['tmp_name'], $uploadDir . $fileName)) {
                    $data['AnhDaiDien'] = $fileName;
                }
            }

            if ($id > 0) {
                // CẬP NHẬT
                $this->model->updateBlog($id, $data);
                $_SESSION['success'] = "Cập nhật bài viết thành công.";
            } else {
                // THÊM MỚI
                $data['MaBaiViet'] = date("mdHis");
                $data['CreatedById'] = $user['TaiKhoanId'];
                $data['TrangThai'] = 0; // Mặc định là Nháp
                $this->model->createBlog($data);
                $_SESSION['success'] = "Đã lưu bản nháp thành công.";
            }

            // Quay lại trang trước đó
            $redirectUrl = $_SERVER['HTTP_REFERER'] ?? 'index.php?controller=adminblog';
            header("Location: " . $redirectUrl);
            exit;
        }
    } // Đã đóng ngoặc đúng vị trí ở đây

    public function activate() {
        // Chỉ ADMIN mới có quyền Duyệt bài
        if (strtoupper($_SESSION['LoginInformation']['MaVaiTro']) !== 'ADMIN') {
            $_SESSION['error'] = "Chỉ Admin mới có quyền duyệt bài.";
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $currentStatus = $_POST['status'] ?? 'published';
            $post = $this->model->getBlogById($id);

            if ($post) {
                $newStatus = 1; // Mặc định sang Published
                $updateDate = false;

                if ($currentStatus === 'draft' || empty($post['NgayDang'])) {
                    $updateDate = true; // Bài nháp hoặc chưa có ngày đăng -> Set ngày đăng = hiện tại
                } elseif ($currentStatus === 'published') {
                    $newStatus = 2; // Đang đăng -> Chuyển thành Ẩn
                }

                $this->model->updateStatus($id, $newStatus, $updateDate);
                $_SESSION['success'] = "Đã cập nhật trạng thái bài viết.";
            }
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;
            $post = $this->model->getBlogById($id);

            if (!$post) {
                $_SESSION['error'] = "Không tìm thấy bài viết.";
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }

            // Kiểm tra quyền xóa (Admin hoặc chính người tạo)
            if (strtoupper($_SESSION['LoginInformation']['MaVaiTro']) === 'ADMIN' || $post['CreatedById'] == $_SESSION['LoginInformation']['TaiKhoanId']) {
                if ($this->model->delete($id)) {
                    $_SESSION['success'] = "Xóa bài viết thành công.";
                }
            } else {
                $_SESSION['error'] = "Bạn không có quyền xóa bài viết này.";
            }
            
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    private function getHeaderTitle($status) {
        switch ($status) {
            case 'draft': return "Bài viết nháp";
            case 'hidden': return "Bài viết đã ẩn";
            default: return "Bài viết đã đăng";
        }
    }
} 