<?php

class BlogModel {
    private $db;
    private const STATUS_PUBLISHED = 2;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getListBlog($page, $pageSize, $keyword = "") {
        $offset = ($page - 1) * $pageSize;

        $where = "WHERE TrangThai = :status";
        $params = [
            ':status' => self::STATUS_PUBLISHED
        ];

        if ($keyword !== "") {
            $where .= " AND TieuDe LIKE :keyword";
            $params[':keyword'] = "%" . $keyword . "%";
        }

        $sqlCount = "SELECT COUNT(*) FROM baiviet $where";
        $stmtCount = $this->db->prepare($sqlCount);

        foreach ($params as $key => $value) {
            $stmtCount->bindValue($key, $value);
        }

        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        $sql = "
            SELECT *
            FROM baiviet
            $where
            ORDER BY COALESCE(NgayDang, CreatedAt) DESC, BaiVietId DESC
            LIMIT :offset, :limit
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$pageSize, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total
        ];
    }

    public function getBlogById($id) {
        $sql = "
            SELECT *
            FROM baiviet
            WHERE BaiVietId = :id 
              AND TrangThai = :status
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id' => (int)$id,
            ':status' => self::STATUS_PUBLISHED
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLatestPosts($excludeId = null, $limit = 5) {
        $sql = "
            SELECT *
            FROM baiviet
            WHERE TrangThai = :status
        ";

        $params = [
            ':status' => self::STATUS_PUBLISHED
        ];

        if (!empty($excludeId)) {
            $sql .= " AND BaiVietId != :excludeId";
            $params[':excludeId'] = (int)$excludeId;
        }

        $sql .= "
            ORDER BY COALESCE(NgayDang, CreatedAt) DESC, BaiVietId DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}