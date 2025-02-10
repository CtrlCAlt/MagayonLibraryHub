<?php
include "connect.php"; // Database connection

// Set a default fine rate if not already set in the session
if (!isset($_SESSION['fine_per_day'])) {
    $_SESSION['fine_per_day'] = 20.00; // Default fine rate is ₱5 per day
}
$fine_per_day = $_SESSION['fine_per_day'];

// Handle book return
if (isset($_GET['return_id'])) {
    $return_id = intval($_GET['return_id']);

    $book_query = "SELECT book_id, expected_return_date FROM borrowed_books WHERE borrow_id = ?";
    $book_stmt = $conn->prepare($book_query);
    $book_stmt->bind_param("i", $return_id);
    $book_stmt->execute();
    $book_stmt->bind_result($book_id, $expected_return_date);
    $book_stmt->fetch();
    $book_stmt->close();

    $overdueFine = calculateOverdueBalance($expected_return_date, date('Y-m-d'));
    $status = ($overdueFine > 0) ? 'Overdue' : 'Returned';

    $update_query = "UPDATE borrowed_books SET return_date = NOW(), status = ?, overdue_fine = ? WHERE borrow_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sdi", $status, $overdueFine, $return_id);
    $stmt->execute();

    $increment_query = "UPDATE books SET quantity = quantity + 1 WHERE id = ?";
    $increment_stmt = $conn->prepare($increment_query);
    $increment_stmt->bind_param("i", $book_id);
    $increment_stmt->execute();

    $_SESSION['success_message'] = "Book marked as returned successfully!";
    header("Location: manage_borrowed_books.php");
    exit;
}

// Initialize the search and filter variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['status']) ? $_GET['status'] : '';
$section = 'borrowed-books'; // Static section value

$query = "SELECT bb.borrow_id, bb.user_id,bb.full_name, bb.book_id, bb.borrow_date, bb.return_date, bb.expected_return_date, bb.status, u.email, b.title
          FROM borrowed_books bb
          JOIN users u ON bb.user_id = u.id
          LEFT JOIN books b ON bb.book_id = b.id";

if (!empty($search) || !empty($filter)) {
    $query .= " WHERE 1=1"; // Ensures we can append conditions safely

    if (!empty($search)) {
        $query .= " AND (u.email LIKE ? OR b.title LIKE ?)";
    }
    
    if (!empty($filter)) {
        $query .= " AND bb.status = ?";
    }
}

// **Fix: Properly update overdue books and fines without repeated increments**
$updateOverdueQuery = "UPDATE borrowed_books 
                        SET status = 'Overdue' 
                        WHERE expected_return_date < CURDATE() 
                        AND return_date IS NULL 
                        AND status != 'Overdue'";
$conn->query($updateOverdueQuery);

// **Fix: Calculate fine dynamically instead of adding ₱20 repeatedly**
$updateOverdueFineQuery = "UPDATE borrowed_books 
                           SET overdue_fine = DATEDIFF(CURDATE(), expected_return_date) * ?
                           WHERE expected_return_date < CURDATE() 
                           AND return_date IS NULL";

$stmt = $conn->prepare($updateOverdueFineQuery);
$stmt->bind_param("d", $fine_per_day);
$stmt->execute();



// Prepare the query
$stmt = $conn->prepare($query);

if (!empty($search) && !empty($filter)) {
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $filter);
} elseif (!empty($search)) {
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
} elseif (!empty($filter)) {
    $stmt->bind_param("s", $filter);
}

$stmt->execute();
$result = $stmt->get_result();

// Helper function to calculate overdue balances
function calculateOverdueBalance($expected_return_date, $return_date) {
    global $fine_per_day;
    
    $expected_date = strtotime($expected_return_date);
    $actual_date = strtotime($return_date);
    
    if ($actual_date > $expected_date) {
        $overdue_days = ceil(($actual_date - $expected_date) / (60 * 60 * 24));
        return max($overdue_days * $fine_per_day, 0); // Ensure the fine is not negative
    }
    return 0;
}

?>