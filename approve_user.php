<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


include_once '../includes/config.php';
include_once '../includes/functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'librarian') {
    header('Location: ../index.php');
    exit();
}

$message = '';
$messageType = '';


function sendAccountEmail($toEmail, $toName, $subject, $bodyHtml) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'iqranoureench@gmail.com';
        $mail->Password   = 'vezw trnp kiwg cssa'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        
        $mail->setFrom('iqranoureench@gmail.com', 'FGDCW BookBridge System');
        $mail->addAddress($toEmail, $toName);

       
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}


if (isset($_GET['id']) && isset($_GET['action'])) {
    $userId = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        try {
            
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND approval_status = 'pending'");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows !== 1) {
                throw new Exception('User not found or already processed.');
            }

            $user = $result->fetch_assoc();

           
            $updateStmt = $conn->prepare("UPDATE users SET approval_status = 'approved', approved_at = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $userId);

            if ($updateStmt->execute()) {
                
                $token = bin2hex(random_bytes(16));

                
                $tokenStmt = $conn->prepare("UPDATE users SET email_verification_token = ? WHERE id = ?");
                $tokenStmt->bind_param("si", $token, $userId);
                $tokenStmt->execute();

                
               $verifyUrl = "http://localhost/lastedit23/Fgdcwlast/Fgdcw/verify_email.php?token=$token";

                $subject = "Verify Your Email - FGDCW Library";

                $body = "
                    Dear {$user['name']},<br><br>
                    Your library account has been <b>approved</b>.<br>
                    Please verify your email address by clicking the link below:<br><br>
                    <a href='{$verifyUrl}' style='padding:10px 15px;background:#007BFF;color:white;text-decoration:none;border-radius:5px;'>Verify Email</a><br><br>
                    Regards,<br>FGDCW BookBridge System
                ";

                sendAccountEmail($user['email'], $user['name'], $subject, $body);

                $message = "User {$user['name']} has been approved and verification email sent.";
                $messageType = "success";
            } else {
                throw new Exception('Error approving user.');
            }
        } catch (Exception $e) {
            $message = 'Error approving user: ' . $e->getMessage();
            $messageType = 'danger';
        }

    } elseif ($action === 'reject') {
        try {
            
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND approval_status = 'pending'");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows !== 1) {
                throw new Exception('User not found or already processed.');
            }

            $user = $result->fetch_assoc();

           
            $updateStmt = $conn->prepare("UPDATE users SET approval_status = 'rejected', rejected_at = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $userId);

            if ($updateStmt->execute()) {
                
                $notificationMsg = "Your account registration has been rejected. Please contact the library for more information.";
                sendNotification($conn, $userId, $notificationMsg);


                $subject = "Library Account Rejected";
                $body = "
                    Dear {$user['name']},<br><br>
                    Unfortunately, your library account registration has been <b>rejected</b>.<br>
                    Please contact the library for more details.<br><br>
                    Regards,<br>FGDCW BookBridge System
                ";

                sendAccountEmail($user['email'], $user['name'], $subject, $body);

                $message = "User {$user['name']} has been rejected.";
                $messageType = "success";
            } else {
                throw new Exception('Error rejecting user.');
            }
        } catch (Exception $e) {
            $message = 'Error rejecting user: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}


if (!empty($message)) {
    $_SESSION['dashboard_message'] = $message;
    $_SESSION['dashboard_message_type'] = $messageType;
}

header('Location: dashboard.php');
exit();
?>
