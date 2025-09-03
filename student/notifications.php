<?php
include_once '../includes/header.php';


if ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'faculty') {
    header('Location: ../index.php');
    exit();
}

$userId = $_SESSION['user_id'];

if (isset($_GET['mark_read']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);
    $stmt->execute();
}

$notifications = [];
$sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
}
?>

<div class="container">
    <h1 class="page-book_name" style="margin-bottom: 30px;">My Notifications</h1>

    <div class="card" >
        <div class="card-body" >
            <?php if (count($notifications) > 0): ?>
                <div class="notification-list">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                            <div class="notification-content">
                                <div class="notification-message">
                                    <?php echo htmlspecialchars($notification['message']); ?>
                                </div>
                                <div class="notification-meta">
                                    <span class="notification-time">
                                        <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                                    </span>
                                    <?php if (!$notification['is_read']): ?>
                                        <span class="badge badge-primary">New</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="notification-actions">
                                <?php if (!$notification['is_read']): ?>
                                    <a href="?mark_read=1&id=<?php echo $notification['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-check"></i> Mark as Read
                                    </a>
                                <?php endif; ?>
                                <a href="?delete=<?php echo $notification['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this notification?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center">No notifications found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.notification-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
     max-height: 70vh;
}

.notification-item {
    background: var(--white);
    border-radius: var(--border-radius);
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border: 1px solid var(--gray-200);
    transition: var(--transition);
}

.notification-item.unread {
    background-color: rgba(13, 71, 161, 0.05);
    border-left: 3px solid var(--primary-color);
}

.notification-content {
    flex: 1;
    margin-right: 15px;
}

.notification-message {
    margin-bottom: 5px;
}

.notification-meta {
    font-size: 0.9em;
    color: var(--text-light);
    display: flex;
    align-items: center;
    gap: 10px;
}

.notification-actions {
    display: flex;
    gap: 10px;
}
</style>

<?php include_once '../includes/footer.php'; ?>