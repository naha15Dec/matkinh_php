<?php
require_once BASE_PATH . '/app/models/AdminTaiKhoanModel.php';
require_once BASE_PATH . '/app/helpers/HashPassword.php';

class AdminTaiKhoanController
{
    private $model;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new AdminTaiKhoanModel($pdo);

        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if (!$sessionAccount || $roleCode !== 'ADMIN') {
            $_SESSION['error'] = "Chỉ Quản trị viên mới có quyền truy cập module này.";
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index()
    {
        $pdo = $this->pdo;

        $keyword = trim($_GET['keyword'] ?? '');
        $role = trim($_GET['role'] ?? '');

        $accounts = $this->model->getAccounts($keyword, $role);
        $roles = $this->model->getAllRoles();

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $isAdmin = $roleCode === 'ADMIN';
        $isStaff = $roleCode === 'STAFF';
        $isShipper = $roleCode === 'SHIPPER';

        $displayName = $sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Admin';

        $title = "Quản lý tài khoản";
        $viewContent = BASE_PATH . '/views/admin/account_manager.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function detail()
{
    $pdo = $this->pdo;

    $id = (int)($_GET['id'] ?? 0);

    if ($id <= 0) {
        $_SESSION['error'] = "Mã tài khoản không hợp lệ.";
        header("Location: index.php?controller=admintaikhoan");
        exit;
    }

    $accountDetail = $this->model->getAccountById($id);
    $roles = $this->model->getAllRoles();

    if (!$accountDetail) {
        $_SESSION['error'] = "Không tìm thấy tài khoản.";
        header("Location: index.php?controller=admintaikhoan");
        exit;
    }

    $sessionAccount = $_SESSION['LoginInformation'];
    $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

    $isAdmin = $roleCode === 'ADMIN';
    $isStaff = $roleCode === 'STAFF';
    $isShipper = $roleCode === 'SHIPPER';

    $displayName = $sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Admin';

    $title = "Chi tiết tài khoản: " . ($accountDetail['TenDangNhap'] ?? '');
    $viewContent = BASE_PATH . '/views/admin/account_detail.php';

    require_once BASE_PATH . '/views/admin/layout.php';
}

    public function toggleActive()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=admintaikhoan");
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        $currentAdminId = (int)($_SESSION['LoginInformation']['TaiKhoanId'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = "Tài khoản không hợp lệ.";
            $this->redirectBack();
        }

        $account = $this->model->getAccountById($id);

        if (!$account) {
            $_SESSION['error'] = "Không tìm thấy tài khoản.";
            $this->redirectBack();
        }

        $accountRoleCode = strtoupper(trim($account['MaVaiTro'] ?? ''));
        $accountIsActive = (int)($account['IsActive'] ?? 0);

        if ($id === $currentAdminId) {
            $_SESSION['error'] = "Bạn không thể tự khóa chính mình.";
            $this->redirectBack();
        }

        if (
            $accountRoleCode === 'ADMIN'
            && $accountIsActive === 1
            && $this->model->countActiveAdmins() <= 1
        ) {
            $_SESSION['error'] = "Không thể khóa quản trị viên cuối cùng của hệ thống.";
            $this->redirectBack();
        }

        if ($this->model->toggleActive($id)) {
            $_SESSION['success'] = $accountIsActive === 1
                ? "Đã khóa tài khoản thành công."
                : "Đã mở khóa tài khoản thành công.";
        } else {
            $_SESSION['error'] = "Cập nhật trạng thái thất bại hoặc dữ liệu không thay đổi.";
        }

        $this->redirectBack();
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=admintaikhoan");
            exit;
        }

        $id = (int)($_POST['TaiKhoanId'] ?? 0);
        $newPassword = trim($_POST['NewPassword'] ?? '');
        $confirmPassword = trim($_POST['ConfirmPassword'] ?? '');

        if ($id <= 0) {
            $_SESSION['error'] = "Tài khoản không hợp lệ.";
            header("Location: index.php?controller=admintaikhoan");
            exit;
        }

        $account = $this->model->getAccountById($id);

        if (!$account) {
            $_SESSION['error'] = "Không tìm thấy tài khoản.";
            header("Location: index.php?controller=admintaikhoan");
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = "Mật khẩu mới phải từ 6 ký tự trở lên.";
        } elseif ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = "Xác nhận mật khẩu không khớp.";
        } else {
            $newHash = HashPassword::hash($newPassword);

            if ($this->model->changePassword($id, $newHash)) {
                $_SESSION['success'] = "Đã đổi mật khẩu cho tài khoản.";
            } else {
                $_SESSION['error'] = "Đổi mật khẩu thất bại hoặc dữ liệu không thay đổi.";
            }
        }

        header("Location: index.php?controller=admintaikhoan&action=detail&id=" . $id);
        exit;
    }

    public function updateRole()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=admintaikhoan");
            exit;
        }

        $id = (int)($_POST['TaiKhoanId'] ?? 0);
        $roleId = (int)($_POST['VaiTroId'] ?? 0);
        $currentAdminId = (int)($_SESSION['LoginInformation']['TaiKhoanId'] ?? 0);

        if ($id <= 0 || $roleId <= 0) {
            $_SESSION['error'] = "Dữ liệu phân quyền không hợp lệ.";
            header("Location: index.php?controller=admintaikhoan");
            exit;
        }

        $account = $this->model->getAccountById($id);

        if (!$account) {
            $_SESSION['error'] = "Không tìm thấy tài khoản.";
            header("Location: index.php?controller=admintaikhoan");
            exit;
        }

        $newRole = $this->model->getRoleById($roleId);

        if (!$newRole) {
            $_SESSION['error'] = "Vai trò không tồn tại hoặc đã bị khóa.";
            header("Location: index.php?controller=admintaikhoan&action=detail&id=" . $id);
            exit;
        }

        $oldRoleCode = strtoupper(trim($account['MaVaiTro'] ?? ''));
        $newRoleCode = strtoupper(trim($newRole['MaVaiTro'] ?? ''));
        $accountIsActive = (int)($account['IsActive'] ?? 0);

        if ($id === $currentAdminId) {
            $_SESSION['error'] = "Bạn không thể tự đổi quyền của mình.";
        } elseif (
            $oldRoleCode === 'ADMIN'
            && $newRoleCode !== 'ADMIN'
            && $accountIsActive === 1
            && $this->model->countActiveAdmins() <= 1
        ) {
            $_SESSION['error'] = "Không thể hạ quyền quản trị viên cuối cùng của hệ thống.";
        } else {
            if ((int)$account['VaiTroId'] === $roleId) {
                $_SESSION['error'] = "Tài khoản đã thuộc vai trò này, không có thay đổi mới.";
            } elseif ($this->model->updateRole($id, $roleId)) {
                $_SESSION['success'] = "Cập nhật phân quyền thành công.";
            } else {
                $_SESSION['error'] = "Cập nhật phân quyền thất bại hoặc dữ liệu không thay đổi.";
            }
        }

        header("Location: index.php?controller=admintaikhoan&action=detail&id=" . $id);
        exit;
    }

    public function updateInfo()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: index.php?controller=admintaikhoan");
        exit;
    }

    $id = (int)($_POST['TaiKhoanId'] ?? 0);

    if ($id <= 0) {
        $_SESSION['error'] = "Tài khoản không hợp lệ.";
        header("Location: index.php?controller=admintaikhoan");
        exit;
    }

    $account = $this->model->getAccountById($id);

    if (!$account) {
        $_SESSION['error'] = "Không tìm thấy tài khoản.";
        header("Location: index.php?controller=admintaikhoan");
        exit;
    }

    $hoTen = trim($_POST['HoTen'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $soDienThoai = trim($_POST['SoDienThoai'] ?? '');
    $diaChi = trim($_POST['DiaChi'] ?? '');

    $email = $email !== '' ? $email : null;
    $soDienThoai = $soDienThoai !== '' ? $soDienThoai : null;
    $diaChi = $diaChi !== '' ? $diaChi : null;

    if ($hoTen === '') {
        $_SESSION['error'] = "Họ tên không được để trống.";
    } elseif ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email không đúng định dạng.";
    } elseif ($email !== null && $this->model->isEmailExists($email, $id)) {
        $_SESSION['error'] = "Email đã được sử dụng bởi tài khoản khác.";
    } elseif ($soDienThoai !== null && $this->model->isPhoneExists($soDienThoai, $id)) {
        $_SESSION['error'] = "Số điện thoại đã được sử dụng bởi tài khoản khác.";
    } else {
        $data = [
            'HoTen' => $hoTen,
            'Email' => $email,
            'SoDienThoai' => $soDienThoai,
            'DiaChi' => $diaChi
        ];

        if ($this->model->updateInfo($id, $data)) {
            $_SESSION['success'] = "Cập nhật thông tin tài khoản thành công.";

            if ($id === (int)($_SESSION['LoginInformation']['TaiKhoanId'] ?? 0)) {
                $_SESSION['LoginInformation']['HoTen'] = $hoTen;
                $_SESSION['LoginInformation']['Email'] = $email;
                $_SESSION['LoginInformation']['SoDienThoai'] = $soDienThoai;
                $_SESSION['LoginInformation']['DiaChi'] = $diaChi;
            }
        } else {
            $_SESSION['error'] = "Thông tin không thay đổi hoặc cập nhật thất bại.";
        }
    }

    header("Location: index.php?controller=admintaikhoan&action=detail&id=" . $id);
    exit;
}

    private function redirectBack()
    {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "index.php?controller=admintaikhoan"));
        exit;
    }
}