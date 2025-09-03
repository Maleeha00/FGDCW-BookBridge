<?php
// Include header
include_once '../includes/header.php';

// Check if user is a librarian
checkUserRole('librarian');

// Get book ID from URL
$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get book details
$stmt = $conn->prepare("
    SELECT b.*, 
           COUNT(DISTINCT ib.id) as times_issued,
           COUNT(DISTINCT CASE WHEN ib.status = 'issued' OR ib.status = 'overdue' THEN ib.id END) as currently_issued
    FROM books b
    LEFT JOIN issued_books ib 
    ON b.id = ib.book_id
    WHERE b.id = ?
    GROUP BY b.id
");
$stmt->bind_param("i", $bookId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: books.php');
    exit();
}

$book = $result->fetch_assoc();

// Get issue history
$stmt = $conn->prepare("
    SELECT ib.*, u.name as user_name
    FROM issued_books ib
    JOIN users u 
    ON ib.user_id = u.id
    WHERE ib.book_id = ?
    ORDER BY ib.issue_date DESC
");
$stmt->bind_param("i", $bookId);
$stmt->execute();
$issueHistory = $stmt->get_result();

// Process book update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_book'])) {
    $book_name = trim($_POST['book_name']);
    $author = trim($_POST['author']);
    $book_no = trim($_POST['book_no']);
    $publisher = trim($_POST['publisher']);
    $category = trim($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    
    // Update book
    $stmt = $conn->prepare("
        UPDATE books 
        SET book_name = ?, author = ?, book_no = ?, publisher = ?, 
            category = ?, total_quantity = ?
        WHERE id = ?
    ");
    
    $stmt->bind_param(
        "sssssii",
        $book_name, $author, $book_no, $publisher, 
        $category, $quantity, $bookId
    );
    
    if ($stmt->execute()) {
        $message = "Book updated successfully.";
        $messageType = "success";

        // Refresh book details with calculated fields
        $stmt = $conn->prepare("
            SELECT b.*, 
                   COUNT(DISTINCT ib.id) as times_issued,
                   COUNT(DISTINCT CASE WHEN ib.status = 'issued' OR ib.status = 'overdue' THEN ib.id END) as currently_issued
            FROM books b
            LEFT JOIN issued_books ib ON b.id = ib.book_id
            WHERE b.id = ?
            GROUP BY b.id
        ");
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $result = $stmt->get_result();
        $book = $result->fetch_assoc();
    } else {
        $message = "Error updating book: " . $stmt->error;
        $messageType = "danger";
    }
}
?>

<div class="d-flex justify-between align-center mb-4">
    <h1 class="page-title">Book Details</h1>
    <a href="books.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Books
    </a>
</div>

<?php if (isset($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="book-details-container">
    <div class="book-info-card">
        <div class="book-cover">
            <div class="no-cover">
                <i class="fas fa-book fa-4x"></i>
            </div>
        </div>
        
        <div class="book-stats">
            <div class="stat-item">
                <span class="stat-label">Total Quantity</span>
                <span class="stat-value"><?php echo $book['total_quantity']; ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Available</span>
                <span class="stat-value"><?php echo $book['available_quantity']; ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Times Issued</span>
                <span class="stat-value"><?php echo $book['times_issued']; ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Currently Issued</span>
                <span class="stat-value"><?php echo $book['currently_issued']; ?></span>
            </div>
        </div>
    </div>
    
    <div class="book-details-form">
        <form action="" method="POST">
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="book_name">book_name</label>
                        <input type="text" id="book_name" name="book_name" value="<?php echo htmlspecialchars($book['book_name']); ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="book_no">Book Number</label>
                        <input type="text" id="book_no" name="book_no" value="<?php echo htmlspecialchars($book['book_no']); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="publisher">Publisher</label>
                        <input type="text" id="publisher" name="publisher" value="<?php echo htmlspecialchars($book['publisher']); ?>">
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($book['category']); ?>">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="quantity">Total Quantity</label>
                        <input type="number" id="quantity" name="quantity" value="<?php echo $book['total_quantity']; ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group text-right">
                <button type="submit" name="update_book" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Book
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3>Issue History</h3>
    </div>
    <div class="card-body">
        <div class="table-container">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Issued To</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Fine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($issueHistory->num_rows > 0): ?>
                        <?php while ($issue = $issueHistory->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($issue['user_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($issue['issue_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($issue['return_date'])); ?></td>
                                <td>
                                    <?php 
                                    if ($issue['actual_return_date']) {
                                        echo date('M d, Y', strtotime($issue['actual_return_date']));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    switch ($issue['status']) {
                                        case 'issued':
                                            echo '<span class="badge badge-primary">Issued</span>';
                                            break;
                                        case 'returned':
                                            echo '<span class="badge badge-success">Returned</span>';
                                            break;
                                        case 'overdue':
                                            echo '<span class="badge badge-danger">Overdue</span>';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($issue['fine_amount'] > 0) {
                                        echo '$' . number_format($issue['fine_amount'], 2);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No issue history found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
// Include footer
include_once '../includes/footer.php';
?>
