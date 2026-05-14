<?php
require_once BASE_PATH . '/app/models/AdminTaiKhoanModel.php';
require_once BASE_PATH . '/app/helpers/HashPassword.php';

class AdminTaiKhoanController
{
    private $model;
    private $pdo;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdo = $pdo;
        $this->model = new AdminTaiKhoanModel($pdo);

        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if (!$sessionAccount || $roleCode !== 'ADMIN') {
            $_SESSION['error'] = "Chỉ Quản trị viên mới có quyền truy cập module này.";
            header("Location: /BanMatKinh/public/index.php?controller=dashboard");
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
            header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan");
            exit;
        }

        $accountDetail = $this->model->getAccountById($id);
        $roles = $this->model->getAllRoles();

        if (!$accountDetail) {
            $_SESSION['error'] = "Không tìm thấy tài khoản.";
            header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan");
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
            header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan");
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

    public function updateInfo()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Phương thức cập nhật không hợp lệ.";
            header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan");
            exit;
        }

        $id = (int)($_POST['TaiKhoanId'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = "Tài khoản không hợp lệ.";
            header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan");
            exit;
        }

        $account = $this->model->getAccountById($id);

        if (!$account) {
            $_SESSION['error'] = "Không tìm thấy tài khoản cần cập nhật.";
            header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan");
            exit;
        }

        $hoTen = trim($_POST['HoTen'] ?? '');
        $email = strtolower(trim($_POST['Email'] ?? ''));
        $soDienThoai = trim($_POST['SoDienThoai'] ?? '');
        $diaChi = trim($_POST['DiaChi'] ?? '');

        if ($hoTen === '') {
            $_SESSION['error'] = "Họ tên không được để trống.";
            $this->redirectToDetail($id);
        }

        if (mb_strlen($hoTen, 'UTF-8') > 150) {
            $_SESSION['error'] = "Họ tên không được vượt quá 150 ký tự.";
            $this->redirectToDetail($id);
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Email không đúng định dạng.";
            $this->redirectToDetail($id);
        }

        if ($email !== '' && mb_strlen($email, 'UTF-8') > 100) {
            $_SESSION['error'] = "Email không được vượt quá 100 ký tự.";
            $this->redirectToDetail($id);
        }

        if ($email !== '' && $this->model->isEmailExists($email, $id)) {
            $_SESSION['error'] = "Email đã được sử dụng bởi tài khoản khác.";
            $this->redirectToDetail($id);
        }

        if ($soDienThoai !== '') {
            $soDienThoai = $this->normalizePhone($soDienThoai);

            if (!$this->isValidVietnamPhone($soDienThoai)) {
                $_SESSION['error'] = "Số điện thoại không hợp lệ. Ví dụ đúng: 0912345678.";
                $this->redirectToDetail($id);
            }

            if ($this->model->isPhoneExists($soDienThoai, $id)) {
                $_SESSION['error'] = "Số điện thoại đã được sử dụng bởi tài khoản khác.";
                $this->redirectToDetail($id);
            }
        }

        if ($diaChi !== '' && mb_strlen($diaChi, 'UTF-8') > 255) {
            $_SESSION['error'] = "Địa chỉ không được vượt quá 255 ký tự.";
            $this->redirectToDetail($id);
        }

        $data = [
            'HoTen' => $hoTen,
            'Email' => $email !== '' ? $email : null,
            'SoDienThoai' => $soDienThoai !== '' ? $soDienThoai : null,
            'DiaChi' => $diaChi !== '' ? $diaChi : null
        ];

        $result = $this->model->updateInfo($id, $data);

        if ($result === true) {
            $_SESSION['success'] = "Cập nhật thông tin tài khoản thành công.";

            if ($id === (int)($_SESSION['LoginInformation']['TaiKhoanId'] ?? 0)) {
                $_SESSION['LoginInformation']['HoTen'] = $data['HoTen'];
                $_SESSION['LoginInformation']['Email'] = $data['Email'];
                $_SESSION['LoginInformation']['SoDienThoai'] = $data['SoDienThoai'];
                $_SESSION['LoginInformation']['DiaChi'] = $data['DiaChi'];
            }
        } else {
            $_SESSION['error'] = "Cập nhật thất bại: " . $result;
        }

        $this->redirectToDetail($id);
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan");
            exit;
        }

        $id = (int)($_POST['TaiKhoanId'] ?? 0);
        $newPassword = trim($_POST['NewPassword'] ?? '');
        $confirmPassword = trim($_POST['ConfirmPassword'] ?? '');

        if ($id <= 0) {
            $_SESSION['error'] = "Tài khoản không hợp lệ.";
            header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan");
            exit;
        }

        $account = $this->model->getAccountById($id);

        if (!$account) {
            $_SESSION['error'] = "Không tìm thấy tài khoản.";
            header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan");
            exit;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = "Mật khẩu mới phải từ 6 ký tự trở lên.";
        } elseif (strlen($newPassword) > 72) {
            $_SESSION['error'] = "Mật khẩu mới không được vượt quá 72 ký tự.";
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

        $this->redirectToDetail($id);
    }

    public function updateRole()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan");
            exit;
        }

        $id = (int)($_POST['TaiKhoanId'] ?? 0);
        $roleId = (int)($_POST['VaiTroId'] ?? 0);
        $currentAdminId = (int)($_SESSION['LoginInformation']['TaiKhoanId'] ?? 0);

        if ($id <= 0 || $roleId <= 0) {
            $_SESSION['error'] = "Dữ liệu phân quyền không hợp lệ.";
            header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan");
            exit;
        }

        $account = $this->model->getAccountById($id);

        if (!$account) {
            $_SESSION['error'] = "Không tìm thấy tài khoản.";
            header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan");
            exit;
        }

        $newRole = $this->model->getRoleById($roleId);

        if (!$newRole) {
            $_SESSION['error'] = "Vai trò không tồn tại hoặc đã bị khóa.";
            $this->redirectToDetail($id);
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

        $this->redirectToDetail($id);
    }

    private function normalizePhone($phone)
    {
        $phone = trim((string)$phone);
        $phone = preg_replace('/[\s\.\-\(\)]/', '', $phone);

        if (str_starts_with($phone, '+84')) {
            $phone = '0' . substr($phone, 3);
        } elseif (str_starts_with($phone, '84') && strlen($phone) === 11) {
            $phone = '0' . substr($phone, 2);
        }

        return $phone;
    }

    private function isValidVietnamPhone($phone)
    {
        return (bool)preg_match('/^0(3|5|7|8|9)[0-9]{8}$/', $phone);
    }

    private function redirectToDetail($id)
    {
        header("Location: /BanMatKinh/public/index.php?controller=admintaikhoan&action=detail&id=" . (int)$id);
        exit;
    }

    private function redirectBack()
    {
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? "/BanMatKinh/public/index.php?controller=admintaikhoan"));
        exit;
    }
}