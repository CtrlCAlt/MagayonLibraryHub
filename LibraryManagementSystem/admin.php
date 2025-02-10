<?php
session_start();
include "connect.php";
// Fetch total users with user type 'u'
$query = "SELECT COUNT(*) AS total_user FROM users WHERE user_type = 'u'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $total_user = $row['total_user'];
} else {
  $total_user = 0; // Default if no users are found
}

// Fetch total books 
$query = "SELECT COUNT(*) AS total_books FROM books WHERE status = 'Available'";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
  $row = $result->fetch_assoc();
  $total_books = $row['total_books'];
} else {
  $total_books = 0; // Default if no users are found
}



$sql = "SELECT * FROM books";
$books_result = $conn->query($sql);

// Initialize the search variable
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Modify the query based on the search term
if (!empty($search)) {
  $sql = "SELECT * FROM books WHERE title LIKE ? OR author LIKE ?";
  $stmt = $conn->prepare($sql);
  $searchTerm = "%" . $search . "%";
  $stmt->bind_param("ss", $searchTerm, $searchTerm);
  $stmt->execute();
  $books_result = $stmt->get_result();
} else {
  // Default query to fetch all books if no search term is provided
  $sql = "SELECT * FROM books";
  $books_result = $conn->query($sql);
}

// Delete book
if (isset($_GET['delete_id'])) {


  $book_id = $_GET['delete_id'];

  // Check if the book is referenced in borrowed_books
  $stmt_check = $conn->prepare("SELECT COUNT(*) FROM borrowed_books WHERE book_id = ?");
  $stmt_check->bind_param("i", $book_id);
  $stmt_check->execute();
  $stmt_check->bind_result($count);
  $stmt_check->fetch();
  $stmt_check->close();

  if ($count > 0) {
      $_SESSION['message'] = "Cannot delete the book. It is currently borrowed.";
      $_SESSION['msg_type'] = "error";
  } else {
      $stmt_delete = $conn->prepare("DELETE FROM books WHERE id = ?");
      $stmt_delete->bind_param("i", $book_id);

      if ($stmt_delete->execute()) {
          $_SESSION['message'] = "Book deleted successfully.";
          $_SESSION['msg_type'] = "success";
      } else {
          $_SESSION['message'] = "Error deleting book.";
          $_SESSION['msg_type'] = "error";
      }

      $stmt_delete->close();
  }

  $conn->close();

  // Redirect back to the books section using JavaScript (instead of PHP header)
  echo "<script>
          window.location.href = 'admin.php?section=books';
        </script>";
  exit();
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="admin.css">
</head>

<body>

<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalLabel">Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <span id="alertMessage"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

  <div class="admin-container">





  
    <!-- Sidebar -->
    <aside class="sidebar">
      <h4 class="text-center mb-4">Admin Panel</h4>
      <nav class="nav flex-column">
        <a href="#" class="nav-link active" id="dashboard-link">Dashboard</a>

  
        <a href="#" class="nav-link" id="books-link">Books</a>
        <a href="?section=borrowed-books" class="nav-link" id="borrowed-books-link">Borrowed Books</a>
        <a href="logout.php" class="nav-link" id="logout-link">Logout</a>






      </nav>
    </aside>
    <?php

    // Check if there is a success message in the session
    if (isset($_SESSION['success_message'])) {
      echo '<script type="text/javascript">
            window.onload = function() {
                alert("' . $_SESSION['success_message'] . '");
            };
          </script>';
      unset($_SESSION['success_message']); // Unset the message after displaying
    }
    ?>
    <!-- Main Content -->
    <div class="content">
      <!-- Header -->
      <header class="header d-flex justify-content-between align-items-center">
        <div class="logo">MPLH</div>
      </header>

      <!-- Dashboard Section -->
      <section id="dashboard-section" class="active">
        <h1>Dashboard</h1>
        <div class="row">
          <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
              <div class="card-body">
                <h5 class="card-title text-center">Total User</h5>
                <p class="card-text text-center">
                  <?php echo $total_user; ?>
                </p>
              </div>
            </div>
          </div>

          <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
              <div class="card-body">
                <h5 class="card-title text-center">Books</h5>
                <p class="card-text text-center">
                  <?php echo $total_books; ?>
                </p>
              </div>
            </div>
          </div>

          <?php
          // Execute the query to get the borrowed books
          $query = "SELECT `borrow_id`, `user_id`, `book_id`, `borrow_date`, `return_date` FROM `borrowed_books`";
          $result = $conn->query($query);

          // Get the total number of borrowed books
          $borrowedBooksCount = $result->num_rows; // or use a specific count if needed

          ?>

          <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
              <div class="card-body">
                <h5 class="card-title text-center">Borrowed Books</h5>
                <p class="card-text text-center"><?php echo $borrowedBooksCount; ?> books borrowed</p>
              </div>
            </div>
            </a>
          </div>

        </div>
      </section>


      <!-- Manage Books Section -->
      <section id="books-section">
        <h1>Manage Books</h1>
        <!-- Search bar -->
        <div class="mb-3">
          <form method="GET" action="" class="d-flex">
            <input type="hidden" name="section" value="books">
            <input type="text" name="search" class="form-control mr-2"
              placeholder="Search by title or author"
              value="<?php echo htmlspecialchars($search); ?>"
              style="width: 20rem; ">
            <button type="submit" class="btn btn-primary">Search</button>
          </form>
        </div>
       
        <!-- Add Book Button -->
        <button class="btn btn-primary mb-3" data-toggle="modal" data-target="#addBookModal">Add Book</button>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Title</th>
              <th>Author</th>
              <th>Published Date</th>
              <th>Publisher</th>
              <th>ISBN</th>
              <th>Genre</th>
              <th>Quantity</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php
if ($books_result->num_rows > 0) {
    while ($row = $books_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['author']) . "</td>";
        echo "<td>" . htmlspecialchars($row['published_date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['publisher']) . "</td>";
        echo "<td>" . htmlspecialchars($row['isbn']) . "</td>";
        echo "<td>" . htmlspecialchars($row['genre']) . "</td>";
        echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "<td class='d-flex justify-content-between align-items-center'>";
        echo "<button type='button' class='btn btn-primary btn-edit' 
                  data-book='" . htmlspecialchars(json_encode($row)) . "'>Edit</button>";
        echo "<a href='admin.php?section=books&delete_id=" . $row['id'] . "' 
                  class='btn btn-danger btn-sm' 
                  style='font-size: 15px; padding: 6px 6px; 
                  onclick=\"return confirm('Are you sure you want to delete this book?');\">
                  Delete
              </a>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='10'>No books found.</td></tr>";
}
?>
          </tbody>
        </table>
      </section>

      <?php include "manage_borrowed_books.php" ?>

      <section id="borrowed-books-section" class="container my-4">
        <h1 class="text-center mb-4">Manage Borrowed Books</h1>

        <!-- Search bar with filter -->
        <div class="d-flex justify-content-start mb-3">
          <form class="d-flex" method="GET" action="">
            <input type="hidden" name="section" value="borrowed-books">
            <input type="text" name="search" class="form-control me-2" style="width: 20rem;"
              placeholder="Search by user or book title"
              value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <select name="status" class="form-select me-2" style="width: 10rem;">
              <option value="">All</option>
              <option value="Borrowed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Borrowed') ? 'selected' : ''; ?>>Borrowed</option>
              <option value="Returned" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Returned') ? 'selected' : ''; ?>>Returned</option>
              <option value="Overdue" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Overdue') ? 'selected' : ''; ?>>Overdue</option>
            </select>
            <button type="submit" class="btn btn-primary">Search</button>
          </form>
        </div>

        <!-- Table to display borrowed books -->
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th>Book Title</th>
              <th>Name</th>
              <th>Borrow Date</th>
              <th>Expected Return Date</th>
              <th>Return Date</th>
              <th>Status</th>
              <th>Overdue Fine (₱)</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                $expected_return_date = $row['expected_return_date'];
                $return_date = !empty($row['return_date']) ? $row['return_date'] : date('Y-m-d'); // Use current date if not returned
                $overdueFine = calculateOverdueBalance($expected_return_date, $return_date);
                 
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['title'] ?? 'Unknown Title') . "</td>";
                echo "<td>" . htmlspecialchars($row['full_name'] ?? 'Unknown Name') . "</td>";
                echo "<td>" . htmlspecialchars($row['borrow_date']) . "</td>";
                echo "<td>" . htmlspecialchars($expected_return_date) . "</td>";
                echo "<td>" . htmlspecialchars($row['return_date'] ?: "N/A") . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "<td>" . number_format($overdueFine, 2) . "</td>";
                echo "<td>
                    <button type='button' class='btn btn-primary btn-sm btn-edit-borrowed' 
                    data-borrowed='" . htmlspecialchars(json_encode($row)) . "'>Edit</button>
                  </td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='7' class='text-center'>No borrowed books found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </section>

      <div class="modal fade" id="editBorrowedBookModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Edit Borrowed Book</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="update_borrowed_book.php">
              <div class="modal-body">
                <input type="hidden" name="borrow_id" id="edit-borrow-id">

                <div class="mb-3">
                  <label class="form-label">Borrow Date</label>
                  <input type="text" id="edit-borrow-date" class="form-control" readonly>
                </div>

                <div class="mb-3">
                  <label class="form-label">Expected Return Date</label>
                  <input type="text" id="edit-expected-return-date" class="form-control" readonly>
                </div>

                <div class="mb-3">
                  <label class="form-label">Return Date</label>
                  <input type="date" name="return_date" id="edit-return-date" class="form-control">
                </div>

                <div class="mb-3">
                  <label class="form-label">Overdue Fine (₱)</label>
                  <input type="number" step="0.01" name="overdue_fine" id="edit-overdue-fine" class="form-control">
                </div>

                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select name="status" id="edit-status" class="form-control">
                    <option value="Borrowed">Borrow</option>
                    <option value="Returned">Returned</option>
                  </select>
                </div>
              </div>

              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Update</button>
              </div>
            </form>
          </div>
        </div>
      </div>



      <!-- Edit Book Modal -->
      <div class="modal fade" id="editBookModal" tabindex="-1" aria-labelledby="editBookModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editBookModalLabel">Edit Book</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="update_book.php">
              <div class="modal-body">
                <input type="hidden" name="book_id" id="edit-book-id">
                <div class="mb-3">
                  <label for="edit-title" class="form-label">Title</label>
                  <input type="text" name="title" id="edit-title" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label for="edit-author" class="form-label">Author</label>
                  <input type="text" name="author" id="edit-author" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label for="edit-published-date" class="form-label">Published Date</label>
                  <input type="date" name="published_date" id="edit-published-date" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label for="edit-genre" class="form-label">Genre</label>
                  <input type="text" name="genre" id="edit-genre" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label for="edit-quantity" class="form-label">Quantity</label>
                  <input type="number" name="quantity" id="edit-quantity" class="form-control" required>
                </div>
                <div class="form-group">
                  <label for="edit-status">Status</label>
                  <select name="status" id="edit-status" class="form-control">
                    <option value="Available">Available</option>
                    <option value="Borrowed">Borrowed</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-success">Update Book</button>
              </div>
            </form>
          </div>
        </div>
      </div>


      <!-- Add Book Modal -->
      <div class="modal fade" id="addBookModal" tabindex="-1" aria-labelledby="addBookModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="addBookModalLabel">Add New Book</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <form method="POST" action="add_book.php" enctype="multipart/form-data">
              <div class="modal-body">
                <!-- Title -->
                <div class="form-group">
                  <label for="title">Title</label>
                  <input type="text" name="title" class="form-control" required>
                </div>

                <!-- Author -->
                <div class="form-group">
                  <label for="author">Author</label>
                  <input type="text" name="author" class="form-control" required>
                </div>

                <!-- ISBN -->
                <div class="form-group">
                  <label for="isbn">ISBN</label>
                  <input type="text" name="isbn" class="form-control" required>
                </div>

                <!-- publisher -->
                <div class="form-group">
                  <label for="isbn">Publisher</label>
                  <input type="text" name="publisher" class="form-control" required>
                </div>


                <!-- Published Date -->
                <div class="form-group">
                  <label for="published_date">Published Date</label>
                  <input type="date" name="published_date" class="form-control" required>
                </div>



                <!-- Genre -->
                <div class="form-group">
                  <label for="genre">Genre</label>
                  <input type="text" name="genre" class="form-control" required>
                </div>

                <!-- Quantity -->
                <div class="form-group">
                  <label for="quantity">Quantity</label>
                  <input type="number" name="quantity" class="form-control" required>
                </div>
                <!-- status -->
                <div class="form-group">
                  <label for="status">Status</label>
                  <select name="status" class="form-control">
                    <option value="Available">Available</option>
                    <option value="Borrowed">Borrowed</option>
                  </select>
                </div>

                <!-- Book Content (Text) -->
                <div class="form-group">
                  <label for="content">Book Content (Text)</label>
                  <textarea name="content" class="form-control" rows="5" placeholder="Enter book content here..."></textarea>
                </div>

                <div class="form-group">
                  <label for="book_image">Upload Book Cover (JPG/PNG)</label>
                  <input type="file" name="book_image" class="form-control" accept=".jpg,.jpeg,.png">
                </div>

                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-success">Save Book</button>
                </div>
            </form>
          </div>
        </div>
      </div>



      <!-- Bootstrap JavaScript and dependencies -->
      <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
      <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
      <!-- Bootstrap 5 JS and Popper -->
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
      <script>
        // Show the correct section based on query parameters
        const urlParams = new URLSearchParams(window.location.search);
        const section = urlParams.get('section') || 'dashboard'; // Default to 'dashboard'

        document.querySelectorAll('.content section').forEach((sec) => sec.classList.add('d-none'));
        document.querySelector(`#${section}-section`).classList.remove('d-none');
        document.querySelectorAll('.nav-link').forEach((nav) => nav.classList.remove('active'));
        document.querySelector(`#${section}-link`).classList.add('active');

        // Toggle between sections on sidebar navigation click
        document.querySelectorAll('.nav-link').forEach((link) => {
          link.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default link behavior

            // Update active state for sidebar links
            document.querySelectorAll('.nav-link').forEach((nav) => nav.classList.remove('active'));
            link.classList.add('active');

            // Update URL with the selected section
            const targetSection = link.id.replace('-link', '-section');
            history.pushState({}, '', `?section=${targetSection.replace('-section', '')}`);

            // Show the target section and hide others
            document.querySelectorAll('.content section').forEach((section) => section.classList.add('d-none'));
            document.querySelector(`#${targetSection}`).classList.remove('d-none');
          });
        });












        // Handle logout functionality
        const logoutLink = document.querySelector('#logout-link');
        if (logoutLink) {
          logoutLink.addEventListener('click', (e) => {
            e.preventDefault(); // Prevent default link behavior

            // Show confirmation dialog
            const isConfirmed = confirm('Are you sure you want to log out?');

            if (isConfirmed) {
              // Perform logout actions, such as clearing session data or tokens
              sessionStorage.clear(); // Clear session data (or use appropriate method)

              // Redirect to the logout page or home page after logout
              window.location.href = 'logout.php'; // Change this URL to your logout page URL
            } else {
              // Redirect to the dashboard section if the user cancels logout
              window.location.href = '?section=dashboard'; // Redirect to the dashboard section
            }
          });
        }

        document.addEventListener('DOMContentLoaded', () => {
          const editButtons = document.querySelectorAll('.btn-edit'); // All edit buttons
          const editBookModal = document.getElementById('editBookModal');
          const modalInstance = new bootstrap.Modal(editBookModal); // Bootstrap 5 modal instance

          // Add click event to each edit button
          editButtons.forEach((button) => {
            button.addEventListener('click', () => {
              const bookData = JSON.parse(button.getAttribute('data-book'));

              // Populate modal fields with book data
              document.getElementById('edit-book-id').value = bookData.id;
              document.getElementById('edit-title').value = bookData.title;
              document.getElementById('edit-author').value = bookData.author;
              document.getElementById('edit-published-date').value = bookData.published_date;
              document.getElementById('edit-genre').value = bookData.genre;
              document.getElementById('edit-quantity').value = bookData.quantity;

              // Populate the status field
              const statusSelect = document.getElementById('edit-status');
              statusSelect.value = bookData.status;

              // Show the modal
              modalInstance.show();
            });
          });
        });


        document.querySelectorAll('.btn-edit-borrowed').forEach(button => {
          button.addEventListener('click', function() {
            const borrowed = JSON.parse(this.dataset.borrowed);

            // Populate the modal with the borrowed data
            document.getElementById('edit-borrow-id').value = borrowed.borrow_id;
            document.getElementById('edit-borrow-date').value = borrowed.borrow_date;
            document.getElementById('edit-expected-return-date').value = borrowed.expected_return_date;
            document.getElementById('edit-return-date').value = borrowed.return_date || ''; // Set to empty if no return date
            document.getElementById('edit-status').value = borrowed.status;

            // Log values for debugging
            console.log({
              borrow_id: borrowed.borrow_id,
              borrow_date: borrowed.borrow_date,
              return_date: document.getElementById('edit-return-date').value,
              overdue_fine: document.getElementById('edit-overdue-fine').value,
              status: document.getElementById('edit-status').value
            });

            // Show the modal
            new bootstrap.Modal(document.getElementById('editBorrowedBookModal')).show();
          });
        });

        // Check if there is a session message to show in modal
    <?php if (isset($_SESSION['message'])): ?>
        var message = "<?php echo $_SESSION['message']; ?>";
        var messageType = "<?php echo $_SESSION['msg_type']; ?>"; // 'success' or 'error'

        // Set alert message in modal
        document.getElementById('alertMessage').innerText = message;

        // Show the modal
        var alertModal = new bootstrap.Modal(document.getElementById('alertModal'), {
            keyboard: false
        });
        alertModal.show();

        // Clear the session message so it doesn't show again
        <?php unset($_SESSION['message']); unset($_SESSION['msg_type']); ?>
    <?php endif; ?>
      </script>







</body>

</html>