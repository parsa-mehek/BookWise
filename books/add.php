<?php
include("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $description = $_POST['description'];

    $sql = "INSERT INTO books (title, author, description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $title, $author, $description);

    if ($stmt->execute()) {
        echo "Book added successfully!";
    } else {
        echo "Error!";
    }
}
?>

<form method="POST">
    <input type="text" name="title" placeholder="Book Title" required><br>
    <input type="text" name="author" placeholder="Author" required><br>
    <textarea name="description" placeholder="Description"></textarea><br>
    <button type="submit">Add Book</button>
</form>
