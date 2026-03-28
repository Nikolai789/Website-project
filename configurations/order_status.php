<?php

const ORDER_STATUS_PENDING = 'pending';
const ORDER_STATUS_SHIPPED = 'shipped';
const ORDER_STATUS_PAID_DELIVERED = 'paid/delivered';

function allowedOrderStatuses(): array
{
    return [
        ORDER_STATUS_PENDING,
        ORDER_STATUS_SHIPPED,
        ORDER_STATUS_PAID_DELIVERED,
    ];
}

function completedOrderStatuses(): array
{
    return [
        ORDER_STATUS_PAID_DELIVERED,
        'delivered',
    ];
}

function normalizeOrderStatus(string $status): string
{
    return strtolower(trim($status));
}

function formatOrderStatusLabel(string $status): string
{
    return match (normalizeOrderStatus($status)) {
        ORDER_STATUS_PENDING => 'Pending',
        ORDER_STATUS_SHIPPED => 'Shipped',
        ORDER_STATUS_PAID_DELIVERED => 'Paid / Delivered',
        default => ucwords(str_replace(['_', '/'], [' ', ' / '], normalizeOrderStatus($status))),
    };
}

function orderStatusClassName(string $status): string
{
    return preg_replace('/[^a-z0-9_-]/i', '-', normalizeOrderStatus($status)) ?: 'unknown';
}
