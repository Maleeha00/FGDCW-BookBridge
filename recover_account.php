<?php
session_start();
include 'includes/config.php';

$message = '';
$messageType = '';
$step = 1; // Step 1: Enter recovery info, Step 2: Show results

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['step']) && $_POST['step'] == '1') {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $department = trim($_POST['department']);
        
        // Validate input
        if (empty($name)) {
            $message = "Please enter your full name";
            $messageType = "danger";
        } else {
            // Search for user with provided information
            $searchConditions = ["name LIKE ?"];
            $searchParams = ["%$name%"];
            $paramTypes = "s";
            
            // Add phone condition if provided
            if (!empty($phone)) {
                $searchConditions[] = "phone = ?";
                $searchParams[] = $phone;
                $paramTypes .= "s";
            }
            
            // Add department condition if provided
            if (!empty($department)) {
                $searchConditions[] = "department LIKE ?";
                $searchParams[] = "%$department%";
                $paramTypes .= "s";
            }
            
            $sql = "SELECT unique_id, email, name, department, phone, role FROM users WHERE " . implode(" AND ", $searchConditions);
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($paramTypes, ...$searchParams);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $users = [];
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
                $step = 2;
            } else {
                $message = "No account found with the provided information. Please check your details and try again.";
                $messageType = "danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="icon" type="image/png" href="uploads/assests/book.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Additional styles for recovery page */
        .user-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .info-card {
            background: var(--gray-100);
            border-radius: var(--border-radius);
            padding: 15px;
            border: 1px solid var(--gray-300);
            transition: var(--transition);
        }

        .info-card:hover {
            background: var(--gray-200);
            transform: translateY(-2px);
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .info-label {
            font-weight: 600;
            color: var(--text-light);
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-value-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-value {
            color: var(--text-color);
            background: var(--white);
            padding: 10px 15px;
            border-radius: var(--border-radius);
            font-family: 'Courier New', monospace;
            font-weight: 600;
            font-size: 1em;
            border: 2px solid var(--gray-300);
            flex: 1;
            word-break: break-all;
        }

        .user-result-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--gray-300);
        }

        .user-result-title {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            color: var(--primary-color);
            font-size: 1.3em;
            font-weight: 600;
        }

        .user-badge {
            background: var(--primary-color);
            color: var(--white);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .login-prompt {
            background: linear-gradient(135deg, rgba(139, 94, 60, 0.1) 0%, rgba(92, 59, 39, 0.1) 100%);
            border: 2px solid var(--primary-color);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin: 25px 0;
        }

        .login-prompt h3 {
            color: var(--primary-color);
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .login-prompt p {
            color: var(--text-color);
            margin: 0;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .user-info-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .info-value-container {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .user-result-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
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
        <div class="auth-container large">
            <div class="auth-header">
                <h1>
                    
                    Account Recovery
                </h1>
                <p>Find your forgotten ID or Email</p>
            </div>
            
            <div class="auth-body">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step">
                        <div class="step-number <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                            <?php echo $step > 1 ? '<i class="fas fa-check"></i>' : '1'; ?>
                        </div>
                        <span class="step-text <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">Enter Details</span>
                    </div>
                    <div class="step-line <?php echo $step > 1 ? 'completed' : ''; ?>"></div>
                    <div class="step">
                        <div class="step-number <?php echo $step >= 2 ? 'active' : ''; ?>">
                            <?php echo $step >= 2 ? '<i class="fas fa-eye"></i>' : '2'; ?>
                        </div>
                        <span class="step-text <?php echo $step >= 2 ? 'active' : ''; ?>">View Results</span>
                    </div>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : ($messageType == 'info' ? 'info-circle' : 'exclamation-circle'); ?>"></i>
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($step == 1): ?>
                    

                    <form method="POST" action="">
                        <input type="hidden" name="step" value="1">
                        
                        <div class="form-group">
                            <label for="name">
                                
                                Full Name *
                            </label>
                            <input type="text" id="name" name="name" placeholder="Enter your full name as registered" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="phone">
                                         
                                        Phone Number
                                    </label>
                                    <input type="tel" id="phone" name="phone" placeholder="Your registered phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="department">
                                        
                                        Department
                                    </label>
                                    <input type="text" id="department" name="department" placeholder="Your department" value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-auth">
                            <i class="fas fa-search"></i> 
                            Search Account
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($step == 2 && isset($users)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Great! Found <?php echo count($users); ?> account(s) matching your information.
                    </div>

                    <div class="results-container">
                        <div class="results-header">
                            <h2><i class="fas fa-user-check"></i> Your Account Details</h2>
                        </div>

                        <?php foreach ($users as $index => $user): ?>
                            <div class="user-result">
                                <div class="user-result-header">
                                    <h3 class="user-result-title">
                                        <i class="fas fa-user-circle"></i>
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </h3>
                                    <span class="user-badge"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
                                </div>
                                
                                <div class="user-info-grid">
                                    <div class="info-card">
                                        <div class="info-item">
                                            <span class="info-label">
                                                <i class="fas fa-id-card"></i> Unique ID
                                            </span>
                                            <div class="info-value-container">
                                                <span class="info-value" id="uid-<?php echo $index; ?>"><?php echo htmlspecialchars($user['unique_id']); ?></span>
                                                <button class="copy-btn" onclick="copyToClipboard('uid-<?php echo $index; ?>', this)">
                                                    <i class="fas fa-copy"></i> Copy
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-card">
                                        <div class="info-item">
                                            <span class="info-label">
                                                <i class="fas fa-envelope"></i> Email Address
                                            </span>
                                            <div class="info-value-container">
                                                <span class="info-value" id="email-<?php echo $index; ?>"><?php echo htmlspecialchars($user['email']); ?></span>
                                                <button class="copy-btn" onclick="copyToClipboard('email-<?php echo $index; ?>', this)">
                                                    <i class="fas fa-copy"></i> Copy
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (!empty($user['department'])): ?>
                                    <div class="info-card">
                                        <div class="info-item">
                                            <span class="info-label">
                                                <i class="fas fa-building"></i> Department
                                            </span>
                                            <div class="info-value-container">
                                                <span class="info-value"><?php echo htmlspecialchars($user['department']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($user['phone'])): ?>
                                    <div class="info-card">
                                        <div class="info-item">
                                            <span class="info-label">
                                                <i class="fas fa-phone"></i> Phone Number
                                            </span>
                                            <div class="info-value-container">
                                                <span class="info-value"><?php echo htmlspecialchars($user['phone']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="login-prompt">
                        <h3><i class="fas fa-lightbulb"></i> Ready to Login?</h3>
                        <p>You can now use either your <strong>Unique ID</strong> or <strong>Email</strong> to access your account.</p>
                    </div>

                    <a href="index.php" class="btn-auth">
                        <i class="fas fa-sign-in-alt"></i> 
                        Go to Login Page
                    </a>
                <?php endif; ?>
                
                <div style="text-align: center;">
                    <a href="index.php" class="btn-link-auth">
                        <i class="fas fa-arrow-left"></i> 
                        Back to Login
                    </a>
                    <?php if ($step == 1): ?>
                        <a href="forgot_password.php" class="btn-link-auth">
                            <i class="fas fa-key"></i> 
                            Forgot Password?
                        </a>
                        <a href="register.php" class="btn-link-auth">
                            <i class="fas fa-user-plus"></i> 
                            Create New Account
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="auth-footer">
                <p>&copy; 2025 Book Bridge. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(elementId, buttonElement) {
            const element = document.getElementById(elementId);
            const text = element.textContent;
            
            navigator.clipboard.writeText(text).then(function() {
                // Update button to show success
                const originalHTML = buttonElement.innerHTML;
                buttonElement.innerHTML = '<i class="fas fa-check"></i> Copied!';
                buttonElement.classList.add('copied');
                
                // Reset button after 2 seconds
                setTimeout(function() {
                    buttonElement.innerHTML = originalHTML;
                    buttonElement.classList.remove('copied');
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                alert('Failed to copy. Please select and copy manually.');
            });
        }

        // Auto-focus on name field when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const nameField = document.getElementById('name');
            if (nameField) {
                nameField.focus();
            }
        });
    </script>
</body>
</html>