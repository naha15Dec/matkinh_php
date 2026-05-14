<?php
class AdminSanPhamModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getProducts($statusProduct = "stock", $keyword = "", $userId = null)
    {
        $sql = "SELECT sp.*, th.TenThuongHieu, lsp.TenLoaiSanPham 
                FROM sanpham sp 
                LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId 
                LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId 
                WHERE 1=1";

        $params = [];

        if ($userId !== null) {
            $sql .= " AND sp.CreatedById = ?";
            $params[] = (int)$userId;
        }

        if (!empty($keyword)) {
            $sql .= " AND (
                        sp.MaSanPham LIKE ? 
                        OR sp.TenSanPham LIKE ? 
                        OR th.TenThuongHieu LIKE ? 
                        OR lsp.TenLoaiSanPham LIKE ?
                    )";

            $search = "%$keyword%";
            array_push($params, $search, $search, $search, $search);
        }

        switch ($statusProduct) {
            case "outofstock":
                $sql .= " AND sp.TrangThai = 1 AND sp.SoLuongTon <= 0";
                break;

            case "inactive":
                $sql .= " AND sp.TrangThai = 2";
                break;

            case "all":
                break;

            case "stock":
            default:
                $sql .= " AND sp.TrangThai = 1 AND sp.SoLuongTon > 0";
                break;
        }

        $sql .= " ORDER BY COALESCE(sp.UpdatedAt, sp.CreatedAt) DESC, sp.SanPhamId DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function getProductById($id)
    {
        $sql = "SELECT sp.*, th.TenThuongHieu, lsp.TenLoaiSanPham
                FROM sanpham sp
                LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
                LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
                WHERE sp.SanPhamId = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);

        return $stmt->fetch();
    }

    public function createProduct($data)
    {
        $sql = "INSERT INTO sanpham (
                    MaSanPham, 
                    TenSanPham, 
                    MoTaNgan, 
                    MoTaChiTiet, 
                    GiaGoc, 
                    GiaBan, 
                    SoLuongTon, 
                    ThuongHieuId, 
                    LoaiSanPhamId, 
                    TrangThai, 
                    IsFeatured, 
                    HinhAnhChinh, 
                    CreatedById, 
                    CreatedAt
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $params = [
            $data['MaSanPham'],
            $data['TenSanPham'],
            $data['MoTaNgan'] ?? null,
            $data['MoTaChiTiet'] ?? null,
            $data['GiaGoc'],
            $data['GiaBan'],
            $data['SoLuongTon'],
            $data['ThuongHieuId'],
            $data['LoaiSanPhamId'],
            $data['TrangThai'] ?? 1,
            $data['IsFeatured'] ?? 0,
            $data['HinhAnhChinh'] ?? null,
            $data['CreatedById']
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function updateProduct($id, $data)
    {
        $sql = "UPDATE sanpham 
                SET MaSanPham = ?, 
                    TenSanPham = ?, 
                    MoTaNgan = ?, 
                    MoTaChiTiet = ?, 
                    GiaGoc = ?, 
                    GiaBan = ?, 
                    SoLuongTon = ?, 
                    ThuongHieuId = ?, 
                    LoaiSanPhamId = ?, 
                    TrangThai = ?, 
                    IsFeatured = ?, 
                    UpdatedAt = NOW()";

        $params = [
            $data['MaSanPham'],
            $data['TenSanPham'],
            $data['MoTaNgan'] ?? null,
            $data['MoTaChiTiet'] ?? null,
            $data['GiaGoc'],
            $data['GiaBan'],
            $data['SoLuongTon'],
            $data['ThuongHieuId'],
            $data['LoaiSanPhamId'],
            $data['TrangThai'],
            $data['IsFeatured']
        ];

        if (array_key_exists('HinhAnhChinh', $data)) {
            $sql .= ", HinhAnhChinh = ?";
            $params[] = $data['HinhAnhChinh'];
        }

        $sql .= " WHERE SanPhamId = ?";
        $params[] = (int)$id;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function toggleFeatured($id)
    {
        $sql = "UPDATE sanpham 
                SET IsFeatured = IF(IsFeatured = 1, 0, 1), 
                    UpdatedAt = NOW() 
                WHERE SanPhamId = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);

        return $stmt->rowCount() > 0;
    }

    public function softDelete($id)
    {
        $sql = "UPDATE sanpham 
                SET TrangThai = 2, 
                    SoLuongTon = 0, 
                    IsFeatured = 0,
                    UpdatedAt = NOW() 
                WHERE SanPhamId = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);

        return $stmt->rowCount() > 0;
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM sanpham WHERE SanPhamId = ?");
        $stmt->execute([(int)$id]);

        return $stmt->rowCount() > 0;
    }

    public function getAllBrands()
    {
        $sql = "SELECT ThuongHieuId, TenThuongHieu 
                FROM thuonghieu 
                WHERE IsActive = 1 
                ORDER BY TenThuongHieu ASC";

        return $this->db->query($sql)->fetchAll();
    }

    public function getAllCategories()
    {
        $sql = "SELECT LoaiSanPhamId, TenLoaiSanPham 
                FROM loaisanpham 
                WHERE IsActive = 1 
                ORDER BY TenLoaiSanPham ASC";

        return $this->db->query($sql)->fetchAll();
    }

    public function brandExists($brandId)
    {
        $sql = "SELECT COUNT(*) 
                FROM thuonghieu 
                WHERE ThuongHieuId = ? 
                  AND IsActive = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$brandId]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function categoryExists($categoryId)
    {
        $sql = "SELECT COUNT(*) 
                FROM loaisanpham 
                WHERE LoaiSanPhamId = ? 
                  AND IsActive = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$categoryId]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function isProductCodeExists($code, $excludeId = 0)
    {
        $sql = "SELECT COUNT(*) 
                FROM sanpham 
                WHERE MaSanPham = ? 
                  AND SanPhamId <> ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([trim($code), (int)$excludeId]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function checkProductInOrders($id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM chitietdonhang WHERE SanPhamId = ?");
        $stmt->execute([(int)$id]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function checkProductInBehaviors($id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM hanhvinguoidung WHERE SanPhamId = ?");
        $stmt->execute([(int)$id]);

        return (int)$stmt->fetchColumn() > 0;
    }
}