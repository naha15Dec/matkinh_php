<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title">Quản lý tài khoản</h1>
        <p class="admin-page-subtitle">Theo dõi tài khoản người dùng, trạng thái và quyền truy cập.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item">
            <a href="index.php?controller=dashboard">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">Quản lý tài khoản</li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">
        <div class="card admin-card shadow-sm">
            <div class="card-header admin-card-header border-0">
                <div class="admin-card-title-wrap">
                    <h3 class="admin-card-title">Danh sách tài khoản</h3>
                    <span class="admin-card-count"><?= count($accounts) ?> tài khoản</span>
                </div>

                <div class="admin-card-tools">
                    <form action="index.php" method="GET" class="admin-search-form">
                        <input type="hidden" name="controller" value="admintaikhoan">
                        <input type="hidden" name="action" value="index">
                        <div class="admin-search-box">
                            <i class="fas fa-search"></i>
                            <input class="form-control" 
                                   type="search" 
                                   name="keyword" 
                                   value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" 
                                   placeholder="Nhập tên tài khoản..." />
                            <button class="btn admin-search-btn" type="submit">Tìm</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table admin-table align-middle">
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
                                    <tr>
                                        <td>
                                            <div class="admin-account-cell">
                                                <div class="admin-account-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <div>
                                                    <div class="admin-account-name"><?= htmlspecialchars($item['TenDangNhap']) ?></div>
                                                    <div class="admin-account-sub">
                                                        <?= !empty($item['Email']) ? htmlspecialchars($item['Email']) : "Chưa có email" ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            <span class="admin-role-badge">
                                                <?= htmlspecialchars($item['TenVaiTro']) ?>
                                            </span>
                                        </td>

                                        <td>
                                            <?php if ($item['IsActive']): ?>
                                                <span class="admin-status-badge success">Hoạt động</span>
                                            <?php else: ?>
                                                <span class="admin-status-badge out-stock">Đã khóa</span>
                                            <?php endif; ?>
                                        </td>

                                        <td class="text-center">
                                            <div class="admin-action-group">
                                                <a href="index.php?controller=admintaikhoan&action=detail&id=<?= $item['TaiKhoanId'] ?>" 
                                                   class="btn admin-btn admin-btn-detail">
                                                    <i class="fas fa-eye mr-1"></i> Chi tiết
                                                </a>

                                                <form action="index.php?controller=admintaikhoan&action=toggleActive" method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $item['TaiKhoanId'] ?>" />
                                                    <?php if ($item['IsActive']): ?>
                                                        <button type="submit" class="btn admin-btn admin-btn-cancel">
                                                            <i class="fas fa-ban mr-1"></i> Vô hiệu
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="submit" class="btn admin-btn admin-btn-save">
                                                            <i class="fas fa-unlock mr-1"></i> Mở khóa
                                                        </button>
                                                    <?php endif; ?>
                                                </form>

                                                <form action="index.php?controller=admintaikhoan&action=toggleActive" method="POST" class="d-inline">
                                                    <input type="hidden" name="id" value="<?= $item['TaiKhoanId'] ?>" />
                                                    <button type="submit" 
                                                            class="btn admin-btn admin-btn-delete" 
                                                            onclick="return confirm('Bạn có chắc muốn vô hiệu hóa tài khoản này?')">
                                                        <i class="fas fa-trash-alt mr-1"></i> Xóa
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4">Không có tài khoản nào.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>