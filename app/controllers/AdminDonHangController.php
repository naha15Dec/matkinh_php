<?php
require_once BASE_PATH . '/app/models/AdminDonHangModel.php';
require_once BASE_PATH . '/app/helpers/OrderConstants.php';

class AdminDonHangController
{
    private $model;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new AdminDonHangModel($pdo);

        if (!isset($_SESSION['LoginInformation'])) {
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }
    }

    public function index()
    {
        $pdo = $this->pdo;

        $keyword = trim($_GET['keyword'] ?? '');
        $status = $_GET['status'] ?? null;
        $currentUser = $_SESSION['LoginInformation'];

        $orders = $this->model->getOrders($currentUser, $keyword, $status);

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $displayName = $sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Tài khoản';
        $isAdmin = $roleCode === 'ADMIN';
        $isStaff = $roleCode === 'STAFF';
        $isShipper = $roleCode === 'SHIPPER';

        $title = "Quản lý đơn hàng";
        $viewContent = BASE_PATH . '/views/admin/order_index.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function detail()
    {
        $pdo = $this->pdo;

        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = "Mã đơn hàng không hợp lệ.";
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        $order = $this->model->getOrderById($id);

        if (!$order) {
            $_SESSION['error'] = "Đơn hàng không tồn tại.";
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        $items = $this->model->getOrderItems($id);
        $histories = $this->model->getOrderHistory($id);
        $shippers = $this->model->getActiveShippers();

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $displayName = $sessionAccount['HoTen'] ?? $sessionAccount['TenDangNhap'] ?? 'Tài khoản';
        $isAdmin = $roleCode === 'ADMIN';
        $isStaff = $roleCode === 'STAFF';
        $isShipper = $roleCode === 'SHIPPER';

        $title = "Chi tiết đơn hàng " . ($order['MaDonHang'] ?? '');
        $viewContent = BASE_PATH . '/views/admin/order_detail.php';

        require_once BASE_PATH . '/views/admin/layout.php';
    }

    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        $id = (int)($_POST['DonHangId'] ?? 0);
        $newStatus = (int)($_POST['TrangThaiMoi'] ?? 0);
        $note = trim($_POST['GhiChu'] ?? '');

        if ($id <= 0 || $newStatus <= 0) {
            $_SESSION['error'] = "Dữ liệu cập nhật trạng thái không hợp lệ.";
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        $currentUser = $_SESSION['LoginInformation'];
        $currentUserId = (int)($currentUser['TaiKhoanId'] ?? 0);

        $order = $this->model->getOrderById($id);

        if (!$order) {
            $_SESSION['error'] = "Không tìm thấy đơn hàng.";
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        $oldStatus = (int)($order['TrangThai'] ?? 0);
        $paymentMethod = strtoupper(trim($order['PhuongThucThanhToan'] ?? ''));

        $updateData = [];

        if ($newStatus === OrderStatusConstants::CONFIRMED) {
            $updateData['ConfirmedById'] = $currentUserId;
            $updateData['NgayXacNhan'] = true;
        }

        if ($newStatus === OrderStatusConstants::DELIVERED) {
            $updateData['NgayHoanTat'] = true;

            if ($paymentMethod === PaymentConstants::COD) {
                $updateData['TrangThaiThanhToan'] = PaymentConstants::PAID;
            }
        }

        if ($this->model->updateStatus($id, $newStatus, $updateData)) {
            $this->model->addHistory(
                $id,
                $oldStatus,
                $newStatus,
                $note,
                $currentUserId
            );

            $_SESSION['success'] = "Cập nhật trạng thái đơn hàng thành công.";
        } else {
            $_SESSION['error'] = "Cập nhật trạng thái đơn hàng thất bại.";
        }

        header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
        exit;
    }

    public function assignShipper()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        $id = (int)($_POST['DonHangId'] ?? 0);
        $shipperId = (int)($_POST['ShipperId'] ?? 0);

        if ($id <= 0) {
            $_SESSION['error'] = "Mã đơn hàng không hợp lệ.";
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        if ($shipperId <= 0) {
            $_SESSION['error'] = "Vui lòng chọn một nhân viên giao hàng.";
            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

        $currentUser = $_SESSION['LoginInformation'];
        $currentUserId = (int)($currentUser['TaiKhoanId'] ?? 0);

        $order = $this->model->getOrderById($id);

        if (!$order) {
            $_SESSION['error'] = "Không tìm thấy đơn hàng.";
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        $oldStatus = (int)($order['TrangThai'] ?? 0);
        $newStatus = OrderStatusConstants::ASSIGNED_TO_SHIPPER;

        $updateData = [
            'ShipperId' => $shipperId
        ];

        if ($this->model->updateStatus($id, $newStatus, $updateData)) {
            $this->model->addHistory(
                $id,
                $oldStatus,
                $newStatus,
                "Gán shipper cho đơn hàng.",
                $currentUserId
            );

            $_SESSION['success'] = "Gán nhân viên giao hàng thành công.";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi gán shipper.";
        }

        header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
        exit;
    }
}