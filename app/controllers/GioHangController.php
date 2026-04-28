<?php

require_once BASE_PATH . '/app/models/GioHangModel.php';
require_once BASE_PATH . '/app/models/HomeModel.php';

class GioHangController {
    private $pdo;
    private $model;
    private $homeModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new GioHangModel($pdo);
        $this->homeModel = new HomeModel($pdo);

        if (!isset($_SESSION['ShoppingCart'])) {
            $_SESSION['ShoppingCart'] = [];
        }
    }

    public function index() {
        $cart = $_SESSION['ShoppingCart'] ?? [];
        $storeInfo = $this->homeModel->getStoreInfo();

        $title = "Giỏ hàng";
        $viewContent = BASE_PATH . '/views/client/cart.php';

        require BASE_PATH . '/views/client/layout.php';
    }

    public function add() {
        $sanPhamId = (int)($_GET['sanPhamId'] ?? $_POST['sanPhamId'] ?? $_POST['SanPhamId'] ?? 0);
        $soLuong = (int)($_POST['soLuong'] ?? $_POST['SoLuong'] ?? 1);

        if ($sanPhamId <= 0) {
            $_SESSION['CartError'] = "Sản phẩm không hợp lệ.";
            header("Location: index.php?controller=sanpham");
            exit;
        }

        $product = $this->model->getProductById($sanPhamId);

        if (!$product || (int)$product['TrangThai'] !== 1) {
            $_SESSION['CartError'] = "Sản phẩm không tồn tại hoặc đã ngừng bán.";
            header("Location: index.php?controller=sanpham");
            exit;
        }

        $tonKho = (int)($product['SoLuongTon'] ?? 0);

        if ($tonKho <= 0) {
            $_SESSION['CartError'] = "Sản phẩm hiện đã hết hàng.";
            header("Location: index.php?controller=sanpham&action=detail&id=" . $sanPhamId);
            exit;
        }

        if ($soLuong < 1) {
            $soLuong = 1;
        }

        if ($soLuong > $tonKho) {
            $soLuong = $tonKho;
        }

        $cart = $_SESSION['ShoppingCart'];
        $found = false;

        foreach ($cart as &$item) {
            if ((int)$item['SanPhamId'] === $sanPhamId) {
                $newQty = (int)$item['SoLuong'] + $soLuong;

                if ($newQty > $tonKho) {
                    $newQty = $tonKho;
                    $_SESSION['CartError'] = "Số lượng sản phẩm đã được điều chỉnh theo tồn kho hiện có.";
                } else {
                    $_SESSION['CartSuccess'] = "Đã cập nhật số lượng sản phẩm trong giỏ hàng.";
                }

                $item['SoLuong'] = $newQty;
                $item['SoLuongTon'] = $tonKho;
                $found = true;
                break;
            }
        }

        unset($item);

        if (!$found) {
            $cart[] = [
                'SanPhamId'   => (int)$product['SanPhamId'],
                'TenSanPham'  => $product['TenSanPham'],
                'HinhAnh'     => $product['HinhAnhChinh'] ?? '',
                'DonGia'      => (float)$product['GiaBan'],
                'GiaGoc'      => (float)($product['GiaGoc'] ?? $product['GiaBan']),
                'GiamGia'     => ((float)($product['GiaGoc'] ?? 0) > (float)$product['GiaBan'])
                                    ? ((float)$product['GiaGoc'] - (float)$product['GiaBan'])
                                    : 0,
                'SoLuong'     => $soLuong,
                'SoLuongTon'  => $tonKho,
                'ThuongHieu'  => $product['TenThuongHieu'] ?? '',
                'LoaiSanPham' => $product['TenLoaiSanPham'] ?? ''
            ];

            $_SESSION['CartSuccess'] = "Đã thêm sản phẩm vào giỏ hàng.";
        }

        $_SESSION['ShoppingCart'] = $cart;

        header("Location: index.php?controller=giohang");
        exit;
    }

    public function update() {
        $sanPhamId = (int)($_POST['sanPhamId'] ?? $_POST['SanPhamId'] ?? $_GET['sanPhamId'] ?? 0);
        $soLuong = (int)($_POST['soLuong'] ?? $_POST['SoLuong'] ?? $_GET['soLuong'] ?? 1);

        if ($sanPhamId <= 0) {
            $_SESSION['CartError'] = "Sản phẩm không hợp lệ.";
            header("Location: index.php?controller=giohang");
            exit;
        }

        $product = $this->model->getProductById($sanPhamId);
        $tonKho = $product ? (int)($product['SoLuongTon'] ?? 0) : 0;

        $cart = $_SESSION['ShoppingCart'] ?? [];

        foreach ($cart as $index => &$item) {
            if ((int)$item['SanPhamId'] === $sanPhamId) {
                if ($soLuong <= 0) {
                    unset($cart[$index]);
                    $_SESSION['CartSuccess'] = "Đã xóa sản phẩm khỏi giỏ hàng.";
                    break;
                }

                if ($tonKho <= 0) {
                    unset($cart[$index]);
                    $_SESSION['CartError'] = "Sản phẩm đã hết hàng và được xóa khỏi giỏ.";
                    break;
                }

                if ($soLuong > $tonKho) {
                    $soLuong = $tonKho;
                    $_SESSION['CartError'] = "Số lượng đã được điều chỉnh theo tồn kho.";
                } else {
                    $_SESSION['CartSuccess'] = "Đã cập nhật giỏ hàng.";
                }

                $item['SoLuong'] = $soLuong;
                $item['SoLuongTon'] = $tonKho;
                break;
            }
        }

        unset($item);

        $_SESSION['ShoppingCart'] = array_values($cart);

        header("Location: index.php?controller=giohang");
        exit;
    }

    public function remove() {
        $sanPhamId = (int)($_GET['sanPhamId'] ?? $_POST['sanPhamId'] ?? $_GET['SanPhamId'] ?? 0);

        $cart = $_SESSION['ShoppingCart'] ?? [];

        foreach ($cart as $index => $item) {
            if ((int)$item['SanPhamId'] === $sanPhamId) {
                unset($cart[$index]);
                $_SESSION['CartSuccess'] = "Đã xóa sản phẩm khỏi giỏ hàng.";
                break;
            }
        }

        $_SESSION['ShoppingCart'] = array_values($cart);

        header("Location: index.php?controller=giohang");
        exit;
    }

    public function clear() {
        $_SESSION['ShoppingCart'] = [];
        $_SESSION['CartSuccess'] = "Đã xóa toàn bộ giỏ hàng.";

        header("Location: index.php?controller=giohang");
        exit;
    }
}