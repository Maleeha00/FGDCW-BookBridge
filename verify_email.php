<?php
include_once '../includes/config.php';

$message = "";
$messageClass = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email_verification_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];

        // Mark email as verified
        $update = $conn->prepare("UPDATE users SET email_verified = TRUE, email_verification_token = NULL WHERE id = ?");
        $update->bind_param("i", $userId);
        $update->execute();

        $message = "🎉 Your email has been successfully verified. You can now log in.";
        $messageClass = "success";
    } else {
        $message = "❌ Invalid or expired verification link.";
        $messageClass = "error";
    }
} else {
    $message = "⚠️ No token provided.";
    $messageClass = "warning";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #74ebd5, #ACB6E5);
            animation: bgMove 10s infinite alternate;
        }

        @keyframes bgMove {
            0% { background-position: 0 0; }
            100% { background-position: 100% 100%; }
        }

        .container {
            background: #fff;
            padding: 35px 25px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
            width: 90%;
            max-width: 400px;
            animation: fadeIn 1.2s ease;
        }

        @keyframes fadeIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            font-weight: 600;
        }

        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 15px;
            font-weight: 500;
            animation: pop 0.5s ease;
        }

        @keyframes pop {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .warning { background: #fff3cd; color: #856404; }

        a {
            display: inline-block;
            margin-top: 10px;
            text-decoration: none;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border-radius: 8px;
            font-weight: 500;
            transition: 0.3s;
        }

        a:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Email Verification</h2>
        <div class="message <?php echo $messageClass; ?>">
            <?php echo $message; ?>
        </div>
        <a href="../index.php">Go to Login</a>
    </div>
</body>
</html>
