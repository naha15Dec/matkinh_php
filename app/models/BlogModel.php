<?php

class BlogModel
{
    private $db;

    // 0 = Nháp, 1 = Đã đăng, 2 = Ẩn
    private const STATUS_PUBLISHED = 1;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getListBlog($page, $pageSize, $keyword = "")
    {
        $page = max(1, (int)$page);
        $pageSize = max(1, (int)$pageSize);
        $offset = ($page - 1) * $pageSize;

        $where = "WHERE bv.TrangThai = :status";

        $params = [
            ':status' => self::STATUS_PUBLISHED
        ];

        if ($keyword !== "") {
            $where .= " AND (
                bv.TieuDe LIKE :keyword
                OR bv.TomTat LIKE :keyword
            )";

            $params[':keyword'] = "%" . $keyword . "%";
        }

        $sqlCount = "
            SELECT COUNT(*)
            FROM baiviet bv
            $where
        ";

        $stmtCount = $this->db->prepare($sqlCount);

        foreach ($params as $key => $value) {
            $stmtCount->bindValue($key, $value);
        }

        $stmtCount->execute();
        $total = (int)$stmtCount->fetchColumn();

        $sql = "
            SELECT 
                bv.*,
                tk.HoTen AS NguoiTao,
                tk.TenDangNhap AS TenDangNhapNguoiTao
            FROM baiviet bv
            LEFT JOIN taikhoan tk ON bv.CreatedById = tk.TaiKhoanId
            $where
            ORDER BY COALESCE(bv.NgayDang, bv.CreatedAt) DESC, bv.BaiVietId DESC
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

    public function getBlogById($id)
    {
        $sql = "
            SELECT 
                bv.*,
                tk.HoTen AS NguoiTao,
                tk.TenDangNhap AS TenDangNhapNguoiTao
            FROM baiviet bv
            LEFT JOIN taikhoan tk ON bv.CreatedById = tk.TaiKhoanId
            WHERE bv.BaiVietId = :id 
              AND bv.TrangThai = :status
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => (int)$id,
            ':status' => self::STATUS_PUBLISHED
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLatestPosts($excludeId = null, $limit = 5)
    {
        $sql = "
            SELECT 
                bv.*,
                tk.HoTen AS NguoiTao
            FROM baiviet bv
            LEFT JOIN taikhoan tk ON bv.CreatedById = tk.TaiKhoanId
            WHERE bv.TrangThai = :status
        ";

        $params = [
            ':status' => self::STATUS_PUBLISHED
        ];

        if (!empty($excludeId)) {
            $sql .= " AND bv.BaiVietId <> :excludeId";
            $params[':excludeId'] = (int)$excludeId;
        }

        $sql .= "
            ORDER BY COALESCE(bv.NgayDang, bv.CreatedAt) DESC, bv.BaiVietId DESC
            LIMIT :limit
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->bindValue(':limit', max(1, (int)$limit), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}