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

// Check for overdue books and update status
$current_date = date('Y-m-d');
$update_status_query = "UPDATE `borrowed_books` 
                        SET `status` = 'Overdue' 
                        WHERE `status` = 'borrow' AND `expected_return_date` < '$current_date'";
mysqli_query($conn, $update_status_query);

// Handling search functionality
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
}

$borrowed_books_query = "SELECT bb.borrow_id, bb.book_id, bb.borrow_date, bb.expected_return_date, bb.status, b.title, b.author, b.book_image, bb.overdue_fine
                         FROM borrowed_books bb
                         JOIN books b ON bb.book_id = b.id
                         WHERE bb.user_id = $user_id
                         AND (b.title LIKE '%$search_query%' OR b.author LIKE '%$search_query%')";
$borrowed_books_result = mysqli_query($conn, $borrowed_books_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowed Books</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex flex-col min-h-screen">
    <!-- Top Navigation -->
    <?php include('includes/side_nav.php'); ?>

    <!-- Main Content -->
    <main class="flex-1 p-6 flex flex-col items-center text-center">
        <h1 class="text-2xl font-bold text-gray-800">Borrowed Books</h1>
        <p class="mt-2 text-gray-600">View the list of books you have borrowed.</p>

        <!-- Display Error/Success -->
        <?php if ($error): ?>
            <div class="mt-4 p-3 bg-red-500 text-white rounded-md"><?= $error ?></div>
        <?php endif; ?>

        <!-- Borrowed Books Section -->
        <div class="mt-8 w-full max-w-4xl">
            <form method="GET" action="borrow_books.php" class="flex items-center justify-center space-x-2 mt-4">
                <input type="text" name="search" value="<?= $search_query ?>" placeholder="Search borrowed books" class="px-4 py-2 border rounded-md">
                <button type="submit" class="bg-gray-500 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-orange-600 hover:scale-105 transition duration-300">Search</button>
            </form>

            <!-- Borrowed Books in Card Format -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
    <?php if (mysqli_num_rows($borrowed_books_result) > 0): ?>
        <?php while ($borrowed_book = mysqli_fetch_assoc($borrowed_books_result)): ?>
            <?php
            $formatted_borrow_date = date('d M Y', strtotime($borrowed_book['borrow_date']));
            $formatted_expected_return_date = date('d M Y', strtotime($borrowed_book['expected_return_date']));
            ?>

<div class="bg-white border rounded-lg shadow p-4 text-center flex flex-col min-h-[24rem] relative hover:scale-105 transition duration-300">
    <!-- Overdue Badge -->
    <?php if ($borrowed_book['status'] == 'Overdue'): ?>
        <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-full">Overdue</span>
    <?php endif; ?>

    <!-- Book Image -->
    <?php if (!empty($borrowed_book['book_image'])): ?>
        <img src="<?= htmlspecialchars($borrowed_book['book_image']) ?>" 
             alt="<?= htmlspecialchars($borrowed_book['title']) ?>" 
             class="w-full h-48 object-cover rounded-lg mb-4">
    <?php else: ?>
        <div class="w-full h-48 bg-gray-200 rounded-lg mb-4 flex items-center justify-center text-gray-500">
            No Image Available
        </div>
    <?php endif; ?>

    <!-- Truncate title if longer than 19 characters -->
    <?php
    $displayTitle = (strlen($borrowed_book['title']) > 19) ? substr($borrowed_book['title'], 0, 19) . "..." : $borrowed_book['title'];
    ?>

    <!-- Book Details -->
    <h3 class="text-lg font-bold text-gray-800 break-words"><?= htmlspecialchars($displayTitle) ?></h3>
    <p class="text-sm text-gray-600"><strong>Author:</strong> <?= htmlspecialchars($borrowed_book['author']) ?></p>
    <p class="text-sm text-gray-600"><strong>Borrow Date:</strong> <?= $formatted_borrow_date ?></p>
    <p class="text-sm text-gray-600"><strong>Expected Return Date:</strong> <?= $formatted_expected_return_date ?></p>
    <p class="text-sm <?= $borrowed_book['status'] == 'Overdue' ? 'text-red-500' : 'text-green-500' ?>">
        <strong>Status:</strong> <?= htmlspecialchars($borrowed_book['status']) ?>
    </p>
    <p class="text-sm text-gray-600"><strong>Overdue Balance:</strong> ₱<?= number_format($borrowed_book['overdue_fine'], 2) ?></p>
</div>

        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-center text-gray-600 col-span-full">You haven't borrowed any books yet or no matching records found.</p>
    <?php endif; ?>
</div>

        </div>
    </main>

    <?php include('includes/footer.php'); ?>

</body>

</html>

<?php mysqli_close($conn); ?>