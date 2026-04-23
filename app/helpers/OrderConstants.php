<?php

class PaymentConstants {
    const COD = "COD";
    const VNPAY = "VNPAY";

    const PENDING = "Pending";
    const PAID = "Paid";
    const FAILED = "Failed";
    const REFUNDED = "Refunded";
}

class OrderStatusConstants {
    const PENDING = 1;               // Chờ xác nhận
    const CONFIRMED = 2;             // Đã xác nhận
    const PREPARING = 3;             // Đang chuẩn bị
    const ASSIGNED_TO_SHIPPER = 4;   // Đã giao shipper
    const DELIVERING = 5;            // Đang giao
    const DELIVERED = 6;             // Giao thành công
    const DELIVERY_FAILED = 7;       // Giao thất bại
    const CANCELLED = 8;             // Đã hủy

    public static function getName($status) {
        switch ($status) {
            case self::PENDING: return "Chờ xác nhận";
            case self::CONFIRMED: return "Đã xác nhận";
            case self::PREPARING: return "Đang chuẩn bị";
            case self::ASSIGNED_TO_SHIPPER: return "Đã giao shipper";
            case self::DELIVERING: return "Đang giao";
            case self::DELIVERED: return "Giao thành công";
            case self::DELIVERY_FAILED: return "Giao thất bại";
            case self::CANCELLED: return "Đã hủy";
            default: return "Không xác định";
        }
    }

    public static function getBadgeClass($status) {
        switch ($status) {
            case self::PENDING: return "badge-warning";
            case self::DELIVERED: return "badge-success";
            case self::CANCELLED: return "badge-danger";
            case self::DELIVERING: return "badge-primary";
            default: return "badge-secondary";
        }
    }
}