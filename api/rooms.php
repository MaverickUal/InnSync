<?php

error_reporting(0);

include "config.php";

// ─── POST store / update / drop ─────────────────────────────────────────────
if (isset($_POST['action'])) {

    if ($_POST['action'] === 'store') {
        $p = json_decode($_POST['payload'] ?? '{}');
        if (!$p || empty($p->room_name)) {
            echo json_encode(['status' => 'failed', 'message' => 'Invalid payload']); exit;
        }

        // nullable INT — use literal NULL in SQL when not set
        $res_type_id = !empty($p->reservation_type_id) ? (int)$p->reservation_type_id : null;

        if ($res_type_id !== null) {
            $stmt = $conn->prepare(
                "INSERT INTO rooms (room_name, type_id, reservation_type_id, price, capacity, status, description)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('siidiss',
                $p->room_name, $p->type_id, $res_type_id,
                $p->price, $p->capacity, $p->status, $p->description
            );
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO rooms (room_name, type_id, reservation_type_id, price, capacity, status, description)
                 VALUES (?, ?, NULL, ?, ?, ?, ?)"
            );
            $stmt->bind_param('sidiss',
                $p->room_name, $p->type_id,
                $p->price, $p->capacity, $p->status, $p->description
            );
        }

        if ($stmt->execute()) {
            $room_id = $conn->insert_id;
            $imgResult = uploadRoomImage($conn, $room_id);
            $msg = 'Room added successfully';
            if ($imgResult !== true && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $msg .= ' (image save failed: ' . $imgResult . ')';
            }
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } else {
            echo json_encode(['status' => 'failed', 'message' => 'Add failed: ' . $stmt->error]);
        }
        exit;
    }

    if ($_POST['action'] === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $p  = json_decode($_POST['payload'] ?? '{}');
        if (!$id || !$p) {
            echo json_encode(['status' => 'failed', 'message' => 'Invalid payload']); exit;
        }

        $res_type_id = !empty($p->reservation_type_id) ? (int)$p->reservation_type_id : null;

        if ($res_type_id !== null) {
            $stmt = $conn->prepare(
                "UPDATE rooms SET room_name=?, type_id=?, reservation_type_id=?, price=?, capacity=?, status=?, description=?
                 WHERE room_id=?"
            );
            $stmt->bind_param('siidissi',
                $p->room_name, $p->type_id, $res_type_id,
                $p->price, $p->capacity, $p->status, $p->description, $id
            );
        } else {
            $stmt = $conn->prepare(
                "UPDATE rooms SET room_name=?, type_id=?, reservation_type_id=NULL, price=?, capacity=?, status=?, description=?
                 WHERE room_id=?"
            );
            $stmt->bind_param('sidissi',
                $p->room_name, $p->type_id,
                $p->price, $p->capacity, $p->status, $p->description, $id
            );
        }

        if ($stmt->execute()) {
            $imgResult = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                deleteRoomImages($conn, $id);
                $imgResult = uploadRoomImage($conn, $id);
            }
            $msg = 'Room updated successfully';
            if ($imgResult !== null && $imgResult !== true) {
                $msg .= ' (image save failed: ' . $imgResult . ')';
            }
            echo json_encode(['status' => 'success', 'message' => $msg]);
        } else {
            echo json_encode(['status' => 'failed', 'message' => 'Update failed: ' . $stmt->error]);
        }
        exit;
    }

    if ($_POST['action'] === 'drop') {
        $id = (int) ($_POST['id'] ?? 0);
        deleteRoomImages($conn, $id);
        $stmt = $conn->prepare("DELETE FROM rooms WHERE room_id=?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Room deleted']);
        } else {
            echo json_encode(['status' => 'failed', 'message' => 'Delete failed: ' . $stmt->error]);
        }
        exit;
    }
}

// ─── GET ──────────────────────────────────────────────────────────────────────
if (isset($_GET['action'])) {

    if ($_GET['action'] === 'get') {
        $where  = 'WHERE 1=1';
        $params = [];
        $types  = '';

        if (!empty($_GET['type_id']))   { $where .= ' AND r.type_id=?';   $params[] = (int)   $_GET['type_id'];   $types .= 'i'; }
        if (!empty($_GET['min_price'])) { $where .= ' AND r.price>=?';    $params[] = (float) $_GET['min_price']; $types .= 'd'; }
        if (!empty($_GET['max_price'])) { $where .= ' AND r.price<=?';    $params[] = (float) $_GET['max_price']; $types .= 'd'; }
        if (!empty($_GET['capacity']))  { $where .= ' AND r.capacity>=?'; $params[] = (int)   $_GET['capacity'];  $types .= 'i'; }

        $sql = "SELECT r.*,
                rt.type_name,
                rtype.reservation_type_id  AS promo_id,
                rtype.type_name            AS promo_name,
                rtype.discount_percent     AS promo_discount,
                (SELECT image_path FROM room_images WHERE room_id = r.room_id LIMIT 1) AS image_path,
                (SELECT COUNT(*) FROM bookings b
                 WHERE b.room_id = r.room_id
                 AND b.status IN ('confirmed','pending')
                 AND DATE(b.check_in)  <= CURDATE()
                 AND DATE(b.check_out) >= CURDATE()
                ) AS booking_occupied,
                (CASE WHEN r.status = 'occupied' THEN 1 ELSE 0 END) AS manual_occupied
                FROM rooms r
                LEFT JOIN room_types rt               ON r.type_id             = rt.type_id
                LEFT JOIN reservation_types rtype     ON r.reservation_type_id = rtype.reservation_type_id
                $where
                ORDER BY r.room_id ASC";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['status' => 'failed', 'message' => 'Prepare failed: ' . $conn->error]); exit;
        }
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();

        $rows = [];
        $res  = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $rows[] = $row;

        echo json_encode(['status' => 'success', 'data' => $rows]);
        exit;
    }

    if ($_GET['action'] === 'getOne') {
        $id   = (int) ($_GET['id'] ?? 0);
        $stmt = $conn->prepare(
            "SELECT r.*,
                    rt.type_name,
                    rtype.reservation_type_id AS promo_id,
                    rtype.type_name           AS promo_name,
                    rtype.discount_percent    AS promo_discount
             FROM rooms r
             LEFT JOIN room_types rt               ON r.type_id             = rt.type_id
             LEFT JOIN reservation_types rtype     ON r.reservation_type_id = rtype.reservation_type_id
             WHERE r.room_id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $room = $stmt->get_result()->fetch_assoc();

        if (!$room) {
            echo json_encode(['status' => 'failed', 'message' => 'Room not found']); exit;
        }

        $imgStmt = $conn->prepare("SELECT * FROM room_images WHERE room_id = ?");
        $imgStmt->bind_param('i', $id);
        $imgStmt->execute();
        $imgs = [];
        $imgRes = $imgStmt->get_result();
        while ($img = $imgRes->fetch_assoc()) $imgs[] = $img;
        $room['images'] = $imgs;

        echo json_encode(['status' => 'success', 'data' => $room]);
        exit;
    }
}

echo json_encode(['status' => 'failed', 'message' => 'No valid action']);

// ─── HELPERS ──────────────────────────────────────────────────────────────────

/**
 * Upload a room image and insert its path into room_images.
 * Returns true on success, or an error string on failure.
 */
function uploadRoomImage($conn, $room_id) {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        return 'no file';
    }

    $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed)) {
        return 'unsupported file type: ' . $ext;
    }

    // Ensure the uploads/rooms directory exists
    $uploadDir = __DIR__ . '/uploads/rooms/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            return 'could not create upload directory';
        }
    }

    // Check directory is writable
    if (!is_writable($uploadDir)) {
        return 'upload directory not writable';
    }

    $filename    = 'room_' . $room_id . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $filename;
    $dbPath      = 'uploads/rooms/' . $filename; // relative path stored in DB

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
        return 'move_uploaded_file failed (check permissions on ' . $uploadDir . ')';
    }

    $stmt = $conn->prepare("INSERT INTO room_images (room_id, image_path) VALUES (?, ?)");
    $stmt->bind_param('is', $room_id, $dbPath);
    if (!$stmt->execute()) {
        return 'DB insert failed: ' . $stmt->error;
    }

    return true;
}

function deleteRoomImages($conn, $room_id) {
    $stmt = $conn->prepare("SELECT image_path FROM room_images WHERE room_id = ?");
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $file = __DIR__ . '/' . $row['image_path'];
        if (file_exists($file)) unlink($file);
    }
    $del = $conn->prepare("DELETE FROM room_images WHERE room_id = ?");
    $del->bind_param('i', $room_id);
    $del->execute();
}
