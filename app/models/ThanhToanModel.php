<?php

class ThanhToanModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getProductForCheckout($productId)
    {
        $sql = "
            SELECT 
                SanPhamId,
                TenSanPham,
                GiaBan,
                SoLuongTon,
                TrangThai
            FROM sanpham
            WHERE SanPhamId = :id
              AND TrangThai = 1
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => (int)$productId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrCreateCustomer($data)
    {
        $email = trim($data['Email'] ?? '');
        $phone = trim($data['SoDienThoai'] ?? '');

        if ($phone === '') {
            throw new Exception("Thiếu số điện thoại khách hàng.");
        }

        if ($email !== '') {
            $sql = "
                SELECT KhachHangId
                FROM khachhang
                WHERE SoDienThoai = :phone OR Email = :email
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'phone' => $phone,
                'email' => $email
            ]);
        } else {
            $sql = "
                SELECT KhachHangId
                FROM khachhang
                WHERE SoDienThoai = :phone
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'phone' => $phone
            ]);
        }

        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer) {
            $updateSql = "
                UPDATE khachhang
                SET
                    HoTen = :name,
                    Email = :email,
                    SoDienThoai = :phone,
                    DiaChi = :address,
                    UpdatedAt = NOW()
                WHERE KhachHangId = :id
            ";

            $stmtUpdate = $this->db->prepare($updateSql);
            $stmtUpdate->execute([
                'name' => $data['HoTen'],
                'email' => $email !== '' ? $email : null,
                'phone' => $phone,
                'address' => $data['DiaChi'],
                'id' => (int)$customer['KhachHangId']
            ]);

            return (int)$customer['KhachHangId'];
        }

        $code = $this->generateCustomerCode();

        $insertSql = "
            INSERT INTO khachhang (
                MaKhachHang,
                HoTen,
                Email,
                SoDienThoai,
                DiaChi,
                CreatedAt,
                IsActive
            ) VALUES (
                :code,
                :name,
                :email,
                :phone,
                :address,
                NOW(),
                1
            )
        ";

        $stmtInsert = $this->db->prepare($insertSql);
        $stmtInsert->execute([
            'code' => $code,
            'name' => $data['HoTen'],
            'email' => $email !== '' ? $email : null,
            'phone' => $phone,
            'address' => $data['DiaChi']
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function createOrder($orderData)
    {
        $sql = "
            INSERT INTO donhang (
                MaDonHang,
                KhachHangId,
                HoTenNguoiNhan,
                SoDienThoaiNguoiNhan,
                DiaChiNhanHang,
                TongTienHang,
                PhiVanChuyen,
                GiamGia,
                TongThanhToan,
                TrangThai,
                PhuongThucThanhToan,
                TrangThaiThanhToan,
                CreatedById,
                NgayDat,
                CreatedAt
            ) VALUES (
                :code,
                :khId,
                :name,
                :phone,
                :address,
                :totalHang,
                :ship,
                :discount,
                :totalPay,
                :status,
                :method,
                :paymentStatus,
                :userId,
                NOW(),
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'code' => $orderData['code'],
            'khId' => (int)$orderData['khId'],
            'name' => $orderData['name'],
            'phone' => $orderData['phone'],
            'address' => $orderData['address'],
            'totalHang' => $orderData['totalHang'],
            'ship' => $orderData['ship'],
            'discount' => $orderData['discount'],
            'totalPay' => $orderData['totalPay'],
            'status' => OrderStatusConstants::PENDING,
            'method' => $orderData['method'],
            'paymentStatus' => PaymentConstants::PENDING,
            'userId' => (int)$orderData['userId']
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function createOrderDetailAndReduceStock($detail)
    {
        $sqlStock = "
            UPDATE sanpham
            SET
                SoLuongTon = SoLuongTon - :qtyReduce,
                UpdatedAt = NOW()
            WHERE
                SanPhamId = :spId
                AND TrangThai = 1
                AND SoLuongTon >= :qtyCheck
        ";

        $stmtStock = $this->db->prepare($sqlStock);

        $stmtStock->execute([
            'qtyReduce' => (int)$detail['qty'],
            'qtyCheck' => (int)$detail['qty'],
            'spId' => (int)$detail['spId']
        ]);

        if ($stmtStock->rowCount() === 0) {
            throw new Exception("Sản phẩm không đủ tồn kho hoặc đã ngừng bán.");
        }

        $sqlDetail = "
            INSERT INTO chitietdonhang (
                DonHangId,
                SanPhamId,
                TenSanPhamSnapshot,
                DonGiaSnapshot,
                SoLuong,
                GiamGiaSnapshot,
                ThanhTien
            ) VALUES (
                :dhId,
                :spId,
                :name,
                :price,
                :qty,
                :discount,
                :subtotal
            )
        ";

        $stmtDetail = $this->db->prepare($sqlDetail);

        $stmtDetail->execute([
            'dhId' => (int)$detail['dhId'],
            'spId' => (int)$detail['spId'],
            'name' => $detail['name'],
            'price' => $detail['price'],
            'qty' => (int)$detail['qty'],
            'discount' => $detail['discount'],
            'subtotal' => $detail['subtotal']
        ]);

        if ($stmtDetail->rowCount() === 0) {
            throw new Exception("Không thể tạo chi tiết đơn hàng.");
        }
    }

    public function updatePaymentStatus($orderCode, $status, $transactionNo = null)
    {
        $sql = "
            UPDATE donhang
            SET
                TrangThaiThanhToan = :status,
                MaGiaoDichThanhToan = :transNo,
                NgayThanhToan = NOW(),
                UpdatedAt = NOW()
            WHERE MaDonHang = :code
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'status' => $status,
            'transNo' => $transactionNo,
            'code' => $orderCode
        ]);

        return $stmt->rowCount() > 0;
    }

    public function cancelFailedVnpayOrder($orderCode, $transactionNo = null)
    {
        try {
            $this->db->beginTransaction();

            $order = $this->getOrderByCode($orderCode);

            if (!$order) {
                $this->db->rollBack();
                return false;
            }

            if ((int)$order['TrangThai'] !== OrderStatusConstants::PENDING) {
                $this->db->rollBack();
                return false;
            }

            $items = $this->getOrderItems((int)$order['DonHangId']);

            foreach ($items as $item) {
                $this->restoreProductStock((int)$item['SanPhamId'], (int)$item['SoLuong']);
            }

            $sql = "
                UPDATE donhang
                SET 
                    TrangThai = :cancelled,
                    TrangThaiThanhToan = :paymentFailed,
                    MaGiaoDichThanhToan = :transNo,
                    NgayHuy = NOW(),
                    UpdatedAt = NOW()
                WHERE DonHangId = :id
                  AND TrangThai = :pending
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'cancelled' => OrderStatusConstants::CANCELLED,
                'paymentFailed' => PaymentConstants::FAILED,
                'transNo' => $transactionNo,
                'id' => (int)$order['DonHangId'],
                'pending' => OrderStatusConstants::PENDING
            ]);

            $this->addOrderHistory(
                (int)$order['DonHangId'],
                OrderStatusConstants::PENDING,
                OrderStatusConstants::CANCELLED,
                null,
                "Thanh toán VNPAY thất bại hoặc bị hủy, hệ thống tự hủy đơn và hoàn tồn kho."
            );

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return false;
        }
    }

    public function getOrderByCode($code)
    {
        $sql = "
            SELECT *
            FROM donhang
            WHERE MaDonHang = :code
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'code' => $code
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOrderItems($orderId)
    {
        $sql = "
            SELECT *
            FROM chitietdonhang
            WHERE DonHangId = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => (int)$orderId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function restoreProductStock($productId, $qty)
    {
        if ($productId <= 0 || $qty <= 0) {
            return false;
        }

        $sql = "
            UPDATE sanpham
            SET 
                SoLuongTon = SoLuongTon + :qty,
                UpdatedAt = NOW()
            WHERE SanPhamId = :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'qty' => (int)$qty,
            'id' => (int)$productId
        ]);

        return $stmt->rowCount() > 0;
    }

    public function addOrderHistory($orderId, $oldStatus, $newStatus, $userId, $note)
    {
        $sql = "
            INSERT INTO lichsutrangthaidonhang (
                DonHangId,
                TrangThaiCu,
                TrangThaiMoi,
                ThayDoiBoiId,
                GhiChu,
                CreatedAt
            ) VALUES (
                :orderId,
                :oldStatus,
                :newStatus,
                :userId,
                :note,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'orderId' => (int)$orderId,
            'oldStatus' => (int)$oldStatus,
            'newStatus' => (int)$newStatus,
            'userId' => $userId !== null ? (int)$userId : null,
            'note' => $note
        ]);

        return $stmt->rowCount() > 0;
    }

    public function orderCodeExists($code)
    {
        $sql = "SELECT COUNT(*) FROM donhang WHERE MaDonHang = :code";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'code' => $code
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function generateCustomerCode()
    {
        do {
            $code = "KH" . date("YmdHis") . rand(100, 999);
        } while ($this->customerCodeExists($code));

        return $code;
    }

    private function customerCodeExists($code)
    {
        $sql = "SELECT COUNT(*) FROM khachhang WHERE MaKhachHang = :code";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'code' => $code
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }
}