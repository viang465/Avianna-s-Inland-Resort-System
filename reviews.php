<?php 
include "conn.php"; 

// 1. HANDLE THE FORM SUBMISSION
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $rating = (int)$_POST['rating'];
    $text = htmlspecialchars(trim($_POST['review_text'] ?? ''), ENT_QUOTES, 'UTF-8');
    $photo_name = "";

    // Validate rating range
    if ($rating < 1 || $rating > 5) $rating = 3;

    if (isset($_FILES['review_photo']) && $_FILES['review_photo']['error'] === 0) {
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_ext   = strtolower(pathinfo($_FILES['review_photo']['name'], PATHINFO_EXTENSION));
        $allowed    = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($file_ext, $allowed)) {
            $photo_name  = time() . "_" . uniqid() . "." . $file_ext;
            $target_path = $upload_dir . $photo_name;
            if (!move_uploaded_file($_FILES['review_photo']['tmp_name'], $target_path)) {
                $photo_name = ""; // Upload failed silently, proceed without photo
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO reviews (name, rating, review_text, photo_path, submission_date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("siss", $name, $rating, $text, $photo_name);
    $stmt->execute();
    
    header("Location: reviews.php?success=1");
    exit();
}

// 2. FETCH REVIEWS
$reviews = [];
$result = $conn->query("SELECT * FROM reviews ORDER BY submission_date DESC");
if($result) {
    while($row = $result->fetch_assoc()) $reviews[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Reviews - Avianna's Inland Resort</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
<style>
    :root { 
        --teal: #2c7a7b; 
        --dark: #1e4d40; 
        --orange: #ed8936; 
        --sand: #f4f7f6;
        --white: #ffffff;
    }

    body { 
        background-color: var(--sand); 
        font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
        color: #333;
        overflow-x: hidden;
    }

    /* Navbar Enhancements */
    .navbar { 
        background-color: var(--dark) !important; 
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        padding: 0.8rem 0;
    }

    /* Header with better mobile responsiveness */
    .header-reviews {
            position: relative;
            min-height: 50vh;
            display: flex;
            flex-direction: column; 
            align-items: center;
            justify-content: center;
            /* Integrated background with logo and tropical green theme */
            background: url('img/bg.jpg') center/contain no-repeat;
            background-size: cover;
            background-color: var(--tropical-green);
            color: white;
        }

    /* Sidebar Sticky Logic Fix */
    .sticky-card {
        position: sticky;
        top: 100px;
        z-index: 10;
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        background: var(--white);
    }

    /* Review Cards */
    .review-card { 
        border: none; 
        border-left: 6px solid var(--teal); 
        border-radius: 15px; 
        background: var(--white); 
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }

    .review-card:hover { 
        transform: translateY(-5px); 
        box-shadow: 0 12px 25px rgba(0,0,0,0.1); 
    }

    /* Form Elements */
    .form-control, .form-select {
        border: 1px solid #e2e8f0;
        padding: 10px 15px;
    }

    .form-control:focus {
        border-color: var(--teal);
        box-shadow: 0 0 0 0.2rem rgba(44, 122, 123, 0.15);
    }

    .btn-post { 
        background: var(--teal); 
        color: white; 
        border: none; 
        font-weight: 600; 
        border-radius: 50px; 
        padding: 12px;
        transition: all 0.3s ease;
    }

    .btn-post:hover { 
        background: var(--dark); 
        color: white; 
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(44, 122, 123, 0.3);
    }

    /* Images */
    .review-img { 
        width: 100%; 
        max-height: 500px; 
        object-fit: cover; 
        border-radius: 12px; 
        margin-top: 15px; 
        transition: opacity 0.3s;
    }

    .review-img:hover {
        opacity: 0.9;
    }

    .rating-stars { 
        color: var(--orange); 
        font-size: 1.1rem;
        letter-spacing: 2px;
    }

    /* Animations */
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .header-reviews h1 { animation: fadeInDown 0.8s ease-out; }
    .header-reviews p { animation: fadeInDown 1s ease-out; }

    /* Custom Scrollbar */
    ::-webkit-scrollbar { width: 8px; }
    ::-webkit-scrollbar-track { background: var(--sand); }
    ::-webkit-scrollbar-thumb { background: var(--teal); border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--dark); }

    /* Missing utility class used on reviewer names */
    .text-teal { color: var(--teal); }

    /* Responsive Adjustments */
    @media (max-width: 991.98px) {
        .sticky-card {
            position: relative;
            top: 0;
            margin-bottom: 30px;
        }
        .header-reviews {
            padding: 80px 0 100px 0;
        }
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
        <div class="ms-auto d-none d-lg-block">
            <a href="index.php" class="btn btn-sm btn-outline-light rounded-pill px-3 me-2">Home</a>
            <a href="aboutus.php" class="btn btn-sm btn-outline-light rounded-pill px-3 me-2">About Us</a>
            <a href="gallery.php" class="btn btn-sm btn-outline-light rounded-pill px-3 me-2">Gallery</a>
            <a href="reviews.php" class="btn btn-sm btn-light rounded-pill px-3 me-2 text-dark">Reviews</a>
            <a href="book.php" class="btn btn-sm btn-warning rounded-pill px-3 fw-bold">Book Now</a>
            
        </div>
    </div>
</nav>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success text-center mb-0 rounded-0 py-2">
    ✅ Your review has been submitted successfully. Thank you!
</div>
<?php endif; ?>

<header class="header-reviews">
    <div class="container">
        <h1 class="display-3 fw-bold">Guest Experiences</h1>
        <p class="lead fs-4">Shared memories from our tropical sanctuary</p>
    </div>
</header>

<div class="container pb-5">
    <div class="row g-5">
        <div class="col-lg-4">
            <div class="card sticky-card p-4" data-aos="fade-right">
                <h4 class="fw-bold mb-3">Share Your Story</h4>
                <form action="reviews.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control rounded-3" placeholder="John Doe" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Your Rating</label>
                        <select name="rating" class="form-select rounded-3">
                            <option value="5">★★★★★ Excellent</option>
                            <option value="4">★★★★☆ Good</option>
                            <option value="3">★★★☆☆ Average</option>
                            <option value="2">★★☆☆☆ Poor</option>
                            <option value="1">★☆☆☆☆ Terrible</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Upload a Memory</label>
                        <input type="file" name="review_photo" class="form-control rounded-3" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Review Details</label>
                        <textarea name="review_text" class="form-control rounded-3" rows="4" placeholder="How can we improve our resort?" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-post w-100 shadow-sm">Post Review</button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <?php if (empty($reviews)): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm" data-aos="fade-up">
                    <p class="text-muted mb-0">No reviews yet. Be the first to share your experience!</p>
                </div>
            <?php else: ?>
                <?php foreach($reviews as $index => $r): ?>
                    <div class="card review-card p-4 mb-4 shadow-sm" 
                         data-aos="fade-up" 
                         data-aos-delay="<?= ($index % 3) * 100 ?>"> <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="fw-bold mb-0 text-teal"><?= htmlspecialchars($r['name']) ?></h5>
                                <div class="rating-stars mb-2">
                                    <?= str_repeat('★', $r['rating']) ?><?= str_repeat('☆', 5 - $r['rating']) ?>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= date('M d, Y', strtotime($r['submission_date'])) ?>
                            </small>
                        </div>

                        <p class="text-dark fs-5 mt-2 mb-0" style="font-style: italic;">
                            "<?= htmlspecialchars($r['review_text']) ?>"
                        </p>

                        <?php if (!empty($r['photo_path'])): ?>
                            <div class="overflow-hidden rounded-3 mt-3">
                                <img src="uploads/<?= $r['photo_path'] ?>" alt="Guest Memory" class="review-img">
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer class="text-center py-5 bg-dark text-secondary mt-5">
    <div class="container">
        <p class="mb-0">&copy; 2026 Avianna's Inland Resort. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Initialize Animations
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true, // Animation happens only once while scrolling down
        mirror: false
    });
</script>

</body>
</html>