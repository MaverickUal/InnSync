<?php
include "config.php";

// Generate a unique reference number
function generateRef() {
    return 'REF-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));
}

if (isset($_POST['action'])) {

    
    // STORE (DOWNPAYMENT)
    if ($_POST['action'] == "store") {
        $payload = json_decode($_POST['payload']);

        $booking_id   = (int) $payload->booking_id;
        $total_amount = (float) $payload->total_amount;
        $downpayment  = (float) ($payload->downpayment_amount ?? 0);
        $remaining    = $total_amount - $downpayment;
        $method       = $payload->downpayment_method ?? $payload->payment_method;
        $ref          = generateRef();
        $now          = date('Y-m-d H:i:s');

        // Prevent duplicate payment
        $chk = $conn->prepare("SELECT payment_id FROM payments WHERE booking_id=?");
        $chk->bind_param("i", $booking_id);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) {
            echo json_encode(["status"=>"failed","message"=>"Payment already exists for this booking"]);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO payments 
            (booking_id, total_amount, downpayment_amount, downpayment_method, downpayment_status,
             downpayment_reference, downpayment_date, remaining_balance, remaining_status, payment_type)
            VALUES (?, ?, ?, ?, 'confirmed', ?, ?, ?, 'pending', 'downpayment_only')
        ");

        $stmt->bind_param("iddsssd",
            $booking_id,
            $total_amount,
            $downpayment,
            $method,
            $ref,
            $now,
            $remaining
        );

        if ($stmt->execute()) {
            $payment_id = $conn->insert_id;

            // Update booking (merged: includes old + new)
            $upd = $conn->prepare("UPDATE bookings SET status='confirmed', downpayment_paid=1 WHERE booking_id=?");
            $upd->bind_param("i", $booking_id);
            $upd->execute();

            // Receipt
            $receipt_no = 'DP-' . strtoupper(uniqid());
            $rcpt = $conn->prepare("INSERT INTO receipts (payment_id, receipt_number) VALUES (?, ?)");
            $rcpt->bind_param("is", $payment_id, $receipt_no);
            $rcpt->execute();

            echo json_encode([
                "status" => "success",
                "message" => "Downpayment confirmed!",
                "payment_id" => $payment_id,
                "reference" => $ref,
                "receipt_number" => $receipt_no
            ]);
        } else {
            echo json_encode(["status"=>"failed","message"=>"Payment failed: ".$conn->error]);
        }
    }

  
    // PAY REMAINING
    if ($_POST['action'] == "payRemaining") {
        $payload = json_decode($_POST['payload']);

        $id     = $payload->payment_id;
        $method = $payload->remaining_method;
        $ref    = generateRef();
        $now    = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("
            UPDATE payments SET 
            remaining_method=?, remaining_status='confirmed', remaining_reference=?,
            remaining_date=?, payment_type='full_payment'
            WHERE payment_id=?
        ");

        $stmt->bind_param("sssi", $method, $ref, $now, $id);

        if ($stmt->execute()) {

            $get = $conn->prepare("SELECT booking_id FROM payments WHERE payment_id=?");
            $get->bind_param("i", $id);
            $get->execute();
            $row = $get->get_result()->fetch_assoc();

            // Receipt
            $receipt_no = 'FULL-' . strtoupper(uniqid());
            $rcpt = $conn->prepare("INSERT INTO receipts (payment_id, receipt_number) VALUES (?, ?)");
            $rcpt->bind_param("is", $id, $receipt_no);
            $rcpt->execute();

            // Update booking
            $upd = $conn->prepare("UPDATE bookings SET status='confirmed' WHERE booking_id=?");
            $upd->bind_param("i", $row['booking_id']);
            $upd->execute();

            echo json_encode([
                "status" => "success",
                "message" => "Full payment complete!",
                "reference" => $ref,
                "receipt_number" => $receipt_no
            ]);
        } else {
            echo json_encode(["status"=>"failed","message"=>"Payment failed: ".$conn->error]);
        }
    }


    // REFUND (ADMIN)
    if ($_POST['action'] == "refund") {
        $id       = $_POST['id'];
        $admin_id = $_SESSION['user']['user_id'];

        $stmt = $conn->prepare("UPDATE payments SET downpayment_status='refunded', remaining_status='refunded' WHERE payment_id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {

            $get = $conn->prepare("SELECT booking_id FROM payments WHERE payment_id=?");
            $get->bind_param("i", $id);
            $get->execute();
            $row = $get->get_result()->fetch_assoc();

            $upd = $conn->prepare("UPDATE cancellations SET status='refunded' WHERE booking_id=?");
            $upd->bind_param("i", $row['booking_id']);
            $upd->execute();

            echo json_encode(["status"=>"success","message"=>"Refund processed"]);
        } else {
            echo json_encode(["status"=>"failed","message"=>"Refund failed"]);
        }
    }
}


// GET REQUESTS (MERGED)
if (isset($_GET['action'])) {

    if ($_GET['action'] == "get") {
        $stmt = $conn->prepare("
            SELECT p.*, b.check_in, b.check_out, b.status AS booking_status,
                   u.fullname, r.room_name,
                   c.refund_amount, c.status AS cancellation_status
            FROM payments p
            LEFT JOIN bookings b      ON p.booking_id = b.booking_id
            LEFT JOIN users u         ON b.user_id    = u.user_id
            LEFT JOIN rooms r         ON b.room_id    = r.room_id
            LEFT JOIN cancellations c ON b.booking_id = c.booking_id
            ORDER BY p.created_at DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) $data[] = $row;

        echo json_encode(["status"=>"success","data"=>$data]);
    }

    if ($_GET['action'] == "getByBooking") {
        $booking_id = $_GET['booking_id'];

        $stmt = $conn->prepare("
            SELECT p.*, b.check_in, b.check_out, u.fullname, u.email, r.room_name, r.price
            FROM payments p
            LEFT JOIN bookings b ON p.booking_id = b.booking_id
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN rooms r ON b.room_id = r.room_id
            WHERE p.booking_id=?
        ");

        $stmt->bind_param("i", $booking_id);
        $stmt->execute();

        echo json_encode(["status"=>"success","data"=>$stmt->get_result()->fetch_assoc()]);
    }

    if ($_GET['action'] == "getByUser") {
        $user_id = $_SESSION['user']['user_id'];

        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        $stmt = $conn->prepare("
            SELECT p.*, b.check_in, b.check_out, r.room_name
            FROM payments p
            LEFT JOIN bookings b ON p.booking_id = b.booking_id
            LEFT JOIN rooms r ON b.room_id = r.room_id
            WHERE b.user_id=?
            ORDER BY p.created_at DESC
        ");

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) $data[] = $row;

        echo json_encode(["status"=>"success","data"=>$data]);
    }

    if ($_GET['action'] == "getOne") {
        $id = $_GET['id'];

        $stmt = $conn->prepare("
            SELECT p.*, b.check_in, b.check_out, u.fullname, u.email, r.room_name, r.price
            FROM payments p
            LEFT JOIN bookings b ON p.booking_id = b.booking_id
            LEFT JOIN users u ON b.user_id = u.user_id
            LEFT JOIN rooms r ON b.room_id = r.room_id
            WHERE p.payment_id=?
        ");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        echo json_encode(["status"=>"success","data"=>$stmt->get_result()->fetch_assoc()]);
    }
}