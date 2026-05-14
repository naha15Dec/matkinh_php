<?php
class AdminProfileModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getAccountById($id)
    {
        $sql = "SELECT 
                    tk.*, 
                    vt.MaVaiTro,
                    vt.TenVaiTro
                FROM taikhoan tk 
                LEFT JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId 
                WHERE tk.TaiKhoanId = ?
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateInfo($id, $data)
    {
        $sql = "UPDATE taikhoan 
                SET 
                    HoTen = ?, 
                    Email = ?, 
                    SoDienThoai = ?, 
                    GioiTinh = ?, 
                    NgaySinh = ?, 
                    DiaChi = ?, 
                    UpdatedAt = NOW() 
                WHERE TaiKhoanId = ?";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            $data['HoTen'] ?? null,
            $data['Email'] ?? null,
            $data['SoDienThoai'] ?? null,
            $data['GioiTinh'],
            $data['NgaySinh'] ?? null,
            $data['DiaChi'] ?? null,
            (int)$id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function updatePassword($id, $hashedPassword)
    {
        $sql = "UPDATE taikhoan 
                SET 
                    MatKhauHash = ?, 
                    UpdatedAt = NOW() 
                WHERE TaiKhoanId = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $hashedPassword,
            (int)$id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function isEmailExists($email, $excludeId = 0)
    {
        if ($email === null || trim($email) === '') {
            return false;
        }

        $sql = "SELECT COUNT(*) 
                FROM taikhoan 
                WHERE Email = ? 
                  AND TaiKhoanId <> ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            trim($email),
            (int)$excludeId
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function isPhoneExists($phone, $excludeId = 0)
    {
        if ($phone === null || trim($phone) === '') {
            return false;
        }

        $sql = "SELECT COUNT(*) 
                FROM taikhoan 
                WHERE SoDienThoai = ? 
                  AND TaiKhoanId <> ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            trim($phone),
            (int)$excludeId
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }
}   