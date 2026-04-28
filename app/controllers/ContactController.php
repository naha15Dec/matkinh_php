<?php

require_once BASE_PATH . '/app/models/ContactModel.php';

class ContactController {
    private $pdo;
    private $model;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new ContactModel($pdo);
    }

    public function index() {
        $storeInfo = $this->model->getStoreInfo();

        $title = "Liên hệ với chúng tôi";
        $viewContent = BASE_PATH . '/views/client/contact.php';

        require BASE_PATH . '/views/client/layout.php';
    }
}