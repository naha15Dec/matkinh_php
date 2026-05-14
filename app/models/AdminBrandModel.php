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
            ORDER BY th.IsActive DESC, th.CreatedAt DESC, th.ThuongHieuId DESC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBrandById($id)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM thuonghieu
            WHERE ThuongHieuId = ?
            LIMIT 1
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
            LIMIT 1
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
              AND ThuongHieuId <> ?
        ");

        $stmt->execute([
            strtoupper(trim($code)),
            (int)$excludeId
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function checkDuplicateName($name, $excludeId = 0)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM thuonghieu
            WHERE TenThuongHieu = ?
              AND ThuongHieuId <> ?
        ");

        $stmt->execute([
            trim($name),
            (int)$excludeId
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function save($data)
    {
        $id = (int)($data['ThuongHieuId'] ?? 0);

        $code = strtoupper(trim($data['MaThuongHieu'] ?? ''));
        $name = trim($data['TenThuongHieu'] ?? '');
        $description = trim($data['MoTa'] ?? '');
        $isActive = !empty($data['IsActive']) ? 1 : 0;

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
            $stmt->execute([
                $code,
                $name,
                $description,
                $isActive,
                $id
            ]);

            return $stmt->rowCount() > 0;
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
        $stmt->execute([
            $code,
            $name,
            $description,
            $isActive
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM thuonghieu
            WHERE ThuongHieuId = ?
        ");

        $stmt->execute([(int)$id]);

        return $stmt->rowCount() > 0;
    }

    public function updateStatus($id, $status)
    {
        $status = !empty($status) ? 1 : 0;

        $stmt = $this->db->prepare("
            UPDATE thuonghieu
            SET IsActive = ?,
                UpdatedAt = NOW()
            WHERE ThuongHieuId = ?
        ");

        $stmt->execute([
            $status,
            (int)$id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function generateUniqueCode($code, $name, $excludeId = 0)
    {
        $code = strtoupper(trim($code));

        if ($code !== '') {
            return mb_substr($code, 0, 20, 'UTF-8');
        }

        $baseCode = $this->generateCodeIfEmpty('', $name);
        $baseCode = mb_substr($baseCode, 0, 12, 'UTF-8');

        $finalCode = $baseCode;
        $counter = 1;

        while ($this->checkDuplicateCode($finalCode, $excludeId)) {
            $suffix = str_pad((string)$counter, 2, '0', STR_PAD_LEFT);
            $finalCode = mb_substr($baseCode, 0, 20 - mb_strlen($suffix, 'UTF-8'), 'UTF-8') . $suffix;
            $counter++;

            if ($counter > 99) {
                $finalCode = 'BR' . date('His') . mt_rand(10, 99);
                break;
            }
        }

        return strtoupper($finalCode);
    }

    public function generateCodeIfEmpty($code, $name)
    {
        $code = strtoupper(trim($code));

        if ($code !== '') {
            return mb_substr($code, 0, 20, 'UTF-8');
        }

        $name = trim($name);

        if ($name === '') {
            return 'BR' . date('His');
        }

        $normalized = $this->removeVietnameseAccents($name);
        $normalized = preg_replace('/[^A-Za-z0-9]/', '', $normalized);
        $generated = strtoupper(substr($normalized, 0, 6));

        if ($generated === '') {
            $generated = 'BR';
        }

        return mb_substr($generated, 0, 20, 'UTF-8');
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