<?php
require_once BASE_PATH . '/app/models/AdminDonHangModel.php';
require_once BASE_PATH . '/app/helpers/OrderConstants.php';

class AdminDonHangController
{
    private $model;
    private $pdo;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdo = $pdo;
        $this->model = new AdminDonHangModel($pdo);

        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        if (!$sessionAccount || !in_array($roleCode, ['ADMIN', 'STAFF', 'SHIPPER'], true)) {
            $_SESSION['error'] = "Bạn không có quyền truy cập module đơn hàng.";
            header("Location: index.php?controller=dashboard");
            exit;
        }
    }

    public function index()
    {
        $pdo = $this->pdo;

        $keyword = trim($_GET['keyword'] ?? '');
        $status = $_GET['status'] ?? null;

        if ($status !== null && $status !== '' && !is_numeric($status)) {
            $status = null;
        }

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

        $sessionAccount = $_SESSION['LoginInformation'];
        $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));

        $order = $this->model->getOrderById($id);

        if (!$order) {
            $_SESSION['error'] = "Đơn hàng không tồn tại.";
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        if (!$this->canViewOrder($order, $sessionAccount, $roleCode)) {
            $_SESSION['error'] = "Bạn không có quyền xem đơn hàng này.";
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        $items = $this->model->getOrderItems($id);
        $histories = $this->model->getOrderHistory($id);
        $shippers = $this->model->getActiveShippers();

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

        if (!$this->isKnownOrderStatus($newStatus)) {
            $_SESSION['error'] = "Trạng thái đơn hàng không hợp lệ.";
            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

        $currentUser = $_SESSION['LoginInformation'];
        $currentUserId = (int)($currentUser['TaiKhoanId'] ?? 0);
        $roleCode = strtoupper(trim($currentUser['MaVaiTro'] ?? ''));

        $order = $this->model->getOrderById($id);

        if (!$order) {
            $_SESSION['error'] = "Không tìm thấy đơn hàng.";
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        if (!$this->canViewOrder($order, $currentUser, $roleCode)) {
            $_SESSION['error'] = "Bạn không có quyền thao tác đơn hàng này.";
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        $oldStatus = (int)($order['TrangThai'] ?? 0);

        if ($newStatus === OrderStatusConstants::ASSIGNED_TO_SHIPPER) {
            $_SESSION['error'] = "Vui lòng dùng chức năng gán shipper để chuyển đơn sang trạng thái đã giao shipper.";
            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

        if ($oldStatus === $newStatus) {
            $_SESSION['error'] = "Đơn hàng đã ở trạng thái này, không có thay đổi mới.";
            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

        if ($this->isFinalStatus($oldStatus)) {
            $_SESSION['error'] = "Đơn hàng đã kết thúc, không thể tiếp tục cập nhật trạng thái.";
            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

        if (!$this->canUpdateStatusByRole($roleCode, $order, $newStatus, $currentUserId)) {
            $_SESSION['error'] = "Bạn không có quyền cập nhật trạng thái này.";
            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            $_SESSION['error'] = "Luồng trạng thái đơn hàng không hợp lệ.";
            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

        if ($newStatus === OrderStatusConstants::CANCELLED) {
            if (!in_array($roleCode, ['ADMIN', 'STAFF'], true)) {
                $_SESSION['error'] = "Shipper không có quyền hủy đơn hàng.";
                header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
                exit;
            }

            $paymentMethod = strtoupper(trim($order['PhuongThucThanhToan'] ?? ''));
            $paymentStatus = $order['TrangThaiThanhToan'] ?? PaymentConstants::PENDING;

            if ($this->model->cancelOrderAndRestoreStock(
                $id,
                $oldStatus,
                $currentUserId,
                $note !== '' ? $note : 'Hủy đơn hàng từ khu vực quản trị.',
                $paymentMethod,
                $paymentStatus
            )) {
                $_SESSION['success'] = "Đã hủy đơn hàng và hoàn lại tồn kho.";
            } else {
                $_SESSION['error'] = "Hủy đơn hàng thất bại.";
            }

            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

        $paymentMethod = strtoupper(trim($order['PhuongThucThanhToan'] ?? ''));
        $updateData = [];

        if ($newStatus === OrderStatusConstants::CONFIRMED && empty($order['ConfirmedById'])) {
            $updateData['ConfirmedById'] = $currentUserId;
            $updateData['NgayXacNhan'] = true;
        }

        if ($newStatus === OrderStatusConstants::DELIVERING) {
            $updateData['NgayGiao'] = true;
        }

        if ($newStatus === OrderStatusConstants::DELIVERED) {
            $updateData['NgayHoanTat'] = true;

            if ($paymentMethod === PaymentConstants::COD) {
                $updateData['TrangThaiThanhToan'] = PaymentConstants::PAID;
                $updateData['NgayThanhToan'] = true;
            }
        }

        if ($this->model->updateStatus($id, $newStatus, $updateData)) {
            $this->model->addHistory(
                $id,
                $oldStatus,
                $newStatus,
                $note !== '' ? $note : 'Cập nhật trạng thái đơn hàng.',
                $currentUserId
            );

            $_SESSION['success'] = "Cập nhật trạng thái đơn hàng thành công.";
        } else {
            $_SESSION['error'] = "Cập nhật trạng thái đơn hàng thất bại hoặc dữ liệu không thay đổi.";
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
        $roleCode = strtoupper(trim($currentUser['MaVaiTro'] ?? ''));

        if (!in_array($roleCode, ['ADMIN', 'STAFF'], true)) {
            $_SESSION['error'] = "Chỉ quản trị viên hoặc nhân viên mới được gán shipper.";
            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

        $order = $this->model->getOrderById($id);

        if (!$order) {
            $_SESSION['error'] = "Không tìm thấy đơn hàng.";
            header("Location: index.php?controller=admindonhang");
            exit;
        }

        $oldStatus = (int)($order['TrangThai'] ?? 0);

        if ($this->isFinalStatus($oldStatus)) {
            $_SESSION['error'] = "Đơn hàng đã kết thúc, không thể gán shipper.";
            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

        if ($oldStatus === OrderStatusConstants::PENDING) {
            $_SESSION['error'] = "Vui lòng xác nhận đơn hàng trước khi gán shipper.";
            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

        if (!$this->model->isActiveShipper($shipperId)) {
            $_SESSION['error'] = "Nhân viên giao hàng không tồn tại hoặc đã bị khóa.";
            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

        $canAssignFromStatus = in_array($oldStatus, [
            OrderStatusConstants::CONFIRMED,
            OrderStatusConstants::PREPARING,
            OrderStatusConstants::ASSIGNED_TO_SHIPPER,
            OrderStatusConstants::DELIVERY_FAILED
        ], true);

        if (!$canAssignFromStatus) {
            $_SESSION['error'] = "Chỉ có thể gán shipper cho đơn đã xác nhận, đang chuẩn bị, đã giao shipper hoặc giao thất bại.";
            header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
            exit;
        }

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
            $_SESSION['error'] = "Có lỗi xảy ra khi gán shipper hoặc dữ liệu không thay đổi.";
        }

        header("Location: index.php?controller=admindonhang&action=detail&id=" . $id);
        exit;
    }

    private function canViewOrder($order, $currentUser, $roleCode)
    {
        if (in_array($roleCode, ['ADMIN', 'STAFF'], true)) {
            return true;
        }

        if ($roleCode === 'SHIPPER') {
            return (int)($order['ShipperId'] ?? 0) === (int)($currentUser['TaiKhoanId'] ?? 0);
        }

        return false;
    }

    private function canUpdateStatusByRole($roleCode, $order, $newStatus, $currentUserId)
    {
        $roleCode = strtoupper(trim($roleCode));
        $newStatus = (int)$newStatus;

        /*
            ADMIN / STAFF:
            - Được xác nhận đơn
            - Được chuyển sang đang chuẩn bị
            - Được hủy đơn nếu luồng trạng thái cho phép
            - Không được tự cập nhật trạng thái giao hàng
            - Muốn giao hàng phải dùng form gán shipper
        */
        if (in_array($roleCode, ['ADMIN', 'STAFF'], true)) {
            return in_array($newStatus, [
                OrderStatusConstants::CONFIRMED,
                OrderStatusConstants::PREPARING,
                OrderStatusConstants::CANCELLED
            ], true);
        }

        /*
            SHIPPER:
            - Chỉ được thao tác đơn đã gán cho mình
            - Được cập nhật trạng thái giao hàng
        */
        if ($roleCode === 'SHIPPER') {
            if ((int)($order['ShipperId'] ?? 0) !== (int)$currentUserId) {
                return false;
            }

            return in_array($newStatus, [
                OrderStatusConstants::DELIVERING,
                OrderStatusConstants::DELIVERED,
                OrderStatusConstants::DELIVERY_FAILED
            ], true);
        }

        return false;
    }

    private function isKnownOrderStatus($status)
    {
        return in_array((int)$status, [
            OrderStatusConstants::PENDING,
            OrderStatusConstants::CONFIRMED,
            OrderStatusConstants::PREPARING,
            OrderStatusConstants::ASSIGNED_TO_SHIPPER,
            OrderStatusConstants::DELIVERING,
            OrderStatusConstants::DELIVERED,
            OrderStatusConstants::DELIVERY_FAILED,
            OrderStatusConstants::CANCELLED
        ], true);
    }

    private function isValidStatusTransition($oldStatus, $newStatus)
    {
        $oldStatus = (int)$oldStatus;
        $newStatus = (int)$newStatus;

        if ($oldStatus === $newStatus) {
            return false;
        }

        if ($newStatus === OrderStatusConstants::CANCELLED) {
            return in_array($oldStatus, [
                OrderStatusConstants::PENDING,
                OrderStatusConstants::CONFIRMED,
                OrderStatusConstants::PREPARING,
                OrderStatusConstants::ASSIGNED_TO_SHIPPER,
                OrderStatusConstants::DELIVERY_FAILED
            ], true);
        }

        $allowed = [
            OrderStatusConstants::PENDING => [
                OrderStatusConstants::CONFIRMED
            ],

            OrderStatusConstants::CONFIRMED => [
                OrderStatusConstants::PREPARING
            ],

            OrderStatusConstants::PREPARING => [
                // Chuyển sang ASSIGNED_TO_SHIPPER phải dùng assignShipper().
            ],

            OrderStatusConstants::ASSIGNED_TO_SHIPPER => [
                OrderStatusConstants::DELIVERING,
                OrderStatusConstants::DELIVERY_FAILED
            ],

            OrderStatusConstants::DELIVERING => [
                OrderStatusConstants::DELIVERED,
                OrderStatusConstants::DELIVERY_FAILED
            ],

            OrderStatusConstants::DELIVERY_FAILED => [
                // Giao thất bại: Admin/Staff có thể hủy ở nhánh CANCELLED.
                // Muốn giao lại thì dùng assignShipper().
            ]
        ];

        return isset($allowed[$oldStatus]) && in_array($newStatus, $allowed[$oldStatus], true);
    }

    private function isFinalStatus($status)
    {
        return in_array((int)$status, [
            OrderStatusConstants::DELIVERED,
            OrderStatusConstants::CANCELLED
        ], true);
    }
}