<?php
class AdminProfileModel {
    private $db;
    public function __construct($pdo) { $this->db = $pdo; }

    public function getAccountById($id) {
        $stmt = $this->db->prepare("SELECT tk.*, vt.TenVaiTro 
                                    FROM taikhoan tk 
                                    LEFT JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId 
                                    WHERE tk.TaiKhoanId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateInfo($id, $data) {
    $sql = "UPDATE taikhoan SET HoTen = ?, Email = ?, SoDienThoai = ?, 
            GioiTinh = ?, NgaySinh = ?, DiaChi = ?, UpdatedAt = NOW() 
            WHERE TaiKhoanId = ?";
    
    return $this->db->prepare($sql)->execute([
        $data['HoTen'], 
        $data['Email'], 
        $data['SoDienThoai'],
        // Logic ép kiểu giới tính (nếu View gửi "true"/"false" hoặc "Nam"/"Nữ")
        ($data['GioiTinh'] == 'true' || $data['GioiTinh'] == 'Nam' ? 1 : 0),
        $data['NgaySinh'], 
        $data['DiaChi'], 
        $id
    ]);
}

    public function updatePassword($id, $hashedPassword) {
        return $this->db->prepare("UPDATE taikhoan SET MatKhauHash = ?, UpdatedAt = NOW() WHERE TaiKhoanId = ?")
                        ->execute([$hashedPassword, $id]);
    }
}