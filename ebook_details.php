<?php
// Include header
include_once '../includes/header.php';

// Check if user is a librarian
checkUserRole('librarian');

// Get e-book ID from URL
$ebookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Process e-book update
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $type = trim($_POST['type']);
    $description = trim($_POST['description']);
    
    // Basic validation
    if (empty($title) || empty($author) || empty($type)) {
        $message = "Title, author, and type are required fields.";
        $messageType = "danger";
    } else {
        // Handle cover image upload
        $coverImage = $_POST['current_cover'] ?? ''; // Keep existing cover by default
        
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == 0) {
            $allowed = array('jpg', 'jpeg', 'png', 'gif');
            $filename = $_FILES['cover_image']['name'];
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($ext), $allowed)) {
                $newFilename = uniqid() . '.' . $ext;
                $uploadDir = '../uploads/covers/';
                
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $uploadFile = $uploadDir . $newFilename;
                
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadFile)) {
                    // Delete old cover image if it exists
                    if (!empty($coverImage) && file_exists($coverImage)) {
                        unlink($coverImage);
                    }
                    $coverImage = $uploadFile;
                }
            }
        }
        
        // Update e-book record
        $stmt = $conn->prepare("
            UPDATE ebooks 
            SET title = ?, author = ?, category = ?, type = ?, description = ?, cover_image = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param("ssssssi", $title, $author, $category, $type, $description, $coverImage, $ebookId);
        
        if ($stmt->execute()) {
            $message = "E-book updated successfully.";
            $messageType = "success";
        } else {
            $message = "Error updating e-book: " . $stmt->error;
            $messageType = "danger";
        }
    }
}

// Get e-book details
$stmt = $conn->prepare("
    SELECT e.*, u.name as uploader_name 
    FROM ebooks e
    LEFT JOIN users u ON e.uploaded_by = u.id
    WHERE e.id = ?
");
$stmt->bind_param("i", $ebookId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: e-books.php');
    exit();
}

$ebook = $result->fetch_assoc();
?>

<div class="container">
    <div class="d-flex justify-between align-center mb-4">
        <h1 class="page-title">E-book Details</h1>
        <a href="e-books.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to E-books
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="ebook-details">
        <div class="ebook-header">
            <div class="ebook-cover-section">
                <div class="ebook-cover-large">
                    <?php if (!empty($ebook['cover_image'])): ?>
                        <img src="<?php echo htmlspecialchars($ebook['cover_image']); ?>" alt="<?php echo htmlspecialchars($ebook['title']); ?>">
                    <?php else: ?>
                        <?php 
                        $fileType = strtolower($ebook['file_type']);
                        $iconClass = '';
                        $iconColor = '';
                        
                        switch ($fileType) {
                            case 'pdf':
                                $iconClass = 'fas fa-file-pdf';
                                $iconColor = '#dc3545';
                                break;
                            case 'doc':
                            case 'docx':
                                $iconClass = 'fas fa-file-word';
                                $iconColor = '#0d6efd';
                                break;
                            case 'epub':
                                $iconClass = 'fas fa-book';
                                $iconColor = '#6f42c1';
                                break;
                            default:
                                $iconClass = 'fas fa-file-alt';
                                $iconColor = '#6c757d';
                        }
                        ?>
                        <i class="<?php echo $iconClass; ?>" style="color: <?php echo $iconColor; ?>; font-size: 6em;"></i>
                    <?php endif; ?>
                </div>
            </div>
            <div>
                <h2 class="ebook-title"><?php echo htmlspecialchars($ebook['title']); ?></h2>
                <div class="ebook-meta">
                    <p>Uploaded by <?php echo htmlspecialchars($ebook['uploader_name']); ?> on 
                       <?php echo date('F j, Y', strtotime($ebook['created_at'])); ?></p>
                </div>
            </div>
            <div class="ebook-actions">
                <a href="<?php echo htmlspecialchars($ebook['file_path']); ?>" class="btn btn-primary" target="_blank">
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
        </div>

        <div class="file-info">
            <div class="file-info-item">
                <span class="file-info-label">File Type</span>
                <span class="file-info-value">
                    <i class="fas fa-file-<?php echo strtolower($ebook['file_type']) == 'pdf' ? 'pdf' : 'alt'; ?>"></i>
                    <?php echo strtoupper($ebook['file_type']); ?>
                </span>
            </div>
            <div class="file-info-item">
                <span class="file-info-label">File Size</span>
                <span class="file-info-value"><?php echo htmlspecialchars($ebook['file_size']); ?></span>
            </div>
            <div class="file-info-item">
                <span class="file-info-label">Category</span>
                <span class="file-info-value"><?php echo htmlspecialchars($ebook['category']); ?></span>
            </div>
        </div>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="current_cover" value="<?php echo htmlspecialchars($ebook['cover_image']); ?>">
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?php echo htmlspecialchars($ebook['title']); ?>" required>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="author">Author</label>
                        <input type="text" id="author" name="author" class="form-control"
                               value="<?php echo htmlspecialchars($ebook['author']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" class="form-control" required>
                    <option value="">Select category</option>
                    <?php 
                    $categories = [
                        'BS IT-1', 'BS IT-2', 'BS IT-3', 'BS IT-4', 'BS IT-5', 'BS IT-6', 'BS IT-7', 'BS IT-8',
                        'BS ENG-1', 'BS ENG-2', 'BS ENG-3', 'BS ENG-4', 'BS ENG-5', 'BS ENG-6', 'BS ENG-7', 'BS ENG-8',
                        'BS HPE-1', 'BS HPE-2', 'BS HPE-3', 'BS HPE-4', 'BS HPE-5', 'BS HPE-6', 'BS HPE-7', 'BS HPE-8',
                        '1st Year Pre Engineering', '2nd Year Pre Engineering',
                        '1st Year Pre Medical', '2nd Year Pre Medical',
                        '1st Year Arts', '2nd Year Arts', '1st Year ICS', '2nd Year ICS'
                    ];
                    foreach ($categories as $cat) {
                        $selected = ($ebook['category'] == $cat) ? 'selected' : '';
                        echo "<option value=\"$cat\" $selected>$cat</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="type">Type</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="">Select Type</option>
                    <?php 
                    $types = ['pastpapers', 'ebooks', 'outlines'];
                    foreach ($types as $t) {
                        $selected = ($ebook['type'] == $t) ? 'selected' : '';
                        echo "<option value=\"$t\" $selected>" . ucfirst($t) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="cover_image">Cover Image</label>
                <input type="file" id="cover_image" name="cover_image" class="form-control" accept="image/*">
                <small class="text-muted">Leave empty to keep current cover. Supported formats: JPG, PNG, GIF. Max size: 2MB</small>
                <?php if (!empty($ebook['cover_image'])): ?>
                    <div class="current-cover-preview">
                        <p><strong>Current Cover:</strong></p>
                        <img src="<?php echo htmlspecialchars($ebook['cover_image']); ?>" alt="Current cover" style="max-width: 100px; height: auto; border-radius: 5px;">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4"><?php echo htmlspecialchars($ebook['description']); ?></textarea>
            </div>

            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update E-book
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.ebook-cover-section {
    margin-right: 30px;
}

.ebook-cover-large {
    width: 200px;
    height: 250px;
    background: var(--gray-100);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.ebook-cover-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.ebook-header {
    display: flex;
    align-items: flex-start;
    margin-bottom: 30px;
    gap: 30px;
}

.current-cover-preview {
    margin-top: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
}

.current-cover-preview p {
    margin: 0 0 10px 0;
    font-size: 0.9em;
    color: #666;
}

@media (max-width: 768px) {
    .ebook-header {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .ebook-cover-section {
        margin-right: 0;
        margin-bottom: 20px;
    }
}
</style>

<?php
// Include footer
include_once '../includes/footer.php';
?>