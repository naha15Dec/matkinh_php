<?php

class GioHangModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getProductById($id)
    {
        $sql = "
            SELECT 
                sp.*,
                th.TenThuongHieu,
                th.IsActive AS ThuongHieuActive,
                lsp.TenLoaiSanPham,
                lsp.IsActive AS LoaiSanPhamActive
            FROM sanpham sp
            LEFT JOIN thuonghieu th 
                ON sp.ThuongHieuId = th.ThuongHieuId
            LEFT JOIN loaisanpham lsp 
                ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
            WHERE sp.SanPhamId = :id
              AND sp.TrangThai = 1
              AND sp.SoLuongTon > 0
              AND (th.IsActive = 1 OR th.ThuongHieuId IS NULL)
              AND (lsp.IsActive = 1 OR lsp.LoaiSanPhamId IS NULL)
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => (int)$id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}