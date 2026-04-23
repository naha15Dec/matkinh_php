<?php
class HashPassword {
    /**
     * Tạo hash bảo mật cho mật khẩu
     */
    public static function hash($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Kiểm tra mật khẩu nhập vào có khớp với hash trong DB không
     */
    public static function verify($password, $hash) {
        return password_verify($password, $hash);
    }
}