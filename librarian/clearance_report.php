<?php
include_once '../includes/header.php';

checkUserRole('librarian');
$message = '';
$clearStatus = false;
$userData = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check'])) {
    $searchTerm = trim($_POST['student_id']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? OR name LIKE ?");
    $likeTerm = "%$searchTerm%";
    $stmt->bind_param("is", $searchTerm, $likeTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();

    if ($userData) {
        $userId = $userData['id'];

        $issuedCheck = $conn->prepare("SELECT COUNT(*) AS issued_count FROM issued_books WHERE user_id = ? AND status = 'issued'");
        $issuedCheck->bind_param("i", $userId);
        $issuedCheck->execute();
        $issuedCount = $issuedCheck->get_result()->fetch_assoc()['issued_count'];

        $fineCheck = $conn->prepare("SELECT COUNT(*) AS unpaid_count FROM fines WHERE user_id = ? AND status != 'paid'");
        $fineCheck->bind_param("i", $userId);
        $fineCheck->execute();
        $unpaidCount = $fineCheck->get_result()->fetch_assoc()['unpaid_count'];

        if ($issuedCount == 0 && $unpaidCount == 0) {
            $clearStatus = true;
        } else {
            $message = " Student has pending issues (Issued books or unpaid fines).";
        }
    } else {
        $message = " No student found with that ID or Name.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Check Student Records</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, sans-serif;
        background: linear-gradient(135deg, #f9f5f0, #f0e3d6);
        margin: 0;
        padding: 0;
    }
    .container {
        max-width: 900px;
        min-height:500px;
        background: white;
        margin: 80px auto;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0px 6px 30px rgba(0,0,0,0.15);
        animation: fadeIn 0.6s ease-in-out;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(20px);}
        to {opacity: 1; transform: translateY(0);}
    }
    h2 {
        color: #7C4A2D;
        text-align: center;
        font-size: 2rem;
        margin-bottom: 25px;
    }
    label {
        display: block;
        margin-top: 15px;
        font-weight: bold;
        font-size: 1rem;
        color: #5A3620;
    }
    input[type="text"] {
        width: 100%;
        padding: 14px;
        margin-top: 8px;
        font-size: 1rem;
        border-radius: 12px;
        border: 1px solid #ccc;
        transition: 0.3s;
    }
    input[type="text"]:focus {
        border-color: #A66E4A;
        outline: none;
        box-shadow: 0px 0px 8px rgba(166,110,74,0.5);
    }
    button {
        background: linear-gradient(135deg, #7C4A2D, #A66E4A);
        color: white;
        padding: 14px;
        border: none;
        font-size: 1rem;
        border-radius: 12px;
        margin-top: 20px;
        cursor: pointer;
        transition: 0.3s;
        width: 100%;
        font-weight: bold;
    }
    button:hover {
        transform: scale(1.02);
        box-shadow: 0px 4px 12px rgba(0,0,0,0.2);
    }
    .result {
        margin-top: 25px;
        padding: 18px;
        font-size: 1.1rem;
        border-radius: 12px;
        text-align: center;
    }
    .success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    .error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>
</head>
<body>
<div class="container">
    <h2>Check Student Record Status</h2>
    <form method="POST">
        <label for="student_id">Search by Student ID or Name:</label>
        <input type="text" id="student_id" name="student_id" placeholder="Enter Student ID or Name" required>
        <button type="submit" name="check">Search & Check</button>
    </form>

    <?php if (!empty($message)): ?>
        <div class="result error"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($clearStatus && $userData): ?>
        <div class="result success"> Student records are clear.</div>
    <?php endif; ?>
</div>
</body>
</html>
