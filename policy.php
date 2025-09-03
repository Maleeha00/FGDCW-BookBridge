<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Policy page</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Poppins', sans-serif;
      background: #ffffffff;
    }

    .rules-header {
      background: linear-gradient(135deg, #977518ff, #9c6e17ff);
      color: #ffffffff;
      text-align: center;
      padding: 30px 20px;
      border-bottom: 2px solid #79623bff;
      font-family: 'Playfair Display', serif;
    }

    .rules-header h1 {
      margin: 0;
      font-size: 2rem;
    }

    .rules-container {
      padding: 30px;
      max-width: 900px;
      margin: auto;
    }

    .rule-item {
      display: flex;
      align-items: flex-start;
      padding: 15px 10px;
      border-bottom: 1px dashed #d4c3a3;
    }

    .rule-item i {
      font-size: 1.4rem;
      margin-right: 15px;
      color: #a67c52;
    }

    .rule-text {
      font-size: 1rem;
      line-height: 1.5;
    }

    .back-link {
      display: inline-block;
      margin: 20px auto;
      padding: 10px 20px;
      background: linear-gradient(135deg, #8b3e11ad, #9e3f22ff);
      color: white;
      border-radius: 8px;
      text-decoration:none;
    }

    .back-link:hover {
      background: linear-gradient(135deg, #8a6851ad, #611d08ff);
    }

    .back-btn
    {
      text-align:center;
    }
  </style>
</head>

<body>

  <div class="rules-header">
    <h1>📜 Library Rules & Regulations</h1>
  </div>

  <div class="rules-container">
    <div class="rule-item"><i class="fas fa-book-open"></i>
      <div class="rule-text">All borrowed books must be returned within the due date to avoid late fines.</div>
    </div>
    <div class="rule-item"><i class="fas fa-volume-mute"></i>
      <div class="rule-text">Remember you email or id in order to login.</div>
    </div>
    <div class="rule-item"><i class="fas fa-hand-holding-heart"></i>
      <div class="rule-text">You can reuquest or reserve one book at a time</div>
    </div>
    <div class="rule-item"><i class="fas fa-laptop"></i>
      <div class="rule-text">Library computers are for academic and research purposes only.</div>
    </div>
    <div class="rule-item"><i class="fas fa-users"></i>
      <div class="rule-text">Group discussions should be held in designated discussion rooms only.</div>
    </div>
    <div class="rule-item"><i class="fas fa-ban"></i>
      <div class="rule-text">Eating and drinking are strictly prohibited inside the library.</div>
    </div>
    <div class="rule-item"><i class="fas fa-id-card"></i>
      <div class="rule-text">Members must carry their library card at all times while inside.</div>
    </div>

    <div  class="back-btn">
      <a href="about.php" class="back-link"> Back to About</a>
    </div>
  </div>

</body>
</html>