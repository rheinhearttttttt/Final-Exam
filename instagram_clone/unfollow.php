<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'instagram_clone');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $follower_id = $_SESSION['user_id'];
    $followed_id = $_POST['followed_id'];

    $sql = "DELETE FROM follows WHERE follower_id = $follower_id AND followed_id = $followed_id";
    if ($conn->query($sql) === TRUE) {
        header("Location: home.php"); // Redirect to home page or any appropriate page after unfollowing
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>
