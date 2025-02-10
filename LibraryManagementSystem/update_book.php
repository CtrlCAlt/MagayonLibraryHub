<?php
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $published_date = $_POST['published_date'];
    $genre = $_POST['genre'];
    $quantity = $_POST['quantity'];
    $status = $_POST['status'];

    // Check if all required fields have values
    if (!empty($book_id) && !empty($title) && !empty($author) && !empty($published_date) && !empty($genre) && !empty($quantity) && !empty($status)) {
        $sql = "UPDATE books SET 
                title = ?, 
                author = ?, 
                published_date = ?, 
                genre = ?, 
                quantity = ?, 
                status = ? 
                WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssisi", $title, $author, $published_date, $genre, $quantity, $status, $book_id);

        if ($stmt->execute()) {
            // Success
            header("Location: admin.php?section=books&success=update");
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        echo "All fields are required.";
    }
}
