<?php

class ProfileModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getAccountById($id) {
        $sql = "
            SELECT tk.*, vt.MaVaiTro, vt.TenVaiTro
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

    public function updateProfile($id, $data) {
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

        return $stmt->execute([
            'name'    => trim($data['name'] ?? ''),
            'phone'   => trim($data['phone'] ?? ''),
            'address' => trim($data['address'] ?? ''),
            'gender'  => $data['gender'] !== '' ? $data['gender'] : null,
            'id'      => (int)$id
        ]);
    }

    public function updatePassword($id, $newHash) {
        $sql = "
            UPDATE taikhoan 
            SET 
                MatKhauHash = :hash,
                UpdatedAt = NOW()
            WHERE TaiKhoanId = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'hash' => $newHash,
            'id'   => (int)$id
        ]);
    }

    public function getOrdersByUser($userId, $page, $pageSize) {
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
            SELECT *
            FROM donhang
            WHERE CreatedById = :userId
            ORDER BY CreatedAt DESC, DonHangId DESC
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

    public function getOrderDetail($maDonHang, $userId) {
        $sql = "
            SELECT 
                dh.*,
                kh.MaKhachHang
            FROM donhang dh
            LEFT JOIN khachhang kh 
                ON dh.KhachHangId = kh.KhachHangId
            WHERE 
                dh.MaDonHang = :code
                AND dh.CreatedById = :userId
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'code'   => $maDonHang,
            'userId' => (int)$userId
        ]);

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $order['items'] = $this->getOrderItems($order['DonHangId']);
        }

        return $order;
    }

    public function getOrderItems($donHangId) {
        $sql = "
            SELECT *
            FROM chitietdonhang
            WHERE DonHangId = :id
            ORDER BY ChiTietDonHangId ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => (int)$donHangId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}