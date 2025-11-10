<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Star Recruiting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background: #1A004D;
            color: white;
            min-height: 100vh;
        }

        .landing-navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 2.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .landing-navbar-brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .landing-navbar-brand img {
            height: 28px;
            width: auto;
        }

        .landing-navbar-brand h1 {
            font-size: 1.75rem;
            font-weight: 900;
            color: #FF3B6B;
            margin: 0;
        }

        .landing-navbar-links {
            display: flex;
            gap: 1.5rem;
            font-size: 0.875rem;
        }

        .landing-navbar-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .landing-navbar-links a:hover {
            color: #FF3B6B;
        }

        .hero-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 5rem 2.5rem;
            gap: 2.5rem;
        }

        @media (min-width: 768px) {
            .hero-section {
                flex-direction: row;
                padding: 5rem 5rem;
            }
        }

        .hero-content {
            flex: 1;
            animation: fadeInUp 0.6s ease-out;
        }

        .hero-content h2 {
            font-size: 3rem;
            font-weight: 900;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .hero-content h2 {
                font-size: 3.5rem;
            }
        }

        .hero-content .highlight {
            color: #FF3B6B;
        }

        .hero-content p {
            font-size: 1.125rem;
            color: #d1d5db;
            margin-bottom: 2rem;
            max-width: 36rem;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn-hero-primary {
            background: #FF3B6B;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 1.125rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-hero-primary:hover {
            background: #FF5684;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 59, 107, 0.3);
            color: white;
        }

        .btn-hero-outline {
            background: transparent;
            color: #FF3B6B;
            border: 2px solid #FF3B6B;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-size: 1.125rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .btn-hero-outline:hover {
            background: #FF3B6B;
            color: white;
            transform: translateY(-2px);
        }

        .hero-image {
            flex: 1;
            display: flex;
            justify-content: center;
            animation: fadeInRight 0.8s ease-out;
        }

        .hero-image img {
            width: 80%;
            max-width: 28rem;
            border-radius: 1rem;
            box-shadow: 0 25px 50px rgba(255, 59, 107, 0.2);
        }

        .features-section {
            padding: 5rem 2.5rem;
            background: #23005C;
        }

        @media (min-width: 768px) {
            .features-section {
                padding: 5rem 5rem;
            }
        }

        .features-section h3 {
            font-size: 2rem;
            font-weight: 900;
            text-align: center;
            margin-bottom: 3rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }

        @media (min-width: 768px) {
            .features-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .feature-card {
            background: #2A2A5A;
            border: none;
            border-radius: 1rem;
            padding: 2rem;
            color: white;
        }

        .feature-card h4 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #FF3B6B;
        }

        .feature-card p {
            color: #d1d5db;
            font-size: 0.875rem;
            line-height: 1.6;
            margin: 0;
        }

        .landing-footer {
            text-align: center;
            padding: 2.5rem;
            font-size: 0.875rem;
            color: #9ca3af;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 767px) {
            .landing-navbar {
                padding: 1rem 1.5rem;
                flex-direction: column;
                gap: 1rem;
            }

            .landing-navbar-links {
                gap: 1rem;
            }

            .hero-section {
                padding: 3rem 1.5rem;
            }

            .hero-content h2 {
                font-size: 2rem;
            }

            .features-section {
                padding: 3rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="landing-navbar">
        <a href="/" class="landing-navbar-brand">
            <img src="{{ asset('images/star-recruiting-logo.png') }}" alt="Star Recruiting Logo" onerror="this.style.display='none';">
            <h1>star recruiting</h1>
        </a>
        <div class="landing-navbar-links">
            <a href="#features">Features</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h2>
                Find your next <span class="highlight">star candidate</span>
            </h2>
            <p>
                Empower your hiring process with intelligent matching, streamlined candidate management, and real-time insights.
            </p>
            <div class="hero-buttons">
                <a href="/register" class="btn-hero-primary">Get Started</a>
                <a href="#features" class="btn-hero-outline">Learn More</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="{{ asset('images/illustration-hiring.png') }}" alt="Recruiting illustration" onerror="this.style.display='none';">
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features-section">
        <h3>Why Choose Star Recruiting?</h3>
        <div class="features-grid">
            <div class="feature-card">
                <h4>AI Candidate Matching</h4>
                <p>Leverage advanced AI to identify top candidates tailored to your role.</p>
            </div>
            <div class="feature-card">
                <h4>Seamless Collaboration</h4>
                <p>Recruiters and hiring managers work together effortlessly in one platform.</p>
            </div>
            <div class="feature-card">
                <h4>Analytics Dashboard</h4>
                <p>Get actionable insights on performance, pipeline, and hiring trends.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        © {{ date('Y') }} Star Recruiting. All rights reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

