<?php
session_start();
include 'includes/config.php';
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Generate reset token
$token = bin2hex(random_bytes(32));
$createdAt = date('Y-m-d H:i:s');
$expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

// Delete any existing reset tokens for this user
$stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();

// Store reset token
$stmt = $conn->prepare("
    INSERT INTO password_resets (user_id, token, created_at, expires_at)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("isss", $user['id'], $token, $createdAt, $expiresAt);
        
        if ($stmt->execute()) {
            // Create reset link
            $resetLink = "http://{$_SERVER['HTTP_HOST']}/reset_password.php?token=" . $token;
            
            // Send email using PHPMailer
            $mail = new PHPMailer(true);
            
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'iqranoureench@gmail.com'; // Your Gmail address
                $mail->Password = 'vezw trnp kiwg cssa'; // Your Gmail app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                // Recipients
                $mail->setFrom('iqranoureench@gmail.com', 'FGDCW BookBridge');
                $mail->addAddress($email, $user['name']);
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "
    <div style='background-color: #F7F9FC; padding: 30px; font-family: Arial, sans-serif; color: #333;'>
        <div style='max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>
            <h2 style='color: #8B5E3C;'>Password Reset Request</h2>
            <p>Dear {$user['name']},</p>
            <p>We received a request to reset your password. Please click the button below to proceed:</p>
            <div style='text-align: center; margin: 30px 0;'>
                <a href='{$resetLink}' 
                   style='background-color: #8B5E3C; color: white; text-decoration: none; padding: 12px 24px; font-size: 16px; border-radius: 5px; display: inline-block;'>
                    Reset Password
                </a>
            </div>
            <p>This link will expire in <strong>1 hour</strong>.</p>
            <p>If you didn't request a password reset, you can safely ignore this email.</p>
            <p style='margin-top: 30px;'>Best regards,<br><strong>FGDCW BookBridge System</strong></p>
        </div>
    </div>
";

                
                $mail->send();
                $message = "Password reset instructions have been sent to your email address.";
                $messageType = "success";
            } catch (Exception $e) {
                $message = "Error sending email: " . $mail->ErrorInfo;
                $messageType = "danger";
            }
        } else {
            $message = "Error generating reset token. Please try again.";
            $messageType = "danger";
        }
    } else {
        $message = "No account found with this email address.";
        $messageType = "danger";
    }
}

// Create password_resets table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_token (token)
)";
$conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password </title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="icon" type="image/png" href="uploads/assests/book.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="auth-navbar">
        <div class="container">
            <a href="index.php" class="auth-logo">
               <img src="/uploads/assests/book.png" alt="Library Logo" class="logo-image">
<span class="navbar-title">BookBridge</span>
            </a>
            <div class="auth-nav-links">
                <a href="gallery.php" class="auth-nav-link">
                    <i class="fas fa-images"></i>
                    <span>Gallery</span>
                </a>
                <a href="about.php" class="auth-nav-link">
                    <i class="fas fa-info-circle"></i>
                    <span>About</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-header">
                <h1>
                    
                    Forgot Password
                </h1>
                <p>Reset your account password</p>
            </div>
            
            <div class="auth-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">
                             
                            Email Address
                        </label>
                        <input type="email" id="email" name="email" placeholder="Enter your registered email address" required>
                    </div>
                    
                    <button type="submit" class="btn-auth">
                        <i class="fas fa-paper-plane"></i> 
                        Send Reset Instructions
                    </button>
                    
                    <div style="text-align: center;">
                        <a href="index.php" class="btn-link-auth">
                            <i class="fas fa-arrow-left"></i> 
                            Back to Login
                        </a>
                        
                        <a href="recover_account.php" class="btn-link-auth">
                            <i class="fas fa-search"></i> 
                            Find Your Account
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="auth-footer">
                <p>&copy; 2025 Book Bridge. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>