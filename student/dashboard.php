<?php
include_once '../includes/header.php';
if ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'faculty') {
    header('Location: ../index.php');
    exit();
}

$userId = $_SESSION['user_id'];

include_once '../includes/config.php';
autoExpireBookRequests($conn);

$currentlyIssued = [];
$sql = "
    SELECT ib.*, b.book_name, b.author
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    WHERE ib.user_id = ? AND (ib.status = 'issued' OR ib.status = 'overdue')
    ORDER BY ib.return_date ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $currentlyIssued[] = $row;
    }
}
$pendingRequests = [];
$sql = "
    SELECT br.*, b.book_name
    FROM reservation_requests br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = ? AND br.status = 'pending'
    ORDER BY br.request_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pendingRequests[] = $row;
    }
}

$awaitingCollection = [];
$sql = "
    SELECT br.*, b.book_name
    FROM reservation_requests br
    JOIN books b ON br.book_id = b.id
    WHERE br.user_id = ? AND br.status = 'approved' AND br.collected = 0
    ORDER BY br.approved_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $awaitingCollection[] = $row;
    }
}
$pendingFines = 0;
$sql = "
    SELECT SUM(f.amount) as total 
    FROM fines f 
    JOIN users u ON f.user_id = u.id 
    WHERE f.user_id = ? AND f.status = 'pending' AND u.role != 'faculty'
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $pendingFines = $row['total'] ?: 0;
}

$notifications = [];
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
?>

<div class="container">
    <h1 class="page-book_name">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>

    <div class="stats-container" >
        

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-info">
                <div class="stat-number">PKR <?php echo number_format($pendingFines, 2); ?></div>
                <div class="stat-label">Pending Fines</div>
            </div>
        </div>

        
    </div>

    <?php if (count($awaitingCollection) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-clock text-warning"></i> Approved – Awaiting Collection</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Important:</strong> You have <?php echo count($awaitingCollection); ?> approved book request(s) waiting for collection. 
                Please visit the library within the expiration time to collect your books.
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Book</th>
                            <th>Approved On</th>
                            <th>Expires On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($awaitingCollection as $request): ?>
                            <tr class="<?php echo strtotime($request['expires_at']) < time() ? 'table-danger' : ''; ?>">
                                <td><?php echo htmlspecialchars($request['book_name']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($request['approved_at'])); ?></td>
                                <td>
                                    <?php 
                                    $expiresAt = strtotime($request['expires_at']);
                                    $now = time();
                                    echo date('M d, Y H:i', $expiresAt);
                                    
                                    if ($expiresAt < $now) {
                                        echo '<br><small class="text-danger"><strong>EXPIRED</strong></small>';
                                    } else {
                                        $hoursLeft = ceil(($expiresAt - $now) / 3600);
                                        echo '<br><small class="text-info">' . $hoursLeft . ' hours left</small>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($expiresAt < $now): ?>
                                        <span class="badge badge-danger">Expired</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Approved – Collect from Library</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="text-center">
                <a href="requests.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All Requests
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
       
        <div class="dashboard-col">
            <div class="card">
                <div class="card-header">
                    <h3>Currently Borrowed Books</h3>
                    <?php if (count($currentlyIssued) > 0): ?>
                        <a href="returns.php" class="btn btn-sm btn-primary">View All</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($currentlyIssued) > 0): ?>
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Book</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($currentlyIssued, 0, 3) as $book): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($book['return_date'])); ?></td>
                                            <td>
                                                <?php 
                                                $today = new DateTime();
                                                $dueDate = new DateTime($book['return_date']);
                                                if ($today > $dueDate) {
                                                    echo '<span class="badge badge-danger">Overdue</span>';
                                                } else {
                                                    echo '<span class="badge badge-primary">Issued</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">You haven't borrowed any books yet.</p>
                        <div class="text-center">
                            <a href="catalog.php" class="btn btn-primary">Browse Books</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="dashboard-col">
            <div class="card">
                <div class="card-header">
                    <h3>Recent Notifications</h3>
                    <?php if (count($notifications) > 0): ?>
                        <a href="notifications.php" class="btn btn-sm btn-primary">View All</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (count($notifications) > 0): ?>
                        <div class="notification-list">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                                    <div class="notification-message">
                                        <?php echo htmlspecialchars($notification['message']); ?>
                                    </div>
                                    <div class="notification-time">
                                        <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No new notifications.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    
</div>

<style>




.notification-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.notification-item {
    padding: 10px;
    border-radius: var(--border-radius);
    background: var(--gray-100);
    transition: var(--transition);
}

.notification-item.unread {
    background: rgba(13, 71, 161, 0.1);
    border-left: 3px solid var(--primary-color);
}

.notification-message {
    margin-bottom: 5px;
}

.notification-time {
    font-size: 0.8em;
    color: var(--text-light);
}

.stats-container {
    margin-top:30px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
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

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(13, 71, 161, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 1.5em;
    color: var(--primary-color);
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