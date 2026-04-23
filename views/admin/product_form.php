<div class="admin-page-header">
    <div>
        <h1 class="admin-page-title"><?= $title ?></h1>
        <p class="admin-page-subtitle">Tạo sản phẩm mới cho hệ thống mắt kính Karma.</p>
    </div>

    <ol class="breadcrumb admin-breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?controller=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="index.php?controller=adminsanpham">Sản phẩm</a></li>
        <li class="breadcrumb-item active"><?= $title ?></li>
    </ol>
</div>

<section class="content">
    <div class="container-fluid p-0">
        <form action="index.php?controller=adminsanpham&action=save" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="SanPhamId" value="<?= $product['SanPhamId'] ?? 0 ?>">

            <div class="row">
                <div class="col-lg-8">
                    <div class="card admin-form-card mb-4 shadow-sm">
                        <div class="card-header border-0 bg-white">
                            <h3 class="card-title font-weight-bold">Thông tin sản phẩm</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="font-weight-bold">Tên sản phẩm</label>
                                <input type="text" name="TenSanPham" class="form-control admin-input" 
                                       placeholder="Nhập tên kính (VD: Ray-Ban Aviator Classic)..."
                                       value="<?= htmlspecialchars($product['TenSanPham'] ?? '') ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Mã sản phẩm (SKU)</label>
                                        <?php 
                                            // Tự động gen mã nếu là thêm mới
                                            $autoSKU = "SP" . date("ymdHis");
                                            $currentSKU = !empty($product['MaSanPham']) ? $product['MaSanPham'] : $autoSKU;
                                        ?>
                                        <input type="text" name="MaSanPham" class="form-control admin-input" 
                                               value="<?= htmlspecialchars($currentSKU) ?>" readonly 
                                               style="background-color: #e9ecef; cursor: not-allowed;"
                                               title="Mã sản phẩm được hệ thống tự động tạo">
                                        <small class="text-muted italic">* Mã tự động tạo để quản lý kho chính xác.</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold">Số lượng tồn kho</label>
                                        <input type="number" name="SoLuongTon" class="form-control admin-input text-right" 
                                               value="<?= $product['SoLuongTon'] ?? 0 ?>" min="0" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Mô tả tóm tắt</label>
                                <textarea name="MoTaNgan" class="form-control admin-input" rows="2" 
                                          placeholder="Mô tả ngắn hiển thị ở danh mục..."><?= htmlspecialchars($product['MoTaNgan'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="font-weight-bold">Chi tiết sản phẩm</label>
                                <textarea name="MoTaChiTiet" class="admin-textarea" id="moTaChiTiet">
                                    <?= $product['MoTaChiTiet'] ?? '' ?>
                                </textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-muted">Giá gốc (đ nhập)</label>
                                        <div class="input-group">
                                            <input type="text" name="GiaGoc" class="form-control admin-input currency-input text-right" 
                                                   value="<?= $product['GiaGoc'] ?? 0 ?>">
                                            <div class="input-group-append"><span class="input-group-text">₫</span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="font-weight-bold text-danger">Giá bán lẻ (đ)</label>
                                        <div class="input-group">
                                            <input type="text" name="GiaBan" class="form-control admin-input currency-input text-right font-weight-bold text-danger" 
                                                   value="<?= $product['GiaBan'] ?? 0 ?>">
                                            <div class="input-group-append"><span class="input-group-text">₫</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card admin-form-card mb-4 shadow-sm">
                        <div class="card-header border-0 bg-white">
                            <h3 class="card-title font-weight-bold">Ảnh đại diện</h3>
                        </div>
                        <div class="card-body">
                            <div id="imagePreviewSingle" class="admin-image-preview-single border rounded d-flex align-items-center justify-content-center mb-3" style="height:250px; overflow:hidden; background:#f8f9fa;">
                                <?php if(!empty($product['HinhAnhChinh'])): ?>
                                    <img src="/BanMatKinh/public/images/<?= $product['HinhAnhChinh'] ?>" class="img-fluid h-100 object-fit-cover" />
                                <?php else: ?>
                                    <span class="text-muted small">Xem trước ảnh sản phẩm</span>
                                <?php endif; ?>
                            </div>
                            <div class="custom-file">
                                <input type="file" name="imageAvatar" class="custom-file-input" id="inputSingleFile" accept=".jpg,.jpeg,.png,.webp">
                                <label class="custom-file-label" for="inputSingleFile">Chọn file ảnh...</label>
                            </div>
                        </div>
                    </div>

                    <div class="card admin-form-card mb-4 shadow-sm">
                        <div class="card-header border-0 bg-white">
                            <h3 class="card-title font-weight-bold">Phân loại & Trạng thái</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Thương hiệu</label>
                                <select name="ThuongHieuId" class="form-control admin-input" required>
                                    <option value="">-- Chọn thương hiệu --</option>
                                    <?php foreach ($brands as $b): ?>
                                        <option value="<?= $b['ThuongHieuId'] ?>" <?= (isset($product['ThuongHieuId']) && $product['ThuongHieuId'] == $b['ThuongHieuId']) ? 'selected' : '' ?>>
                                            <?= $b['TenThuongHieu'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Loại sản phẩm</label>
                                <select name="LoaiSanPhamId" class="form-control admin-input" required>
                                    <option value="">-- Chọn loại sản phẩm --</option>
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= $c['LoaiSanPhamId'] ?>" <?= (isset($product['LoaiSanPhamId']) && $product['LoaiSanPhamId'] == $c['LoaiSanPhamId']) ? 'selected' : '' ?>>
                                            <?= $c['TenLoaiSanPham'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Trạng thái kinh doanh</label>
                                <select name="TrangThai" class="form-control admin-input">
                                    <option value="1" <?= (isset($product['TrangThai']) && $product['TrangThai'] == 1) ? 'selected' : '' ?>>✅ Đang bán</option>
                                    <option value="2" <?= (isset($product['TrangThai']) && $product['TrangThai'] == 2) ? 'selected' : '' ?>>🚫 Ngừng bán</option>
                                </select>
                            </div>

                            <div class="custom-control custom-checkbox mt-3">
                                <input type="hidden" name="IsFeatured" value="0">
                                <input type="checkbox" name="IsFeatured" class="custom-control-input" id="feat" value="1" <?= (!empty($product['IsFeatured'])) ? 'checked' : '' ?>>
                                <label class="custom-control-label font-weight-bold text-primary" for="feat">Sản phẩm nổi bật</label>
                            </div>
                        </div>
                    </div>

                    <div class="admin-form-actions mt-4">
                        <button type="submit" class="btn btn-primary btn-block btn-lg shadow">
                            <i class="fas fa-save mr-1"></i> <?= isset($product) ? 'Cập nhật thay đổi' : 'Tạo sản phẩm ngay' ?>
                        </button>
                        <a href="index.php?controller=adminsanpham" class="btn btn-link btn-block text-muted">Hủy bỏ và quay lại</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script src="https://cdn.tiny.cloud/1/mjx5bcjxjzsekqcvy1ai2z4fspdr4j94doloncihjvicobhw/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
    // 1. Khởi tạo TinyMCE cho mô tả chi tiết
    tinymce.init({
        selector: '.admin-textarea',
        height: 400,
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        setup: function (editor) {
            editor.on('change', function () { editor.save(); });
        }
    });

    // 2. Preview ảnh khi chọn file
    document.getElementById('inputSingleFile').addEventListener('change', function () {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('imagePreviewSingle').innerHTML =
                    '<img src="' + e.target.result + '" class="img-fluid h-100 object-fit-cover" />';
            };
            reader.readAsDataURL(file);
            // Cập nhật nhãn file
            this.nextElementSibling.innerText = file.name;
        }
    });

    // 3. Logic xử lý định dạng tiền tệ (VND)
    const formatVND = (v) => v.replace(/\D/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    
    document.querySelectorAll(".currency-input").forEach(input => {
        // Định dạng khi load trang
        input.value = formatVND(input.value);

        input.addEventListener("input", function() {
            this.value = formatVND(this.value);
        });

        input.addEventListener("focus", function() {
            this.value = this.value.replace(/\./g, "");
        });

        input.addEventListener("blur", function() {
            this.value = formatVND(this.value);
        });
    });

    // Trước khi submit, xóa dấu chấm để gửi số nguyên về server
    document.querySelector("form").addEventListener("submit", function() {
        document.querySelectorAll(".currency-input").forEach(input => {
            input.value = input.value.replace(/\./g, "");
        });
    });
</script>