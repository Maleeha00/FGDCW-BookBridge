<?php
include_once '../includes/header.php';

if ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'faculty') {
    header('Location: ../index.php');
    exit();
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';


if (isset($_POST['cancel_reservation'])) {
    $reservationId = (int)$_POST['reservation_id'];
    
    $result = cancelBookReservation($conn, $reservationId, $userId);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'danger';
}


cleanExpiredReservations($conn);
$reservations = getUserReservations($conn, $userId);

$activeReservations = array_filter($reservations, function($res) { return $res['status'] == 'active'; });
$fulfilledReservations = array_filter($reservations, function($res) { return $res['status'] == 'fulfilled'; });
$expiredReservations = array_filter($reservations, function($res) { return $res['status'] == 'expired'; });
$cancelledReservations = array_filter($reservations, function($res) { return $res['status'] == 'cancelled'; });
?>

<div class="container">
    <h1 class="page-book_name">My Book Reservations</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    
    <div class="alert alert-info mb-4">
        <i class="fas fa-magic"></i>
        <strong>Auto-Issue Feature:</strong> When a reserved book becomes available, it will be automatically issued to you! 
        You'll receive a notification and can collect the book from the library.
    </div>

    <div class="stats-container mb-4">
        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-number"><?php echo count($activeReservations); ?></div>
                <div class="stat-label">Active Reservations</div>
            </div>
        </div>

        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-number"><?php echo count($fulfilledReservations); ?></div>
                <div class="stat-label">Auto-Issued Books</div>
            </div>
        </div>

        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-number"><?php echo count($expiredReservations); ?></div>
                <div class="stat-label">Expired</div>
            </div>
        </div>

        <div class="stat-card">
            
            <div class="stat-info">
                <div class="stat-number"><?php echo count($cancelledReservations); ?></div>
                <div class="stat-label">Cancelled</div>
            </div>
        </div>
    </div>

    <?php if (count($activeReservations) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-clock text-warning"></i> Active Reservations</h3>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Book Details</th>
                            <th>Reserved On</th>
                            <th>Queue Position</th>
                            <th>Expires On</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeReservations as $reservation): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($reservation['book_name']); ?></strong><br>
                                    <small class="text-muted">by <?php echo htmlspecialchars($reservation['author']); ?></small><br>
                                    <small class="text-info">
                                        <?php echo $reservation['available_quantity']; ?> available
                                    </small>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($reservation['reservation_date'])); ?></td>
                                <td>
                                    <span class="badge badge-info">
                                        #<?php echo $reservation['priority_number']; ?> in queue
                                    </span>
                                    <?php if ($reservation['priority_number'] == 1): ?>
                                        <br><small class="text-success"><i class="fas fa-magic"></i> Next for auto-issue!</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php 
                                    $expiresAt = new DateTime($reservation['expires_at']);
                                    $now = new DateTime();
                                    $timeLeft = $now->diff($expiresAt);
                                    
                                    if ($expiresAt > $now) {
                                        echo date('M d, Y', strtotime($reservation['expires_at']));
                                        echo '<br><small class="text-muted">';
                                        if ($timeLeft->days > 0) {
                                            echo $timeLeft->days . ' days left';
                                        } else {
                                            echo $timeLeft->h . ' hours left';
                                        }
                                        echo '</small>';
                                    } else {
                                        echo '<span class="text-danger">Expired</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($reservation['notes'])): ?>
                                        <?php echo htmlspecialchars($reservation['notes']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No notes</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                        <button type="submit" name="cancel_reservation" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Are you sure you want to cancel this reservation?')">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
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
    <?php if (count($fulfilledReservations) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-magic text-success"></i> Auto-Issued Books</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <strong>Success!</strong> These books were automatically issued to you when they became available. 
                Please collect them from the library.
            </div>
            
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Book Details</th>
                            <th>Reserved On</th>
                            <th>Auto-Issued On</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fulfilledReservations as $reservation): ?>
                            <tr class="table-success">
                                <td>
                                    <strong><?php echo htmlspecialchars($reservation['book_name']); ?></strong><br>
                                    <small class="text-muted">by <?php echo htmlspecialchars($reservation['author']); ?></small>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($reservation['reservation_date'])); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($reservation['notified_at'])); ?></td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fas fa-magic"></i> Auto-Issued
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($reservation['notes'])): ?>
                                        <?php echo htmlspecialchars($reservation['notes']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No notes</span>
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
    <?php if (count($expiredReservations) > 0 || count($cancelledReservations) > 0): ?>
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-history text-muted"></i> Reservation History</h3>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Book Details</th>
                            <th>Reserved On</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_merge($expiredReservations, $cancelledReservations) as $reservation): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($reservation['book_name']); ?></strong><br>
                                    <small class="text-muted">by <?php echo htmlspecialchars($reservation['author']); ?></small>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($reservation['reservation_date'])); ?></td>
                                <td>
                                    <?php if ($reservation['status'] == 'expired'): ?>
                                        <span class="badge badge-warning">Expired</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Cancelled</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($reservation['notes'])): ?>
                                        <?php echo htmlspecialchars($reservation['notes']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No notes</span>
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

    <?php if (count($reservations) == 0): ?>
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-bookmark fa-3x text-muted mb-3"></i>
                <h3>No Reservations</h3>
                <p class="text-muted">You haven't made any book reservations yet. Reserve books when they're unavailable to get them automatically issued when available!</p>
                <a href="books.php" class="btn btn-primary">
                    <i class="fas fa-search"></i> Browse Books
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
    text-align: center;
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
}


.stat-info {
    
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

.table-success {
    background-color: rgba(40, 167, 69, 0.1);
}

.table-success:hover {
    background-color: rgba(40, 167, 69, 0.15);
}

.alert-info {
    background-color: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}
</style>

<?php include_once '../includes/footer.php'; ?>
