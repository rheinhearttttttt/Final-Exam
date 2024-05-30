<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'instagram_clone');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = [];
$followers_count = 0;
$following_count = 0;
$posts = [];
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;








if ($_SERVER['REQUEST_METHOD'] == 'GET' && $user_id !== null) {
    $user_stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $user_stmt->bind_param('i', $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();

        $followers_stmt = $conn->prepare("SELECT COUNT(*) AS follower_count FROM follows WHERE followed_id = ?");
        $followers_stmt->bind_param('i', $user_id);
        $followers_stmt->execute();
        $followers_result = $followers_stmt->get_result();
        if ($followers_result->num_rows > 0) {
            $followers_count = $followers_result->fetch_assoc()['follower_count'];
        }

        $following_stmt = $conn->prepare("SELECT COUNT(*) AS following_count FROM follows WHERE follower_id = ?");
        $following_stmt->bind_param('i', $user_id);
        $following_stmt->execute();
        $following_result = $following_stmt->get_result();
        if ($following_result->num_rows > 0) {
            $following_count = $following_result->fetch_assoc()['following_count'];
        }

        $posts_stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
        $posts_stmt->bind_param('i', $user_id);
        $posts_stmt->execute();
        $posts_result = $posts_stmt->get_result();
        if ($posts_result->num_rows > 0) {
            $posts = $posts_result->fetch_all(MYSQLI_ASSOC);
        }
    } else {
        echo "User not found.";
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $user_id = intval($_POST['user_id']);

    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $file_info = pathinfo($file['name']);
        $file_extension = strtolower($file_info['extension']);

        if (in_array($file_extension, $allowed_types)) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . uniqid() . '.' . $file_extension;

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $update_stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $update_stmt->bind_param('si', $target_file, $user_id);

                if ($update_stmt->execute()) {
                    echo "Profile picture uploaded successfully.";
                } else {
                    unlink($target_file);
                    echo "Error updating profile picture.";
                }
            } else {
                echo "Error uploading file.";
            }
        } else {
            echo "Invalid file type. Allowed types: jpg, jpeg, png, gif";
        }
    } else {
        $upload_errors = array(
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
        );

        echo "Error uploading file: " . ($upload_errors[$file['error']] ?? "Unknown error");
    }
} else {
    echo "User ID not provided.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($user['username'] ?? ''); ?>'s Profile</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #fff;
        }
        .profile-header, .profile-info {
            background-color: #1e1e1e;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        .profile-picture {
            border-radius: 50%;
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right: 15px;
        }
        .post {
            background-color: #1e1e1e;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .post img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        .link-light {
            color: #bbb;
        }
        .link-light:hover {
            color: #fff;
        }
    </style>
</head>
<body>
    
<div class="container mt-5">
    <div class="profile-header d-flex align-items-center">
        <?php if (!empty($user['profile_picture'])): ?>
            <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="profile-picture">
        <?php else: ?>
            <img src="default_profile_picture.jpg" alt="Default Profile Picture" class="profile-picture">
        <?php endif; ?>
        <h1><?php echo htmlspecialchars($user['username'] ?? ''); ?>'s Profile</h1>
    </div>

    <div class="profile-info">
        <div class="row">
            <div class="col-md-4">
                <p class="mb-0">Followers: <?php echo $followers_count; ?></p>
            </div>
            <div class="col-md-4">
                <p class="mb-0">Following: <?php echo $following_count; ?></p>
            </div>
            <div class="col-md-4">
                <p class="mb-0">Posts: <?php echo count($posts); ?></p>
            </div>
        </div>
    </div>

    <!-- Profile Picture Upload Form -->
    <?php if ($user_id === $_SESSION['user_id']): ?>
        <div class="mb-3">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?user_id=<?php echo htmlspecialchars($user_id); ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                <div class="form-group">
                    <label for="profile_picture" class="text-muted">Change Profile Picture</label>
                    <input type="file" class="form-control-file" name="profile_picture" id="profile_picture" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </form>
        </div>
    <?php endif; ?>

        <!-- Posts Section -->
        <h2>Posts</h2>
        <div>
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <p><?php echo htmlspecialchars($post['caption']); ?></p>
                        <p><small>Posted on: <?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($post['created_at']))); ?></small></p>
                        <?php if (!empty($post['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="Post Image">
                        <?php endif; ?>
                        <?php
                        $post_id = $post['id'];
                        $comment_stmt = $conn->prepare("SELECT comments.comment, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ?");
                        $comment_stmt->bind_param('i', $post_id);
                        $comment_stmt->execute();
                        $comment_result = $comment_stmt->get_result();
                        ?>
                        <div class="mt-3">
                            <?php if ($comment_result->num_rows > 0): ?>
                                <h5>Comments</h5>
                                <ul class="list-unstyled">
                                    <?php while ($comment = $comment_result->fetch_assoc()): ?>
                                        <li class="media mb-2">
                                            <div class="media-body">
                                                <h6 class="mt-0 mb-1"><?php echo htmlspecialchars($comment['username']); ?></h6>
                                                <?php echo htmlspecialchars($comment['comment']); ?>
                                            </div>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p>No comments yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No posts available.</p>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
