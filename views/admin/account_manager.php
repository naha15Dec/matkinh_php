<?php
$accounts = $accounts ?? [];
$roles = $roles ?? [];
$keyword = $keyword ?? ($_GET['keyword'] ?? '');
$role = $role ?? ($_GET['role'] ?? '');
$baseUrl = $baseUrl ?? '';
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-users-cog mr-1"></i>
                Account Management
            </span>

            <h1 class="admin-page-title mb-1">Quản lý tài khoản</h1>

            <p class="admin-page-subtitle mb-0">
                Theo dõi tài khoản người dùng, trạng thái hoạt động và quyền truy cập hệ thống.
            </p>
        </div>

        <ol class="breadcrumb admin-breadcrumb mt-3 mt-md-0">
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Tài khoản</li>
        </ol>
    </div>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success admin-alert">
                <i class="fas fa-check-circle mr-1"></i>
                <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger admin-alert">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="premium-panel">
            <div class="premium-panel-header account-panel-header">
                <div>
                    <span class="admin-kicker">Karma Eyewear</span>
                    <h5 class="mb-0">Danh sách tài khoản</h5>
                </div>

                <span class="admin-card-count">
                    <?= count($accounts) ?> tài khoản
                </span>
            </div>

            <div class="account-filter-bar">
                <form action="<?= $baseUrl ?>/index.php" method="GET" class="account-filter-form">
                    <input type="hidden" name="controller" value="admintaikhoan">
                    <input type="hidden" name="action" value="index">

                    <div class="account-search-box">
                        <i class="fas fa-search"></i>
                        <input
                            type="search"
                            name="keyword"
                            value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
                            placeholder="Tìm theo tên đăng nhập, họ tên hoặc email..."
                        >
                    </div>

                    <select name="role" class="account-role-select">
                        <option value="">Tất cả vai trò</option>

                        <?php foreach ($roles as $roleItem): ?>
                            <?php
                            $roleValue = $roleItem['MaVaiTro'] ?? $roleItem['VaiTroId'] ?? '';
                            $roleText = $roleItem['TenVaiTro'] ?? $roleItem['MaVaiTro'] ?? 'Vai trò';
                            ?>
                            <option value="<?= htmlspecialchars($roleValue, ENT_QUOTES, 'UTF-8') ?>"
                                <?= (string)$role === (string)$roleValue ? 'selected' : '' ?>>
                                <?= htmlspecialchars($roleText, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button type="submit" class="btn account-filter-btn">
                        <i class="fas fa-filter mr-1"></i>
                        Lọc
                    </button>

                    <?php if (!empty($keyword) || !empty($role)): ?>
                        <a href="<?= $baseUrl ?>/index.php?controller=admintaikhoan" class="btn account-reset-btn">
                            Xóa lọc
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="premium-panel-body p-0">
                <div class="table-responsive">
                    <table class="table account-table mb-0">
                        <thead>
                            <tr>
                                <th>Tài khoản</th>
                                <th>Vai trò</th>
                                <th>Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (!empty($accounts)): ?>
                                <?php foreach ($accounts as $item): ?>
                                    <?php
                                    $id = (int)($item['TaiKhoanId'] ?? 0);
                                    $username = $item['TenDangNhap'] ?? 'unknown';
                                    $fullName = $item['HoTen'] ?? '';
                                    $email = $item['Email'] ?? '';
                                    $roleName = $item['TenVaiTro'] ?? $item['MaVaiTro'] ?? 'Chưa phân quyền';
                                    $isActive = !empty($item['IsActive']);
                                    ?>

                                    <tr>
                                        <td>
                                            <div class="account-user-cell">
                                                <div class="account-avatar">
                                                    <?= strtoupper(mb_substr($username, 0, 1, 'UTF-8')) ?>
                                                </div>

                                                <div>
                                                    <div class="account-name">
                                                        <?= htmlspecialchars($fullName ?: $username, ENT_QUOTES, 'UTF-8') ?>
                                                    </div>

                                                    <div class="account-meta">
                                                        <span>@<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></span>

                                                        <?php if (!empty($email)): ?>
                                                            <span class="mx-1">•</span>
                                                            <span><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
                                                        <?php else: ?>
                                                            <span class="mx-1">•</span>
                                                            <span>Chưa có email</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="account-role-badge">
                                                <i class="fas fa-user-shield mr-1"></i>
                                                <?= htmlspecialchars($roleName, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php if ($isActive): ?>
                                                <span class="account-status active">
                                                    <i class="fas fa-circle"></i>
                                                    Hoạt động
                                                </span>
                                            <?php else: ?>
                                                <span class="account-status locked">
                                                    <i class="fas fa-circle"></i>
                                                    Đã khóa
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <div class="account-action-group">
                                                <a
                                                    href="<?= $baseUrl ?>/index.php?controller=admintaikhoan&action=detail&id=<?= $id ?>"
                                                    class="btn account-btn account-btn-detail"
                                                >
                                                    <i class="fas fa-eye mr-1"></i>
                                                    Chi tiết
                                                </a>

                                                <form
                                                    action="<?= $baseUrl ?>/index.php?controller=admintaikhoan&action=toggleActive"
                                                    method="POST"
                                                    class="d-inline"
                                                >
                                                    <input type="hidden" name="id" value="<?= $id ?>">

                                                    <?php if ($isActive): ?>
                                                        <button
                                                            type="submit"
                                                            class="btn account-btn account-btn-lock"
                                                            onclick="return confirm('Bạn có chắc muốn khóa tài khoản này?')"
                                                        >
                                                            <i class="fas fa-ban mr-1"></i>
                                                            Khóa
                                                        </button>
                                                    <?php else: ?>
                                                        <button
                                                            type="submit"
                                                            class="btn account-btn account-btn-unlock"
                                                            onclick="return confirm('Bạn có chắc muốn mở khóa tài khoản này?')"
                                                        >
                                                            <i class="fas fa-unlock mr-1"></i>
                                                            Mở khóa
                                                        </button>
                                                    <?php endif; ?>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4">
                                        <div class="account-empty-state">
                                            <div class="account-empty-icon">
                                                <i class="fas fa-user-slash"></i>
                                            </div>
                                            <h6>Không tìm thấy tài khoản</h6>
                                            <p>Thử thay đổi từ khóa tìm kiếm hoặc bộ lọc vai trò.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</section>