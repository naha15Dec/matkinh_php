<?php
require_once BASE_PATH . '/app/models/SanPhamModel.php';
require_once BASE_PATH . '/app/models/HomeModel.php';

class SanPhamController
{
    private $pdo;
    private $sanPhamModel;
    private $homeModel;

    private const DEFAULT_PAGE_SIZE = 9;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdo = $pdo;
        $this->sanPhamModel = new SanPhamModel($pdo);
        $this->homeModel = new HomeModel($pdo);
    }

    public function index()
    {
        $filter = [
            'CategoryId' => isset($_GET['CategoryId']) ? max(0, (int)$_GET['CategoryId']) : 0,
            'BrandId'    => isset($_GET['BrandId']) ? max(0, (int)$_GET['BrandId']) : 0,
            'Keyword'    => trim($_GET['Keyword'] ?? ''),
            'PriceRange' => trim($_GET['PriceRange'] ?? ''),
            'Page'       => isset($_GET['Page']) ? max(1, (int)$_GET['Page']) : 1,
            'PageSize'   => self::DEFAULT_PAGE_SIZE
        ];

        $this->resolvePriceRange($filter['PriceRange'], $filter);

        $result = $this->sanPhamModel->getFilteredProducts(
            $filter,
            $filter['Page'],
            $filter['PageSize']
        );

        $products = $result['data'];
        $totalCount = (int)$result['totalCount'];

        $pagination = $this->calculatePagination(
            $filter['Page'],
            $filter['PageSize'],
            $totalCount
        );

        $filter['Page'] = $pagination['CurrentPage'];

        $brandList = $this->sanPhamModel->getActiveBrands();
        $typeProductList = $this->sanPhamModel->getActiveCategories();
        $storeInfo = $this->homeModel->getStoreInfo();

        $title = "Danh sách mắt kính";

        if (!empty($filter['Keyword'])) {
            $title = "Tìm kiếm: " . $filter['Keyword'];
        }

        $viewContent = BASE_PATH . '/views/client/product_index.php';

        include BASE_PATH . '/views/client/layout.php';
    }

    public function detail()
    {
        $sanPhamId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($sanPhamId <= 0) {
            $_SESSION['error'] = "Sản phẩm không hợp lệ.";
            header("Location: index.php?controller=sanpham");
            exit;
        }

        $product = $this->sanPhamModel->getChiTietSanPham($sanPhamId);

        if (!$product) {
            $_SESSION['error'] = "Sản phẩm không tồn tại, đã ngừng bán hoặc thuộc thương hiệu/danh mục đã bị khóa.";
            header("Location: index.php?controller=sanpham");
            exit;
        }

        try {
            $sessionId = session_id();
            $sessionAccount = $_SESSION['LoginInformation'] ?? null;

            $khachHangId = null;

            if ($sessionAccount) {
                $khachHangId = $this->sanPhamModel->resolveCustomerIdFromAccount($sessionAccount);
            }

            $this->sanPhamModel->logUserBehavior(
                $khachHangId,
                $sanPhamId,
                'VIEW',
                $sessionId,
                1.0,
                'PRODUCT_DETAIL'
            );
        } catch (Exception $e) {
            // Không để lỗi log hành vi làm hỏng trang chi tiết sản phẩm.
        }

        $relatedProducts = $this->sanPhamModel->getRelatedProducts(
            $sanPhamId,
            (int)($product['LoaiSanPhamId'] ?? 0),
            (int)($product['ThuongHieuId'] ?? 0)
        );

        $recommendedProducts = $this->sanPhamModel->getRecommendedProductsLocal(
            $sanPhamId,
            (float)($product['GiaBan'] ?? 0)
        );

        $storeInfo = $this->homeModel->getStoreInfo();

        $title = $product['TenSanPham'] ?? 'Chi tiết sản phẩm';
        $viewContent = BASE_PATH . '/views/client/product_detail.php';

        include BASE_PATH . '/views/client/layout.php';
    }

    private function resolvePriceRange($selectedPrice, &$filter)
    {
        if ($selectedPrice === '' || !is_numeric($selectedPrice)) {
            return;
        }

        $price = (int)$selectedPrice;

        if ($price === 500000) {
            $filter['MinPrice'] = 0;
            $filter['MaxPrice'] = 500000;
        } elseif ($price === 3000000) {
            $filter['MinPrice'] = 500000;
            $filter['MaxPrice'] = 3000000;
        } elseif ($price === 5000000) {
            $filter['MinPrice'] = 3000000;
            $filter['MaxPrice'] = 5000000;
        } elseif ($price >= 10000000) {
            $filter['MinPrice'] = 10000000;
        }
    }

    private function calculatePagination($page, $pageSize, $totalCount)
    {
        $page = max(1, (int)$page);
        $pageSize = max(1, (int)$pageSize);
        $totalCount = max(0, (int)$totalCount);

        $totalPages = (int)ceil($totalCount / $pageSize);

        if ($totalPages <= 0) {
            $totalPages = 1;
        }

        $currentPage = min($page, $totalPages);

        $displayStart = max(1, $currentPage - 2);
        $displayEnd = min($totalPages, $currentPage + 2);

        if (($displayEnd - $displayStart) < 4) {
            if ($displayStart === 1) {
                $displayEnd = min($totalPages, $displayStart + 4);
            } elseif ($displayEnd === $totalPages) {
                $displayStart = max(1, $displayEnd - 4);
            }
        }

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