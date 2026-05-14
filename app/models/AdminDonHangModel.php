<?php
class AdminDonHangModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getOrders($currentUser, $keyword = "", $status = null)
    {
        $roleCode = strtoupper(trim($currentUser['MaVaiTro'] ?? ''));
        $currentUserId = (int)($currentUser['TaiKhoanId'] ?? 0);

        $sql = "SELECT 
                    dh.*, 
                    kh.HoTen AS TenKhachHang,
                    tk1.HoTen AS NguoiTao,
                    tk.HoTen AS NguoiXacNhan,
                    tk2.HoTen AS ShipperName,
                    COALESCE(
                        (
                            SELECT SUM(ct.SoLuong) 
                            FROM chitietdonhang ct 
                            WHERE ct.DonHangId = dh.DonHangId
                        ), 
                        0
                    ) AS SoLuongSanPham
                FROM donhang dh
                LEFT JOIN khachhang kh ON dh.KhachHangId = kh.KhachHangId
                LEFT JOIN taikhoan tk ON dh.ConfirmedById = tk.TaiKhoanId
                LEFT JOIN taikhoan tk1 ON dh.CreatedById = tk1.TaiKhoanId
                LEFT JOIN taikhoan tk2 ON dh.ShipperId = tk2.TaiKhoanId
                WHERE 1=1";

        $params = [];

        if ($roleCode === 'SHIPPER') {
            $sql .= " AND dh.ShipperId = ?";
            $params[] = $currentUserId;
        } elseif (!in_array($roleCode, ['ADMIN', 'STAFF'], true)) {
            $sql .= " AND 1=0";
        }

        if (!empty($keyword)) {
            $sql .= " AND (
                        dh.MaDonHang LIKE ? 
                        OR dh.HoTenNguoiNhan LIKE ? 
                        OR dh.SoDienThoaiNguoiNhan LIKE ?
                    )";

            $search = "%{$keyword}%";
            array_push($params, $search, $search, $search);
        }

        if ($status !== null && $status !== '') {
            $sql .= " AND dh.TrangThai = ?";
            $params[] = (int)$status;
        }

        $sql .= " ORDER BY dh.NgayDat DESC, dh.DonHangId DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getOrderById($id)
    {
        $sql = "SELECT 
                    dh.*, 
                    kh.HoTen AS TenKhachHang,
                    kh.Email AS EmailKhachHang,
                    kh.SoDienThoai AS SoDienThoaiKhachHang,
                    tk2.HoTen AS ShipperName,
                    tk2.TenDangNhap AS ShipperUsername,
                    tk.HoTen AS ConfirmedByName,
                    tk1.HoTen AS CreatedByName
                FROM donhang dh 
                LEFT JOIN khachhang kh ON dh.KhachHangId = kh.KhachHangId
                LEFT JOIN taikhoan tk2 ON dh.ShipperId = tk2.TaiKhoanId
                LEFT JOIN taikhoan tk ON dh.ConfirmedById = tk.TaiKhoanId
                LEFT JOIN taikhoan tk1 ON dh.CreatedById = tk1.TaiKhoanId
                WHERE dh.DonHangId = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);

        return $stmt->fetch();
    }

    public function getOrderItems($orderId)
    {
        $sql = "SELECT 
                    ct.*, 
                    sp.HinhAnhChinh, 
                    sp.MaSanPham,
                    sp.TenSanPham AS TenSanPhamHienTai
                FROM chitietdonhang ct 
                LEFT JOIN sanpham sp ON ct.SanPhamId = sp.SanPhamId 
                WHERE ct.DonHangId = ?
                ORDER BY ct.ChiTietDonHangId ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$orderId]);

        return $stmt->fetchAll();
    }

    public function getOrderHistory($orderId)
    {
        $sql = "SELECT 
                    ls.*, 
                    tk.HoTen AS NguoiCapNhat,
                    tk.TenDangNhap AS TenDangNhapCapNhat,
                    vt.TenVaiTro AS VaiTroNguoiCapNhat
                FROM lichsutrangthaidonhang ls 
                LEFT JOIN taikhoan tk ON ls.ThayDoiBoiId = tk.TaiKhoanId
                LEFT JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId
                WHERE ls.DonHangId = ? 
                ORDER BY ls.CreatedAt DESC, ls.LichSuId DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$orderId]);

        return $stmt->fetchAll();
    }

    public function updateStatus($orderId, $newStatus, $data = [])
    {
        $fields = [
            "TrangThai = ?",
            "UpdatedAt = NOW()"
        ];

        $params = [
            (int)$newStatus
        ];

        if (array_key_exists('ConfirmedById', $data)) {
            $fields[] = "ConfirmedById = ?";
            $params[] = (int)$data['ConfirmedById'];
        }

        if (array_key_exists('NgayXacNhan', $data)) {
            $fields[] = "NgayXacNhan = NOW()";
        }

        if (array_key_exists('NgayGiao', $data)) {
            $fields[] = "NgayGiao = NOW()";
        }

        if (array_key_exists('NgayHoanTat', $data)) {
            $fields[] = "NgayHoanTat = NOW()";
        }

        if (array_key_exists('NgayHuy', $data)) {
            $fields[] = "NgayHuy = NOW()";
        }

        if (array_key_exists('TrangThaiThanhToan', $data)) {
            $fields[] = "TrangThaiThanhToan = ?";
            $params[] = $data['TrangThaiThanhToan'];
        }

        if (array_key_exists('NgayThanhToan', $data)) {
            $fields[] = "NgayThanhToan = NOW()";
        } elseif (array_key_exists('TrangThaiThanhToan', $data)) {
            $fields[] = "NgayThanhToan = NOW()";
        }

        if (array_key_exists('ShipperId', $data)) {
            $fields[] = "ShipperId = ?";
            $params[] = (int)$data['ShipperId'];
        }

        $params[] = (int)$orderId;

        $sql = "UPDATE donhang 
                SET " . implode(", ", $fields) . " 
                WHERE DonHangId = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function addHistory($orderId, $oldStatus, $newStatus, $note, $userId)
    {
        $sql = "INSERT INTO lichsutrangthaidonhang (
                    DonHangId, 
                    TrangThaiCu, 
                    TrangThaiMoi, 
                    ThayDoiBoiId, 
                    GhiChu, 
                    CreatedAt
                ) VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            (int)$orderId,
            (int)$oldStatus,
            (int)$newStatus,
            (int)$userId,
            trim((string)$note)
        ]);

        return $stmt->rowCount() > 0;
    }

    public function getActiveShippers()
    {
        $sql = "SELECT 
                    tk.TaiKhoanId, 
                    tk.HoTen, 
                    tk.TenDangNhap,
                    tk.SoDienThoai
                FROM taikhoan tk 
                JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId 
                WHERE vt.MaVaiTro = 'SHIPPER' 
                  AND vt.IsActive = 1
                  AND tk.IsActive = 1
                ORDER BY tk.HoTen ASC, tk.TenDangNhap ASC";

        return $this->db->query($sql)->fetchAll();
    }

    public function isActiveShipper($shipperId)
    {
        $sql = "SELECT COUNT(*)
                FROM taikhoan tk
                JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId
                WHERE tk.TaiKhoanId = ?
                  AND tk.IsActive = 1
                  AND vt.IsActive = 1
                  AND vt.MaVaiTro = 'SHIPPER'";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$shipperId]);

        return (int)$stmt->fetchColumn() > 0;
    }
}