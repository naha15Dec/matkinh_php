<?php
class AdminBrandModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getAllBrands() {
        // Lấy thương hiệu và đếm số sản phẩm thuộc về nó để kiểm tra trước khi xóa
        $sql = "SELECT th.*, 
                (SELECT COUNT(*) FROM sanpham sp WHERE sp.ThuongHieuId = th.ThuongHieuId) as SoSanPham 
                FROM thuonghieu th 
                ORDER BY th.CreatedAt DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getBrandById($id) {
        $stmt = $this->db->prepare("SELECT * FROM thuonghieu WHERE ThuongHieuId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function checkDuplicateCode($code, $excludeId = 0) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM thuonghieu WHERE MaThuongHieu = ? AND ThuongHieuId != ?");
        $stmt->execute([$code, $excludeId]);
        return $stmt->fetchColumn() > 0;
    }

    public function save($data) {
    // Logic Tự động Gen Mã Thương Hiệu nếu trống
    if (empty($data['MaThuongHieu'])) {
        // Lấy 3 ký tự đầu của tên, chuyển sang tiếng Việt không dấu (nếu cần) và viết hoa
        $name = $data['TenThuongHieu'];
        $genCode = strtoupper(substr($name, 0, 3)); 
        $data['MaThuongHieu'] = $genCode;
    } else {
        $data['MaThuongHieu'] = strtoupper($data['MaThuongHieu']);
    }

    if (isset($data['ThuongHieuId']) && $data['ThuongHieuId'] > 0) {
        // Update
        $sql = "UPDATE thuonghieu SET MaThuongHieu = ?, TenThuongHieu = ?, MoTa = ?, IsActive = ?, UpdatedAt = NOW() WHERE ThuongHieuId = ?";
        return $this->db->prepare($sql)->execute([
            $data['MaThuongHieu'], $data['TenThuongHieu'], $data['MoTa'], $data['IsActive'], $data['ThuongHieuId']
        ]);
    } else {
        // Insert
        $sql = "INSERT INTO thuonghieu (MaThuongHieu, TenThuongHieu, MoTa, IsActive, CreatedAt) VALUES (?, ?, ?, ?, NOW())";
        return $this->db->prepare($sql)->execute([
            $data['MaThuongHieu'], $data['TenThuongHieu'], $data['MoTa'], $data['IsActive']
        ]);
    }
}

    public function delete($id) {
        return $this->db->prepare("DELETE FROM thuonghieu WHERE ThuongHieuId = ?")->execute([$id]);
    }

    public function updateStatus($id, $status) {
        return $this->db->prepare("UPDATE thuonghieu SET IsActive = ?, UpdatedAt = NOW() WHERE ThuongHieuId = ?")
                        ->execute([$status, $id]);
    }
}