<?php
require_once BASE_PATH . '/app/models/HomeModel.php';

class HomeController {
    private $pdo;
    private $homeModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->homeModel = new HomeModel($pdo);
    }

    // ========================= TRANG CHỦ =========================
    public function index() {
        // 1. Lấy dữ liệu từ Model
        $listDiscountProduct = $this->homeModel->getDiscountProducts(8);
        $listNewProduct      = $this->homeModel->getNewProducts(8);
        $listDealHot         = $this->homeModel->getDiscountProducts(2); 
        $listLatestBlog      = $this->homeModel->getLatestBlogs(3);
        $storeInfo           = $this->homeModel->getStoreInfo();

        // 2. Thiết lập thông tin cho Layout
        $title = "Trang chủ - Thế giới mắt kính cao cấp"; // Tiêu đề tab trình duyệt
        
        // 3. Khai báo file view con để Layout nạp vào
        $viewContent = BASE_PATH . '/views/client/home.php';

        // 4. Gọi Layout tổng (Layout sẽ tự động có các biến $listDiscountProduct, $storeInfo... để dùng)
        include BASE_PATH . '/views/client/layout.php';
    }

    // ========================= TÌM KIẾM THEO ID/MÃ =========================
    public function findProductByID() {
        $idProduct = trim($_GET['idProduct'] ?? $_POST['idProduct'] ?? '');

        if (empty($idProduct)) {
            header("Location: index.php?controller=home&action=index");
            exit();
        }

        $product = $this->homeModel->findProductByCodeOrId($idProduct);

        if (!$product) {
            $_SESSION['HomeSearchError'] = "Không tìm thấy sản phẩm phù hợp.";
            header("Location: index.php?controller=home&action=index");
            exit();
        }

        // Chuyển hướng thẳng sang trang chi tiết sản phẩm nếu tìm thấy
        header("Location: index.php?controller=sanpham&action=detail&id=" . $product['SanPhamId']);
        exit();
    }
}