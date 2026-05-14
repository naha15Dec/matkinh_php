<?php
class SanPhamModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getFilteredProducts($filters, $page = 1, $pageSize = 9)
{
    $page = max(1, (int)$page);
    $pageSize = max(1, (int)$pageSize);
    $offset = ($page - 1) * $pageSize;

    $where = "
        WHERE sp.TrangThai = 1
          AND sp.SoLuongTon > 0
          AND (lsp.IsActive = 1 OR lsp.LoaiSanPhamId IS NULL)
          AND (th.IsActive = 1 OR th.ThuongHieuId IS NULL)
    ";

    $params = [];

    if (!empty($filters['CategoryId'])) {
        $where .= " AND sp.LoaiSanPhamId = :catId";
        $params[':catId'] = (int)$filters['CategoryId'];
    }

    if (!empty($filters['BrandId'])) {
        $where .= " AND sp.ThuongHieuId = :brandId";
        $params[':brandId'] = (int)$filters['BrandId'];
    }

    if (!empty($filters['Keyword'])) {
        $where .= " AND (
            sp.TenSanPham LIKE :keywordName
            OR sp.MaSanPham LIKE :keywordCode
            OR th.TenThuongHieu LIKE :keywordBrand
            OR lsp.TenLoaiSanPham LIKE :keywordType
        )";

        $search = '%' . trim($filters['Keyword']) . '%';

        $params[':keywordName'] = $search;
        $params[':keywordCode'] = $search;
        $params[':keywordBrand'] = $search;
        $params[':keywordType'] = $search;
    }

    if (isset($filters['MinPrice'])) {
        $where .= " AND sp.GiaBan >= :minPrice";
        $params[':minPrice'] = (float)$filters['MinPrice'];
    }

    if (isset($filters['MaxPrice'])) {
        $where .= " AND sp.GiaBan <= :maxPrice";
        $params[':maxPrice'] = (float)$filters['MaxPrice'];
    }

    $countSql = "
        SELECT COUNT(sp.SanPhamId) AS total
        FROM sanpham sp
        LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
        LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
        {$where}
    ";

    $stmtCount = $this->db->prepare($countSql);

    foreach ($params as $key => $value) {
        if (in_array($key, [':catId', ':brandId'], true)) {
            $stmtCount->bindValue($key, (int)$value, PDO::PARAM_INT);
        } else {
            $stmtCount->bindValue($key, $value);
        }
    }

    $stmtCount->execute();
    $totalCount = (int)$stmtCount->fetchColumn();

    $sql = "
        SELECT 
            sp.*,
            lsp.TenLoaiSanPham,
            th.TenThuongHieu
        FROM sanpham sp
        LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
        LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
        {$where}
        ORDER BY sp.CreatedAt DESC, sp.SanPhamId DESC
        LIMIT :offset, :limit
    ";

    $stmt = $this->db->prepare($sql);

    foreach ($params as $key => $value) {
        if (in_array($key, [':catId', ':brandId'], true)) {
            $stmt->bindValue($key, (int)$value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $value);
        }
    }

    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$pageSize, PDO::PARAM_INT);

    $stmt->execute();

    return [
        'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        'totalCount' => $totalCount
    ];
}

    public function getChiTietSanPham($id)
    {
        $sql = "
            SELECT 
                sp.*,
                lsp.TenLoaiSanPham,
                th.TenThuongHieu
            FROM sanpham sp
            LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
            LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
            WHERE sp.SanPhamId = :id
              AND sp.TrangThai = 1
              AND (lsp.IsActive = 1 OR lsp.LoaiSanPhamId IS NULL)
              AND (th.IsActive = 1 OR th.ThuongHieuId IS NULL)
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => (int)$id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRelatedProducts($currentProductId, $categoryId, $brandId)
    {
        $sql = "
            SELECT 
                sp.*,
                lsp.TenLoaiSanPham,
                th.TenThuongHieu
            FROM sanpham sp
            LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
            LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
            WHERE sp.SanPhamId <> :id
              AND sp.TrangThai = 1
              AND sp.SoLuongTon > 0
              AND (lsp.IsActive = 1 OR lsp.LoaiSanPhamId IS NULL)
              AND (th.IsActive = 1 OR th.ThuongHieuId IS NULL)
              AND (
                    sp.LoaiSanPhamId = :catId
                    OR sp.ThuongHieuId = :brandId
              )
            ORDER BY sp.CreatedAt DESC, sp.SanPhamId DESC
            LIMIT 8
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => (int)$currentProductId,
            ':catId' => (int)$categoryId,
            ':brandId' => (int)$brandId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecommendedProductsLocal($currentProductId, $currentPrice)
    {
        $currentPrice = (float)$currentPrice;

        if ($currentPrice <= 0) {
            return $this->getFallbackRecommendedProducts($currentProductId);
        }

        $minPrice = $currentPrice * 0.8;
        $maxPrice = $currentPrice * 1.2;

        $sql = "
            SELECT 
                sp.*,
                lsp.TenLoaiSanPham,
                th.TenThuongHieu
            FROM sanpham sp
            LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
            LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
            WHERE sp.SanPhamId <> :id
              AND sp.TrangThai = 1
              AND sp.SoLuongTon > 0
              AND sp.GiaBan BETWEEN :minPrice AND :maxPrice
              AND (lsp.IsActive = 1 OR lsp.LoaiSanPhamId IS NULL)
              AND (th.IsActive = 1 OR th.ThuongHieuId IS NULL)
            ORDER BY RAND()
            LIMIT 4
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => (int)$currentProductId,
            ':minPrice' => $minPrice,
            ':maxPrice' => $maxPrice
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($results)) {
            return $this->getFallbackRecommendedProducts($currentProductId);
        }

        return $results;
    }

    private function getFallbackRecommendedProducts($currentProductId)
    {
        $sql = "
            SELECT 
                sp.*,
                lsp.TenLoaiSanPham,
                th.TenThuongHieu
            FROM sanpham sp
            LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
            LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
            WHERE sp.SanPhamId <> :id
              AND sp.TrangThai = 1
              AND sp.SoLuongTon > 0
              AND (lsp.IsActive = 1 OR lsp.LoaiSanPhamId IS NULL)
              AND (th.IsActive = 1 OR th.ThuongHieuId IS NULL)
            ORDER BY RAND()
            LIMIT 4
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => (int)$currentProductId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function logUserBehavior($khachHangId, $sanPhamId, $loaiHanhVi, $sessionId, $trongSo = 1, $ghiChu = 'PRODUCT_DETAIL')
    {
        $sanPhamId = (int)$sanPhamId;

        if ($sanPhamId <= 0) {
            return false;
        }

        $allowedActions = ['VIEW', 'SEARCH', 'ADD_TO_CART', 'PURCHASE', 'REVIEW'];

        $loaiHanhVi = strtoupper(trim((string)$loaiHanhVi));

        if (!in_array($loaiHanhVi, $allowedActions, true)) {
            $loaiHanhVi = 'VIEW';
        }

        $khachHangId = $khachHangId ? (int)$khachHangId : null;

        if ($khachHangId !== null && !$this->customerExists($khachHangId)) {
            $khachHangId = null;
        }

        $sql = "
            INSERT INTO hanhvinguoidung (
                KhachHangId,
                SanPhamId,
                LoaiHanhVi,
                Nguon,
                SessionId,
                TrongSo,
                GhiChu,
                CreatedAt
            ) VALUES (
                :khId,
                :spId,
                :hanhVi,
                'WEB',
                :sessId,
                :trongSo,
                :ghiChu,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':khId' => $khachHangId,
            ':spId' => $sanPhamId,
            ':hanhVi' => $loaiHanhVi,
            ':sessId' => $sessionId,
            ':trongSo' => (float)$trongSo,
            ':ghiChu' => trim((string)$ghiChu)
        ]);

        return $stmt->rowCount() > 0;
    }

    public function resolveCustomerIdFromAccount($account)
    {
        if (!$account || !is_array($account)) {
            return null;
        }

        $email = trim($account['Email'] ?? '');
        $phone = trim($account['SoDienThoai'] ?? '');

        if ($email === '' && $phone === '') {
            return null;
        }

        if ($email !== '' && $phone !== '') {
            $sql = "
                SELECT KhachHangId
                FROM khachhang
                WHERE Email = :email OR SoDienThoai = :phone
                ORDER BY KhachHangId DESC
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                ':email' => $email,
                ':phone' => $phone
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? (int)$result['KhachHangId'] : null;
        }

        if ($email !== '') {
            $sql = "
                SELECT KhachHangId
                FROM khachhang
                WHERE Email = :email
                ORDER BY KhachHangId DESC
                LIMIT 1
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':email' => $email
            ]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result ? (int)$result['KhachHangId'] : null;
        }

        $sql = "
            SELECT KhachHangId
            FROM khachhang
            WHERE SoDienThoai = :phone
            ORDER BY KhachHangId DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':phone' => $phone
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (int)$result['KhachHangId'] : null;
    }

    private function customerExists($khachHangId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM khachhang
            WHERE KhachHangId = ?
        ");

        $stmt->execute([
            (int)$khachHangId
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function getActiveBrands()
    {
        $sql = "
            SELECT *
            FROM thuonghieu
            WHERE IsActive = 1
            ORDER BY TenThuongHieu ASC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveCategories()
    {
        $sql = "
            SELECT *
            FROM loaisanpham
            WHERE IsActive = 1
            ORDER BY TenLoaiSanPham ASC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}