<?php

include_once '../includes/header.php';

include_once '../includes/config.php'; 

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

try {
    
    $stmt = $conn->prepare("
        SELECT id, fine_id 
        FROM payments 
        WHERE user_id = ? AND payment_status = 'Pending' 
        ORDER BY payment_date DESC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $payment = $result->fetch_assoc();
        $payment_id = $payment['id'];
        $fine_id = $payment['fine_id'];

        
        $conn->begin_transaction();

        $stmt1 = $conn->prepare("UPDATE payments SET payment_status = 'Complete', transaction_id = 'SANDBOX_DEMO' WHERE id = ?");
        $stmt1->bind_param("i", $payment_id);
        $stmt1->execute();

        $stmt2 = $conn->prepare("UPDATE fines SET status = 'paid' WHERE id = ?");
        $stmt2->bind_param("i", $fine_id);
        $stmt2->execute();

        $conn->commit();
        
        $message = "Your payment has been successfully processed and recorded.";
        $messageType = "success";

    } else {
        
        $message = "Thank you for your payment.";
        $messageType = "info";
    }

} catch (Exception $e) {
    $conn->rollback();
    $message = "An error occurred while updating your record. Please contact the librarian.";
    $messageType = "danger";
}

?>

<div class="container">
    <div class="card" style="margin-top: 50px; text-align: center; padding: 40px;">
        <div class="card-body">
            
            <?php if ($messageType == 'success' || $messageType == 'info'): ?>
                <i class="fas fa-check-circle fa-5x text-success mb-4"></i>
                <h1 class="page-title">Payment Successful!</h1>
            <?php else: ?>
                <i class="fas fa-exclamation-circle fa-5x text-danger mb-4"></i>
                <h1 class="page-title">An Error Occurred</h1>
            <?php endif; ?>

            <p class="text-muted"><?php echo $message; ?></p>
            
            <br>
            <a href="fines.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to My Fines
            </a>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>