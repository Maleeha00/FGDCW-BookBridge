<?php
include_once '../includes/config.php';
include_once '../includes/functions.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'faculty') {
    header('Location: ../index.php');
    exit();
}

$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

if (isset($_POST['request_book'])) {
    $bookId = (int)$_POST['book_id'];
    $notes = trim($_POST['notes']);

    $stmt = $conn->prepare("
        SELECT id FROM reservation_requests 
        WHERE book_id = ? AND user_id = ? AND status = 'pending'
    ");
    $stmt->bind_param("ii", $bookId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $message = "You already have a pending reservation request for this book.";
        $messageType = "warning";
    } else {
        
        $stmt = $conn->prepare("
            INSERT INTO reservation_requests (book_id, user_id, notes)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $bookId, $userId, $notes);
        
        if ($stmt->execute()) {
            $message = "Book reservation request submitted successfully!";
            $messageType = "success";
        } else {
            $message = "Error submitting request: " . $stmt->error;
            $messageType = "danger";
        }
    }
}

if (isset($_POST['request_reservation'])) {
    $bookId = (int)$_POST['book_id'];
    $notes = trim($_POST['notes']);
    
    $result = createReservationRequest($conn, $bookId, $userId, $notes);
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'danger';
}

$pendingFinesQuery = "
    SELECT SUM(f.amount) as total 
    FROM fines f 
    JOIN users u ON f.user_id = u.id 
    WHERE f.user_id = ? AND f.status = 'pending' AND u.role != 'faculty'
";
$stmt = $conn->prepare($pendingFinesQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$pendingFines = $result->fetch_assoc()['total'] ?? 0;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$categories = [];
$result = $conn->query("SELECT DISTINCT category FROM books WHERE category != '' ORDER BY category");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

$booksPerPage = 15;
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../uploads/assests/book.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--text-color);
        }

        /* Navigation Bar */
        .catalog-navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--primary-color);
        }

        .navbar-brand img {
            height: 65px;
            width: 100%;
        }

        .navbar-brand h1 {
            font-size: 1.6em;
            font-weight: bold;
            margin: 0;
        }

        .navbar-actions {
            display: flex;
            gap: 15px;
        }

        .nav-btn {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .catalog-btn {
            background: var(--secondary-color);
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .catalog-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .dashboard-btn,
.logout-btn {
    
    color: var(--white);
    border: none;
    transition: var(--transition);
    border-radius: var(--border-radius);
}


.dashboard-btn {
    border-radius: 25px;
    background: var(--primary-color);
}

.dashboard-btn:hover {
    background: var(--primary-light); 
}


.logout-btn {
    border-radius: 25px;
    background: var(--accent-color);
}

.logout-btn:hover {
    background: var(--primary-light); 
}

       
        .books-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .books-header {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            text-align: center;
        }

        .books-header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .search-filter-section {
            background: rgba(255, 255, 255, 0.9);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .search-filter-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input-group {
            flex: 2;
            min-width: 250px;
        }

        .filter-select-group {
            flex: 1;
            min-width: 200px;
        }

        .search-input, .filter-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--gray-300);
            border-radius: 25px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .search-input:focus, .filter-select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(139, 94, 60, 0.1);
        }

        .search-actions {
            display: flex;
            gap: 10px;
        }

        .search-btn, .clear-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-btn {
            background: var(--primary-color);
            color: white;
        }

        .search-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .clear-btn {
            background: var(--gray-400);
            color: white;
        }

        .clear-btn:hover {
            background: var(--gray-500);
            transform: translateY(-2px);
        }
        .view-toggle {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .view-btn {
            padding: 12px 25px;
            border: 2px solid var(--primary-color);
            background: white;
            color: var(--primary-color);
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-btn.active {
            background: var(--primary-color);
            color: white;
        }

        .view-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .fine-banner {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.3);
        }

    
        .alert {
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: #2e7d32;
            border: 1px solid rgba(76, 175, 80, 0.3);
        }

        .alert-warning {
            background: rgba(255, 152, 0, 0.1);
            color: #ef6c00;
            border: 1px solid rgba(255, 152, 0, 0.3);
        }

        .alert-danger {
            background: rgba(244, 67, 54, 0.1);
            color: #c62828;
            border: 1px solid rgba(244, 67, 54, 0.3);
        }

     
        .books-section {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

      
        .books-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .books-table th,
        .books-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray-300);
        }

        .books-table th {
            background: var(--gray-100);
            font-weight: 600;
            color: var(--text-color);
        }

        .books-table tr:hover {
            background: var(--gray-100);
        }

        .book-book_name-cell {
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

        .available {
            background: rgba(76, 175, 80, 0.1);
            color: #2e7d32;
        }

        .unavailable {
            background: rgba(244, 67, 54, 0.1);
            color: #c62828;
        }

        .books-grid {
            display: none;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .books-grid.active {
            display: grid;
        }

        .books-table.hidden {
            display: none;
        }

        .book-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
        }

        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .book-info {
            padding: 20px;
        }

        .book-book_name {
            font-size: 1.1em;
            font-weight: 700;
            margin: 0 0 8px 0;
            color: var(--primary-color);
            line-height: 1.3;
        }

        .book-author {
            color: var(--text-light);
            margin: 0 0 10px 0;
            font-style: italic;
        }

        .book-details {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 15px;
            font-size: 0.9em;
        }

        .book-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-request {
            background: var(--primary-color);
            color: white;
        }

        .btn-request:hover {
            background: var(--primary-dark);
        }

        .btn-reserve {
            background: var(--warning-color);
            color: white;
        }

        .btn-reserve:hover {
            background: #f57c00;
        }

        .btn-disabled {
            background: var(--gray-400);
            color: var(--gray-600);
            cursor: not-allowed;
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

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 15px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-book_name {
            margin: 0;
            font-size: 1.3em;
        }

        .modal-close {
            background: none;
            border: none;
            color: white;
            font-size: 1.5em;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .modal-body {
            padding: 30px;
        }

        .book-request-info {
            background: var(--gray-100);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .book-request-info h4 {
            color: var(--primary-color);
            margin: 0 0 10px 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--gray-300);
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(139, 94, 60, 0.1);
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
        }

        .modal-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background: var(--gray-300);
            color: var(--text-color);
        }

        .btn-cancel:hover {
            background: var(--gray-400);
        }

        .btn-submit {
            background: var(--primary-color);
            color: white;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
        }

       
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            margin-bottom: 15px;
            color: var(--text-color);
        }

       
        @media (max-width: 768px) {
            .navbar-container {
                flex-direction: column;
                gap: 15px;
            }

            .navbar-actions {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }

            .books-header h1 {
                font-size: 2em;
            }

            .books-header {
                padding: 20px;
            }

            .search-filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input-group,
            .filter-select-group {
                flex: none;
                min-width: auto;
            }

            .search-actions {
                justify-content: center;
            }

            .books-table {
                font-size: 0.9em;
            }

            .books-table th,
            .books-table td {
                padding: 10px 8px;
            }

            .books-grid {
                grid-template-columns: 1fr;
            }

            .books-section {
                padding: 20px;
            }

            .books-container {
                padding: 20px 15px;
            }

            .pagination-container {
                flex-wrap: wrap;
                gap: 5px;
            }
        }

        @media (max-width: 480px) {
            .books-header h1 {
                font-size: 1.8em;
            }

            .modal {
                margin: 10px;
            }

            .modal-header,
            .modal-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    
    <nav class="catalog-navbar">
        <div class="navbar-container">
            <a href="catalog.php" class="navbar-brand">
                <img src="../uploads/assests/book.png" alt="Library Logo" class="logo-image">
<span class="navbar-book_name">BookBridge</span>
            </a>
            
            <div class="navbar-actions">
                <a href="catalog.php" class="nav-btn catalog-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Catalog
                </a>
                <a href="dashboard.php" class="nav-btn dashboard-btn">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="../logout.php" class="nav-btn logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    
    <div class="books-container">
        
        <div class="books-header">
            <h1>All Books</h1>
            <p>Explore our complete collection of books</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : ($messageType == 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($pendingFines > 0): ?>
            <div class="fine-banner">
                <strong><i class="fas fa-exclamation-triangle"></i> Outstanding Fines:</strong>
                You have pending fines of <strong>PKR <?php echo number_format($pendingFines, 2); ?></strong>.
                <a href="fines.php" style="color: white; text-decoration: underline; margin-left: 10px;">
                    Pay Now
                </a>
            </div>
        <?php endif; ?>

        
        <div class="search-filter-section">
            <form method="GET" class="search-filter-form">
                <div class="search-input-group">
                    <input type="text" name="search" placeholder="Search books by book_name, author, or book number..." 
                           class="search-input" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-select-group">
                    <select name="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="search-actions">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if (!empty($search) || !empty($category)): ?>
                        <a href="all_books.php" class="clear-btn">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="view-toggle">
            <button class="view-btn active" onclick="switchView('table')">
                <i class="fas fa-list"></i> Table View
            </button>
            <button class="view-btn" onclick="switchView('grid')">
                <i class="fas fa-th"></i> Grid View
            </button>
        </div>

        <div class="books-section">
            <?php if (count($books) > 0): ?>
               
                <table class="books-table" id="tableView">
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
                                <td class="book-book_name-cell"><?php echo htmlspecialchars($book['book_name']); ?></td>
                                <td class="book-author-cell"><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo htmlspecialchars($book['category']); ?></td>
                                <td><?php echo htmlspecialchars($book['book_no']); ?></td>
                                <td><?php echo htmlspecialchars($book['publisher']); ?></td>
                                <td>
                                    <?php if ($book['available_quantity'] > 0): ?>
                                        <span class="availability-badge available">
                                            <?php echo $book['available_quantity']; ?> / <?php echo $book['total_quantity']; ?> available
                                        </span>
                                    <?php else: ?>
                                        <span class="availability-badge unavailable">Not available</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($pendingFines > 0): ?>
                                        <button class="action-btn btn-disabled" disabled book_name="Pay pending fines to reserve books">
                                            <i class="fas fa-ban"></i> Cannot Reserve
                                        </button>
                                    <?php elseif ($book['available_quantity'] > 0): ?>
                                        <button class="action-btn btn-request" onclick="openRequestModal(<?php echo $book['id']; ?>, '<?php echo addslashes($book['book_name']); ?>', '<?php echo addslashes($book['author']); ?>', '<?php echo addslashes($book['book_no']); ?>', <?php echo $book['available_quantity']; ?>)">
                                            <i class="fas fa-book"></i> Reserve
                                        </button>
                                    <?php else: ?>
                                        <button class="action-btn btn-reserve" onclick="openReservationModal(<?php echo $book['id']; ?>, '<?php echo addslashes($book['book_name']); ?>', '<?php echo addslashes($book['author']); ?>', '<?php echo addslashes($book['book_no']); ?>')">
                                            <i class="fas fa-bookmark"></i> Request
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Grid View -->
                <div class="books-grid" id="gridView">
                    <?php foreach ($books as $book): ?>
                        <div class="book-card">
                            <div class="book-info">
                                <h3 class="book-book_name"><?php echo htmlspecialchars($book['book_name']); ?></h3>
                                <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                                <div class="book-details">
                                    <span><strong>Category:</strong> <?php echo htmlspecialchars($book['category']); ?></span>
                                    <?php if (!empty($book['book_no'])): ?>
                                        <span><strong>Book No:</strong> <?php echo htmlspecialchars($book['book_no']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($book['publisher'])): ?>
                                        <span><strong>Publisher:</strong> <?php echo htmlspecialchars($book['publisher']); ?></span>
                                    <?php endif; ?>
                                    <span>
                                        <?php if ($book['available_quantity'] > 0): ?>
                                            <span class="availability-badge available">
                                                <?php echo $book['available_quantity']; ?> / <?php echo $book['total_quantity']; ?> available
                                            </span>
                                        <?php else: ?>
                                            <span class="availability-badge unavailable">Not available</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="book-actions">
                                    <?php if ($pendingFines > 0): ?>
                                        <button class="action-btn btn-disabled" disabled book_name="Pay pending fines to request books">
                                            <i class="fas fa-ban"></i> Cannot Reserve
                                        </button>
                                    <?php elseif ($book['available_quantity'] > 0): ?>
                                        <button class="action-btn btn-request" onclick="openRequestModal(<?php echo $book['id']; ?>, '<?php echo addslashes($book['book_name']); ?>', '<?php echo addslashes($book['author']); ?>', '<?php echo addslashes($book['book_no']); ?>', <?php echo $book['available_quantity']; ?>)">
                                            <i class="fas fa-book"></i> Reserve
                                        </button>
                                    <?php else: ?>
                                        <button class="action-btn btn-reserve" onclick="openReservationModal(<?php echo $book['id']; ?>, '<?php echo addslashes($book['book_name']); ?>', '<?php echo addslashes($book['author']); ?>', '<?php echo addslashes($book['book_no']); ?>')">
                                            <i class="fas fa-bookmark"></i> Request
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-book-open"></i>
                    <h3>No Books Found</h3>
                    <?php if (!empty($search) || !empty($category)): ?>
                        <p>No books match your search criteria. Try adjusting your search terms.</p>
                        <a href="all_books.php" class="action-btn btn-request" style="margin-top: 20px; display: inline-flex;">
                            <i class="fas fa-list"></i> View All Books
                        </a>
                    <?php else: ?>
                        <p>There are no books available in the library at the moment.</p>
                        <a href="catalog.php" class="action-btn btn-request" style="margin-top: 20px; display: inline-flex;">
                            <i class="fas fa-arrow-left"></i> Back to Catalog
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal-overlay" id="requestModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-book_name">Reserve Book</h3>
                <button class="modal-close" onclick="closeModal('requestModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="book-request-info" id="requestBookInfo">
                    
                </div>
                
                <form method="POST">
                    <input type="hidden" name="book_id" id="requestBookId">
                    
                    <div class="form-group">
                        <label for="requestNotes">Additional Notes (Optional)</label>
                        <textarea id="requestNotes" name="notes" class="form-control" rows="3" placeholder="Any special requests or notes..."></textarea>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="modal-btn btn-cancel" onclick="closeModal('requestModal')">Cancel</button>
                        <button type="submit" name="request_book" class="modal-btn btn-submit">
                            <i class="fas fa-paper-plane"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   
    <div class="modal-overlay" id="reservationModal">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-book_name">Request Book</h3>
                <button class="modal-close" onclick="closeModal('reservationModal')">&times;</button>
            </div>
            <div class="modal-body">
                <div class="book-request-info" id="reservationBookInfo">
                   
                </div>
                
                <div class="alert alert-info" style="margin-bottom: 20px;">
                    <i class="fas fa-magic"></i>
                    <strong>Auto-Issue Feature:</strong> When this book becomes available, it will be automatically issued to you! You'll receive a notification to collect it from the library.
                </div>
                
                <form method="POST">
                    <input type="hidden" name="book_id" id="reservationBookId">
                    
                    <div class="form-group">
                        <label for="reservationNotes">Additional Notes (Optional)</label>
                        <textarea id="reservationNotes" name="notes" class="form-control" rows="3" placeholder="Any special requests or notes..."></textarea>
                    </div>
                    
                    <div class="modal-actions">
                        <button type="button" class="modal-btn btn-cancel" onclick="closeModal('reservationModal')">Cancel</button>
                        <button type="submit" name="request_reservation" class="modal-btn btn-submit">
                            <i class="fas fa-bookmark"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        
        function switchView(viewType) {
            const tableView = document.getElementById('tableView');
            const gridView = document.getElementById('gridView');
            const viewBtns = document.querySelectorAll('.view-btn');
            
            viewBtns.forEach(btn => btn.classList.remove('active'));
            
            if (viewType === 'table') {
                tableView.style.display = 'table';
                gridView.classList.remove('active');
                document.querySelector('.view-btn:first-child').classList.add('active');
            } else {
                tableView.style.display = 'none';
                gridView.classList.add('active');
                document.querySelector('.view-btn:last-child').classList.add('active');
            }
            
            localStorage.setItem('preferredView', viewType);
        }

        
        document.addEventListener('DOMContentLoaded', function() {
            const savedView = localStorage.getItem('preferredView');
            if (savedView === 'grid') {
                switchView('grid');
            }
        });

        // Open request modal
        function openRequestModal(bookId, book_name, author, bookNo, availableQuantity) {
            document.getElementById('requestBookId').value = bookId;
            document.getElementById('requestBookInfo').innerHTML = `
                <h4>${book_name}</h4>
                <p style="margin: 5px 0; color: #666;">by ${author}</p>
                <p style="margin: 5px 0; color: #666;">Book No: ${bookNo}</p>
                <div style="margin-top: 10px;">
                    <span style="background: #28a745; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8em;">
                        ${availableQuantity} copies available
                    </span>
                </div>
            `;
            document.getElementById('requestModal').classList.add('active');
        }

        
        function openReservationModal(bookId, book_name, author, bookNo) {
            document.getElementById('reservationBookId').value = bookId;
            document.getElementById('reservationBookInfo').innerHTML = `
                <h4>${book_name}</h4>
                <p style="margin: 5px 0; color: #666;">by ${author}</p>
                <p style="margin: 5px 0; color: #666;">Book No: ${bookNo}</p>
                <div style="margin-top: 10px;">
                    <span style="background: #dc3545; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8em;">
                        Currently unavailable
                    </span>
                </div>
            `;
            document.getElementById('reservationModal').classList.add('active');
        }

        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal-overlay')) {
                e.target.classList.remove('active');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                    modal.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>
