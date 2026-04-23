<?php
class AdminBlogModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // Lấy danh sách bài viết (Có phân quyền)
    public function getBlogs($status = "published", $keyword = "", $userId = null) {
        $statusValue = $this->getStatusValue($status);
        $params = [$statusValue];

        // JOIN với bảng tài khoản để lấy tên người tạo (Sửa từ TaiKhoanId thành CreatedById)
        $sql = "SELECT bv.*, tk.HoTen as NguoiTao 
                FROM baiviet bv 
                LEFT JOIN taikhoan tk ON bv.CreatedById = tk.TaiKhoanId 
                WHERE bv.TrangThai = ?";

        // Nếu có userId truyền vào (Nhân viên), chỉ lấy bài của họ
        if ($userId !== null) {
            $sql .= " AND bv.CreatedById = ?";
            $params[] = $userId;
        }

        if (!empty($keyword)) {
            $sql .= " AND (bv.MaBaiViet LIKE ? OR bv.TieuDe LIKE ?)";
            $search = "%$keyword%";
            array_push($params, $search, $search);
        }

        $sql .= " ORDER BY COALESCE(bv.UpdatedAt, bv.CreatedAt) DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Lấy chi tiết 1 bài viết
    public function getBlogById($id) {
        $stmt = $this->db->prepare("SELECT * FROM baiviet WHERE BaiVietId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // THÊM MỚI BÀI VIẾT
    public function createBlog($data) {
        $sql = "INSERT INTO baiviet (MaBaiViet, TieuDe, TomTat, NoiDung, AnhDaiDien, CreatedById, TrangThai, CreatedAt) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        return $this->db->prepare($sql)->execute([
            $data['MaBaiViet'], 
            $data['TieuDe'], 
            $data['TomTat'], 
            $data['NoiDung'], 
            $data['AnhDaiDien'], 
            $data['CreatedById'], 
            $data['TrangThai']
        ]);
    }

    // CẬP NHẬT NỘI DUNG BÀI VIẾT
    public function updateBlog($id, $data) {
        $sql = "UPDATE baiviet SET TieuDe = ?, TomTat = ?, NoiDung = ?, UpdatedAt = NOW()";
        $params = [$data['TieuDe'], $data['TomTat'], $data['NoiDung']];

        // Chỉ cập nhật ảnh nếu có ảnh mới
        if (!empty($data['AnhDaiDien'])) {
            $sql .= ", AnhDaiDien = ?";
            $params[] = $data['AnhDaiDien'];
        }

        $sql .= " WHERE BaiVietId = ?";
        $params[] = $id;

        return $this->db->prepare($sql)->execute($params);
    }

    // CẬP NHẬT TRẠNG THÁI (Duyệt bài/Ẩn bài)
    public function updateStatus($id, $newStatus, $updatePublishDate = false) {
        $sql = "UPDATE baiviet SET TrangThai = ?, UpdatedAt = NOW()";
        $params = [$newStatus];

        if ($updatePublishDate) {
            $sql .= ", NgayDang = NOW()";
        }

        $sql .= " WHERE BaiVietId = ?";
        $params[] = $id;

        return $this->db->prepare($sql)->execute($params);
    }

    // XÓA BÀI VIẾT (Xóa vĩnh viễn theo code C#)
    public function delete($id) {
        $sql = "DELETE FROM baiviet WHERE BaiVietId = ?";
        return $this->db->prepare($sql)->execute([$id]);
    }

    // Chuyển đổi text status sang số int trong DB
    private function getStatusValue($status) {
        switch (strtolower($status)) {
            case 'draft': return 0;   // Nháp
            case 'hidden': return 2;  // Ẩn
            default: return 1;        // Đăng
        }
    }
}