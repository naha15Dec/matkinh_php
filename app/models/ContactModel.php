<?php

class ContactModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getStoreInfo() {
        $sql = "
            SELECT *
            FROM thongtincuahang
            WHERE IsActive = 1
            ORDER BY UpdatedAt DESC
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: [];
    }
}