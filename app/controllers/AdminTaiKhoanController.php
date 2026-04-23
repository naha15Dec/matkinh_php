<?php
require_once BASE_PATH . '/app/models/AdminTaiKhoanModel.php';

class AdminTaiKhoanController {
    private $model;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new AdminTaiKhoanModel($pdo);
        
        // Bảo vệ: Chỉ Admin mới được vào module này
        if (!isset($_SESSION['LoginInformation']) || strtoupper(trim($_SESSION['LoginInformation']['MaVaiTro'] ?? '')) !== 'ADMIN') {
            $_SESSION['error'] = "Chỉ Quản trị viên mới có quyền truy cập module này.";
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    // Trang danh sách tài khoản
    public function index() {
    $pdo = $this->pdo; 

    $keyword = $_GET['keyword'] ?? '';
    $role = $_GET['role'] ?? '';
    
    $accounts = $this->model->getAccounts($keyword, $role);
    $roles = $this->model->getAllRoles();
    
    // Đảm bảo các biến quyền cũng có sẵn cho layout/view
    $sessionAccount = $_SESSION['LoginInformation'];
    $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));
    $isAdmin = ($roleCode === 'ADMIN');
    $isStaff = ($roleCode === 'STAFF');
    $isShipper = ($roleCode === 'SHIPPER');

    $title = "Quản lý tài khoản";
    $viewContent = BASE_PATH . '/views/admin/account_manager.php';
    require_once BASE_PATH . '/views/admin/layout.php';
}

    // Trang chi tiết tài khoản (Xử lý các form Update, Change Pass, Role)
    public function detail() {
        $pdo = $this->pdo;
        
        $id = $_GET['id'] ?? 0;
        $account = $this->model->getAccountById($id);
        $roles = $this->model->getAllRoles();

        if (!$account) {
            $_SESSION['error'] = "Không tìm thấy tài khoản.";
            header("Location: index.php?controller=admintaikhoan");
            exit;
        }

        $title = "Chi tiết tài khoản: " . $account['TenDangNhap'];
        $viewContent = BASE_PATH . '/views/admin/account_detail.php';
        require_once BASE_PATH . '/views/admin/layout.php';
    }

    // Khóa/Mở khóa tài khoản
    public function toggleActive() {
        $id = $_POST['id'] ?? 0;
        $currentAdminId = $_SESSION['LoginInformation']['TaiKhoanId'];

        if ($id == $currentAdminId) {
            $_SESSION['error'] = "Bạn không thể tự khóa chính mình.";
        } else {
            if ($this->model->toggleActive($id)) {
                $_SESSION['success'] = "Cập nhật trạng thái thành công.";
            }
        }
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=admintaikhoan"));
        exit;
    }

    // SỬA TẠI ĐÂY: Đổi mật khẩu dùng chuẩn HashPassword (Bcrypt) của bạn
    public function changePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['TaiKhoanId'] ?? 0;
            $newPassword = $_POST['NewPassword'] ?? '';
            $confirmPassword = $_POST['ConfirmPassword'] ?? '';

            if (strlen($newPassword) < 6) {
                $_SESSION['error'] = "Mật khẩu mới phải từ 6 ký tự trở lên.";
            } elseif ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = "Xác nhận mật khẩu không khớp.";
            } else {
                // Dùng Bcrypt của bạn
                $newHash = HashPassword::hash($newPassword);
                if ($this->model->changePassword($id, $newHash)) {
                    $_SESSION['success'] = "Đã đổi mật khẩu cho tài khoản.";
                }
            }
            header("Location: index.php?controller=admintaikhoan&action=detail&id=" . $id);
            exit;
        }
    }

    // Cập nhật phân quyền
    public function updateRole() {
        $id = $_POST['TaiKhoanId'] ?? 0;
        $roleId = $_POST['VaiTroId'] ?? 0;
        $currentAdminId = $_SESSION['LoginInformation']['TaiKhoanId'];

        if ($id == $currentAdminId) {
            $_SESSION['error'] = "Bạn không thể tự đổi quyền của mình.";
        } else {
            if ($this->model->updateRole($id, $roleId)) {
                $_SESSION['success'] = "Cập nhật phân quyền thành công.";
            }
        }
        header("Location: index.php?controller=admintaikhoan&action=detail&id=" . $id);
        exit;
    }
}