<?php
include_once '../includes/header.php';

checkUserRole('librarian');
$message = '';
$messageType = '';

if (isset($_POST['issue_book'])) {
    $bookId = (int)$_POST['book_id'];
    $userId = (int)$_POST['user_id'];
    $returnDate = $_POST['return_date'];
    

    if (empty($bookId) || empty($userId) || empty($returnDate)) {
        $message = "All fields are required.";
        $messageType = "danger";
    } else {
        
        if (canIssueBook($conn, $bookId)) {
            
            $conn->begin_transaction();
            
            try {
                
                $stmt = $conn->prepare("
                    INSERT INTO issued_books (book_id, user_id, return_date)
                    VALUES (?, ?, ?)
                ");
                $stmt->bind_param("iis", $bookId, $userId, $returnDate);
                $stmt->execute();
                
                updateBookAvailability($conn, $bookId, 'issue');
                
                
                $book_name = getBookbook_name($conn, $bookId);
                $userName = getUserName($conn, $userId);
                
                $notificationMsg = "The book '{$book_name}' has been issued to you. Please return it by " . date('F j, Y', strtotime($returnDate)) . ".";
                sendNotification($conn, $userId, $notificationMsg);
                
                $conn->commit();
                
                $message = "Book issued successfully to {$userName}.";
                $messageType = "success";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "Error issuing book: " . $e->getMessage();
                $messageType = "danger";
            }
        } else {
            $message = "This book is not available for issue.";
            $messageType = "danger";
        }
    }
}

$availableBooks = [];
$bookSql = "SELECT id, book_name, author, available_quantity FROM books WHERE available_quantity > 0 ORDER BY book_name";
$bookResult = $conn->query($bookSql);
if ($bookResult) {
    while ($row = $bookResult->fetch_assoc()) {
        $availableBooks[] = $row;
    }
}
$users = [];
$userSql = "SELECT id, name, email, role FROM users WHERE role != 'librarian' ORDER BY name";
$userResult = $conn->query($userSql);
if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
}


?>

<h1 class="page-title">Issue Book</h1>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="dashboard-row">
    
    <div class="dashboard-col">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-hand-holding"></i>Issue New Book</h3>
            </div>
            <div class="card-body">
                <form action="" method="POST" id="issueBookForm">
                    <div class="form-group">
                        <label for="book_id">Select Book <span class="text-danger">*</span></label>
                        <select id="book_id" name="book_id" class="form-control" required>
                            <option value="">Choose a book...</option>
                            <?php foreach ($availableBooks as $book): ?>
                                <option value="<?php echo $book['id']; ?>" data-available="<?php echo $book['available_quantity']; ?>">
                                    <?php echo htmlspecialchars($book['book_name']); ?> by <?php echo htmlspecialchars($book['author']); ?>
                                    (<?php echo $book['available_quantity']; ?> available)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="user_id">Select User <span class="text-danger">*</span></label>
                        <select id="user_id" name="user_id" class="form-control" required>
                            <option value="">Choose a user...</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user['id']; ?>" data-role="<?php echo $user['role']; ?>">
                                    <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>) - <?php echo ucfirst($user['role']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="issue_date">Issue Date</label>
                                <input type="date" id="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="return_date">Return Date <span class="text-danger">*</span></label>
                                <input type="date" id="return_date" name="return_date" class="form-control" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="issue-summary" id="issueSummary" style="display: none;">
                        <div class="summary-card">
                            <h4>Issue Summary</h4>
                            <div class="summary-details">
                                <div class="summary-item">
                                    <span class="label">Book:</span>
                                    <span class="value" id="selectedBook">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="label">User:</span>
                                    <span class="value" id="selectedUser">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="label">Return Date:</span>
                                    <span class="value" id="selectedReturnDate">-</span>
                                </div>
                                <div class="summary-item">
                                    <span class="label">Days:</span>
                                    <span class="value" id="issueDays">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group text-right" style="margin-top: 20px;">
                        <button type="submit" name="issue_book" class="btn btn-primary">
                            <i class="fas fa-hand-holding"></i> Issue Book
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const bookSelect = document.getElementById('book_id');
    const userSelect = document.getElementById('user_id');
    const returnDateInput = document.getElementById('return_date');
    const issueSummary = document.getElementById('issueSummary');
    
    function updateSummary() {
        const selectedBook = bookSelect.options[bookSelect.selectedIndex];
        const selectedUser = userSelect.options[userSelect.selectedIndex];
        const returnDate = returnDateInput.value;
        
        if (bookSelect.value && userSelect.value && returnDate) {
            document.getElementById('selectedBook').textContent = selectedBook.text;
            document.getElementById('selectedUser').textContent = selectedUser.text;
            document.getElementById('selectedReturnDate').textContent = new Date(returnDate).toLocaleDateString();
            
            const today = new Date();
            const returnDateObj = new Date(returnDate);
            const diffTime = Math.abs(returnDateObj - today);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            document.getElementById('issueDays').textContent = diffDays + ' days';
            
            issueSummary.style.display = 'block';
        } else {
            issueSummary.style.display = 'none';
        }
    }
    
    bookSelect.addEventListener('change', updateSummary);
    userSelect.addEventListener('change', updateSummary);
    returnDateInput.addEventListener('change', updateSummary);
    
    
    function addSearchToSelect(selectElement) {
        const options = Array.from(selectElement.options);
        
        selectElement.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            options.forEach(option => {
                if (option.value === '') return; 
                
                const text = option.text.toLowerCase();
                if (text.includes(searchTerm)) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
   
});
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#book_id').select2({
        placeholder: "Search or select a book...",
        allowClear: true
    });
    $('#user_id').select2({
        placeholder: "Search or select a user...",
        allowClear: true
    });
});
</script>
</body>
</html>

<style>
.issue-summary {
    margin-top: 20px;
    padding: 20px;
    background-color: var(--gray-100);
    border-radius: var(--border-radius);
    border-left: 4px solid var(--primary-color);
}

.summary-card h4 {
    margin: 0 0 15px 0;
    color: var(--primary-color);
    font-weight: 600;
}

.summary-details {
    display: grid;
    gap: 10px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid var(--gray-300);
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item .label {
    font-weight: 500;
    color: var(--text-light);
}

.summary-item .value {
    font-weight: 600;
    color: var(--text-color);
}

.recent-issues-list {
    max-height: 500px;
    overflow-y: auto;
}

.recent-issue-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    margin-bottom: 10px;
    background-color: var(--gray-100);
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.recent-issue-item:hover {
    background-color: var(--gray-200);
}

.issue-book-info h4 {
    margin: 0 0 5px 0;
    font-size: 1em;
    font-weight: 600;
}

.issue-book-info .author {
    margin: 0;
    font-size: 0.9em;
    color: var(--text-light);
}

.issue-user-info p {
    margin: 0 0 5px 0;
    font-size: 0.8em;
    color: var(--text-light);
    display: flex;
    align-items: center;
    gap: 5px;
}

.issue-user-info .user-name {
    font-weight: 500;
    color: var(--text-color);
}

.issue-status {
    text-align: right;
}

.form-control {
    transition: var(--transition);
}

.form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.1);
}

@media (max-width: 768px) {
    .recent-issue-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .issue-status {
        text-align: left;
        width: 100%;
    }
}
</style>

<?php
// Include footer
include_once '../includes/footer.php';
?>