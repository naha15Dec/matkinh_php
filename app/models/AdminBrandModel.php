<?php

class AdminBrandModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getAllBrands()
    {
        $sql = "
            SELECT th.*,
                   (
                       SELECT COUNT(*)
                       FROM sanpham sp
                       WHERE sp.ThuongHieuId = th.ThuongHieuId
                   ) AS SoSanPham
            FROM thuonghieu th
            ORDER BY th.CreatedAt DESC, th.ThuongHieuId DESC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBrandById($id)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM thuonghieu
            WHERE ThuongHieuId = ?
        ");

        $stmt->execute([(int)$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBrandWithProductCount($id)
    {
        $stmt = $this->db->prepare("
            SELECT th.*,
                   (
                       SELECT COUNT(*)
                       FROM sanpham sp
                       WHERE sp.ThuongHieuId = th.ThuongHieuId
                   ) AS SoSanPham
            FROM thuonghieu th
            WHERE th.ThuongHieuId = ?
        ");

        $stmt->execute([(int)$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function checkDuplicateCode($code, $excludeId = 0)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM thuonghieu
            WHERE MaThuongHieu = ?
              AND ThuongHieuId != ?
        ");

        $stmt->execute([
            strtoupper(trim($code)),
            (int)$excludeId
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function save($data)
    {
        $id = (int)($data['ThuongHieuId'] ?? 0);

        $code = $this->generateCodeIfEmpty(
            $data['MaThuongHieu'] ?? '',
            $data['TenThuongHieu'] ?? ''
        );

        $name = trim($data['TenThuongHieu'] ?? '');
        $description = trim($data['MoTa'] ?? '');
        $isActive = (int)($data['IsActive'] ?? 1);

        if ($id > 0) {
            $sql = "
                UPDATE thuonghieu
                SET MaThuongHieu = ?,
                    TenThuongHieu = ?,
                    MoTa = ?,
                    IsActive = ?,
                    UpdatedAt = NOW()
                WHERE ThuongHieuId = ?
            ";

            $stmt = $this->db->prepare($sql);

            return $stmt->execute([
                $code,
                $name,
                $description,
                $isActive,
                $id
            ]);
        }

        $sql = "
            INSERT INTO thuonghieu
            (
                MaThuongHieu,
                TenThuongHieu,
                MoTa,
                IsActive,
                CreatedAt
            )
            VALUES (?, ?, ?, ?, NOW())
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $code,
            $name,
            $description,
            $isActive
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM thuonghieu
            WHERE ThuongHieuId = ?
        ");

        return $stmt->execute([(int)$id]);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("
            UPDATE thuonghieu
            SET IsActive = ?,
                UpdatedAt = NOW()
            WHERE ThuongHieuId = ?
        ");

        return $stmt->execute([
            (int)$status,
            (int)$id
        ]);
    }

    public function generateCodeIfEmpty($code, $name)
    {
        $code = strtoupper(trim($code));

        if ($code !== '') {
            return $code;
        }

        $name = trim($name);

        if ($name === '') {
            return 'BR' . date('His');
        }

        $normalized = $this->removeVietnameseAccents($name);
        $normalized = preg_replace('/[^A-Za-z0-9]/', '', $normalized);
        $generated = strtoupper(substr($normalized, 0, 3));

        if ($generated === '') {
            $generated = 'BR';
        }

        return $generated;
    }

    private function removeVietnameseAccents($str)
    {
        $unicode = [
            'a' => 'áàảãạăắằẳẵặâấầẩẫậ',
            'd' => 'đ',
            'e' => 'éèẻẽẹêếềểễệ',
            'i' => 'íìỉĩị',
            'o' => 'óòỏõọôốồổỗộơớờởỡợ',
            'u' => 'úùủũụưứừửữự',
            'y' => 'ýỳỷỹỵ',
            'A' => 'ÁÀẢÃẠĂẮẰẲẴẶÂẤẦẨẪẬ',
            'D' => 'Đ',
            'E' => 'ÉÈẺẼẸÊẾỀỂỄỆ',
            'I' => 'ÍÌỈĨỊ',
            'O' => 'ÓÒỎÕỌÔỐỒỔỖỘƠỚỜỞỠỢ',
            'U' => 'ÚÙỦŨỤƯỨỪỬỮỰ',
            'Y' => 'ÝỲỶỸỴ'
        ];

        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/[$uni]/u", $nonUnicode, $str);
        }

        return $str;
    }
}