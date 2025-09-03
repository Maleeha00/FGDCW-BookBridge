<?php
include_once '../includes/header.php';

// Check if user is student or faculty
if ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'faculty') {
    header('Location: ../index.php');
    exit();
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle request cancellation
if (isset($_POST['cancel_request'])) {
    $requestId = (int)$_POST['request_id'];
    
    // Verify the request belongs to the current user
    $stmt = $conn->prepare("SELECT id FROM book_requests WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->bind_param("ii", $requestId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE book_requests SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $requestId);
        
        if ($stmt->execute()) {
            $message = "Request cancelled successfully.";
            $messageType = "success";
        } else {
            $message = "Error cancelling request.";
            $messageType = "danger";
        }
    } else {
        $message = "Request not found or cannot be cancelled.";
        $messageType = "danger";
    }
}

// Handle reservation request cancellation
if (isset($_POST['cancel_reservation_request'])) {
    $requestId = (int)$_POST['request_id'];
    
    // Verify the request belongs to the current user
    $stmt = $conn->prepare("SELECT id FROM reservation_requests WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->bind_param("ii", $requestId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE reservation_requests SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param("i", $requestId);
        
        if ($stmt->execute()) {
            $message = "Reservation request cancelled successfully.";
            $messageType = "success";
        } else {
            $message = "Error cancelling reservation request.";
            $messageType = "danger";
        }
    } else {
        $message = "Reservation request not found or cannot be cancelled.";
        $messageType = "danger";
    }
}

// Get all book requests for the user
$sql = "
    SELECT br.*, b.book_name, b.author, b.book_no, b.available_quantity, 'book' as request_type, br.collected, br.expires_at, br.approved_at, br.collected_at
    FROM book_requests br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = ?
    ORDER BY br.request_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$bookRequests = [];
while ($row = $result->fetch_assoc()) {
    $bookRequests[] = $row;
}

// Get all reservation requests for the user
$sql = "
    SELECT rr.*, b.book_name, b.author, b.book_no, b.available_quantity, 'reservation' as request_type
    FROM reservation_requests rr
    JOIN books b ON rr.book_id = b.id
    WHERE rr.user_id = ?
    ORDER BY rr.request_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$reservationRequests = [];
while ($row = $result->fetch_assoc()) {
    $reservationRequests[] = $row;
}

// Combine all requests
$allRequests = array_merge($bookRequests, $reservationRequests);
usort($allRequests, function($a, $b) {
    return strtotime($b['request_date']) - strtotime($a['request_date']);
});

// Separate requests by status and type
$pendingBookRequests = array_filter($bookRequests, function($req) { return $req['status'] == 'pending'; });
$approvedBookRequests = array_filter($bookRequests, function($req) { return $req['status'] == 'approved'; });
$rejectedBookRequests = array_filter($bookRequests, function($req) { return $req['status'] == 'rejected'; });
$cancelledBookRequests = array_filter($bookRequests, function($req) { return $req['status'] == 'cancelled'; });
$expiredBookRequests = array_filter($bookRequests, function($req) { return $req['status'] == 'expired'; });

$pendingReservationRequests = array_filter($reservationRequests, function($req) { return $req['status'] == 'pending'; });
$approvedReservationRequests = array_filter($reservationRequests, function($req) { return $req['status'] == 'approved'; });
$rejectedReservationRequests = array_filter($reservationRequests, function($req) { return $req['status'] == 'rejected'; });
$cancelledReservationRequests = array_filter($reservationRequests, function($req) { return $req['status'] == 'cancelled'; });
?>

<div class="container">
    <h1 class="page-book_name">My Requests</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Quick Stats -->
    <div class="stats-container mb-4">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-number"><?php echo count($bookRequests); ?></div>
                <div class="stat-label">Book Requests</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-number"><?php echo count($reservationRequests); ?></div>
                <div class="stat-label">Reservation Requests</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-number"><?php echo count($pendingBookRequests) + count($pendingReservationRequests); ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-number"><?php echo count($approvedBookRequests) + count($approvedReservationRequests); ?></div>
                <div class="stat-label">Approved</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-number"><?php echo count($expiredBookRequests); ?></div>
                <div class="stat-label">Expired</div>
            </div>
        </div>
    </div>

    <!-- Pending Requests -->
    <?php if (count($pendingBookRequests) > 0 || count($pendingReservationRequests) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3>Pending Requests</h3>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Book Details</th>
                            <th>Request Date</th>
                            <th>Notes</th>
                            <th>Availability</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $pendingRequests = array_merge($pendingBookRequests, $pendingReservationRequests);
                        usort($pendingRequests, function($a, $b) {
                            return strtotime($b['request_date']) - strtotime($a['request_date']);
                        });
                        
                        foreach ($pendingRequests as $request): 
                        ?>
                            <tr>
                                <td>
                                    <?php if ($request['request_type'] == 'book'): ?>
                                        <span class="badge badge-primary">
                                            Book Request
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">
                                            Reservation Request
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($request['book_name']); ?></strong><br>
                                    <small class="text-muted">by <?php echo htmlspecialchars($request['author']); ?></small><br>
                                    <?php if (!empty($request['book_no'])): ?>
                                        <small class="text-muted">Book No: <?php echo htmlspecialchars($request['book_no']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($request['request_date'])); ?></td>
                                <td>
                                    <?php if (!empty($request['notes'])): ?>
                                        <?php echo htmlspecialchars($request['notes']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No notes</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($request['available_quantity'] > 0): ?>
                                        <span class="badge badge-success"><?php echo $request['available_quantity']; ?> available</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Not available</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <?php if ($request['request_type'] == 'book'): ?>
                                            <button type="submit" name="cancel_request" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Are you sure you want to cancel this book request?')">
                                                Cancel
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" name="cancel_reservation_request" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Are you sure you want to cancel this reservation request?')">
                                                Cancel
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Approved Requests -->
    <?php if (count($approvedBookRequests) > 0 || count($approvedReservationRequests) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3>Approved Requests</h3>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Book Details</th>
                            <th>Request Date</th>
                            <th>Approved Date</th>
                            <th>Notes</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $approvedRequests = array_merge($approvedBookRequests, $approvedReservationRequests);
                        usort($approvedRequests, function($a, $b) {
                            return strtotime($b['request_date']) - strtotime($a['request_date']);
                        });
                        
                        foreach ($approvedRequests as $request): 
                        ?>
                            <tr>
                                <td>
                                    <?php if ($request['request_type'] == 'book'): ?>
                                        <span class="badge badge-primary">
                                            Book Request
                                        </span>
                                        <?php if ($request['collected']): ?>
                                            <br><span class="badge badge-success">Collected</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge badge-warning">
                                            Reservation Request
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($request['book_name']); ?></strong><br>
                                    <small class="text-muted">by <?php echo htmlspecialchars($request['author']); ?></small><br>
                                    <?php if (!empty($request['book_no'])): ?>
                                        <small class="text-muted">Book No: <?php echo htmlspecialchars($request['book_no']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($request['request_date'])); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($request['approved_at'])); ?></td>
                                <td>
                                    <?php if (!empty($request['notes'])): ?>
                                        <?php echo htmlspecialchars($request['notes']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No notes</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-success">Approved</span><br>
                                    <?php if ($request['request_type'] == 'book'): ?>
                                        <?php if ($request['collected']): ?>
                                            <small class="text-muted">Collected on: <?php echo date('M d, Y H:i', strtotime($request['collected_at'])); ?></small>
                                        <?php else: ?>
                                            <?php if (isset($request['expires_at'])): ?>
                                                <small class="text-warning">
                                                    <i class="fas fa-clock"></i> 
                                                    Expires: <?php echo date('M d, Y H:i', strtotime($request['expires_at'])); ?>
                                                </small>
                                                <?php if (strtotime($request['expires_at']) < time()): ?>
                                                    <br><small class="text-danger"><strong>EXPIRED - Please submit new request</strong></small>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <small class="text-muted">Added to reservation queue</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Rejected/Cancelled Requests -->
    <?php if (count($rejectedBookRequests) > 0 || count($rejectedReservationRequests) > 0 || count($cancelledBookRequests) > 0 || count($cancelledReservationRequests) > 0 || count($expiredBookRequests) > 0): ?>
    <div class="card">
        <div class="card-header">
            <h3>Rejected, Cancelled & Expired Requests</h3>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Book Details</th>
                            <th>Request Date</th>
                            <th>Notes</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rejectedCancelledRequests = array_merge(
                            $rejectedBookRequests, 
                            $rejectedReservationRequests, 
                            $cancelledBookRequests, 
                            $cancelledReservationRequests,
                            $expiredBookRequests
                        );
                        usort($rejectedCancelledRequests, function($a, $b) {
                            return strtotime($b['request_date']) - strtotime($a['request_date']);
                        });
                        
                        foreach ($rejectedCancelledRequests as $request): 
                        ?>
                            <tr>
                                <td>
                                    <?php if ($request['request_type'] == 'book'): ?>
                                        <span class="badge badge-primary">
                                            Book Request
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">
                                            Reservation Request
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($request['book_name']); ?></strong><br>
                                    <small class="text-muted">by <?php echo htmlspecialchars($request['author']); ?></small><br>
                                    <?php if (!empty($request['book_no'])): ?>
                                        <small class="text-muted">Book No: <?php echo htmlspecialchars($request['book_no']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($request['request_date'])); ?></td>
                                <td>
                                    <?php if (!empty($request['notes'])): ?>
                                        <?php echo htmlspecialchars($request['notes']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No notes</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($request['status'] == 'rejected'): ?>
                                        <span class="badge badge-danger">Rejected</span>
                                    <?php elseif ($request['status'] == 'expired'): ?>
                                        <span class="badge badge-secondary">Expired</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (count($allRequests) == 0): ?>
        <div class="card">
            <div class="card-body text-center">
                <h3>No Requests</h3>
                <p class="text-muted">You haven't made any book or reservation requests yet.</p>
                <a href="catalog.php" class="btn btn-primary">
                    Browse Books
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.stat-card {
    background: var(--white);
    padding: 20px;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    display: flex;
    align-items: center;
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}

.stat-info {
    flex: 1;
}

.stat-number {
    font-size: 1.8em;
    font-weight: 700;
    color: var(--primary-color);
    line-height: 1;
    margin-bottom: 5px;
}

.stat-label {
    color: var(--text-light);
    font-size: 0.9em;
}
</style>

<?php include_once '../includes/footer.php'; ?>