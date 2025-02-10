<?php
// Check if the session is already started
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Start the session if it hasn't started yet
}

// Ensure the user is logged in
if (!isset($_SESSION['id'])) {
    // Redirect to login page if not logged in
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['id'];  // Fetch the user ID from the session

// Check for overdue books for the logged-in user
$overdue_query = "SELECT COUNT(*) AS overdue_count 
                  FROM borrowed_books 
                  WHERE user_id = $user_id 
                  AND status = 'Overdue'";

$overdue_result = mysqli_query($conn, $overdue_query);
if ($overdue_result) {
    $overdue_data = mysqli_fetch_assoc($overdue_result);
    $overdue_count = $overdue_data['overdue_count'];
} else {
    // Handle error if query fails
    $overdue_count = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar with Notification Icon</title>
    <!-- Include Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="notification.css">
    <link rel="stylesheet" href="logout.css">
</head>

<body>
    <?php
    // Get the current page filename
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
    <nav class="flex justify-between items-center py-2 bg-gray-800 text-white px-4">
        <!-- Title -->
        <div class="text-xl font-semibold logo">MPLH</div>

        <!-- Navbar Links -->
        <div class="flex space-x-4">
            <a href="student.php" class="hover:bg-gray-700 px-4 py-2 rounded <?php echo ($current_page == 'student.php') ? 'bg-gray-600' : ''; ?>">Books</a>
            <a href="borrow_books.php" class="hover:bg-gray-700 px-4 py-2 rounded <?php echo ($current_page == 'borrow_books.php') ? 'bg-gray-600' : ''; ?>">Borrowed Books</a>
            <a href="profile.php" class="hover:bg-gray-700 px-4 py-2 rounded <?php echo ($current_page == 'profile.php') ? 'bg-gray-600' : ''; ?>">
                <i class="fas fa-user"></i>
            </a>

            <!-- Notification Icon with Overdue Badge -->
            <div class="relative">
                <a href="#" id="notificationIcon" class="hover:bg-gray-700 px-4 py-2 rounded inline-block">
                    <i class="fas fa-bell"></i>
                    <?php if (isset($overdue_count) && $overdue_count > 0): ?>
                        <span class="absolute top-0 right-0 transform translate-x-1/2 -translate-y-1/2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"><?php echo $overdue_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Logout with Confirmation Modal -->
            <a href="#" id="logoutLink" class="hover:bg-gray-700 px-4 py-2 rounded">Logout</a>
        </div>
    </nav>


    <!-- Notification Modal -->
    <div id="notificationModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 class="modal-title">Notifications</h2>
            <ul class="modal-list">
                <?php if ($overdue_count > 0): ?>
                    <li class="overdue-item">You have <?php echo $overdue_count; ?> overdue book(s).</li>
                <?php else: ?>
                    <li class="no-overdue-item">No overdue books.</li>
                <?php endif; ?>
            </ul>
            <a href="borrow_books.php" class="view-button">
                <button class="bg-gray-500 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-orange-600 hover:scale-105 transition duration-300">
                    View Borrowed Books
                </button>
            </a>

        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeLogoutModal">&times;</span>
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to logout?</p>
            <div class="modal-buttons">
                <button id="confirmLogout">Yes</button>
                <button id="cancelLogout">No</button>
            </div>
        </div>
    </div>

    <script>
        var modal = document.getElementById("notificationModal");
        var notificationIcon = document.getElementById("notificationIcon");
        var closeBtn = document.getElementsByClassName("close")[0];
        notificationIcon.onclick = function(event) {
            event.preventDefault();
            modal.style.display = "block";
        }
        closeBtn.onclick = function() {
            modal.style.display = "none";
        }
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Logout Confirmation
        var logoutLink = document.getElementById("logoutLink");
        var logoutModal = document.getElementById("logoutModal");
        var closeLogoutModal = document.getElementById("closeLogoutModal");
        var confirmLogout = document.getElementById("confirmLogout");
        var cancelLogout = document.getElementById("cancelLogout");

        logoutLink.onclick = function(event) {
            event.preventDefault();
            logoutModal.style.display = "block";
        }
        closeLogoutModal.onclick = function() {
            logoutModal.style.display = "none";
        }
        cancelLogout.onclick = function() {
            logoutModal.style.display = "none";
        }
        confirmLogout.onclick = function() {
            window.location.href = "logout.php";
        }
    </script>
</body>

</html>