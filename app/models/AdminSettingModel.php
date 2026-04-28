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
            ORDER BY t.UpdatedAt DESC
            LIMIT 1
        ");

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getHistory()
    {
        $stmt = $this->db->query("
            SELECT t.*, tk.TenDangNhap, tk.HoTen
            FROM thongtincuahang t
            LEFT JOIN taikhoan tk ON tk.TaiKhoanId = t.UpdatedById
            ORDER BY t.UpdatedAt DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveSetting($data)
    {
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

        return $stmt->execute([
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
            (int)($data['IsActive'] ?? 1),
            (int)($data['UpdatedById'] ?? 0)
        ]);
    }

    public function deleteHistory($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM thongtincuahang
            WHERE ThongTinCuaHangId = ?
        ");

        return $stmt->execute([(int)$id]);
    }
}