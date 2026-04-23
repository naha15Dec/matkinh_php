<?php
class SanPhamModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // ========================= 1. DANH SÁCH & BỘ LỌC =========================
    public function getFilteredProducts($filters, $page = 1, $pageSize = 9) {
        $sql = "SELECT sp.*, lsp.TenLoaiSanPham, th.TenThuongHieu 
                FROM sanpham sp
                LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
                LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
                WHERE sp.TrangThai = 1 ";
        
        $params = [];

        if (!empty($filters['CategoryId'])) {
            $sql .= " AND sp.LoaiSanPhamId = :catId ";
            $params['catId'] = $filters['CategoryId'];
        }

        if (!empty($filters['BrandId'])) {
            $sql .= " AND sp.ThuongHieuId = :brandId ";
            $params['brandId'] = $filters['BrandId'];
        }

        if (!empty($filters['Keyword'])) {
            $sql .= " AND sp.TenSanPham LIKE :keyword ";
            $params['keyword'] = '%' . $filters['Keyword'] . '%';
        }

        if (isset($filters['MinPrice'])) {
            $sql .= " AND sp.GiaBan >= :minPrice ";
            $params['minPrice'] = $filters['MinPrice'];
        }
        if (isset($filters['MaxPrice'])) {
            $sql .= " AND sp.GiaBan <= :maxPrice ";
            $params['maxPrice'] = $filters['MaxPrice'];
        }

        // Đếm tổng số lượng (để phân trang)
        $countSql = str_replace("SELECT sp.*, lsp.TenLoaiSanPham, th.TenThuongHieu", "SELECT COUNT(sp.SanPhamId) as total", $sql);
        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $totalCount = $stmtCount->fetch()['total'];

        // Thêm phân trang và sắp xếp
        $sql .= " ORDER BY sp.CreatedAt DESC, sp.SanPhamId ASC LIMIT :offset, :limit";
        $stmt = $this->db->prepare($sql);
        
        foreach($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->bindValue(':offset', ($page - 1) * $pageSize, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$pageSize, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'totalCount' => $totalCount
        ];
    }

    // ========================= 2. CHI TIẾT SẢN PHẨM =========================
    public function getChiTietSanPham($id) {
        $sql = "SELECT sp.*, lsp.TenLoaiSanPham, th.TenThuongHieu 
                FROM sanpham sp
                LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
                LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
                WHERE sp.SanPhamId = :id AND sp.TrangThai = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // ========================= 3. SẢN PHẨM LIÊN QUAN =========================
    public function getRelatedProducts($currentProductId, $categoryId, $brandId) {
        $sql = "SELECT * FROM sanpham 
                WHERE SanPhamId != :id 
                AND TrangThai = 1 
                AND (LoaiSanPhamId = :catId OR ThuongHieuId = :brandId)
                ORDER BY CreatedAt DESC LIMIT 8";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $currentProductId,
            'catId' => $categoryId,
            'brandId' => $brandId
        ]);
        return $stmt->fetchAll();
    }

    // ========================= 4. SẢN PHẨM GỢI Ý (LOCAL DB) =========================
    public function getRecommendedProductsLocal($currentProductId, $currentPrice) {
        $minPrice = $currentPrice * 0.8;
        $maxPrice = $currentPrice * 1.2;

        $sql = "SELECT * FROM sanpham 
                WHERE SanPhamId != :id 
                AND TrangThai = 1 
                AND GiaBan BETWEEN :minPrice AND :maxPrice
                ORDER BY RAND() LIMIT 4";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $currentProductId,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice
        ]);
        
        $results = $stmt->fetchAll();

        // Fallback: Nếu không có sp ngang giá thì lấy random
        if (empty($results)) {
            $sqlFallback = "SELECT * FROM sanpham WHERE SanPhamId != :id AND TrangThai = 1 ORDER BY RAND() LIMIT 4";
            $stmtFB = $this->db->prepare($sqlFallback);
            $stmtFB->execute(['id' => $currentProductId]);
            return $stmtFB->fetchAll();
        }

        return $results;
    }

    // ========================= 5. GHI LOG HÀNH VI =========================
    public function logUserBehavior($khachHangId, $sanPhamId, $loaiHanhVi, $sessionId, $trongSo = 1, $ghiChu = 'PRODUCT_DETAIL') {
        $sql = "INSERT INTO hanhvinguoidung (KhachHangId, SanPhamId, LoaiHanhVi, Nguon, SessionId, TrongSo, GhiChu, CreatedAt) 
                VALUES (:khId, :spId, :hanhVi, 'WEB', :sessId, :trongSo, :ghiChu, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'khId' => $khachHangId ?: null,
            'spId' => $sanPhamId,
            'hanhVi' => $loaiHanhVi,
            'sessId' => $sessionId,
            'trongSo' => $trongSo,
            'ghiChu' => $ghiChu
        ]);
    }

    // ========================= 6. UTILITIES =========================
    public function getActiveBrands() {
        return $this->db->query("SELECT * FROM thuonghieu WHERE IsActive = 1 ORDER BY TenThuongHieu")->fetchAll();
    }

    public function getActiveCategories() {
        return $this->db->query("SELECT * FROM loaisanpham WHERE IsActive = 1 ORDER BY TenLoaiSanPham")->fetchAll();
    }
}