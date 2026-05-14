<?php

class ProfileModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getAccountById($id)
    {
        $sql = "
            SELECT 
                tk.*, 
                vt.MaVaiTro, 
                vt.TenVaiTro
            FROM taikhoan tk
            LEFT JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId
            WHERE tk.TaiKhoanId = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => (int)$id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $data)
    {
        $sql = "
            UPDATE taikhoan 
            SET 
                HoTen = :name,
                SoDienThoai = :phone,
                DiaChi = :address,
                GioiTinh = :gender,
                UpdatedAt = NOW()
            WHERE TaiKhoanId = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'name'    => trim($data['name'] ?? ''),
            'phone'   => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'gender'  => $data['gender'],
            'id'      => (int)$id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function updatePassword($id, $newHash)
    {
        $sql = "
            UPDATE taikhoan 
            SET 
                MatKhauHash = :hash,
                UpdatedAt = NOW()
            WHERE TaiKhoanId = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'hash' => $newHash,
            'id'   => (int)$id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function isPhoneExists($phone, $excludeId = 0)
    {
        if ($phone === null || trim($phone) === '') {
            return false;
        }

        $sql = "
            SELECT COUNT(*)
            FROM taikhoan
            WHERE SoDienThoai = :phone
              AND TaiKhoanId <> :id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'phone' => trim($phone),
            'id' => (int)$excludeId
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function getOrdersByUser($userId, $page, $pageSize)
    {
        $page = max(1, (int)$page);
        $pageSize = max(1, (int)$pageSize);
        $offset = ($page - 1) * $pageSize;

        $sqlCount = "
            SELECT COUNT(*) 
            FROM donhang 
            WHERE CreatedById = :userId
        ";

        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute([
            'userId' => (int)$userId
        ]);

        $totalCount = (int)$stmtCount->fetchColumn();

        $sql = "
            SELECT 
                DonHangId,
                MaDonHang,
                KhachHangId,
                CreatedById,
                HoTenNguoiNhan,
                SoDienThoaiNguoiNhan,
                DiaChiNhanHang,
                TongTienHang,
                PhiVanChuyen,
                GiamGia,
                TongThanhToan,
                TrangThai,
                NgayDat,
                NgayXacNhan,
                NgayGiao,
                NgayHoanTat,
                NgayHuy,
                PhuongThucThanhToan,
                TrangThaiThanhToan,
                MaGiaoDichThanhToan,
                NgayThanhToan,
                CreatedAt,
                UpdatedAt,
                COALESCE(
                    (
                        SELECT SUM(ct.SoLuong)
                        FROM chitietdonhang ct
                        WHERE ct.DonHangId = donhang.DonHangId
                    ),
                    0
                ) AS SoLuongSanPham
            FROM donhang
            WHERE CreatedById = :userId
            ORDER BY NgayDat DESC, DonHangId DESC
            LIMIT :offset, :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':userId', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$pageSize, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'totalCount' => $totalCount
        ];
    }

    public function getOrderDetail($maDonHang, $userId)
    {
        $sql = "
            SELECT 
                dh.*,
                kh.MaKhachHang,
                kh.HoTen AS TenKhachHang,
                tk.HoTen AS ShipperName
            FROM donhang dh
            LEFT JOIN khachhang kh ON dh.KhachHangId = kh.KhachHangId
            LEFT JOIN taikhoan tk ON dh.ShipperId = tk.TaiKhoanId
            WHERE 
                dh.MaDonHang = :code
                AND dh.CreatedById = :userId
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'code'   => trim($maDonHang),
            'userId' => (int)$userId
        ]);

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $order['items'] = $this->getOrderItems($order['DonHangId']);
        }

        return $order;
    }

    public function getOrderItems($donHangId)
    {
        $sql = "
            SELECT 
                ct.ChiTietDonHangId,
                ct.DonHangId,
                ct.SanPhamId,
                ct.TenSanPhamSnapshot,
                ct.DonGiaSnapshot,
                ct.SoLuong,
                ct.GiamGiaSnapshot,
                ct.ThanhTien,
                sp.MaSanPham,
                sp.HinhAnhChinh,
                sp.TenSanPham AS TenSanPhamHienTai
            FROM chitietdonhang ct
            LEFT JOIN sanpham sp ON ct.SanPhamId = sp.SanPhamId
            WHERE ct.DonHangId = :id
            ORDER BY ct.ChiTietDonHangId ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => (int)$donHangId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelPendingOrder($donHangId, $userId, $note = '')
    {
        try {
            $this->db->beginTransaction();

            $sql = "
                UPDATE donhang
                SET 
                    TrangThai = :cancelled,
                    NgayHuy = NOW(),
                    UpdatedAt = NOW()
                WHERE DonHangId = :donHangId
                  AND CreatedById = :userId
                  AND TrangThai = :pending
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'cancelled' => OrderStatusConstants::CANCELLED,
                'donHangId' => (int)$donHangId,
                'userId' => (int)$userId,
                'pending' => OrderStatusConstants::PENDING
            ]);

            if ($stmt->rowCount() <= 0) {
                $this->db->rollBack();
                return false;
            }

            $sqlHistory = "
                INSERT INTO lichsutrangthaidonhang (
                    DonHangId,
                    TrangThaiCu,
                    TrangThaiMoi,
                    ThayDoiBoiId,
                    GhiChu,
                    CreatedAt
                ) VALUES (
                    :donHangId,
                    :oldStatus,
                    :newStatus,
                    :userId,
                    :note,
                    NOW()
                )
            ";

            $stmtHistory = $this->db->prepare($sqlHistory);
            $stmtHistory->execute([
                'donHangId' => (int)$donHangId,
                'oldStatus' => OrderStatusConstants::PENDING,
                'newStatus' => OrderStatusConstants::CANCELLED,
                'userId' => (int)$userId,
                'note' => trim((string)$note)
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return false;
        }
    }
}