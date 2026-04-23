<?php
require_once BASE_PATH . '/app/models/ContactModel.php';
// Nên có thêm HomeModel nếu layout.php phụ thuộc vào biến $storeInfo chung
require_once BASE_PATH . '/app/models/HomeModel.php';

class ContactController {
    private $pdo;
    private $model;
    private $homeModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new ContactModel($pdo);
        $this->homeModel = new HomeModel($pdo);
    }

    public function index() {
        // Lấy dữ liệu cửa hàng để hiển thị trong trang liên hệ và cả Header/Footer của Layout
        $storeInfo = $this->model->getStoreInfo();
        
        // Nếu layout.php của bạn yêu cầu biến $storeInfo từ HomeModel, hãy dùng:
        // $storeInfo = $this->homeModel->getStoreInfo();

        // --- CẤU HÌNH LAYOUT ---
        $title = "Liên hệ với chúng tôi"; 
        $viewContent = BASE_PATH . '/views/client/contact.php';
        
        // Đảm bảo đường dẫn này khớp với vị trí file layout thực tế
        include BASE_PATH . '/views/client/layout.php';
    }
}