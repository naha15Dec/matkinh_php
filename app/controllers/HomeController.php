<?php

require_once BASE_PATH . '/app/models/HomeModel.php';

class HomeController {
    private $pdo;
    private $homeModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->homeModel = new HomeModel($pdo);
    }

    public function index() {
        $listDiscountProduct = $this->homeModel->getDiscountProducts(8);
        $listNewProduct      = $this->homeModel->getNewProducts(8);
        $listDealHot         = $this->homeModel->getDealHotProducts(4);
        $listLatestBlog      = $this->homeModel->getLatestBlogs(3);
        $storeInfo           = $this->homeModel->getStoreInfo();

        $title = "Trang chủ - Thế giới mắt kính cao cấp";
        $viewContent = BASE_PATH . '/views/client/home.php';

        require BASE_PATH . '/views/client/layout.php';
    }

    public function findProductByID() {
        $idProduct = trim($_GET['idProduct'] ?? $_POST['idProduct'] ?? '');

        if ($idProduct === '') {
            $_SESSION['error'] = "Vui lòng nhập mã hoặc ID sản phẩm.";
            header("Location: index.php?controller=home");
            exit;
        }

        $product = $this->homeModel->findProductByCodeOrId($idProduct);

        if (!$product) {
            $_SESSION['error'] = "Không tìm thấy sản phẩm phù hợp.";
            header("Location: index.php?controller=home");
            exit;
        }

        header("Location: index.php?controller=sanpham&action=detail&id=" . $product['SanPhamId']);
        exit;
    }
}