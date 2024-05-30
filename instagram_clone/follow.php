<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['followed_id'])) {
    $conn = new mysqli('localhost', 'root', '', 'instagram_clone');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $follower_id = $_SESSION['user_id'];
    $followed_id = $_POST['followed_id'];

    $check_follow_sql = "SELECT * FROM follows WHERE follower_id = $follower_id AND followed_id = $followed_id";
    $check_follow_result = $conn->query($check_follow_sql);

    if ($check_follow_result && $check_follow_result->num_rows > 0) {
        // User is already followed, so unfollow
        $unfollow_sql = "DELETE FROM follows WHERE follower_id = $follower_id AND followed_id = $followed_id";
        if ($conn->query($unfollow_sql) === TRUE) {
            // Unfollow successful
            header("Location: search.php"); // Redirect back to search results
            exit();
        } else {
            echo "Error unfollowing user: " . $conn->error;
        }
    } else {
        // User is not followed, so follow
        $follow_sql = "INSERT INTO follows (follower_id, followed_id) VALUES ($follower_id, $followed_id)";
        if ($conn->query($follow_sql) === TRUE) {
            // Follow successful
            header("Location: search.php"); // Redirect back to search results
            exit();
        } else {
            echo "Error following user: " . $conn->error;
        }
    }

    $conn->close();
} else {
    // Redirect to search page if accessed without proper form submission
    header("Location: search.php");
    exit();
}
?>
