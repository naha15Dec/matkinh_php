<?php
$product = $product ?? null;
$brands = $brands ?? [];
$categories = $categories ?? [];
$baseUrl = $baseUrl ?? '';

$isEdit = !empty($product);
$pageTitle = $title ?? ($isEdit ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới');

$autoSKU = "SP" . date("ymdHis");
$currentSKU = !empty($product['MaSanPham']) ? $product['MaSanPham'] : $autoSKU;

$image = $product['HinhAnhChinh'] ?? '';
$imageSrc = $image ? $baseUrl . '/images/' . $image : $baseUrl . '/images/default.jpg';
?>

<div class="admin-page-header mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <span class="admin-kicker">
                <i class="fas fa-glasses mr-1"></i>
                Product Editor
            </span>

            <h1 class="admin-page-title mb-1">
                <?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?>
            </h1>

            <p class="admin-page-subtitle mb-0">
                <?= $isEdit ? 'Cập nhật thông tin sản phẩm kính mắt.' : 'Tạo sản phẩm mới cho hệ thống Karma Eyewear.' ?>
            </p>
        </div>

        <ol class="breadcrumb admin-breadcrumb mt-3 mt-md-0">
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=dashboard">Dashboard</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= $baseUrl ?>/index.php?controller=adminsanpham">Sản phẩm</a>
            </li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></li>
        </ol>
    </div>
</div>

<section class="content">
    <div class="container-fluid p-0">

        <form action="<?= $baseUrl ?>/index.php?controller=adminsanpham&action=save"
              method="POST"
              enctype="multipart/form-data"
              id="productForm">

            <input type="hidden" name="SanPhamId" value="<?= (int)($product['SanPhamId'] ?? 0) ?>">

            <div class="row">

                <div class="col-lg-8 mb-4">
                    <div class="premium-panel product-form-panel mb-4">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Basic Info</span>
                                <h5 class="mb-0">Thông tin sản phẩm</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <div class="form-group">
                                <label class="product-form-label">Tên sản phẩm</label>
                                <input type="text"
                                       name="TenSanPham"
                                       class="form-control product-input"
                                       placeholder="VD: Ray-Ban Aviator Classic..."
                                       value="<?= htmlspecialchars($product['TenSanPham'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                       required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="product-form-label">Mã sản phẩm</label>
                                        <input type="text"
                                               name="MaSanPham"
                                               class="form-control product-input readonly"
                                               value="<?= htmlspecialchars($currentSKU, ENT_QUOTES, 'UTF-8') ?>"
                                               readonly>
                                        <small class="product-help">Mã tự động tạo để quản lý kho chính xác.</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="product-form-label">Số lượng tồn kho</label>
                                        <input type="number"
                                               name="SoLuongTon"
                                               class="form-control product-input text-right"
                                               value="<?= (int)($product['SoLuongTon'] ?? 0) ?>"
                                               min="0"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="product-form-label">Mô tả tóm tắt</label>
                                <textarea name="MoTaNgan"
                                          class="form-control product-input product-textarea"
                                          rows="3"
                                          placeholder="Mô tả ngắn hiển thị ở danh mục..."><?= htmlspecialchars($product['MoTaNgan'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="premium-panel product-form-panel mb-4">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Detail</span>
                                <h5 class="mb-0">Chi tiết sản phẩm</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <textarea name="MoTaChiTiet"
                                      class="admin-textarea"
                                      id="moTaChiTiet"><?= htmlspecialchars($product['MoTaChiTiet'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>
                    </div>

                    <div class="premium-panel product-form-panel">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Pricing</span>
                                <h5 class="mb-0">Giá bán</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-md-0">
                                        <label class="product-form-label">Giá gốc</label>
                                        <div class="input-group product-money-group">
                                            <input type="text"
                                                   name="GiaGoc"
                                                   class="form-control product-input currency-input text-right"
                                                   value="<?= htmlspecialchars($product['GiaGoc'] ?? 0, ENT_QUOTES, 'UTF-8') ?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text">₫</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="product-form-label">Giá bán</label>
                                        <div class="input-group product-money-group">
                                            <input type="text"
                                                   name="GiaBan"
                                                   class="form-control product-input currency-input text-right"
                                                   value="<?= htmlspecialchars($product['GiaBan'] ?? 0, ENT_QUOTES, 'UTF-8') ?>">
                                            <div class="input-group-append">
                                                <span class="input-group-text">₫</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4 mb-4">

                    <div class="premium-panel product-form-panel mb-4">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Image</span>
                                <h5 class="mb-0">Ảnh đại diện</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <div id="imagePreviewSingle" class="product-image-preview">
                                <img src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>"
                                     alt="Ảnh sản phẩm"
                                     onerror="this.src='<?= $baseUrl ?>/images/default.jpg'">
                            </div>

                            <div class="custom-file mt-3">
                                <input type="file"
                                       name="imageAvatar"
                                       class="custom-file-input"
                                       id="inputSingleFile"
                                       accept=".jpg,.jpeg,.png,.webp">
                                <label class="custom-file-label" for="inputSingleFile">Chọn file ảnh...</label>
                            </div>

                            <div class="product-note mt-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Nên dùng ảnh rõ sản phẩm, nền sạch, tỉ lệ vuông hoặc ngang.
                            </div>
                        </div>
                    </div>

                    <div class="premium-panel product-form-panel mb-4">
                        <div class="premium-panel-header">
                            <div>
                                <span class="admin-kicker">Category</span>
                                <h5 class="mb-0">Phân loại</h5>
                            </div>
                        </div>

                        <div class="premium-panel-body">
                            <div class="form-group">
                                <label class="product-form-label">Thương hiệu</label>
                                <select name="ThuongHieuId" class="form-control product-input" required>
                                    <option value="">-- Chọn thương hiệu --</option>

                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?= (int)$brand['ThuongHieuId'] ?>"
                                            <?= (isset($product['ThuongHieuId']) && (int)$product['ThuongHieuId'] === (int)$brand['ThuongHieuId']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($brand['TenThuongHieu'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="product-form-label">Loại sản phẩm</label>
                                <select name="LoaiSanPhamId" class="form-control product-input" required>
                                    <option value="">-- Chọn loại sản phẩm --</option>

                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= (int)$category['LoaiSanPhamId'] ?>"
                                            <?= (isset($product['LoaiSanPhamId']) && (int)$product['LoaiSanPhamId'] === (int)$category['LoaiSanPhamId']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['TenLoaiSanPham'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="product-form-label">Trạng thái kinh doanh</label>
                                <select name="TrangThai" class="form-control product-input">
                                    <option value="1" <?= (isset($product['TrangThai']) && (int)$product['TrangThai'] === 1) ? 'selected' : '' ?>>
                                        Đang bán
                                    </option>
                                    <option value="2" <?= (isset($product['TrangThai']) && (int)$product['TrangThai'] === 2) ? 'selected' : '' ?>>
                                        Ngừng bán
                                    </option>
                                </select>
                            </div>

                            <div class="custom-control custom-checkbox product-checkbox mt-3">
                                <input type="hidden" name="IsFeatured" value="0">
                                <input type="checkbox"
                                       name="IsFeatured"
                                       class="custom-control-input"
                                       id="feat"
                                       value="1"
                                    <?= !empty($product['IsFeatured']) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="feat">
                                    Sản phẩm nổi bật
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn product-submit-btn btn-block">
                        <i class="fas fa-save mr-1"></i>
                        <?= $isEdit ? 'Cập nhật thay đổi' : 'Tạo sản phẩm ngay' ?>
                    </button>

                    <a href="<?= $baseUrl ?>/index.php?controller=adminsanpham" class="btn product-cancel-btn btn-block mt-2">
                        Hủy bỏ và quay lại
                    </a>

                </div>

            </div>
        </form>

    </div>
</section>

<script src="https://cdn.tiny.cloud/1/mjx5bcjxjzsekqcvy1ai2z4fspdr4j94doloncihjvicobhw/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    tinymce.init({
        selector: '.admin-textarea',
        height: 420,
        plugins: 'anchor autolink charmap codesample image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline | link image table | align lineheight | numlist bullist indent outdent | removeformat',
        branding: false,
        menubar: false,
        setup: function (editor) {
            editor.on('change', function () {
                editor.save();
            });
        }
    });

    const fileInput = document.getElementById('inputSingleFile');

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const file = this.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    document.getElementById('imagePreviewSingle').innerHTML =
                        '<img src="' + e.target.result + '" alt="preview">';
                };

                reader.readAsDataURL(file);
                this.nextElementSibling.innerText = file.name;
            }
        });
    }

    const formatVND = (value) => {
        return value.toString().replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    };

    document.querySelectorAll('.currency-input').forEach(function (input) {
        input.value = formatVND(input.value);

        input.addEventListener('input', function () {
            this.value = formatVND(this.value);
        });

        input.addEventListener('focus', function () {
            this.value = this.value.replace(/\./g, '');
        });

        input.addEventListener('blur', function () {
            this.value = formatVND(this.value);
        });
    });

    document.getElementById('productForm').addEventListener('submit', function () {
        if (typeof tinymce !== 'undefined') {
            tinymce.triggerSave();
        }

        document.querySelectorAll('.currency-input').forEach(function (input) {
            input.value = input.value.replace(/\./g, '');
        });
    });
</script>