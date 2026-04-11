<?php
include "config.php";

header('Content-Type: application/json');

if (isset($_GET['action']) && $_GET['action'] === 'get') {

    $sql = "
        SELECT
            p.payment_id            AS transaction_id,
            u.fullname,
            r.room_name,
            b.check_in,
            b.check_out,
            b.status                AS booking_status,
            p.payment_type,
            p.total_amount,
            p.downpayment_amount,
            p.downpayment_method,
            p.downpayment_reference,
            p.downpayment_status,
            p.downpayment_date,
            p.remaining_balance,
            p.remaining_method,
            p.remaining_reference,
            p.remaining_status,
            p.remaining_date,
            rc_dp.receipt_number    AS dp_receipt_number,
            rc_full.receipt_number  AS full_receipt_number,
            c.refund_amount,
            c.status                AS cancellation_status
        FROM payments p
        LEFT JOIN bookings b       ON p.booking_id  = b.booking_id
        LEFT JOIN users u          ON b.user_id     = u.user_id
        LEFT JOIN rooms r          ON b.room_id     = r.room_id
        LEFT JOIN receipts rc_dp   ON p.payment_id  = rc_dp.payment_id
                                   AND rc_dp.receipt_number LIKE 'DP-%'
        LEFT JOIN receipts rc_full ON p.payment_id  = rc_full.payment_id
                                   AND rc_full.receipt_number LIKE 'FULL-%'
        LEFT JOIN cancellations c  ON b.booking_id  = c.booking_id
        ORDER BY p.payment_id DESC
    ";

    $result = $conn->query($sql);
    if (!$result) {
        echo json_encode(["status" => "failed", "message" => $conn->error]);
        exit;
    }

    $data = [];
    while ($row = $result->fetch_assoc()) $data[] = $row;

    echo json_encode(["status" => "success", "data" => $data]);
    exit;
}

echo json_encode(["status" => "failed", "message" => "No valid action"]);