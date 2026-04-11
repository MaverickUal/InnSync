<?php
include "config.php";

if (isset($_POST['action'])) {

    if ($_POST['action'] == "store") {
        $payload = json_decode($_POST['payload']);
        $user_id = $_SESSION['user']['user_id'];
        $status  = "pending";
        $downpayment_amount = $payload->downpayment_amount ?? 0;

        $statement = $conn->prepare("INSERT INTO bookings (user_id, room_id, reservation_type_id, check_in, check_out, status, downpayment_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $statement->bind_param("iiisssd",
            $user_id, $payload->room_id, $payload->reservation_type_id,
            $payload->check_in, $payload->check_out,
            $status, $downpayment_amount
        );
        if ($statement->execute()) {
            $booking_id = $conn->insert_id;
            echo json_encode(["status" => "success", "message" => "Booking created!", "booking_id" => $booking_id]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Booking failed: " . $conn->error]);
        }
    }

    if ($_POST['action'] == "cancel") {
        $id      = (int) $_POST['id'];
        $payload = json_decode($_POST['payload']);
        $user_id = $_SESSION['user']['user_id'];

        $bookStmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ? AND user_id = ? AND status IN ('pending','confirmed')");
        $bookStmt->bind_param("ii", $id, $user_id);
        $bookStmt->execute();
        $booking = $bookStmt->get_result()->fetch_assoc();

        if (!$booking) {
            echo json_encode(["status" => "failed", "message" => "Booking not found or already cancelled"]);
            exit;
        }

        $days_until    = (strtotime($booking['check_in']) - time()) / 86400;
        $refund_amount = 0;

        $ruleStmt = $conn->prepare("SELECT * FROM refund_rules ORDER BY days_before DESC");
        $ruleStmt->execute();
        $rules = $ruleStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $payStmt = $conn->prepare("SELECT downpayment_amount, downpayment_status, remaining_balance, remaining_status, payment_type FROM payments WHERE booking_id = ?");
        $payStmt->bind_param("i", $id);
        $payStmt->execute();
        $payment = $payStmt->get_result()->fetch_assoc();

        if ($payment) {
            $total_paid = 0;
            if ($payment['downpayment_status'] === 'confirmed') $total_paid += $payment['downpayment_amount'];
            if ($payment['payment_type'] === 'full_payment' && $payment['remaining_status'] === 'confirmed') $total_paid += $payment['remaining_balance'];
            foreach ($rules as $rule) {
                if ($days_until >= $rule['days_before']) {
                    $refund_amount = $total_paid * ($rule['refund_percent'] / 100);
                    break;
                }
            }
        }

        $updateStmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
        $updateStmt->bind_param("i", $id);
        $updateStmt->execute();

        $reason     = $payload->reason ?? "Cancelled by user";
        $cancelStmt = $conn->prepare("INSERT INTO cancellations (booking_id, reason, refund_amount, status) VALUES (?, ?, ?, 'pending')");
        $cancelStmt->bind_param("isd", $id, $reason, $refund_amount);
        $cancelStmt->execute();

        echo json_encode(["status" => "success", "message" => "Booking cancelled", "refund_amount" => $refund_amount]);
    }

    if ($_POST['action'] == "update") {
        $id      = $_POST['id'];
        $payload = json_decode($_POST['payload']);

        $statement = $conn->prepare("UPDATE bookings SET status = ? WHERE booking_id = ?");
        $statement->bind_param("si", $payload->status, $id);
        if ($statement->execute()) {
            echo json_encode(["status" => "success", "message" => "Booking updated"]);
        } else {
            echo json_encode(["status" => "failed", "message" => "Update failed"]);
        }
    }
}

if (isset($_GET['action'])) {

    // ── AVAILABILITY CHECK ────────────────────────────────────────────────────
    // Works with both DATE and DATETIME columns via CAST
    if ($_GET['action'] == "check_availability") {
        $room_id   = (int)($_GET['room_id']   ?? 0);
        $check_in  = $conn->real_escape_string($_GET['check_in']  ?? '');
        $check_out = $conn->real_escape_string($_GET['check_out'] ?? '');

        if (!$room_id || !$check_in || !$check_out) {
            echo json_encode(["status" => "failed", "message" => "Missing parameters"]);
            exit;
        }

        // Block if any active booking's window (check_in → check_out + 2hr) overlaps requested window
        $stmt = $conn->prepare("
            SELECT booking_id, check_in, check_out
            FROM bookings
            WHERE room_id = ?
            AND status IN ('confirmed','pending')
            AND DATE(check_in)  < DATE(?)
            AND DATE(check_out) > DATE(?)
            LIMIT 1
        ");
        $stmt->bind_param("iss", $room_id, $check_out, $check_in);
        $stmt->execute();
        $conflict = $stmt->get_result()->fetch_assoc();

        if ($conflict) {
            $ci       = date('M d, Y g:i A', strtotime($conflict['check_in']));
            $co       = date('M d, Y g:i A', strtotime($conflict['check_out']));
            $co_plus2 = date('M d, Y g:i A', strtotime($conflict['check_out'] . ' +2 hours'));
            echo json_encode([
                "status"  => "unavailable",
                "message" => "This room is already booked from {$ci} to {$co}. Earliest next check-in: {$co_plus2} (includes 2-hr cleaning buffer).",
                "conflict"=> $conflict
            ]);
        } else {
            echo json_encode(["status" => "available"]);
        }
        exit;
    }

    if ($_GET['action'] == "get") {
        $statement = $conn->prepare(
            "SELECT b.*, u.fullname, u.email, r.room_name, rt.type_name as reservation_type,
             p.payment_id, p.total_amount, p.payment_type, p.downpayment_status, p.remaining_status
             FROM bookings b
             LEFT JOIN users u              ON b.user_id             = u.user_id
             LEFT JOIN rooms r              ON b.room_id             = r.room_id
             LEFT JOIN reservation_types rt ON b.reservation_type_id = rt.reservation_type_id
             LEFT JOIN payments p           ON b.booking_id          = p.booking_id
             ORDER BY b.created_at DESC"
        );
        $statement->execute();
        $result   = $statement->get_result();
        $bookings = [];
        while ($row = $result->fetch_assoc()) $bookings[] = $row;
        echo json_encode(["status" => "success", "data" => $bookings]);
    }

    if ($_GET['action'] == "getByUser") {
        $user_id = $_SESSION['user']['user_id'];
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
        $statement = $conn->prepare(
            "SELECT b.*, r.room_name, rt.type_name as reservation_type,
             p.payment_id, p.total_amount, p.downpayment_amount, p.downpayment_status,
             p.remaining_balance, p.remaining_status, p.payment_type,
             c.refund_amount, c.status AS cancellation_status
             FROM bookings b
             LEFT JOIN rooms r              ON b.room_id             = r.room_id
             LEFT JOIN reservation_types rt ON b.reservation_type_id = rt.reservation_type_id
             LEFT JOIN payments p           ON b.booking_id          = p.booking_id
             LEFT JOIN cancellations c      ON b.booking_id          = c.booking_id
             WHERE b.user_id = ?
             ORDER BY b.created_at DESC"
        );
        $statement->bind_param("i", $user_id);
        $statement->execute();
        $result   = $statement->get_result();
        $bookings = [];
        while ($row = $result->fetch_assoc()) $bookings[] = $row;
        echo json_encode(["status" => "success", "data" => $bookings]);
    }

    if ($_GET['action'] == "getOne") {
        $id = $_GET['id'];
        $statement = $conn->prepare(
            "SELECT b.*, u.fullname, u.email, r.room_name, r.price, rt.type_name as reservation_type
             FROM bookings b
             LEFT JOIN users u              ON b.user_id             = u.user_id
             LEFT JOIN rooms r              ON b.room_id             = r.room_id
             LEFT JOIN reservation_types rt ON b.reservation_type_id = rt.reservation_type_id
             WHERE b.booking_id = ?"
        );
        $statement->bind_param("i", $id);
        $statement->execute();
        echo json_encode(["status" => "success", "data" => $statement->get_result()->fetch_assoc()]);
    }
}
