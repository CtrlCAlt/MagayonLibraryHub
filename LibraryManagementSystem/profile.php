<?php
session_start();
include('connect.php');

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['id'];
$error = '';

// Fetch user profile data
$user_query = "SELECT id, fullName, email, user_type, date FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user_data = mysqli_fetch_assoc($user_result);

if (!$user_data) {
    $error = "Unable to fetch user data.";
}

// Handling profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    // Update query
    $update_query = "UPDATE users SET fullName = '$full_name', email = '$email' WHERE id = $user_id";
    if (mysqli_query($conn, $update_query)) {
        $success_message = "Profile updated successfully!";
    } else {
        $error = "Error updating profile: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .input-field:focus {
            border-color:rgb(247, 119, 14);
            box-shadow: 0 0 0 2px rgb(238, 122, 6);
        }

        .btn-primary {
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-primary:hover {
            background-color:rgb(235, 149, 37);
            transform: scale(1.05);
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .input-label {
            font-size: 1rem;
            font-weight: 500;
            color: #4A4A4A;
        }

        .error-msg, .success-msg {
            padding: 1rem;
            margin-top: 1rem;
            border-radius: 0.375rem;
            color: white;
            font-size: 1rem;
        }

        .error-msg {
            background-color: #EF4444;
        }

        .success-msg {
            background-color: #10B981;
        }
    </style>
</head>

<body class="bg-gray-100 flex flex-col min-h-screen font-sans">
    <!-- Top Navigation -->
    <?php include('includes/side_nav.php'); ?>

    <!-- Main Content -->
    <main class="flex-1 p-6 flex flex-col items-center text-center">
        <h1 class="text-3xl font-bold text-gray-800">User Profile</h1>
        <p class="mt-2 text-gray-600">View and edit your profile information</p>

        <!-- Display Error/Success -->
        <?php if ($error): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php elseif (isset($success_message)): ?>
            <div class="success-msg"><?= $success_message ?></div>
        <?php endif; ?>

        <!-- Profile Information -->
        <div class="mt-6 w-full max-w-4xl bg-white border rounded-lg shadow card p-6">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Your Profile Information</h2>
            <form method="POST" action="profile.php" class="space-y-6">
                <div class="flex flex-col">
                    <label for="full_name" class="input-label">Full Name</label>
                    <div class="flex items-center space-x-3">
                        <input type="text" id="full_name" name="full_name" value="<?= $user_data['fullName'] ?>"
                            class="input-field px-4 py-2 border rounded-md w-full transition-colors focus:outline-none" required>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label for="email" class="input-label">Email</label>
                    <div class="flex items-center space-x-3">
                        <input type="text" id="email" name="email" value="<?= $user_data['email'] ?>"
                            class="input-field px-4 py-2 border rounded-md w-full transition-colors focus:outline-none" required>
                    </div>
                </div>

                <div class="flex flex-col">
                    <label for="date" class="input-label">Account Created</label>
                    <div class="flex items-center space-x-3">
                        <input type="text" id="date" name="date" value="<?= date('d M Y', strtotime($user_data['date'])) ?>"
                            class="px-4 py-2 border rounded-md w-full text-gray-600 bg-gray-100 cursor-not-allowed"
                            disabled>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-primary px-6 py-3 bg-gray-500 text-white rounded-md mt-6 w-full">
                    Update Profile
                </button>
            </form>
        </div>
    </main>

    <?php include('includes/footer.php'); ?>

</body>

</html>


<?php mysqli_close($conn); ?>
