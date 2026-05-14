<?php

class AdminBlogModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getBlogs($status = "published", $keyword = "", $userId = null)
    {
        $params = [];

        $sql = "
            SELECT 
                bv.*, 
                tk.HoTen AS NguoiTao,
                tk.TenDangNhap AS TenDangNhapNguoiTao
            FROM baiviet bv
            LEFT JOIN taikhoan tk ON bv.CreatedById = tk.TaiKhoanId
            WHERE 1=1
        ";

        if ($status !== 'all') {
            $statusValue = $this->getStatusValue($status);
            $sql .= " AND bv.TrangThai = ?";
            $params[] = $statusValue;
        }

        if ($userId !== null) {
            $sql .= " AND bv.CreatedById = ?";
            $params[] = (int)$userId;
        }

        if (!empty($keyword)) {
            $sql .= " AND (
                        bv.MaBaiViet LIKE ? 
                        OR bv.TieuDe LIKE ? 
                        OR bv.TomTat LIKE ?
                    )";

            $search = "%" . trim($keyword) . "%";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY COALESCE(bv.UpdatedAt, bv.CreatedAt) DESC, bv.BaiVietId DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBlogById($id)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM baiviet
            WHERE BaiVietId = ?
            LIMIT 1
        ");

        $stmt->execute([(int)$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createBlog($data)
    {
        $maBaiViet = !empty($data['MaBaiViet'])
            ? trim($data['MaBaiViet'])
            : 'BV' . date('ymdHis') . mt_rand(100, 999);

        $trangThai = isset($data['TrangThai'])
            ? (int)$data['TrangThai']
            : 1;

        if (!in_array($trangThai, [0, 1, 2], true)) {
            $trangThai = 1;
        }

        $ngayDang = $trangThai === 1 ? date('Y-m-d H:i:s') : null;

        $sql = "
            INSERT INTO baiviet
            (
                MaBaiViet,
                TieuDe,
                TomTat,
                NoiDung,
                AnhDaiDien,
                CreatedById,
                TrangThai,
                NgayDang,
                CreatedAt
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $maBaiViet,
            $data['TieuDe'] ?? '',
            $data['TomTat'] ?? '',
            $data['NoiDung'] ?? '',
            $data['AnhDaiDien'] ?? null,
            (int)($data['CreatedById'] ?? 0),
            $trangThai,
            $ngayDang
        ]);

        return $stmt->rowCount() > 0;
    }

    public function updateBlog($id, $data)
    {
        $sql = "
            UPDATE baiviet
            SET MaBaiViet = ?,
                TieuDe = ?,
                TomTat = ?,
                NoiDung = ?,
                UpdatedAt = NOW()
        ";

        $params = [
            $data['MaBaiViet'] ?? '',
            $data['TieuDe'] ?? '',
            $data['TomTat'] ?? '',
            $data['NoiDung'] ?? ''
        ];

        if (array_key_exists('AnhDaiDien', $data)) {
            $sql .= ", AnhDaiDien = ?";
            $params[] = $data['AnhDaiDien'] ?? null;
        }

        if (array_key_exists('TrangThai', $data)) {
            $trangThai = (int)$data['TrangThai'];

            if (!in_array($trangThai, [0, 1, 2], true)) {
                $trangThai = 1;
            }

            $sql .= ", TrangThai = ?";
            $params[] = $trangThai;

            if ($trangThai === 1) {
                $sql .= ", NgayDang = COALESCE(NgayDang, NOW())";
            }
        }

        $sql .= " WHERE BaiVietId = ?";
        $params[] = (int)$id;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function updateStatus($id, $newStatus, $updatePublishDate = false)
    {
        $newStatus = (int)$newStatus;

        if (!in_array($newStatus, [0, 1, 2], true)) {
            return false;
        }

        $sql = "
            UPDATE baiviet
            SET TrangThai = ?,
                UpdatedAt = NOW()
        ";

        $params = [$newStatus];

        if ($updatePublishDate) {
            $sql .= ", NgayDang = COALESCE(NgayDang, NOW())";
        }

        $sql .= " WHERE BaiVietId = ?";
        $params[] = (int)$id;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function deleteBlog($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM baiviet
            WHERE BaiVietId = ?
        ");

        $stmt->execute([(int)$id]);

        return $stmt->rowCount() > 0;
    }

    public function delete($id)
    {
        return $this->deleteBlog($id);
    }

    public function isBlogCodeExists($code, $excludeId = 0)
    {
        $sql = "
            SELECT COUNT(*)
            FROM baiviet
            WHERE MaBaiViet = ?
              AND BaiVietId <> ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            trim($code),
            (int)$excludeId
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function getStatusValue($status)
    {
        switch (strtolower((string)$status)) {
            case 'draft':
                return 0;

            case 'hidden':
                return 2;

            case 'published':
            default:
                return 1;
        }
    }
}