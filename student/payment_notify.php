<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../includes/config.php';

$log_file = __DIR__ . '/payfast_log.txt'; 


$initial_log = file_put_contents($log_file, "--- New ITN Request ---\n" . date('Y-m-d H:i:s') . "\n", FILE_APPEND);

if ($initial_log === false) {
    
    die("Error: Could not write to log file. Please check folder permissions for C:\\xampp\\htdocs\\FGDCW\\student\\");
}

file_put_contents($log_file, print_r($_POST, true), FILE_APPEND);

$conn = new mysqli("localhost", "root", "", "fgdcw_bookbridge");
if ($conn->connect_error) {
    file_put_contents($log_file, "Database Connection Error: " . $conn->connect_error . "\n", FILE_APPEND);
    die("DB Connection Error"); 
}


$pfData = $_POST;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, PAYFAST_FORM_ACTION);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($pfData));
$response = curl_exec($ch);
curl_close($ch);

file_put_contents($log_file, "Verification Response: " . $response . "\n", FILE_APPEND);

if (trim($response) === 'VALID') {
    $payment_status = $pfData['payment_status'];
    $m_payment_id = $pfData['m_payment_id'];
    $pf_payment_id = $pfData['pf_payment_id'];

    if ($payment_status === 'COMPLETE') {
        $conn->begin_transaction();
        try {
            $stmt1 = $conn->prepare("UPDATE payments SET payment_status = 'Complete', transaction_id = ?, payment_details = ? WHERE id = ?");
            $payment_details_json = json_encode($pfData);
            $stmt1->bind_param("ssi", $pf_payment_id, $payment_details_json, $m_payment_id);
            $stmt1->execute();

            $stmt_get_fine = $conn->prepare("SELECT fine_id FROM payments WHERE id = ?");
            $stmt_get_fine->bind_param("i", $m_payment_id);
            $stmt_get_fine->execute();
            $fine_id = $stmt_get_fine->get_result()->fetch_assoc()['fine_id'];

            if ($fine_id) {
                $stmt2 = $conn->prepare("UPDATE fines SET status = 'paid' WHERE id = ?");
                $stmt2->bind_param("i", $fine_id);
                $stmt2->execute();
            }
            $conn->commit();
            file_put_contents($log_file, "Database updated for payment ID: " . $m_payment_id . "\n\n", FILE_APPEND);
        } catch (Exception $e) {
            $conn->rollback();
            file_put_contents($log_file, "Database Error: " . $e->getMessage() . "\n\n", FILE_APPEND);
        }
    } else {
        file_put_contents($log_file, "Payment status NOT COMPLETE. Status: " . $payment_status . "\n\n", FILE_APPEND);
    }
} else {
    file_put_contents($log_file, "ITN verification FAILED.\n\n", FILE_APPEND);
}

header('HTTP/1.0 200 OK');
flush();
?>