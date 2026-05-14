<?php

require_once BASE_PATH . '/app/models/ThanhToanModel.php';
require_once BASE_PATH . '/app/models/HomeModel.php';
require_once BASE_PATH . '/app/helpers/OrderConstants.php';

class ThanhToanController
{
    private $pdo;
    private $model;
    private $homeModel;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->model = new ThanhToanModel($pdo);
        $this->homeModel = new HomeModel($pdo);
    }

    private function checkLogin()
    {
        if (!isset($_SESSION['LoginInformation'])) {
            $_SESSION['NotificationLogin'] = "Bạn cần đăng nhập để thực hiện thanh toán.";
            header("Location: index.php?controller=taikhoan&action=login");
            exit;
        }

        return $_SESSION['LoginInformation'];
    }

    public function index()
    {
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

        require BASE_PATH . '/views/client/layout.php';
    }

    public function process()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=thanhtoan");
            exit;
        }

        $user = $this->checkLogin();
        $cart = $_SESSION['ShoppingCart'] ?? [];

        if (empty($cart)) {
            $_SESSION['error'] = "Giỏ hàng của bạn đang trống.";
            header("Location: index.php?controller=giohang");
            exit;
        }

        $receiverName = trim($_POST['HoTenNguoiNhan'] ?? '');
        $receiverPhone = trim($_POST['SoDienThoaiNguoiNhan'] ?? '');
        $receiverEmail = trim($_POST['Email'] ?? '');
        $receiverAddress = trim($_POST['DiaChiNhanHang'] ?? '');

        $method = strtoupper(trim($_POST['PhuongThucThanhToan'] ?? PaymentConstants::COD));
        $method = ($method === PaymentConstants::VNPAY) ? PaymentConstants::VNPAY : PaymentConstants::COD;

        if ($receiverName === '' || $receiverPhone === '' || $receiverAddress === '') {
            $_SESSION['error'] = "Vui lòng nhập đầy đủ họ tên, số điện thoại và địa chỉ nhận hàng.";
            header("Location: index.php?controller=thanhtoan");
            exit;
        }

        if ($receiverEmail !== '' && !filter_var($receiverEmail, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Email không đúng định dạng.";
            header("Location: index.php?controller=thanhtoan");
            exit;
        }

        try {
            $this->pdo->beginTransaction();

            $khachHangId = $this->model->getOrCreateCustomer([
                'HoTen'       => $receiverName,
                'Email'       => $receiverEmail,
                'SoDienThoai' => $receiverPhone,
                'DiaChi'      => $receiverAddress
            ]);

            $validatedItems = [];
            $tongTienHang = 0;

            foreach ($cart as $item) {
                $sanPhamId = (int)($item['SanPhamId'] ?? 0);
                $soLuong = (int)($item['SoLuong'] ?? 0);

                if ($sanPhamId <= 0 || $soLuong <= 0) {
                    throw new Exception("Dữ liệu giỏ hàng không hợp lệ.");
                }

                $product = $this->model->getProductForCheckout($sanPhamId);

                if (!$product) {
                    throw new Exception("Một sản phẩm trong giỏ hàng không tồn tại hoặc đã ngừng bán.");
                }

                if ((int)$product['SoLuongTon'] < $soLuong) {
                    throw new Exception("Sản phẩm {$product['TenSanPham']} không đủ tồn kho.");
                }

                $donGia = (float)$product['GiaBan'];
                $thanhTien = $donGia * $soLuong;

                $validatedItems[] = [
                    'SanPhamId' => $sanPhamId,
                    'TenSanPham' => $product['TenSanPham'],
                    'DonGia' => $donGia,
                    'SoLuong' => $soLuong,
                    'GiamGia' => 0,
                    'ThanhTien' => $thanhTien
                ];

                $tongTienHang += $thanhTien;
            }

            if ($tongTienHang <= 0) {
                throw new Exception("Tổng tiền đơn hàng không hợp lệ.");
            }

            $tongGiamGia = 0;
            $phiVanChuyen = $tongTienHang >= 1000000 ? 0 : 30000;
            $totalFinal = $tongTienHang + $phiVanChuyen - $tongGiamGia;

            $orderCode = $this->generateOrderCode();

            $orderId = $this->model->createOrder([
                'code'      => $orderCode,
                'khId'      => $khachHangId,
                'name'      => $receiverName,
                'phone'     => $receiverPhone,
                'address'   => $receiverAddress,
                'totalHang' => $tongTienHang,
                'ship'      => $phiVanChuyen,
                'discount'  => $tongGiamGia,
                'totalPay'  => $totalFinal,
                'userId'    => (int)($user['TaiKhoanId'] ?? 0),
                'method'    => $method
            ]);

            foreach ($validatedItems as $item) {
                $this->model->createOrderDetailAndReduceStock([
                    'dhId'     => $orderId,
                    'spId'     => $item['SanPhamId'],
                    'name'     => $item['TenSanPham'],
                    'price'    => $item['DonGia'],
                    'qty'      => $item['SoLuong'],
                    'discount' => $item['GiamGia'],
                    'subtotal' => $item['ThanhTien']
                ]);
            }

            $this->pdo->commit();

            $_SESSION['LastCreatedOrderCode'] = $orderCode;

            if ($method === PaymentConstants::VNPAY) {
                $this->execVnPay($orderCode, $totalFinal);
            }

            $_SESSION['ShoppingCart'] = [];
            $_SESSION['success'] = "Đặt hàng thành công! Cảm ơn bạn đã tin dùng Karma Eyewear.";

            header("Location: index.php?controller=thanhtoan&action=success");
            exit;

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            $_SESSION['error'] = "Lỗi đặt hàng: " . $e->getMessage();
            header("Location: index.php?controller=thanhtoan");
            exit;
        }
    }

    private function execVnPay($orderCode, $amount)
    {
        $vnp_TmnCode = VNP_TMNCODE;
        $vnp_HashSecret = VNP_HASHSECRET;
        $vnp_Url = VNP_URL;
        $vnp_Returnurl = VNP_RETURNURL;

        $inputData = [
            "vnp_Version"    => "2.1.0",
            "vnp_TmnCode"    => $vnp_TmnCode,
            "vnp_Amount"     => (int)round($amount * 100),
            "vnp_Command"    => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode"   => "VND",
            "vnp_IpAddr"     => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            "vnp_Locale"     => "vn",
            "vnp_OrderInfo"  => "Thanh toán đơn hàng " . $orderCode,
            "vnp_OrderType"  => "billpayment",
            "vnp_ReturnUrl"  => $vnp_Returnurl,
            "vnp_TxnRef"     => $orderCode
        ];

        ksort($inputData);

        $query = "";
        $hashdata = "";
        $i = 0;

        foreach ($inputData as $key => $value) {
            if ($i === 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }

            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;

        if (!empty($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        header('Location: ' . $vnp_Url);
        exit;
    }

    public function vnpay_return()
    {
        $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';

        $inputData = [];

        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) === "vnp_") {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        ksort($inputData);

        $hashData = "";
        $i = 0;

        foreach ($inputData as $key => $value) {
            if ($i === 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, VNP_HASHSECRET);

        $orderCode = $_GET['vnp_TxnRef'] ?? '';
        $responseCode = $_GET['vnp_ResponseCode'] ?? '';
        $transactionNo = $_GET['vnp_TransactionNo'] ?? null;

        if ($secureHash === $vnp_SecureHash && $responseCode === '00') {
            $this->model->updatePaymentStatus($orderCode, PaymentConstants::PAID, $transactionNo);

            $_SESSION['LastCreatedOrderCode'] = $orderCode;
            $_SESSION['ShoppingCart'] = [];
            $_SESSION['success'] = "Thanh toán qua VNPAY thành công!";

            header("Location: index.php?controller=thanhtoan&action=success");
            exit;
        }

        if ($secureHash === $vnp_SecureHash && $orderCode !== '') {
            $this->model->cancelFailedVnpayOrder($orderCode, $transactionNo);
        }

        $_SESSION['error'] = "Thanh toán thất bại hoặc đã bị hủy.";
        header("Location: index.php?controller=giohang");
        exit;
    }

    public function success()
    {
        $storeInfo = $this->homeModel->getStoreInfo();

        $orderCode = $_SESSION['LastCreatedOrderCode'] ?? null;
        $order = null;

        if ($orderCode) {
            $order = $this->model->getOrderByCode($orderCode);
        }

        if (!$order) {
            header("Location: index.php?controller=home");
            exit;
        }

        $title = "Đặt hàng thành công";
        $viewContent = BASE_PATH . '/views/client/checkout_success.php';

        require BASE_PATH . '/views/client/layout.php';
    }

    private function generateOrderCode()
    {
        do {
            $code = "DH" . date("YmdHis") . rand(100, 999);
        } while ($this->model->orderCodeExists($code));

        return $code;
    }
}