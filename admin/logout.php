<?php
session_start();
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out - Avianna's Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <style>
        :root { 
            --primary-green: #1e4d40; 
            --accent-teal: #2c7a7b; 
            --bg-light: #f0f4f8;
        }

        body { 
            background-color: var(--bg-light); 
            font-family: 'Inter', sans-serif;
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: 0;
        }

        /* --- STRENGTHENED FIX TO REMOVE THE ARROW --- */
        #scrollUp, .scroll-to-top, .back-to-top, [id*="scroll"], .tp-top-arrow, button[title*="top"] {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        .logout-card { 
            background: white; 
            padding: 50px 40px; 
            border-radius: 24px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.08); 
            text-align: center; 
            max-width: 420px; 
            width: 90%; 
            border: 1px solid rgba(0,0,0,0.05);
            animation: fadeInScale 0.5s ease-out;
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .icon-circle { 
            width: 80px; 
            height: 80px; 
            background: #f0fdf4; 
            color: #16a34a; 
            font-size: 32px; 
            line-height: 80px; 
            border-radius: 50%; 
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        h1 {
            font-weight: 700;
            color: var(--primary-green);
            font-size: 1.75rem;
            margin-bottom: 10px;
        }

        p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn-return { 
            background-color: var(--primary-green); 
            color: white; 
            padding: 12px 35px; 
            border-radius: 12px; 
            text-decoration: none; 
            display: inline-block; 
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-return:hover { 
            background-color: var(--accent-teal);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 122, 123, 0.3);
        }

        /* Redirect Progress Bar */
        .progress-container {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
            margin-top: 40px;
        }

        .progress-bar { 
            height: 100%; 
            background: linear-gradient(90deg, var(--accent-teal), #4fd1c5); 
            width: 0%; 
            border-radius: 10px;
            animation: progress 5s linear forwards; 
        }

        @keyframes progress { 
            from { width: 0%; } 
            to { width: 100%; } 
        }

        .redirect-text {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<div class="logout-card">
    <div class="icon-circle">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
            <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 10.293 1.854 7.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0l7-7z"/>
        </svg>
    </div>
    
    <h1>Securely Logged Out</h1>
    <p>Thank you for keeping Avianna's Resort running smoothly. Your session has ended.</p>
    
    <a href="login.php" class="btn-return">Login Again</a>
    
    <div class="progress-container">
        <div class="progress-bar"></div>
    </div>
    <div class="redirect-text">Auto-redirecting in 5 seconds</div>
</div>

<script>
    // Automatically redirect to login page after 5 seconds
    setTimeout(() => { 
        window.location.href = "login.php"; 
    }, 5000);
</script>

</body>
</html>