<?php
require_once BASE_PATH . '/app/models/TaiKhoanModel.php';
require_once BASE_PATH . '/app/models/HomeModel.php';
require_once BASE_PATH . '/app/helpers/HashPassword.php';

class TaiKhoanController
{
    private $pdo;
    private $accountModel;
    private $homeModel;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdo = $pdo;
        $this->accountModel = new TaiKhoanModel($pdo);
        $this->homeModel = new HomeModel($pdo);
    }

    // Alias để route cũ action=login vẫn chạy
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->loginPost();
            return;
        }

        $this->loginView();
    }

    public function loginView()
    {
        $this->renderLogin();
    }

    public function loginPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }

        $username = trim($_POST['Username'] ?? '');
        $password = $_POST['Password'] ?? '';
        $errors = [];

        if ($username === '' || $password === '') {
            $errors['Global'][] = "Vui lòng nhập đầy đủ thông tin.";
            $this->renderLogin($errors);
            return;
        }

        if (mb_strlen($username, 'UTF-8') > 100) {
            $errors['Global'][] = "Thông tin đăng nhập không hợp lệ.";
            $this->renderLogin($errors);
            return;
        }

        $account = $this->accountModel->getAccountByUsername($username);

        if (!$account || empty($account['MatKhauHash']) || !HashPassword::verify($password, $account['MatKhauHash'])) {
            $errors['Global'][] = "Sai tài khoản hoặc mật khẩu.";
            $this->renderLogin($errors);
            return;
        }

        if (empty($account['IsActive'])) {
            $errors['Global'][] = "Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.";
            $this->renderLogin($errors);
            return;
        }

        $this->accountModel->updateLastLogin((int)$account['TaiKhoanId']);

        $freshAccount = $this->accountModel->getAccountById((int)$account['TaiKhoanId']);
        $_SESSION['LoginInformation'] = $freshAccount ?: $account;

        $_SESSION['success'] = "Chào mừng " . htmlspecialchars($account['HoTen'] ?? $account['TenDangNhap'], ENT_QUOTES, 'UTF-8') . " đã quay trở lại!";

        $maVaiTro = strtoupper(trim($account['MaVaiTro'] ?? ''));

        if (in_array($maVaiTro, ['ADMIN', 'STAFF', 'SHIPPER'], true)) {
            header("Location: index.php?controller=dashboard");
            exit;
        }

        header("Location: index.php?controller=home");
        exit;
    }

    // Alias để route cũ action=register vẫn chạy
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->registerPost();
            return;
        }

        $this->registerView();
    }

    public function registerView()
    {
        $this->renderRegister();
    }

    public function registerPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=taikhoan&action=register");
            exit;
        }

        $rvm = [
            'Username'    => trim($_POST['Username'] ?? ''),
            'Password'    => $_POST['Password'] ?? '',
            'ConfirmPass' => $_POST['ConfirmPassword'] ?? '',
            'FirstName'   => trim($_POST['FirstName'] ?? ''),
            'LastName'    => trim($_POST['LastName'] ?? ''),
            'Mobile'      => trim($_POST['Mobile'] ?? ''),
            'Email'       => strtolower(trim($_POST['Email'] ?? '')),
            'Sex'         => trim($_POST['Sex'] ?? ''),
            'DateOfBirth' => trim($_POST['DateOfBirth'] ?? ''),
            'Address'     => trim($_POST['Address'] ?? '')
        ];

        $errors = [];

        if (mb_strlen($rvm['Username'], 'UTF-8') < 4) {
            $errors['Username'] = "Tên đăng nhập từ 4 ký tự.";
        } elseif (mb_strlen($rvm['Username'], 'UTF-8') > 50) {
            $errors['Username'] = "Tên đăng nhập không được vượt quá 50 ký tự.";
        } elseif (!preg_match('/^[A-Za-z0-9_.-]+$/', $rvm['Username'])) {
            $errors['Username'] = "Tên đăng nhập chỉ nên gồm chữ, số, dấu gạch dưới, gạch ngang hoặc dấu chấm.";
        }

        if (strlen($rvm['Password']) < 6) {
            $errors['Password'] = "Mật khẩu tối thiểu 6 ký tự.";
        }

        if ($rvm['Password'] !== $rvm['ConfirmPass']) {
            $errors['ConfirmPassword'] = "Mật khẩu không khớp.";
        }

        if ($rvm['Mobile'] === '') {
            $errors['Mobile'] = "Vui lòng nhập số điện thoại.";
        } elseif (mb_strlen($rvm['Mobile'], 'UTF-8') > 20) {
            $errors['Mobile'] = "Số điện thoại không được vượt quá 20 ký tự.";
        }

        if ($rvm['Email'] !== '') {
            if (!filter_var($rvm['Email'], FILTER_VALIDATE_EMAIL)) {
                $errors['Email'] = "Email không đúng định dạng.";
            } elseif (mb_strlen($rvm['Email'], 'UTF-8') > 100) {
                $errors['Email'] = "Email không được vượt quá 100 ký tự.";
            }
        }

        if ($rvm['FirstName'] === '' && $rvm['LastName'] === '') {
            $errors['FullName'] = "Vui lòng nhập họ tên.";
        }

        $fullName = trim($rvm['LastName'] . ' ' . $rvm['FirstName']);

        if ($fullName === '') {
            $fullName = "Khách hàng";
        }

        if (mb_strlen($fullName, 'UTF-8') > 150) {
            $errors['FullName'] = "Họ tên không được vượt quá 150 ký tự.";
        }

        if (mb_strlen($rvm['Address'], 'UTF-8') > 255) {
            $errors['Address'] = "Địa chỉ không được vượt quá 255 ký tự.";
        }

        if ($rvm['DateOfBirth'] !== '' && !$this->isValidDate($rvm['DateOfBirth'])) {
            $errors['DateOfBirth'] = "Ngày sinh không hợp lệ.";
        }

        if (empty($errors)) {
            if ($this->accountModel->checkUsernameExists($rvm['Username'])) {
                $errors['Username'] = "Tên đăng nhập đã tồn tại.";
            }

            if ($this->accountModel->checkPhoneExists($rvm['Mobile'])) {
                $errors['Mobile'] = "Số điện thoại đã được sử dụng.";
            }

            if ($rvm['Email'] !== '' && $this->accountModel->checkEmailExists($rvm['Email'])) {
                $errors['Email'] = "Email đã được sử dụng.";
            }
        }

        if (!empty($errors)) {
            $this->renderRegister($errors, $rvm);
            return;
        }

        try {
            $this->pdo->beginTransaction();

            $roleId = $this->accountModel->getCustomerRoleId();

            $maKH = $this->generateCustomerCode();

            $gioiTinh = $this->parseGender($rvm['Sex']);
            $email = $rvm['Email'] !== '' ? $rvm['Email'] : null;
            $birthday = $rvm['DateOfBirth'] !== '' ? $rvm['DateOfBirth'] : null;
            $address = $rvm['Address'] !== '' ? $rvm['Address'] : null;

            $this->accountModel->createCustomer([
                'MaKhachHang' => $maKH,
                'HoTen'       => $fullName,
                'Email'       => $email,
                'SoDienThoai' => $rvm['Mobile'],
                'GioiTinh'    => $gioiTinh,
                'NgaySinh'    => $birthday,
                'DiaChi'      => $address,
                'GhiChu'      => "Đăng ký từ Web"
            ]);

            $newAccount = $this->accountModel->createAccount([
                'VaiTroId'    => $roleId,
                'TenDangNhap' => $rvm['Username'],
                'MatKhauHash' => HashPassword::hash($rvm['Password']),
                'HoTen'       => $fullName,
                'Email'       => $email,
                'SoDienThoai' => $rvm['Mobile'],
                'GioiTinh'    => $gioiTinh,
                'NgaySinh'    => $birthday,
                'DiaChi'      => $address
            ]);

            if (!$newAccount) {
                throw new Exception("Không thể tạo tài khoản.");
            }

            $this->pdo->commit();

            $_SESSION['LoginInformation'] = $newAccount;
            $_SESSION['success'] = "Đăng ký thành viên thành công! Chào mừng " . htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') . ".";

            header("Location: index.php?controller=home");
            exit;

        } catch (Exception $ex) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $errors['Global'][] = "Lỗi hệ thống: " . $ex->getMessage();
            $this->renderRegister($errors, $rvm);
        }
    }

    public function logout()
    {
        $this->logoutAccount();
    }

    public function logoutAccount()
    {
        unset($_SESSION['LoginInformation']);
        unset($_SESSION['ShoppingCart']);

        $_SESSION['success'] = "Bạn đã đăng xuất thành công.";

        header("Location: index.php?controller=home");
        exit;
    }

    private function renderLogin($errors = [])
    {
        $storeInfo = $this->homeModel->getStoreInfo();

        $title = "Đăng nhập";
        $viewContent = BASE_PATH . '/views/client/login.php';

        require BASE_PATH . '/views/client/layout.php';
    }

    private function renderRegister($errors = [], $rvm = [])
    {
        $storeInfo = $this->homeModel->getStoreInfo();

        $title = "Đăng ký";
        $viewContent = BASE_PATH . '/views/client/register.php';

        require BASE_PATH . '/views/client/layout.php';
    }

    private function parseGender($sex)
    {
        $sex = mb_strtolower(trim((string)$sex), 'UTF-8');

        if ($sex === 'nam' || $sex === '1' || $sex === 'male') {
            return 1;
        }

        if ($sex === 'nữ' || $sex === 'nu' || $sex === '0' || $sex === 'female') {
            return 0;
        }

        return null;
    }

    private function isValidDate($date)
    {
        $dt = DateTime::createFromFormat('Y-m-d', $date);

        return $dt && $dt->format('Y-m-d') === $date;
    }

    private function generateCustomerCode()
    {
        do {
            $maKH = "KH" . date("YmdHis") . rand(100, 999);
        } while ($this->accountModel->checkCustomerCodeExists($maKH));

        return $maKH;
    }
}