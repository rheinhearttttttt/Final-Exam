<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'instagram_clone');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    $comment = $_POST['comment'];

    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $post_id, $user_id, $comment);

    if ($stmt->execute()) {
        header("Location: home.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
