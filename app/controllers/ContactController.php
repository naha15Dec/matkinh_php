<?php

require_once BASE_PATH . '/app/models/ContactModel.php';

class ContactController
{
    private $pdo;
    private $model;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new ContactModel($pdo);
    }

    public function index()
    {
        $storeInfo = $this->model->getStoreInfo();

        $title = "Liên hệ với chúng tôi";
        $viewContent = BASE_PATH . '/views/client/contact.php';

        require BASE_PATH . '/views/client/layout.php';
    }

    public function send()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=contact");
            exit;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $fullName = trim($_POST['FullName'] ?? '');
        $phone = trim($_POST['Phone'] ?? '');
        $email = trim($_POST['Email'] ?? '');
        $subject = trim($_POST['Subject'] ?? '');
        $message = trim($_POST['Message'] ?? '');

        if ($fullName === '' || $phone === '' || $message === '') {
            $_SESSION['error'] = "Vui lòng nhập đầy đủ họ tên, số điện thoại và nội dung cần tư vấn.";
            header("Location: index.php?controller=contact");
            exit;
        }

        if (mb_strlen($fullName, 'UTF-8') > 150) {
            $_SESSION['error'] = "Họ tên không được vượt quá 150 ký tự.";
            header("Location: index.php?controller=contact");
            exit;
        }

        if (mb_strlen($phone, 'UTF-8') > 20) {
            $_SESSION['error'] = "Số điện thoại không được vượt quá 20 ký tự.";
            header("Location: index.php?controller=contact");
            exit;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Email không đúng định dạng.";
            header("Location: index.php?controller=contact");
            exit;
        }

        if (mb_strlen($subject, 'UTF-8') > 200) {
            $_SESSION['error'] = "Chủ đề không được vượt quá 200 ký tự.";
            header("Location: index.php?controller=contact");
            exit;
        }

        if (mb_strlen($message, 'UTF-8') > 1000) {
            $_SESSION['error'] = "Nội dung liên hệ không được vượt quá 1000 ký tự.";
            header("Location: index.php?controller=contact");
            exit;
        }

        $_SESSION['success'] = "Karma Eyewear đã nhận thông tin liên hệ của bạn. Chúng tôi sẽ phản hồi trong thời gian sớm nhất.";

        header("Location: index.php?controller=contact");
        exit;
    }
}