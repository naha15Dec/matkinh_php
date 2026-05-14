<?php

require_once BASE_PATH . '/app/models/GioHangModel.php';
require_once BASE_PATH . '/app/models/HomeModel.php';

class GioHangController
{
    private $pdo;
    private $model;
    private $homeModel;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdo = $pdo;
        $this->model = new GioHangModel($pdo);
        $this->homeModel = new HomeModel($pdo);

        if (!isset($_SESSION['ShoppingCart']) || !is_array($_SESSION['ShoppingCart'])) {
            $_SESSION['ShoppingCart'] = [];
        }
    }

    public function index()
    {
        $this->refreshCartItems();

        $cart = $_SESSION['ShoppingCart'] ?? [];
        $storeInfo = $this->homeModel->getStoreInfo();

        $title = "Giỏ hàng";
        $viewContent = BASE_PATH . '/views/client/cart.php';

        require BASE_PATH . '/views/client/layout.php';
    }

    public function add()
    {
        $sanPhamId = (int)($_GET['sanPhamId'] ?? $_POST['sanPhamId'] ?? $_POST['SanPhamId'] ?? 0);
        $soLuong = (int)($_POST['soLuong'] ?? $_POST['SoLuong'] ?? 1);

        if ($sanPhamId <= 0) {
            $_SESSION['CartError'] = "Sản phẩm không hợp lệ.";
            header("Location: index.php?controller=sanpham");
            exit;
        }

        $product = $this->model->getProductById($sanPhamId);

        if (!$product) {
            $_SESSION['CartError'] = "Sản phẩm không tồn tại, đã ngừng bán hoặc thuộc danh mục/thương hiệu đã bị khóa.";
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

                $item = $this->buildCartItem($product, $newQty);
                $found = true;
                break;
            }
        }

        unset($item);

        if (!$found) {
            $cart[] = $this->buildCartItem($product, $soLuong);
            $_SESSION['CartSuccess'] = "Đã thêm sản phẩm vào giỏ hàng.";
        }

        $_SESSION['ShoppingCart'] = array_values($cart);

        header("Location: index.php?controller=giohang");
        exit;
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=giohang");
            exit;
        }

        $sanPhamId = (int)($_POST['sanPhamId'] ?? $_POST['SanPhamId'] ?? 0);
        $soLuong = (int)($_POST['soLuong'] ?? $_POST['SoLuong'] ?? 1);

        if ($sanPhamId <= 0) {
            $_SESSION['CartError'] = "Sản phẩm không hợp lệ.";
            header("Location: index.php?controller=giohang");
            exit;
        }

        $product = $this->model->getProductById($sanPhamId);
        $cart = $_SESSION['ShoppingCart'] ?? [];

        foreach ($cart as $index => &$item) {
            if ((int)$item['SanPhamId'] === $sanPhamId) {
                if ($soLuong <= 0) {
                    unset($cart[$index]);
                    $_SESSION['CartSuccess'] = "Đã xóa sản phẩm khỏi giỏ hàng.";
                    break;
                }

                if (!$product) {
                    unset($cart[$index]);
                    $_SESSION['CartError'] = "Sản phẩm đã ngừng bán hoặc không còn hợp lệ nên được xóa khỏi giỏ.";
                    break;
                }

                $tonKho = (int)($product['SoLuongTon'] ?? 0);

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

                $item = $this->buildCartItem($product, $soLuong);
                break;
            }
        }

        unset($item);

        $_SESSION['ShoppingCart'] = array_values($cart);

        header("Location: index.php?controller=giohang");
        exit;
    }

    public function remove()
    {
        $sanPhamId = (int)($_POST['sanPhamId'] ?? $_POST['SanPhamId'] ?? $_GET['sanPhamId'] ?? $_GET['SanPhamId'] ?? 0);

        if ($sanPhamId <= 0) {
            $_SESSION['CartError'] = "Sản phẩm không hợp lệ.";
            header("Location: index.php?controller=giohang");
            exit;
        }

        $cart = $_SESSION['ShoppingCart'] ?? [];
        $removed = false;

        foreach ($cart as $index => $item) {
            if ((int)$item['SanPhamId'] === $sanPhamId) {
                unset($cart[$index]);
                $removed = true;
                break;
            }
        }

        $_SESSION['ShoppingCart'] = array_values($cart);

        $_SESSION[$removed ? 'CartSuccess' : 'CartError'] = $removed
            ? "Đã xóa sản phẩm khỏi giỏ hàng."
            : "Không tìm thấy sản phẩm trong giỏ hàng.";

        header("Location: index.php?controller=giohang");
        exit;
    }

    public function clear()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=giohang");
            exit;
        }

        $_SESSION['ShoppingCart'] = [];
        $_SESSION['CartSuccess'] = "Đã xóa toàn bộ giỏ hàng.";

        header("Location: index.php?controller=giohang");
        exit;
    }

    private function refreshCartItems()
    {
        $cart = $_SESSION['ShoppingCart'] ?? [];
        $newCart = [];
        $hasChanged = false;

        foreach ($cart as $item) {
            $sanPhamId = (int)($item['SanPhamId'] ?? 0);
            $qty = (int)($item['SoLuong'] ?? 0);

            if ($sanPhamId <= 0 || $qty <= 0) {
                $hasChanged = true;
                continue;
            }

            $product = $this->model->getProductById($sanPhamId);

            if (!$product) {
                $hasChanged = true;
                continue;
            }

            $tonKho = (int)($product['SoLuongTon'] ?? 0);

            if ($tonKho <= 0) {
                $hasChanged = true;
                continue;
            }

            if ($qty > $tonKho) {
                $qty = $tonKho;
                $hasChanged = true;
            }

            $newCart[] = $this->buildCartItem($product, $qty);
        }

        if ($hasChanged) {
            $_SESSION['CartError'] = "Giỏ hàng đã được cập nhật theo tình trạng sản phẩm và tồn kho hiện tại.";
        }

        $_SESSION['ShoppingCart'] = array_values($newCart);
    }

    private function buildCartItem($product, $qty)
    {
        $giaBan = (float)($product['GiaBan'] ?? 0);
        $giaGoc = (float)($product['GiaGoc'] ?? $giaBan);

        return [
            'SanPhamId'   => (int)$product['SanPhamId'],
            'TenSanPham'  => $product['TenSanPham'],
            'HinhAnh'     => $product['HinhAnhChinh'] ?? '',
            'DonGia'      => $giaBan,
            'GiaGoc'      => $giaGoc,
            'GiamGia'     => ($giaGoc > $giaBan) ? ($giaGoc - $giaBan) : 0,
            'SoLuong'     => max(1, (int)$qty),
            'SoLuongTon'  => (int)($product['SoLuongTon'] ?? 0),
            'ThuongHieu'  => $product['TenThuongHieu'] ?? '',
            'LoaiSanPham' => $product['TenLoaiSanPham'] ?? ''
        ];
    }
}