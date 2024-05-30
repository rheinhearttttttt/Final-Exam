<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo 'error';
    exit();
}

if (!isset($_POST['followed_id']) || !isset($_POST['action'])) {
    echo 'error';
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable detailed error reporting
$conn = new mysqli('localhost', 'root', '', 'instagram_clone');
if ($conn->connect_error) {
    echo 'error';
    exit();
}

$follower_id = $_SESSION['user_id'];
$followed_id = $_POST['followed_id'];
$action = $_POST['action'];

if ($action === 'follow') {
    $follow_sql = "INSERT INTO follows (follower_id, followed_id) VALUES (?, ?)";
    $stmt = $conn->prepare($follow_sql);
    $stmt->bind_param("ii", $follower_id, $followed_id);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
} else if ($action === 'unfollow') {
    $unfollow_sql = "DELETE FROM follows WHERE follower_id = ? AND followed_id = ?";
    $stmt = $conn->prepare($unfollow_sql);
    $stmt->bind_param("ii", $follower_id, $followed_id);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}

$conn->close();
?>
