<?php
include_once '../includes/config.php';

$type = isset($_GET['type']) ? $_GET['type'] : null;
$category = isset($_GET['category']) ? $_GET['category'] : null;

if ($type && $category) {
   $stmt = $conn->prepare("SELECT * FROM ebooks WHERE type = ? AND category = ?");
   $stmt->bind_param("ss", $type, $category);
   $stmt->execute();
   $result = $stmt->get_result();
} else {
  
   $result = $conn->query("SELECT * FROM ebooks");
}
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

$categories = [];
$sql = "
    SELECT 
        category,
        COUNT(*) as book_count
    FROM books
    WHERE category != '' AND category IS NOT NULL
    GROUP BY category 
    ORDER BY category
";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$totalBooksQuery = "SELECT COUNT(*) as total FROM books";
$totalBooksResult = $conn->query($totalBooksQuery);
$totalBooks = $totalBooksResult->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Catalog </title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="icon" type="image/png" href="../uploads/assests/book.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--text-color);
        }
        .catalog-navbar { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 15px 0; box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1); position: sticky; top: 0; z-index: 1000; }
        .navbar-container { max-width: 1400px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .navbar-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--primary-color); }
        .navbar-brand img { height: 65px; width: 100%; }
        .navbar-book_name { font-size: 1.6em; font-weight: bold; }
        .navbar-actions { display: flex; gap: 15px; }
        .nav-btn { padding: 10px 20px; border-radius: 25px; font-weight: 600; text-decoration: none; transition: all 0.3s ease; display: flex; align-items: center; gap: 8px; }
        .dashboard-btn, .logout-btn { color: var(--white); border: none; transition: var(--transition); border-radius: var(--border-radius); }
        .dashboard-btn { background: var(--primary-color); }
        .dashboard-btn:hover { background: var(--primary-light); }
        .logout-btn { background: var(--accent-color); }
        .logout-btn:hover { background: var(--primary-light); }
        .catalog-container { max-width: 1400px; margin: 0 auto; padding: 40px 20px; }
        .welcome-section { text-align: center; margin-bottom: 30px; background: rgba(255, 255, 255, 0.9); padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .welcome-section h1 { font-size: 3em; margin-bottom: 15px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .welcome-section p { font-size: 1.2em; color: var(--text-light); margin-bottom: 20px; }
        .toggle-buttons { margin-top: 10px; }
        .toggle-buttons button { padding: 10px 20px; margin: 0 5px; border: none; border-radius: 20px; background-color: #d2b48c; color: #fff; font-weight: bold; cursor: pointer; transition: 0.3s ease; }
        .toggle-buttons button.active { background-color: #a67b5b; }
        .section-content { display: none; }
        .section-content.active { display: block; }
        .library-shelves { background: rgba(255, 255, 255, 0.9); border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .shelves-header { text-align: center; margin-bottom: 40px; }
        .shelves-header h2 { font-size: 2.5em; color: var(--primary-color); margin-bottom: 10px; }
        .shelves-header p { font-size: 1.1em; color: var(--text-light); }
        .category-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; max-width: 1000px; margin: 0 auto; }
        .category-door { background: linear-gradient(145deg, #8B5E3C, #7C4A2D); border-radius: 15px; height: 200px; border: 3px solid #5A3620; cursor: pointer; overflow: hidden; position: relative; transition: all 0.4s ease; }
        .category-door:hover { transform: perspective(1000px) rotateY(-15deg) scale(1.05); }
        .door-content { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: white; text-align: center; position: relative; z-index: 2; }
        .door-icon { font-size: 3em; margin-bottom: 15px; color: #F9F5F0; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); }
        .door-book_name { font-size: 1.3em; font-weight: 700; margin-bottom: 8px; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3); }
        .door-subbook_name { font-size: 0.9em; opacity: 0.9; font-weight: 500; }
        .door-handle { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); width: 12px; height: 12px; background: #C97B4A; border-radius: 50%; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.3); }
    
     
    .almirah-container {
      padding: 30px 40px;
    }
    .almirah {
      margin-bottom: 50px;
      background-color: #9e7251ff;
      padding: 20px;
      border-radius: 15px;
      border: 2px solid #d4bfa8;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .almirah h2 {
      text-align: center;
      color: #5c3d26;
      margin-bottom: 20px;
      font-size: 24px;
    }
    .sem-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      justify-items: center;
    }
    .sem-box {
      background-color: #fff;
      border: 1px solid #d9c5b2;
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      width: 100%;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      transition: transform 0.3s;
    }
    .sem-box:hover {
      transform: translateY(-5px);
    }
    .sem-box i {
      font-size: 26px;
      color: #8b5e3c;
      margin-bottom: 10px;
    }
    .sem-box h3 {
      font-size: 18px;
      color: #5c3d26;
      margin-bottom: 5px;
    }
    .sem-box p {
      color: #7c5c44;
      font-size: 14px;
    }
    .sub-links p { margin: 5px 0; }
    .sub-links a { color: #8b5e3c; font-weight: bold; text-decoration: none; }
    .sub-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <nav class="catalog-navbar">
        <div class="navbar-container">
            <a href="#" class="navbar-brand">
                <img src="../uploads/assests/book.png" alt="Library Logo">
                <span class="navbar-book_name">BookBridge</span>
            </a>
            <div class="navbar-actions">
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

    <div class="catalog-container">
        <div class="welcome-section">
           <h1><span style="font-family: 'Lucida Handwriting';">
             Welcome to Our Book Bridge
          </span></h1>

            <div class="toggle-buttons">
                <button class="active" onclick="toggleSection('resources')" id="btn-resources">All Resources</button>
                <button onclick="toggleSection('ebooks')" id="btn-ebooks">Ebooks</button>
            </div>
        </div>

        <div id="resources" class="library-shelves section-content active">
            <div class="shelves-header">
                <h2>Browse by Category</h2>
                <p>Click on any door to explore our collection</p>
            </div>

            <?php
            $categoryIcons = [
                "Biology" => "fas fa-dna",
                "Chemistry" => "fas fa-flask",
                "Physics" => "fas fa-atom",
                "Urdu Novels" => "fas fa-feather-alt",
                "Islamiat" => "fas fa-mosque",
                "Computer" => "fas fa-laptop-code",
                "Science" => "fas fa-flask",
                "Economics" => "fas fa-chart-line",
                "English" => "fas fa-book-open",
                "HPE" => "fas fa-dumbbell",
                "Information Technology" => "fas fa-network-wired",
                "Mathematics" => "fa-solid fa-square-root-alt",
                "Urdu" => "fa-solid fa-pen-nib",
                "Pakistan Studies" => "fa-solid fa-flag",
                "Computer Science" => "fa-solid fa-laptop-code",
                "History" => "fas fa-landmark",
                "Geography" => "fas fa-globe-asia",
                "Political Science" => "fas fa-balance-scale-left",
                "Sociology" => "fas fa-users",
                "Psychology" => "fas fa-brain",
                "Business Studies" => "fas fa-briefcase",
                "Art & Design" => "fas fa-palette",
                "Philosophy" => "fas fa-comment-dots"
            ];
            ?>

            <div class="category-grid">
                <div class="category-door" onclick="window.location.href='all_books.php'">
                    <div class="door-content">
                        <div class="door-icon"><i class="fas fa-layer-group"></i></div>
                        <div class="door-book_name">All Books</div>
                        <div class="door-subbook_name"><?php echo $totalBooks; ?> books</div>
                    </div>
                    <div class="door-handle"></div>
                </div>
                <?php foreach ($categories as $category): 
                    $catName = $category['category'];
                    
                    $icon = isset($categoryIcons[$catName]) ? $categoryIcons[$catName] : "fas fa-book";
                ?>
                <div class="category-door" onclick="window.location.href='category_books.php?category=<?php echo urlencode($catName); ?>'">
                    <div class="door-content">
                        <div class="door-icon"><i class="<?php echo $icon; ?>"></i></div>
                        <div class="door-book_name"><?php echo htmlspecialchars($catName); ?></div>
                        <div class="door-subbook_name"><?php echo $category['book_count']; ?> books</div>
                    </div>
                    <div class="door-handle"></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    
        <div id="ebooks" class="library-shelves section-content">
    <div class="shelves-header">
        <h2>Remotely Accessible Ebooks</h2>
        <p>Access ebooks from anywhere at any time</p>
    </div>

    <div class="almirah-container">

      
        <div class="almirah">
            <h2 style="color: #ffffffff;">BS Information Technology</h2>
            <div class="sem-grid">
               <div class="sem-box">
    <i class="fas fa-desktop"></i><h3>Semester 1</h3>
    <div class="sub-links">
        <p><a href="ebooks.php?type=ebooks&category=BS IT-1">E-Books</a></p>
        <p><a href="ebooks.php?type=outlines&category=BS IT-1">Outlines</a></p>
         <p><a href="ebooks.php?type=pastpapers&category=BS IT-1">Past Papers</a></p>
        

    </div>
</div>
               <div class="sem-box">
    <i class="fas fa-microchip"></i><h3>Semester 2</h3>
    <div class="sub-links">
        <p><a href="ebooks.php?type=ebooks&category=BS IT-2">Ebooks</a></p>
        <p><a href="ebooks.php?type=outlines&category=BS IT-2">Outlines</a></p>
        <p><a href="ebooks.php?type=pastpapers&category=BS IT-2">Past Papers</a></p>
    </div>
</div>
<div class="sem-box">
              <i class="fas fa-database"></i><h3>Semester 3</h3>
              <div class="sub-links">
                <p><a href="ebooks.php?type=ebooks&category=BS IT-3">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS IT-3">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS IT-3">Past Papers</a></p>
              </div>
            </div>
                
            <div class="sem-box">
              <i class="fas fa-network-wired"></i><h3>Semester 4</h3>
              <div class="sub-links">
                <p><a href="ebooks.php?type=ebooks&category=BS IT-4">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS IT-4">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS IT-4">Past Papers</a></p>
              </div>
            </div>
                <div class="sem-box">
                    <i class="fas fa-robot"></i><h3>Semester 5</h3>
                    <div class="sub-links">
                       <p><a href="ebooks.php?type=ebooks&category=BS IT-5">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS IT-5">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS IT-5">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-database"></i><h3>Semester 6</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS IT-6">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS IT-6">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS IT-6">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-network-wired"></i><h3>Semester 7</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS IT-7">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS IT-7">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS IT-7">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-cloud"></i><h3>Semester 8</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS IT-8">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS IT-8">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS IT-8">Past Papers</a></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="almirah">
            <h2 style="color: #ffffffff;">BS Health and Physical Education</h2>
            <div class="sem-grid">
                <div class="sem-box">
                    <i class="fas fa-running"></i><h3>Semester 1</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS HPE-1">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS HPE-1">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS HPE-1">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-volleyball-ball"></i><h3>Semester 2</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS HPE-2">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS HPE-2">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS HPE-2">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-biking"></i><h3>Semester 3</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS HPE-3">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS HPE-3">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS HPE-3">Past Papers</a></p>
                    </div>
                </div>
<div class="sem-box">
                    <i class="fas fa-table-tennis"></i><h3>Semester 4</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS HPE-4">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS HPE-4">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS HPE-4">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-first-aid"></i><h3>Semester 5</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS HPE-5">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS HPE-5">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS HPE-5">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-dumbbell"></i><h3>Semester 6</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS HPE-6">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS HPE-6">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS HPE-6">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-heartbeat"></i><h3>Semester 7</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS HPE-7">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS HPE-7">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS HPE-7">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-swimmer"></i><h3>Semester 8</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS HPE-8">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS HPE-8">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS HPE-8">Past Papers</a></p>
                    </div>
                </div>
            </div>
        </div>
       
        <div class="almirah">
            <h2 style="color: #ffffffff;">BS English</h2>
            <div class="sem-grid">
                <div class="sem-box">
                    <i class="fas fa-book-open"></i><h3>Semester 1</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS ENG-1">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS ENG-1">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS ENG-1">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-newspaper"></i><h3>Semester 2</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS ENG-2">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS ENG-2">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS ENG-2">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-pen-fancy"></i><h3>Semester 3</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS ENG-3">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS ENG-3">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS ENG-3">Past Papers</a></p>
                    </div>
                </div>
<div class="sem-box">
                    <i class="fas fa-theater-masks"></i><h3>Semester 4</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS ENG-4">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS ENG-4">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS ENG-4">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-book"></i><h3>Semester 5</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS ENG-5">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS ENG-5">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS ENG-5">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-keyboard"></i><h3>Semester 6</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS ENG-6">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS ENG-6">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS ENG-6">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-microphone-alt"></i><h3>Semester 7</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS ENG-7">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS ENG-7">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS ENG-7">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-film"></i><h3>Semester 8</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=BS ENG-8">Ebooks</a></p>
                <p><a href="ebooks.php?type=outlines&category=BS ENG-8">Outlines</a></p>
                <p><a href="ebooks.php?type=pastpapers&category=BS ENG-8">Past Papers</a></p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="almirah">
            <h2 style="color: #ffffffff;">Intermediate</h2>
            <div class="sem-grid">
                <div class="sem-box">
                    <i class="fas fa-pencil-alt"></i><h3>1 year</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=1st Year Pre Engineering">Ebooks</a></p>
                        <p><a href="ebooks.php?type=pastpapers&category=1st Year Pre Engineering">Past Papers</a></p>
                    </div>
                </div>
                <div class="sem-box">
                    <i class="fas fa-calculator"></i><h3>2nd year</h3>
                    <div class="sub-links">
                        <p><a href="ebooks.php?type=ebooks&category=2nd Year Pre Engineering">Ebooks</a></p>
                        <p><a href="ebooks.php?type=pastpapers&category=2nd Year Pre Engineering">Past Papers</a></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</div>
    </div>
    <script>
        function toggleSection(section) {
            document.getElementById('resources').classList.remove('active');
            document.getElementById('ebooks').classList.remove('active');
            document.getElementById('btn-resources').classList.remove('active');
            document.getElementById('btn-ebooks').classList.remove('active');
            document.getElementById(section).classList.add('active');
            document.getElementById('btn-' + section).classList.add('active');
        }
    </script>
        
</body>
</html>