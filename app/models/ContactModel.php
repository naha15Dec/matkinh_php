<?php
class ContactModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getStoreInfo() {
        // Lấy thông tin cửa hàng đang hoạt động, ưu tiên cái mới cập nhật nhất
        $sql = "SELECT * FROM thongtincuahang 
                WHERE IsActive = 1 
                ORDER BY UpdatedAt DESC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch();
    }
}