<?php
class AdminDonHangModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getOrders($currentUser, $keyword = "", $status = null) {
    // Thêm Subquery: (SELECT SUM(SoLuong) FROM chitietdonhang WHERE DonHangId = dh.DonHangId) as SoLuongSanPham
    $sql = "SELECT dh.*, kh.HoTen as TenKhachHang, 
                   tk1.HoTen as NguoiTao, tk.HoTen as NguoiXacNhan, tk2.HoTen as ShipperName,
                   (SELECT SUM(SoLuong) FROM chitietdonhang WHERE DonHangId = dh.DonHangId) as SoLuongSanPham
            FROM donhang dh
            LEFT JOIN khachhang kh ON dh.KhachHangId = kh.KhachHangId
            LEFT JOIN taikhoan tk ON dh.ConfirmedById = tk.TaiKhoanId
            LEFT JOIN taikhoan tk1 ON dh.CreatedById = tk1.TaiKhoanId
            LEFT JOIN taikhoan tk2 ON dh.ShipperId = tk2.TaiKhoanId
            WHERE 1=1";
    $params = [];

    // Phân quyền Shipper
    if (strtoupper($currentUser['MaVaiTro']) === 'SHIPPER') {
        $sql .= " AND dh.ShipperId = ?";
        $params[] = $currentUser['TaiKhoanId'];
    }

    // Tìm kiếm
    if (!empty($keyword)) {
        $sql .= " AND (dh.MaDonHang LIKE ? OR dh.HoTenNguoiNhan LIKE ? OR dh.SoDienThoaiNguoiNhan LIKE ?)";
        $search = "%$keyword%";
        array_push($params, $search, $search, $search);
    }

    // Lọc trạng thái
    if ($status !== null && $status !== '') {
        $sql .= " AND dh.TrangThai = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY dh.NgayDat DESC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

    public function getOrderById($id) {
        $stmt = $this->db->prepare("SELECT dh.*, kh.HoTen as TenKhachHang, tk2.HoTen as ShipperName, tk.HoTen as ConfirmedByName
                                    FROM donhang dh 
                                    LEFT JOIN khachhang kh ON dh.KhachHangId = kh.KhachHangId
                                    LEFT JOIN taikhoan tk2 ON dh.ShipperId = tk2.TaiKhoanId
                                    LEFT JOIN taikhoan tk ON dh.ConfirmedById = tk.TaiKhoanId
                                    WHERE dh.DonHangId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getOrderItems($orderId) {
        $stmt = $this->db->prepare("SELECT ct.*, sp.HinhAnhChinh, sp.MaSanPham 
                                    FROM chitietdonhang ct 
                                    LEFT JOIN sanpham sp ON ct.SanPhamId = sp.SanPhamId 
                                    WHERE ct.DonHangId = ?");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function getOrderHistory($orderId) {
        $stmt = $this->db->prepare("SELECT ls.*, tk.HoTen as NguoiCapNhat 
                                    FROM lichsutrangthaidonhang ls 
                                    LEFT JOIN taikhoan tk ON ls.ThayDoiBoiId = tk.TaiKhoanId 
                                    WHERE ls.DonHangId = ? ORDER BY ls.CreatedAt DESC");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public function updateStatus($orderId, $newStatus, $data = []) {
        $fields = ["TrangThai = ?", "UpdatedAt = NOW()"];
        $params = [$newStatus];

        if (isset($data['ConfirmedById'])) { $fields[] = "ConfirmedById = ?"; $params[] = $data['ConfirmedById']; }
        if (isset($data['NgayXacNhan'])) { $fields[] = "NgayXacNhan = NOW()"; }
        if (isset($data['NgayGiao'])) { $fields[] = "NgayGiao = NOW()"; }
        if (isset($data['NgayHoanTat'])) { $fields[] = "NgayHoanTat = NOW()"; }
        if (isset($data['NgayHuy'])) { $fields[] = "NgayHuy = NOW()"; }
        if (isset($data['TrangThaiThanhToan'])) { $fields[] = "TrangThaiThanhToan = ?"; $params[] = $data['TrangThaiThanhToan']; $fields[] = "NgayThanhToan = NOW()"; }
        if (isset($data['ShipperId'])) { $fields[] = "ShipperId = ?"; $params[] = $data['ShipperId']; }

        $params[] = $orderId;
        $sql = "UPDATE donhang SET " . implode(", ", $fields) . " WHERE DonHangId = ?";
        return $this->db->prepare($sql)->execute($params);
    }

    public function addHistory($orderId, $oldStatus, $newStatus, $note, $userId) {
        $sql = "INSERT INTO lichsutrangthaidonhang (DonHangId, TrangThaiCu, TrangThaiMoi, ThayDoiBoiId, GhiChu, CreatedAt) 
                VALUES (?, ?, ?, ?, ?, NOW())";
        return $this->db->prepare($sql)->execute([$orderId, $oldStatus, $newStatus, $userId, $note]);
    }

    public function getActiveShippers() {
        return $this->db->query("SELECT tk.TaiKhoanId, tk.HoTen, tk.TenDangNhap 
                                 FROM taikhoan tk 
                                 JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId 
                                 WHERE vt.MaVaiTro = 'SHIPPER' AND tk.IsActive = 1")->fetchAll();
    }
}