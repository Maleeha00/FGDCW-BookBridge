<?php
session_start();
include 'includes/config.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'librarian') {
        header('Location: librarian/dashboard.php');
    } else {
        header('Location: student/catalog.php');
    }
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_identifier = trim($_POST['login_identifier']); 
    $password = $_POST['password'];
    $ipAddress = getUserIpAddress();
    if (empty($login_identifier) || empty($password)) {
        $error = "Please enter both Unique ID and password";
    } else {
        
        $securityCheck = checkLoginAttempts($conn, $login_identifier, $ipAddress);
        if ($securityCheck['blocked']) {
            $error = $securityCheck['message'];
        } else {
           
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR unique_id = ?");
            $stmt->bind_param("ss", $login_identifier, $login_identifier);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();


                
                if ($user['role'] != 'librarian' && $user['approval_status'] != 'approved') {
                    if ($user['approval_status'] == 'pending') {
                        $error = "Your account is pending approval. Please wait for librarian approval or contact the library.";
                    } else {
                        $error = "Your account has been rejected. Please contact the library for more information.";
                    }
                    recordLoginAttempt($conn, $login_identifier, $ipAddress, false);

                
                } elseif (!empty($user['email_verification_token'])) {
                    $error = "Your email is not verified. Please check your inbox and verify your email to log in.";
                    recordLoginAttempt($conn, $login_identifier, $ipAddress, false);

                } elseif (password_verify($password, $user['password'])) {
                    recordLoginAttempt($conn, $login_identifier, $ipAddress, true);

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['unique_id'] = $user['unique_id'];

                    if ($user['role'] == 'librarian') {
                        header('Location: librarian/dashboard.php');
                    } else {
                        header('Location: student/catalog.php');
                    }
                    exit();

                } else {
                    recordLoginAttempt($conn, $login_identifier, $ipAddress, false);
                    $error = "Invalid password";
                }
            } else {
                recordLoginAttempt($conn, $login_identifier, $ipAddress, false);
                $error = "User not found";
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, in
    
    itial-scale=1.0">
    <title>Login</title>
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
                <h1>Welcome Back</h1>
                
            </div>
            
            <div class="auth-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> 
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="loginForm">
                    <div class="form-group">
                        <label for="login_identifier">
                            Unique ID
                        </label>
                        <input type="text" id="login_identifier" name="login_identifier" 
                               placeholder="Unique ID or Email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">
                            Password
                        </label>
                        <input type="password" id="password" name="password" 
                               placeholder="Password" required>
                        <i class="fas fa-eye password-toggle" id="toggleIcon" onclick="togglePassword()"></i>
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
                    
                    <button type="submit" class="btn-auth" id="loginButton">
                        Sign In
                    </button>

                    <div style="text-align: center;">
                        <a href="forgot_password.php" class="btn-link-auth">
                            <i class="fas fa-key"></i> 
                            Forgot Password?
                        </a>

                        
                        
                        <a href="register.php" class="btn-link-auth">
                            <i class="fas fa-user-plus"></i> 
                            Don't have an account? Register here
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="auth-footer">
                <p>&copy; 2025 Book Bridge. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash password-toggle';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'fas fa-eye password-toggle';
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const passwordRequirements = document.getElementById('passwordRequirements');
            const lengthReq = document.getElementById('length-req');
            const uppercaseReq = document.getElementById('uppercase-req');
            const specialReq = document.getElementById('special-req');
            passwordInput.addEventListener('focus', function() {
                passwordRequirements.style.display = 'block';
            });

            passwordInput.addEventListener('blur', function() {
                setTimeout(() => {
                    passwordRequirements.style.display = 'none';
                }, 200);
            });

            passwordInput.addEventListener('input', function() {
                const password = this.value;
                
            
                if (password.length >= 8) {
                    lengthReq.classList.add('valid');
                    lengthReq.classList.remove('invalid');
                    lengthReq.querySelector('i').className = 'fas fa-check';
                } else {
                    lengthReq.classList.add('invalid');
                    lengthReq.classList.remove('valid');
                    lengthReq.querySelector('i').className = 'fas fa-times';
                }

                
                if (/[A-Z]/.test(password)) {
                    uppercaseReq.classList.add('valid');
                    uppercaseReq.classList.remove('invalid');
                    uppercaseReq.querySelector('i').className = 'fas fa-check';
                } else {
                    uppercaseReq.classList.add('invalid');
                    uppercaseReq.classList.remove('valid');
                    uppercaseReq.querySelector('i').className = 'fas fa-times';
                }

               
                if (/[@#$]/.test(password)) {
                    specialReq.classList.add('valid');
                    specialReq.classList.remove('invalid');
                    specialReq.querySelector('i').className = 'fas fa-check';
                } else {
                    specialReq.classList.add('invalid');
                    specialReq.classList.remove('valid');
                    specialReq.querySelector('i').className = 'fas fa-times';
                }
            });

            
            const errorAlert = document.querySelector('.alert-danger');
            if (errorAlert && errorAlert.textContent.includes('Please wait')) {
                const match = errorAlert.textContent.match(/(\d+) seconds/);
                if (match) {
                    let remainingTime = parseInt(match[1]);
                    const loginButton = document.getElementById('loginButton');
                    
                   
                    loginButton.disabled = true;
                    loginButton.style.background = 'var(--gray-400)';
                    loginButton.style.cursor = 'not-allowed';
                    
                   
                    const countdownInterval = setInterval(() => {
                        remainingTime--;
                        
                        if (remainingTime > 0) {
                            errorAlert.innerHTML = `<i class="fas fa-exclamation-circle"></i> Too many failed login attempts. Please wait <span style="font-weight: bold; color: var(--danger-color);">${remainingTime}</span> seconds before trying again.`;
                        } else {
                            clearInterval(countdownInterval);
                            errorAlert.style.display = 'none';
                            loginButton.disabled = false;
                            loginButton.style.background = '';
                            loginButton.style.cursor = 'pointer';
                        }
                    }, 1000);
                }
            }
        });
    </script>
</body>
</html>