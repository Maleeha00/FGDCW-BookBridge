<?php
// Include header
include_once '../includes/header.php';

// Check if user is a librarian
checkUserRole('librarian');

// Process fine operations
$message = '';
$messageType = '';

// Record payment
if (isset($_POST['record_payment'])) {
    $fineId = (int)$_POST['fine_id'];
    $amount = (float)$_POST['amount'];
    $paymentMethod = trim($_POST['payment_method']);
    $receiptNumber = trim($_POST['receipt_number']);
    
    // Basic validation
    if ($amount <= 0) {
        $message = "Payment amount must be greater than zero.";
        $messageType = "danger";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get fine details
            $stmt = $conn->prepare("
                SELECT f.*, u.id as user_id, u.name as user_name, b.book_name as book_name
                FROM fines f
                JOIN issued_books ib ON f.issued_book_id = ib.id
                JOIN books b ON ib.book_id = b.id
                JOIN users u ON f.user_id = u.id
                WHERE f.id = ?
            ");
            $stmt->bind_param("i", $fineId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $fine = $result->fetch_assoc();
                
                // Check if payment amount is valid
                if ($amount > $fine['amount']) {
                    throw new Exception("Payment amount cannot exceed fine amount.");
                }
                
                // Create payment record
                $stmt = $conn->prepare("
                    INSERT INTO payments (fine_id, user_id, amount, payment_method, receipt_number)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("idsss", $fineId, $fine['user_id'], $amount, $paymentMethod, $receiptNumber);
                $stmt->execute();
                
                // Update fine status if fully paid
                if ($amount >= $fine['amount']) {
                    $stmt = $conn->prepare("UPDATE fines SET status = 'paid' WHERE id = ?");
                    $stmt->bind_param("i", $fineId);
                    $stmt->execute();
                }
                
                // Send notification to user
                $notificationMsg = "Your payment of $" . number_format($amount, 2) . " for the fine related to '{$fine['book_name']}' has been recorded.";
                sendNotification($conn, $fine['user_id'], $notificationMsg);
                
                $conn->commit();
                
                $message = "Payment recorded successfully.";
                $messageType = "success";
            } else {
                throw new Exception("Fine record not found.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error recording payment: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

$today = date('Y-m-d');
$overdueSql = "
    SELECT ib.id as issued_book_id, ib.user_id, b.book_name, DATEDIFF(?, ib.return_date) as days_overdue
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    WHERE ib.status = 'issued' AND ? > ib.return_date
      AND NOT EXISTS (
          SELECT 1 FROM fines f WHERE f.issued_book_id = ib.id
      )
";
$stmt = $conn->prepare($overdueSql);
$stmt->bind_param("ss", $today, $today);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $fineAmount = (float)$row['days_overdue'] * 100.00;
    $reason = 'Late return of book "' . $row['book_name'] . '"';
    $insertFine = $conn->prepare("
        INSERT INTO fines (issued_book_id, user_id, amount, reason, status)
        VALUES (?, ?, ?, ?, 'pending')
    ");
    $insertFine->bind_param("iids", $row['issued_book_id'], $row['user_id'], $fineAmount, $reason);
    $insertFine->execute();
}

// Handle search and filtering
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build the query
$sql = "
    SELECT f.*, u.name as user_name, u.role as user_role, b.book_name as book_name, ib.issue_date, ib.return_date, ib.actual_return_date,
           DATEDIFF(COALESCE(ib.actual_return_date, CURRENT_DATE), ib.return_date) as days_overdue
    FROM fines f
    JOIN issued_books ib ON f.issued_book_id = ib.id
    JOIN books b ON ib.book_id = b.id
    JOIN users u ON f.user_id = u.id
    WHERE 1=1 AND u.role != 'faculty'
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
    $sql .= " AND f.status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY f.created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$fines = [];
while ($row = $result->fetch_assoc()) {
    $fines[] = $row;
}
?>

<h1 class="page-title">Manage Fines</h1>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="d-flex justify-between align-center mb-4">
    
    <div class="d-flex">
        <form action="" method="GET" class="d-flex">
            <div class="form-group mr-2" style="margin-bottom: 0; margin-right: 10px;">
                <input type="text" name="search" placeholder="Search fines..." class="form-control" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="form-group mr-2" style="margin-bottom: 0; margin-right: 10px;">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="paid" <?php echo $status == 'paid' ? 'selected' : ''; ?>>Paid</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>
</div>

<div class="table-container" style="margin-top:30px;">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>User</th>
                <th>Book</th>
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Days Late</th>
                <th>Fine Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($fines) > 0): ?>
                <?php foreach ($fines as $fine): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fine['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($fine['book_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($fine['return_date'])); ?></td>
                        <td>
                            <?php 
                            if ($fine['actual_return_date']) {
                                echo date('M d, Y', strtotime($fine['actual_return_date']));
                            } else {
                                echo '<span class="badge badge-warning">Not Returned</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <?php
                            if ($fine['days_overdue'] > 0) {
                                echo '<span class="badge badge-danger">' . $fine['days_overdue'] . ' days</span>';
                            } else {
                                echo '0 days';
                            }
                            ?>
                        </td>
                        <td>PKR <?php echo number_format($fine['amount'], 2); ?></td>
                        <td>
                            <?php if ($fine['status'] == 'pending'): ?>
                                <span class="badge badge-warning">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-success">Paid</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($fine['status'] == 'pending'): ?>
                                <button class="btn btn-sm btn-primary" data-modal-target="paymentModal<?php echo $fine['id']; ?>">
                                    <i class="fas fa-money-bill-wave"></i> Record Payment
                                </button>
                                
                                <!-- Payment Modal -->
                                <div class="modal-overlay" id="paymentModal<?php echo $fine['id']; ?>">
                                    <div class="modal payment-modal">
                                        <div class="modal-header">
                                            <h3 class="modal-title">Record Fine Payment</h3>
                                            <button class="modal-close">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Recording payment for fine of <strong>PKR <?php echo number_format($fine['amount'], 2); ?></strong>
                                                issued to <strong><?php echo htmlspecialchars($fine['user_name']); ?></strong>
                                                for the book <strong><?php echo htmlspecialchars($fine['book_name']); ?></strong>.</p>
                                            
                                            <form action="" method="POST" id="paymentForm<?php echo $fine['id']; ?>">
                                                <input type="hidden" name="fine_id" value="<?php echo $fine['id']; ?>">
                                                
                                                <div class="form-group">
                                                    <label for="amount<?php echo $fine['id']; ?>">Payment Amount (PKR)</label>
                                                    <input type="number" id="amount<?php echo $fine['id']; ?>" name="amount" class="form-control" step="0.01" min="0.01" max="<?php echo $fine['amount']; ?>" value="<?php echo $fine['amount']; ?>" required>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="payment_method<?php echo $fine['id']; ?>">Payment Method</label>
                                                    <select id="payment_method<?php echo $fine['id']; ?>" name="payment_method" class="form-control" required>
                                                        <option value="cash">Cash</option>
                                                        <option value="stripe">Credit Card </option>
                                                    </select>
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label for="receipt_number<?php echo $fine['id']; ?>">Receipt Number (Optional)</label>
                                                    <input type="text" id="receipt_number<?php echo $fine['id']; ?>" name="receipt_number" class="form-control">
                                                </div>
                                                
                                                <div class="stripe-payment-section" id="stripeSection<?php echo $fine['id']; ?>" style="display: none;">
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i>
                                                        <strong>Stripe Payment Processing</strong><br>
                                                        Click the button below to process payment via Stripe.
                                                    </div>
                                                    
                                                    <button type="button" class="btn btn-success" onclick="processStripePayment(<?php echo $fine['id']; ?>, <?php echo $fine['amount']; ?>)">
                                                        <i class="fab fa-stripe"></i> Process Stripe Payment
                                                    </button>
                                                </div>
                                                
                                                <div class="form-group text-right">
                                                    <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                                                    <button type="submit" name="record_payment" class="btn btn-primary" id="cashPaymentBtn<?php echo $fine['id']; ?>">Record Cash Payment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <a href="fine_details.php?id=<?php echo $fine['id']; ?>" class="btn btn-sm btn-info" style="margin-top: 5px;">
                                <i class="fas fa-info-circle"></i> Details
                            </a>
                            
                            <?php if ($fine['status'] == 'paid'): ?>
                                <a href="payment_details.php?fine_id=<?php echo $fine['id']; ?>" class="btn btn-sm btn-secondary" style="margin-top: 5px;">
                                    <i class="fas fa-receipt"></i> View Payment
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No fines found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Stripe Payment Modal -->
<div class="modal-overlay" id="stripePaymentModal">
    <div class="modal payment-modal">
        <div class="modal-header">
            <h3 class="modal-title">Stripe Payment Processing</h3>
            <button class="modal-close" onclick="closeStripeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="stripe-payment-container">
                <div class="payment-info">
                    <h4>Payment Details</h4>
                    <div class="payment-summary">
                        <div class="payment-item">
                            <span>Amount:</span>
                            <span id="stripeAmount">$0.00</span>
                        </div>
                        <div class="payment-item">
                            <span>Fine ID:</span>
                            <span id="stripeFineId">#</span>
                        </div>
                    </div>
                </div>
                
                <div class="stripe-form">
                    <div id="card-element">
                        <!-- Stripe Elements will create form elements here -->
                    </div>
                    <div id="card-errors" role="alert"></div>
                </div>
                
                <div class="stripe-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeStripeModal()">Cancel</button>
                    <button type="button" class="btn btn-success" id="submit-payment">
                        <i class="fas fa-credit-card"></i> Pay Now
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-container {
    display: flex;
    gap: 15px;
}

.badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
}

.table td {
    vertical-align: middle;
}

.btn-group {
    display: flex;
    gap: 5px;
}

.btn {
    white-space: nowrap;
}

.payment-modal .modal {
    max-width: 600px;
}

.stripe-payment-section {
    margin-top: 20px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.stripe-payment-container {
    max-width: 500px;
    margin: 0 auto;
}

.payment-info {
    margin-bottom: 30px;
}

.payment-summary {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-top: 15px;
}

.payment-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-weight: 500;
}

.payment-item:last-child {
    margin-bottom: 0;
    font-size: 1.1em;
    color: #0d47a1;
}

.stripe-form {
    margin-bottom: 30px;
}

#card-element {
    padding: 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background: white;
}

#card-errors {
    color: #dc3545;
    margin-top: 10px;
    font-size: 0.9em;
}

.stripe-actions {
    text-align: center;
}

.stripe-actions .btn {
    margin: 0 10px;
    min-width: 120px;
}
</style>

<script>
// Show/hide Stripe payment section based on payment method selection
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethodSelects = document.querySelectorAll('[id^="payment_method"]');
    
    paymentMethodSelects.forEach(select => {
        const fineId = select.id.replace('payment_method', '');
        const stripeSection = document.getElementById('stripeSection' + fineId);
        const cashPaymentBtn = document.getElementById('cashPaymentBtn' + fineId);
        
        select.addEventListener('change', function() {
            if (this.value === 'stripe') {
                stripeSection.style.display = 'block';
                cashPaymentBtn.style.display = 'none';
            } else {
                stripeSection.style.display = 'none';
                cashPaymentBtn.style.display = 'inline-block';
            }
        });
    });
});

// Stripe payment processing
let stripe;
let elements;
let cardElement;
let currentFineId;
let currentAmount;

function processStripePayment(fineId, amount) {
    currentFineId = fineId;
    currentAmount = amount;
    
    document.getElementById('stripeFineId').textContent = '#' + fineId;
    document.getElementById('stripeAmount').textContent = 'Rs ' + amount.toFixed(2);
    
    // Show Stripe modal
    document.getElementById('stripePaymentModal').classList.add('active');
    
    // Initialize Stripe
    initializeStripe();
}

function initializeStripe() {
    // Stripe publishable key (test key)
    const stripePublishableKey = 'pk_test_51RXrGJ4KfG2Zot2yqATlNthP1rmv44p2UxKkM4fgXUrBBzcCJaogNREypEto3QvO9D7dfuY2mqEBgPGX8c8LgfLD00nAS0nnVR';
    
    if (!stripe) {
        stripe = Stripe(stripePublishableKey);
        elements = stripe.elements();
        
        cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#424770',
                    '::placeholder': {
                        color: '#aab7c4',
                    },
                },
            },
        });
        
        cardElement.mount('#card-element');
        
        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
        
        // Handle form submission
        document.getElementById('submit-payment').addEventListener('click', handleStripePayment);
    }
}

async function handleStripePayment() {
    const submitButton = document.getElementById('submit-payment');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    try {
        // Create payment method
        const {error, paymentMethod} = await stripe.createPaymentMethod({
            type: 'card',
            card: cardElement,
        });
        
        if (error) {
            document.getElementById('card-errors').textContent = error.message;
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-credit-card"></i> Pay Now';
            return;
        }
        
        // Send payment to your server
        const response = await fetch('process_stripe_payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                payment_method_id: paymentMethod.id,
                amount: Math.round(currentAmount * 100), // Convert to cents
                fine_id: currentFineId,
                currency: 'pkr'
            }),
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Payment successful
            alert('Payment processed successfully!');
            closeStripeModal();
            location.reload(); // Refresh the page to show updated status
        } else {
            document.getElementById('card-errors').textContent = result.error || 'Payment failed. Please try again.';
        }
    } catch (error) {
        document.getElementById('card-errors').textContent = 'An error occurred. Please try again.';
    }
    
    submitButton.disabled = false;
    submitButton.innerHTML = '<i class="fas fa-credit-card"></i> Pay Now';
}

function closeStripeModal() {
    document.getElementById('stripePaymentModal').classList.remove('active');
    
    // Clear any error messages
    document.getElementById('card-errors').textContent = '';
    
    // Clear the card element
    if (cardElement) {
        cardElement.clear();
    }
}
</script>

<!-- Include Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>

<?php include_once '../includes/footer.php'; ?>