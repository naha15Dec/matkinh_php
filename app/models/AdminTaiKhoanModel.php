<?php
class AdminTaiKhoanModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getAccounts($keyword = "", $role = "")
    {
        $sql = "SELECT tk.*, vt.MaVaiTro, vt.TenVaiTro 
                FROM taikhoan tk 
                JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId 
                WHERE 1=1";

        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (
                        tk.TenDangNhap LIKE ? 
                        OR tk.HoTen LIKE ? 
                        OR tk.Email LIKE ? 
                        OR tk.SoDienThoai LIKE ?
                    )";

            $search = "%$keyword%";
            array_push($params, $search, $search, $search, $search);
        }

        if (!empty($role)) {
            $sql .= " AND vt.MaVaiTro = ?";
            $params[] = $role;
        }

        $sql .= " ORDER BY vt.MaVaiTro ASC, tk.TenDangNhap ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getAccountById($id)
    {
        $sql = "SELECT tk.*, vt.MaVaiTro, vt.TenVaiTro 
                FROM taikhoan tk 
                JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId 
                WHERE tk.TaiKhoanId = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);

        return $stmt->fetch();
    }

    public function toggleActive($id)
    {
        $sql = "UPDATE taikhoan 
                SET IsActive = IF(IsActive = 1, 0, 1), 
                    UpdatedAt = NOW() 
                WHERE TaiKhoanId = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);

        return $stmt->rowCount() > 0;
    }

    public function updateInfo($id, $data)
{
    $sql = "UPDATE taikhoan 
            SET HoTen = ?, 
                Email = ?, 
                SoDienThoai = ?, 
                DiaChi = ?, 
                UpdatedAt = NOW() 
            WHERE TaiKhoanId = ?";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
        $data['HoTen'] ?? null,
        $data['Email'] ?? null,
        $data['SoDienThoai'] ?? null,
        $data['DiaChi'] ?? null,
        (int)$id
    ]);

    return $stmt->rowCount() > 0;
}
    public function changePassword($id, $newHash)
    {
        $sql = "UPDATE taikhoan 
                SET MatKhauHash = ?, 
                    UpdatedAt = NOW() 
                WHERE TaiKhoanId = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$newHash, (int)$id]);

        return $stmt->rowCount() > 0;
    }

    public function updateRole($id, $roleId)
    {
        $sql = "UPDATE taikhoan 
                SET VaiTroId = ?, 
                    UpdatedAt = NOW() 
                WHERE TaiKhoanId = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$roleId, (int)$id]);

        return $stmt->rowCount() > 0;
    }

    public function getAllRoles()
    {
        $sql = "SELECT * 
                FROM vaitro 
                WHERE IsActive = 1 
                ORDER BY VaiTroId ASC";

        return $this->db->query($sql)->fetchAll();
    }

    public function getRoleById($roleId)
    {
        $sql = "SELECT * 
                FROM vaitro 
                WHERE VaiTroId = ? 
                  AND IsActive = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$roleId]);

        return $stmt->fetch();
    }

    public function countActiveAdmins()
    {
        $sql = "SELECT COUNT(*) 
                FROM taikhoan tk
                JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId
                WHERE vt.MaVaiTro = 'ADMIN'
                  AND tk.IsActive = 1
                  AND vt.IsActive = 1";

        return (int)$this->db->query($sql)->fetchColumn();
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
    $stmt->execute([$email, (int)$excludeId]);

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
    $stmt->execute([$phone, (int)$excludeId]);

    return (int)$stmt->fetchColumn() > 0;
}
}