<?php
class AdminSanPhamModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // 1. Lấy danh sách sản phẩm (Theo lọc trạng thái và từ khóa)
    // Thêm tham số $userId vào đầu hàm
    public function getProducts($statusProduct = "stock", $keyword = "", $userId = null) {
        $sql = "SELECT sp.*, th.TenThuongHieu, lsp.TenLoaiSanPham 
                FROM sanpham sp 
                LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId 
                LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId 
                WHERE 1=1";
        $params = [];

        // --- PHÂN QUYỀN: Cực kỳ quan trọng ---
        if ($userId !== null) {
            $sql .= " AND sp.CreatedById = ?";
            $params[] = $userId;
        }

        if (!empty($keyword)) {
            $sql .= " AND (sp.MaSanPham LIKE ? OR sp.TenSanPham LIKE ?)";
            $search = "%$keyword%";
            array_push($params, $search, $search);
        }

        switch ($statusProduct) {
            case "outofstock":
                $sql .= " AND sp.TrangThai = 1 AND sp.SoLuongTon <= 0";
                break;
            case "inactive":
                $sql .= " AND sp.TrangThai = 2";
                break;
            default: // stock
                $sql .= " AND sp.TrangThai = 1 AND sp.SoLuongTon > 0";
                break;
        }

        $sql .= " ORDER BY COALESCE(sp.UpdatedAt, sp.CreatedAt) DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // 2. Lấy 1 sản phẩm theo ID
    public function getProductById($id) {
        $stmt = $this->db->prepare("SELECT * FROM sanpham WHERE SanPhamId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // 3. THÊM MỚI sản phẩm
    public function createProduct($data) {
    $sql = "INSERT INTO sanpham (MaSanPham, TenSanPham, MoTaNgan, MoTaChiTiet, GiaGoc, GiaBan, SoLuongTon, 
                                ThuongHieuId, LoaiSanPhamId, TrangThai, IsFeatured, HinhAnhChinh, CreatedById, CreatedAt) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $params = [
        $data['MaSanPham'], 
        $data['TenSanPham'], 
        $data['MoTaNgan'], 
        $data['MoTaChiTiet'],
        $data['GiaGoc'], 
        $data['GiaBan'], 
        $data['SoLuongTon'], 
        $data['ThuongHieuId'], 
        $data['LoaiSanPhamId'], 
        $data['TrangThai'] ?? 1, 
        $data['IsFeatured'] ?? 0, 
        $data['HinhAnhChinh'] ?? 'default.jpg',
        $data['CreatedById'] // <--- Bổ sung dòng này
    ];
    
    return $this->db->prepare($sql)->execute($params);
}

    // 4. CẬP NHẬT sản phẩm
    public function updateProduct($id, $data) {
        $sql = "UPDATE sanpham SET MaSanPham = ?, TenSanPham = ?, MoTaNgan = ?, MoTaChiTiet = ?, 
                GiaGoc = ?, GiaBan = ?, SoLuongTon = ?, ThuongHieuId = ?, LoaiSanPhamId = ?, 
                TrangThai = ?, IsFeatured = ?, UpdatedAt = NOW()";
        
        $params = [
            $data['MaSanPham'], $data['TenSanPham'], $data['MoTaNgan'], $data['MoTaChiTiet'],
            $data['GiaGoc'], $data['GiaBan'], $data['SoLuongTon'], $data['ThuongHieuId'], 
            $data['LoaiSanPhamId'], $data['TrangThai'], $data['IsFeatured']
        ];

        if (isset($data['HinhAnhChinh'])) {
            $sql .= ", HinhAnhChinh = ?";
            $params[] = $data['HinhAnhChinh'];
        }

        $sql .= " WHERE SanPhamId = ?";
        $params[] = $id;
        
        return $this->db->prepare($sql)->execute($params);
    }

    // 5. Bật/Tắt nổi bật
    public function toggleFeatured($id) {
        return $this->db->prepare("UPDATE sanpham SET IsFeatured = NOT IsFeatured, UpdatedAt = NOW() WHERE SanPhamId = ?")->execute([$id]);
    }

    // 6. Ngừng bán (Soft Delete)
    public function softDelete($id) {
    // Chuyển trạng thái sang 2 (Ngừng bán) và set số lượng tồn về 0
    $sql = "UPDATE sanpham 
            SET TrangThai = 2, 
                SoLuongTon = 0, 
                UpdatedAt = NOW() 
            WHERE SanPhamId = ?";
    return $this->db->prepare($sql)->execute([$id]);
}

    // --- BỔ SUNG CÁC HÀM LẤY DATA PHỤ ---

    public function getAllBrands() {
        return $this->db->query("SELECT ThuongHieuId, TenThuongHieu FROM thuonghieu WHERE IsActive = 1 ORDER BY TenThuongHieu ASC")->fetchAll();
    }

    public function getAllCategories() {
        return $this->db->query("SELECT LoaiSanPhamId, TenLoaiSanPham FROM loaisanpham WHERE IsActive = 1 ORDER BY TenLoaiSanPham ASC")->fetchAll();
    }

    public function checkProductInOrders($id) {
    // Kiểm tra trong bảng chi tiết đơn hàng
    $stmt = $this->db->prepare("SELECT COUNT(*) FROM chitietdonhang WHERE SanPhamId = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn() > 0;
}

    // Thêm hàm xóa cứng (Chỉ dùng khi chưa có đơn hàng)
    public function delete($id) {
        return $this->db->prepare("DELETE FROM sanpham WHERE SanPhamId = ?")->execute([$id]);
    }
}