<?php
class ProfileModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // Lấy thông tin tài khoản mới nhất từ DB
    public function getAccountById($id) {
        $sql = "SELECT * FROM taikhoan WHERE TaiKhoanId = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); // Ép kiểu mảng kết hợp
    }

    // Cập nhật thông tin cá nhân
    public function updateProfile($id, $data) {
        $sql = "UPDATE taikhoan SET 
                HoTen = :name, 
                SoDienThoai = :phone, 
                DiaChi = :address, 
                GioiTinh = :gender, 
                UpdatedAt = NOW() 
                WHERE TaiKhoanId = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'gender' => $data['gender'],
            'id' => $id
        ]);
    }

    // Đổi mật khẩu
    public function updatePassword($id, $newHash) {
        $sql = "UPDATE taikhoan SET MatKhauHash = :hash, UpdatedAt = NOW() WHERE TaiKhoanId = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['hash' => $newHash, 'id' => $id]);
    }

    // Lấy danh sách đơn hàng (Phân trang)
    public function getOrdersByUser($userId, $page, $pageSize) {
        $offset = ($page - 1) * $pageSize;
        
        $sqlCount = "SELECT COUNT(*) FROM donhang WHERE CreatedById = :userId";
        $stmtCount = $this->db->prepare($sqlCount);
        $stmtCount->execute(['userId' => $userId]);
        $totalCount = $stmtCount->fetchColumn();

        $sql = "SELECT * FROM donhang 
                WHERE CreatedById = :userId 
                ORDER BY CreatedAt DESC, DonHangId DESC 
                LIMIT :offset, :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$pageSize, PDO::PARAM_INT);
        $stmt->execute();
        
        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'totalCount' => $totalCount
        ];
    }

    // Chi tiết 1 đơn hàng (Gộp luôn items)
    public function getOrderDetail($maDonHang, $userId) {
        // Dùng LEFT JOIN để nếu lỡ mất thông tin khách hàng đơn vẫn hiện được
        $sql = "SELECT dh.*, kh.MaKhachHang 
                FROM donhang dh
                LEFT JOIN khachhang kh ON dh.KhachHangId = kh.KhachHangId
                WHERE dh.MaDonHang = :code AND dh.CreatedById = :userId LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['code' => $maDonHang, 'userId' => $userId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($order) {
            $order['items'] = $this->getOrderItems($order['DonHangId']);
        }
        return $order;
    }

    // Hàm lấy danh sách sản phẩm của đơn hàng (Tách riêng để Controller dễ dùng)
    public function getOrderItems($donHangId) {
        $sql = "SELECT * FROM chitietdonhang WHERE DonHangId = :id ORDER BY ChiTietDonHangId ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $donHangId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}