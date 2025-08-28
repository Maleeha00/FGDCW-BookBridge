<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fgdcw_bookbridge";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set timezone
date_default_timezone_set('Asia/Karachi'); 
$conn->query("SET time_zone = '+05:00'");

// Check if database exists, if not create it
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Function to generate unique ID
function generateUniqueId($conn, $role) {
    $prefix = '';
    switch($role) {
        case 'student':
            $prefix = 'STU';
            break;
        case 'faculty':
            $prefix = 'FAC';
            break;
        case 'librarian':
            $prefix = 'LIB';
            break;
    }
    
    do {
        $randomNumber = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $uniqueId = $prefix . $randomNumber;
        
        // Check if this ID already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE unique_id = ?");
        $stmt->bind_param("s", $uniqueId);
        $stmt->execute();
        $result = $stmt->get_result();
    } while ($result->num_rows > 0);
    
    return $uniqueId;
}

// Function to auto-expire approved book requests that haven't been collected
function autoExpireBookRequests($conn) {
    // Get expired approved requests
    $stmt = $conn->prepare("
        SELECT br.id, br.user_id, b.book_name 
        FROM book_requests br
        JOIN books b ON br.book_id = b.id
        WHERE br.status = 'approved' 
        AND br.collected = 0 
        AND br.expires_at < NOW()
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $expiredCount = 0;
    while ($row = $result->fetch_assoc()) {
        // Mark as expired
        $updateStmt = $conn->prepare("UPDATE book_requests SET status = 'expired' WHERE id = ?");
        $updateStmt->bind_param("i", $row['id']);
        $updateStmt->execute();
        
        // Send notification to user
        $message = "Your approved request for '{$row['book_name']}' has expired because you didn't collect it within 2 days. Please submit a new request if you still need the book.";
        sendNotification($conn, $row['user_id'], $message);
        
        $expiredCount++;
    }
    
    return $expiredCount;
}

// Function to check login attempts and implement security
function checkLoginAttempts($conn, $identifier, $ipAddress) {
    // Clean old attempts (older than 60 minutes)
    $cleanupSql = "DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 60 MINUTE)";
    $conn->query($cleanupSql);
    
    // Check failed attempts in last 60 minutes
    $stmt = $conn->prepare("
        SELECT COUNT(*) as failed_attempts, 
               MAX(attempt_time) as last_attempt
        FROM login_attempts 
        WHERE (identifier = ? OR ip_address = ?) 
        AND success = FALSE 
        AND attempt_time > DATE_SUB(NOW(), INTERVAL 60 MINUTE)
    ");
    $stmt->bind_param("ss", $identifier, $ipAddress);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['failed_attempts'] >= 3) {
        $lastAttempt = new DateTime($row['last_attempt']);
        $now = new DateTime();
        $timeDiff = $now->getTimestamp() - $lastAttempt->getTimestamp();
        $blockDuration = 60 * 60; // 60 minutes in seconds
        
        if ($timeDiff < $blockDuration) {
            return array(
                'blocked' => true,
                'remaining_time' => ceil(($blockDuration - $timeDiff) / 60),
                'message' => 'Too many failed login attempts. Please wait ' . ceil(($blockDuration - $timeDiff) / 60) . ' seconds before trying again.'
            );
        }
    }
    
    return array('blocked' => false);
}

// Function to record login attempt
function recordLoginAttempt($conn, $identifier, $ipAddress, $success) {
    $stmt = $conn->prepare("INSERT INTO login_attempts (identifier, ip_address, success) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $identifier, $ipAddress, $success);
    $stmt->execute();
}

// Function to get user's IP address
function getUserIpAddress() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Calculate fine amount based on days overdue
/*function calculateFine($dueDate, $returnDate, $finePerDay = 100.00, $userRole = 'student') {
    // Faculty members are exempt from fines
    if ($userRole === 'faculty') {
        return 0;
    }
    
    $due = new DateTime($dueDate);
    $return = new DateTime($returnDate);
    $diff = $return->diff($due);
    
    if ($return > $due) {
        $daysOverdue = $diff->days;
        return $daysOverdue * $finePerDay;
    }
    
    return 0;
}*/
// ===== FINAL AND CORRECT numberToWords FUNCTION =====
// ===================================================================
function numberToWords($number) {
    $dictionary = [
        0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine',
        10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen',
        20 => 'twenty', 30 => 'thirty', 40 => 'forty', 50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety'
    ];
    $number = (int) $number;
    $string = '';

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens = ((int) ($number / 10)) * 10;
            $units = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= ' ' . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds = (int) ($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' hundred';
            if ($remainder) {
                $string .= ' and ' . numberToWords($remainder);
            }
            break;
        default:
             if($number < 100000){
                $thousands = (int)($number/1000);
                $remainder = $number % 1000;
                $string = numberToWords($thousands) . ' thousand';
                if ($remainder) {
                    $string .= ' ' . numberToWords($remainder);
                }
             }
            break;
    }
    return ucwords($string);
}
// === In lines ko yahan, function ke BAHAR paste karein ===
// PayFast Sandbox Settings
define('PAYFAST_MERCHANT_ID', '10041236');
define('PAYFAST_MERCHANT_KEY', 'dg5sphhgqqgfi');

// PayFast URLs for Sandbox
define('PAYFAST_FORM_ACTION', 'https://sandbox.payfast.co.za/eng/process');

// Aap ki website ke URLs (Aap ke project ke mutabiq)
define('BASE_URL', 'http://localhost/FGDCW/');
define('PAYFAST_RETURN_URL', BASE_URL . 'student/payment_success.php');
define('PAYFAST_CANCEL_URL', BASE_URL . 'student/payment_cancelled.php');
define('PAYFAST_NOTIFY_URL', BASE_URL . 'student/payment_notify.php');
// === Yahan tak ===
?>