<?php

class ThanhToanModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getOrCreateCustomer($data) {
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
                    DiaChi = :address,
                    UpdatedAt = NOW()
                WHERE KhachHangId = :id
            ";

            $stmtUpdate = $this->db->prepare($updateSql);
            $stmtUpdate->execute([
                'name' => $data['HoTen'],
                'email' => $email !== '' ? $email : null,
                'address' => $data['DiaChi'],
                'id' => $customer['KhachHangId']
            ]);

            return $customer['KhachHangId'];
        }

        $code = "KH" . date("YmdHis") . rand(100, 999);

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

        return $this->db->lastInsertId();
    }

    public function createOrder($orderData) {
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
                1,
                :method,
                'PENDING',
                :userId,
                NOW(),
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'code' => $orderData['code'],
            'khId' => $orderData['khId'],
            'name' => $orderData['name'],
            'phone' => $orderData['phone'],
            'address' => $orderData['address'],
            'totalHang' => $orderData['totalHang'],
            'ship' => $orderData['ship'],
            'discount' => $orderData['discount'],
            'totalPay' => $orderData['totalPay'],
            'method' => $orderData['method'],
            'userId' => $orderData['userId']
        ]);

        return $this->db->lastInsertId();
    }

    public function createOrderDetailAndReduceStock($detail) {
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
            'dhId' => $detail['dhId'],
            'spId' => $detail['spId'],
            'name' => $detail['name'],
            'price' => $detail['price'],
            'qty' => $detail['qty'],
            'discount' => $detail['discount'],
            'subtotal' => $detail['subtotal']
        ]);

        $sqlStock = "
            UPDATE sanpham
            SET
                SoLuongTon = SoLuongTon - :qtyReduce,
                UpdatedAt = NOW()
            WHERE
                SanPhamId = :spId
                AND SoLuongTon >= :qtyCheck
        ";

        $stmtStock = $this->db->prepare($sqlStock);

        $stmtStock->execute([
            'qtyReduce' => $detail['qty'],
            'qtyCheck' => $detail['qty'],
            'spId' => $detail['spId']
        ]);

        if ($stmtStock->rowCount() === 0) {
            throw new Exception("Sản phẩm không đủ tồn kho.");
        }
    }

    public function updatePaymentStatus($orderCode, $status, $transactionNo = null) {
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

        return $stmt->execute([
            'status' => $status,
            'transNo' => $transactionNo,
            'code' => $orderCode
        ]);
    }

    public function getOrderByCode($code) {
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
}