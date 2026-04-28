<?php

class HomeModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getDiscountProducts($limit = 8) {
        $sql = "
            SELECT 
                sp.*,
                lsp.TenLoaiSanPham,
                th.TenThuongHieu
            FROM sanpham sp
            LEFT JOIN loaisanpham lsp 
                ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
            LEFT JOIN thuonghieu th 
                ON sp.ThuongHieuId = th.ThuongHieuId
            WHERE 
                sp.TrangThai = 1
                AND sp.GiaGoc IS NOT NULL
                AND sp.GiaBan IS NOT NULL
                AND sp.GiaGoc > sp.GiaBan
            ORDER BY 
                (sp.GiaGoc - sp.GiaBan) DESC,
                sp.CreatedAt DESC,
                sp.SanPhamId DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNewProducts($limit = 8) {
        $sql = "
            SELECT 
                sp.*,
                lsp.TenLoaiSanPham,
                th.TenThuongHieu
            FROM sanpham sp
            LEFT JOIN loaisanpham lsp 
                ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
            LEFT JOIN thuonghieu th 
                ON sp.ThuongHieuId = th.ThuongHieuId
            WHERE sp.TrangThai = 1
            ORDER BY 
                sp.CreatedAt DESC,
                sp.SanPhamId DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDealHotProducts($limit = 4) {
        return $this->getDiscountProducts($limit);
    }

    public function getLatestBlogs($limit = 3) {
        $sql = "
            SELECT *
            FROM baiviet
            WHERE TrangThai = 2
            ORDER BY 
                COALESCE(NgayDang, CreatedAt) DESC,
                BaiVietId DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStoreInfo() {
        $sql = "
            SELECT *
            FROM thongtincuahang
            WHERE IsActive = 1
            ORDER BY UpdatedAt DESC
            LIMIT 1
        ";

        $stmt = $this->db->query($sql);
        $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

        return $result ?: [];
    }

    public function findProductByCodeOrId($keyword) {
        $sql = "
            SELECT SanPhamId
            FROM sanpham
            WHERE 
                TrangThai = 1
                AND (
                    MaSanPham = :keyword
                    OR SanPhamId = :id
                )
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $id = is_numeric($keyword) ? (int)$keyword : 0;

        $stmt->bindValue(':keyword', $keyword, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}