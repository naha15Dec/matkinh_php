<?php
class HomeModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // Lấy sản phẩm giảm giá (GiaGoc > GiaBan)
    public function getDiscountProducts($limit = 8) {
        $sql = "SELECT sp.*, lsp.TenLoaiSanPham, th.TenThuongHieu 
                FROM sanpham sp
                LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
                LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
                WHERE sp.TrangThai = 1 AND sp.GiaGoc > sp.GiaBan
                ORDER BY (sp.GiaGoc - sp.GiaBan) DESC, sp.CreatedAt DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy sản phẩm mới nhất
    public function getNewProducts($limit = 8) {
        $sql = "SELECT sp.*, lsp.TenLoaiSanPham, th.TenThuongHieu 
                FROM sanpham sp
                LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
                LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
                WHERE sp.TrangThai = 1
                ORDER BY sp.CreatedAt DESC, sp.SanPhamId DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy bài viết mới nhất
    public function getLatestBlogs($limit = 3) {
        $sql = "SELECT * FROM baiviet 
                WHERE TrangThai = 2 
                ORDER BY COALESCE(NgayDang, CreatedAt) DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Lấy thông tin cửa hàng
    public function getStoreInfo() {
        $sql = "SELECT * FROM thongtincuahang 
                WHERE IsActive = 1 
                ORDER BY UpdatedAt DESC 
                LIMIT 1";
        $result = $this->db->query($sql)->fetch();
        
        // Trả về mảng rỗng thay vì false để tránh lỗi "Trying to access array offset on value of type bool" trong View
        return $result ? $result : [];
    }

    // Tìm kiếm nhanh theo Mã hoặc ID
    public function findProductByCodeOrId($keyword) {
        $sql = "SELECT SanPhamId FROM sanpham 
                WHERE TrangThai = 1 AND (MaSanPham = :keyword OR SanPhamId = :id) 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        
        $id = is_numeric($keyword) ? (int)$keyword : 0; 
        $stmt->bindValue(':keyword', $keyword, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
}