<?php
class ThanhToanModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    // Tìm hoặc tạo khách hàng mới
    public function getOrCreateCustomer($data) {
        $sql = "SELECT KhachHangId FROM khachhang WHERE SoDienThoai = :phone OR Email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['phone' => $data['SoDienThoai'], 'email' => $data['Email']]);
        $customer = $stmt->fetch();

        if ($customer) {
            $updateSql = "UPDATE khachhang SET HoTen = :name, DiaChi = :address, UpdatedAt = NOW() WHERE KhachHangId = :id";
            $this->db->prepare($updateSql)->execute([
                'name' => $data['HoTen'],
                'address' => $data['DiaChi'],
                'id' => $customer['KhachHangId']
            ]);
            return $customer['KhachHangId'];
        } else {
            $code = "KH" . date("YmdHis") . rand(100, 999);
            $insertSql = "INSERT INTO khachhang (MaKhachHang, HoTen, Email, SoDienThoai, DiaChi, CreatedAt, IsActive) 
                          VALUES (:code, :name, :email, :phone, :address, NOW(), 1)";
            $this->db->prepare($insertSql)->execute([
                'code' => $code,
                'name' => $data['HoTen'],
                'email' => $data['Email'],
                'phone' => $data['SoDienThoai'],
                'address' => $data['DiaChi']
            ]);
            return $this->db->lastInsertId();
        }
    }

    // Tạo đơn hàng chính
    public function createOrder($orderData) {
        $sql = "INSERT INTO donhang (MaDonHang, KhachHangId, HoTenNguoiNhan, SoDienThoaiNguoiNhan, DiaChiNhanHang, 
                TongTienHang, PhiVanChuyen, GiamGia, TongThanhToan, TrangThai, PhuongThucThanhToan, TrangThaiThanhToan, CreatedById, NgayDat, CreatedAt) 
                VALUES (:code, :khId, :name, :phone, :address, :totalHang, :ship, :discount, :totalPay, 1, :method, 'PENDING', :userId, NOW(), NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($orderData);
        return $this->db->lastInsertId();
    }

    // Lưu chi tiết đơn và trừ kho
    public function createOrderDetailAndReduceStock($detail) {
        $sqlDetail = "INSERT INTO chitietdonhang (DonHangId, SanPhamId, TenSanPhamSnapshot, DonGiaSnapshot, SoLuong, GiamGiaSnapshot, ThanhTien) 
                      VALUES (:dhId, :spId, :name, :price, :qty, :discount, :subtotal)";
        $this->db->prepare($sqlDetail)->execute($detail);

        $sqlStock = "UPDATE sanpham SET SoLuongTon = SoLuongTon - :qty, UpdatedAt = NOW() WHERE SanPhamId = :spId";
        $this->db->prepare($sqlStock)->execute(['qty' => $detail['qty'], 'spId' => $detail['spId']]);
    }

    public function updatePaymentStatus($orderCode, $status, $transactionNo = null) {
        $sql = "UPDATE donhang SET TrangThaiThanhToan = :status, MaGiaoDichThanhToan = :transNo, NgayThanhToan = NOW(), UpdatedAt = NOW() 
                WHERE MaDonHang = :code";
        $this->db->prepare($sql)->execute(['status' => $status, 'transNo' => $transactionNo, 'code' => $orderCode]);
    }

    public function getOrderByCode($code) {
        $sql = "SELECT * FROM donhang WHERE MaDonHang = :code LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}