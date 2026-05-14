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

    if ($username === '') {
        $errors['Global'][] = "Vui lòng nhập tên đăng nhập, email hoặc số điện thoại.";
    }

    if ($password === '') {
        $errors['Global'][] = "Vui lòng nhập mật khẩu.";
    }

    if ($username !== '' && mb_strlen($username, 'UTF-8') > 100) {
        $errors['Global'][] = "Thông tin đăng nhập không hợp lệ.";
    }

    if ($password !== '' && strlen($password) > 72) {
        $errors['Global'][] = "Mật khẩu không hợp lệ.";
    }

    if (!empty($errors)) {
        $this->renderLogin($errors);
        return;
    }

    $loginValue = $this->normalizeLoginValue($username);
    $account = $this->accountModel->getAccountByUsername($loginValue);

    if (!$account || empty($account['MatKhauHash'])) {
        $errors['Global'][] = "Sai tài khoản hoặc mật khẩu.";
        $this->renderLogin($errors);
        return;
    }

    if (empty($account['IsActive'])) {
        $errors['Global'][] = "Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.";
        $this->renderLogin($errors);
        return;
    }

    if (!HashPassword::verify($password, $account['MatKhauHash'])) {
        $errors['Global'][] = "Sai tài khoản hoặc mật khẩu.";
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
        'Mobile'      => $this->normalizePhone($_POST['Mobile'] ?? ''),
        'Email'       => strtolower(trim($_POST['Email'] ?? '')),
        'Sex'         => trim($_POST['Sex'] ?? ''),
        'DateOfBirth' => trim($_POST['DateOfBirth'] ?? ''),
        'Address'     => trim($_POST['Address'] ?? '')
    ];

    $errors = [];

    if ($rvm['Username'] === '') {
        $errors['Username'] = "Vui lòng nhập tên đăng nhập.";
    } elseif (mb_strlen($rvm['Username'], 'UTF-8') < 4) {
        $errors['Username'] = "Tên đăng nhập phải có ít nhất 4 ký tự.";
    } elseif (mb_strlen($rvm['Username'], 'UTF-8') > 50) {
        $errors['Username'] = "Tên đăng nhập không được vượt quá 50 ký tự.";
    } elseif (!preg_match('/^[A-Za-z][A-Za-z0-9_.-]{3,49}$/', $rvm['Username'])) {
        $errors['Username'] = "Tên đăng nhập phải bắt đầu bằng chữ cái và chỉ gồm chữ, số, dấu chấm, gạch dưới hoặc gạch ngang.";
    }

    if ($rvm['Password'] === '') {
        $errors['Password'] = "Vui lòng nhập mật khẩu.";
    } elseif (strlen($rvm['Password']) < 6) {
        $errors['Password'] = "Mật khẩu tối thiểu 6 ký tự.";
    } elseif (strlen($rvm['Password']) > 72) {
        $errors['Password'] = "Mật khẩu không được vượt quá 72 ký tự.";
    } elseif (!$this->isStrongEnoughPassword($rvm['Password'])) {
        $errors['Password'] = "Mật khẩu nên có ít nhất 1 chữ cái và 1 chữ số.";
    }

    if ($rvm['ConfirmPass'] === '') {
        $errors['ConfirmPassword'] = "Vui lòng nhập lại mật khẩu.";
    } elseif ($rvm['Password'] !== $rvm['ConfirmPass']) {
        $errors['ConfirmPassword'] = "Mật khẩu nhập lại không khớp.";
    }

    if ($rvm['FirstName'] === '' && $rvm['LastName'] === '') {
        $errors['FullName'] = "Vui lòng nhập họ tên.";
    }

    if ($rvm['FirstName'] !== '' && !$this->isValidVietnameseName($rvm['FirstName'])) {
        $errors['FullName'] = "Tên không được chứa số hoặc ký tự đặc biệt.";
    }

    if ($rvm['LastName'] !== '' && !$this->isValidVietnameseName($rvm['LastName'])) {
        $errors['FullName'] = "Họ không được chứa số hoặc ký tự đặc biệt.";
    }

    $fullName = trim($rvm['LastName'] . ' ' . $rvm['FirstName']);

    if ($fullName === '') {
        $fullName = "Khách hàng";
    }

    if (mb_strlen($fullName, 'UTF-8') > 150) {
        $errors['FullName'] = "Họ tên không được vượt quá 150 ký tự.";
    }

    if ($rvm['Mobile'] === '') {
        $errors['Mobile'] = "Vui lòng nhập số điện thoại.";
    } elseif (!$this->isValidVietnamPhone($rvm['Mobile'])) {
        $errors['Mobile'] = "Số điện thoại không hợp lệ. Ví dụ đúng: 0912345678.";
    }

    if ($rvm['Email'] !== '') {
        if (!filter_var($rvm['Email'], FILTER_VALIDATE_EMAIL)) {
            $errors['Email'] = "Email không đúng định dạng.";
        } elseif (mb_strlen($rvm['Email'], 'UTF-8') > 100) {
            $errors['Email'] = "Email không được vượt quá 100 ký tự.";
        }
    }

    if ($rvm['Sex'] !== '' && !in_array($rvm['Sex'], ['Nam', 'Nữ', 'nam', 'nữ', 'nu', '1', '0'], true)) {
        $errors['Sex'] = "Giới tính không hợp lệ.";
    }

    if ($rvm['DateOfBirth'] !== '') {
        if (!$this->isValidDate($rvm['DateOfBirth'])) {
            $errors['DateOfBirth'] = "Ngày sinh không hợp lệ.";
        } elseif ($this->isFutureDate($rvm['DateOfBirth'])) {
            $errors['DateOfBirth'] = "Ngày sinh không được lớn hơn ngày hiện tại.";
        } elseif (!$this->isOldEnough($rvm['DateOfBirth'], 13)) {
            $errors['DateOfBirth'] = "Người dùng phải từ 13 tuổi trở lên.";
        }
    }

    if ($rvm['Address'] !== '') {
        if (mb_strlen($rvm['Address'], 'UTF-8') < 5) {
            $errors['Address'] = "Địa chỉ quá ngắn.";
        } elseif (mb_strlen($rvm['Address'], 'UTF-8') > 255) {
            $errors['Address'] = "Địa chỉ không được vượt quá 255 ký tự.";
        }
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

        $existingCustomer = $this->accountModel->getCustomerByPhoneOrEmail($rvm['Mobile'], $rvm['Email']);

        if ($existingCustomer) {
            $errors['Mobile'] = $errors['Mobile'] ?? "Số điện thoại hoặc email đã tồn tại trong hồ sơ khách hàng.";
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

    private function normalizeLoginValue($value)
{
    $value = trim((string)$value);

    if ($this->looksLikePhone($value)) {
        return $this->normalizePhone($value);
    }

    return $value;
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

private function looksLikePhone($value)
{
    $value = trim((string)$value);

    return (bool)preg_match('/^(\+84|84|0)[0-9\s\.\-\(\)]{8,15}$/', $value);
}

private function isValidVietnamPhone($phone)
{
    $phone = $this->normalizePhone($phone);

    return (bool)preg_match('/^0(3|5|7|8|9)[0-9]{8}$/', $phone);
}

private function isStrongEnoughPassword($password)
{
    return preg_match('/[A-Za-z]/', $password)
        && preg_match('/[0-9]/', $password);
}

private function isValidVietnameseName($name)
{
    $name = trim((string)$name);

    if ($name === '') {
        return true;
    }

    return (bool)preg_match("/^[\p{L}\s'.-]+$/u", $name);
}

private function isFutureDate($date)
{
    $input = DateTime::createFromFormat('Y-m-d', $date);
    $today = new DateTime('today');

    if (!$input) {
        return true;
    }

    return $input > $today;
}

private function isOldEnough($date, $minAge = 13)
{
    $birthday = DateTime::createFromFormat('Y-m-d', $date);

    if (!$birthday) {
        return false;
    }

    $today = new DateTime('today');
    $age = $today->diff($birthday)->y;

    return $age >= $minAge;
}
}