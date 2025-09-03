<?php
include_once '../includes/header.php';

if ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'faculty') {
    header('Location: ../index.php');
    exit();
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $class = trim($_POST['class']);
    $phone = trim($_POST['phone']);

    if (empty($name) || empty($email)) {
        $message = "Name and email are required fields.";
        $messageType = "danger";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $message = "Name can only contain letters and spaces.";
        $messageType = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $messageType = "danger";
    } elseif (!preg_match("/^\d{11}$/", $phone)) {
        $message = "Phone number must be exactly 11 digits.";
        $messageType = "danger";
    } else {
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "This email is already in use.";
            $messageType = "danger";
        } else {
            
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, class = ?, phone = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $email, $class, $phone, $userId);

            if ($stmt->execute()) {
                $_SESSION['name'] = $name;
                $_SESSION['email'] = $email;
                $message = "Profile updated successfully.";
                $messageType = "success";
                
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $message = "Error updating profile: " . $stmt->error;
                $messageType = "danger";
            }
        }
    }
}

if (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $message = "All password fields are required.";
        $messageType = "danger";
    } elseif ($newPassword !== $confirmPassword) {
        $message = "New passwords do not match.";
        $messageType = "danger";
    } elseif (strlen($newPassword) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = "danger";
    } else {
        
        if (password_verify($currentPassword, $user['password'])) {

            if (password_verify($newPassword, $user['password'])) {
                $message = "New password cannot be the same as your current password.";
                $messageType = "danger";
            } else {
               
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashedPassword, $userId);

                if ($stmt->execute()) {
                    $message = "Password changed successfully.";
                    $messageType = "success";
                } else {
                    $message = "Error changing password: " . $stmt->error;
                    $messageType = "danger";
                }
            }
        } else {
            $message = "Current password is incorrect.";
            $messageType = "danger";
        }
    }
}

?>

<style>
.form-group input,
.form-group select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 1rem;
    box-sizing: border-box;
    margin-top: 4px;
    margin-bottom: 10px;
    transition: border-color 0.2s;
}
.form-group input:focus,
.form-group select:focus {
    border-color: #007bff;
    outline: none;
}
.unique-id-display {
    background: #e3f2fd;
    border: 2px solid #2196f3;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    text-align: center;
}
.unique-id-display h3 {
    color: #1976d2;
    margin: 0 0 10px 0;
}
.unique-id-display .id-value {
    font-size: 1.5em;
    font-weight: bold;
    color: #0d47a1;
    background: white;
    padding: 10px;
    border-radius: 8px;
    margin: 10px 0;
    letter-spacing: 2px;
    font-family: monospace;
}
.unique-id-display .copy-btn {
    background: #2196f3;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 5px;
    cursor: pointer;
    margin: 5px;
    font-size: 0.9em;
}
.unique-id-display .copy-btn:hover {
    background: #1976d2;
}
.unique-id-display p {
    margin: 10px 0 0 0;
    color: #666;
    font-size: 0.9em;
}
.validation-warning {
    color: #dc3545;
    font-size: 0.875em;
    margin-top: 5px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.validation-message {
    font-size: 0.875em;
    margin-top: 5px;
    font-weight: 600;
}

.validation-message.success {
    color: #28a745;
}

.validation-message.error {
    color: #dc3545;
}

.password-requirements {
    background: var(--gray-100);
    border: 1px solid var(--gray-300);
    border-radius: var(--border-radius);
    padding: 15px;
    margin-top: 10px;
    font-size: 0.9em;
    animation: fadeIn 0.3s ease-out;
}

.password-requirements h4 {
    margin: 0 0 10px 0;
    color: var(--text-color);
    font-size: 0.95em;
    font-weight: 600;
}

.requirement {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    color: var(--text-light);
    transition: var(--transition);
}

.requirement i {
    margin-right: 8px;
    width: 14px;
    transition: var(--transition);
}

.requirement.valid {
    color: var(--success-color);
}

.requirement.invalid {
    color: var(--danger-color);
}

.form-control.invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
}

.form-control.valid {
    border-color: #28a745;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

</style>

<div class="container">
    <h1 class="page-book_name">My Profile</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="unique-id-display">
        <h3><i class="fas fa-id-card"></i> Your Unique ID</h3>
        <div class="id-value" id="uniqueId"><?php echo htmlspecialchars($user['unique_id']); ?></div>
        <button type="button" class="copy-btn" onclick="copyToClipboard()">
            <i class="fas fa-copy"></i> Copy ID
        </button>
        <p>Use this ID or your email to login to the system</p>
    </div>

    <div class="dashboard-row">
        <div class="dashboard-col">
            <div class="card">
                <div class="card-header">
                    <h3>Profile Information</h3>
                </div>
                <div class="card-body">
                    <form action="" method="POST">
                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name"
                                           pattern="^[A-Za-z\s]+$"
                                           book_name="Only letters and spaces are allowed"
                                           oninput="this.value = this.value.replace(/[^A-Za-z\s]/g, '')"
                                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email"
                                           value="<?php echo htmlspecialchars($user['email']); ?>"
                                           pattern="[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$"
                                           book_name="Please enter a valid email (e.g. abc@gmail.com)"
                                           required oninput="validateEmail(this)">
                                    <small id="emailWarning" style="color: red; display: none;">Invalid email format. e.g. example@gmail.com</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="class">Class</label>
                                    <select id="class" name="class" required <?php echo ($user['role'] == 'faculty') ? 'disabled' : ''; ?>>
                                        <?php
                                        $classs = [
                                            "BS IT-1","BS IT-2","BS IT-3","BS IT-4","BS IT-5","BS IT-6","BS IT-7","BS IT-8",
                                            "BS ENG-1","BS ENG-2","BS ENG-3","BS ENG-4","BS ENG-5","BS ENG-6","BS ENG-7","BS ENG-8",
                                            "BS HPE-1","BS HPE-2","BS HPE-3","BS HPE-4","BS HPE-5","BS HPE-6","BS HPE-7","BS HPE-8",
                                            "1st Year Pre Engineering", "2nd Year Pre Engineering",
                                            "1st Year Pre Medical", "2nd Year Pre Medical",
                                            "1st Year Arts", "2nd Year Arts", "1st Year ICS", "2nd Year ICS"
                                        ];
                                        foreach ($classs as $dept) {
                                            $selected = ($user['class'] == $dept) ? 'selected' : '';
                                            echo "<option value=\"$dept\" $selected>$dept</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-col">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone"
                                           value="<?php echo htmlspecialchars($user['phone']); ?>"
                                           pattern="^\d{11}$"
                                           book_name="Enter exactly 11 digit phone number"
                                           oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                           maxlength="11" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" value="<?php echo ucfirst($user['role']); ?>" readonly style="background-color: #f5f5f5;">
                        </div>

                        <div class="form-group">
                            <label>Account Created</label>
                            <input type="text" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" readonly style="background-color: #f5f5f5;">
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

       
    <div class="dashboard-col">
        <div class="card">
            <div class="card-header">
                <h3>Change Password</h3>
            </div>
            <div class="card-body">
                <form action="" method="POST" id="passwordForm">
                    <div class="form-group">
                        <label for="current_password">Current Password <span class="text-danger">*</span></label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password <span class="text-danger">*</span></label>
                        <input type="password" id="new_password" name="new_password" class="form-control" 
                               oninput="validatePassword(this)" 
                               onfocus="showPasswordRequirements()" 
                               onblur="hidePasswordRequirements()" 
                               required>
                        <div class="password-requirements" id="passwordRequirements" style="display: none;">
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
                                <span>At least one special character (@, #, $, etc.)</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                               oninput="validatePasswordMatch()" required>
                        <small id="passwordMatchMessage" class="validation-message"></small>
                    </div>
                    
                    <div class="form-group text-right">
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

<script>
function copyToClipboard() {
    const uniqueId = document.getElementById('uniqueId').textContent;
    navigator.clipboard.writeText(uniqueId).then(function() {
        const btn = document.querySelector('.copy-btn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.style.background = '#4caf50';
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.style.background = '#2196f3';
        }, 2000);
    });
}

function validateEmail(input) {
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const warning = document.getElementById('emailWarning');
    
    if (!emailPattern.test(input.value) && input.value.length > 0) {
        warning.style.display = 'block';
        input.classList.add('invalid');
        input.classList.remove('valid');
    } else if (input.value.length > 0) {
        warning.style.display = 'none';
        input.classList.add('valid');
        input.classList.remove('invalid');
    } else {
        warning.style.display = 'none';
        input.classList.remove('valid', 'invalid');
    }
}

// Password validation
function validatePassword(input) {
    const password = input.value;
    const lengthReq = document.getElementById('length-req');
    const uppercaseReq = document.getElementById('uppercase-req');
    const specialReq = document.getElementById('special-req');
    
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
   
    if (/[@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
        specialReq.classList.add('valid');
        specialReq.classList.remove('invalid');
        specialReq.querySelector('i').className = 'fas fa-check';
    } else {
        specialReq.classList.add('invalid');
        specialReq.classList.remove('valid');
        specialReq.querySelector('i').className = 'fas fa-times';
        isValid = false;
    }
    
    if (password.length > 0) {
        if (isValid) {
            input.classList.add('valid');
            input.classList.remove('invalid');
        } else {
            input.classList.add('invalid');
            input.classList.remove('valid');
        }
    } else {
        input.classList.remove('valid', 'invalid');
    }
    
    validatePasswordMatch();
}

function validatePasswordMatch() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const message = document.getElementById('passwordMatchMessage');
    
    if (confirmPassword === '') {
        message.textContent = '';
        message.className = 'validation-message';
        return;
    }
    
    if (newPassword === confirmPassword) {
        message.textContent = '✓ Passwords match';
        message.className = 'validation-message success';
        document.getElementById('confirm_password').classList.add('valid');
        document.getElementById('confirm_password').classList.remove('invalid');
    } else {
        message.textContent = '✗ Passwords do not match';
        message.className = 'validation-message error';
        document.getElementById('confirm_password').classList.add('invalid');
        document.getElementById('confirm_password').classList.remove('valid');
    }
}

function showPasswordRequirements() {
    const requirements = document.getElementById('passwordRequirements');
    requirements.style.display = 'block';
}

function hidePasswordRequirements() {
    setTimeout(() => {
        const requirements = document.getElementById('passwordRequirements');
        const confirmField = document.getElementById('confirm_password');
      
        if (document.activeElement !== confirmField) {
            requirements.style.display = 'none';
        }
    }, 200);
}

document.addEventListener('DOMContentLoaded', function() {
    
    const profileForm = document.getElementById('profileForm');
    
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const phoneInput = document.getElementById('phone');
            
            let isValid = true;
            
            if (!/^[A-Za-z\s]+$/.test(nameInput.value)) {
                isValid = false;
                nameInput.classList.add('invalid');
                document.getElementById('nameWarning').style.display = 'block';
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value)) {
                isValid = false;
                emailInput.classList.add('invalid');
                document.getElementById('emailWarning').style.display = 'block';
            }
            
            if (!/^\d{10,13}$/.test(phoneInput.value)) {
                isValid = false;
                phoneInput.classList.add('invalid');
                document.getElementById('phoneWarning').style.display = 'block';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fix the validation errors before submitting.');
            }
        });
    }
    
    const passwordForm = document.getElementById('passwordForm');
    
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword.length < 8 || 
                !/[A-Z]/.test(newPassword) || 
                !/[@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(newPassword)) {
                e.preventDefault();
                alert('Password does not meet the requirements. Please check the requirements and try again.');
                return;
            }
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                return;
            }
        });
    }
});
</script>

<?php

include_once '../includes/footer.php';
?>

