<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    // User not logged in
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if (!isset($_POST['postId']) || !isset($_POST['liked'])) {
    // Required data not provided
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_POST['postId'];
$liked = $_POST['liked'];

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'instagram_clone');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update the likes in the database
if ($liked) {
    // Unlike
    $sql = "DELETE FROM likes WHERE user_id = $user_id AND post_id = $post_id";
} else {
    // Like
    $sql = "INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)";
}

if ($conn->query($sql) === TRUE) {
    // Query executed successfully, retrieve updated like count
    $like_count_sql = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = $post_id";
    $like_count_result = $conn->query($like_count_sql);
    if ($like_count_result && $like_count_result->num_rows > 0) {
        $like_count_row = $like_count_result->fetch_assoc();
        $like_count = $like_count_row['like_count'];

        // Return updated like count
        echo json_encode(['success' => true, 'like_count' => $like_count]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to retrieve like count']);
    }
} else {
    // Error occurred while updating likes
    echo json_encode(['success' => false, 'message' => 'Error updating likes']);
}

$conn->close();
?>
