<?php
class BlogModel {
    private $db;
    private const STATUS_PUBLISHED = 2; // Khớp với BlogStatusPublished của bạn

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // Lấy danh sách bài viết có phân trang
    public function getListBlog($page, $pageSize, $keyword = "") {
        $offset = ($page - 1) * $pageSize;
        $params = ['status' => self::STATUS_PUBLISHED];
        
        $where = "WHERE TrangThai = :status";
        if (!empty($keyword)) {
            $where .= " AND TieuDe LIKE :keyword";
            $params['keyword'] = "%$keyword%";
        }

        // Đếm tổng số bài viết
        $sqlCount = "SELECT COUNT(*) FROM baiviet $where";
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute($params);
        $total = $stmtCount->fetchColumn();

        // Lấy dữ liệu bài viết
        $sql = "SELECT * FROM baiviet $where 
                ORDER BY COALESCE(NgayDang, CreatedAt) DESC 
                LIMIT :offset, :limit";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$pageSize, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data' => $stmt->fetchAll(),
            'total' => $total
        ];
    }

    // Lấy chi tiết một bài viết
    public function getBlogById($id) {
        $sql = "SELECT * FROM baiviet WHERE BaiVietId = :id AND TrangThai = :status LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id, 'status' => self::STATUS_PUBLISHED]);
        return $stmt->fetch();
    }

    // Lấy bài viết mới nhất (Sidebar)
    public function getLatestPosts($excludeId = null, $limit = 5) {
        $sql = "SELECT * FROM baiviet WHERE TrangThai = :status";
        $params = ['status' => self::STATUS_PUBLISHED];

        if ($excludeId) {
            $sql .= " AND BaiVietId != :excludeId";
            $params['excludeId'] = $excludeId;
        }

        $sql .= " ORDER BY COALESCE(NgayDang, CreatedAt) DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}