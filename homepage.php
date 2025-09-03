<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>FGDCW BookBridge</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/auth.css">
  <link rel="icon" type="image/png" href="uploads/assests/book.png">

  <style>
    .carousel-item img { width: 100vw; height: 90vh; }
    .custom-heading { font-size: 2.5rem; text-align: center; margin: 20px auto; display: block; }
    .d-flex .m-3 { text-align: center; position: relative; transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .d-flex .m-3 img { width: 150px; height: 150px; border-radius: 50%; transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .d-flex .m-3 p { font-size: 18px; margin-top: 10px; text-align: center; }
    .d-flex .m-3:hover img { transform: scale(1.1); box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.3); }
    body { margin: 0; background: #fff; }
    footer { background: linear-gradient(to right, #A66E4A, #5A3620); color: #fff; text-align: center; padding: 20px 10px; font-size: 14px; }
    hr { border: 0.5px solid #ccc; margin: 0; }

    .about-page { background: #f2f2f2; padding: 60px 0; margin-top: 60px; }
    .about-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
    .about-section { background: #fff; border-radius: 10px; padding: 40px; box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); }
    .section-header { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
    .section-icon { width: 60px; height: 60px; background: linear-gradient(135deg, #2d8b6cab 0%, #00326ac5 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5em; }
    .section-book_name { font-size: 1.8em; color: #00416A; margin: 0; font-weight: 600; }
    .section-content p { color: #333; line-height: 1.7; font-size: 1.05em; }
    .contact-info { margin-top: 30px; display: grid; grid-template-columns: 2fr 3fr; gap: 30px; }
    .left-contact-logo { text-align: left; }
    .left-contact-logo img { height: 120px; object-fit: contain; }
    .left-contact-logo p { color: #888; margin-top: 10px; }
    .contact-items { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; }
    .contact-item { display: flex; align-items: flex-start; gap: 15px; }
    .contact-icon { background: #006a5891; color: #fff; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
    .contact-details h4 { margin: 0 0 5px 0; font-weight: 600; color: #222; }
    .contact-details p { margin: 0; font-size: 0.9rem; color: #555; }
    @media (max-width: 768px) {
      .contact-info { grid-template-columns: 1fr; }
      .left-contact-logo { text-align: center; }
    }
  </style>
</head>

<body>

<!-- Replaced Navbar -->
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

<div id="imageCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000">
  <div class="carousel-indicators">
    <?php for ($i = 0; $i < 6; $i++): ?>
      <button type="button" data-bs-target="#imageCarousel" data-bs-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active"' : '' ?>></button>
    <?php endfor; ?>
  </div>
  <div class="carousel-inner">
    <?php
    $images = ['uploads/assests/lib2.jpg', 'uploads/assests/almiras.jpg', 'uploads/assests/noticeBoard.jpg', 'uploads/assests/lib1.jpg', 'uploads/assests/bookshelf.jpg', 'uploads/assests/login.jpeg'];
    foreach ($images as $index => $img) {
      $active = $index === 0 ? 'active' : '';
      echo "<div class='carousel-item $active'><img src='$img' class='d-block w-100' alt='Slide ".($index + 1)."' /></div>";
    }
    ?>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>

<section class="my-5">
  <h2 class="custom-heading">Available Resources</h2>
  <div class="d-flex justify-content-center flex-wrap">
    <?php
    $subjects = [
      ['uploads/assests/eng.jpg', 'ENGLISH'],
      ['uploads/assests/magazines.jpg', 'MONTHLY MAGAZINES'],
      ['uploads/assests/eco.jpg', 'ECONOMICS'],
      ['uploads/assests/hpe.jpg', 'HEALTH AND PHYSICAL'],
      ['uploads/assests/novels.webp', 'ENGLISH NOVELS'],
      ['uploads/assests/maths.jpg', 'MATHS']
    ];
    foreach ($subjects as [$img, $label]) {
      echo "<div class='m-3'><img src='$img' class='rounded-circle' onclick='showLoginPrompt()'><p>$label</p></div>";
    }
    ?>
  </div>
</section>

<section class="my-5">
  <div class="d-flex justify-content-center flex-wrap">
    <?php
    $subjects2 = [
      ['uploads/assests/urdu.jpg', 'URDU'],
      ['uploads/assests/chemistry.jpg', 'CHEMISTRY'],
      ['uploads/assests/comp.jpg', 'COMPUTER'],
      ['uploads/assests/hpe.png', 'HPE'],
      ['uploads/assests/isl.jpg', 'ISLAMIAT'],
      ['uploads/assests/IT.jpg', 'IT']
    ];
    foreach ($subjects2 as [$img, $label]) {
      echo "<div class='m-3'><img src='$img' class='rounded-circle' onclick='showLoginPrompt()'><p>$label</p></div>";
    }
    ?>
  </div>
</section>

<div class="about-page">
  <div class="about-container">
    <div class="about-section">
      <div class="section-header">
        <div class="section-icon">
          <i class="fas fa-envelope"></i>
        </div>
        <h2 class="section-book_name">Get in Touch</h2>
      </div>
      <div class="section-content">
        <p>We'd love to hear from you! Whether you have questions, suggestions, or need support, our team is here to help.</p>
        <div class="contact-info">
          <div class="left-contact-logo">
            <img src="uploads/assests/logo.png" alt="College Logo">
            <p>Proudly affiliated with Federal Board and NUP</p>
          </div>
          <div class="contact-items">
            <div class="contact-item">
              <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
              <div class="contact-details">
                <h4>Address</h4>
                <p>Kharian Cantt, Punjab, Pakistan</p>
              </div>
            </div>
            <div class="contact-item">
              <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
              <div class="contact-details">
                <h4>Phone</h4>
                <p>(053) 9240102</p>
              </div>
            </div>
            <div class="contact-item">
              <div class="contact-icon"><i class="fas fa-envelope"></i></div>
              <div class="contact-details">
                <h4>Email</h4>
                <p>fgkharian@gmail.com</p>
              </div>
            </div>
            <div class="contact-item">
              <div class="contact-icon"><i class="fas fa-clock"></i></div>
              <div class="contact-details">
                <h4>Hours</h4>
                <p>Mon-Fri: 8AM–10PM<br>Sat-Sun: 10AM–8PM</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<hr />
<footer>
  © <?= date("Y") ?> F.G. Degree College For Women, Kharian Cantt. All Rights Reserved.
  Digital Library | Made with ❤ by <b>Aqsa Hakeem, Maleeha and Iqra Noureen (BSIT)</b>
</footer>

<script>
  function showLoginPrompt() {
    if (confirm("Kindly login to access the items.")) {
      window.location.href = '/index.php';
    }
  }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
