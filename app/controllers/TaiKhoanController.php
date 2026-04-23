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

    // ============================================================
    // CÁC HÀM PUBLIC (Gọi từ index.php)
    // ============================================================

    /**
     * Hiển thị trang đăng nhập
     */
    public function loginView() {
        $this->renderLogin();
    }

    /**
     * Xử lý logic đăng nhập khi người dùng nhấn nút
     */
    public function loginPost() {
        $username = trim($_POST['Username'] ?? '');
        $password = $_POST['Password'] ?? '';
        $errors = [];

        if (empty($username) || empty($password)) {
            $errors['Global'][] = "Vui lòng nhập đầy đủ thông tin.";
        } else {
            $account = $this->accountModel->getAccountByUsername($username);
            
            // Dùng HashPassword đã tạo để kiểm tra Bcrypt
            if ($account && HashPassword::verify($password, $account['MatKhauHash'])) {
                // Cập nhật thời gian đăng nhập
                $this->accountModel->updateLastLogin($account['TaiKhoanId']);
                
                // Lưu session
                $_SESSION['LoginInformation'] = $account;
                
                // Chuyển hướng theo vai trò
                $maVaiTro = strtoupper(trim($account['MaVaiTro'] ?? ''));
                if (in_array($maVaiTro, ['ADMIN', 'STAFF', 'SHIPPER'])) {
                    header("Location: index.php?area=admin&controller=dashboard");
                } else {
                    header("Location: index.php");
                }
                $_SESSION['success'] = "Chào mừng " . $account['HoTen'] . " đã quay trở lại!";
                header("Location: index.php");
                exit;
            } else {
                $errors['Global'][] = "Sai tài khoản hoặc mật khẩu.";
            }
        }
        $this->renderLogin($errors);
    }

    /**
     * Hiển thị trang đăng ký
     */
    public function registerView() {
        $this->renderRegister();
    }

    /**
     * Xử lý logic đăng ký khi người dùng nhấn nút
     */
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
        // Validation cơ bản
        if (strlen($rvm['Username']) < 4) $errors['Username'] = "Tên đăng nhập từ 4 ký tự.";
        if (strlen($rvm['Password']) < 6) $errors['Password'] = "Mật khẩu tối thiểu 6 ký tự.";
        if ($rvm['Password'] !== $rvm['ConfirmPass']) $errors['ConfirmPassword'] = "Mật khẩu không khớp.";
        
        // Kiểm tra tồn tại trong DB
        if (empty($errors)) {
            if ($this->accountModel->checkUsernameExists($rvm['Username'])) $errors['Username'] = "Tên đã tồn tại.";
            if ($this->accountModel->checkPhoneExists($rvm['Mobile'])) $errors['Mobile'] = "SĐT đã sử dụng.";
            if (!empty($rvm['Email']) && $this->accountModel->checkEmailExists($rvm['Email'])) $errors['Email'] = "Email đã sử dụng.";
        }

        if (!empty($errors)) {
            $this->renderRegister($errors, $rvm);
            return;
        }

        try {
            $this->pdo->beginTransaction();
            
            $roleId = $this->accountModel->getCustomerRoleId();
            $fullName = trim(($rvm['LastName'] ?? '') . ' ' . ($rvm['FirstName'] ?? '')) ?: "Khách hàng";
            
            // Tạo mã KH duy nhất
            do {
                $maKH = "KH" . date("YmdHis") . rand(100, 999);
            } while ($this->accountModel->checkCustomerCodeExists($maKH));

            // Xử lý giới tính
            $gioiTinh = null;
            if (strtolower($rvm['Sex']) == 'nam') $gioiTinh = 1;
            elseif (in_array(strtolower($rvm['Sex']), ['nữ', 'nu'])) $gioiTinh = 0;

            // 1. Tạo Khách hàng
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

            // 2. Tạo Tài khoản (Dùng Bcrypt hash)
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
            
            // 1. Lưu thông tin đăng nhập vào Session (Quan trọng: Phải làm trước khi chuyển hướng)
            $_SESSION['LoginInformation'] = $newAccount;

            // 2. Lưu thông báo thành công
            $_SESSION['success'] = "Đăng ký thành viên thành công! Chào mừng " . htmlspecialchars($fullName) . ".";

            // 3. Chuyển hướng duy nhất một lần ở cuối
            header("Location: index.php");
            exit;
        } catch (Exception $ex) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            $errors['Global'][] = "Lỗi hệ thống: " . $ex->getMessage();
            $this->renderRegister($errors, $rvm);
        }
    }

    /**
     * Xử lý đăng xuất
     */
    public function logoutAccount() {
        unset($_SESSION['LoginInformation']);
        // Xóa giỏ hàng nếu muốn giống C# logic của bạn
        unset($_SESSION['ShoppingCart']); 
        
        header("Location: index.php");
        exit;
    }

    // ============================================================
    // CÁC HÀM PRIVATE (Chỉ dùng nội bộ trong class này)
    // ============================================================

    private function renderLogin($errors = []) {
        $storeInfo = $this->homeModel->getStoreInfo();
        $title = "Đăng nhập";
        $viewContent = BASE_PATH . '/views/client/login.php';
        include BASE_PATH . '/views/client/layout.php';
    }

    private function renderRegister($errors = [], $rvm = []) {
        $storeInfo = $this->homeModel->getStoreInfo();
        $title = "Đăng ký";
        $viewContent = BASE_PATH . '/views/client/register.php';
        include BASE_PATH . '/views/client/layout.php';
    }
}