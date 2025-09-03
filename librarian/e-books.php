<?php
include_once '../includes/header.php';

checkUserRole('librarian');
$message = '';
$messageType = '';
if (isset($_POST['upload_ebook'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $type = trim($_POST['type']);

    if (empty($title) || empty($author) || empty($type)) {
        $message = "Title ,author and type are required fields.";
        $messageType = "danger";
    } else if (!isset($_FILES['ebook_file']) || $_FILES['ebook_file']['error'] != 0) {
        $message = "Please select a valid e-book file to upload.";
        $messageType = "danger";
    } else {
        $coverImage = "";
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            $filename = $_FILES['cover']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($ext), $allowed)) {
                $newFilename = uniqid() . '.' . $ext;
                $uploadDir = '../uploads/ebook_covers/';
                
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $uploadFile = $uploadDir . $newFilename;
                
                if (move_uploaded_file($_FILES['cover']['tmp_name'], $uploadFile)) {
                    $coverImage = $uploadFile;
                }
            }
        }
        
        $fileUpload = uploadFile($_FILES['ebook_file'], '../uploads/ebooks/');
        
        if ($fileUpload['success']) {
            $stmt = $conn->prepare("
                INSERT INTO ebooks (title, author, category, description, file_path, file_size, file_type, cover_image, uploaded_by , type)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)
            ");
            
            $filePath = $fileUpload['file_path'];
            $fileSize = $fileUpload['file_size'];
            $fileType = $fileUpload['file_type'];
            $uploadedBy = $_SESSION['user_id'];
            
            $stmt->bind_param(
                "ssssssssis",
                $title, $author, $category, $description, $filePath, $fileSize, $fileType, $coverImage, $uploadedBy,$type
            );
            
            if ($stmt->execute()) {
                $message = "E-book uploaded successfully.";
                $messageType = "success";
            } else {
                $message = "Error uploading e-book: " . $stmt->error;
                $messageType = "danger";
            }
        } else {
            $message = "Error uploading file: " . $fileUpload['message'];
            $messageType = "danger";
        }
    }
}
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $stmt = $conn->prepare("SELECT file_path, cover_image FROM ebooks WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $ebook = $result->fetch_assoc();
        $filePath = $ebook['file_path'];
        $coverImage = $ebook['cover_image'];
        $stmt = $conn->prepare("DELETE FROM ebooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        
            if (!empty($coverImage) && file_exists($coverImage)) {
                unlink($coverImage);
            }
            
            $message = "E-book deleted successfully.";
            $messageType = "success";
        } else {
            $message = "Error deleting e-book: " . $stmt->error;
            $messageType = "danger";
        }
    } else {
        $message = "E-book not found.";
        $messageType = "warning";
    }
}
$sql = "ALTER TABLE ebooks ADD COLUMN IF NOT EXISTS cover_image VARCHAR(255)";
$conn->query($sql);

$categories = [];
$result = $conn->query("SELECT DISTINCT category FROM ebooks WHERE category != '' ORDER BY category");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$sql = "SELECT e.*, u.name as uploader_name 
        FROM ebooks e 
        LEFT JOIN users u 
        ON e.uploaded_by = u.id 
        WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (e.title LIKE ? OR e.author LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

if (!empty($category)) {
    $sql .= " AND e.category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY e.created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$ebooks = [];
while ($row = $result->fetch_assoc()) {
    $ebooks[] = $row;
}
?>

<h1 class="page-title">Manage E-Books</h1>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="d-flex justify-between align-center mb-4">
    <button class="btn btn-primary" data-modal-target="uploadEbookModal">
        <i class="fas fa-upload"></i> Upload New E-Book
    </button>
    
    <div class="d-flex">
        <form action="" method="GET" class="d-flex">
            <div class="form-group mr-2" style="margin-bottom: 0; margin-right: 10px;">
                <input type="text" name="search" placeholder="Search e-books..." class="form-control" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <?php

$categories = [
    "BS IT-1","BS IT-2","BS IT-3","BS IT-4","BS IT-5","BS IT-6","BS IT-7","BS IT-8",
    "BS ENG-1","BS ENG-2","BS ENG-3","BS ENG-4","BS ENG-5","BS ENG-6","BS ENG-7","BS ENG-8",
    "BS HPE-1","BS HPE-2","BS HPE-3","BS HPE-4","BS HPE-5","BS HPE-6","BS HPE-7","BS HPE-8",
    "1st Year Pre Engineering","2nd Year Pre Engineering",
    "1st Year Pre Medical","2nd Year Pre Medical",
    "1st Year Arts","2nd Year Arts",
    "1st Year ICS","2nd Year ICS"
];
?>

<div class="form-group mr-2" style="margin-bottom: 0; margin-right: 10px;"> 
    <select name="category" class="form-control">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat); ?>" 
                <?php echo $category == $cat ? 'selected' : ''; ?>>
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
<div class="ebooks-grid" style="margin-top: 30px;">
    <?php if (count($ebooks) > 0): ?>
        <?php foreach ($ebooks as $ebook): ?>
            <div class="ebook-card">
                <div class="ebook-cover">
                    <?php if (!empty($ebook['cover_image'])): ?>
                        <img src="<?php echo htmlspecialchars($ebook['cover_image']); ?>" alt="<?php echo htmlspecialchars($ebook['title']); ?>">
                    <?php else: ?>
                        <?php 
                        $fileType = strtolower($ebook['file_type']);
                        $iconClass = '';
                        $iconColor = '';

                        if (!empty($ebook['type'])): ?>
                            <div class="ebook-type">
                                <i class="fas fa-bookmark"></i>
                                <span><?php echo htmlspecialchars($ebook['type']); ?></span>
                            </div>
                        <?php endif; 

                        switch ($fileType) {
                            case 'pdf':
                                $iconClass = 'fas fa-file-pdf';
                                $iconColor = '#dc3545';
                                break;
                            case 'epub':
                                $iconClass = 'fas fa-book-open';
                                $iconColor = '#6f42c1';
                                break;
                            case 'doc':
                            case 'docx':
                                $iconClass = 'fas fa-file-word';
                                $iconColor = '#0d6efd';
                                break;
                            default:
                                $iconClass = 'fas fa-file-alt';
                                $iconColor = '#6c757d';
                        }
                        ?>
                        <i class="<?php echo $iconClass; ?> fa-4x" style="color: <?php echo $iconColor; ?>;"></i>
                    <?php endif; ?>
                    <span class="file-type-badge"><?php echo strtoupper($ebook['file_type']); ?></span>
                </div>
                
                <div class="ebook-info">
                    <h3 class="ebook-title"><?php echo htmlspecialchars($ebook['title']); ?></h3>
                    <p class="ebook-author">By <?php echo htmlspecialchars($ebook['author']); ?></p>
                    
                    <?php if (!empty($ebook['category'])): ?>
                        <div class="ebook-category">
                            <i class="fas fa-tag"></i>
                            <span><?php echo htmlspecialchars($ebook['category']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="ebook-meta">
                        <div class="meta-item">
                            <i class="fas fa-hdd"></i>
                            <span><?php echo htmlspecialchars($ebook['file_size']); ?></span>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?php echo date('M d, Y', strtotime($ebook['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="ebook-actions">
                    <a href="<?php echo htmlspecialchars($ebook['file_path']); ?>" class="btn btn-primary btn-sm" target="_blank">
                        <i class="fas fa-download"></i> Download
                    </a>
                    <a href="ebook_details.php?id=<?php echo $ebook['id']; ?>" class="btn btn-secondary btn-sm">
                        <i class="fas fa-info-circle"></i> Details
                    </a>
                    <a href="?delete=<?php echo $ebook['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirmDelete('Are you sure you want to delete this e-book?')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-warning">No e-books found.</div>
    <?php endif; ?>
</div>


<div class="modal-overlay" id="uploadEbookModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Upload New E-Book</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="author">Author <span class="text-danger">*</span></label>
                            <input type="text" id="author" name="author" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="form-group">
    <label for="category">Category</label>
    <select id="category" name="category" class="form-control" required>
        <option value="">Select category</option>
        <option value="BS IT-1">BS IT-1</option>
        <option value="BS IT-2">BS IT-2</option>
        <option value="BS IT-3">BS IT-3</option>
        <option value="BS IT-4">BS IT-4</option>
        <option value="BS IT-5">BS IT-5</option>
        <option value="BS IT-6">BS IT-6</option>
        <option value="BS IT-7">BS IT-7</option>
        <option value="BS IT-8">BS IT-8</option>
        <option value="BS ENG-1">BS ENG-1</option>
        <option value="BS ENG-2">BS ENG-2</option>
        <option value="BS ENG-3">BS ENG-3</option>
        <option value="BS ENG-4">BS ENG-4</option>
        <option value="BS ENG-5">BS ENG-5</option>
        <option value="BS ENG-6">BS ENG-6</option>
        <option value="BS ENG-7">BS ENG-7</option>
        <option value="BS ENG-8">BS ENG-8</option>
        <option value="BS HPE-1">BS HPE-1</option>
        <option value="BS HPE-2">BS HPE-2</option>
        <option value="BS HPE-3">BS HPE-3</option>
        <option value="BS HPE-4">BS HPE-4</option>
        <option value="BS HPE-5">BS HPE-5</option>
        <option value="BS HPE-6">BS HPE-6</option>
        <option value="BS HPE-7">BS HPE-7</option>
        <option value="BS HPE-8">BS HPE-8</option>
        <option value="1st Year Pre Engineering">1st Year Pre Engineering</option>
        <option value="2nd Year Pre Engineering">2nd Year Pre Engineering</option>
        <option value="1st Year Pre Medical">1st Year Pre Medical</option>
        <option value="2nd Year Pre Medical">2nd Year Pre Medical</option>
        <option value="1st Year Arts">1st Year Arts</option>
        <option value="2nd Year Arts">2nd Year Arts</option>
        <option value="1st Year ICS">1st Year ICS</option>
        <option value="2nd Year ICS">2nd Year ICS</option>
    </select>
</div>

                <div class="form-group">
                 <label for="type">Type <span class="text-danger">*</span></label>
                 <select id="type" name="type" class="form-control" required>
                 <option value="">Select Type</option>
                 <option value="pastpapers">Pastpapers</option>
                 <option value="ebooks">E-Books</option>
                 <option value="outlines">Outlines</option>
                 </select>
                 </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="cover">E-Book Cover Image</label>
                    <input type="file" id="cover" name="cover" class="form-control" accept="image/*">
                    <small class="text-muted">Supported formats: JPG, PNG, GIF. Max size: 2MB</small>
                </div>
                
                <div class="form-group">
                    <label for="ebook_file">E-Book File <span class="text-danger">*</span></label>
                    <input type="file" id="ebook_file" name="ebook_file" class="form-control" accept=".pdf,.doc,.docx,.epub" required>
                    <small class="text-muted">Supported formats: PDF, DOC, DOCX, EPUB. Max size: 100MB</small>
                </div>
                
                <div class="form-group text-right">
                    <button type="button" class="btn btn-secondary modal-close">Cancel</button>
                    <button type="submit" name="upload_ebook" class="btn btn-primary">Upload E-Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.ebooks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.ebook-card {
    background: var(--white);
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid var(--gray-200);
}

.ebook-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.ebook-cover {
    height: 200px;
    background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    border-bottom: 1px solid var(--gray-200);
    overflow: hidden;
    
}

.ebook-cover img {
    width: 100%;
    height: 100%;
    object-fit: fit;
}

.file-type-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: var(--primary-color);
    color: var(--white);
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8em;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.ebook-info {
    padding: 20px;
    flex: 1;
}

.ebook-title {
    font-size: 1.2em;
    font-weight: 700;
    color: var(--primary-color);
    margin: 0 0 8px 0;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.ebook-author {
    color: var(--text-light);
    font-size: 0.9em;
    margin: 0 0 15px 0;
    font-weight: 500;
}

.ebook-category {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 15px;
    color: var(--primary-color);
    font-size: 0.9em;
    font-weight: 500;
}

.ebook-category i {
    font-size: 0.8em;
}

.ebook-type {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 15px;
    color: var(--warning-color);
    font-size: 0.9em;
    font-weight: 500;
}

.ebook-type i {
    font-size: 0.8em;
}

.ebook-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    font-size: 0.85em;
    color: var(--text-light);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 6px;
}

.meta-item i {
    font-size: 0.9em;
    color: var(--primary-color);
}

.ebook-actions {
    padding: 15px 20px;
    background: var(--gray-100);
    display: flex;
    gap: 8px;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

.ebook-actions .btn {
    flex: 1;
    text-align: center;
    font-weight: 600;
    border-radius: 8px;
    transition: var(--transition);
    min-width: 90px;
}

.ebook-actions .btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    border: none;
}

.ebook-actions .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 71, 161, 0.3);
}

.ebook-actions .btn-secondary {
    background: var(--gray-400);
    border: none;
    color: var(--text-color);
}

.ebook-actions .btn-secondary:hover {
    background: var(--gray-500);
    transform: translateY(-1px);
    color: var(--white);
}

.ebook-actions .btn-danger:hover {
    transform: translateY(-1px);
}

@media (max-width: 768px) {
    .ebooks-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .ebook-actions {
        flex-direction: column;
        gap: 10px;
    }
    
    .ebook-actions .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .ebooks-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .ebook-card {
        margin: 0 -10px;
        border-radius: 10px;
    }
    
    .ebook-info {
        padding: 15px;
    }
    
    .ebook-actions {
        padding: 12px 15px;
    }
}
</style>

<?php
include_once '../includes/footer.php';
?>