<?php

include_once '../includes/header.php';
?>

<div class="container">
    <div class="card" style="margin-top: 50px; text-align: center; padding: 40px;">
        <div class="card-body">
            <i class="fas fa-times-circle fa-5x text-danger mb-4"></i>
            <h1 class="page-title">Payment Cancelled</h1>
            <p class="text-muted">Your payment process was cancelled.</p>
            <p>If you wish to try again, please go back to the fines page.</p>
            <br>
            <a href="fines.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Back to My Fines
            </a>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>