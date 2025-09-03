<?php
include_once '../includes/header.php';
checkUserRole('librarian');
$message = '';
$messageType = '';
if (isset($_POST['return'])) {
    $id = (int)$_POST['issued_book_id'];
    $fineAmount = isset($_POST['fine_amount']) ? (float)$_POST['fine_amount'] : 0;
    $returnDate = date('Y-m-d');

    $bookCondition = isset($_POST['book_condition']) ? $_POST['book_condition'] : '';
    if ($bookCondition !== 'good') {
        $message = "Return cannot be processed because the book is not marked as in good condition.";
        $messageType = "danger";
    } else {
       
        $conn->begin_transaction();
        
        try {
            $stmt = $conn->prepare("
                SELECT ib.*, b.book_name, u.id as user_id, u.name as user_name
                FROM issued_books ib
                JOIN books b ON ib.book_id = b.id
                JOIN users u ON ib.user_id = u.id
                WHERE ib.id = ?
            ");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $issuedBook = $result->fetch_assoc();
                
                
                $stmt = $conn->prepare("
                    UPDATE issued_books 
                    SET actual_return_date = ?, status = 'returned', fine_amount = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("sdi", $returnDate, $fineAmount, $id);
                $stmt->execute();
                
                
                updateBookAvailability($conn, $issuedBook['book_id'], 'return');
                
        
                if ($fineAmount > 0) {
                    $stmt = $conn->prepare("
                        INSERT INTO fines (issued_book_id, user_id, amount, reason)
                        VALUES (?, ?, ?, ?)
                    ");
                    $reason = "Late return of book '{$issuedBook['book_name']}'";
                    $stmt->bind_param("idds", $id, $issuedBook['user_id'], $fineAmount, $reason);
                    $stmt->execute();
                    
                    
                    $notificationMsg = "You have been charged a fine of PKR " . number_format($fineAmount, 2) . " for late return of '{$issuedBook['book_name']}'. Please settle the payment at the library.";
                    sendNotification($conn, $issuedBook['user_id'], $notificationMsg);
                }
                
                
                $notificationMsg = "Your book '{$issuedBook['book_name']}' has been returned successfully.";
                sendNotification($conn, $issuedBook['user_id'], $notificationMsg);
                
                
                $conn->commit();
                
                
                $reservationResult = processBookReservations($conn, $issuedBook['book_id']);
                
                $message = "Book returned successfully.";
                if ($fineAmount > 0) {
                    $message .= " A fine of PKR " . number_format($fineAmount, 2) . " has been issued.";
                }
                if ($reservationResult['success']) {
                    $message .= " " . $reservationResult['message'];
                }
                $messageType = "success";
            } else {
                throw new Exception("Issued book record not found.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error processing return: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}



$today = date('Y-m-d');
$updateOverdueSql = "
    UPDATE issued_books 
    SET status = 'overdue',
        fine_amount = GREATEST(DATEDIFF(?, return_date) * 100.00, 0)
    WHERE status = 'issued' AND ? > return_date
";
$stmt = $conn->prepare($updateOverdueSql);
$stmt->bind_param("ss", $today, $today);
$stmt->execute();

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';


$sql = "
    SELECT ib.*, b.book_name as book_name, u.name as user_name,
           CASE 
               WHEN ib.actual_return_date IS NULL AND CURRENT_DATE > ib.return_date THEN 'overdue'
               WHEN ib.actual_return_date IS NULL THEN 'issued'
               ELSE ib.status
           END as current_status,
           GREATEST(DATEDIFF(CURRENT_DATE, ib.return_date), 0) as days_overdue
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    JOIN users u ON ib.user_id = u.id
    WHERE 1=1
";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (b.book_name LIKE ? OR u.name LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if (!empty($status)) {
    if ($status == 'overdue') {
        $sql .= " AND ib.actual_return_date IS NULL AND CURRENT_DATE > ib.return_date";
    } elseif ($status == 'issued') {
        $sql .= " AND ib.actual_return_date IS NULL AND CURRENT_DATE <= ib.return_date";
    } else {
        $sql .= " AND ib.status = ?";
        $params[] = $status;
        $types .= "s";
    }
}

$sql .= " ORDER BY 
    CASE WHEN ib.actual_return_date IS NULL THEN 0 ELSE 1 END,
    ib.return_date ASC,
    ib.issue_date DESC";


$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$issuedBooks = [];
while ($row = $result->fetch_assoc()) {
    $issuedBooks[] = $row;
}
?>

<h1 class="page-title">Book Returns</h1>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="d-flex justify-between align-center mb-4">
    <div class="status-summary">
        <div class="badge-container">
    
    <div class="d-flex">
        <form action="" method="GET" class="d-flex">
            <div class="form-group mr-2" style="margin-bottom: 0; margin-right: 10px;">
                <input type="text" name="search" placeholder="Search..." class="form-control" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="form-group mr-2" style="margin-bottom: 0; margin-right: 10px;">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="issued" <?php echo $status == 'issued' ? 'selected' : ''; ?>>Issued</option>
                    <option value="returned" <?php echo $status == 'returned' ? 'selected' : ''; ?>>Returned</option>
                    <option value="overdue" <?php echo $status == 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>
</div>

<div class="table-container" style="margin-top:30px;">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Book Name</th>
                <th>Issued To</th>
                <th>Issue Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Fine</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($issuedBooks) > 0): ?>
                <?php foreach ($issuedBooks as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                        <td><?php echo htmlspecialchars($book['user_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($book['issue_date'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($book['return_date'])); ?></td>
                        <td>
                            <?php 
                            if ($book['actual_return_date']) {
                                echo date('M d, Y', strtotime($book['actual_return_date']));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            switch ($book['current_status']) {
                                case 'issued':
                                    echo '<span class="badge badge-primary">Issued</span>';
                                    break;
                                case 'returned':
                                    echo '<span class="badge badge-success">Returned</span>';
                                    break;
                                case 'overdue':
                                    echo '<span class="badge badge-danger">Overdue</span>';
                                    break;
                                default:
                                    echo '<span class="badge badge-secondary">' . htmlspecialchars($book['current_status']) . '</span>';
                            }
                            ?>
                        </td>
                        <td>
    <?php 

    $roleStmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $roleStmt->bind_param("i", $book['user_id']);
    $roleStmt->execute();
    $roleResult = $roleStmt->get_result();
    $userRole = '';
    if ($roleData = $roleResult->fetch_assoc()) {
        $userRole = $roleData['role'];
    }

    if ($userRole === 'faculty' && $book['current_status'] == 'overdue' && !$book['actual_return_date']) {
        
    }
    else if ($book['fine_amount'] > 0) {
        echo 'PKR ' . number_format($book['fine_amount'], 2);
    } else if ($book['current_status'] == 'overdue' && !$book['actual_return_date']) {
        $suggestedFine = $book['days_overdue'] * 100.00;
        echo '<span class="text-danger">PKR ' . number_format($suggestedFine, 2) . ' (pending)</span>';
    } else {
        echo '-';
    }
    ?>
</td>
                        <td>
                            <?php if ($book['current_status'] != 'returned'): ?>
                                <button class="btn btn-sm btn-primary" data-modal-target="returnModal<?php echo $book['id']; ?>">
                                    <i class="fas fa-undo"></i> Return
                                </button>
                                
                                <div class="modal-overlay" id="returnModal<?php echo $book['id']; ?>">
                                    <div class="modal">
                                        <div class="modal-header">
                                            <h3 class="modal-title">Process Book Return</h3>
                                            <button class="modal-close">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <p>You are processing the return of <strong><?php echo htmlspecialchars($book['book_name']); ?></strong>
                                                issued to <strong><?php echo htmlspecialchars($book['user_name']); ?></strong>.</p>
                                            
                                            <?php
                                            
                                            $suggestedFine = 0;
                                            $daysOverdue = 0;
                                            $userRole = '';
                                            $today = new DateTime();
                                            $dueDate = new DateTime($book['return_date']);
                                            
                                           
                                            $roleStmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
                                            $roleStmt->bind_param("i", $book['user_id']);
                                            $roleStmt->execute();
                                            $roleResult = $roleStmt->get_result();
                                            if ($roleData = $roleResult->fetch_assoc()) {
                                                $userRole = $roleData['role'];
                                            }
                                            
                                            if ($today > $dueDate) {
                                                $diff = $today->diff($dueDate);
                                                $daysOverdue = $diff->days;
                                                
                                                $suggestedFine = ($userRole === 'faculty') ? 0 : ($daysOverdue * 100.00);
                                            }
                                            
                                            
                                            $reservationQueue = getBookReservationQueue($conn, $book['book_id']);
                                            ?>
                                            
                                            <?php if (count($reservationQueue) > 0): ?>
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle"></i>
                                                    <strong>Reservation Alert:</strong> This book has <?php echo count($reservationQueue); ?> active reservation(s). 
                                                    The book will be automatically issued to the next person in queue.
                                                    <br><br>
                                                    <strong>Next in queue:</strong> <?php echo htmlspecialchars($reservationQueue[0]['user_name']); ?>
                                                    <br>
                                                    <span class="text-success"><i class="fas fa-magic"></i> <strong>Auto-Issue:</strong> The book will be automatically issued to this user upon return.</span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <form action="" method="POST">
                                                <input type="hidden" name="issued_book_id" value="<?php echo $book['id']; ?>">
                                                <div class="form-group">
    <label for="book_condition<?php echo $book['id']; ?>">Book Condition</label>
    <select id="book_condition<?php echo $book['id']; ?>" name="book_condition" class="form-control" required>
        <option value="">-- Select Condition --</option>
        <option value="good">Book is in good condition</option>
        <option value="damaged">Book is damaged</option>
        <option value="missing">Book is missing pages</option>
    </select>
</div>

                                                
                                                <?php if ($daysOverdue > 0): ?>
                                                    <?php if ($userRole === 'faculty'): ?>
                                                        <div class="alert alert-info">
                                                            This book is <strong><?php echo $daysOverdue; ?> days</strong> overdue.
                                                            <br><strong>Faculty Exemption:</strong> No fine will be charged as this user is a faculty member.
                                                        </div>
                                                        <input type="hidden" name="fine_amount" value="0">
                                                    <?php else: ?>
                                                        <div class="alert alert-warning">
                                                            This book is <strong><?php echo $daysOverdue; ?> days</strong> overdue.
                                                            Suggested fine: PKR <?php echo number_format($suggestedFine, 2); ?>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <label for="fine_amount<?php echo $book['id']; ?>">Fine Amount (PKR)</label>
                                                            <input type="number" id="fine_amount<?php echo $book['id']; ?>" name="fine_amount" class="form-control" step="0.01" min="0" value="<?php echo $suggestedFine; ?>">
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div class="alert alert-success">
                                                        This book is being returned on time. No fine required.
                                                    </div>
                                                    <input type="hidden" name="fine_amount" value="0">
                                                <?php endif; ?>
                                                
                                                <div class="form-group text-right">
                                                    <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                                                    <button type="submit" name="return" class="btn btn-primary">
                                                        <?php if (count($reservationQueue) > 0): ?>
                                                            <i class="fas fa-magic"></i> Return & Auto-Issue
                                                        <?php else: ?>
                                                            <i class="fas fa-undo"></i> Confirm Return
                                                        <?php endif; ?>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">Already returned</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No issued books found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.alert-info {
    background-color: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}

.text-success {
    color: #28a745 !important;
}

.fa-magic {
    color: #ff9800;
}
</style>

<?php
include_once '../includes/footer.php';
?>