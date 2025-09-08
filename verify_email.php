
<?php
include_once 'includes/config.php';
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
        $update = $conn->prepare("UPDATE users SET email_verified = TRUE, email_verification_token = NULL WHERE id = ?");
        $update->bind_param("i", $userId);
        $update->execute();

        $message = "Your email has been successfully verified. You can now log in.";
        $messageClass = "success";
    } else {
        $message = "Invalid or expired verification link.";
        $messageClass = "error";
    }
} else {
    $message = "No token provided.";
    $messageClass = "warning";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
     <link rel="icon" type="image/png" href="uploads/assests/book.png">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f5f7fa;
        }

        .container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 350px;
            width: 90%;
        }

        .icon {
            width: 70px;
            height: 70px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 20px;
        }

        .icon svg {
            width: 35px;
            height: 35px;
            fill: white;
        }

        h2 {
            font-size: 22px;
            margin: 10px 0;
            color: #333;
        }

        p {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 10px 20px;
            background: #007bff;
            color: #fff;
            border-radius: 6px;
            font-size: 14px;
            transition: 0.3s;
        }

        .btn:hover {
            background: #0056b3;
        }

        .error { color: #e74c3c; }
        .warning { color: #f39c12; }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M20.285 6.709l-11.285 11.285-5.285-5.285 1.414-1.414 3.871 3.871 9.871-9.871z"/>
            </svg>
        </div>
        <h2>Email Verification</h2>
        <p class="<?php echo $messageClass; ?>"><?php echo $message; ?></p>
        <a href="index.php" class="btn">Go to Login</a>
    </div>
</body>
</html>
