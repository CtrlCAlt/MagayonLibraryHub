<?php
include('connect.php');
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Function to show the modal with the correct book details and expected borrow date
        function showModal(bookId, bookTitle, bookAuthor, bookQuantity) {
            // Show the modal
            document.getElementById('modal').classList.remove('hidden');
            
            // Set the book details in the modal
            document.getElementById('book_id').value = bookId;
            document.getElementById('book_title').innerText = bookTitle;
            document.getElementById('book_author').innerText = bookAuthor;
            document.getElementById('book_quantity').innerText = bookQuantity;

            // Calculate the expected return date (5 days from today)
            const today = new Date();
            today.setDate(today.getDate() + 5);
            const day = today.getDate().toString().padStart(2, '0');
            const month = (today.getMonth() + 1).toString().padStart(2, '0');
            const year = today.getFullYear();
            const expectedReturnDate = `${day} ${month} ${year}`;

            // Set the expected return date in the modal
            document.getElementById('expected_return_date').innerText = expectedReturnDate;
        }

        // Function to close the modal
        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }
    </script>
</head>
<body class="bg-gray-100">

    <!-- Top Navigation -->
    <?php include('includes/side_nav.php'); ?>

    <!-- Main Content -->
    <main class="flex flex-col items-start justify-start bg-gray-100 p-6">
    <h1 class="text-2xl font-bold text-gray-800 text-left w-full text-center">Welcome to Magayon Library Hub</h1>

    <!-- Search Form -->
    <div class="mt-6 flex justify-center w-full">
        <form method="GET" action="" class="flex items-center space-x-2 w-full sm:w-1/2 lg:w-1/3">
            <input type="text" name="search" placeholder="Search for books..." class="px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500 w-full" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
            <button type="submit" class="bg-gray-500 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:bg-orange-600 hover:scale-105 transition duration-300">Search</button>
        </form>
    </div>

    <div class="mt-6 w-full flex justify-center">
    <div class="w-full lg:w-2/3">
        <h2 class="text-xl font-bold text-gray-700 text-center">Available Books</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mt-4 justify-center">

            <?php
            // Database connection
            include('connect.php');

            // Get search term
            $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

            // Modify query to include search term
            $query = "SELECT `id`, `title`, `author`, `published_date`, `genre`, `quantity`, `status`, `book_image` FROM `books` WHERE `quantity` > 0";
            
            if ($search) {
                $query .= " AND (`title` LIKE '%$search%' OR `author` LIKE '%$search%' OR `genre` LIKE '%$search%')";
            }

            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='bg-white border rounded-lg shadow p-4 text-center hover:scale-105 transition duration-300'>";
                    
                    // Display the book image
                    if (!empty($row['book_image'])) {
                        echo "<img src='" . $row['book_image'] . "' alt='" . $row['title'] . "' class='w-full h-48 object-cover rounded-lg mb-4'>";
                    } else {
                        echo "<div class='w-full h-48 bg-gray-200 rounded-lg mb-4 flex items-center justify-center text-gray-500'>No Image Available</div>";
                    }
            
                    // Truncate title if longer than 19 characters
                    $displayTitle = (strlen($row['title']) > 19) ? substr($row['title'], 0, 19) . "..." : $row['title'];
            
                    echo "<h3 class='text-lg font-bold text-gray-800'>" . htmlspecialchars($displayTitle) . "</h3>";
                    echo "<p class='text-sm text-gray-600'><strong>Quantity Available:</strong> " . $row['quantity'] . "</p>";
                    echo "<p class='text-sm text-green-600'><strong>Status:</strong> " . ($row['quantity'] > 0 ? 'Available' : 'Not Available') . "</p>";
            
                    if ($row['quantity'] > 0) {
                        echo "<div class='flex justify-center space-x-2 mt-2'>";
                        echo "<button onclick='showModal(" . $row['id'] . ", \"" . addslashes($row['title']) . "\", \"" . addslashes($row['author']) . "\", " . $row['quantity'] . ")' class='px-4 py-2 bg-orange-500 text-white rounded-md hover:scale-105 transition duration-300'>Borrow</button>";
                        echo "<a href='view_book.php?id=" . $row['id'] . "' class='px-4 py-2 bg-gray-800 text-white rounded-md hover:scale-105 transition duration-300'>View</a>";
                        echo "</div>";
                    }
            
                    echo "</div>";
                }
            } else {
                echo "<p class='text-center text-gray-600'>No books available</p>";
            }
            
            mysqli_close($conn);
            ?>
        </div>
    </main>

    <!-- Modal for Borrowing Book -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg w-96">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Borrow Book</h2>
            <p class="text-lg text-gray-700">Title: <span id="book_title" class="font-semibold"></span></p>
            <p class="text-lg text-gray-700">Author: <span id="book_author" class="font-semibold"></span></p>
            <p class="text-lg text-gray-700">Quantity Available: <span id="book_quantity" class="font-semibold"></span></p>
            <p class="text-lg text-gray-700">Expected Return Date: <span id="expected_return_date" class="font-semibold"></span></p>

            <form method="POST" action="borrow.php" class="mt-4">
                <input type="hidden" name="book_id" id="book_id">
                <input type="text" name="full_name" placeholder="Full Name" class="px-4 py-2 border rounded-md mt-2 w-full" required>
                <input type="text" name="contact_number" placeholder="Contact Number" class="px-4 py-2 border rounded-md mt-2 w-full" required>
                <textarea name="address" placeholder="Address" class="px-4 py-2 border rounded-md mt-2 w-full" required></textarea>
                <button type="submit" name="borrow" class="px-4 py-2 bg-gray-500 text-white rounded-md mt-2 w-full">Borrow</button>
            </form>

            <button onclick="closeModal()" class="mt-4 text-red-500">Close</button>
        </div>
    </div>
    </div>
</main>

<?php include('includes/footer.php'); ?>

</body>
</html>
