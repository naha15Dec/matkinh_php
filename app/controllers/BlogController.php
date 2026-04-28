<?php

require_once BASE_PATH . '/app/models/BlogModel.php';
require_once BASE_PATH . '/app/models/HomeModel.php';

class BlogController {
    private $pdo;
    private $model;
    private $homeModel;

    private const PAGE_SIZE = 6;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new BlogModel($pdo);
        $this->homeModel = new HomeModel($pdo);
    }

    public function index() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $keyword = trim($_GET['keyword'] ?? '');

        $result = $this->model->getListBlog($page, self::PAGE_SIZE, $keyword);

        $listPost = $result['data'];
        $total = $result['total'];
        $totalPages = max(1, ceil($total / self::PAGE_SIZE));

        $listPostPopular = $this->model->getLatestPosts(null, 5);
        $storeInfo = $this->homeModel->getStoreInfo();

        $title = "Tin tức & Xu hướng mắt kính";
        $viewContent = BASE_PATH . '/views/client/blog_index.php';

        require BASE_PATH . '/views/client/layout.php';
    }

    public function detail() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $post = $this->model->getBlogById($id);

        if (!$post) {
            $_SESSION['error'] = "Bài viết không tồn tại hoặc chưa được xuất bản.";
            header("Location: index.php?controller=blog");
            exit;
        }

        $listPostPopular = $this->model->getLatestPosts($id, 5);
        $storeInfo = $this->homeModel->getStoreInfo();

        $title = $post['TieuDe'] ?? "Chi tiết bài viết";
        $viewContent = BASE_PATH . '/views/client/blog_detail.php';

        require BASE_PATH . '/views/client/layout.php';
    }
}