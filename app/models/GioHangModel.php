<?php

class GioHangModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getProductById($id) {
        $sql = "
            SELECT 
                sp.*,
                th.TenThuongHieu,
                lsp.TenLoaiSanPham
            FROM sanpham sp
            LEFT JOIN thuonghieu th 
                ON sp.ThuongHieuId = th.ThuongHieuId
            LEFT JOIN loaisanpham lsp 
                ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
            WHERE sp.SanPhamId = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => (int)$id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}