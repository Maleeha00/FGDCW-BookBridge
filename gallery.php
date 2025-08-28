<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Gallery | BookBridge</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display&family=Open+Sans&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="icon" type="image/png" href="uploads/assests/book.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
       
       /*Gallery */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f7fa;
        }

        .gallery-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            text-align: center;
            margin-top: 100px;
            color: #2c3e50;
        }

        .gallery-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }


        .gallery-item {
            overflow: hidden;
            border-radius: 15px;
            box-shadow: 0 8px 16px #0000001a;
            cursor: pointer;
            transition: transform 0.3s ease;
            position: relative;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            display: block;
            transition: transform 0.5s ease;
        }  

        .gallery-item:hover img {
            transform: scale(1.08);
        }

        .lightbox {
            display: none;
            position: fixed;
            z-index: 999;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: #000000cc;
            justify-content: center;
            align-items: center;
        }

        .lightbox img {
            max-width: 90%;
            max-height: 80vh;
            border-radius: 10px;
            box-shadow: 0 0 15px #ffffff4d;
        }

        .lightbox:target {
            display: flex;
        }

        .close-btn {
            position: absolute;
            top: 30px;
            right: 50px;
            font-size: 40px;
            color: white;
            z-index:1000px;
            .close-btn {
}
        }

        footer {
            background: linear-gradient(to right, #A66E4A, #5A3620);
            color: #fff;
            text-align: center;
            padding: 20px 10px;
            font-size: 14px;
        }

        hr {
            border: 0.5px solid #ccc;
            margin: 0;
        }

        @media (max-width: 600px) {
            .gallery-title {
                font-size: 2rem; 
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

    <!-- Title -->
    <h1 class="gallery-title">Glimpses of Our Library</h1>

    <!-- Gallery -->
    <div class="gallery-container">
        <a class="gallery-item" href="#img1"><img src="uploads/assests/almira.jpg" alt=""></a>
        <a class="gallery-item" href="#img2"><img src="uploads/assests/libQuote.jpg" alt=""></a>
        <a class="gallery-item" href="#img3"><img src="uploads/assests/libTable.jpg" alt=""></a>
        <a class="gallery-item" href="#img4"><img src="uploads/assests/noticeboard.jpg" alt=""></a>
        <a class="gallery-item" href="#img5"><img src="uploads/assests/bookshelf.jpg" alt=""></a>
        <a class="gallery-item" href="#img6"><img src="uploads/assests/lib1.jpg" alt=""></a>
        <a class="gallery-item" href="#img7"><img src="uploads/assests/login.jpeg" alt=""></a>
        <a class="gallery-item" href="#img8"><img src="uploads/assests/lib2.jpg" alt=""></a>
        <a class="gallery-item" href="#img9"><img src="uploads/assests/lib3.jpg" alt=""></a>
    </div>

    <!-- Lightboxes -->
    <div id="img1" class="lightbox"><a href="#" class="close-btn">&times;</a><img src="uploads/assests/almira.jpg"
            alt=""></div>
    <div id="img2" class="lightbox"><a href="#" class="close-btn">&times;</a><img src="uploads/assests/libQuote.jpg"
            alt=""></div>
    <div id="img3" class="lightbox"><a href="#" class="close-btn">&times;</a><img src="uploads/assests/libTable.jpg"
            alt=""></div>
    <div id="img4" class="lightbox"><a href="#" class="close-btn">&times;</a><img src="uploads/assests/noticeboard.jpg"
            alt=""></div>
    <div id="img5" class="lightbox"><a href="#" class="close-btn">&times;</a><img src="uploads/assests/bookshelf.jpg"
            alt=""></div>
    <div id="img6" class="lightbox"><a href="#" class="close-btn">&times;</a><img src="uploads/assests/lib1.jpg" alt="">
    </div>
    <div id="img7" class="lightbox"><a href="#" class="close-btn">&times;</a><img src="uploads/assests/login.jpeg"
            alt=""></div>
    <div id="img8" class="lightbox"><a href="#" class="close-btn">&times;</a><img src="uploads/assests/lib2.jpg" alt="">
    </div>
    <div id="img9" class="lightbox"><a href="#" class="close-btn">&times;</a><img src="uploads/assests/lib3.jpg" alt="">
    </div>

    <!-- Footer -->
    <hr />
    <footer>
        © <?= date("Y") ?> F.G. Degree College For Women, Kharian Cantt. All Rights Reserved.
        Digital Library | Made with ❤ by <b>Aqsa Hakeem, Maleeha and Iqra Noureen (BSIT)</b>
    </footer>

</body>

</html>
