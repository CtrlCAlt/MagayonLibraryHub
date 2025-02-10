<style>

.banner {
    width: 100%;
    height: 100vh;
    background-image: linear-gradient(rgba(0,0,0,0.75), rgba(0,0,0,0.75)),url('image/Student.png');
    background-size: cover;
    background-position: center;
    position: relative;
}
.logo {
    font-size: 36px;
    font-weight: bold;
    font-family: 'Poppins', sans-serif;
    text-transform: uppercase;
    letter-spacing: 4px;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
    background: linear-gradient(90deg, #ff5733, #f1c40f);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}


    .navbar-nav {
    list-style: none;
    display: flex;
  }
.navbar-nav .nav-link{
    color: #6fa2ee !important;
    text-decoration: none;
    position: relative; /* Needed for positioning the ::after pseudo-element */
    transition: color 0.3s ease; /* Smooth color transition */
}

.navbar-nav .nav-link::after {
    content: '';
    position: absolute;
    width: 0; /* Initially hidden */
    height: 3px; /* Thickness of the underline */
    border-radius: 10px;
    background-color: #e58b0e; /* Color of the underline */
    left: 0;
    bottom: -3px; /* Position it slightly below the text */
    transition: width 0.3s ease; /* Smooth underline animation */
}

.navbar-nav .nav-link:hover {
    color: #e58b0e !important; /* Change text color on hover */
}

.navbar-nav .nav-link:hover::after {
    width: 100%; /* Expand the underline fully on hover */
}
</style>

<nav class="navbar navbar-expand-lg container">
            <a class="navbar-brand logo" href="#">STMS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#about-section">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#developer-section">Developers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Log Out</a>
                    </li>
                </ul>
            </div>
        </nav>

