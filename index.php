<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Healthcare Portal - Home</title>
<link rel="stylesheet" href="assets/css/style.css?v=6">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* =========================
   Global Styles
========================= */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: #f4f6fa;
    color: #333;
    line-height: 1.6;
}

/* =========================
   Header
========================= */
header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 50px;
    background: linear-gradient(135deg, #2f80ed, #56ccf2);
    color: #fff;
    flex-wrap: wrap;
}

header .logo-title {
    display: flex;
    align-items: center;
    gap: 15px;
}

header .logo {
    height: 70px;
    width: 70px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
}

header h1 {
    font-size: 1.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    margin: 0;
}

header h1 a {
    color: #fff;
    text-decoration: none;
}

header nav {
    display: flex;
    align-items: center;
    gap: 20px;
}

header nav a {
    color: #fff;
    text-decoration: none;
    font-weight: 600;
    font-size: 1rem;
    display: flex;
    align-items: center;
    transition: 0.3s;
}

header nav a i {
    margin-right: 6px;
}

header nav a:hover {
    color: #ffeb3b;
    transform: scale(1.05);
}

/* =========================
   Hero Section
========================= */
.hero {
    position: relative;
    width: 100%;
    height: 550px;
    background: url('assets/images/hero.png') center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    color: #fff;
}

.hero::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.45);
}

.hero h2, .hero p, .hero .btn {
    position: relative;
    z-index: 2;
}

.hero h2 {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow: 1px 1px 5px rgba(0,0,0,0.6);
}

.hero p {
    font-size: 1.3rem;
    margin-bottom: 30px;
    text-shadow: 1px 1px 5px rgba(0,0,0,0.6);
}

.hero .btn {
    padding: 15px 35px;
    background: linear-gradient(135deg, #ff6b6b, #f94d6a);
    color: #fff;
    font-weight: 700;
    border-radius: 50px;
    text-decoration: none;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
    transition: 0.4s;
}

.hero .btn:hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 12px 25px rgba(0,0,0,0.4);
}

/* =========================
   Features / Cards Section
========================= */
.main-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px 60px 20px;
}

.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 30px;
    margin-top: 50px;
}

.card {
    background: linear-gradient(135deg, #ffffff, #f0f4ff);
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    transition: transform 0.4s, box-shadow 0.4s, background 0.4s;
    text-align: center;
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    background: linear-gradient(135deg, #d9f0ff, #e0e9ff);
}

.card h3 {
    font-size: 1.4rem;
    margin-bottom: 15px;
    color: #2f80ed;
}

.card p {
    font-size: 1rem;
    color: #555;
}

.card a.btn {
    display: inline-block;
    margin-top: 15px;
    text-decoration: none;
    color: #fff;
    background: linear-gradient(135deg, #ff6b6b, #f94d6a);
    padding: 10px 20px;
    border-radius: 50px;
    font-weight: 600;
    transition: 0.4s;
}

.card a.btn:hover {
    transform: translateY(-3px) scale(1.05);
}

/* =========================
   About Section
========================= */
#about {
    text-align: center;
    padding: 60px 20px;
}

#about h2 {
    font-size: 2rem;
    color: #2f80ed;
    margin-bottom: 20px;
}

#about p {
    font-size: 1.1rem;
    color: #555;
    max-width: 800px;
    margin: 0 auto;
}

/* =========================
   Footer
========================= */
footer {
    background: linear-gradient(135deg, #2f80ed, #56ccf2);
    color: #fff;
    text-align: center;
    padding: 25px 20px;
    font-size: 0.9rem;
}

/* =========================
   Responsive
========================= */
@media screen and (max-width: 1024px) {
    .hero {
        height: 450px;
    }
    .hero h2 {
        font-size: 2.5rem;
    }
}

@media screen and (max-width: 768px) {
    header {
        flex-direction: column;
        align-items: flex-start;
    }
    .hero {
        height: 350px;
    }
    .hero h2 {
        font-size: 2rem;
    }
    .dashboard-cards {
        grid-template-columns: 1fr;
    }
}

@media screen and (max-width: 480px) {
    .hero h2 {
        font-size: 1.6rem;
    }
    .hero p {
        font-size: 1rem;
    }
    .hero .btn {
        padding: 12px 25px;
    }
}
</style>
</head>
<body>

<!-- HEADER -->
<header>
    <div class="logo-title">
        <img src="assets/images/logo.png" alt="Healthcare Portal Logo" class="logo">
        <h1><a href="index.php">Healthcare Portal</a></h1>
    </div>
    <nav>
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
        <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
        <a href="#about"><i class="fas fa-info-circle"></i> About</a>
    </nav>
</header>

<!-- HERO SECTION -->
<section class="hero">
    <div>
        <h2>Welcome to Healthcare Portal</h2>
        <p>Book appointments, view prescriptions, and manage healthcare easily.</p>
        <a href="register.php" class="btn">Get Started</a>
    </div>
</section>

<!-- FEATURES / CARDS -->
<section class="main-container dashboard-cards">
    <div class="card">
        <h3>Find Doctors</h3>
        <p>Search for available doctors by specialization and book instantly.</p>
        <a href="modules/patient/book_appointment.php" class="btn">Book Now</a>
    </div>
    <div class="card">
        <h3>Manage Appointments</h3>
        <p>Track your upcoming and past appointments at a glance.</p>
        <a href="modules/patient/view_appointments.php" class="btn">View Appointments</a>
    </div>
    <div class="card">
        <h3>Access Prescriptions</h3>
        <p>View and download prescriptions and lab reports online.</p>
        <a href="modules/patient/view_prescription.php" class="btn">View Prescriptions</a>
    </div>
</section>

<!-- ABOUT SECTION -->
<section class="main-container" id="about">
    <h2>About Us</h2>
    <p>
        Healthcare Portal is a one-stop solution for patients, doctors, and admins to manage healthcare efficiently.
        Our mission is to simplify medical services, making them accessible and organized for everyone.
    </p>
</section>

<!-- FOOTER -->
<footer>
    <p>&copy; 2026 Healthcare Portal. All rights reserved.</p>
    <p>Contact: support@healthcareportal.com | +91 1234567890</p>
</footer>

<script src="assets/js/script.js"></script>
</body>
</html>