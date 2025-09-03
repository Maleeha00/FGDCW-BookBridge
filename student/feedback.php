<?php
include_once '../includes/header.php';
if ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'faculty') {
    header('Location: ../index.php');
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userId = $_SESSION['user_id'];
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $type = $_POST['feedback_type'];
    
    if (empty($subject) || empty($message)) {
        $message = "Please fill in all required fields.";
        $messageType = "danger";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO feedback (user_id, subject, message, type)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $userId, $subject, $message, $type);
        
        if ($stmt->execute()) {
            $message = "Thank you for your feedback!";
            $messageType = "success";
        } else {
            $message = "Error submitting feedback: " . $stmt->error;
            $messageType = "danger";
        }
    }
}

$conn->query($sql);
?>

<div class="container">
    <h1 class="page-book_name">Provide Feedback</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <form action="" method="POST">
                <div class="form-group">
                    <label for="feedback_type">Feedback Type</label>
                    <select id="feedback_type" name="feedback_type" class="form-control" required>
                        <option value="suggestion">Suggestion</option>
                        <option value="complaint">Complaint</option>
                        <option value="appreciation">Appreciation</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject">Subject of the feedback</label>
                    <input type="text" id="subject" name="subject" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="message">Your Feedback</label>
                    <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                </div>

                <div class="form-group text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php
    $stmt = $conn->prepare("
        SELECT * FROM feedback 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    ?>

    <?php if ($result->num_rows > 0): ?>
        <div class="card mt-4">
            <div class="card-header">
                <h3>Your Previous Feedback</h3>
            </div>
            <div class="card-body">
                <div class="feedback-list">
                    <?php while ($feedback = $result->fetch_assoc()): ?>
                        <div class="feedback-item">
                            <div class="feedback-header">
                                <span class="feedback-type badge badge-<?php 
                                    echo $feedback['type'] == 'suggestion' ? 'primary' : 
                                        ($feedback['type'] == 'complaint' ? 'danger' : 'success'); 
                                ?>">
                                    <?php echo ucfirst($feedback['type']); ?>
                                </span>
                                <span class="feedback-date">
                                    <?php echo date('M d, Y H:i', strtotime($feedback['created_at'])); ?>
                                </span>
                            </div>
                            <h4 class="feedback-subject"><?php echo htmlspecialchars($feedback['subject']); ?></h4>
                            <div class="feedback-message">
                                <?php echo nl2br(htmlspecialchars($feedback['message'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.feedback-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.feedback-item {
    background: var(--gray-100);
    border-radius: var(--border-radius);
    padding: 20px;
    transition: var(--transition);
}

.feedback-item:hover {
    transform: translateY(-2px);
    box-shadow: var(--box-shadow);
}

.feedback-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.feedback-type {
    text-transform: capitalize;
}

.feedback-date {
    color: var(--text-light);
    font-size: 0.9em;
}

.feedback-subject {
    margin: 0 0 10px 0;
    color: var(--primary-color);
}

.feedback-message {
    color: var(--text-color);
    white-space: pre-line;
}
</style>

<?php include_once '../includes/footer.php'; ?>