<?php
class TaiKhoanModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getAccountByUsername($username) {
        $sql = "SELECT tk.*, vt.MaVaiTro 
                FROM taikhoan tk 
                LEFT JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId 
                WHERE tk.TenDangNhap = :username AND tk.IsActive = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateLastLogin($accountId) {
        $sql = "UPDATE taikhoan SET LastLoginAt = NOW(), UpdatedAt = NOW() WHERE TaiKhoanId = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $accountId]);
    }

    public function checkUsernameExists($username) {
        $stmt = $this->db->prepare("SELECT 1 FROM taikhoan WHERE TenDangNhap = :username");
        $stmt->execute(['username' => $username]);
        return (bool)$stmt->fetch();
    }

    public function checkEmailExists($email) {
        $stmt = $this->db->prepare("SELECT 1 FROM taikhoan WHERE Email = :email");
        $stmt->execute(['email' => $email]);
        return (bool)$stmt->fetch();
    }

    public function checkPhoneExists($phone) {
        $stmt = $this->db->prepare("SELECT 1 FROM taikhoan WHERE SoDienThoai = :phone");
        $stmt->execute(['phone' => $phone]);
        return (bool)$stmt->fetch();
    }

    public function getCustomerRoleId() {
        $sql = "SELECT VaiTroId FROM vaitro 
                WHERE IsActive = 1 AND (MaVaiTro = 'USER' OR MaVaiTro = 'CUSTOMER' OR MaVaiTro = 'KHACHHANG') 
                LIMIT 1";
        $stmt = $this->db->query($sql);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$role) throw new Exception("Hệ thống thiếu Role khách hàng (USER/CUSTOMER).");
        return $role['VaiTroId'];
    }

    public function checkCustomerCodeExists($code) {
        $stmt = $this->db->prepare("SELECT 1 FROM khachhang WHERE MaKhachHang = :code");
        $stmt->execute(['code' => $code]);
        return (bool)$stmt->fetch();
    }

    public function getCustomerByPhoneOrEmail($phone, $email) {
        $sql = "SELECT KhachHangId FROM khachhang WHERE SoDienThoai = :phone";
        $params = ['phone' => $phone];
        if (!empty($email)) {
            $sql .= " OR Email = :email";
            $params['email'] = $email;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createCustomer($data) {
        $sql = "INSERT INTO khachhang (MaKhachHang, HoTen, Email, SoDienThoai, GioiTinh, NgaySinh, DiaChi, GhiChu, IsActive, CreatedAt) 
                VALUES (:ma, :hoten, :email, :sdt, :gioitinh, :ngaysinh, :diachi, :ghichu, 1, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'ma' => $data['MaKhachHang'],
            'hoten' => $data['HoTen'],
            'email' => $data['Email'] ?: null,
            'sdt' => $data['SoDienThoai'],
            'gioitinh' => $data['GioiTinh'],
            'ngaysinh' => $data['NgaySinh'] ?: null,
            'diachi' => $data['DiaChi'] ?: '',
            'ghichu' => $data['GhiChu']
        ]);
        return $this->db->lastInsertId();
    }

    public function createAccount($data) {
        $sql = "INSERT INTO taikhoan (VaiTroId, TenDangNhap, MatKhauHash, HoTen, Email, SoDienThoai, GioiTinh, NgaySinh, DiaChi, IsActive, CreatedAt) 
                VALUES (:vaitro, :user, :pass, :hoten, :email, :sdt, :gioitinh, :ngaysinh, :diachi, 1, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'vaitro'   => $data['VaiTroId'],
            'user'     => $data['TenDangNhap'],
            'pass'     => $data['MatKhauHash'],
            'hoten'    => $data['HoTen'],
            'email'    => $data['Email'],
            'sdt'      => $data['SoDienThoai'],
            'gioitinh' => $data['GioiTinh'],
            'ngaysinh' => $data['NgaySinh'],
            'diachi'   => $data['DiaChi']
        ]);
        $id = $this->db->lastInsertId();
        $st = $this->db->prepare("SELECT tk.*, vt.MaVaiTro FROM taikhoan tk LEFT JOIN vaitro vt ON tk.VaiTroId = vt.VaiTroId WHERE TaiKhoanId = ?");
        $st->execute([$id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }
}