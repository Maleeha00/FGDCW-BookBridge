<?php
session_start();
include 'includes/config.php';
$message = '';
$messageType = '';
$generatedId = '';
function validatePassword($password) {
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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = $_POST['role'];
    $class = trim($_POST['class']);
    $phone = trim($_POST['phone']);

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $message = "All required fields must be filled out";
        $messageType = "danger";
        } elseif (substr($email, -10) !== '@gmail.com') {
    $message = "Email must end with @gmail.com";
    $messageType = "danger";

    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match";
        $messageType = "danger";
    } else {
        $passwordErrors = validatePassword($password);
        if (!empty($passwordErrors)) {
            $message = implode(". ", $passwordErrors);
            $messageType = "danger";
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $message = "Email already registered";
                $messageType = "danger";
            } else {
                $uniqueId = generateUniqueId($conn, $role);
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
                
                $stmt = $conn->prepare("INSERT INTO users (unique_id, name, email, password, role, class, phone, approval_status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->bind_param("sssssss", $uniqueId, $name, $email, $hashedPassword, $role, $class, $phone);

                if ($stmt->execute()) {
                    $generatedId = $uniqueId;
                    $message = "Important:</strong> Your account is pending librarian approval. You will receive an email once approved.";
                    $messageType = "success";
                } else {
                    $message = "Error registering user: " . $stmt->error;
                    $messageType = "danger";
                }
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
    <title>Register </title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="icon" type="image/png" href="uploads/assests/book.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
   <nav class="auth-navbar">
    <div class="container" style="display: flex;">
        <a href="index.php" class="auth-logo" style="display: flex;">
            <img src="uploads/assests/book.png" alt="Library Logo" style="height: 40px;">
            <span style="font-family: 'Brush Script MT', serif; font-size: 24px; font-weight: bold; color: #5C4033;">BookBridge</span>
        </a>
        <div class="auth-nav-links" style="margin-left: auto;">
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
                <h1>Register</h1>
            </div>
            <div class="auth-body">
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                        <?php echo $message; ?>
                    </div>
                    <?php if ($messageType == 'success' && !empty($generatedId)): ?>
                        <div class="unique-id-display">
                            <h3><i class="fas fa-id-card"></i> Your Unique ID</h3>
                            <div class="id-value" id="uniqueId"><?php echo $generatedId; ?></div>
                            <button type="button" class="copy-btn" onclick="copyToClipboard()">
                                <i class="fas fa-copy"></i> Copy ID
                            </button>
                            <p><strong>Important:</strong> Save this ID safely. You can use either your email or this unique ID to login.</p>
                            <a href="index.php" class="btn-auth" style="display: inline-block; margin-top: 15px; text-decoration: none; width: auto; padding: 12px 24px;">
                                <i class="fas fa-sign-in-alt"></i> Go to Login
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($messageType != 'success'): ?>
                <form method="POST" action="" id="registerForm">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="name">Full Name </label>
                                <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                                <small id="nameWarning" style="color: red; display: none;">Only alphabets and spaces are allowed.</small>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="email">Email Address </label>
                                <input type="email" id="email" name="email" placeholder="Enter your email" required>
                                <small id="emailWarning" style="color: red; display: none;">Enter valid email address. <br> email@gmail.com</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="role">Role </label>
                        <select id="role" name="role" required>
                            <option value="">Select your role</option>
                            <option value="student">Student</option>
                            <option value="faculty">Faculty Member</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="class">Class</label>
                                <select id="class" name="class" required>
                                    <option value="">Select your class</option>
                                    <option value="BS IT-1">BS IT-1</option>
                                    <option value="BS IT-2">BS IT-2</option>
                                    <option value="BS IT-3">BS IT-3</option>
                                    <option value="BS IT-4">BS IT-4</option>
                                    <option value="BS IT-5">BS IT-5</option>
                                    <option value="BS IT-6">BS IT-6</option>
                                    <option value="BS IT-7">BS IT-7</option>
                                    <option value="BS IT-8">BS IT-8</option>
                                    <option value="BS ENG-1">BS ENG-1</option>
                                    <option value="BS ENG-2">BS ENG-2</option>
                                    <option value="BS ENG-3">BS ENG-3</option>
                                    <option value="BS ENG-4">BS ENG-4</option>
                                    <option value="BS ENG-5">BS ENG-5</option>
                                    <option value="BS ENG-6">BS ENG-6</option>
                                    <option value="BS ENG-7">BS ENG-7</option>
                                    <option value="BS ENG-8">BS ENG-8</option>
                                    <option value="BS HPE-1">BS HPE-1</option>
                                    <option value="BS HPE-2">BS HPE-2</option>
                                    <option value="BS HPE-3">BS HPE-3</option>
                                    <option value="BS HPE-4">BS HPE-4</option>
                                    <option value="BS HPE-5">BS HPE-5</option>
                                    <option value="BS HPE-6">BS HPE-6</option>
                                    <option value="BS HPE-7">BS HPE-7</option>
                                    <option value="BS HPE-8">BS HPE-8</option>
                                    <option value="1st Year Pre Engineering">1st Year Pre Engineering</option>
                                    <option value="2nd Year Pre Engineering">2nd Year Pre Engineering</option>
                                    <option value="1st Year Pre Medical">1st Year Pre Medical</option>
                                    <option value="2nd Year Pre Medical ">2nd Year Pre Medical</option>
                                    <option value="1st Year Arts">1st Year Arts</option>
                                    <option value="2nd Year Arts">2nd Year Arts</option>
                                    <option value="1st Year ICS">1st Year ICS </option>
                                    <option value="2nd Year ICS">2nd Year ICS</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" placeholder="03123456789 ">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="password">Password </label>
                                <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                                <div class="password-requirements" id="passwordRequirements">
                                    <h4>Password Requirements:</h4>
                                    <div class="requirement" id="length-req"><i class="fas fa-times"></i><span>At least 8 characters long</span></div>
                                    <div class="requirement" id="uppercase-req"><i class="fas fa-times"></i><span>At least one uppercase letter (A-Z)</span></div>
                                    <div class="requirement" id="special-req"><i class="fas fa-times"></i><span>At least one special character (@, #, $)</span></div>
                                </div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="confirm_password">Confirm Password </label>
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-auth" id="submitBtn" disabled>Create Account</button>
                    <div style="text-align: center;">
                        <a href="index.php" class="btn-link-auth">
                            <i class="fas fa-sign-in-alt"></i> Already have an account? Sign in
                        </a>
                    </div>
                </form>
                <?php endif; ?>
            </div>

            <div class="auth-footer">
                <p>&copy; 2025 Book Bridge. All rights reserved.</p>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('registerForm').addEventListener('submit', function (e) {
    let emailField = document.getElementById('email');
    let emailValue = emailField.value.trim();
    let emailWarning = document.getElementById('emailWarning');

    if (!emailValue.endsWith("@gmail.com")) {
        e.preventDefault(); 
        emailWarning.style.display = 'block';
        emailWarning.innerText = "Email must end with @gmail.com";
        emailField.focus();
    } else {
        emailWarning.style.display = 'none';
    }
});
        function copyToClipboard() {
            const uniqueId = document.getElementById('uniqueId').textContent;
            navigator.clipboard.writeText(uniqueId).then(function() {
                const btn = document.querySelector('.copy-btn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                btn.classList.add('copied');
                setTimeout(function() {
                    btn.innerHTML = originalText;
                    btn.classList.remove('copied');
                }, 2000);
            });
        }
        document.addEventListener('DOMContentLoaded', function () {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');
    const lengthReq = document.getElementById('length-req');
    const uppercaseReq = document.getElementById('uppercase-req');
    const specialReq = document.getElementById('special-req');
    const passwordRequirements = document.getElementById('passwordRequirements');
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const nameWarning = document.getElementById('nameWarning');
    const emailWarning = document.getElementById('emailWarning');
    const phoneInput = document.getElementById('phone');
    const roleSelect = document.getElementById('role');
    const classSelect = document.getElementById('class');
    const uniqueIdInput = document.getElementById('unique_id'); 

    if (roleSelect && classSelect) {
        roleSelect.addEventListener('change', function () {
            if (this.value === 'faculty') {
                classSelect.disabled = true;
                classSelect.value = '';
            } else {
                classSelect.disabled = false;
            }
        });
    }
    const confirmPasswordMsg = document.createElement('small');
    confirmPasswordMsg.style.display = 'block';
    confirmPasswordMsg.style.marginTop = '5px';
    confirmPasswordMsg.style.fontWeight = 'bold';
    confirmPasswordMsg.style.color = '#d9534f';
    confirmPasswordInput.parentNode.appendChild(confirmPasswordMsg);
    confirmPasswordInput.addEventListener('input', function () {
        if (confirmPasswordInput.value === passwordInput.value && confirmPasswordInput.value.length > 0) {
            confirmPasswordMsg.textContent = ' Password matched';
            confirmPasswordMsg.style.color = 'green';
        } else {
            confirmPasswordMsg.textContent = 'Password does not match';
            confirmPasswordMsg.style.color = 'red';
        }
    });
    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            const original = this.value;
            const sanitized = original.replace(/[^0-9]/g, '').slice(0, 11);
            this.value = sanitized;

            if (!/^03[0-9]{9}$/.test(sanitized)) {
                phoneInput.style.borderColor = 'red';
            } else {
                phoneInput.style.borderColor = 'green';
            }
        });
    }
    if (nameInput) {
        nameInput.addEventListener('input', function () {
            const original = this.value;
            const sanitized = original.replace(/[^a-zA-Z\s]/g, '');
            this.value = sanitized;

            const regex = /^[a-zA-Z\s]*$/;
            nameWarning.style.display = !regex.test(original) ? 'block' : 'none';
        });
    }
    if (emailInput) {
        emailInput.addEventListener('input', function () {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            emailWarning.style.display = !emailPattern.test(this.value) ? 'block' : 'none';
        });
    }
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
    passwordInput.addEventListener('input', function () {
        validatePassword();
        if (this.value.length > 0) {
            passwordRequirements.classList.add('show');
        }
    });
    confirmPasswordInput.addEventListener('input', validatePassword);
    const form = document.getElementById('registerForm');
    if (form) {
        form.addEventListener('submit', function (e) {
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
