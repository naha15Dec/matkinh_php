<?php
class AdminSanPhamModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getProducts($statusProduct = "stock", $keyword = "", $userId = null, $page = 1, $pageSize = 10)
    {
        $page = max(1, (int)$page);
        $pageSize = max(1, (int)$pageSize);
        $offset = ($page - 1) * $pageSize;

        $where = "WHERE 1=1";
        $params = [];

        if ($userId !== null) {
            $where .= " AND sp.CreatedById = ?";
            $params[] = (int)$userId;
        }

        if (trim($keyword) !== '') {
            $where .= " AND (
                sp.MaSanPham LIKE ?
                OR sp.TenSanPham LIKE ?
                OR th.TenThuongHieu LIKE ?
                OR lsp.TenLoaiSanPham LIKE ?
            )";

            $search = "%" . trim($keyword) . "%";
            array_push($params, $search, $search, $search, $search);
        }

        switch ($statusProduct) {
            case "outofstock":
                $where .= " AND sp.TrangThai = 1 AND sp.SoLuongTon <= 0";
                break;

            case "inactive":
                $where .= " AND sp.TrangThai = 2";
                break;

            case "all":
                break;

            case "stock":
            default:
                $where .= " AND sp.TrangThai = 1 AND sp.SoLuongTon > 0";
                break;
        }

        $countSql = "
            SELECT COUNT(*)
            FROM sanpham sp
            LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
            LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
            {$where}
        ";

        $stmtCount = $this->db->prepare($countSql);
        $stmtCount->execute($params);
        $totalCount = (int)$stmtCount->fetchColumn();

        $sql = "
            SELECT 
                sp.*, 
                th.TenThuongHieu, 
                lsp.TenLoaiSanPham,
                tk.HoTen AS NguoiTao,
                tk.TenDangNhap AS TenDangNhapNguoiTao
            FROM sanpham sp
            LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
            LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
            LEFT JOIN taikhoan tk ON sp.CreatedById = tk.TaiKhoanId
            {$where}
            ORDER BY COALESCE(sp.UpdatedAt, sp.CreatedAt) DESC, sp.SanPhamId DESC
            LIMIT ?, ?
        ";

        $stmt = $this->db->prepare($sql);

        $executeParams = $params;
        $executeParams[] = (int)$offset;
        $executeParams[] = (int)$pageSize;

        $stmt->execute($executeParams);

        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'totalCount' => $totalCount
        ];
    }

    public function getProductById($id)
    {
        $sql = "
            SELECT 
                sp.*, 
                th.TenThuongHieu, 
                lsp.TenLoaiSanPham,
                tk.HoTen AS NguoiTao,
                tk.TenDangNhap AS TenDangNhapNguoiTao
            FROM sanpham sp
            LEFT JOIN thuonghieu th ON sp.ThuongHieuId = th.ThuongHieuId
            LEFT JOIN loaisanpham lsp ON sp.LoaiSanPhamId = lsp.LoaiSanPhamId
            LEFT JOIN taikhoan tk ON sp.CreatedById = tk.TaiKhoanId
            WHERE sp.SanPhamId = ?
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createProduct($data)
    {
        $sql = "
            INSERT INTO sanpham (
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
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
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
        ]);

        return $stmt->rowCount() > 0;
    }

    public function updateProduct($id, $data)
    {
        $sql = "
            UPDATE sanpham
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
                UpdatedAt = NOW()
        ";

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
        $sql = "
            UPDATE sanpham
            SET IsFeatured = IF(IsFeatured = 1, 0, 1),
                UpdatedAt = NOW()
            WHERE SanPhamId = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id]);

        return $stmt->rowCount() > 0;
    }

    public function softDelete($id)
    {
        $sql = "
            UPDATE sanpham
            SET TrangThai = 2,
                SoLuongTon = 0,
                IsFeatured = 0,
                UpdatedAt = NOW()
            WHERE SanPhamId = ?
        ";

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
        return $this->db->query("
            SELECT ThuongHieuId, TenThuongHieu
            FROM thuonghieu
            WHERE IsActive = 1
            ORDER BY TenThuongHieu ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllCategories()
    {
        return $this->db->query("
            SELECT LoaiSanPhamId, TenLoaiSanPham
            FROM loaisanpham
            WHERE IsActive = 1
            ORDER BY TenLoaiSanPham ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function brandExists($brandId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM thuonghieu
            WHERE ThuongHieuId = ?
              AND IsActive = 1
        ");

        $stmt->execute([(int)$brandId]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function categoryExists($categoryId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM loaisanpham
            WHERE LoaiSanPhamId = ?
              AND IsActive = 1
        ");

        $stmt->execute([(int)$categoryId]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function isProductCodeExists($code, $excludeId = 0)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM sanpham
            WHERE MaSanPham = ?
              AND SanPhamId <> ?
        ");

        $stmt->execute([
            trim($code),
            (int)$excludeId
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function checkProductInOrders($id)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM chitietdonhang
            WHERE SanPhamId = ?
        ");

        $stmt->execute([(int)$id]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function checkProductInBehaviors($id)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM hanhvinguoidung
            WHERE SanPhamId = ?
        ");

        $stmt->execute([(int)$id]);

        return (int)$stmt->fetchColumn() > 0;
    }
}