<?php
include "config.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(["status" => "failed", "message" => "Unauthorized"]);
    exit;
}

if (isset($_GET['action'])) {

    // ── STATS ──────────────────────────────────────────────────────────────────
    if ($_GET['action'] === 'stats') {

        $r = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='customer'");
        $uTotal = (int)$r->fetch_assoc()['total'];

        $r = $conn->query("SELECT COUNT(*) AS approved FROM users WHERE role='customer' AND status='approved'");
        $uApproved = (int)$r->fetch_assoc()['approved'];

        // Room counts by DB status
        $r = $conn->query("SELECT
            COUNT(*) AS total_rooms,
            SUM(CASE WHEN status='available'   THEN 1 ELSE 0 END) AS db_available,
            SUM(CASE WHEN status='unavailable' THEN 1 ELSE 0 END) AS unavailable,
            SUM(CASE WHEN status='maintenance' THEN 1 ELSE 0 END) AS maintenance,
            SUM(CASE WHEN status='occupied'    THEN 1 ELSE 0 END) AS db_occupied
            FROM rooms");
        $rooms = $r->fetch_assoc();

        // Rooms currently occupied via active confirmed/pending bookings today
        $r = $conn->query("SELECT COUNT(DISTINCT room_id) AS occupied FROM bookings
            WHERE status IN ('confirmed','pending')
            AND DATE(check_in)  <= CURDATE()
            AND DATE(check_out) >= CURDATE()");
        $bookingOccupied = (int)$r->fetch_assoc()['occupied'];

        // Total occupied = booking-occupied + manually set to occupied in DB
        $totalOccupied = $bookingOccupied + (int)$rooms['db_occupied'];

        // Available = total - occupied(booking) - db_occupied - maintenance - unavailable
        $available = (int)$rooms['total_rooms']
                   - $bookingOccupied
                   - (int)$rooms['db_occupied']
                   - (int)$rooms['maintenance']
                   - (int)$rooms['unavailable'];
        if ($available < 0) $available = 0;

        // Bookings
        $r = $conn->query("SELECT COUNT(*) AS total FROM bookings");
        $bTotal = (int)$r->fetch_assoc()['total'];

        $r = $conn->query("SELECT COUNT(*) AS cnt FROM bookings WHERE status='pending'");
        $bPending = (int)$r->fetch_assoc()['cnt'];

        $r = $conn->query("SELECT COUNT(*) AS cnt FROM bookings WHERE status='confirmed'");
        $bConfirmed = (int)$r->fetch_assoc()['cnt'];

        $r = $conn->query("SELECT COUNT(*) AS cnt FROM bookings WHERE status='cancelled'");
        $bCancelled = (int)$r->fetch_assoc()['cnt'];

        $r = $conn->query("SELECT COUNT(*) AS cnt FROM bookings WHERE status='completed'");
        $bCompleted = (int)$r->fetch_assoc()['cnt'];

        // Revenue — only non-refunded payments
        // downpayment_status='refunded' means it was returned, so exclude it
        // Also subtract any refund_amount from processed cancellations
        $r = $conn->query("SELECT
            COALESCE(SUM(CASE WHEN p.downpayment_status='confirmed' THEN p.downpayment_amount ELSE 0 END), 0) AS total_downpayments,
            COALESCE(SUM(CASE WHEN p.remaining_status='confirmed'   THEN p.remaining_balance  ELSE 0 END), 0) AS total_remaining
            FROM payments p
            INNER JOIN bookings b ON p.booking_id = b.booking_id
            WHERE b.status NOT IN ('cancelled')");
        $rev = $r->fetch_assoc();

        // Total refunded amounts that have been processed
        $r2 = $conn->query("SELECT COALESCE(SUM(refund_amount), 0) AS total_refunded
            FROM cancellations WHERE status = 'refunded'");
        $rev['total_refunded']  = (float)$r2->fetch_assoc()['total_refunded'];
        $rev['total_collected'] = ($rev['total_downpayments'] + $rev['total_remaining']) - $rev['total_refunded'];
        if ($rev['total_collected'] < 0) $rev['total_collected'] = 0;

        $r = $conn->query("SELECT COUNT(*) AS cnt FROM cancellations WHERE status='pending'");
        $pendingRefunds = (int)$r->fetch_assoc()['cnt'];

        echo json_encode([
            "status" => "success",
            "data"   => [
                "users"           => ["total" => $uTotal, "approved" => $uApproved],
                "rooms"           => [
                    "total_rooms" => (int)$rooms['total_rooms'],
                    "available"   => $available,
                    "unavailable" => (int)$rooms['unavailable'],
                    "maintenance" => (int)$rooms['maintenance']
                ],
                "occupied_rooms"  => $totalOccupied,
                "bookings"        => ["total" => $bTotal, "pending" => $bPending, "confirmed" => $bConfirmed, "cancelled" => $bCancelled, "completed" => $bCompleted],
                "revenue"         => $rev,
                "pending_refunds" => $pendingRefunds
            ]
        ]);
        exit;
    }

    // ── ROOM STATUS GRID ───────────────────────────────────────────────────────
    if ($_GET['action'] === 'room_status') {

        $sql = "SELECT r.room_id, r.room_name, r.status AS room_status, rt.type_name,
                r.price, r.capacity,
                b.booking_id, b.status AS booking_status,
                b.check_in, b.check_out,
                u.fullname AS guest_name
            FROM rooms r
            LEFT JOIN room_types rt ON r.type_id = rt.type_id
            LEFT JOIN bookings b ON b.room_id = r.room_id
                AND b.status IN ('confirmed','pending')
                AND DATE(b.check_in)  <= CURDATE()
                AND DATE(b.check_out) >= CURDATE()
            LEFT JOIN users u ON b.user_id = u.user_id
            ORDER BY r.room_id ASC";

        $result = $conn->query($sql);
        if (!$result) {
            echo json_encode(["status" => "failed", "message" => $conn->error]);
            exit;
        }
        $rooms = [];
        while ($row = $result->fetch_assoc()) $rooms[] = $row;
        echo json_encode(["status" => "success", "data" => $rooms]);
        exit;
    }

    // ── RECENT BOOKINGS ────────────────────────────────────────────────────────
    if ($_GET['action'] === 'recent_bookings') {
        $limit = (int)($_GET['limit'] ?? 8);
        $stmt  = $conn->prepare(
            "SELECT b.booking_id, b.status, b.check_in, b.check_out, b.created_at,
             u.fullname, r.room_name,
             p.total_amount, p.payment_type
             FROM bookings b
             LEFT JOIN users u    ON b.user_id    = u.user_id
             LEFT JOIN rooms r    ON b.room_id    = r.room_id
             LEFT JOIN payments p ON b.booking_id = p.booking_id
             ORDER BY b.created_at DESC
             LIMIT ?"
        );
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $data   = [];
        while ($row = $result->fetch_assoc()) $data[] = $row;
        echo json_encode(["status" => "success", "data" => $data]);
        exit;
    }

    // ── PAYMENT OVERVIEW ──────────────────────────────────────────────────────
    if ($_GET['action'] === 'payment_overview') {
        // Only count payments from non-cancelled bookings
        $r = $conn->query(
            "SELECT p.downpayment_method AS method,
             COUNT(*) AS count,
             COALESCE(SUM(p.downpayment_amount), 0) AS total
             FROM payments p
             INNER JOIN bookings b ON p.booking_id = b.booking_id
             WHERE p.downpayment_status = 'confirmed'
             AND b.status NOT IN ('cancelled')
             GROUP BY p.downpayment_method"
        );
        $methods = [];
        while ($row = $r->fetch_assoc()) $methods[] = $row;
        echo json_encode(["status" => "success", "data" => ["methods" => $methods]]);
        exit;
    }

    // ── REPORTS ───────────────────────────────────────────────────────────────
    if ($_GET['action'] === 'reports') {
        $type   = $_GET['type']   ?? 'daily';
        $period = $_GET['period'] ?? date('Y-m-d');

        switch ($type) {
            case 'weekly':
                $start = date('Y-m-d', strtotime('monday this week', strtotime($period)));
                $end   = date('Y-m-d', strtotime('sunday this week', strtotime($period)));
                break;
            case 'monthly':
                $start = date('Y-m-01', strtotime($period));
                $end   = date('Y-m-t',  strtotime($period));
                break;
            default:
                $start = $period;
                $end   = $period;
        }

        $stmt = $conn->prepare(
            "SELECT b.booking_id, b.status, b.check_in, b.check_out,
             u.fullname, r.room_name, rt.type_name AS room_type,
             p.total_amount, p.downpayment_amount, p.downpayment_status,
             p.remaining_balance, p.remaining_status, p.payment_type,
             c.refund_amount, c.status AS cancellation_status
             FROM bookings b
             LEFT JOIN users u         ON b.user_id    = u.user_id
             LEFT JOIN rooms r         ON b.room_id    = r.room_id
             LEFT JOIN room_types rt   ON r.type_id    = rt.type_id
             LEFT JOIN payments p      ON b.booking_id = p.booking_id
             LEFT JOIN cancellations c ON b.booking_id = c.booking_id
             WHERE DATE(b.created_at) BETWEEN ? AND ?
             ORDER BY b.created_at DESC"
        );
        $stmt->bind_param("ss", $start, $end);
        $stmt->execute();
        $result   = $stmt->get_result();
        $bookings = [];
        while ($row = $result->fetch_assoc()) $bookings[] = $row;

        $stmt2 = $conn->prepare(
            "SELECT
             COUNT(DISTINCT b.booking_id) AS total_bookings,
             SUM(CASE WHEN b.status='confirmed' THEN 1 ELSE 0 END) AS confirmed,
             SUM(CASE WHEN b.status='cancelled' THEN 1 ELSE 0 END) AS cancelled,
             SUM(CASE WHEN b.status='completed' THEN 1 ELSE 0 END) AS completed,
             COALESCE(SUM(CASE WHEN p.downpayment_status='confirmed' AND b.status NOT IN ('cancelled') THEN p.downpayment_amount ELSE 0 END),0) AS dp_revenue,
             COALESCE(SUM(CASE WHEN p.remaining_status='confirmed'   AND b.status NOT IN ('cancelled') THEN p.remaining_balance  ELSE 0 END),0) AS rem_revenue,
             COALESCE(SUM(CASE WHEN c.status='refunded' THEN c.refund_amount ELSE 0 END),0) AS total_refunded
             FROM bookings b
             LEFT JOIN payments p      ON b.booking_id = p.booking_id
             LEFT JOIN cancellations c ON b.booking_id = c.booking_id
             WHERE DATE(b.created_at) BETWEEN ? AND ?"
        );
        $stmt2->bind_param("ss", $start, $end);
        $stmt2->execute();
        $summary = $stmt2->get_result()->fetch_assoc();
        $gross = $summary['dp_revenue'] + $summary['rem_revenue'];
        $summary['total_refunded'] = (float)$summary['total_refunded'];
        $summary['gross_revenue']  = $gross;
        $summary['total_revenue']  = max(0, $gross - $summary['total_refunded']);

        echo json_encode([
            "status" => "success",
            "data"   => ["bookings" => $bookings, "summary" => $summary],
            "period" => ["start" => $start, "end" => $end, "type" => $type]
        ]);
        exit;
    }

    // ── APPROVE / REJECT USER ─────────────────────────────────────────────────
    if ($_GET['action'] === 'approve_user') {
        $id     = (int)($_GET['id'] ?? 0);
        $status = $_GET['status'] ?? '';
        if (!in_array($status, ['approved','blacklist'])) {
            echo json_encode(["status" => "failed", "message" => "Invalid status"]); exit;
        }
        $stmt = $conn->prepare("UPDATE users SET status=? WHERE user_id=?");
        $stmt->bind_param("si", $status, $id);
        echo json_encode($stmt->execute()
            ? ["status" => "success", "message" => "User {$status}"]
            : ["status" => "failed",  "message" => $conn->error]);
        exit;
    }
}

// ── POST approve/blacklist ───────────────────────────────────────────────────
if (isset($_POST['action']) && $_POST['action'] === 'approve_user') {
    $id     = (int)$_POST['id'];
    $status = $_POST['status'] ?? '';
    if (!in_array($status, ['approved','blacklist'])) {
        echo json_encode(["status" => "failed", "message" => "Invalid status"]); exit;
    }
    $stmt = $conn->prepare("UPDATE users SET status=? WHERE user_id=?");
    $stmt->bind_param("si", $status, $id);
    echo json_encode($stmt->execute()
        ? ["status" => "success", "message" => "User {$status}"]
        : ["status" => "failed",  "message" => $conn->error]);
    exit;
}

echo json_encode(["status" => "failed", "message" => "No valid action"]);
