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

// Handle borrowing book action
if (isset($_POST['borrow'])) {
    $book_id = $_POST['book_id'];
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $contact_number = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Check if the book is available
    $check_query = "SELECT `quantity` FROM `books` WHERE `id` = $book_id";
    $result = mysqli_query($conn, $check_query);
    $row = mysqli_fetch_assoc($result);

    if ($row['quantity'] > 0) {
        $expected_return_date = date('Y-m-d', strtotime('+5 days'));

        // Set initial status as 'Active'
        $status = 'Borrowed';

        // Insert into borrowed_books table
        $insert_query = "INSERT INTO `borrowed_books` (`user_id`, `book_id`, `expected_return_date`, `status`, `full_name`, `contact_number`, `address`) 
                         VALUES ($user_id, $book_id, '$expected_return_date', '$status', '$full_name', '$contact_number', '$address')";
        if (mysqli_query($conn, $insert_query)) {
            // Update book quantity
            $update_query = "UPDATE `books` SET `quantity` = `quantity` - 1 WHERE `id` = $book_id";
            mysqli_query($conn, $update_query);

            // Redirect to index.php after successful borrowing
            header("Location: borrow_books.php");
            exit;
        } else {
            $error = 'Error borrowing the book. Please try again.';
        }
    } else {
        $error = 'This book is not available for borrowing.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Book - Library System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <!-- Top Navigation -->
    <?php include('includes/side_nav.php'); ?>

    <!-- Main Content -->
    <main class="flex flex-col items-center justify-center bg-gray-100 p-6">
        <h1 class="text-2xl font-bold text-gray-800 text-center">Borrow Book</h1>
        <p class="mt-2 text-gray-600 text-center">You can borrow a book from the available list below.</p>

        <?php if ($error): ?>
            <div class="mt-4 text-center text-red-600">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="mt-6 w-full sm:w-1/2 lg:w-2/3">
            <h2 class="text-xl font-bold text-gray-700 text-center">Available Books</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-4">
                <?php
                // Get available books
                $query = "SELECT `id`, `title`, `author`, `published_date`, `genre`, `quantity` FROM `books` WHERE `quantity` > 0";
                $result = mysqli_query($conn, $query);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<div class='bg-white border rounded-lg shadow p-4 text-center'>";
                        echo "<h3 class='text-lg font-bold text-gray-800'>" . $row['title'] . "</h3>";
                        echo "<p class='text-sm text-gray-600'><strong>Author:</strong> " . $row['author'] . "</p>";
                        echo "<p class='text-sm text-gray-600'><strong>Published:</strong> " . $row['published_date'] . "</p>";
                        echo "<p class='text-sm text-gray-600'><strong>Genre:</strong> " . $row['genre'] . "</p>";
                        echo "<p class='text-sm text-gray-600'><strong>Quantity Available:</strong> " . $row['quantity'] . "</p>";
                        echo "<form method='POST' action='' class='mt-2'>";
                        echo "<input type='hidden' name='book_id' value='" . $row['id'] . "'>";

                        // Add fields for full name, contact number, address, and expected return date
                        echo "<input type='text' name='full_name' placeholder='Full Name' class='px-4 py-2 border rounded-md mt-2 w-full' required>";
                        echo "<input type='text' name='contact_number' placeholder='Contact Number' class='px-4 py-2 border rounded-md mt-2 w-full' required>";
                        echo "<textarea name='address' placeholder='Address' class='px-4 py-2 border rounded-md mt-2 w-full' required></textarea>";

                        // Display the expected return date (formatted as '29 Jan 2025')
                        $formatted_expected_return_date = date('d M Y', strtotime('+5 days'));
                        echo "<label for='expected_return_date' class='text-gray-600 mt-2'>Expected Return Date:</label>";
                        echo "<input type='text' value='" . $formatted_expected_return_date . "' class='px-4 py-2 border rounded-md mt-2 w-full' readonly>";

                        echo "<button type='submit' name='borrow' class='px-4 py-2 bg-green-500 text-white rounded-md mt-2'>Borrow</button>";
                        echo "</form>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='text-center text-gray-600'>No books are available for borrowing at the moment.</p>";
                }

                mysqli_close($conn);
                ?>
            </div>
        </div>
    </main>
</body>
</html>
