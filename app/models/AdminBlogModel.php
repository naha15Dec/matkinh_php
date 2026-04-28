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
        $statusValue = $this->getStatusValue($status);
        $params = [$statusValue];

        $sql = "
            SELECT bv.*, tk.HoTen AS NguoiTao
            FROM baiviet bv
            LEFT JOIN taikhoan tk ON bv.CreatedById = tk.TaiKhoanId
            WHERE bv.TrangThai = ?
        ";

        if ($userId !== null) {
            $sql .= " AND bv.CreatedById = ?";
            $params[] = (int)$userId;
        }

        if (!empty($keyword)) {
            $sql .= " AND (bv.MaBaiViet LIKE ? OR bv.TieuDe LIKE ?)";
            $search = "%" . $keyword . "%";
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY COALESCE(bv.UpdatedAt, bv.CreatedAt) DESC";

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
        ");

        $stmt->execute([(int)$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createBlog($data)
    {
        $maBaiViet = !empty($data['MaBaiViet'])
            ? $data['MaBaiViet']
            : 'BV' . date('ymdHis');

        $trangThai = isset($data['TrangThai'])
            ? (int)$data['TrangThai']
            : 1;

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

        return $stmt->execute([
            $maBaiViet,
            $data['TieuDe'] ?? '',
            $data['TomTat'] ?? '',
            $data['NoiDung'] ?? '',
            $data['AnhDaiDien'] ?? '',
            (int)($data['CreatedById'] ?? 0),
            $trangThai,
            $ngayDang
        ]);
    }

    public function updateBlog($id, $data)
    {
        $sql = "
            UPDATE baiviet
            SET TieuDe = ?,
                TomTat = ?,
                NoiDung = ?,
                UpdatedAt = NOW()
        ";

        $params = [
            $data['TieuDe'] ?? '',
            $data['TomTat'] ?? '',
            $data['NoiDung'] ?? ''
        ];

        if (array_key_exists('AnhDaiDien', $data)) {
            $sql .= ", AnhDaiDien = ?";
            $params[] = $data['AnhDaiDien'] ?? '';
        }

        if (isset($data['TrangThai'])) {
            $sql .= ", TrangThai = ?";
            $params[] = (int)$data['TrangThai'];

            if ((int)$data['TrangThai'] === 1) {
                $sql .= ", NgayDang = COALESCE(NgayDang, NOW())";
            }
        }

        $sql .= " WHERE BaiVietId = ?";
        $params[] = (int)$id;

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    public function updateStatus($id, $newStatus, $updatePublishDate = false)
    {
        $sql = "
            UPDATE baiviet
            SET TrangThai = ?,
                UpdatedAt = NOW()
        ";

        $params = [(int)$newStatus];

        if ($updatePublishDate) {
            $sql .= ", NgayDang = NOW()";
        }

        $sql .= " WHERE BaiVietId = ?";
        $params[] = (int)$id;

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    public function deleteBlog($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM baiviet
            WHERE BaiVietId = ?
        ");

        return $stmt->execute([(int)$id]);
    }

    // Giữ lại alias này nếu code cũ còn gọi delete()
    public function delete($id)
    {
        return $this->deleteBlog($id);
    }

    private function getStatusValue($status)
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