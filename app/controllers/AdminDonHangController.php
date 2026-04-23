<?php
require_once BASE_PATH . '/app/models/AdminDonHangModel.php';

class AdminDonHangController {
    private $model;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new AdminDonHangModel($pdo);
        if (!isset($_SESSION['LoginInformation'])) {
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }
    }

    public function index() {
        $pdo = $this->pdo;
        $keyword = $_GET['keyword'] ?? '';
        $status = $_GET['status'] ?? null;
        $currentUser = $_SESSION['LoginInformation'];

        $orders = $this->model->getOrders($currentUser, $keyword, $status);
        
        $title = "Quản lý đơn hàng";
        $viewContent = BASE_PATH . '/views/admin/order_index.php';
        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function detail() {
        $pdo = $this->pdo;
        $id = $_GET['id'] ?? 0;
        $order = $this->model->getOrderById($id);
        if (!$order) { die("Đơn hàng không tồn tại."); }

        $items = $this->model->getOrderItems($id);
        $histories = $this->model->getOrderHistory($id);
        $shippers = $this->model->getActiveShippers();

        $title = "Chi tiết đơn hàng " . $order['MaDonHang'];
        $viewContent = BASE_PATH . '/views/admin/order_detail.php';
        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function updateStatus() {
        $id = $_POST['DonHangId'];
        $newStatus = (int)$_POST['TrangThaiMoi'];
        $note = $_POST['GhiChu'] ?? '';
        $currentUser = $_SESSION['LoginInformation'];
        
        $order = $this->model->getOrderById($id);
        $oldStatus = $order['TrangThai'];

        // Logic cập nhật (rút gọn từ C#)
        $updateData = [];
        if ($newStatus == 2) { // CONFIRMED
            $updateData['ConfirmedById'] = $currentUser['TaiKhoanId'];
            $updateData['NgayXacNhan'] = true;
        }
        if ($newStatus == 6) $updateData['NgayHoanTat'] = true; // DELIVERED
        
        // Xử lý thanh toán COD khi giao thành công
        if ($newStatus == 6 && strtoupper($order['PhuongThucThanhToan']) == 'COD') {
            $updateData['TrangThaiThanhToan'] = 'Paid';
        }

        $this->model->updateStatus($id, $newStatus, $updateData);
        $this->model->addHistory($id, $oldStatus, $newStatus, $note, $currentUser['TaiKhoanId']);

        $_SESSION['success'] = "Cập nhật trạng thái thành công.";
        header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
    }

    // Hàm gán Shipper cho đơn hàng
    public function assignShipper() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['DonHangId'] ?? 0;
            $shipperId = $_POST['ShipperId'] ?? 0;
            $currentUser = $_SESSION['LoginInformation'];

            // 1. Kiểm tra đơn hàng tồn tại
            $order = $this->model->getOrderById($id);
            if (!$order) {
                $_SESSION['error'] = "Không tìm thấy đơn hàng.";
                header("Location: index.php?controller=admindonhang");
                exit;
            }

            // 2. Kiểm tra shipper hợp lệ
            if (empty($shipperId)) {
                $_SESSION['error'] = "Vui lòng chọn một nhân viên giao hàng.";
                header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
                exit;
            }

            // 3. Thực hiện gán shipper và đổi trạng thái sang "Đã giao shipper" (Status = 4)
            $oldStatus = $order['TrangThai'];
            $newStatus = OrderStatusConstants::ASSIGNED_TO_SHIPPER; // Số 4

            $updateData = [
                'ShipperId' => $shipperId
            ];

            if ($this->model->updateStatus($id, $newStatus, $updateData)) {
                // Lưu vào lịch sử đơn hàng
                $note = "Gán shipper cho đơn hàng.";
                $this->model->addHistory($id, $oldStatus, $newStatus, $note, $currentUser['TaiKhoanId']);
                
                $_SESSION['success'] = "Gán nhân viên giao hàng thành công.";
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi gán shipper.";
            }

            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }
    }
}