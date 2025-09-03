<?php
include_once '../includes/header.php';

checkUserRole('librarian');

$message = '';
$messageType = '';

$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    if (empty($name) || empty($email) || empty($phone)) {
        $message = "Name, email, and phone number are required fields.";
        $messageType = "danger";
    } elseif (!preg_match('/^[0-9]{10,13}$/', $phone)) {
        $message = "Phone number must be 10-13 digits.";
        $messageType = "danger";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = "This email is already in use by another user.";
            $messageType = "danger";
        } else {
            
            $stmt = $conn->prepare("
                UPDATE users 
                SET name = ?, email = ?, phone = ?
                WHERE id = ?
            ");
            
            $stmt->bind_param(
                "sssi",
                $name, $email, $phone, $userId
            );
            
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
    } elseif ($newPassword != $confirmPassword) {
        $message = "New password and confirmation do not match.";
        $messageType = "danger";
    } elseif (strlen($newPassword) < 8) {
        $message = "New password must be at least 8 characters long.";
        $messageType = "danger";
    } elseif (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[^a-zA-Z0-9]/', $newPassword)) {
        $message = "Password must contain at least one uppercase letter and one special character.";
        $messageType = "danger";
    } else {
        
        if (password_verify($currentPassword, $user['password'])) {
            
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
        } else {
            $message = "Current password is incorrect.";
            $messageType = "danger";
        }
    }
}
?>

<h1 class="page-title">My Profile</h1>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="dashboard-row">
   
    <div class="dashboard-col">
        <div class="card">
            <div class="card-header">
                <h3>Profile Information</h3>
            </div>
            <div class="card-body">
                <form action="" method="POST" id="profileForm">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="name">Full Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" 
                                       pattern="^[A-Za-z\s]+$"
                                       title="Only letters and spaces are allowed"
                                       oninput="validateName(this)"
                                       required>
                                <small id="nameWarning" class="validation-warning" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i> Only letters and spaces are allowed
                                </small>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="email">Email <span class="text-danger">*</span></label>
                                <input type="email" id="email" name="email" class="form-control" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                                       pattern="[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$"
                                       title="Please enter a valid email address"
                                       oninput="validateEmail(this)"
                                       required>
                                <small id="emailWarning" class="validation-warning" style="display: none;">
                                    <i class="fas fa-exclamation-triangle"></i> Please enter a valid email format (e.g., name@example.com)
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($user['phone']); ?>" 
                               pattern="^\d{10,13}$"
                               title="Phone number must be 10-13 digits"
                               oninput="validatePhone(this)"
                               maxlength="13"
                               required>
                        <small id="phoneWarning" class="validation-warning" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i> Phone number must be 11 digits only
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Account Created</label>
                        <input type="text" class="form-control" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" readonly>
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
        
<style>
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

<script>
function validateName(input) {
    const namePattern = /^[A-Za-z\s]+$/;
    const warning = document.getElementById('nameWarning');

    const originalValue = input.value;
    const sanitizedValue = originalValue.replace(/[^A-Za-z\s]/g, '');
    
    if (originalValue !== sanitizedValue) {
        input.value = sanitizedValue;
    }
    
    if (!namePattern.test(input.value) && input.value.length > 0) {
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

function validatePhone(input) {
    const phonePattern = /^\d{11}$/;
    const warning = document.getElementById('phoneWarning');

    const originalValue = input.value;
    const sanitizedValue = originalValue.replace(/[^0-9]/g, '');
    
    if (originalValue !== sanitizedValue) {
        input.value = sanitizedValue;
    }
    
    if (!phonePattern.test(input.value) && input.value.length > 0) {
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