<?php
$currentInfo = $currentInfo ?? [];
$history = $history ?? [];
$baseUrl = $baseUrl ?? '';

$logo = $currentInfo['Logo'] ?? '';
$banner = $currentInfo['Banner'] ?? '';

$logoSrc = $logo ? $baseUrl . '/images/' . $logo : $baseUrl . '/images/default-logo.png';
$bannerSrc = $banner ? $baseUrl . '/images/' . $banner : $baseUrl . '/images/default-banner.jpg';
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-store mr-1"></i>
                Store Settings
            </span>

            <h1 class="admin-page-title mb-1">Cài đặt Website</h1>

            <p class="admin-page-subtitle mb-0">
                Quản lý thông tin cửa hàng, logo, banner và liên kết mạng xã hội.
            </p>
        </div>

        <ol class="breadcrumb admin-breadcrumb mt-3 mt-md-0">
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Cài đặt Website</li>
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

        <form action="<?= $baseUrl ?>/index.php?controller=adminsetting&action=save"
              method="POST"
              enctype="multipart/form-data">

            <div class="row">
                <div class="col-lg-8 mb-4">

                    <div class="premium-panel setting-panel mb-4">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Store Information</span>
                                <h5 class="mb-0">Thông tin cửa hàng</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <div class="form-group">
                                <label class="setting-label">Tên cửa hàng</label>
                                <input type="text"
                                       name="TenCuaHang"
                                       class="form-control setting-input"
                                       value="<?= htmlspecialchars($currentInfo['TenCuaHang'] ?? 'Karma Eyewear', ENT_QUOTES, 'UTF-8') ?>"
                                       required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="setting-label">Hotline</label>
                                        <input type="text"
                                               name="Hotline"
                                               class="form-control setting-input"
                                               value="<?= htmlspecialchars($currentInfo['Hotline'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="setting-label">Email</label>
                                        <input type="email"
                                               name="Email"
                                               class="form-control setting-input"
                                               value="<?= htmlspecialchars($currentInfo['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="setting-label">Địa chỉ</label>
                                <input type="text"
                                       name="DiaChi"
                                       class="form-control setting-input"
                                       value="<?= htmlspecialchars($currentInfo['DiaChi'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>

                            <div class="form-group">
                                <label class="setting-label">Mô tả ngắn</label>
                                <textarea name="MoTaNgan"
                                          class="form-control setting-input setting-textarea"
                                          rows="3"><?= htmlspecialchars($currentInfo['MoTaNgan'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>

                            <div class="form-group mb-0">
                                <label class="setting-label">Giới thiệu</label>
                                <textarea name="GioiThieu"
                                          class="form-control setting-input setting-textarea"
                                          rows="6"><?= htmlspecialchars($currentInfo['GioiThieu'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="premium-panel setting-panel">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">History</span>
                                <h5 class="mb-0">Lịch sử cập nhật</h5>
                            </div>

                            <span class="admin-card-count"><?= count($history) ?> bản ghi</span>
                        </div>

                        <div class="premium-panel-body p-0">
                            <div class="table-responsive">
                                <table class="table setting-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cửa hàng</th>
                                            <th>Người cập nhật</th>
                                            <th>Thời gian</th>
                                            <th class="text-center">Thao tác</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php if (!empty($history)): ?>
                                            <?php foreach ($history as $item): ?>
                                                <?php $itemId = (int)($item['ThongTinCuaHangId'] ?? 0); ?>

                                                <tr>
                                                    <td>
                                                        <span class="setting-code">#<?= $itemId ?></span>
                                                    </td>

                                                    <td>
                                                        <div class="setting-history-name">
                                                            <?= htmlspecialchars($item['TenCuaHang'] ?? 'Cửa hàng', ENT_QUOTES, 'UTF-8') ?>
                                                        </div>
                                                        <div class="setting-history-meta">
                                                            <?= !empty($item['IsActive']) ? 'Đang kích hoạt' : 'Không kích hoạt' ?>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <span class="setting-user-badge">
                                                            <i class="fas fa-user-shield mr-1"></i>
                                                            <?= htmlspecialchars($item['HoTen'] ?? $item['TenDangNhap'] ?? 'Admin', ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <div class="setting-date">
                                                            <?= !empty($item['UpdatedAt']) ? date('d/m/Y H:i', strtotime($item['UpdatedAt'])) : 'Chưa có' ?>
                                                        </div>
                                                    </td>

                                                    <td class="text-center">
                                                        <div class="setting-action-group">
                                                            <button type="button"
                                                                    class="btn setting-btn setting-btn-view"
                                                                    data-toggle="modal"
                                                                    data-target="#settingModal<?= $itemId ?>">
                                                                <i class="fas fa-eye mr-1"></i>
                                                                Xem
                                                            </button>

                                                            <form action="<?= $baseUrl ?>/index.php?controller=adminsetting&action=deleteHistory"
                                                                  method="POST"
                                                                  class="d-inline"
                                                                  onsubmit="return confirm('Xóa bản ghi lịch sử này?')">
                                                                <input type="hidden" name="id" value="<?= $itemId ?>">

                                                                <button type="submit" class="btn setting-btn setting-btn-delete">
                                                                    <i class="fas fa-trash-alt mr-1"></i>
                                                                    Xóa
                                                                </button>
                                                            </form>
                                                        </div>

                                                        <div class="modal fade setting-modal" id="settingModal<?= $itemId ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header setting-modal-header">
                                                                        <div>
                                                                            <span class="admin-kicker">History Detail</span>
                                                                            <h5 class="modal-title mb-0">
                                                                                Bản ghi #<?= $itemId ?>
                                                                            </h5>
                                                                        </div>
                                                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                                    </div>

                                                                    <div class="modal-body setting-modal-body">
                                                                        <div class="setting-modal-grid">
                                                                            <div>
                                                                                <span>Cửa hàng</span>
                                                                                <strong><?= htmlspecialchars($item['TenCuaHang'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                                                                            </div>
                                                                            <div>
                                                                                <span>Hotline</span>
                                                                                <strong><?= htmlspecialchars($item['Hotline'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                                                                            </div>
                                                                            <div>
                                                                                <span>Email</span>
                                                                                <strong><?= htmlspecialchars($item['Email'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                                                                            </div>
                                                                            <div>
                                                                                <span>Địa chỉ</span>
                                                                                <strong><?= htmlspecialchars($item['DiaChi'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                                                                            </div>
                                                                        </div>

                                                                        <div class="setting-modal-note mt-3">
                                                                            <strong>Mô tả:</strong>
                                                                            <p><?= nl2br(htmlspecialchars($item['MoTaNgan'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                                                                        </div>

                                                                        <div class="setting-modal-note">
                                                                            <strong>Giới thiệu:</strong>
                                                                            <p><?= nl2br(htmlspecialchars($item['GioiThieu'] ?? '', ENT_QUOTES, 'UTF-8')) ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5">
                                                    <div class="setting-empty-state">
                                                        <div class="setting-empty-icon">
                                                            <i class="fas fa-history"></i>
                                                        </div>
                                                        <h6>Chưa có lịch sử</h6>
                                                        <p>Hệ thống chưa có bản ghi cấu hình nào.</p>
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

                <div class="col-lg-4 mb-4">

                    <div class="premium-panel setting-panel mb-4">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Brand Assets</span>
                                <h5 class="mb-0">Logo & Banner</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <label class="setting-label">Logo hiện tại</label>
                            <div id="logoPreview" class="setting-logo-preview mb-3">
                                <img src="<?= htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="Logo"
                                     onerror="this.src='<?= $baseUrl ?>/images/default-logo.png'">
                            </div>

                            <div class="custom-file mb-4">
                                <input type="file"
                                       name="LogoFile"
                                       class="custom-file-input setting-file-input"
                                       id="LogoFile"
                                       accept=".jpg,.jpeg,.png,.webp"
                                       data-preview="logoPreview">
                                <label class="custom-file-label" for="LogoFile">Chọn logo...</label>
                            </div>

                            <label class="setting-label">Banner hiện tại</label>
                            <div id="bannerPreview" class="setting-banner-preview mb-3">
                                <img src="<?= htmlspecialchars($bannerSrc, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="Banner"
                                     onerror="this.src='<?= $baseUrl ?>/images/default-banner.jpg'">
                            </div>

                            <div class="custom-file">
                                <input type="file"
                                       name="BannerFile"
                                       class="custom-file-input setting-file-input"
                                       id="BannerFile"
                                       accept=".jpg,.jpeg,.png,.webp"
                                       data-preview="bannerPreview">
                                <label class="custom-file-label" for="BannerFile">Chọn banner...</label>
                            </div>
                        </div>
                    </div>

                    <div class="premium-panel setting-panel mb-4">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Social Links</span>
                                <h5 class="mb-0">Liên kết mạng xã hội</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <div class="form-group">
                                <label class="setting-label">Facebook</label>
                                <input type="text"
                                       name="FacebookUrl"
                                       class="form-control setting-input"
                                       value="<?= htmlspecialchars($currentInfo['FacebookUrl'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>

                            <div class="form-group">
                                <label class="setting-label">Instagram</label>
                                <input type="text"
                                       name="InstagramUrl"
                                       class="form-control setting-input"
                                       value="<?= htmlspecialchars($currentInfo['InstagramUrl'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>

                            <div class="form-group mb-0">
                                <label class="setting-label">Zalo</label>
                                <input type="text"
                                       name="ZaloUrl"
                                       class="form-control setting-input"
                                       value="<?= htmlspecialchars($currentInfo['ZaloUrl'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="premium-panel setting-panel">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Publish</span>
                                <h5 class="mb-0">Lưu cấu hình</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <div class="custom-control custom-switch setting-switch mb-3">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="IsActive"
                                       name="IsActive"
                                    <?= !isset($currentInfo['IsActive']) || !empty($currentInfo['IsActive']) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="IsActive">
                                    Kích hoạt cấu hình này
                                </label>
                            </div>

                            <button type="submit" class="btn setting-submit-btn btn-block">
                                <i class="fas fa-save mr-1"></i>
                                Lưu cấu hình website
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </form>

    </div>
</section>

<script>
document.querySelectorAll('.setting-file-input').forEach(function (input) {
    input.addEventListener('change', function () {
        const file = this.files[0];
        const previewId = this.dataset.preview;
        const label = this.nextElementSibling;

        if (!file || !previewId) {
            return;
        }

        const reader = new FileReader();

        reader.onload = function (e) {
            document.getElementById(previewId).innerHTML =
                '<img src="' + e.target.result + '" alt="preview">';
        };

        reader.readAsDataURL(file);

        if (label) {
            label.innerText = file.name;
        }
    });
});
</script>