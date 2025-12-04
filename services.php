<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services - Hair Cutting Hub</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="" id="theme-css">
    <script>
        const selectedTheme = localStorage.getItem('selectedTheme');
        if (selectedTheme && selectedTheme !== 'default') {
            document.getElementById('theme-css').href = `themes/${selectedTheme}-theme.css`;
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <i class="fas fa-cut"></i>
                <span>Hair Cutting Hub</span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="services.php" class="nav-link active">Services</a></li>
                <li><a href="barbers.php" class="nav-link">Barbers</a></li>
                <li><a href="booking.php" class="nav-link">Booking</a></li>
                <li><a href="gallery.php" class="nav-link">Gallery</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
                <li><a href="theme-selector.php" class="nav-link theme-btn"><i class="fas fa-palette"></i> Themes</a></li>
                <li><a href="login.php" class="nav-link login-link">Login</a></li>
            </ul>
        </div>
    </nav>

    <main style="padding-top: 100px;">
        <section class="services">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Our Premium Services</h2>
                    <p class="section-subtitle">Professional grooming services tailored to your style</p>
                </div>
                <div class="services-grid">
                    <div class="service-card glass-card">
                        <div class="service-icon">
                            <i class="fas fa-cut"></i>
                        </div>
                        <div class="service-front">
                            <h3>Classic Haircut</h3>
                            <div class="price">$25</div>
                        </div>
                        <div class="service-back">
                            <p>Duration: 30 mins</p>
                            <p>Traditional scissor cut with styling</p>
                            <button class="book-service-btn" onclick="window.location.href='booking.php'">Book Now</button>
                        </div>
                    </div>
                    <div class="service-card glass-card">
                        <div class="service-icon">
                            <i class="fas fa-magic"></i>
                        </div>
                        <div class="service-front">
                            <h3>Fade Cut</h3>
                            <div class="price">$30</div>
                        </div>
                        <div class="service-back">
                            <p>Duration: 45 mins</p>
                            <p>Modern fade with precision blending</p>
                            <button class="book-service-btn" onclick="window.location.href='booking.php'">Book Now</button>
                        </div>
                    </div>
                    <div class="service-card glass-card">
                        <div class="service-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="service-front">
                            <h3>Beard Trim</h3>
                            <div class="price">$20</div>
                        </div>
                        <div class="service-back">
                            <p>Duration: 25 mins</p>
                            <p>Professional beard shaping</p>
                            <button class="book-service-btn" onclick="window.location.href='booking.php'">Book Now</button>
                        </div>
                    </div>
                    <div class="service-card glass-card">
                        <div class="service-icon">
                            <i class="fas fa-spa"></i>
                        </div>
                        <div class="service-front">
                            <h3>Hot Towel Shave</h3>
                            <div class="price">$35</div>
                        </div>
                        <div class="service-back">
                            <p>Duration: 40 mins</p>
                            <p>Traditional straight razor shave</p>
                            <button class="book-service-btn" onclick="window.location.href='booking.php'">Book Now</button>
                        </div>
                    </div>
                    <div class="service-card glass-card">
                        <div class="service-icon">
                            <i class="fas fa-crown"></i>
                        </div>
                        <div class="service-front">
                            <h3>Premium Package</h3>
                            <div class="price">$60</div>
                        </div>
                        <div class="service-back">
                            <p>Duration: 90 mins</p>
                            <p>Complete grooming experience</p>
                            <button class="book-service-btn" onclick="window.location.href='booking.php'">Book Now</button>
                        </div>
                    </div>
                    <div class="service-card glass-card">
                        <div class="service-icon">
                            <i class="fas fa-shower"></i>
                        </div>
                        <div class="service-front">
                            <h3>Hair Wash & Style</h3>
                            <div class="price">$15</div>
                        </div>
                        <div class="service-back">
                            <p>Duration: 20 mins</p>
                            <p>Wash, condition, and styling</p>
                            <button class="book-service-btn" onclick="window.location.href='booking.php'">Book Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
