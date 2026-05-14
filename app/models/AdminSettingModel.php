<?php

class AdminSettingModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getLatestSetting()
    {
        $stmt = $this->db->query("
            SELECT t.*, tk.TenDangNhap, tk.HoTen
            FROM thongtincuahang t
            LEFT JOIN taikhoan tk ON tk.TaiKhoanId = t.UpdatedById
            ORDER BY t.IsActive DESC, t.UpdatedAt DESC, t.ThongTinCuaHangId DESC
            LIMIT 1
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSettingById($id)
    {
        $stmt = $this->db->prepare("
            SELECT t.*, tk.TenDangNhap, tk.HoTen
            FROM thongtincuahang t
            LEFT JOIN taikhoan tk ON tk.TaiKhoanId = t.UpdatedById
            WHERE t.ThongTinCuaHangId = ?
            LIMIT 1
        ");

        $stmt->execute([(int)$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getHistory()
    {
        $stmt = $this->db->query("
            SELECT t.*, tk.TenDangNhap, tk.HoTen
            FROM thongtincuahang t
            LEFT JOIN taikhoan tk ON tk.TaiKhoanId = t.UpdatedById
            ORDER BY t.IsActive DESC, t.UpdatedAt DESC, t.ThongTinCuaHangId DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveSetting($data)
    {
        try {
            $this->db->beginTransaction();

            $isActive = !empty($data['IsActive']) ? 1 : 0;

            if ($isActive === 1) {
                $this->db->exec("
                    UPDATE thongtincuahang 
                    SET IsActive = 0
                    WHERE IsActive = 1
                ");
            }

            $sql = "
                INSERT INTO thongtincuahang
                (
                    TenCuaHang,
                    Hotline,
                    Email,
                    DiaChi,
                    MoTaNgan,
                    GioiThieu,
                    Logo,
                    Banner,
                    FacebookUrl,
                    InstagramUrl,
                    ZaloUrl,
                    IsActive,
                    UpdatedById,
                    UpdatedAt
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->execute([
                $data['TenCuaHang'] ?? '',
                $data['Hotline'] ?? '',
                $data['Email'] ?? '',
                $data['DiaChi'] ?? '',
                $data['MoTaNgan'] ?? '',
                $data['GioiThieu'] ?? '',
                $data['Logo'] ?? '',
                $data['Banner'] ?? '',
                $data['FacebookUrl'] ?? '',
                $data['InstagramUrl'] ?? '',
                $data['ZaloUrl'] ?? '',
                $isActive,
                (int)($data['UpdatedById'] ?? 0)
            ]);

            $ok = $stmt->rowCount() > 0;

            if (!$ok) {
                $this->db->rollBack();
                return false;
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return false;
        }
    }

    public function deleteHistory($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM thongtincuahang
            WHERE ThongTinCuaHangId = ?
              AND IsActive = 0
        ");

        $stmt->execute([(int)$id]);

        return $stmt->rowCount() > 0;
    }
}