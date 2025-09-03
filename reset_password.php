<?php
session_start();
include 'includes/config.php';

$message = '';
$messageType = '';
$validToken = false;
$token = isset($_GET['token']) ? $_GET['token'] : '';

function validatePassword($password)
{
    $errors = [];

    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }

    
    if (!preg_match('/[@#$]/', $password)) {
        $errors[] = "Password must contain at least one special character (@, #, $)";
    }

    return $errors;
}


if (!empty($token)) {
    $stmt = $conn->prepare("
        SELECT pr.*, u.email, u.id as user_id
        FROM password_resets pr
        JOIN users u ON pr.user_id = u.id
        WHERE pr.token = ? AND pr.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $reset = $result->fetch_assoc();
        $validToken = true;
    } else {
        $message = "Invalid or expired reset token. Please request a new password reset.";
        $messageType = "danger";
    }
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && $validToken) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    
    $passwordErrors = validatePassword($password);
    if (!empty($passwordErrors)) {
        $message = implode(". ", $passwordErrors);
        $messageType = "danger";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "danger";
    } else {
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $reset['user_id']);

        if ($stmt->execute()) {
            
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->bind_param("i", $reset['user_id']);
            $stmt->execute();

            $message = "Password has been reset successfully. You can now login with your new password.";
            $messageType = "success";

            
            header("refresh:3;url=index.php");
        } else {
            $message = "Error resetting password. Please try again.";
            $messageType = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="icon" type="image/png" href="uploads/assests/book.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    
  <nav class="auth-navbar">
    <div class="container">
      <a href="index.php" class="auth-logo">
        <img src="/uploads/assests/book.png" alt="Library Logo" class="logo-image">
        <span class="navbar-title">BookBridge</span>
      </a>
      <div class="auth-nav-links">
        <a href="gallery.php" class="auth-nav-link">
          <i class="fa-solid fa-images"></i>
          <span>Gallery</span>
        </a>
        <a href="about.php" class="auth-nav-link">
          <i class="fa-solid fa-circle-info"></i>
          <span>About</span>
        </a>
        <a href="index.php" class="auth-nav-link">
          <i class="fa-solid fa-user-plus"></i>
          <span>Login</span>
        </a>
      </div>
    </div>
  </nav>

    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-header">
                <h1>

                    Reset Password
                </h1>
                <p>Create your new password</p>
            </div>

            <div class="auth-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i
                            class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($validToken): ?>
                    <form method="POST" action="reset_password.php?token=<?php echo htmlspecialchars($token); ?>"
                        id="resetForm">
                        <div class="form-group">
                            <label for="password">

                                New Password
                            </label>
                            <input type="password" id="password" name="password" placeholder="Enter your new password"
                                required>
                            <div class="password-requirements" id="passwordRequirements">
                                <h4>Password Requirements:</h4>
                                <div class="requirement" id="length-req">
                                    <i class="fas fa-times"></i>
                                    <span>At least 8 characters long</span>
                                </div>
                                <div class="requirement" id="uppercase-req">
                                    <i class="fas fa-times"></i>
                                    <span>At least one uppercase letter (A-Z)</span>
                                </div>
                                <div class="requirement" id="special-req">
                                    <i class="fas fa-times"></i>
                                    <span>At least one special character (@, #, $)</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">

                                Confirm New Password
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                placeholder="Confirm your new password" required>
                        </div>

                        <button type="submit" class="btn-auth" id="submitBtn" disabled>

                            Update Password
                        </button>
                    </form>
                <?php else: ?>
                    <div style="text-align: center;">
                        <a href="forgot_password.php" class="btn-auth">
                            <i class="fas fa-redo"></i>
                            Request New Reset Link
                        </a>
                    </div>
                <?php endif; ?>

                <div style="text-align: center;">
                    <a href="index.php" class="btn-link-auth">
                        <i class="fas fa-arrow-left"></i>
                        Back to Login
                    </a>
                </div>
            </div>

            <div class="auth-footer">
                <p>&copy; <?= date("Y") ?>  FGDCW Bookbridge system. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const submitBtn = document.getElementById('submitBtn');
            const lengthReq = document.getElementById('length-req');
            const uppercaseReq = document.getElementById('uppercase-req');
            const specialReq = document.getElementById('special-req');
            const passwordRequirements = document.getElementById('passwordRequirements');

            if (passwordInput && confirmPasswordInput) {
                
                passwordInput.addEventListener('focus', function () {
                    passwordRequirements.classList.add('show');
                });

                
                passwordInput.addEventListener('blur', function () {
                    setTimeout(() => {
                        if (document.activeElement !== confirmPasswordInput) {
                            passwordRequirements.classList.remove('show');
                        }
                    }, 200);
                });

                function validatePassword() {
                    const password = passwordInput.value;
                    let isValid = true;

                   
                    if (password.length >= 8) {
                        lengthReq.classList.add('valid');
                        lengthReq.classList.remove('invalid');
                        lengthReq.querySelector('i').className = 'fas fa-check';
                    } else {
                        lengthReq.classList.add('invalid');
                        lengthReq.classList.remove('valid');
                        lengthReq.querySelector('i').className = 'fas fa-times';
                        isValid = false;
                    }

                    if (/[A-Z]/.test(password)) {
                        uppercaseReq.classList.add('valid');
                        uppercaseReq.classList.remove('invalid');
                        uppercaseReq.querySelector('i').className = 'fas fa-check';
                    } else {
                        uppercaseReq.classList.add('invalid');
                        uppercaseReq.classList.remove('valid');
                        uppercaseReq.querySelector('i').className = 'fas fa-times';
                        isValid = false;
                    }

                    
                    if (/[@#$]/.test(password)) {
                        specialReq.classList.add('valid');
                        specialReq.classList.remove('invalid');
                        specialReq.querySelector('i').className = 'fas fa-check';
                    } else {
                        specialReq.classList.add('invalid');
                        specialReq.classList.remove('valid');
                        specialReq.querySelector('i').className = 'fas fa-times';
                        isValid = false;
                    }

                    
                    const passwordsMatch = password === confirmPasswordInput.value && password.length > 0;

                    
                    submitBtn.disabled = !(isValid && passwordsMatch);

                    return isValid;
                }

                passwordInput.addEventListener('input', function () {
                    validatePassword();
                    
                    if (this.value.length > 0) {
                        passwordRequirements.classList.add('show');
                    }
                });

                confirmPasswordInput.addEventListener('input', validatePassword);

                
                document.getElementById('resetForm').addEventListener('submit', function (e) {
                    if (!validatePassword()) {
                        e.preventDefault();
                        alert('Please ensure your password meets all requirements.');
                    }

                    if (passwordInput.value !== confirmPasswordInput.value) {
                        e.preventDefault();
                        alert('Passwords do not match.');
                    }
                });
            }
        });
    </script>
</body>

</html>