<?php
class AdminTypeModel
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getAllTypes()
    {
        $sql = "
            SELECT loai.*, 
                   (
                       SELECT COUNT(*) 
                       FROM sanpham sp 
                       WHERE sp.LoaiSanPhamId = loai.LoaiSanPhamId
                   ) AS SoSanPham 
            FROM loaisanpham loai
            ORDER BY loai.IsActive DESC, loai.CreatedAt DESC, loai.LoaiSanPhamId DESC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM loaisanpham
            WHERE LoaiSanPhamId = ?
            LIMIT 1
        ");

        $stmt->execute([(int)$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTypeWithProductCount($id)
    {
        $stmt = $this->db->prepare("
            SELECT loai.*,
                   (
                       SELECT COUNT(*)
                       FROM sanpham sp
                       WHERE sp.LoaiSanPhamId = loai.LoaiSanPhamId
                   ) AS SoSanPham
            FROM loaisanpham loai
            WHERE loai.LoaiSanPhamId = ?
            LIMIT 1
        ");

        $stmt->execute([(int)$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function checkDuplicateCode($code, $excludeId = 0)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM loaisanpham
            WHERE MaLoaiSanPham = ?
              AND LoaiSanPhamId <> ?
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
            FROM loaisanpham
            WHERE TenLoaiSanPham = ?
              AND LoaiSanPhamId <> ?
        ");

        $stmt->execute([
            trim($name),
            (int)$excludeId
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    public function save($data)
    {
        $id = (int)($data['LoaiSanPhamId'] ?? 0);

        $code = strtoupper(trim($data['MaLoaiSanPham'] ?? ''));
        $name = trim($data['TenLoaiSanPham'] ?? '');
        $description = trim($data['MoTa'] ?? '');
        $isActive = !empty($data['IsActive']) ? 1 : 0;

        if ($id > 0) {
            $sql = "
                UPDATE loaisanpham
                SET MaLoaiSanPham = ?,
                    TenLoaiSanPham = ?,
                    MoTa = ?,
                    IsActive = ?,
                    UpdatedAt = NOW()
                WHERE LoaiSanPhamId = ?
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
            INSERT INTO loaisanpham (
                MaLoaiSanPham,
                TenLoaiSanPham,
                MoTa,
                IsActive,
                CreatedAt
            ) VALUES (?, ?, ?, ?, NOW())
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

    public function updateStatus($id, $status)
    {
        $status = !empty($status) ? 1 : 0;

        $stmt = $this->db->prepare("
            UPDATE loaisanpham 
            SET IsActive = ?,
                UpdatedAt = NOW()
            WHERE LoaiSanPhamId = ?
        ");

        $stmt->execute([
            $status,
            (int)$id
        ]);

        return $stmt->rowCount() > 0;
    }

    public function hardDelete($id)
    {
        $stmt = $this->db->prepare("
            DELETE FROM loaisanpham
            WHERE LoaiSanPhamId = ?
        ");

        $stmt->execute([(int)$id]);

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
                $finalCode = 'LSP' . date('His') . mt_rand(10, 99);
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
            return 'LSP' . date('His');
        }

        $normalized = $this->removeVietnameseSign($name);
        $words = preg_split('/\s+/', $normalized);

        $generated = '';

        foreach ($words as $word) {
            $word = preg_replace('/[^A-Za-z0-9]/', '', $word);

            if ($word !== '') {
                $generated .= strtoupper(substr($word, 0, 1));
            }
        }

        if (mb_strlen($generated, 'UTF-8') < 2) {
            $generated = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $normalized), 0, 6));
        }

        if ($generated === '') {
            $generated = 'LSP';
        }

        return mb_substr($generated, 0, 20, 'UTF-8');
    }

    private function removeVietnameseSign($str)
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