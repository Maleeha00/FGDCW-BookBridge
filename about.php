<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About | BookBridge</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

  <!-- Required CSS Links for Auth Navbar -->
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/auth.css">
  <link rel="icon" type="image/svg+xml" href="uploads/assests/book.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      scroll-behavior: smooth;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: #f0f2f5;
      color: #333;
    }

    section {
      padding: 80px 20px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .section-book_name {
      font-size: 3rem;
      margin-bottom: 30px;
      color: #444;
    }

    .info-block {
      background: rgba(255,255,255,0.85);
      border-radius: 20px;
      padding: 40px;
      margin: 20px auto;
      max-width: 1000px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.1);
      display: flex;
      gap: 40px;
      align-items: center;
      transition: transform 0.4s ease;
      opacity: 0;
      transform: translateY(50px);
    }

    .info-block.show {
      opacity: 1;
      transform: translateY(0) scale(1.02);
      transition: all 1s ease;
    }

    .info-block.reverse {
      flex-direction: row-reverse;
    }

    .info-block.center {
      justify-content: center;
      text-align: center;
    }

    .info-text {
      flex: 1;
    }

    .info-text h2 {
      color: #764ba2;
      font-size: 2rem;
      margin-bottom: 15px;
    }

    .info-text p {
      font-size: 1.1rem;
      color: #555;
    }

    .info-image {
      flex: 1;
      text-align: center;
    }

    .info-image img {
      width: 90%;
      max-width: 350px;
      border-radius: 20px;
      padding: 3px;
      border: 1.5px solid transparent;
      background: linear-gradient(#fff, #fff) padding-box, linear-gradient(135deg, #667eea, #764ba2) border-box;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      transition: transform 0.4s ease, box-shadow 0.4s ease;
    }

    .info-image img:hover {
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(118, 75, 162, 0.5), 0 0 25px rgba(102, 126, 234, 0.5);
    }
    
      footer { background: linear-gradient(to right, #A66E4A, #5A3620); color: #fff; text-align: center; padding: 20px 10px; font-size: 14px; }


    #home {
      background: linear-gradient(to right, #dfe9f3, #ffffff);
    }

    #bs-english {
      background: linear-gradient(to right, #f9f9f9, #e0c3fc);
    }

    #bs-hpe {
      background: linear-gradient(to right, #e0f7fa, #ffffff);
    }

    #bs-it {
      background: linear-gradient(to right, #c2e9fb, #a1c4fd);
    }

    #intermediate {
      background: linear-gradient(to right, #ffe0e0, #ffffff);
    }

    @media (max-width: 768px) {
      .info-block, .info-block.reverse {
        flex-direction: column;
      }
    }

    /* View Policy Button */
    .view-policy-btn {
      display: inline-block;
      margin-top: 20px;
      padding: 12px 25px;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: #fff;
      border-radius: 30px;
      text-decoration: none;
      font-weight: bold;
      transition: all 0.3s ease;
      box-shadow: 0 0 10px rgba(118, 75, 162, 0.6), 0 0 20px rgba(102, 126, 234, 0.4);
      animation: pulseGlow 2s infinite;
    }

    .view-policy-btn:hover {
      background: linear-gradient(135deg, #5a6fd1, #6a4192);
      transform: scale(1.08);
      box-shadow: 0 0 15px rgba(118, 75, 162, 0.9), 0 0 25px rgba(102, 126, 234, 0.7);
    }

    @keyframes pulseGlow {
      0% {
        box-shadow: 0 0 10px rgba(118, 75, 162, 0.6), 0 0 20px rgba(102, 126, 234, 0.4);
      }
      50% {
        box-shadow: 0 0 20px rgba(118, 75, 162, 0.9), 0 0 35px rgba(102, 126, 234, 0.7);
      }
      100% {
        box-shadow: 0 0 10px rgba(118, 75, 162, 0.6), 0 0 20px rgba(102, 126, 234, 0.4);
      }
    }
  </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="auth-navbar">
    <div class="container">
        <a href="index.php" class="auth-logo">
            <img src="/uploads/assests/book.png" alt="Library Logo" class="logo-image">
            <span class="navbar-title">BookBridge</span>
        </a>
        <div class="auth-nav-links">
            <a href="gallery.php" class="auth-nav-link">
                <i class="fas fa-images"></i>
                <span>Gallery</span>
            </a>
            <a href="about.php" class="auth-nav-link">
                <i class="fas fa-info-circle"></i>
                <span>About</span>
            </a>
            <a href="index.php" class="auth-nav-link">
                <i class="fas fa-user-plus"></i>
                <span>Login</span>
            </a>
        </div>
    </div>
</nav>

<!-- Home Section -->
<section id="home">
  <h2 class="section-book_name">Welcome to Our Digital Campus</h2>
  <div class="info-block center show">
    <div class="info-text">
      <h2>Our Digital Evolution</h2>
      <p>Transforming education with a powerful digital library and smart access to academic resources. Experience a modern, flexible, and connected way of learning.</p>
      <a href="policy.php" class="view-policy-btn">View Library Policy</a>
    </div>
    <div class="info-image">
      <img src="uploads/assests/lib1.jpg" alt="Home Image">
    </div>
  </div>
</section>

<!-- BS English Section -->
<section id="bs-english">
  <h2 class="section-book_name">BS English Department</h2>
  <div class="info-block reverse show">
    <div class="info-image">
      <img src="uploads/assests/about1.jpg" alt="BS English Image">
    </div>
    <div class="info-text">
      <h2>Literature Meets Technology</h2>
      <p>We bridge the beauty of classic English literature with the convenience of digital access. Explore poetry, novels, and critical essays through our easy-to-use digital portal.</p>
    </div>
  </div>
</section>

<!-- BS HPE Section -->
<section id="bs-hpe">
  <h2 class="section-book_name">BS Health & Physical Education</h2>
  <div class="info-block show">
    <div class="info-text">
      <h2>Empowering Health through Knowledge</h2>
      <p>BS HPE promotes physical education and healthy lifestyles by making research, fitness guides, and educational material accessible digitally to all students.</p>
    </div>
    <div class="info-image">
      <img src="uploads/assests/about2.jpg" alt="BS HPE Image">
    </div>
  </div>
</section>

<!-- BS IT Section -->
<section id="bs-it">
  <h2 class="section-book_name">BS Information Technology</h2>
  <div class="info-block reverse show">
    <div class="info-image">
      <img src="uploads/assests/about3.jpg" alt="BS IT Image">
    </div>
    <div class="info-text">
      <h2>Driving the Future with Technology</h2>
      <p>Welcome to the powerhouse of innovation! Our BS IT department pioneers the digital revolution with cutting-edge resources, smart learning systems, and a vision to empower tech leaders of tomorrow. Explore AI, cybersecurity, software engineering, and more — all at your fingertips!</p>
    </div>
  </div>
</section>

<!-- Intermediate Section -->
<section id="intermediate">
  <h2 class="section-book_name">Intermediate Students</h2>
  <div class="info-block show">
    <div class="info-text">
      <h2>Building Strong Foundations</h2>
      <p>For our intermediate students, we offer a carefully designed digital environment that nurtures learning, enhances study practices, and prepares you for a successful academic journey.</p>
    </div>
    <div class="info-image">
      <img src="uploads/assests/about4.jpg" alt="Intermediate Image">
    </div>
  </div>
</section>

<!-- Footer -->
<hr />
<footer>
  © <?= date("Y") ?> F.G. Degree College For Women, Kharian Cantt. All Rights Reserved.
  Digital Library | Made with ❤ by <b>Aqsa Hakeem, Maleeha and Iqra Noureen (BSIT)</b>
</footer>

<!-- Scroll Animation Script -->
<script>
  const blocks = document.querySelectorAll('.info-block');

  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('show');
      }
    });
  }, { threshold: 0.3 });

  blocks.forEach(block => {
    observer.observe(block);
  });
</script>

</body>
</html>