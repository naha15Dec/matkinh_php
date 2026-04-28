<?php

class TaiKhoanModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function getAccountByUsername($username) {
    $sql = "
        SELECT 
            tk.*,
            vt.MaVaiTro,
            vt.TenVaiTro
        FROM taikhoan tk
        LEFT JOIN vaitro vt 
            ON tk.VaiTroId = vt.VaiTroId
        WHERE 
            tk.IsActive = 1
            AND (
                tk.TenDangNhap = :login_username
                OR tk.Email = :login_email
                OR tk.SoDienThoai = :login_phone
            )
        LIMIT 1
    ";

    $login = trim($username);

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        'login_username' => $login,
        'login_email'    => $login,
        'login_phone'    => $login
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    public function updateLastLogin($accountId) {
        $sql = "
            UPDATE taikhoan 
            SET 
                LastLoginAt = NOW(),
                UpdatedAt = NOW()
            WHERE TaiKhoanId = :id
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => (int)$accountId
        ]);
    }

    public function checkUsernameExists($username) {
        $sql = "SELECT 1 FROM taikhoan WHERE TenDangNhap = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'username' => trim($username)
        ]);

        return (bool)$stmt->fetchColumn();
    }

    public function checkEmailExists($email) {
        if (empty($email)) {
            return false;
        }

        $sql = "SELECT 1 FROM taikhoan WHERE Email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'email' => trim($email)
        ]);

        return (bool)$stmt->fetchColumn();
    }

    public function checkPhoneExists($phone) {
        if (empty($phone)) {
            return false;
        }

        $sql = "SELECT 1 FROM taikhoan WHERE SoDienThoai = :phone LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'phone' => trim($phone)
        ]);

        return (bool)$stmt->fetchColumn();
    }

    public function getCustomerRoleId() {
        $sql = "
            SELECT VaiTroId 
            FROM vaitro
            WHERE 
                IsActive = 1
                AND MaVaiTro IN ('USER', 'CUSTOMER', 'KHACHHANG')
            LIMIT 1
        ";

        $stmt = $this->db->query($sql);
        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$role) {
            throw new Exception("Hệ thống thiếu vai trò khách hàng: USER/CUSTOMER/KHACHHANG.");
        }

        return (int)$role['VaiTroId'];
    }

    public function checkCustomerCodeExists($code) {
        $sql = "SELECT 1 FROM khachhang WHERE MaKhachHang = :code LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'code' => trim($code)
        ]);

        return (bool)$stmt->fetchColumn();
    }

    public function getCustomerByPhoneOrEmail($phone, $email = null) {
        $sql = "
            SELECT KhachHangId 
            FROM khachhang 
            WHERE SoDienThoai = :phone
        ";

        $params = [
            'phone' => trim($phone)
        ];

        if (!empty($email)) {
            $sql .= " OR Email = :email";
            $params['email'] = trim($email);
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createCustomer($data) {
        $sql = "
            INSERT INTO khachhang (
                MaKhachHang,
                HoTen,
                Email,
                SoDienThoai,
                GioiTinh,
                NgaySinh,
                DiaChi,
                GhiChu,
                IsActive,
                CreatedAt
            ) VALUES (
                :ma,
                :hoten,
                :email,
                :sdt,
                :gioitinh,
                :ngaysinh,
                :diachi,
                :ghichu,
                1,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'ma'       => $data['MaKhachHang'],
            'hoten'    => $data['HoTen'],
            'email'    => !empty($data['Email']) ? $data['Email'] : null,
            'sdt'      => $data['SoDienThoai'],
            'gioitinh' => $data['GioiTinh'] ?? null,
            'ngaysinh' => !empty($data['NgaySinh']) ? $data['NgaySinh'] : null,
            'diachi'   => $data['DiaChi'] ?? '',
            'ghichu'   => $data['GhiChu'] ?? ''
        ]);

        return $this->db->lastInsertId();
    }

    public function createAccount($data) {
        $sql = "
            INSERT INTO taikhoan (
                VaiTroId,
                TenDangNhap,
                MatKhauHash,
                HoTen,
                Email,
                SoDienThoai,
                GioiTinh,
                NgaySinh,
                DiaChi,
                IsActive,
                CreatedAt
            ) VALUES (
                :vaitro,
                :user,
                :pass,
                :hoten,
                :email,
                :sdt,
                :gioitinh,
                :ngaysinh,
                :diachi,
                1,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'vaitro'   => $data['VaiTroId'],
            'user'     => $data['TenDangNhap'],
            'pass'     => $data['MatKhauHash'],
            'hoten'    => $data['HoTen'],
            'email'    => !empty($data['Email']) ? $data['Email'] : null,
            'sdt'      => $data['SoDienThoai'],
            'gioitinh' => $data['GioiTinh'] ?? null,
            'ngaysinh' => !empty($data['NgaySinh']) ? $data['NgaySinh'] : null,
            'diachi'   => $data['DiaChi'] ?? ''
        ]);

        $id = $this->db->lastInsertId();

        $sqlGet = "
            SELECT 
                tk.*,
                vt.MaVaiTro,
                vt.TenVaiTro
            FROM taikhoan tk
            LEFT JOIN vaitro vt 
                ON tk.VaiTroId = vt.VaiTroId
            WHERE tk.TaiKhoanId = :id
            LIMIT 1
        ";

        $st = $this->db->prepare($sqlGet);
        $st->execute([
            'id' => $id
        ]);

        return $st->fetch(PDO::FETCH_ASSOC);
    }
}