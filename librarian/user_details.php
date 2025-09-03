<?php
include_once '../includes/header.php';
checkUserRole('librarian');
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: users.php');
    exit();
}

$user = $result->fetch_assoc();
$stmt = $conn->prepare("
    SELECT ib.*, b.book_name, b.author
    FROM issued_books ib
    JOIN books b ON ib.book_id = b.id
    WHERE ib.user_id = ?
    ORDER BY ib.issue_date DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$issuedBooks = $stmt->get_result();
$stmt = $conn->prepare("
    SELECT f.*, b.book_name as book_name
    FROM fines f
    JOIN issued_books ib ON f.issued_book_id = ib.id
    JOIN books b ON ib.book_id = b.id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$fines = $stmt->get_result();
?>

<div class="d-flex justify-between align-center mb-4">
    <h1 class="page-title">User Details</h1>
    <div>
        
        <a href="users.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
</div>

<div class="dashboard-row" style="margin-top:30px";>
    <div class="dashboard-col">
        <div class="card">
            <div class="card-header">
                <h3>Personal Information</h3>
            </div>
            <div class="card-body">
                <div class="user-info">
                    <div class="info-item">
                        <label>Full Name:</label>
                        <span><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Email:</label>
                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Role:</label>
                        <span>
                            <?php 
                            switch ($user['role']) {
                                case 'student':
                                    echo '<span class="badge badge-primary">Student</span>';
                                    break;
                                case 'faculty':
                                    echo '<span class="badge badge-success">Faculty</span>';
                                    break;
                                case 'librarian':
                                    echo '<span class="badge badge-warning">Librarian</span>';
                                    break;
                            }
                            ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <label>class:</label>
                        <span><?php echo htmlspecialchars($user['class'] ?: 'Not specified'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Phone:</label>
                        <span><?php echo htmlspecialchars($user['phone'] ?: 'Not specified'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <label>Account Created:</label>
                        <span><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="card mt-4">
    <div class="card-header">
        <h3>Issued Books History</h3>
    </div>
    <div class="card-body" >
        <div class="table-container">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Book Name</th>
                        <th>Author</th>
                        <th>Issue Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                        <th>Fine</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($issuedBooks->num_rows > 0): ?>
                        <?php while ($book = $issuedBooks->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['book_name']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
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
                                    switch ($book['status']) {
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
                                    if ($book['fine_amount'] > 0) {
                                        echo '$' . number_format($book['fine_amount'], 2);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No books issued yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.user-info {
    display: grid;
    gap: 15px;
}

.info-item {
    display: flex;
    border-bottom: 1px solid var(--gray-200);
    padding-bottom: 10px;
}

.info-item label {
    font-weight: 600;
    width: 150px;
    color: var(--text-light);
}
</style>

<?php
include_once '../includes/footer.php';
?>