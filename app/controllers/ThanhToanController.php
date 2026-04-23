<?php
require_once BASE_PATH . '/app/models/ThanhToanModel.php';
require_once BASE_PATH . '/app/models/HomeModel.php'; 

class ThanhToanController {
    private $pdo;
    private $model;
    private $homeModel;

    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->model = new ThanhToanModel($pdo);
        $this->homeModel = new HomeModel($pdo);
    }

    /**
     * Kiểm tra đăng nhập và trả về thông tin user
     */
    private function checkLogin() {
        if (!isset($_SESSION['LoginInformation'])) {
            $_SESSION['NotificationLogin'] = "Bạn cần đăng nhập để thực hiện thanh toán.";
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }
        return $_SESSION['LoginInformation'];
    }

    // ========================= 1. TRANG NHẬP THÔNG TIN (CHECKOUT) =========================
    public function index() {
        $user = $this->checkLogin();
        $cart = $_SESSION['ShoppingCart'] ?? [];
        
        if (empty($cart)) {
            $_SESSION['error'] = "Giỏ hàng của bạn đang trống.";
            header("Location: index.php?controller=giohang");
            exit;
        }

        $storeInfo = $this->homeModel->getStoreInfo();
        $title = "Thanh toán đơn hàng";
        $viewContent = BASE_PATH . '/views/client/checkout.php';
        include BASE_PATH . '/views/client/layout.php';
    }

    // ========================= 2. XỬ LÝ LƯU ĐƠN HÀNG (PROCESS) =========================
    public function process() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php");
            exit;
        }

        $user = $this->checkLogin();
        $cart = $_SESSION['ShoppingCart'] ?? [];
        
        if (empty($cart)) { 
            header("Location: index.php?controller=giohang"); 
            exit; 
        }

        try {
            $this->pdo->beginTransaction();

            // 1. Tìm hoặc cập nhật thông tin khách hàng
            $khachHangId = $this->model->getOrCreateCustomer([
                'HoTen' => trim($_POST['HoTenNguoiNhan']),
                'Email' => trim($_POST['Email']),
                'SoDienThoai' => trim($_POST['SoDienThoaiNguoiNhan']),
                'DiaChi' => trim($_POST['DiaChiNhanHang'])
            ]);

            // 2. Tính toán tiền hàng
            $tongTienHang = 0; 
            $tongGiamGia = 0; 
            $phiVanChuyen = 30000; // Có thể để logic free ship đơn từ 1tr tại đây

            foreach($cart as $item) {
                $tongTienHang += $item['DonGia'] * $item['SoLuong'];
                $tongGiamGia += ($item['GiamGia'] ?? 0) * $item['SoLuong'];
            }

            $orderCode = "DH" . date("YmdHis") . rand(10, 99);
            $totalFinal = ($tongTienHang + $phiVanChuyen - $tongGiamGia);
            $method = $_POST['PhuongThucThanhToan'] ?? 'COD';

            // 3. Lưu đơn hàng chính
            $orderId = $this->model->createOrder([
                'code' => $orderCode, 
                'khId' => $khachHangId,
                'name' => trim($_POST['HoTenNguoiNhan']), 
                'phone' => trim($_POST['SoDienThoaiNguoiNhan']),
                'address' => trim($_POST['DiaChiNhanHang']), 
                'totalHang' => $tongTienHang,
                'ship' => $phiVanChuyen, 
                'discount' => $tongGiamGia,
                'totalPay' => $totalFinal, 
                'userId' => $user['TaiKhoanId'],
                'method' => ($method === 'VNPAY' ? 'VNPAY' : 'COD')
            ]);

            // 4. Lưu chi tiết đơn hàng
            foreach($cart as $item) {
                $this->model->createOrderDetailAndReduceStock([
                    'dhId' => $orderId, 
                    'spId' => $item['SanPhamId'],
                    'name' => $item['TenSanPham'], 
                    'price' => $item['DonGia'],
                    'qty' => $item['SoLuong'], 
                    'discount' => $item['GiamGia'] ?? 0,
                    'subtotal' => ($item['DonGia'] - ($item['GiamGia'] ?? 0)) * $item['SoLuong']
                ]);
            }

            $this->pdo->commit();
            
            // QUAN TRỌNG: Lưu mã đơn hàng vào Session để trang Success sử dụng
            $_SESSION['LastCreatedOrderCode'] = $orderCode; 
            
            // 5. Xử lý thanh toán theo phương thức
            if ($method === 'VNPAY') {
                $this->execVnPay($orderCode, $totalFinal);
            } else {
                $_SESSION['ShoppingCart'] = []; // Xóa giỏ hàng sau khi đặt COD thành công
                $_SESSION['success'] = "Đặt hàng thành công! Cảm ơn bạn đã tin dùng Karma Eyewear.";
                header("Location: index.php?controller=thanhtoan&action=success");
                exit;
            }

        } catch (Exception $e) {
            if($this->pdo->inTransaction()) $this->pdo->rollBack();
            die("Lỗi đặt hàng: " . $e->getMessage());
        }
    }

    // ========================= 3. TÍCH HỢP VNPAY =========================
    private function execVnPay($orderCode, $amount) {
        $vnp_TmnCode = VNP_TMNCODE; 
        $vnp_HashSecret = VNP_HASHSECRET;
        $vnp_Url = VNP_URL;
        $vnp_Returnurl = VNP_RETURNURL;

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $amount * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $_SERVER['REMOTE_ADDR'],
            "vnp_Locale" => 'vn',
            "vnp_OrderInfo" => "Thanh toán đơn hàng " . $orderCode,
            "vnp_OrderType" => 'billpayment',
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $orderCode
        );

        ksort($inputData);
        $query = ""; 
        $i = 0; 
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) { $hashdata .= '&' . urlencode($key) . "=" . urlencode($value); }
            else { $hashdata .= urlencode($key) . "=" . urlencode($value); $i = 1; }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        header('Location: ' . $vnp_Url);
        exit;
    }

    public function vnpay_return() {
        $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") { $inputData[$key] = $value; }
        }
        unset($inputData['vnp_SecureHash']); 
        ksort($inputData);
        $hashData = ""; $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) { $hashData .= '&' . urlencode($key) . "=" . urlencode($value); }
            else { $hashData .= urlencode($key) . "=" . urlencode($value); $i = 1; }
        }

        if (hash_hmac('sha512', $hashData, VNP_HASHSECRET) == $vnp_SecureHash) {
            if ($_GET['vnp_ResponseCode'] == '00') {
                $this->model->updatePaymentStatus($_GET['vnp_TxnRef'], 'PAID', $_GET['vnp_TransactionNo']);
                
                $_SESSION['LastCreatedOrderCode'] = $_GET['vnp_TxnRef'];
                $_SESSION['ShoppingCart'] = [];
                $_SESSION['success'] = "Thanh toán qua VNPAY thành công!";
                
                header("Location: index.php?controller=thanhtoan&action=success");
                exit;
            }
        }
        $_SESSION['error'] = "Thanh toán thất bại hoặc đã bị hủy.";
        header("Location: index.php?controller=giohang");
        exit;
    }

    // ========================= 4. TRANG THÀNH CÔNG (SUCCESS) =========================
    public function success() {
        $storeInfo = $this->homeModel->getStoreInfo();

        // Lấy mã đơn hàng từ Session
        $orderCode = $_SESSION['LastCreatedOrderCode'] ?? null;
        $order = null;

        if ($orderCode) {
            // Lấy thông tin chi tiết từ Model để hiển thị lên View
            $order = $this->model->getOrderByCode($orderCode);
        }

        // Chặn trường hợp truy cập trực tiếp trang success mà không có đơn hàng
        if (!$order) {
            header("Location: index.php");
            exit;
        }

        $title = "Đặt hàng thành công";
        $viewContent = BASE_PATH . '/views/client/checkout_success.php';
        
        // Lưu ý: Đừng unset mã đơn ngay tại đây nếu view cần dùng AJAX để in hóa đơn, 
        // nhưng nếu chỉ hiện thông báo thì để nguyên cho view xử lý.
        include BASE_PATH . '/views/client/layout.php';
    }
}