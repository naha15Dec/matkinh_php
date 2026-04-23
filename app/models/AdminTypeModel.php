<?php
class AdminTypeModel {
    private $db;
    public function __construct($pdo) { $this->db = $pdo; }

    public function getAllTypes() {
        $sql = "SELECT loai.*, 
                (SELECT COUNT(*) FROM sanpham sp WHERE sp.LoaiSanPhamId = loai.LoaiSanPhamId) as SoSanPham 
                FROM loaisanpham loai ORDER BY loai.CreatedAt DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM loaisanpham WHERE LoaiSanPhamId = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function save($data) {
    // 1. Logic Tự động Gen Mã theo chữ cái đầu nếu mã để trống
    if (empty($data['MaLoaiSanPham'])) {
        $name = $data['TenLoaiSanPham'];
        
        // Tách chuỗi thành mảng các từ
        $words = explode(' ', $name);
        $genCode = '';
        foreach ($words as $w) {
            // Lấy chữ cái đầu của mỗi từ và viết hoa
            if (!empty($w)) {
                $genCode .= mb_substr($w, 0, 1);
            }
        }
        
        // Chuyển sang viết hoa và loại bỏ dấu tiếng Việt (optional)
        $data['MaLoaiSanPham'] = strtoupper($this->removeVietnameseSign($genCode));
        
        // Nếu mã vẫn quá ngắn (ví dụ tên chỉ có 1 từ), hãy thêm số ngẫu nhiên
        if (strlen($data['MaLoaiSanPham']) < 2) {
            $data['MaLoaiSanPham'] .= rand(10, 99);
        }
    } else {
        $data['MaLoaiSanPham'] = strtoupper(trim($data['MaLoaiSanPham']));
    }

    // 2. Phần logic Insert/Update giữ nguyên như cũ
        if (isset($data['LoaiSanPhamId']) && $data['LoaiSanPhamId'] > 0) {
            $sql = "UPDATE loaisanpham SET MaLoaiSanPham = ?, TenLoaiSanPham = ?, MoTa = ?, IsActive = ?, UpdatedAt = NOW() WHERE LoaiSanPhamId = ?";
            return $this->db->prepare($sql)->execute([
                $data['MaLoaiSanPham'], $data['TenLoaiSanPham'], $data['MoTa'], $data['IsActive'], $data['LoaiSanPhamId']
            ]);
        } else {
            $sql = "INSERT INTO loaisanpham (MaLoaiSanPham, TenLoaiSanPham, MoTa, IsActive, CreatedAt) VALUES (?, ?, ?, ?, NOW())";
            return $this->db->prepare($sql)->execute([
                $data['MaLoaiSanPham'], $data['TenLoaiSanPham'], $data['MoTa'], $data['IsActive']
            ]);
        }
    }

    // Hàm bổ trợ loại bỏ dấu tiếng Việt để mã sạch hơn (ví dụ: Á -> A)
    private function removeVietnameseSign($str) {
        $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", "a", $str);
        $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", "e", $str);
        $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", "i", $str);
        $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", "o", $str);
        $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", "u", $str);
        $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", "y", $str);
        $str = preg_replace("/(đ)/", "d", $str);
        return $str;
    }

    public function updateStatus($id, $status) {
        return $this->db->prepare("UPDATE loaisanpham SET IsActive = ?, UpdatedAt = NOW() WHERE LoaiSanPhamId = ?")
                        ->execute([$status, $id]);
    }

    public function hardDelete($id) {
        return $this->db->prepare("DELETE FROM loaisanpham WHERE LoaiSanPhamId = ?")->execute([$id]);
    }
}