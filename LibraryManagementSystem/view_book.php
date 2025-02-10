<?php
include('connect.php');
session_start();

// Check if the book ID is provided
if (isset($_GET['id'])) {
    $book_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Fetch book details from the database
    $query = "SELECT `id`, `title`, `author`, `published_date`, `genre`, `quantity`, `status`, `book_image`, `content`, `isbn`, `publisher` FROM `books` WHERE `id` = '$book_id'";
    $result = mysqli_query($conn, $query);

    // Check if the book exists
    if (mysqli_num_rows($result) > 0) {
        $book = mysqli_fetch_assoc($result);
    } else {
        echo "<p class='text-center text-red-500'>Book not found!</p>";
        exit;
    }
} else {
    echo "<p class='text-center text-red-500'>Invalid book ID!</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Book Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <!-- Top Navigation -->
    <?php include('includes/side_nav.php'); ?>

    <!-- Main Content -->
    <main class="flex flex-col items-center bg-gray-100 p-6">
        <h1 class="text-3xl font-bold text-gray-800">Book Details</h1>

        <div class="bg-white border rounded-lg shadow-lg p-6 mt-6 w-full lg:w-2/3 xl:w-1/2 mx-auto">
            
            <!-- Book Image -->
            <?php if (!empty($book['book_image'])): ?>
                <div class="flex justify-center">
                    <img src="<?= $book['book_image'] ?>" 
                        alt="<?= htmlspecialchars($book['title']) ?>" 
                        class="w-auto max-w-full max-h-[500px] object-contain rounded-lg">
                </div>
            <?php else: ?>
                <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center text-gray-500">
                    No Image Available
                </div>
            <?php endif; ?>

            <!-- Book Details -->
            <div class="mt-6">
                <h2 class="text-2xl font-bold text-gray-800 text-center"><?= htmlspecialchars($book['title']) ?></h2>
                
                <div class="mt-4 space-y-2 text-gray-700">
                    <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
                    <p><strong>Genre:</strong> <?= htmlspecialchars($book['genre']) ?></p>
                    <p><strong>Year Published:</strong> <?= date('Y', strtotime($book['published_date'])) ?></p>
                    <p><strong>ISBN Number:</strong> <?= htmlspecialchars($book['isbn']) ?></p>
                    <p><strong>Publisher:</strong> <?= htmlspecialchars($book['publisher']) ?></p>
                    <p><strong>Quantity Available:</strong> <?= htmlspecialchars($book['quantity']) ?></p>
                    <p class="<?= $book['quantity'] > 0 ? 'text-green-600' : 'text-red-600' ?>">
                        <strong>Status:</strong> <?= $book['quantity'] > 0 ? 'Available' : 'Not Available' ?>
                    </p>
                </div>

                <!-- Description -->
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-800">Description Summary</h3>
                    <p class="mt-2 text-gray-600"><?= nl2br(htmlspecialchars($book['content'])) ?></p>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-6 text-center">
                <a href="student.php" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                    Back to Books
                </a>
            </div>
        </div>
    </main>

    <?php include('includes/footer.php'); ?>

</body>
</html>

<?php
mysqli_close($conn);
?>
