<?php
require_once BASE_PATH . '/app/models/SanPhamModel.php';
// Import thêm HomeModel để lấy thông tin cửa hàng cho Layout
require_once BASE_PATH . '/app/models/HomeModel.php';

class SanPhamController {
    private $pdo;
    private $sanPhamModel;
    private $homeModel; // Thêm property này
    private const DEFAULT_PAGE_SIZE = 9;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->sanPhamModel = new SanPhamModel($pdo);
        $this->homeModel = new HomeModel($pdo); // Khởi tạo HomeModel
    }

    // ========================= 1. TRANG DANH SÁCH =========================
    public function index() {
        // Nhận filter từ URL
        $filter = [
            'CategoryId' => isset($_GET['CategoryId']) ? (int)$_GET['CategoryId'] : 0,
            'BrandId'    => isset($_GET['BrandId']) ? (int)$_GET['BrandId'] : 0,
            'Keyword'    => trim($_GET['Keyword'] ?? ''),
            'PriceRange' => trim($_GET['PriceRange'] ?? ''),
            'Page'       => isset($_GET['Page']) ? max(1, (int)$_GET['Page']) : 1,
            'PageSize'   => self::DEFAULT_PAGE_SIZE
        ];

        $this->resolvePriceRange($filter['PriceRange'], $filter);

        // Lấy dữ liệu sản phẩm
        $result = $this->sanPhamModel->getFilteredProducts($filter, $filter['Page'], $filter['PageSize']);
        $products = $result['data'];
        $totalCount = $result['totalCount'];

        // Phân trang & Load danh mục
        $pagination = $this->calculatePagination($filter['Page'], $filter['PageSize'], $totalCount);
        $brandList = $this->sanPhamModel->getActiveBrands();
        $typeProductList = $this->sanPhamModel->getActiveCategories();

        // --- QUAN TRỌNG: Lấy dữ liệu Store cho Layout ---
        $storeInfo = $this->homeModel->getStoreInfo();

        // --- GẮN LAYOUT ---
        $title = "Danh sách mắt kính";
        if (!empty($filter['Keyword'])) $title = "Tìm kiếm: " . $filter['Keyword'];
        
        $viewContent = BASE_PATH . '/views/client/product_index.php';
        // Sử dụng đường dẫn có /client/ để đồng bộ
        include BASE_PATH . '/views/client/layout.php';
    }

    // ========================= 2. TRANG CHI TIẾT =========================
    public function detail() {
        $sanPhamId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($sanPhamId <= 0) {
            header("Location: index.php?controller=home");
            exit;
        }

        $product = $this->sanPhamModel->getChiTietSanPham($sanPhamId);
        
        if (!$product) {
            header("Location: index.php?controller=home");
            exit;
        }

        // Ghi Log Hành Vi Người Dùng
        try {
            $khachHangId = $_SESSION['LoginInformation']['TaiKhoanId'] ?? null; 
            $sessionId = session_id(); 
            $this->sanPhamModel->logUserBehavior($khachHangId, $sanPhamId, 'VIEW', $sessionId, 1.0);
        } catch (Exception $e) {}

        // Lấy sản phẩm liên quan và gợi ý
        $relatedProducts = $this->sanPhamModel->getRelatedProducts($sanPhamId, $product['LoaiSanPhamId'], $product['ThuongHieuId']);
        $recommendedProducts = $this->sanPhamModel->getRecommendedProductsLocal($sanPhamId, $product['GiaBan']);

        // --- QUAN TRỌNG: Lấy dữ liệu Store cho Layout ---
        $storeInfo = $this->homeModel->getStoreInfo();

        // --- GẮN LAYOUT ---
        $title = $product['TenSanPham'];
        $viewContent = BASE_PATH . '/views/client/product_detail.php';
        // Sử dụng đường dẫn có /client/ để đồng bộ
        include BASE_PATH . '/views/client/layout.php';
    }

    // ========================= 3. PRIVATE METHODS =========================
    private function resolvePriceRange($selectedPrice, &$filter) {
        if (empty($selectedPrice) || !is_numeric($selectedPrice)) return;

        $price = (int)$selectedPrice;
        if ($price == 500000) {
            $filter['MinPrice'] = 0;
            $filter['MaxPrice'] = 500000;
        } elseif ($price == 3000000) {
            $filter['MinPrice'] = 500000;
            $filter['MaxPrice'] = 3000000;
        } elseif ($price == 5000000) {
            $filter['MinPrice'] = 3000000;
            $filter['MaxPrice'] = 5000000;
        } elseif ($price >= 10000000) {
            $filter['MinPrice'] = 10000000;
        }
    }

    private function calculatePagination($page, $pageSize, $totalCount) {
        $totalPages = (int)ceil($totalCount / $pageSize);
        if ($totalPages <= 0) $totalPages = 1;

        $currentPage = $page > $totalPages ? $totalPages : $page;
        $displayStart = $currentPage < 5 ? 1 : (($currentPage - 1) >= ($totalPages - 5) ? max($totalPages - 5, 1) : ($currentPage - 1));
        $displayEnd = $currentPage + 4 > $totalPages ? $totalPages : $currentPage + 4;

        return [
            'CurrentPage' => $currentPage,
            'PageSize' => $pageSize,
            'TotalCount' => $totalCount,
            'TotalPages' => $totalPages,
            'DisplayStart' => $displayStart,
            'DisplayEnd' => $displayEnd
        ];
    }
}