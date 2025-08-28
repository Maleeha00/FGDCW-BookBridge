<?php
// Include header
include_once '../includes/header.php';

// Check if user is a librarian
checkUserRole('librarian');

// Auto-expire book requests that haven't been collected
autoExpireBookRequests($conn);

// Auto-process any pending reservations when books become available
// This runs every time the dashboard loads to ensure reservations are processed
$autoProcessSql = "
    SELECT DISTINCT b.id as book_id
    FROM books b
    INNER JOIN book_reservations br ON b.id = br.book_id
    WHERE b.available_quantity > 0 
    AND br.status = 'active'
";
$autoProcessResult = $conn->query($autoProcessSql);

if ($autoProcessResult && $autoProcessResult->num_rows > 0) {
    while ($row = $autoProcessResult->fetch_assoc()) {
        autoProcessReservationsOnAvailability($conn, $row['book_id']);
    }
}

// Get dashboard statistics
$totalBooks = getTotalBooks($conn);
$issuedBooks = getIssuedBooks($conn);
$totalUsers = getTotalUsers($conn);
$pendingRequests = getPendingRequests($conn); // Now includes both book and reservation requests
$totalFines = getTotalUnpaidFines($conn);

// Get recent activities (limited to 5 with scroll)
$recentActivities = [];
$sql = "
    (SELECT 'book_issued' as type, b.book_name as book_name, u.name as user_name, ib.issue_date as date
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN users u ON ib.user_id = u.id
    ORDER BY ib.issue_date DESC
    LIMIT 10)
    
    UNION
    
    (SELECT 'book_returned' as type, b.book_name as book_name, u.name as user_name, ib.actual_return_date as date
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN users u ON ib.user_id = u.id
    WHERE ib.status = 'returned'
    ORDER BY ib.actual_return_date DESC
    LIMIT 10)
    
    UNION
    
    (SELECT 'fine_paid' as type, CONCAT('Fine for ', b.book_name) as book_name, u.name as user_name, p.payment_date as date
    FROM payments p
    JOIN fines f ON p.fine_id = f.id
    JOIN issued_books ib ON f.issued_book_id = ib.id
    JOIN books b ON ib.book_id = b.id
    JOIN users u ON p.user_id = u.id
    ORDER BY p.payment_date DESC
    LIMIT 10)
    
    ORDER BY date DESC
    LIMIT 20
";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentActivities[] = $row;
    }
}

// Get books due for return today
$today = date('Y-m-d');
$dueTodayBooks = [];
$sql = "
    SELECT ib.id, b.book_name, u.name as user_name, ib.issue_date, ib.return_date
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN users u ON ib.user_id = u.id
    WHERE ib.return_date = ? AND ib.status = 'issued'
    ORDER BY ib.return_date ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dueTodayBooks[] = $row;
    }
}

// Get overdue books
$overdueBooks = [];
$sql = "
    SELECT ib.id, b.book_name, u.name as user_name, ib.issue_date, ib.return_date, 
           DATEDIFF(CURRENT_DATE, ib.return_date) as days_overdue
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN users u ON ib.user_id = u.id
    WHERE ib.return_date < CURRENT_DATE AND ib.status = 'issued'
    ORDER BY ib.return_date ASC
    LIMIT 5
";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $overdueBooks[] = $row;
    }
}

// Get pending requests (both book and reservation requests)
$pendingBookRequests = [];
$pendingReservationRequests = [];

// Book requests
$sql = "
    SELECT br.id, b.book_name, u.name as user_name, br.request_date, 'book' as request_type
    FROM book_requests br
    JOIN books b ON br.book_id = b.id
    JOIN users u ON br.user_id = u.id
    WHERE br.status = 'pending'
    ORDER BY br.request_date DESC
    LIMIT 5
";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pendingBookRequests[] = $row;
    }
}

// Reservation requests
$sql = "
    SELECT rr.id, b.book_name, u.name as user_name, rr.request_date, 'reservation' as request_type
    FROM reservation_requests rr
    JOIN books b ON rr.book_id = b.id
    JOIN users u ON rr.user_id = u.id
    WHERE rr.status = 'pending'
    ORDER BY rr.request_date DESC
    LIMIT 5
";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pendingReservationRequests[] = $row;
    }
}

// Combine pending requests
$allPendingRequests = array_merge($pendingBookRequests, $pendingReservationRequests);
usort($allPendingRequests, function($a, $b) {
    return strtotime($b['request_date']) - strtotime($a['request_date']);
});
$allPendingRequests = array_slice($allPendingRequests, 0, 5);

// Get active reservations summary
$activeReservations = [];
$sql = "
    SELECT br.id, b.book_name, u.name as user_name, br.priority_number, br.reservation_date
    FROM book_reservations br
    JOIN books b ON br.book_id = b.id
    JOIN users u ON br.user_id = u.id
    WHERE br.status = 'active'
    ORDER BY br.reservation_date DESC
    LIMIT 5
";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $activeReservations[] = $row;
    }
}
//automated notifications
// Auto-send overdue notifications (only one per overdue book per user)

// 1. Find all issued books that are overdue
$today = date('Y-m-d');
$sqlOverdue = "
    SELECT ib.id AS issue_id, ib.user_id, b.book_name
    FROM issued_books ib
    INNER JOIN books b ON ib.book_id = b.id
    WHERE ib.status = 'issued'
      AND ib.return_date < ?
";
$stmt = $conn->prepare($sqlOverdue);
$stmt->bind_param("s", $today);
$stmt->execute();
$result = $stmt->get_result();

// 2. For each overdue book, check if notification already sent
while ($row = $result->fetch_assoc()) {
    $userId = $row['user_id'];
    $book_name = $row['book_name'];

    // Check if we already sent a notification for this issue_id
    $checkSql = "
        SELECT id FROM notifications
        WHERE user_id = ? 
          AND message LIKE ?
          AND DATE(created_at) = CURDATE()
        LIMIT 1
    ";
    $likeMsg = "%$book_name%"; // partial match
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("is", $userId, $likeMsg);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows === 0) {
        // Send notification
        $message = "Your borrowed book '$book_name' is overdue. Please return it as soon as possible.";
        sendNotification($conn, $userId, $message);
    }

    $checkStmt->close();
}
$stmt->close();
?>

<h1 class="page-title">Librarian Dashboard</h1>

<!-- Display Messages -->
<?php if (isset($_SESSION['dashboard_message'])): ?>
    <div class="alert alert-<?php echo $_SESSION['dashboard_message_type']; ?> alert-dismissible fade show" role="alert">
        <i class="fas fa-<?php echo $_SESSION['dashboard_message_type'] == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
        <?php echo $_SESSION['dashboard_message']; ?>
        <button type="button" class="btn-close" onclick="this.parentElement.remove()" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php 
    // Clear the message after displaying
    unset($_SESSION['dashboard_message']);
    unset($_SESSION['dashboard_message_type']);
    ?>
<?php endif; ?>

<!-- 📷 Stats with Background Images -->
<div class="dashboard-image-section">
    <div class="overlay">
        <div class="stat-overlay books">
            <h2>Total Books: <?php echo $totalBooks; ?></h2>
            <!--<p><?php echo $totalBooks; ?></p>-->
        </div>
        <div class="stat-overlay issued">
            <h2>Issued Books: <?php echo $issuedBooks; ?></h2>
            <!--<p><?php echo $issuedBooks; ?></p>-->
        </div>
        <div class="stat-overlay users">
            <h2>Total Users: <?php echo $totalUsers; ?></h2>
            <!--<p><?php echo $totalUsers; ?></p>-->
        </div>
        <div class="stat-overlay requests">
            <h2>Pending Requests: <?php echo $pendingRequests; ?></h2>
            <!--<p><?php echo $pendingRequests; ?></p>-->
        </div>
        <div class="stat-overlay fines">
            <h2>Pending Fines: Rs <?php echo number_format($totalFines, 2); ?></h2>
            <!--<p>Rs <?php echo number_format($totalFines, 2); ?></p>-->
        </div>
    </div>
</div>

<!-- User Approval Section - TOP PRIORITY -->
<?php
// Get pending user approvals
$pendingUsers = [];
$sql = "SELECT * FROM users WHERE approval_status = 'pending' ORDER BY id DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pendingUsers[] = $row;
    }
}
?>

<?php if (count($pendingUsers) > 0): ?>
<div class="dashboard-row">
    <div class="dashboard-col">
        <div class="card priority-card">
            <div class="card-header d-flex justify-between align-center">
                <h3><i class="fas fa-user-clock text-warning"></i> Pending User Approvals</h3>
                <span class="badge badge-warning"><?php echo count($pendingUsers); ?></span>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Unique ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Phone</th>
                                <th>Registration Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingUsers as $user): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($user['unique_id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['role'] == 'student' ? 'primary' : 'info'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['department']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($user['created_at'] ?? 'now')); ?></td>
                                    <td>
                                        <a href="approve_user.php?id=<?php echo $user['id']; ?>&action=approve" 
                                           class="btn btn-xs btn-success"
                                           onclick="return confirm('Approve this user?')">
                                            <i class="fas fa-check"></i> Approve
                                        </a>
                                        <a href="approve_user.php?id=<?php echo $user['id']; ?>&action=reject" 
                                           class="btn btn-xs btn-danger"
                                           onclick="return confirm('Reject this user?')">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="dashboard-row">
    <!-- Recent Activity with Scroll -->
    <div class="dashboard-col">
        <div class="recent-activity">
            <div class="activity-header">
                <h3>Recent Activity</h3>
                <span class="activity-count"><?php echo count($recentActivities); ?> activities</span>
            </div>
            <div class="activity-body">
                <div class="activity-scroll-container">
                    <ul class="activity-list">
                        <?php if (count($recentActivities) > 0): ?>
                            <?php foreach ($recentActivities as $activity): ?>
                                <li class="activity-item">
                                    <div class="activity-icon">
                                        <?php if ($activity['type'] == 'book_issued'): ?>
                                            <i class="fas fa-hand-holding" style="color: #4caf50;"></i>
                                        <?php elseif ($activity['type'] == 'book_returned'): ?>
                                            <i class="fas fa-undo" style="color: #2196f3;"></i>
                                        <?php elseif ($activity['type'] == 'fine_paid'): ?>
                                            <i class="fas fa-money-bill-wave" style="color: #ff9800;"></i>
                                        <?php else: ?>
                                            <i class="fas fa-info-circle"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="activity-info">
                                        <h4 class="activity-title">
                                            <?php 
                                            if ($activity['type'] == 'book_issued') {
                                                echo 'Book Issued: ' . htmlspecialchars($activity['book_name']);
                                            } elseif ($activity['type'] == 'book_returned') {
                                                echo 'Book Returned: ' . htmlspecialchars($activity['book_name']);
                                            } elseif ($activity['type'] == 'fine_paid') {
                                                echo htmlspecialchars($activity['book_name']);
                                            }
                                            ?>
                                        </h4>
                                        <div class="activity-meta">
                                            <span class="activity-time">
                                                <i class="fas fa-clock"></i>
                                                <?php echo date('M d, Y H:i', strtotime($activity['date'])); ?>
                                            </span>
                                            <span class="activity-user">
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($activity['user_name']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="activity-item">
                                <div class="activity-info">
                                    <h4 class="activity-title">No recent activity</h4>
                                    <p class="text-muted">Activities will appear here as they happen</p>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Requests (NEW SECTION) -->
    <div class="dashboard-col">
        <div class="card">
            <div class="card-header d-flex justify-between align-center">
                <h3>Pending Requests</h3>
                <div class="header-badges">
                    <span class="badge badge-warning"><?php echo count($allPendingRequests); ?></span>
                    <a href="requests.php" class="btn btn-sm btn-primary">View All</a>
                </div>
            </div>
            <div class="card-body">
                <?php if (count($allPendingRequests) > 0): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Book</th>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allPendingRequests as $request): ?>
                                    <tr>
                                        <td>
                                            <?php if ($request['request_type'] == 'book'): ?>
                                                <span class="badge badge-primary">Book</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning">Reserve</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($request['book_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['user_name']); ?></td>
                                        <td><?php echo date('M d', strtotime($request['request_date'])); ?></td>
                                        <td>
                                            <a href="requests.php?id=<?php echo $request['id']; ?>&action=approve&type=<?php echo $request['request_type']; ?>" 
                                               class="btn btn-xs btn-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="requests.php?id=<?php echo $request['id']; ?>&action=reject&type=<?php echo $request['request_type']; ?>" 
                                               class="btn btn-xs btn-danger">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-check fa-3x"></i>
                        <p>No pending requests</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-row">
    <!-- Active Reservations -->
    
    
    <!-- Books Due Today -->
    <div class="dashboard-col">
        <div class="card">
            <div class="card-header">
                <h3>Books Due Today</h3>
                <span class="badge badge-warning"><?php echo count($dueTodayBooks); ?></span>
            </div>
            <div class="card-body">
                <?php if (count($dueTodayBooks) > 0): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>User</th>
                                    <th>Issue Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dueTodayBooks as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                                        <td><?php echo htmlspecialchars($book['user_name']); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($book['issue_date'])); ?></td>
                                        <td>
                                            <a href="returns.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-undo"></i> Process Return
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-check fa-3x"></i>
                        <p>No books due today</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-row">
    <!-- Overdue Books -->
    <div class="dashboard-col">
        <div class="card">
            <div class="card-header">
                <h3>Overdue Books</h3>
                <span class="badge badge-danger"><?php echo count($overdueBooks); ?></span>
            </div>
            <div class="card-body">
                <?php if (count($overdueBooks) > 0): ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>User</th>
                                    <th>Days Overdue</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdueBooks as $book): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                                        <td><?php echo htmlspecialchars($book['user_name']); ?></td>
                                        <td>
                                            <span class="badge badge-danger"><?php echo $book['days_overdue']; ?> days</span>
                                        </td>
                                        <td>
                                            <a href="returns.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-undo"></i> Return
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($overdueBooks) >= 5): ?>
                        <div class="text-center mt-3">
                            <a href="returns.php?status=overdue" class="btn btn-primary">View All Overdue Books</a>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle fa-3x"></i>
                        <p>No overdue books</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<style>
    .dashboard-image-section {
    padding: 50px;
    background-color: #f0f0f0;
}

.overlay {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}
.stat-overlay h2 {
    font-size: 20px;
    margin-bottom: 0px;
    color: saddlebrown; /* 🔁 Change this to your desired color */
}
.stat-overlay p {
    font-size: 28px; /* bara number */
    font-weight: bold; /* thoda prominent */
    margin: 5px 0 0;
}

.stat-overlay {
    width: 250px;
    height: 200px;
    background-size: cover;
    background-color:white;
    background-position: cover;
    border-radius: 15px;
    color: black;
    display: flex;
    flex-direction: column;
    justify-content: top;
    align-items: center;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.7);
    font-weight: bold;
    box-shadow: 0 0 10px rgba(0,0,0,0.3);
    padding: 10px;
    text-align: top;
}

.books {
    background-image: url('dash_images/2books..jpg');
    background-size: contain;     /* 🔁 Change from 'cover' to 'contain' */
    background-repeat: no-repeat; /* Prevent repetition */
    background-position: center;
    background-color: white; 
}

.issued {
    background-image: url('dash_images/2issued..jpg');
    background-size: contain;     /* 🔁 Change from 'cover' to 'contain' */
    background-repeat: no-repeat; /* Prevent repetition */
    background-position: center;
    background-color: white; 
}

.users {
    background-image: url('dash_images/2users..jpg');
    background-size: contain;     /* 🔁 Change from 'cover' to 'contain' */
    background-repeat: no-repeat; /* Prevent repetition */
    background-position: center;
    background-color: white; 
}

.requests {
    background-image: url('dash_images/2requests.jpg');
    background-size: contain;     /* 🔁 Change from 'cover' to 'contain' */
    background-repeat: no-repeat; /* Prevent repetition */
    background-position: center;
    background-color: white; 
}

.fines {
    background-image: url('dash_images/2fines..jpg');
    background-size: contain;     /* 🔁 Change from 'cover' to 'contain' */
    background-repeat: no-repeat; /* Prevent repetition */
    background-position: center;
    background-color: white; 
}*/

/* Alert Styles */
.alert {
    margin-bottom: 20px;
    padding: 15px 20px;
    border-radius: 8px;
    border: 1px solid transparent;
    position: relative;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeaa7;
    color: #856404;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.alert i {
    margin-right: 8px;
}

.alert-dismissible {
    padding-right: 50px;
}

.btn-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    color: inherit;
    opacity: 0.7;
}

.btn-close:hover {
    opacity: 1;
}

/* Priority Card Styles */
.priority-card {
    border: 2px solid #ffc107;
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
    background: linear-gradient(135deg, #fff9c4 0%, #ffffff 100%);
    margin-bottom: 30px;
}

.priority-card .card-header {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
    color: #fff;
    border-bottom: 2px solid #ff8f00;
}

.priority-card .card-header h3 {
    color: #fff;
    margin: 0;
}

.priority-card .card-header .badge {
    background-color: #fff;
    color: #ffc107;
    font-weight: bold;
}

.activity-scroll-container {
    max-height: 400px;
    overflow-y: auto;
    padding-right: 5px;
}

.activity-scroll-container::-webkit-scrollbar {
    width: 6px;
}

.activity-scroll-container::-webkit-scrollbar-track {
    background: var(--gray-200);
    border-radius: 3px;
}

.activity-scroll-container::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 3px;
}

.activity-scroll-container::-webkit-scrollbar-thumb:hover {
    background: var(--primary-dark);
}

.activity-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.activity-count {
    font-size: 0.9em;
    color: var(--text-light);
}

.activity-meta {
    display: flex;
    flex-direction: column;
    gap: 5px;
    font-size: 0.8em;
    color: var(--text-light);
}

.activity-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-light);
}

.empty-state i {
    margin-bottom: 15px;
    opacity: 0.5;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header-badges {
    display: flex;
    gap: 10px;
    align-items: center;
}

.alert-info {
    background-color: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}

.fa-magic {
    color: #ff9800;
}

.btn-xs {
    padding: 2px 6px;
    font-size: 0.75em;
    line-height: 1.2;
}

.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

@media (max-width: 768px) {
    .stats-container {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
    
    .header-badges {
        flex-direction: column;
        gap: 5px;
    }
}
</style>

<?php
// Include footer
include_once '../includes/footer.php';
?>