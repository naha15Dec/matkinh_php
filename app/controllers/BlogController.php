<?php
require_once BASE_PATH . '/app/models/BlogModel.php';
// Cần nạp thêm HomeModel để lấy thông tin cửa hàng cho Layout
require_once BASE_PATH . '/app/models/HomeModel.php';

class BlogController {
    private $pdo;
    private $model;
    private $homeModel; // Thêm biến này
    private const PAGE_SIZE = 3;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new BlogModel($pdo);
        $this->homeModel = new HomeModel($pdo); // Khởi tạo HomeModel
    }

    // Trang danh sách bài viết
    public function index() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : "";

        $result = $this->model->getListBlog($page, self::PAGE_SIZE, $keyword);
        
        $listPost = $result['data'];
        $total = $result['total'];
        $totalPages = ceil($total / self::PAGE_SIZE);

        $listPostPopular = $this->model->getLatestPosts();

        // --- QUAN TRỌNG: Lấy dữ liệu cho Header/Footer của Layout ---
        $storeInfo = $this->homeModel->getStoreInfo();

        // --- GẮN LAYOUT ---
        $title = "Tin tức & Sự kiện";
        $viewContent = BASE_PATH . '/views/client/blog_index.php';
        
        // Sửa đường dẫn include cho thống nhất với HomeController
        include BASE_PATH . '/views/client/layout.php';
    }

    // Trang chi tiết bài viết
    public function detail() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        $post = $this->model->getBlogById($id);
        if (!$post) {
            header("Location: index.php?controller=blog");
            exit;
        }

        $listPostPopular = $this->model->getLatestPosts($id);

        // --- QUAN TRỌNG: Lấy dữ liệu cho Header/Footer của Layout ---
        $storeInfo = $this->homeModel->getStoreInfo();

        // --- GẮN LAYOUT ---
        $title = $post['TieuDe'] ?? "Chi tiết bài viết";
        $viewContent = BASE_PATH . '/views/client/blog_detail.php';
        
        // Đảm bảo đường dẫn include file layout.php là chính xác (thêm /client/)
        include BASE_PATH . '/views/client/layout.php';
    }
}