<?php
class AdminSettingModel {
    private $db;
    public function __construct($pdo) { $this->db = $pdo; }

    // Lấy thông tin mới nhất để hiển thị lên Web
    public function getLatestSetting() {
        return $this->db->query("SELECT * FROM thongtincuahang ORDER BY UpdatedAt DESC LIMIT 1")->fetch();
    }

    // Lấy toàn bộ lịch sử thay đổi
    public function getHistory() {
        return $this->db->query("SELECT * FROM thongtincuahang ORDER BY UpdatedAt DESC")->fetchAll();
    }

    // Thêm bản ghi mới (Cập nhật thông tin)
    public function saveSetting($data) {
        $sql = "INSERT INTO thongtincuahang (TenCuaHang, Hotline, Email, DiaChi, MoTaNgan, GioiThieu, Logo, Banner, FacebookUrl, InstagramUrl, ZaloUrl, IsActive, UpdatedById, UpdatedAt) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['TenCuaHang'], $data['Hotline'], $data['Email'], $data['DiaChi'],
            $data['MoTaNgan'], $data['GioiThieu'], $data['Logo'], $data['Banner'],
            $data['FacebookUrl'], $data['InstagramUrl'], $data['ZaloUrl'],
            $data['IsActive'], $data['UpdatedById']
        ]);
    }

    public function deleteHistory($id) {
        return $this->db->prepare("DELETE FROM thongtincuahang WHERE ThongTinCuaHangId = ?")->execute([$id]);
    }
}