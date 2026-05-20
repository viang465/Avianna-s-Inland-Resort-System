<?php 
include "conn.php"; 

// Define Gallery Data
$gallery_items = [
    [
        "title" => "Luxury Poolside",
        "desc" => "Relax by our crystal clear infinity pool.",
        "img" => "https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=800&q=80"
    ],
    [
        "title" => "Botanical Garden",
        "desc" => "Serenity found in every green corner.",
        "img" => "https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?auto=format&fit=crop&w=800&q=80"
    ],
    [
        "title" => "Sunset Bar",
        "desc" => "Premium refreshments with a view.",
        "img" => "https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=800&q=80"
    ],
    [
        "title" => "Master Suite",
        "desc" => "Comfort meets elegance in our rooms.",
        "img" => "https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80"
    ],
    [
        "title" => "Al Fresco Dining",
        "desc" => "Gourmet meals under the open sky.",
        "img" => "https://images.unsplash.com/photo-1533777857889-4be7c70b33f7?auto=format&fit=crop&w=800&q=80"
    ],
    [
        "title" => "The Spa",
        "desc" => "Renew your mind and body.",
        "img" => "https://images.unsplash.com/photo-1544161515-4ab6ce6db874?auto=format&fit=crop&w=800&q=80"
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery | Avianna's Inland Resort</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
    :root {
        --tropical-green: #1a4731;
        --accent-gold: #ffc107;
        --deep-palm: #0e2a1d;
        --overlay-bg: rgba(14, 42, 29, 0.8); /* Slightly darker for better text contrast */
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f8f9fa;
        color: #333;
    }

    h1, .navbar-brand, h4 {
        font-family: 'Playfair Display', serif;
    }

    /* Navbar */
    .navbar {
        background-color: var(--tropical-green) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Header Section */
    .header-gallery {
        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), 
                    url('https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=1600&q=80');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: white;
        padding: 120px 0;
    }

    /* Gallery Card Styles */
    .gallery-card {
        border-radius: 20px; /* More rounded for a modern look */
        overflow: hidden;
        position: relative;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        background: #fff;
        height: 100%;
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .gallery-img-container {
        position: relative;
        aspect-ratio: 4/3;
        overflow: hidden;
    }

    .gallery-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.8s ease;
    }

    /* Hover State: Image Scale */
    .gallery-card:hover img {
        transform: scale(1.15);
    }

    /* Overlay Effect */
    .gallery-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--overlay-bg);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        opacity: 0;
        transition: all 0.4s ease;
        padding: 30px;
        backdrop-filter: blur(3px); /* Subtle blur for luxury feel */
    }

    .gallery-card:hover .gallery-overlay {
        opacity: 1;
    }

    /* Overlay Text Animation */
    .overlay-text h4 {
        color: var(--accent-gold);
        margin-bottom: 10px;
        transform: translateY(20px);
        transition: transform 0.4s ease 0.1s;
    }

    .overlay-text p {
        font-size: 0.9rem;
        font-weight: 300;
        transform: translateY(20px);
        transition: transform 0.4s ease 0.2s;
    }

    .gallery-card:hover .overlay-text h4,
    .gallery-card:hover .overlay-text p {
        transform: translateY(0);
    }

    /* Footer */
    footer {
        background-color: var(--deep-palm) !important;
        border-top: 5px solid var(--accent-gold);
    }

    /* Bootstrap button consistency */
    .btn-warning {
        background-color: var(--accent-gold);
        border: none;
        color: #000;
    }

    .btn-warning:hover {
        background-color: #e5af06;
    }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <img src="img/avianna.png" 
             alt="Avianna Logo" 
             style="width: 50px; height: auto; margin-bottom: 5px;" 
             class="animate__animated animate__fadeIn shadow-sm rounded-circle">
        <a class="navbar-brand fw-bold" href="index.php">Avianna's Inland Resort</a>
        <div class="ms-auto">
            <a href="index.php" class="btn btn-sm btn-outline-light rounded-pill px-3 me-2">Home</a>
            <a href="aboutus.php" class="btn btn-sm btn-outline-light rounded-pill px-3 me-2">About</a>
            <a href="gallery.php" class="btn btn-sm btn-light rounded-pill px-3 me-2 text-dark">Gallery</a>
            <a href="reviews.php" class="btn btn-sm btn-outline-light rounded-pill px-3 me-2">Reviews</a>
            <a href="book.php" class="btn btn-sm btn-warning rounded-pill px-3 fw-bold">Book Now</a>
        </div>
    </div>
</nav>

<header class="header-gallery text-center py-5">
    <div class="container">
        <h1 class="display-4 fw-bold animate__animated animate__fadeInDown">Resort Gallery</h1>
        <p class="lead animate__animated animate__fadeInUp">A glimpse into your next tropical escape.</p>
    </div>
</header>

<main class="container my-5">
    <div class="row g-4">
        <?php foreach ($gallery_items as $index => $item): ?>
            <div class="col-md-6 col-lg-4 animate__animated animate__fadeInUp" style="animation-delay: <?= $index * 0.1 ?>s">
                <div class="gallery-card">
                    <div class="gallery-img-container">
                        <img src="<?= $item['img'] ?>" alt="<?= $item['title'] ?>" class="img-fluid">
                        <div class="gallery-overlay">
                            <div class="overlay-text">
                                <h4 class="fw-bold mb-1"><?= $item['title'] ?></h4>
                                <p class="small mb-0"><?= $item['desc'] ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<footer class="bg-dark text-white text-center py-4">
    <div class="container">
        <p class="mb-0">&copy; 2026 Avianna's Inland Resort. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>