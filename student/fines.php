<?php
include_once '../includes/header.php';
if ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'faculty') {
    header('Location: ../index.php');
    exit();
}

$today = date('Y-m-d');

$overdueSql = "
    SELECT ib.id as issued_book_id, b.book_name, DATEDIFF(?, ib.return_date) as days_overdue
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    WHERE ib.user_id = ? 
      AND ib.status = 'issued' 
      AND ? > ib.return_date
      AND NOT EXISTS (
          SELECT 1 FROM fines f WHERE f.issued_book_id = ib.id
      )
";
$stmt_generate_fine = $conn->prepare($overdueSql);
$stmt_generate_fine->bind_param("sis", $today, $userId, $today);
$stmt_generate_fine->execute();
$overdue_result = $stmt_generate_fine->get_result();

while ($row = $overdue_result->fetch_assoc()) {
    $fineAmount = (float)$row['days_overdue'] * 10.00; 
    $reason = 'Late return of book: ' . $row['book_name'];
    
    $insertFine = $conn->prepare("
        INSERT INTO fines (issued_book_id, user_id, amount, reason, status)
        VALUES (?, ?, ?, ?, 'pending')
    ");
    $insertFine->bind_param("iids", $row['issued_book_id'], $userId, $fineAmount, $reason);
    $insertFine->execute();
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

$sql = "
    SELECT f.*, b.book_name, b.author, ib.return_date, ib.actual_return_date, ib.issue_date, u.role as user_role,
           p.payment_date, p.payment_method, p.receipt_number, p.transaction_id
    FROM fines f
    JOIN issued_books ib ON f.issued_book_id = ib.id
    JOIN books b ON ib.book_id = b.id
    JOIN users u ON f.user_id = u.id
    LEFT JOIN payments p ON f.id = p.fine_id
    WHERE f.user_id = ? AND u.role != 'faculty'
    ORDER BY f.created_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$fines = [];
while ($row = $result->fetch_assoc()) {
    $fines[] = $row;
}

$pendingFines = array_filter($fines, function($fine) { return $fine['status'] == 'pending'; });
$paidFines = array_filter($fines, function($fine) { return $fine['status'] == 'paid'; });

$totalPending = array_sum(array_column($pendingFines, 'amount'));
$totalPaid = array_sum(array_column($paidFines, 'amount'));
$totalFines = $totalPending + $totalPaid;
?>

<div class="container">
    <h1 class="page-book_name">My Fines</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <div class="stats-container mb-4">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-number">PKR <?php echo number_format($totalPending, 2); ?></div>
                <div class="stat-label">Pending Fines</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-number">PKR <?php echo number_format($totalPaid, 2); ?></div>
                <div class="stat-label">Paid Fines</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-number">PKR <?php echo number_format($totalFines, 2); ?></div>
                <div class="stat-label">Total Fines</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-number"><?php echo count($fines); ?></div>
                <div class="stat-label">Total Records</div>
            </div>
        </div>
    </div>

    <?php if (count($pendingFines) > 0): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h3><i class="fas fa-exclamation-triangle text-danger"></i> Pending Fines</h3>
            <div class="card-header-actions">
                <span class="badge badge-danger">Total: PKR <?php echo number_format($totalPending, 2); ?></span>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i>
                <strong>Important:</strong> Please pay your pending fines to continue borrowing books from the library.
            </div>
            
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Book Details</th>
                            <th>Issue Period</th>
                            <th>Late Return</th>
                            <th>Fine Amount</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingFines as $fine): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($fine['book_name']); ?></strong><br>
                                    <small class="text-muted">by <?php echo htmlspecialchars($fine['author']); ?></small>
                                </td>
                                <td>
                                    <strong>Issued:</strong> <?php echo date('M d, Y', strtotime($fine['issue_date'])); ?><br>
                                    <strong>Due:</strong> <?php echo date('M d, Y', strtotime($fine['return_date'])); ?>
                                </td>
                                <td>
    
    <?php 
    
    if (empty($fine['actual_return_date']) || $fine['actual_return_date'] == '0000-00-00') {
      
        echo '<strong>Returned:</strong> <span class="badge badge-warning">Not Yet Returned</span><br>';
        
        $dueDate = new DateTime($fine['return_date']);
        $today = new DateTime();
        if ($today > $dueDate) {
            $lateDays = $today->diff($dueDate)->days;
            echo '<span class="text-danger">' . $lateDays . ' day(s) overdue</span>';
        }

    } else {
    
        echo '<strong>Returned:</strong> ' . date('M d, Y', strtotime($fine['actual_return_date'])) . '<br>';
    
        $dueDate = new DateTime($fine['return_date']);
        $returnDate = new DateTime($fine['actual_return_date']);
        $lateDays = $returnDate->diff($dueDate)->days;
        echo '<span class="text-danger">' . $lateDays . ' day(s) late</span>';
    }
    ?>
</td>
            <td>
                <span class="text-danger font-weight-bold">PKR <?php echo number_format($fine['amount'], 2); ?></span>
            </td>
            <td><?php echo htmlspecialchars($fine['reason']); ?></td>
           
            <td>
    
    <form action="initiate_payment.php" method="post" style="display: inline-block; margin-bottom: 5px;">
        <input type="hidden" name="fine_id" value="<?php echo $fine['id']; ?>">
        <input type="hidden" name="amount" value="<?php echo $fine['amount']; ?>">
        <button type="submit" class="btn btn-success btn-sm">
            <i class="fas fa-credit-card"></i> Pay with PayFast
        </button>
    </form>

    
    <a href="generate_challan.php?fine_id=<?php echo $fine['id']; ?>" class="btn btn-primary btn-sm" target="_blank">
        <i class="fas fa-file-invoice"></i> Generate Challan
    </a>
    
</td>
           
        </tr>
    <?php endforeach; ?>
</tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (count($paidFines) > 0): ?>
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-check-circle text-success"></i> Payment History</h3>
            <div class="card-header-actions">
                <span class="badge badge-success">Total Paid: PKR <?php echo number_format($totalPaid, 2); ?></span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Book Details</th>
                            <th>Fine Details</th>
                            <th>Payment Info</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paidFines as $fine): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($fine['book_name']); ?></strong><br>
                                    <small class="text-muted">by <?php echo htmlspecialchars($fine['author']); ?></small>
                                </td>
                                <td>
                                    <strong>Amount:</strong> PKR <?php echo number_format($fine['amount'], 2); ?><br>
                                    <strong>Reason:</strong> <?php echo htmlspecialchars($fine['reason']); ?><br>
                                    <small class="text-muted">Fine Date: <?php echo date('M d, Y', strtotime($fine['created_at'])); ?></small>
                                </td>
                                <td>
                                    <strong>Paid:</strong> <?php echo date('M d, Y H:i', strtotime($fine['payment_date'])); ?><br>
                                    <strong>Method:</strong> <?php echo ucwords(str_replace('_', ' ', $fine['payment_method'])); ?><br>
                                    <?php if ($fine['transaction_id']): ?>
                                        <strong>Transaction:</strong> <?php echo $fine['transaction_id']; ?><br>
                                    <?php endif; ?>
                                    <span class="badge badge-success">Paid</span>
                                </td>
                                <td>
                                    <strong><?php echo $fine['receipt_number']; ?></strong><br>
                                    <a href="receipt.php?transaction_id=<?php echo $fine['transaction_id'];?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-receipt"></i> View Receipt
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (count($fines) == 0): ?>
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-smile fa-3x text-success mb-3"></i>
                <h3>No Fines!</h3>
                <p class="text-muted">You have no fines. Keep up the good work by returning books on time!</p>
                <a href="catalog.php" class="btn btn-primary">
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
   
    display: flex;
    flex-direction: column;
    align-items: flex-start;
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

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header-actions {
    display: flex;
    gap: 10px;
}

.font-weight-bold {
    font-weight: 700;
}
</style>

<?php include_once '../includes/footer.php'; ?>
