<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// =======================
// CONFIG & HELPER
// =======================
require_once BASE_PATH . '/config.php';
require_once BASE_PATH . '/app/helpers/HashPassword.php';
require_once BASE_PATH . '/app/helpers/OrderConstants.php';

// =======================
// MODELS
// =======================
require_once BASE_PATH . '/app/models/SanPhamModel.php';
require_once BASE_PATH . '/app/models/TaiKhoanModel.php';
require_once BASE_PATH . '/app/models/HomeModel.php';
require_once BASE_PATH . '/app/models/GioHangModel.php';
require_once BASE_PATH . '/app/models/ThanhToanModel.php';
require_once BASE_PATH . '/app/models/ProfileModel.php';
require_once BASE_PATH . '/app/models/BlogModel.php';
require_once BASE_PATH . '/app/models/ContactModel.php';

require_once BASE_PATH . '/app/models/AdminTaiKhoanModel.php';
require_once BASE_PATH . '/app/models/AdminDonHangModel.php';
require_once BASE_PATH . '/app/models/AdminSanPhamModel.php';
require_once BASE_PATH . '/app/models/AdminBlogModel.php';
require_once BASE_PATH . '/app/models/AdminBrandModel.php';
require_once BASE_PATH . '/app/models/AdminRevenueModel.php';
require_once BASE_PATH . '/app/models/AdminProfileModel.php';
require_once BASE_PATH . '/app/models/AdminSettingModel.php';
require_once BASE_PATH . '/app/models/AdminTypeModel.php';

// =======================
// CONTROLLERS
// =======================
require_once BASE_PATH . '/app/controllers/HomeController.php';
require_once BASE_PATH . '/app/controllers/SanPhamController.php';
require_once BASE_PATH . '/app/controllers/TaiKhoanController.php';
require_once BASE_PATH . '/app/controllers/GioHangController.php';
require_once BASE_PATH . '/app/controllers/ThanhToanController.php';
require_once BASE_PATH . '/app/controllers/ProfileController.php';
require_once BASE_PATH . '/app/controllers/BlogController.php';
require_once BASE_PATH . '/app/controllers/ContactController.php';
require_once BASE_PATH . '/app/controllers/ErrorController.php';

require_once BASE_PATH . '/app/controllers/DashboardController.php';
require_once BASE_PATH . '/app/controllers/AdminTaiKhoanController.php';
require_once BASE_PATH . '/app/controllers/AdminDonHangController.php';
require_once BASE_PATH . '/app/controllers/AdminSanPhamController.php';
require_once BASE_PATH . '/app/controllers/AdminBlogController.php';
require_once BASE_PATH . '/app/controllers/AdminBrandController.php';
require_once BASE_PATH . '/app/controllers/AdminRevenueController.php';
require_once BASE_PATH . '/app/controllers/AdminProfileController.php';
require_once BASE_PATH . '/app/controllers/AdminSettingController.php';
require_once BASE_PATH . '/app/controllers/AdminTypeController.php';

// =======================
// ROUTE PARAMS
// =======================
$controllerName = strtolower($_GET['controller'] ?? 'home');
$actionName = strtolower($_GET['action'] ?? 'index');

// =======================
// ROUTER
// =======================
switch ($controllerName) {

    // CLIENT
    case 'home':
        $ctrl = new HomeController($pdo);

        if ($actionName === 'findproductbyid') {
            $ctrl->findProductByID();
        } else {
            $ctrl->index();
        }
        break;

    case 'taikhoan':
        $ctrl = new TaiKhoanController($pdo);

        switch ($actionName) {
            case 'login':
                $_SERVER['REQUEST_METHOD'] === 'POST'
                    ? $ctrl->loginPost()
                    : $ctrl->loginView();
                break;

            case 'register':
                $_SERVER['REQUEST_METHOD'] === 'POST'
                    ? $ctrl->registerPost()
                    : $ctrl->registerView();
                break;

            case 'logout':
                $ctrl->logoutAccount();
                break;

            default:
                $ctrl->loginView();
                break;
        }
        break;

    case 'sanpham':
        $ctrl = new SanPhamController($pdo);

        if ($actionName === 'detail') {
            $ctrl->detail();
        } else {
            $ctrl->index();
        }
        break;

    case 'giohang':
    case 'cart':
        $ctrl = new GioHangController($pdo);

        switch ($actionName) {
            case 'add':
                $ctrl->add();
                break;
            case 'update':
                $ctrl->update();
                break;
            case 'remove':
                $ctrl->remove();
                break;
            case 'clear':
                $ctrl->clear();
                break;
            default:
                $ctrl->index();
                break;
        }
        break;

    case 'thanhtoan':
        $ctrl = new ThanhToanController($pdo);

        switch ($actionName) {
            case 'process':
                $ctrl->process();
                break;
            case 'vnpay_return':
                $ctrl->vnpay_return();
                break;
            case 'success':
                $ctrl->success();
                break;
            default:
                $ctrl->index();
                break;
        }
        break;

    case 'profile':
        $ctrl = new ProfileController($pdo);

        switch ($actionName) {
            case 'updateinfo':
                $ctrl->updateInfo();
                break;
            case 'changepassword':
                $ctrl->changePassword();
                break;
            case 'orderdetail':
                $ctrl->orderDetail();
                break;
            default:
                $ctrl->index();
                break;
        }
        break;

    case 'blog':
        $ctrl = new BlogController($pdo);

        if ($actionName === 'detail') {
            $ctrl->detail();
        } else {
            $ctrl->index();
        }
        break;

    case 'contact':
        $ctrl = new ContactController($pdo);
        $ctrl->index();
        break;

    case 'error':
        $ctrl = new ErrorController($pdo);
        $ctrl->index();
        break;

    // ADMIN
    case 'dashboard':
        $ctrl = new DashboardController($pdo);
        $ctrl->index();
        break;

    case 'admintaikhoan':
        $ctrl = new AdminTaiKhoanController($pdo);

        switch ($actionName) {
            case 'detail':
                $ctrl->detail();
                break;
            case 'toggleactive':
                $ctrl->toggleActive();
                break;
            case 'changepassword':
                $ctrl->changePassword();
                break;
            case 'updaterole':
                $ctrl->updateRole();
                break;
            default:
                $ctrl->index();
                break;
        }
        break;

    case 'admindonhang':
        $ctrl = new AdminDonHangController($pdo);

        switch ($actionName) {
            case 'detail':
                $ctrl->detail();
                break;
            case 'updatestatus':
                $ctrl->updateStatus();
                break;
            case 'assignshipper':
                $ctrl->assignShipper();
                break;
            default:
                $ctrl->index();
                break;
        }
        break;

    case 'adminsanpham':
        $ctrl = new AdminSanPhamController($pdo);

        switch ($actionName) {
            case 'togglefeatured':
                $ctrl->toggleFeatured();
                break;
            case 'edit':
                $ctrl->edit();
                break;
            case 'save':
                $ctrl->save();
                break;
            case 'delete':
                $ctrl->delete();
                break;
            default:
                $ctrl->index();
                break;
        }
        break;

    case 'adminblog':
        $ctrl = new AdminBlogController($pdo);

        switch ($actionName) {
            case 'edit':
                $ctrl->edit();
                break;
            case 'save':
                $ctrl->save();
                break;
            case 'delete':
                $ctrl->delete();
                break;
            default:
                $ctrl->index();
                break;
        }
        break;

    case 'adminbrand':
        $ctrl = new AdminBrandController($pdo);

        switch ($actionName) {
            case 'save':
                $ctrl->save();
                break;
            case 'delete':
                $ctrl->delete();
                break;
            default:
                $ctrl->index();
                break;
        }
        break;

    case 'admintype':
        $ctrl = new AdminTypeController($pdo);

        switch ($actionName) {
            case 'save':
                $ctrl->save();
                break;
            case 'delete':
                $ctrl->delete();
                break;
            default:
                $ctrl->index();
                break;
        }
        break;

    case 'adminrevenue':
        $ctrl = new AdminRevenueController($pdo);
        $ctrl->index();
        break;

    case 'adminprofile':
        $ctrl = new AdminProfileController($pdo);

        switch ($actionName) {
            case 'update':
                $ctrl->update();
                break;
            case 'changepassword':
                $ctrl->changePassword();
                break;
            default:
                $ctrl->index();
                break;
        }
        break;

    case 'adminsetting':
        $ctrl = new AdminSettingController($pdo);

        switch ($actionName) {
            case 'save':
                $ctrl->save();
                break;
            case 'deletehistory':
                $ctrl->deleteHistory();
                break;
            default:
                $ctrl->index();
                break;
        }
        break;

    default:
        $ctrl = new ErrorController($pdo);
        $ctrl->index();
        break;
}