<?php
include_once '../includes/header.php';

checkUserRole('librarian');
$fineId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("
    SELECT f.*, u.name as user_name, b.book_name as book_name, 
           ib.issue_date, ib.return_date, ib.actual_return_date
    FROM fines f
    JOIN issued_books ib ON f.issued_book_id = ib.id
    JOIN books b ON ib.book_id = b.id
    JOIN users u ON f.user_id = u.id
    WHERE f.id = ?
");
$stmt->bind_param("i", $fineId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: fines.php');
    exit();
}

$fine = $result->fetch_assoc();
$payment = null;
if ($fine['status'] == 'paid') {
    $stmt = $conn->prepare("
        SELECT * FROM payments 
        WHERE fine_id = ? 
        ORDER BY payment_date DESC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $fineId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $payment = $result->fetch_assoc();
    }
}
?>

<div class="container">
    <div class="d-flex justify-between align-center mb-4">
        <h1 class="page-title">Fine Details</h1>
        <a href="fines.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Fines
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Fine Information</h3>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div class="info-item">
                    <label>Student Name:</label>
                    <span><?php echo htmlspecialchars($fine['user_name']); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Book Name:</label>
                    <span><?php echo htmlspecialchars($fine['book_name']); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Issue Date:</label>
                    <span><?php echo date('M d, Y', strtotime($fine['issue_date'])); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Due Date:</label>
                    <span><?php echo date('M d, Y', strtotime($fine['return_date'])); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Return Date:</label>
                    <span><?php echo date('M d, Y', strtotime($fine['actual_return_date'])); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Days Overdue:</label>
                    <span>
                        <?php
                        $dueDate = new DateTime($fine['return_date']);
                        $returnDate = new DateTime($fine['actual_return_date']);
                        $diff = $returnDate->diff($dueDate);
                        echo $diff->days . ' days';
                        ?>
                    </span>
                </div>
                
                <div class="info-item">
                    <label>Fine Amount:</label>
                    <span class="text-danger">Rs <?php echo number_format($fine['amount'], 2); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Status:</label>
                    <span>
                        <?php if ($fine['status'] == 'paid'): ?>
                            <span class="badge badge-success">Paid</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Pending</span>
                        <?php endif; ?>
                    </span>
                </div>
                
                <div class="info-item">
                    <label>Reason:</label>
                    <span><?php echo htmlspecialchars($fine['reason']); ?></span>
                </div>
                
                <div class="info-item">
                    <label>Created On:</label>
                    <span><?php echo date('M d, Y H:i', strtotime($fine['created_at'])); ?></span>
                </div>
            </div>

            <?php if ($payment): ?>
                <div class="payment-details mt-4">
                    <h4>Payment Details</h4>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Payment Date:</label>
                            <span><?php echo date('M d, Y H:i', strtotime($payment['payment_date'])); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <label>Payment Method:</label>
                            <span><?php echo ucfirst($payment['payment_method']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <label>Receipt Number:</label>
                            <span><?php echo htmlspecialchars($payment['receipt_number']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <label>Amount Paid:</label>
                            <span class="text-success">Rs <?php echo number_format($payment['amount'], 2); ?></span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.info-grid {
    display: grid;
    gap: 20px;
    margin-top: 20px;
}

.info-item {
    display: flex;
    border-bottom: 1px solid var(--gray-200);
    padding-bottom: 10px;
}

.info-item label {
    font-weight: 600;
    width: 150px;
    color: var(--text-light);
}

.info-item span {
    flex: 1;
}

.payment-details {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--gray-300);
}

.payment-details h4 {
    margin-bottom: 20px;
    color: var(--primary-color);
}

.mt-4 {
    margin-top: 1.5rem;
}
</style>

<?php include_once '../includes/footer.php'; ?>