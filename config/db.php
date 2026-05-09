<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "Book_Wise";

$conn = new mysqli($host, $user, $password, $database);

// connection check
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
