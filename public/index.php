<?php
session_start();

// Bật hiển thị lỗi để dễ debug khi làm đồ án
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Chỉ định nghĩa nếu chưa có (Tránh lỗi trùng hằng số với config.php)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// 1. Cấu hình & Helper
require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/app/helpers/HashPassword.php';
require_once BASE_PATH . '/app/helpers/OrderConstants.php';

// 2. Nạp Models
require_once BASE_PATH . '/app/models/SanPhamModel.php'; 
require_once BASE_PATH . '/app/models/TaiKhoanModel.php';
require_once BASE_PATH . '/app/models/HomeModel.php';
require_once BASE_PATH . '/app/models/GioHangModel.php';
require_once BASE_PATH . '/app/models/ThanhToanModel.php';
require_once BASE_PATH . '/app/models/ProfileModel.php';
require_once BASE_PATH . '/app/models/BlogModel.php';
require_once BASE_PATH . '/app/models/ContactModel.php';

// 3. Nạp Controllers
require_once BASE_PATH . '/app/controllers/SanPhamController.php';
require_once BASE_PATH . '/app/controllers/TaiKhoanController.php';
require_once BASE_PATH . '/app/controllers/HomeController.php';
require_once BASE_PATH . '/app/controllers/GioHangController.php';
require_once BASE_PATH . '/app/controllers/ThanhToanController.php';
require_once BASE_PATH . '/app/controllers/ProfileController.php';
require_once BASE_PATH . '/app/controllers/BlogController.php';
require_once BASE_PATH . '/app/controllers/ContactController.php';
require_once BASE_PATH . '/app/controllers/ErrorController.php';

// Lấy thông tin từ URL
$controllerName = $_GET['controller'] ?? 'home'; 
$actionName = $_GET['action'] ?? 'index';

// Điều phối đến Controller tương ứng
switch ($controllerName) {
    case 'taikhoan':
        $authController = new TaiKhoanController($pdo);
        switch ($actionName) {
            case 'login': 
                // Tự động nhận biết nếu là POST thì gọi loginPost, nếu là GET thì gọi loginView
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $authController->loginPost();
                } else {
                    $authController->loginView();
                }
                break;

            case 'register': 
                // SỬA TẠI ĐÂY: Nếu user submit form (POST), gọi registerPost()
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $authController->registerPost();
                } else {
                    $authController->registerView();
                }
                break;

            case 'logout': 
                $authController->logoutAccount(); 
                break;

            default: 
                $authController->loginView(); 
                break;
        }
        break;

    case 'sanpham':
        $shopController = new SanPhamController($pdo);
        if ($actionName == 'detail') {
            $shopController->detail();
        } else {
            $shopController->index();
        }
        break;
    
    case 'giohang':
    case 'cart':
        $cartController = new GioHangController($pdo);
        switch ($actionName) {
            case 'add': $cartController->add(); break;
            case 'update': $cartController->update(); break;
            case 'remove': $cartController->remove(); break;
            case 'clear': $cartController->clear(); break;
            default: $cartController->index(); break;
        }
        break;

    case 'thanhtoan':
        $checkoutCtrl = new ThanhToanController($pdo);
        switch ($actionName) {
            case 'process': $checkoutCtrl->process(); break;
            case 'vnpay_return': $checkoutCtrl->vnpay_return(); break;
            case 'success': $checkoutCtrl->success(); break;
            default: $checkoutCtrl->index(); break;
        }
        break;

    case 'profile':
        $profileCtrl = new ProfileController($pdo);
        switch ($actionName) {
            case 'updateInfo': $profileCtrl->updateInfo(); break;
            case 'changePassword': $profileCtrl->changePassword(); break;
            case 'orderDetail': $profileCtrl->orderDetail(); break;
            default: $profileCtrl->index(); break;
        }
        break;

    case 'blog':
        $blogCtrl = new BlogController($pdo);
        ($actionName == 'detail') ? $blogCtrl->detail() : $blogCtrl->index();
        break;

    case 'contact':
        (new ContactController($pdo))->index();
        break;

    case 'error':
        (new ErrorController($pdo))->index();
        break;

    default:
        $homeController = new HomeController($pdo);
        if ($actionName == 'findProductByID') {
            $homeController->findProductByID();
        } else {
            $homeController->index();
        }
        break;
}