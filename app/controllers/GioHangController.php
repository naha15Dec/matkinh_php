<?php
require_once BASE_PATH . '/app/models/GioHangModel.php';
// THÊM: Nạp HomeModel để lấy StoreInfo cho Layout
require_once BASE_PATH . '/app/models/HomeModel.php';

class GioHangController {
    private $pdo;
    private $gioHangModel;
    private $homeModel; // THÊM

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->gioHangModel = new GioHangModel($pdo);
        $this->homeModel = new HomeModel($pdo); // THÊM
    }

    private function getCart() {
        return $_SESSION['ShoppingCart'] ?? [];
    }

    private function saveCart($cart) {
        $_SESSION['ShoppingCart'] = $cart;
    }

    // ========================= TRANG GIỎ HÀNG =========================
    public function index() {
        $cart = $this->getCart();
        
        // THÊM: Lấy StoreInfo để Layout không bị lỗi Fatal Error
        $storeInfo = $this->homeModel->getStoreInfo();

        // Cấu hình Layout
        $title = "Giỏ hàng của bạn";
        $viewContent = BASE_PATH . '/views/client/cart.php';
        
        // Đảm bảo đường dẫn khớp với /views/client/layout.php
        include BASE_PATH . '/views/client/layout.php';
    }

    // ========================= THÊM VÀO GIỎ =========================
    public function add() {
        // Bắt buộc đăng nhập để mua hàng
        if (!isset($_SESSION['LoginInformation'])) {
            $_SESSION['NotificationLogin'] = "Bạn cần đăng nhập để thêm sản phẩm vào giỏ hàng.";
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }

        $sanPhamId = (int)($_POST['SanPhamId'] ?? $_GET['sanPhamId'] ?? 0);
        $product = $this->gioHangModel->getProductById($sanPhamId);

        // Kiểm tra tính hợp lệ của sản phẩm
        if (!$product || $product['TrangThai'] != 1) {
            $_SESSION['CartError'] = "Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.";
            header("Location: index.php?controller=giohang");
            exit;
        }

        // Kiểm tra tồn kho
        if ($product['SoLuongTon'] <= 0) {
            $_SESSION['CartError'] = "Sản phẩm hiện đang hết hàng.";
            header("Location: index.php?controller=giohang");
            exit;
        }

        $cart = $this->getCart();
        $found = false;

        foreach ($cart as &$item) {
            if ($item['SanPhamId'] == $sanPhamId) {
                if ($item['SoLuong'] + 1 > $product['SoLuongTon']) {
                    $_SESSION['CartError'] = "Số lượng yêu cầu vượt quá tồn kho (Hiện có: {$product['SoLuongTon']}).";
                    header("Location: index.php?controller=giohang");
                    exit;
                }
                $item['SoLuong'] += 1;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $cart[] = [
                'SanPhamId' => $product['SanPhamId'],
                'TenSanPham' => $product['TenSanPham'],
                'HinhAnh' => $product['HinhAnhChinh'],
                'DonGia' => (float)$product['GiaBan'], // Dùng giá bán thực tế
                'GiamGia' => ($product['GiaGoc'] > $product['GiaBan']) ? ($product['GiaGoc'] - $product['GiaBan']) : 0,
                'SoLuong' => 1
            ];
        }

        $this->saveCart($cart);
        $_SESSION['CartSuccess'] = "Đã thêm sản phẩm vào giỏ hàng.";
        header("Location: index.php?controller=giohang");
    }

    // ========================= CẬP NHẬT SỐ LƯỢNG =========================
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $sanPhamId = (int)($_POST['sanPhamId'] ?? 0);
        $soLuong = (int)($_POST['soLuong'] ?? 0);
        $cart = $this->getCart();
        $product = $this->gioHangModel->getProductById($sanPhamId);
        
        foreach ($cart as $key => &$item) {
            if ($item['SanPhamId'] == $sanPhamId) {
                if ($soLuong <= 0) {
                    unset($cart[$key]);
                } elseif ($soLuong > $product['SoLuongTon']) {
                    $_SESSION['CartError'] = "Kho không đủ. Chỉ còn {$product['SoLuongTon']} cái.";
                } else {
                    $item['SoLuong'] = $soLuong;
                }
                break;
            }
        }
        $this->saveCart(array_values($cart));
        header("Location: index.php?controller=giohang");
    }

    // ========================= XÓA 1 SẢN PHẨM =========================
    public function remove() {
        $sanPhamId = (int)($_GET['sanPhamId'] ?? 0);
        $cart = $this->getCart();
        foreach ($cart as $key => $item) {
            if ($item['SanPhamId'] == $sanPhamId) { 
                unset($cart[$key]); 
                break; 
            }
        }
        $this->saveCart(array_values($cart));
        header("Location: index.php?controller=giohang");
    }

    // ========================= XÓA SẠCH GIỎ HÀNG =========================
    public function clear() {
        $this->saveCart([]);
        header("Location: index.php?controller=giohang");
    }
}