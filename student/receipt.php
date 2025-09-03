<?php

include_once '../includes/header.php';

include_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['transaction_id']) || empty($_GET['transaction_id'])) {
    echo "<div class='container'><div class='alert alert-danger'>Invalid receipt request.</div></div>";
    include_once '../includes/footer.php';
    exit();
}

$transactionId = $_GET['transaction_id'];
$userId = $_SESSION['user_id'];

$sql = "
    SELECT 
        p.amount, p.payment_date, p.payment_method, p.transaction_id,
        f.reason,
        b.book_name, b.author,
        u.name as user_name, u.unique_id as user_unique_id
    FROM payments p
    JOIN fines f ON p.fine_id = f.id
    JOIN issued_books ib ON f.issued_book_id = ib.id
    JOIN books b ON ib.book_id = b.id
    JOIN users u ON p.user_id = u.id
    WHERE p.transaction_id = ? AND p.user_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $transactionId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<div class='container'><div class='alert alert-danger'>Receipt not found or you do not have permission to view it.</div></div>";
    include_once '../includes/footer.php';
    exit();
}

$receipt = $result->fetch_assoc();
?>

<div class="container">
    <div class="card receipt-card">
        <div class="card-header">
            <h2>Payment Receipt</h2>
            <button onclick="window.print()" class="btn btn-primary no-print"><i class="fas fa-print"></i> Print Receipt</button>
        </div>
        <div class="card-body">
            <div class="receipt-header">
                <div>
                    <h4>Library Fine Receipt</h4>
                    <p>FGDCW BookBridge System</p>
                </div>
                <div>
                    <strong>Date:</strong> <?php echo date('M d, Y H:i A', strtotime($receipt['payment_date'])); ?><br>
                    <strong>Transaction ID:</strong> <?php echo htmlspecialchars($receipt['transaction_id']); ?>
                </div>
            </div>

            <hr>

            <div class="receipt-details">
                <p><strong>Student Name:</strong> <?php echo htmlspecialchars($receipt['user_name']); ?></p>
                <p><strong>Student ID:</strong> <?php echo htmlspecialchars($receipt['user_unique_id']); ?></p>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            Fine for: <strong><?php echo htmlspecialchars($receipt['book_name']); ?></strong><br>
                            <small class="text-muted">(<?php echo htmlspecialchars($receipt['reason']); ?>)</small>
                        </td>
                        <td class="text-right">PKR <?php echo number_format($receipt['amount'], 2); ?></td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-right">Total Paid</th>
                        <th class="text-right">PKR <?php echo number_format($receipt['amount'], 2); ?></th>
                    </tr>
                </tfoot>
            </table>
            
            <div class="receipt-footer">
                <p><strong>Payment Method:</strong> <?php echo ucfirst($receipt['payment_method']); ?></p>
                <p class="text-center">Thank you for your payment!</p>
            </div>
        </div>
    </div>
</div>

<style>
    .receipt-card {
        margin-top: 30px;
        border: 1px solid #dee2e6;
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,.15);
    }
    .receipt-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .text-right {
        text-align: right;
    }
    .receipt-footer {
        margin-top: 30px;
    }
    @media print {
        .no-print, .main-sidebar, .main-header {
            display: none !important;
        }
        .container {
            width: 100%;
            padding: 0;
            margin: 0;
        }
        .receipt-card {
            box-shadow: none;
            border: none;
        }
    }
</style>

<?php include_once '../includes/footer.php'; ?>