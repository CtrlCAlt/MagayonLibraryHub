<?php

include "connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"] ?? "";
    $author = $_POST["author"] ?? "";
    $isbn = $_POST["isbn"] ?? "";  // Fix: Prevent undefined index warning
    $published_date = $_POST["published_date"] ?? "";
    $publisher = $_POST["publisher"] ?? "";
    $genre = $_POST["genre"] ?? "";
    $quantity = $_POST["quantity"] ?? 0;
    $content = $_POST["content"] ?? "";
    $file_path = null; 
    $book_image_path = null; // New book image path

    $target_dir = __DIR__ . "/uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Handle book image upload (if any)
    if (isset($_FILES["book_image"]) && $_FILES["book_image"]["error"] == 0) {
        $image_name = basename($_FILES["book_image"]["name"]);
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $allowed_exts = ["jpg", "jpeg", "png"];

        if (in_array($image_ext, $allowed_exts)) {
            $unique_image_name = uniqid() . "_" . $image_name;
            $target_image = $target_dir . $unique_image_name;

            if (move_uploaded_file($_FILES["book_image"]["tmp_name"], $target_image)) {
                $book_image_path = "uploads/" . $unique_image_name; // Store the relative path
            } else {
                die("Error uploading book image. Please check file permissions.");
            }
        } else {
            die("Invalid image format. Only JPG, JPEG, and PNG are allowed.");
        }
    }

    $sql = "INSERT INTO books (title, author, isbn, published_date,publisher, genre, quantity, content, book_image)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssss", $title, $author, $isbn, $published_date,$publisher, $genre, $quantity, $content, $book_image_path);

    if ($stmt->execute()) {
        echo "New book added successfully.";
        header("Location: admin.php?section=books");
    } else {
        echo "SQL Error: " . $stmt->error;
    }
    $stmt->close();

}

$conn->close();

?>
