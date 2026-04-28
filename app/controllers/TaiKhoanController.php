<?php

class TaiKhoanController {
    private $pdo;
    private $accountModel;
    private $homeModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->accountModel = new TaiKhoanModel($pdo);
        $this->homeModel = new HomeModel($pdo);
    }

    public function loginView() {
        $this->renderLogin();
    }

    public function loginPost() {
        $username = trim($_POST['Username'] ?? '');
        $password = $_POST['Password'] ?? '';
        $errors = [];

        if ($username === '' || $password === '') {
            $errors['Global'][] = "Vui lòng nhập đầy đủ thông tin.";
            $this->renderLogin($errors);
            return;
        }

        $account = $this->accountModel->getAccountByUsername($username);

        if (!$account || !HashPassword::verify($password, $account['MatKhauHash'])) {
            $errors['Global'][] = "Sai tài khoản hoặc mật khẩu.";
            $this->renderLogin($errors);
            return;
        }

        $this->accountModel->updateLastLogin($account['TaiKhoanId']);

        $_SESSION['LoginInformation'] = $account;
        $_SESSION['success'] = "Chào mừng " . htmlspecialchars($account['HoTen'] ?? $account['TenDangNhap']) . " đã quay trở lại!";

        $maVaiTro = strtoupper(trim($account['MaVaiTro'] ?? ''));

        if (in_array($maVaiTro, ['ADMIN', 'STAFF', 'SHIPPER'])) {
            header("Location: index.php?controller=dashboard");
            exit;
        }

        header("Location: index.php?controller=home");
        exit;
    }

    public function registerView() {
        $this->renderRegister();
    }

    public function registerPost() {
        $rvm = [
            'Username'    => trim($_POST['Username'] ?? ''),
            'Password'    => $_POST['Password'] ?? '',
            'ConfirmPass' => $_POST['ConfirmPassword'] ?? '',
            'FirstName'   => trim($_POST['FirstName'] ?? ''),
            'LastName'    => trim($_POST['LastName'] ?? ''),
            'Mobile'      => trim($_POST['Mobile'] ?? ''),
            'Email'       => strtolower(trim($_POST['Email'] ?? '')),
            'Sex'         => trim($_POST['Sex'] ?? ''),
            'DateOfBirth' => $_POST['DateOfBirth'] ?? null,
            'Address'     => trim($_POST['Address'] ?? '')
        ];

        $errors = [];

        if (strlen($rvm['Username']) < 4) {
            $errors['Username'] = "Tên đăng nhập từ 4 ký tự.";
        }

        if (strlen($rvm['Password']) < 6) {
            $errors['Password'] = "Mật khẩu tối thiểu 6 ký tự.";
        }

        if ($rvm['Password'] !== $rvm['ConfirmPass']) {
            $errors['ConfirmPassword'] = "Mật khẩu không khớp.";
        }

        if ($rvm['Mobile'] === '') {
            $errors['Mobile'] = "Vui lòng nhập số điện thoại.";
        }

        if ($rvm['FirstName'] === '' && $rvm['LastName'] === '') {
            $errors['FullName'] = "Vui lòng nhập họ tên.";
        }

        if (empty($errors)) {
            if ($this->accountModel->checkUsernameExists($rvm['Username'])) {
                $errors['Username'] = "Tên đăng nhập đã tồn tại.";
            }

            if ($this->accountModel->checkPhoneExists($rvm['Mobile'])) {
                $errors['Mobile'] = "Số điện thoại đã được sử dụng.";
            }

            if (!empty($rvm['Email']) && $this->accountModel->checkEmailExists($rvm['Email'])) {
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

            $fullName = trim($rvm['LastName'] . ' ' . $rvm['FirstName']);
            if ($fullName === '') {
                $fullName = "Khách hàng";
            }

            do {
                $maKH = "KH" . date("YmdHis") . rand(100, 999);
            } while ($this->accountModel->checkCustomerCodeExists($maKH));

            $gioiTinh = null;
            $sex = mb_strtolower($rvm['Sex'], 'UTF-8');

            if ($sex === 'nam') {
                $gioiTinh = 1;
            } elseif (in_array($sex, ['nữ', 'nu'])) {
                $gioiTinh = 0;
            }

            $this->accountModel->createCustomer([
                'MaKhachHang' => $maKH,
                'HoTen'       => $fullName,
                'Email'       => $rvm['Email'],
                'SoDienThoai' => $rvm['Mobile'],
                'GioiTinh'    => $gioiTinh,
                'NgaySinh'    => $rvm['DateOfBirth'],
                'DiaChi'      => $rvm['Address'],
                'GhiChu'      => "Đăng ký từ Web"
            ]);

            $newAccount = $this->accountModel->createAccount([
                'VaiTroId'    => $roleId,
                'TenDangNhap' => $rvm['Username'],
                'MatKhauHash' => HashPassword::hash($rvm['Password']),
                'HoTen'       => $fullName,
                'Email'       => $rvm['Email'],
                'SoDienThoai' => $rvm['Mobile'],
                'GioiTinh'    => $gioiTinh,
                'NgaySinh'    => $rvm['DateOfBirth'],
                'DiaChi'      => $rvm['Address']
            ]);

            $this->pdo->commit();

            $_SESSION['LoginInformation'] = $newAccount;
            $_SESSION['success'] = "Đăng ký thành viên thành công! Chào mừng " . htmlspecialchars($fullName) . ".";

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

    public function logoutAccount() {
        unset($_SESSION['LoginInformation']);
        unset($_SESSION['ShoppingCart']);

        $_SESSION['success'] = "Bạn đã đăng xuất thành công.";

        header("Location: index.php?controller=home");
        exit;
    }

    private function renderLogin($errors = []) {
        $storeInfo = $this->homeModel->getStoreInfo();

        $title = "Đăng nhập";
        $viewContent = BASE_PATH . '/views/client/login.php';

        require BASE_PATH . '/views/client/layout.php';
    }

    private function renderRegister($errors = [], $rvm = []) {
        $storeInfo = $this->homeModel->getStoreInfo();

        $title = "Đăng ký";
        $viewContent = BASE_PATH . '/views/client/register.php';

        require BASE_PATH . '/views/client/layout.php';
    }
}