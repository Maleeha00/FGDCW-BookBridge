<?php
include_once '../includes/header.php';
checkUserRole('librarian');
$message = '';
$messageType = '';

if (isset($_POST['add_book'])) {
    $book_name = trim($_POST['book_name']);
    $author = trim($_POST['author']);
    $book_no = trim($_POST['book_no']);
    $publisher = trim($_POST['publisher']);
    $category = trim($_POST['category']);
    $quantity = (int)$_POST['quantity'];
    
   
    if (empty($book_name) || empty($author) || empty($quantity)) {
        $message = "book_name, author, and quantity are required fields.";
        $messageType = "danger";
    } else {
        
        if (!empty($book_no)) {
            $stmt = $conn->prepare("SELECT id FROM books WHERE book_no = ?");
            $stmt->bind_param("s", $book_no);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $message = "A book with this book number already exists.";
                $messageType = "danger";
            }
        }
        
        if (empty($message)) {
            $stmt = $conn->prepare("
                INSERT INTO books (book_name, author, book_no, publisher, category, 
                                  total_quantity, available_quantity)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->bind_param(
                "sssssii",
                $book_name, $author, $book_no, $publisher, $category,
                $quantity, $quantity
            );
            
            if ($stmt->execute()) {
                $message = "Book added successfully.";
                $messageType = "success";
            } else {
                $message = "Error adding book: " . $stmt->error;
                $messageType = "danger";
            }
        }
    }
}


$categories = [];
$result = $conn->query("SELECT DISTINCT category FROM books WHERE category != '' ORDER BY category");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}


$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';


$booksPerPage = 12;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $booksPerPage;


$sql = "SELECT * FROM books WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (book_name LIKE ? OR author LIKE ? OR book_no LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "sss";
}

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}


$countSql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
$stmt = $conn->prepare($countSql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$totalBooks = $result->fetch_assoc()['COUNT(*)'];
$totalPages = ceil($totalBooks / $booksPerPage);

$sql .= " ORDER BY book_name LIMIT ? OFFSET ?";
$params[] = $booksPerPage;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}
?>

<h1 class="page-title">Manage Books<br><br></h1>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="d-flex justify-between align-center mb-4">
    <button class="btn btn-primary" data-modal-target="addBookModal">
        <i class="fas fa-plus"></i> Add New Book
    </button>
    
    <div class="d-flex">
        <form action="" method="GET" class="d-flex">
            <div class="form-group mr-2" style="margin-bottom: 0; margin-right: 10px;">
                <input type="text" name="search" placeholder="Search books..." class="form-control" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="form-group mr-2" style="margin-bottom: 0; margin-right: 10px;">
                <select name="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>
</div>

<div class="view-options" style="margin-top: 20px;">
    <button class="view-option" data-view="books-grid">
        <i class="fas fa-th"></i> Grid View
    </button>
    <button class="view-option active" data-view="books-table">
        <i class="fas fa-list"></i> Table View
    </button>
</div>


<div class="books-container books-table" id="tableView">
    <?php if (count($books) > 0): ?>
        <div class="table-container">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>book_name</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Book No.</th>
                        <th>Publisher</th>
                        <th>Availability</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                        <tr>
                            <td class="book-name-cell"><?php echo htmlspecialchars($book['book_name']); ?></td>
                            <td class="book-author-cell"><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['category']); ?></td>
                            <td><?php echo htmlspecialchars($book['book_no']); ?></td>
                            <td><?php echo htmlspecialchars($book['publisher']); ?></td>
                            <td>
                                <span class="availability-badge <?php echo $book['available_quantity'] > 0 ? 'available' : 'unavailable'; ?>">
                                    <?php echo $book['available_quantity']; ?> / <?php echo $book['total_quantity']; ?> available
                                </span>
                            </td>
                            <td>
                                <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-info-circle"></i> Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No books found.</div>
    <?php endif; ?>
</div>


<div class="books-container books-grid" id="gridView" style="display: none;">
    <?php if (count($books) > 0): ?>
        <div class="books-grid-container">
            <?php foreach ($books as $book): ?>
                <div class="book-card" data-category="<?php echo htmlspecialchars($book['category']); ?>">
                    
                    <div class="book-info">
                        <h3 class="book-name"><?php echo htmlspecialchars($book['book_name']); ?></h3>
                        <p class="book-author">By <?php echo htmlspecialchars($book['author']); ?></p>
                        <div class="book-details">
                            <span><?php echo htmlspecialchars($book['category']); ?></span>
                            <span>Book No: <?php echo htmlspecialchars($book['book_no']); ?></span>
                            <span>
                                <?php echo $book['available_quantity']; ?> / <?php echo $book['total_quantity']; ?> available
                            </span>
                        </div>
                    </div>
                    <div class="book-actions">
                        <a href="book_details.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-info-circle"></i> Edit 
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">No books found.</div>
    <?php endif; ?>
</div>


<?php if ($totalPages > 1): ?>
    <div class="pagination-container">
        <?php if ($page > 1): ?>
            <a href="?search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page - 1; ?>" class="pagination-btn">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php else: ?>
            <span class="pagination-btn disabled">
                <i class="fas fa-chevron-left"></i>
            </span>
        <?php endif; ?>

        <?php
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);
        
        if ($startPage > 1): ?>
            <a href="?search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&page=1" class="pagination-btn">1</a>
            <?php if ($startPage > 2): ?>
                <span class="pagination-btn disabled">...</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <a href="?search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $i; ?>" 
               class="pagination-btn <?php echo $i == $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
                <span class="pagination-btn disabled">...</span>
            <?php endif; ?>
            <a href="?search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $totalPages; ?>" class="pagination-btn"><?php echo $totalPages; ?></a>
        <?php endif; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?search=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category); ?>&page=<?php echo $page + 1; ?>" class="pagination-btn">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php else: ?>
            <span class="pagination-btn disabled">
                <i class="fas fa-chevron-right"></i>
            </span>
        <?php endif; ?>
    </div>
<?php endif; ?>


<div class="modal-overlay" id="addBookModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Add New Book</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form action="" method="POST">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="book_name">book_name <span class="text-danger">*</span></label>
                            <input type="text" id="book_name" name="book_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="author">Author <span class="text-danger">*</span></label>
                            <input type="text" id="author" name="author" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="book_no">Book Number</label>
                            <input type="text" id="book_no" name="book_no" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="publisher">Publisher</label>
                            <input type="text" id="publisher" name="publisher" class="form-control">
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" id="category" name="category" class="form-control" list="categories">
                            <datalist id="categories">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="quantity">Quantity <span class="text-danger">*</span></label>
                            <input type="number" id="quantity" name="quantity" class="form-control" min="1" value="1" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group text-right">
                    <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                    <button type="submit" name="add_book" class="btn btn-primary">Add Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>

.view-options {
    display: flex;
    justify-content: center;
    gap: 0;
    margin-bottom: 20px;
}

.view-option {
    padding: 10px 20px;
    background-color: var(--gray-200);
    border: 1px solid var(--gray-300);
    cursor: pointer;
    transition: var(--transition);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.view-option:first-child {
    border-top-left-radius: var(--border-radius);
    border-bottom-left-radius: var(--border-radius);
}

.view-option:last-child {
    border-top-right-radius: var(--border-radius);
    border-bottom-right-radius: var(--border-radius);
    border-left: none;
}

.view-option.active {
    background-color: var(--primary-color);
    color: var(--white);
    border-color: var(--primary-color);
}

.view-option:hover {
    background-color: var(--primary-light);
    color: var(--white);
}


.book-name-cell {
    font-weight: 600;
    color: var(--primary-color);
}

.book-author-cell {
    color: var(--text-light);
    font-style: italic;
}

.availability-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 600;
}

.availability-badge.available {
    background: rgba(76, 175, 80, 0.1);
    color: #2e7d32;
}

.availability-badge.unavailable {
    background: rgba(244, 67, 54, 0.1);
    color: #c62828;
}


.books-grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.book-card {
    background-color: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    transition: var(--transition);
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.book-cover {
    height: 200px;
    background-color: var(--gray-200);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-light);
    overflow: hidden;
}

.book-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.book-info {
    padding: 15px;
}

.book-name {
    margin: 0 0 10px 0;
    font-size: 1em;
    font-weight: 600;
    line-height: 1.3;
    color: var(--primary-color);
}

.book-author {
    color: var(--text-light);
    font-size: 0.9em;
    margin-bottom: 10px;
    font-style: italic;
}

.book-details {
    display: flex;
    flex-direction: column;
    gap: 5px;
    font-size: 0.8em;
    color: var(--text-light);
    margin-bottom: 15px;
}

.book-actions {
    padding: 10px 15px;
    border-top: 1px solid var(--gray-200);
    display: flex;
    gap: 5px;
}


.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 30px;
}

.pagination-btn {
    padding: 10px 15px;
    border: 2px solid var(--primary-color);
    background: white;
    color: var(--primary-color);
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    min-width: 45px;
    text-align: center;
}

.pagination-btn:hover {
    background: var(--primary-color);
    color: white;
}

.pagination-btn.active {
    background: var(--primary-color);
    color: white;
}

.pagination-btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

@media (max-width: 768px) {
    .books-grid-container {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 15px;
    }
    
    .view-options {
        margin-bottom: 15px;
    }
    
    .view-option {
        padding: 8px 15px;
        font-size: 0.9em;
    }
    
    .pagination-container {
        flex-wrap: wrap;
        gap: 5px;
    }
}

@media (max-width: 480px) {
    .books-grid-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewOptions = document.querySelectorAll('.view-option');
    const tableView = document.getElementById('tableView');
    const gridView = document.getElementById('gridView');
    
    viewOptions.forEach(option => {
        option.addEventListener('click', function() {
            
            viewOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            const viewMode = this.getAttribute('data-view');
            
            if (viewMode === 'books-grid') {
                tableView.style.display = 'none';
                gridView.style.display = 'block';
            } else {
                tableView.style.display = 'block';
                gridView.style.display = 'none';
            }
            
           
            localStorage.setItem('booksViewMode', viewMode);
        });
    });
    
    
    const savedViewMode = localStorage.getItem('booksViewMode');
    if (savedViewMode) {
        
        viewOptions.forEach(option => {
            if (option.getAttribute('data-view') === savedViewMode) {
                option.classList.add('active');
            } else {
                option.classList.remove('active');
            }
        });
        
        
        if (savedViewMode === 'books-grid') {
            tableView.style.display = 'none';
            gridView.style.display = 'block';
        } else {
            tableView.style.display = 'block';
            gridView.style.display = 'none';
        }
    }
});
</script>

<?php
include_once '../includes/footer.php';
?>
