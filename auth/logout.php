<?php
session_start();
$_SESSION = [];
session_destroy();

header('Location: /book-review/index.php');
exit();
?>
