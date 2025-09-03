<?php
include_once '../includes/header.php';
checkUserRole('librarian');
$message = '';
$messageType = '';
if (isset($_POST['send_notification'])) {
    $userIds = $_POST['user_ids'];
    $notificationMsg = trim($_POST['message']);
    
    if (empty($userIds) || empty($notificationMsg)) {
        $message = "Please select at least one user and enter a message.";
        $messageType = "danger";
    } else {
        
        $conn->begin_transaction();
        try {
            
            $successCount = 0;
            
            foreach ($userIds as $userId) {
                if (sendNotification($conn, $userId, $notificationMsg)) {
                    $successCount++;
                }
            }
            $conn->commit();
            
            if ($successCount > 0) {
                $message = "Notification sent successfully to {$successCount} users.";
                $messageType = "success";
            } else {
                $message = "No notifications were sent.";
                $messageType = "warning";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error sending notifications: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

$users = [];
$userSql = "SELECT id, name, email, role FROM users ORDER BY name";
$userResult = $conn->query($userSql);
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
}
?>
<h1 class="page-title">Notifications</h1>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="dashboard-row">
    
    <div class="dashboard-col">
        <div class="card">
            <div class="card-header">
                <h3>Send New Notification</h3>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="user_ids">Select Recipients <span class="text-danger">*</span></label>
                        <div class="user-selection-container">
                            <div class="selection-options">
                                <a href="#" onclick="selectAllUsers(); return false;">Select All</a> | 
                                <a href="#" onclick="deselectAllUsers(); return false;">Deselect All</a> | 
                                <a href="#" onclick="selectUsersByRole('student'); return false;">All Students</a> | 
                                <a href="#" onclick="selectUsersByRole('faculty'); return false;">All Faculty</a>
                            </div>
                            <div class="form-group">
    <label for="user_search">Search Users</label>
    <input type="text" id="user_search" class="form-control" placeholder="Type name or email to search..." onkeyup="filterUsers()">
</div>
<div class="user-selection-container">
    
    <select id="user_ids" name="user_ids[]" multiple class="form-control" style="height: 150px;" required>
        <?php foreach ($users as $user): ?>
            <option value="<?php echo $user['id']; ?>" data-role="<?php echo $user['role']; ?>">
                <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>) - <?php echo ucfirst($user['role']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Notification Message <span class="text-danger">*</span></label>
                        <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                    </div>
                    
                    <div class="form-group text-right">
                        <button type="submit" name="send_notification" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Send Notification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
function selectAllUsers() {
    const select = document.getElementById('user_ids');
    for (let i = 0; i < select.options.length; i++) {
        select.options[i].selected = true;
    }
}

function deselectAllUsers() {
    const select = document.getElementById('user_ids');
    for (let i = 0; i < select.options.length; i++) {
        select.options[i].selected = false;
    }
}

function selectUsersByRole(role) {
    const select = document.getElementById('user_ids');
    for (let i = 0; i < select.options.length; i++) {
        if (select.options[i].getAttribute('data-role') === role) {
            select.options[i].selected = true;
        } else {
            select.options[i].selected = false;
        }
    }
}

function filterUsers() {
    const search = document.getElementById('user_search').value.toLowerCase();
    const select = document.getElementById('user_ids');
    for (let i = 0; i < select.options.length; i++) {
        const text = select.options[i].text.toLowerCase();
        select.options[i].style.display = text.includes(search) ? '' : 'none';
    }
}
</script>
<?php

include_once '../includes/footer.php';
?>