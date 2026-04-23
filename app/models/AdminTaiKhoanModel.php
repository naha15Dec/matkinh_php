<?php
class AdminTaiKhoanModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getAccounts($keyword = "", $role = "") {
        $sql = "SELECT tk.*, vt.MaVaiTro, vt.TenVaiTro 
                FROM taikhoan tk 
                JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId WHERE 1=1";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " AND (tk.TenDangNhap LIKE ? OR tk.HoTen LIKE ? OR tk.Email LIKE ? OR tk.SoDienThoai LIKE ?)";
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

    public function getAccountById($id) {
        $stmt = $this->db->prepare("SELECT tk.*, vt.MaVaiTro, vt.TenVaiTro FROM taikhoan tk JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId WHERE tk.TaiKhoanId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function toggleActive($id) {
        $stmt = $this->db->prepare("UPDATE taikhoan SET IsActive = NOT IsActive, UpdatedAt = NOW() WHERE TaiKhoanId = ?");
        return $stmt->execute([$id]);
    }

    public function updateInfo($id, $data) {
        $sql = "UPDATE taikhoan SET HoTen = ?, Email = ?, SoDienThoai = ?, DiaChi = ?, UpdatedAt = NOW() WHERE TaiKhoanId = ?";
        return $this->db->prepare($sql)->execute([$data['HoTen'], $data['Email'], $data['SoDienThoai'], $data['DiaChi'], $id]);
    }

    public function changePassword($id, $newHash) {
        return $this->db->prepare("UPDATE taikhoan SET MatKhauHash = ?, UpdatedAt = NOW() WHERE TaiKhoanId = ?")->execute([$newHash, $id]);
    }

    public function updateRole($id, $roleId) {
        return $this->db->prepare("UPDATE taikhoan SET VaiTroId = ?, UpdatedAt = NOW() WHERE TaiKhoanId = ?")->execute([$roleId, $id]);
    }

    public function getAllRoles() {
        return $this->db->query("SELECT * FROM vaitro ORDER BY VaiTroId")->fetchAll();
    }
}