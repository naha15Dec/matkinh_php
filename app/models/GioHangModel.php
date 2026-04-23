<?php
class GioHangModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getProductById($id) {
        $sql = "SELECT * FROM sanpham WHERE SanPhamId = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}