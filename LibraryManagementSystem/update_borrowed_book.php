<?php
include "connect.php"; // Database connection
session_start();

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and get the input data
    $borrow_id = intval($_POST['borrow_id']);
    $borrow_date = trim($_POST['borrow_date']); // Borrow date is not required if not updating
    $return_date = !empty($_POST['return_date']) ? trim($_POST['return_date']) : null;
    $status = trim($_POST['status']);
    $overdue_fine = isset($_POST['overdue_fine']) ? floatval($_POST['overdue_fine']) : 0.00;

    // Validate required fields
    if (empty($borrow_id) || empty($status)) {
        $_SESSION['error_message'] = "Borrow ID and Status are required.";
        header("Location: admin.php?section=borrowed-books&error=missing_fields");
        exit;
    }

    // Prepare the update query
    $update_query = "UPDATE borrowed_books 
                     SET return_date = ?, overdue_fine = ?, status = ? 
                     WHERE borrow_id = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($update_query)) {
        $stmt->bind_param("sdsi", $return_date, $overdue_fine, $status, $borrow_id);
        
        // Execute the query
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Borrowed book updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating borrowed book: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Database error: Unable to prepare statement.";
    }
}

// Redirect to the manage borrowed books page
header("Location: admin.php?section=borrowed-books");
exit;
?>
