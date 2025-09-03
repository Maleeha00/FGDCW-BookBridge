<?php
session_start();

include_once '../includes/config.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'faculty')) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_POST['fine_id']) || !isset($_POST['amount'])) {
    
    header('Location: fines.php');
    exit();
}

$fine_id = $_POST['fine_id'];
$amount = $_POST['amount'];
$user_id = $_SESSION['user_id'];

$sql = "INSERT INTO payments (fine_id, user_id, amount, payment_method, payment_status, payment_date) VALUES (?, ?, ?, 'PayFast', 'Pending', NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iid", $fine_id, $user_id, $amount);
$stmt->execute();
$new_payment_id = $stmt->insert_id; 
$stmt->close();

$data = array(
    
    'merchant_id' => PAYFAST_MERCHANT_ID,
    'merchant_key' => PAYFAST_MERCHANT_KEY,
    'return_url' => PAYFAST_RETURN_URL,
    'cancel_url' => PAYFAST_CANCEL_URL,
    'notify_url' => PAYFAST_NOTIFY_URL,
    
    'name_first' => explode(' ', $_SESSION['name'])[0], 
    'name_last'  => substr(strstr($_SESSION['name']," "), 1) ?: ' ', 
    'email_address' => $_SESSION['email'],

    'm_payment_id' => $new_payment_id,
    'amount' => number_format(floatval($amount), 2, '.', ''),
    'item_name' => 'Library Fine for Fine ID: ' . $fine_id,
    'custom_str1' => $_SESSION['unique_id'] 
);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to PayFast...</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column; }
        .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="spinner"></div>
    <p style="margin-top: 20px;">Please wait while you are being redirected to the secure payment page...</p>

    <form action="<?php echo PAYFAST_FORM_ACTION; ?>" method="post" name="payfastForm" id="payfastForm">
        <?php
        foreach($data as $key => $val) {
            echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($val) . '">';
        }
        ?>
    </form>
    
    <script type="text/javascript">
        document.getElementById('payfastForm').submit();
    </script>
</body>
</html>