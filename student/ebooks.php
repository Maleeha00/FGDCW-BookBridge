<?php
include_once '../includes/header.php';

if ($_SESSION['role'] != 'student' && $_SESSION['role'] != 'faculty') {
    header('Location: ../index.php');
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

$categories = [];
$result = $conn->query("SELECT DISTINCT type FROM ebooks WHERE type != '' ORDER BY type");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['type'];
    }
}

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

if (!empty($type)) {
    $sql .= " AND e.type = ?";
    $params[] = $type;
    $types .= "s";
}

if (!empty($category)) {
    $sql .= " AND e.category = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY e.created_at DESC";

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

<div class="container">
    <h1 class="page-title">
        <?php if (!empty($category) && !empty($type)): ?>
            <?php echo htmlspecialchars($category); ?> - <?php echo ucfirst(htmlspecialchars($type)); ?>
        <?php else: ?>
            E-Books Library
        <?php endif; ?>
    </h1>

    <?php if (!empty($category) && !empty($type)): ?>
        <div class="breadcrumb-nav">
            <a href="catalog.php" class="breadcrumb-link">
                <i class="fas fa-home"></i> Catalog
            </a>
            <span class="breadcrumb-separator">></span>
            <a href="ebooks.php" class="breadcrumb-link">E-Books</a>
            <span class="breadcrumb-separator">></span>
            <span class="breadcrumb-current"><?php echo htmlspecialchars($category); ?> - <?php echo ucfirst(htmlspecialchars($type)); ?></span>
        </div>
    <?php endif; ?>

    <div class="search-section mb-4">
        <form action="" method="GET" class="search-form">
            <?php if (!empty($category)): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
            <?php endif; ?>
            <?php if (!empty($type)): ?>
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">
            <?php endif; ?>
            
            <div class="search-row">
                <div class="search-input-group">
                    <input type="text" name="search" placeholder="Search e-books by title or author..."
                           class="form-control search-input" value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <?php if (empty($category) && empty($type)): ?>
                    <div class="search-select-group">
                        <select name="type" class="form-control type-select">
                            <option value="">All Types</option>
                            <?php 
                            $types = ['ebooks', 'outlines', 'pastpapers']; 
                            foreach ($types as $t): ?>
                                <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $type == $t ? 'selected' : ''; ?>>
                                    <?php echo ucfirst(htmlspecialchars($t)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="search-select-group">
                        <select name="category" class="form-control type-select">
                            <option value="">All Categories</option>
                            <?php 
                            $categoryResult = $conn->query("SELECT DISTINCT category FROM ebooks WHERE category != '' ORDER BY category");
                            if ($categoryResult) {
                                while ($row = $categoryResult->fetch_assoc()) {
                                    $selected = ($category == $row['category']) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($row['category']) . "' $selected>" . htmlspecialchars($row['category']) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="search-button-group">
                    <button type="submit" class="btn btn-primary search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if (!empty($search) || (!empty($type) && empty($category)) || (!empty($category) && empty($type))): ?>
                        <a href="ebooks.php" class="btn btn-secondary clear-btn">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php elseif (!empty($search) && !empty($category) && !empty($type)): ?>
                        <a href="ebooks.php?category=<?php echo urlencode($category); ?>&type=<?php echo urlencode($type); ?>" class="btn btn-secondary clear-btn">
                            <i class="fas fa-times"></i> Clear Search
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <?php if (count($ebooks) > 0): ?>
        <div class="ebooks-grid">
            <?php foreach ($ebooks as $ebook): ?>
                <div class="ebook-card">
                    <div class="ebook-cover">
                        <?php if (!empty($ebook['cover_image'])): ?>
                            <img src="<?php echo htmlspecialchars($ebook['cover_image']); ?>" alt="<?php echo htmlspecialchars($ebook['title']); ?>">
                        <?php else: ?>
                            <div class="ebook-icon">
                                <?php
                                $fileType = strtolower($ebook['file_type']);
                                $iconClass = '';
                                $iconColor = '';

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
                                <i class="<?php echo $iconClass; ?>" style="color: <?php echo $iconColor; ?>; font-size: 4em;"></i>
                            </div>
                        <?php endif; ?>
                        <span class="file-type-badge"><?php echo strtoupper($ebook['file_type']); ?></span>
                    </div>

                    <div class="ebook-info">
                        <h3 class="ebook-title"><?php echo htmlspecialchars($ebook['title']); ?></h3>
                        <p class="ebook-author">By <?php echo htmlspecialchars($ebook['author']); ?></p>

                        <?php if (!empty($ebook['type'])): ?>
                            <div class="ebook-type">
                                <i class="fas fa-tag"></i>
                                <span><?php echo htmlspecialchars($ebook['type']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($ebook['category'])): ?>
                            <div class="ebook-category">
                                <i class="fas fa-graduation-cap"></i>
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

                        <?php if (!empty($ebook['description'])): ?>
                            <div class="ebook-description">
                                <?php
                                $description = htmlspecialchars($ebook['description']);
                                echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="ebook-actions">
                        <a href="<?php echo htmlspecialchars($ebook['file_path']); ?>" class="btn btn-primary" download>
                            <i class="fas fa-download"></i> Download
                        </a>
                        
                        <a href="<?php echo htmlspecialchars($ebook['file_path']); ?>" class="btn btn-primary" target="_blank">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <button class="btn btn-secondary btn-sm ebook-details-btn" data-ebook-id="<?php echo $ebook['id']; ?>">
                            <i class="fas fa-info-circle"></i> Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results">
            <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
            <h3>No E-Books Found</h3>
            <p class="text-muted">
                <?php if (!empty($search) || !empty($type) || !empty($category)): ?>
                    No e-books match your search criteria. Try adjusting your search terms.
                <?php else: ?>
                    No e-books are currently available in the library.
                <?php endif; ?>
            </p>
            <?php if (!empty($search) || !empty($type) || !empty($category)): ?>
                <a href="ebooks.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> View All E-Books
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>


<div class="modal-overlay" id="ebookModal">
    <div class="modal ebook-modal">
        <div class="modal-header">
            <h3 class="modal-title">E-book Details</h3>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body" id="modalContent">
            
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary modal-close">Close</button>
            <a href="#" id="modalDownloadBtn" class="btn btn-primary" target="_blank">
                <i class="fas fa-download"></i> Download E-book
            </a>
        </div>
    </div>
</div>

<style>
.search-section {
    background: var(--white);
    padding: 20px;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    margin-bottom: 30px;
}

.search-form {
    width: 100%;
}

.search-row {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.search-input-group {
    flex: 2;
    min-width: 250px;
}

.search-select-group {
    flex: 1;
    min-width: 200px;
}

.search-button-group {
    display: flex;
    gap: 10px;
}

.search-input, .type-select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid var(--gray-300);
    border-radius: var(--border-radius);
    font-size: 1em;
    transition: var(--transition);
}

.search-input:focus, .type-select:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(13, 71, 161, 0.1);
}

.search-btn, .clear-btn {
    padding: 12px 20px;
    white-space: nowrap;
    font-weight: 500;
}

.clear-btn {
    background-color: var(--gray-400);
    color: var(--white);
}

.clear-btn:hover {
    background-color: var(--gray-500);
}

.ebooks-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 18px;
}

.ebook-card {
    background: var(--white);
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid var(--gray-200);
    display: flex;
    flex-direction: column;
    height: 100%;
}

.ebook-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}

.ebook-cover {
    background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
    text-align: center;
    position: relative;
    border-bottom: 1px solid var(--gray-200);
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ebook-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.file-type-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--primary-color);
    color: var(--white);
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.75em;
    font-weight: 600;
}

.ebook-info {
    padding: 18px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.ebook-title {
    font-size: 1.1em;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 6px;
    line-height: 1.2;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.ebook-author {
    color: var(--text-light);
    font-size: 0.95em;
    margin-bottom: 10px;
    font-weight: 500;
}

.ebook-type {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
    color: var(--primary-color);
    font-size: 0.85em;
}

.ebook-category {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
    color: var(--success-color);
    font-size: 0.85em;
    font-weight: 600;
}

.breadcrumb-nav {
    background: rgba(255, 255, 255, 0.9);
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.breadcrumb-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
}

.breadcrumb-link:hover {
    color: var(--primary-dark);
    text-decoration: underline;
}

.breadcrumb-separator {
    margin: 0 10px;
    color: var(--text-light);
}

.breadcrumb-current {
    color: var(--text-color);
    font-weight: 600;
}

.ebook-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 0.8em;
    color: var(--text-light);
}

.meta-item i {
    font-size: 0.85em;
    color: var(--primary-color);
}

.ebook-description {
    color: var(--text-color);
    font-size: 0.85em;
    line-height: 1.4;
    background: var(--gray-100);
    padding: 10px;
    border-radius: 6px;
    border-left: 3px solid var(--primary-color);
    margin-bottom: 12px;
    max-height: 70px;
    overflow: hidden;
}

.ebook-actions {
    padding: 15px 18px;
    background: var(--gray-100);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 5px; 
}

.ebook-actions .btn {
    font-weight: 600;
    border-radius: 6px;
    transition: var(--transition);
    padding: 8px 12px;  b
    font-size: 0.8em; 
    flex: 1; 
    text-align: center;
}


.ebook-actions .btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    border: none;
    color: var(--white);
}

.ebook-actions .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 71, 161, 0.3);
}

.ebook-actions .btn-secondary {
    background: var(--gray-400);
    color: var(--text-color);
}

.ebook-actions .btn-secondary:hover {
    background: var(--gray-500);
    transform: translateY(-1px);
    color: var(--white);
}


.ebook-modal .modal {
    max-width: 600px;
}

.no-results {
    text-align: center;
    padding: 60px 20px;
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
}

.no-results h3 {
    color: var(--text-color);
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .search-row {
        flex-direction: column;
        align-items: stretch;
    }

    .search-input-group,
    .search-select-group {
        flex: none;
        min-width: auto;
    }

    .search-button-group {
        justify-content: center;
    }

    .search-btn, .clear-btn {
        flex: 1;
        max-width: 150px;
    }

    .ebooks-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

@media (max-width: 480px) {
    .ebook-card {
        margin: 0 -10px;
        border-radius: 10px;
    }

    .ebook-info {
        padding: 20px;
    }
    .ebook-actions {
        padding: 15px 20px;
        flex-direction: row; 
    }
    .ebook-actions .btn {
        width: auto;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailsButtons = document.querySelectorAll('.ebook-details-btn');
    const modal = document.getElementById('ebookModal');
    const modalContent = document.getElementById('modalContent');
    const modalDownloadBtn = document.getElementById('modalDownloadBtn');
    const modalCloseButtons = document.querySelectorAll('.modal-close');
    const modalOverlay = document.querySelector('.modal-overlay');
    
    const ebooks = <?php echo json_encode($ebooks); ?>;

    detailsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const ebookId = parseInt(this.getAttribute('data-ebook-id'));
            const ebook = ebooks.find(e => e.id == ebookId);

            if (ebook) {
               
                const fileType = ebook.file_type.toLowerCase();
                let iconClass = 'fas fa-file-alt';
                let iconColor = '#6c757d';

                switch (fileType) {
                    case 'pdf':
                        iconClass = 'fas fa-file-pdf';
                        iconColor = '#dc3545';
                        break;
                    case 'epub':
                        iconClass = 'fas fa-book-open';
                        iconColor = '#6f42c1';
                        break;
                    case 'doc':
                    case 'docx':
                        iconClass = 'fas fa-file-word';
                        iconColor = '#0d6efd';
                        break;
                }

                modalContent.innerHTML = `
                    <div class="ebook-detail-content">
                        <div class="detail-header">
                            <div class="detail-icon">
                                <i class="${iconClass}" style="color: ${iconColor};"></i>
                            </div>
                            <div class="detail-title">
                                <h4>${ebook.title}</h4>
                                <p>By ${ebook.author}</p>
                            </div>
                        </div>

                        <div class="detail-info">
                            <div class="info-row">
                                <span class="info-label">type:</span>
                                <span class="info-value">${ebook.type || 'Not specified'}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">File Type:</span>
                                <span class="info-value">${ebook.file_type.toUpperCase()}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">File Size:</span>
                                <span class="info-value">${ebook.file_size}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Added On:</span>
                                <span class="info-value">${new Date(ebook.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                            </div>
                            ${ebook.uploader_name ? `
                            <div class="info-row">
                                <span class="info-label">Uploaded By:</span>
                                <span class="info-value">${ebook.uploader_name}</span>
                            </div>
                            ` : ''}
                        </div>

                        ${ebook.description ? `
                        <div class="detail-description">
                            <h5>Description</h5>
                            <p>${ebook.description.replace(/\n/g, '<br>')}</p>
                        </div>
                        ` : ''}
                    </div>
                `;

                modalDownloadBtn.href = ebook.file_path;
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });
    });

   
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    modalCloseButtons.forEach(button => {
        button.addEventListener('click', closeModal);
    });

    modalOverlay.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });
});
</script>

<style>
.ebook-detail-content {
    padding: 10px 0;
}

.detail-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--gray-200);
}

.detail-icon i {
    font-size: 3em;
}

.detail-title h4 {
    margin: 0 0 5px 0;
    color: var(--primary-color);
    font-size: 1.4em;
}

.detail-title p {
    margin: 0;
    color: var(--text-light);
    font-size: 1.1em;
}

.detail-info {
    margin-bottom: 25px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--gray-200);
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: var(--text-light);
    flex: 0 0 120px;
}

.info-value {
    color: var(--text-color);
    font-weight: 500;
    text-align: right;
}

.detail-description {
    background: var(--gray-100);
    padding: 20px;
    border-radius: 10px;
    border-left: 4px solid var(--primary-color);
}

.detail-description h5 {
    margin: 0 0 15px 0;
    color: var(--primary-color);
    font-size: 1.1em;
}

.detail-description p {
    margin: 0;
    line-height: 1.6;
    color: var(--text-color);
}

@media (max-width: 768px) {
    .detail-header {
        flex-direction: column;
        text-align: center;
    }

    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }

    .info-value {
        text-align: left;
    }
}
</style>

<?php include_once '../includes/footer.php'; ?>