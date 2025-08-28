<?php
// Include header
include_once '../includes/header.php';

// Check if user is a librarian
checkUserRole('librarian');

// Auto-expire book requests that haven't been collected
autoExpireBookRequests($conn);

// Process request operations
$message = '';
$messageType = '';

// // ----------------- HELPER FUNCTIONS -----------------

// function approveReservationRequest($conn, $id) {
//     try {
//         // Get reservation request details
//         $stmt = $conn->prepare("
//             SELECT rr.*, b.book_name, b.available_quantity, u.id as user_id, u.name as user_name
//             FROM reservation_requests rr
//             JOIN books b ON rr.book_id = b.id
//             JOIN users u ON rr.user_id = u.id
//             WHERE rr.id = ?
//         ");
//         $stmt->bind_param("i", $id);
//         $stmt->execute();
//         $result = $stmt->get_result();

//         if ($result->num_rows != 1) {
//             return ['success' => false, 'message' => "Reservation request not found."];
//         }

//         $request = $result->fetch_assoc();

//         $conn->begin_transaction();

//         // Check book availability
//         if ($request['available_quantity'] <= 0) {
//             throw new Exception("Book is not available.");
//         }

//         // Approve reservation
//         $stmt = $conn->prepare("UPDATE reservation_requests SET status = 'approved', approved_at = NOW() WHERE id = ?");
//         $stmt->bind_param("i", $id);
//         $stmt->execute();

//         // Generate return date (14 days from now)
//         $returnDate = generateDueDate(14);

//         // Create issued book record
//         $stmt = $conn->prepare("
//             INSERT INTO issued_books (book_id, user_id, return_date)
//             VALUES (?, ?, ?)
//         ");
//         $stmt->bind_param("iis", $request['book_id'], $request['user_id'], $returnDate);
//         $stmt->execute();

//         // Update availability
//         updateBookAvailability($conn, $request['book_id'], 'issue');

//         // Notify user
//         $notificationMsg = "Your reservation for '{$request['book_name']}' has been approved. Please collect the book from the library.";
//         sendNotification($conn, $request['user_id'], $notificationMsg);

//         $conn->commit();
//         return ['success' => true, 'message' => "Reservation approved successfully and book issued to {$request['user_name']}."];
//     } catch (Exception $e) {
//         $conn->rollback();
//         return ['success' => false, 'message' => "Error approving reservation: " . $e->getMessage()];
//     }
// }

// function rejectReservationRequest($conn, $id) {
//     try {
//         $stmt = $conn->prepare("
//             SELECT rr.*, b.book_name, u.id as user_id
//             FROM reservation_requests rr
//             JOIN books b ON rr.book_id = b.id
//             JOIN users u ON rr.user_id = u.id
//             WHERE rr.id = ?
//         ");
//         $stmt->bind_param("i", $id);
//         $stmt->execute();
//         $result = $stmt->get_result();

//         if ($result->num_rows != 1) {
//             return ['success' => false, 'message' => "Reservation request not found."];
//         }

//         $request = $result->fetch_assoc();

//         // Reject
//         $stmt = $conn->prepare("UPDATE reservation_requests SET status = 'rejected' WHERE id = ?");
//         $stmt->bind_param("i", $id);
//         $stmt->execute();

//         // Notify
//         $notificationMsg = "Your reservation for '{$request['book_name']}' has been rejected.";
//         sendNotification($conn, $request['user_id'], $notificationMsg);

//         return ['success' => true, 'message' => "Reservation rejected successfully."];
//     } catch (Exception $e) {
//         return ['success' => false, 'message' => "Error rejecting reservation: " . $e->getMessage()];
//     }
// }

// ----------------- MAIN REQUEST PROCESSING -----------------

if (isset($_GET['id']) && isset($_GET['action']) && isset($_GET['type'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    $type = $_GET['type']; // 'book' or 'reservation'
    
    if ($type == 'book') {
        // ---------------- BOOK REQUESTS ----------------
        if ($action == 'approve') {
            try {
                // Fetch request with book and user details (ensure it's pending)
                $stmt = $conn->prepare("
                    SELECT br.*, b.book_name, b.available_quantity, u.id as user_id, u.name as user_name
                    FROM book_requests br
                    JOIN books b ON br.book_id = b.id
                    JOIN users u ON br.user_id = u.id
                    WHERE br.id = ? AND br.status = 'pending'
                ");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows !== 1) {
                    throw new Exception('Book request not found or already processed.');
                }
                $request = $res->fetch_assoc();

                // Count currently approved and uncollected requests for the same book
                $countStmt = $conn->prepare("
                    SELECT COUNT(*) AS approved_count
                    FROM book_requests
                    WHERE book_id = ? AND status = 'approved' AND collected = 0
                ");
                $countStmt->bind_param("i", $request['book_id']);
                $countStmt->execute();
                $countRes = $countStmt->get_result();
                $approvedCount = (int)$countRes->fetch_assoc()['approved_count'];

                if ($approvedCount >= (int)$request['available_quantity']) {
                    throw new Exception('Cannot approve: no available copies left.');
                }

                // Approve this request and set expiry (2 days)
                $approveStmt = $conn->prepare("UPDATE book_requests SET status = 'approved', approved_at = NOW(), expires_at = DATE_ADD(NOW(), INTERVAL 2 DAY) WHERE id = ?");
                $approveStmt->bind_param("i", $id);
                $approveStmt->execute();

                // Notify user
                $notificationMsg = "Your request for '{$request['book_name']}' has been approved. Please collect it within 2 days.";
                sendNotification($conn, (int)$request['user_id'], $notificationMsg);

                // After approving, if now the approved count reaches availability, auto-reject remaining pending requests for this book
                $countStmt->execute();
                $countRes = $countStmt->get_result();
                $approvedCountAfter = (int)$countRes->fetch_assoc()['approved_count'];
                if ($approvedCountAfter >= (int)$request['available_quantity']) {
                    // Find pending requests to reject
                    $pendingStmt = $conn->prepare("SELECT id, user_id FROM book_requests WHERE book_id = ? AND status = 'pending'");
                    $pendingStmt->bind_param("i", $request['book_id']);
                    $pendingStmt->execute();
                    $pendingRes = $pendingStmt->get_result();
                    while ($row = $pendingRes->fetch_assoc()) {
                        $rejId = (int)$row['id'];
                        $rejUserId = (int)$row['user_id'];
                        $conn->query("UPDATE book_requests SET status = 'rejected' WHERE id = {$rejId}");
                        sendNotification($conn, $rejUserId, "Your request for '{$request['book_name']}' was rejected because all available copies have been allocated.");
                    }
                }

                $message = 'Book request approved successfully.';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Error approving request: ' . $e->getMessage();
                $messageType = 'danger';
            }
        } elseif ($action == 'reject') {
            try {
                // Fetch pending request
                $stmt = $conn->prepare("
                    SELECT br.*, b.book_name, u.id as user_id
                    FROM book_requests br
                    JOIN books b ON br.book_id = b.id
                    JOIN users u ON br.user_id = u.id
                    WHERE br.id = ? AND br.status = 'pending'
                ");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows !== 1) {
                    throw new Exception('Book request not found or already processed.');
                }
                $request = $res->fetch_assoc();

                $upd = $conn->prepare("UPDATE book_requests SET status = 'rejected' WHERE id = ?");
                $upd->bind_param("i", $id);
                $upd->execute();

                sendNotification($conn, (int)$request['user_id'], "Your request for '{$request['book_name']}' has been rejected.");

                $message = 'Book request rejected successfully.';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Error rejecting request: ' . $e->getMessage();
                $messageType = 'danger';
            }
        } elseif ($action == 'collected') {
            // Mark an approved request as collected, create issued_books record, and decrement availability
            try {
                // Fetch approved & not yet collected request
                $stmt = $conn->prepare("
                    SELECT br.*, b.book_name, b.available_quantity, u.id as user_id
                    FROM book_requests br
                    JOIN books b ON br.book_id = b.id
                    JOIN users u ON br.user_id = u.id
                    WHERE br.id = ? AND br.status = 'approved' AND br.collected = 0
                ");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res->num_rows !== 1) {
                    throw new Exception('Approved request not found or already collected.');
                }
                $request = $res->fetch_assoc();

                // Check expiry
                if (!empty($request['expires_at']) && strtotime($request['expires_at']) < time()) {
                    // Mark expired
                    $conn->query("UPDATE book_requests SET status = 'expired' WHERE id = " . (int)$request['id']);
                    throw new Exception('Request has expired and cannot be collected.');
                }

                // Ensure availability at collection time
                if ((int)$request['available_quantity'] <= 0) {
                    throw new Exception('No copies available to collect at this moment.');
                }

                $conn->begin_transaction();

                // Create issued_books record with default 14-day due date
                $returnDate = generateDueDate(14);
                $ins = $conn->prepare("INSERT INTO issued_books (book_id, user_id, return_date) VALUES (?, ?, ?)");
                $ins->bind_param("iis", $request['book_id'], $request['user_id'], $returnDate);
                $ins->execute();

                // Decrement availability
                updateBookAvailability($conn, (int)$request['book_id'], 'issue');

                // Mark collected
                $conn->query("UPDATE book_requests SET collected = 1, collected_at = NOW() WHERE id = " . (int)$request['id']);

                // Notify user
                sendNotification($conn, (int)$request['user_id'], "Please collect your approved book '{$request['book_name']}'. It has now been issued to you. Due date: " . date('F j, Y', strtotime($returnDate)) . ".");

                $conn->commit();

                $message = 'Marked as collected and book issued.';
                $messageType = 'success';
            } catch (Exception $e) {
                $conn->rollback();
                $message = 'Error marking collected: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    } elseif ($type == 'reservation') {
        // ---------------- RESERVATION REQUESTS ----------------
        if ($action == 'approve') {
            $result = approveReservationRequest($conn, $id);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
        } elseif ($action == 'reject') {
            $result = rejectReservationRequest($conn, $id);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'danger';
        }
    }
}

// ----------------- SEARCH / FILTER -----------------

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$requestType = isset($_GET['request_type']) ? trim($_GET['request_type']) : '';

// Get book requests
$bookRequests = [];
$sql = "
    SELECT br.*, b.book_name as book_name, u.name as user_name, 'book' as request_type, br.collected, br.expires_at, b.available_quantity
    FROM book_requests br
    JOIN books b ON br.book_id = b.id
    JOIN users u ON br.user_id = u.id
    WHERE 1=1
";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (b.book_name LIKE ? OR u.name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if (!empty($status)) {
    $sql .= " AND br.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY br.request_date DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $bookRequests[] = $row;
}

// Get reservation requests
$reservationRequests = [];
$sql = "
    SELECT rr.*, b.book_name as book_name, u.name as user_name, 'reservation' as request_type, b.available_quantity
    FROM reservation_requests rr
    JOIN books b ON rr.book_id = b.id
    JOIN users u ON rr.user_id = u.id
    WHERE 1=1
";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (b.book_name LIKE ? OR u.name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if (!empty($status)) {
    $sql .= " AND rr.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY rr.request_date DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $reservationRequests[] = $row;
}

// Combine all requests
$allRequests = array_merge($bookRequests, $reservationRequests);
usort($allRequests, function($a, $b) {
    return strtotime($b['request_date']) - strtotime($a['request_date']);
});
?>

<h1 class="page-title">Book & Reservation Requests</h1>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<!-- Search Form -->
<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
    <form action="" method="GET" style="display: flex; align-items: center; gap: 10px;">
        <input 
            type="text" 
            name="search" 
            placeholder="Search requests..." 
            class="form-control" 
            style="width: 300px;"
            value="<?php echo htmlspecialchars($search); ?>"
        >
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Search
        </button>
    </form>
</div>

<!-- Requests Table -->
<div class="table-container" style="margin-top:30px;">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Type</th>
                <th>Book Name</th>
                <th>Requested By</th>
                <th>Request Date</th>
                <th>Status</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($allRequests) > 0): ?>
                <?php foreach ($allRequests as $request): ?>
                    <tr>
                        <td>
                            <?php if ($request['request_type'] == 'book'): ?>
                                <span class="badge badge-primary">
                                    <i class="fas fa-book"></i>&nbsp;Book Request
                                </span>
                            <?php else: ?>
                                <span class="badge badge-warning">
                                    <i class="fas fa-bookmark"></i>&nbsp;Reservation Request
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($request['book_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['user_name']); ?></td>
                        <td><?php echo date('M d, Y H:i', strtotime($request['request_date'])); ?></td>
                        <td>
                            <?php 
                            switch ($request['status']) {
                                case 'pending':
                                    echo '<span class="badge badge-warning">Pending</span>';
                                    break;
                                case 'approved':
                                    if ($request['request_type'] == 'book' && !$request['collected']) {
                                        echo '<span class="badge badge-info">Approved - Awaiting Collection</span>';
                                        if (isset($request['expires_at']) && strtotime($request['expires_at']) < time()) {
                                            echo '<br><small class="text-danger">EXPIRED</small>';
                                        }
                                    } else {
                                        echo '<span class="badge badge-success">Approved</span>';
                                    }
                                    break;
                                case 'rejected':
                                    echo '<span class="badge badge-danger">Rejected</span>';
                                    break;
                                case 'expired':
                                    echo '<span class="badge badge-secondary">Expired</span>';
                                    break;
                                default:
                                    echo htmlspecialchars($request['status']);
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($request['notes'] ?? ''); ?></td>
                        <td>
                            <?php if ($request['status'] == 'pending'): ?>
                                <?php 
                                $canApprove = true;
                                if ($request['request_type'] == 'book') {
                                    $approvedCountStmt = $conn->prepare("
                                        SELECT COUNT(*) as approved_count 
                                        FROM book_requests 
                                        WHERE book_id = ? AND status = 'approved' AND collected = 0
                                    ");
                                    $approvedCountStmt->bind_param("i", $request['book_id']);
                                    $approvedCountStmt->execute();
                                    $approvedCountResult = $approvedCountStmt->get_result();
                                    $approvedCount = $approvedCountResult->fetch_assoc()['approved_count'];
                                    
                                    if ($approvedCount >= $request['available_quantity']) {
                                        $canApprove = false;
                                    }
                                }
                                ?>
                                <?php if ($canApprove): ?>
                                <a href="?id=<?php echo $request['id']; ?>&action=approve&type=<?php echo $request['request_type']; ?>" 
                                   class="btn btn-sm btn-success js-confirm" 
                                   data-title="Approve Request" 
                                   data-message="Are you sure you want to approve this request?">
                                    <i class="fas fa-check"></i> Approve
                                </a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="fas fa-ban"></i> Cannot Approve
                                    </button>
                                <?php endif; ?>
                                
                                <a href="?id=<?php echo $request['id']; ?>&action=reject&type=<?php echo $request['request_type']; ?>" 
                                   class="btn btn-sm btn-danger js-confirm"
                                   data-title="Reject Request"
                                   data-message="Are you sure you want to reject this request?">
                                    <i class="fas fa-times"></i> Reject
                                </a>
                            <?php elseif ($request['status'] == 'approved' && $request['request_type'] == 'book' && !$request['collected']): ?>
                                <a href="?id=<?php echo $request['id']; ?>&action=collected&type=book" 
                                   class="btn btn-sm btn-primary js-confirm" 
                                   data-title="Mark as Collected"
                                   data-message="Confirm the student has collected the book?">
                                    <i class="fas fa-hand-holding"></i> Hand over
                                </a>
                                <small class="text-muted d-block">
                                    Expires: <?php echo isset($request['expires_at']) ? date('M d, Y H:i', strtotime($request['expires_at'])) : 'N/A'; ?>
                                </small>
                            <?php else: ?>
                                <span class="text-muted">Already processed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No requests found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
// Include footer
include_once '../includes/footer.php';
?>
