<?php
// Include header
include_once '../includes/header.php';

// Check if user is a librarian
checkUserRole('librarian');

// Process weed-off operations
$message = '';
$messageType = '';

if (isset($_POST['weed_off_book'])) {
    $bookId = (int)$_POST['book_id'];
    $reason = trim($_POST['reason']);

    if (empty($bookId) || empty($reason)) {
        $message = "Please select a book and provide a reason for removal.";
        $messageType = "danger";
    } else {
        $conn->begin_transaction();

        try {
            // Check if book is currently issued
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM issued_books WHERE book_id = ? AND (status = 'issued' OR status = 'overdue')");
            $stmt->bind_param("i", $bookId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                throw new Exception("Cannot remove this book. It is currently issued to users.");
            }

            // Get book details before deletion (using book_name from books table)
            $stmt = $conn->prepare("SELECT book_name, author FROM books WHERE id = ?");
            $stmt->bind_param("i", $bookId);
            $stmt->execute();
            $book = $stmt->get_result()->fetch_assoc();

            if (!$book) {
                throw new Exception("Book not found.");
            }

            // Insert into weed-off history (using book_title as column name)
            $stmt = $conn->prepare("INSERT INTO weed_off_books (book_id, book_title, reason, removed_by) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issi", $bookId, $book['book_name'], $reason, $_SESSION['user_id']);
            $stmt->execute();

            // Delete the book
            $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
            $stmt->bind_param("i", $bookId);
            $stmt->execute();

            $conn->commit();
            $message = "Book '{$book['book_name']}' has been successfully removed from the library.";
            $messageType = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Create weed_off_books table if it doesn't exist
$conn->query("CREATE TABLE IF NOT EXISTS weed_off_books (
    id INT AUTO_INCREMENT PRIMARY KEY, 
    book_id INT, 
    book_title VARCHAR(255), 
    reason TEXT, 
    removed_by INT, 
    removed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (removed_by) REFERENCES users(id)
)");

// Get all books for dropdown (excluding currently issued books)
$books = [];
$sql = "
    SELECT DISTINCT b.id, b.book_name, b.author, b.book_no, b.category, b.total_quantity
    FROM books b
    LEFT JOIN issued_books ib ON b.id = ib.book_id AND (ib.status = 'issued' OR ib.status = 'overdue')
    WHERE ib.id IS NULL
    ORDER BY b.book_name ASC
";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}

// Get weed-off history
$history = [];
$sql = "SELECT wh.*, u.name as librarian_name FROM weed_off_books wh JOIN users u ON wh.removed_by = u.id ORDER BY wh.removed_at DESC LIMIT 20";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
}
?>

<div class="weed-off-container">
    <h1 class="page-title">
        <i class="fas fa-trash-alt"></i> Weed Off Books
    </h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>" id="alertMessage">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Simple Weed-Off Form -->
    <div class="weed-off-form-card">
        <form id="weedOffForm" method="POST">
            <!-- Search and Select Book -->
            <div class="form-group">
                <label for="bookSearch">
                    <i class="fas fa-search"></i> Search and Select Book to Remove
                </label>
                <div class="search-container">
                    <input type="text" id="bookSearch" class="search-input" placeholder="Click here to see all books or start typing to search..." autocomplete="off">
                    <div class="dropdown-list" id="bookDropdown">
                        <?php foreach ($books as $book): ?>
                            <div class="dropdown-item" 
                                 data-id="<?php echo $book['id']; ?>"
                                 data-book_title="<?php echo htmlspecialchars($book['book_name']); ?>"
                                 data-author="<?php echo htmlspecialchars($book['author']); ?>"
                                 data-category="<?php echo htmlspecialchars($book['category']); ?>"
                                 data-book-no="<?php echo htmlspecialchars($book['book_no']); ?>">
                                <div class="book-info">
                                    <div class="book-name"><?php echo htmlspecialchars($book['book_name']); ?></div>
                                    <div class="book-details">
                                        by <?php echo htmlspecialchars($book['author']); ?>
                                        <?php if (!empty($book['book_no'])): ?>
                                            • Book No: <?php echo htmlspecialchars($book['book_no']); ?>
                                        <?php endif; ?>
                                        <?php if (!empty($book['category'])): ?>
                                            • <?php echo htmlspecialchars($book['category']); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <input type="hidden" id="selectedBookId" name="book_id">
            </div>

            <!-- Selected Book Display -->
            <div class="selected-book-display" id="selectedBookDisplay" style="display: none;">
                <div class="selected-book-info">
                    <h3>Selected Book:</h3>
                    <div class="book-card-selected">
                        <div class="book-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="book-details-selected">
                            <div class="book-title" id="selectedBookTitle"></div>
                            <div class="author" id="selectedAuthor"></div>
                            <div class="meta" id="selectedMeta"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reason for Removal -->
            <div class="reason-section" id="reasonSection" style="display: none;">
                <div class="form-group">
                    <label for="reason">
                        <i class="fas fa-edit"></i> Reason for Removal *
                    </label>
                    <textarea id="reason" name="reason" class="reason-textarea" rows="4" 
                              placeholder="Please provide a detailed reason for removing this book from the library..." required></textarea>
                </div>

                <div class="action-buttons">
                    <button type="button" class="btn btn-cancel" onclick="resetForm()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" name="weed_off_book" class="btn btn-remove">
                        <i class="fas fa-trash-alt"></i> Remove Book
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Removal History -->
    <div class="history-section">
        <h2 class="history-title">
            <i class="fas fa-history"></i> Removal History
        </h2>
        
        <?php if (count($history) > 0): ?>
            <div class="history-table-container">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Reason</th>
                            <th>Removed By</th>
                            <th>Removed On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $record): ?>
                            <tr>
                                <td class="book-title-cell"><?php echo htmlspecialchars($record['book_title']); ?></td>
                                <td class="reason-cell"><?php echo htmlspecialchars($record['reason']); ?></td>
                                <td><?php echo htmlspecialchars($record['librarian_name']); ?></td>
                                <td><?php echo date('M d, Y H:i', strtotime($record['removed_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-history">
                <i class="fas fa-inbox"></i>
                <p>No books have been removed yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.weed-off-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

.page-title {
    text-align: center;
    color: var(--primary-color);
    margin-bottom: 40px;
    font-size: 2.2em;
    font-weight: 700;
}

.weed-off-form-card {
    background: var(--white);
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    margin-bottom: 40px;
    border: 1px solid var(--gray-200);
}

.form-group {
    margin-bottom: 30px;
}

.form-group label {
    display: block;
    margin-bottom: 12px;
    font-weight: 600;
    color: var(--text-color);
    font-size: 1.1em;
}

.search-container {
    position: relative;
}

.search-input {
    width: 100%;
    padding: 15px 20px;
    border: 2px solid var(--gray-300);
    border-radius: 12px;
    font-size: 1.1em;
    transition: all 0.3s ease;
    background: var(--white);
    box-sizing: border-box;
}

.search-input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 4px rgba(139, 94, 60, 0.1);
}

.dropdown-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--white);
    border: 2px solid var(--primary-color);
    border-top: none;
    border-radius: 0 0 12px 12px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    display: none;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.dropdown-list.show {
    display: block;
}

.dropdown-item {
    padding: 15px 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    border-bottom: 1px solid var(--gray-200);
}

.dropdown-item:hover {
    background: var(--gray-100);
}

.dropdown-item:last-child {
    border-bottom: none;
}

.dropdown-item.selected {
    background: var(--primary-color);
    color: var(--white);
}

.book-info .book-name {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 5px;
}

.book-info .book-details {
    font-size: 0.9em;
    color: var(--text-light);
}

.dropdown-item.selected .book-info .book-name,
.dropdown-item.selected .book-info .book-details {
    color: var(--white);
}

.selected-book-display {
    margin-top: 25px;
    padding: 20px;
    background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
    border-radius: 12px;
    border-left: 4px solid var(--primary-color);
}

.selected-book-info h3 {
    margin: 0 0 15px 0;
    color: var(--primary-color);
    font-size: 1.2em;
}

.book-card-selected {
    display: flex;
    align-items: center;
    gap: 15px;
    background: var(--white);
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.book-icon {
    width: 50px;
    height: 50px;
    background: var(--primary-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 1.5em;
}

.book-details-selected .book-title {
    font-size: 1.1em;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 5px;
}

.book-details-selected .author {
    font-size: 1em;
    color: var(--text-color);
    margin-bottom: 3px;
}

.book-details-selected .meta {
    font-size: 0.9em;
    color: var(--text-light);
}

.reason-section {
    margin-top: 30px;
    padding: 25px;
    background: rgba(220, 53, 69, 0.05);
    border-radius: 12px;
    border: 2px solid rgba(220, 53, 69, 0.2);
}

.reason-textarea {
    width: 100%;
    padding: 15px;
    border: 2px solid var(--gray-300);
    border-radius: 10px;
    font-size: 1em;
    font-family: inherit;
    resize: vertical;
    min-height: 100px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.reason-textarea:focus {
    border-color: var(--danger-color);
    outline: none;
    box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1);
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 25px;
}

.btn {
    padding: 12px 30px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 1em;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 140px;
    justify-content: center;
}

.btn-cancel {
    background: var(--gray-400);
    color: var(--white);
}

.btn-cancel:hover {
    background: var(--gray-500);
    transform: translateY(-2px);
}

.btn-remove {
    background: var(--danger-color);
    color: var(--white);
}

.btn-remove:hover {
    background: #c82333;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
}

.history-section {
    background: var(--white);
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--gray-200);
}

.history-title {
    color: var(--primary-color);
    margin-bottom: 25px;
    font-size: 1.5em;
    font-weight: 600;
    text-align: center;
}

.history-table-container {
    overflow-x: auto;
}

.history-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.history-table th {
    background: var(--gray-100);
    color: var(--text-color);
    padding: 15px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid var(--gray-300);
}

.history-table td {
    padding: 15px;
    border-bottom: 1px solid var(--gray-200);
    vertical-align: top;
}

.history-table tr:hover {
    background: var(--gray-100);
}

.book-title-cell {
    font-weight: 600;
    color: var(--primary-color);
}

.reason-cell {
    max-width: 300px;
    word-wrap: break-word;
    line-height: 1.4;
}

.no-history {
    text-align: center;
    padding: 40px;
    color: var(--text-light);
}

.no-history i {
    font-size: 3em;
    margin-bottom: 15px;
    opacity: 0.5;
}

.no-history p {
    margin: 0;
    font-size: 1.1em;
}

/* Scrollbar styling for dropdown */
.dropdown-list::-webkit-scrollbar {
    width: 8px;
}

.dropdown-list::-webkit-scrollbar-track {
    background: var(--gray-200);
    border-radius: 4px;
}

.dropdown-list::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 4px;
}

.dropdown-list::-webkit-scrollbar-thumb:hover {
    background: var(--primary-dark);
}

/* Responsive Design */
@media (max-width: 768px) {
    .weed-off-container {
        padding: 15px;
    }
    
    .weed-off-form-card {
        padding: 25px 20px;
    }
    
    .history-section {
        padding: 20px;
    }
    
    .page-title {
        font-size: 1.8em;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
    
    .history-table th,
    .history-table td {
        padding: 10px 8px;
        font-size: 0.9em;
    }
    
    .reason-cell {
        max-width: 200px;
    }
}

@media (max-width: 480px) {
    .book-card-selected {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .dropdown-item {
        padding: 12px 15px;
    }
    
    .search-input {
        padding: 12px 15px;
        font-size: 1em;
    }
}

/* Animation for smooth transitions */
.selected-book-display,
.reason-section {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Alert auto-hide animation */
.alert {
    animation: fadeIn 0.3s ease-out;
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
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('bookSearch');
    const dropdown = document.getElementById('bookDropdown');
    const selectedBookDisplay = document.getElementById('selectedBookDisplay');
    const reasonSection = document.getElementById('reasonSection');
    const selectedBookId = document.getElementById('selectedBookId');
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    
    let selectedBook = null;

    // Show dropdown when search input is clicked or focused
    searchInput.addEventListener('focus', function() {
        dropdown.classList.add('show');
        filterBooks(''); // Show all books initially
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            dropdown.classList.remove('show');
        }
    });

    // Filter books as user types
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterBooks(searchTerm);
    });

    // Handle book selection
    dropdownItems.forEach(item => {
        item.addEventListener('click', function() {
            selectBook(this);
        });
    });

    function filterBooks(searchTerm) {
        dropdownItems.forEach(item => {
            const bookTitle = item.dataset.book_title.toLowerCase();
            const author = item.dataset.author.toLowerCase();
            const bookNo = item.dataset.bookNo.toLowerCase();
            const category = item.dataset.category.toLowerCase();
            
            if (bookTitle.includes(searchTerm) || 
                author.includes(searchTerm) || 
                bookNo.includes(searchTerm) || 
                category.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function selectBook(item) {
        selectedBook = {
            id: item.dataset.id,
            book_title: item.dataset.book_title,
            author: item.dataset.author,
            category: item.dataset.category,
            bookNo: item.dataset.bookNo
        };

        // Update search input
        searchInput.value = selectedBook.book_title;
        selectedBookId.value = selectedBook.id;

        // Hide dropdown
        dropdown.classList.remove('show');

        // Show selected book display
        document.getElementById('selectedBookTitle').textContent = selectedBook.book_title;
        document.getElementById('selectedAuthor').textContent = 'by ' + selectedBook.author;
        
        let metaText = '';
        if (selectedBook.bookNo) metaText += 'Book No: ' + selectedBook.bookNo;
        if (selectedBook.category) {
            if (metaText) metaText += ' • ';
            metaText += selectedBook.category;
        }
        document.getElementById('selectedMeta').textContent = metaText;

        selectedBookDisplay.style.display = 'block';
        reasonSection.style.display = 'block';

        // Focus on reason textarea
        setTimeout(() => {
            document.getElementById('reason').focus();
        }, 300);
    }

    // Form submission with confirmation
    document.getElementById('weedOffForm').addEventListener('submit', function(e) {
        if (!selectedBook) {
            e.preventDefault();
            alert('Please select a book to remove.');
            return;
        }

        const reason = document.getElementById('reason').value.trim();
        if (!reason) {
            e.preventDefault();
            alert('Please provide a reason for removing the book.');
            return;
        }

        const confirmMessage = `Are you sure you want to permanently remove "${selectedBook.book_title}" from the library?\n\nThis action cannot be undone.`;
        if (!confirm(confirmMessage)) {
            e.preventDefault();
        }
    });

    // Auto-hide success/error messages
    const alertMessage = document.getElementById('alertMessage');
    if (alertMessage) {
        setTimeout(() => {
            alertMessage.style.opacity = '0';
            alertMessage.style.transform = 'translateY(-20px)';
            setTimeout(() => {
                alertMessage.remove();
            }, 300);
        }, 5000);
    }
});

function resetForm() {
    document.getElementById('bookSearch').value = '';
    document.getElementById('selectedBookId').value = '';
    document.getElementById('reason').value = '';
    document.getElementById('selectedBookDisplay').style.display = 'none';
    document.getElementById('reasonSection').style.display = 'none';
    document.getElementById('bookDropdown').classList.remove('show');
    selectedBook = null;
}
</script>

<?php include_once '../includes/footer.php'; ?>