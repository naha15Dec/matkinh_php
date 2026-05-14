<?php

class ErrorController
{
    private $pdo;

    public function __construct($pdo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->pdo = $pdo;
    }

    public function index()
    {
        $sessionAccount = $_SESSION['LoginInformation'] ?? null;
        $roleCode = "";

        if ($sessionAccount && !empty($sessionAccount['TaiKhoanId']) && $this->pdo) {
            try {
                $sql = "SELECT v.MaVaiTro 
                        FROM taikhoan t 
                        JOIN vaitro v ON t.VaiTroId = v.VaiTroId 
                        WHERE t.TaiKhoanId = :id 
                          AND t.IsActive = 1 
                          AND v.IsActive = 1
                        LIMIT 1";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'id' => (int)$sessionAccount['TaiKhoanId']
                ]);

                $account = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($account) {
                    $roleCode = strtoupper(trim($account['MaVaiTro'] ?? ''));
                }
            } catch (PDOException $e) {
                $roleCode = strtoupper(trim($sessionAccount['MaVaiTro'] ?? ''));
            }
        }

        $isAdminLike = in_array($roleCode, ['ADMIN', 'STAFF', 'SHIPPER'], true);

        include BASE_PATH . '/views/client/error.php';
    }
}