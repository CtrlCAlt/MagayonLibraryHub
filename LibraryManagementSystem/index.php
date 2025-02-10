<?php
include("connect.php"); // Include your database connection
session_start();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'enroll') {
        // Enrollment Logic
        $fullname = trim($_POST['fullname']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Check if any field is empty
        if (empty($fullname) || empty($email) || empty($password)) {
            $_SESSION['error_message'] = 'All fields are required.';
            header("Location: index.php");
            exit();
        }

        // Check if username is already taken
        $stmt = $conn->prepare("SELECT id FROM `users` WHERE `email` = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $_SESSION['error_message'] = 'Email is already taken. Please choose another.';
            header("Location: index.php");
            exit();
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Save user into the database
        $stmt = $conn->prepare("INSERT INTO `users`(`fullName`, `email`, `password`) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fullname, $email, $hashedPassword);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Sign up successfully! You can now log in.';
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error_message'] = 'An error occurred. Please try again.';
            header("Location: index.php");
            exit();
        }
    } elseif ($_POST['action'] === 'login') {
        // Login Logic
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Validate input
        if (empty($email) || empty($password)) {
            $_SESSION['error_message'] = 'Email and password cannot be empty.';
            header("Location: index.php");
            exit();
        }

        // Check credentials
        $stmt = $conn->prepare("SELECT id, password, user_type FROM `users` WHERE `email` = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();

            if (password_verify($password, $user_data['password'])) {
                // Set session variables
                $_SESSION['id'] = $user_data['id'];
                $_SESSION['user_type'] = $user_data['user_type'];
                $_SESSION['email'] = $email;

                // Redirect based on user type
                if ($user_data['user_type'] === 'u') {
                    header("Location: student.php");
                } elseif ($user_data['user_type'] === 'a') {
                    header("Location: admin.php");
                }
                exit();
            } else {
                $_SESSION['error_message'] = 'Incorrect password. Please try again.';
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = 'No account found with this email.';
            header("Location: index.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>This is Home Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="index.css">
    <style>
    .body {
    width: 100%;
    height: 100vh;
    background-image: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)), url('./image/library\ bg.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed; /* This makes the background fixed */
    position: relative;
}
        /* Glassmorphism style */
.glass-modal {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 15px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease-in-out, opacity 0.3s ease-in-out;
}

/* Animation for modal appearance */
.modal.fade .modal-dialog {
    transform: scale(0.8);
    opacity: 0;
}

.modal.show .modal-dialog {
    transform: scale(1);
    opacity: 1;
}

#developer-section {
        margin-top: 550px; /* Add space between the title and this section */
    }

    </style>
</head>

<body class="body">
    <div class="banner">
        <nav class="navbar navbar-expand-lg container">
            <a class="navbar-brand logo" href="#">MPLH</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    
                    <li class="nav-item">
                        <a class="nav-link" href="#about-section">About</a>
                    </li>
                    

                </ul>
            </div>
        </nav>
        <div class="content">
            <h1>Magayon Public Library Hub </h1>
            <div>
                <button class="btnLogin-popup btn btn-outline-primary">Login/Signup</button>
            </div>
        </div>

        <!-- Popup Success Message -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<script type="text/javascript">
                    window.onload = function() {
                        alert("' . $_SESSION['success_message'] . '");
                    };
                  </script>';
            unset($_SESSION['success_message']); // Unset after displaying
        }
        ?>

        <div class="wrapper">
            <span class="icon-close">
                <ion-icon name="close"></ion-icon>
            </span>
            <?php include("formlogin.php") ?>

            <div class="form-box register">
                <h2>Sign Up</h2>
                <form action="index.php" method="POST">
                    <input type="hidden" name="action" value="enroll">
                    <div class="input-box">
                        <span class="icon"><ion-icon name="person"></ion-icon></span>
                        <input type="text" name="fullname" required placeholder="">
                        <label>Full Name</label>
                    </div>
                    <div class="input-box">
                        <span class="icon"><ion-icon name="mail"></ion-icon></span>
                        <input type="text" name="email" required placeholder="">
                        <label>Email</label>
                    </div>
                    <div class="input-box">
                        <span class="icon"><ion-icon name="lock-closed"></ion-icon></span>
                        <input type="password" name="password" required placeholder="">
                        <label>Password</label>
                    </div>
                    
                    <button type="submit" class="btn-log">Sign up</button>
                    <div class="login-register">
                        <p>Already have an account? <a href="#" class="login-link">Login now</a></p>
                    </div>
                </form>
            </div>
    </div> 
    </div>


<!-- About Section -->
<div id="about-section" class="container-fluid py-5" style="color: white; margin-top:50%">
    <div class="container">
        <h2 class="text-center mb-4" style="color: #FFD700;">About</h2>
        <div class="row">
            <div class="col-md-12" style="padding-left: 20%; padding-right: 20%;">
                <p style="font-size: 1.2rem; line-height: 1.8;">
                    The **Magayon Public Library Hub** is a vibrant community space dedicated to fostering a love for reading, learning, and personal growth. 
                    We provide an extensive collection of books, digital resources, and educational programs to cater to individuals of all ages and backgrounds.
                </p>
                <p style="font-size: 1.2rem; line-height: 1.8;">
                    Our mission is to empower the community through access to knowledge, creativity, and culture. Whether you're a student, professional, or lifelong learner, 
                    the Magayon Public Library Hub offers a welcoming environment to explore, discover, and grow. We strive to promote literacy, creativity, and community engagement in every way we can.
                </p>
                <p style="font-size: 1.2rem; line-height: 1.8;">
                    From quiet reading areas to interactive workshops, the Library Hub is a place for all to come together, collaborate, and enrich their lives with knowledge. 
                    Visit us today and explore a world of possibilities.
                </p>
            </div>
        </div>
    </div>
</div>

 <script>
// Optional script to ensure smooth animations
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('schoolDescriptionModal');

    modal.addEventListener('show.bs.modal', () => {
        modal.classList.add('animate-in');
    });

    modal.addEventListener('hidden.bs.modal', () => {
        modal.classList.remove('animate-in');
    });
});

 </script>
    <!-- Bootstrap JS -->
    <script src="login.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
