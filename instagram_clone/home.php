<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'instagram_clone');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $profile_sql = "SELECT username, profile_picture FROM users WHERE id = $user_id";
    $profile_result = $conn->query($profile_sql);
    if ($profile_result && $profile_result->num_rows > 0) {
        $profile_row = $profile_result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Yezier</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #fff;
        }
        .card {
            background-color: #1e1e1e;
            border: 1px solid #333;
            margin-bottom: 20px; /* Reduce margin to make posts slimmer */
        }
        .card-header, .card-footer {
            background-color: #2c2c2c;
            padding: 0.5rem 1rem; /* Adjust padding for header and footer */
        }
        .form-control {
            background-color: #333;
            border: 1px solid #444;
            color: #fff;
        }
        .form-control:focus {
            background-color: #444;
            border-color: #555;
            color: #fff;
        }
        .btn-primary {
            background-color: #6200ea;
            border-color: #6200ea;
        }
        .btn-primary:hover {
            background-color: #3700b3;
            border-color: #3700b3;
        }
        .link-light {
            color: #bb86fc;
        }
        .link-light:hover {
            color: #fff;
        }
        .profile-picture {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 10px;
        }
        .default-picture {
            background-color: #444;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: inline-block;
            margin-right: 10px;
        }
        .search-bar {
            width: 100%; /* Adjust width to fit container */
        }
        .like-button, .heart-button {
            cursor: pointer;
            color: #fff;
            margin-right: 10px;
        }
        .like-button.liked, .heart-button.liked {
            color: #e0245e;
        }
        .comment-input {
            border-radius: 20px;
            border: 1px solid #ced4da;
            padding: 10px;
            margin-top: 10px;
        }
        .comment-input:focus {
            border-color: #6200ea;
            box-shadow: 0 0 5px rgba(98, 0, 234, 0.5);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="#">Yezier</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="profile.php?user_id=<?php echo $user_id; ?>">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Log Out</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
    <?php if (isset($profile_row)): ?>
        <div class="card mb-4">
            <div class="card-body text-center">
                <?php if (!empty($profile_row['profile_picture']) && file_exists($profile_row['profile_picture'])): ?>
                    <img src="<?= htmlspecialchars($profile_row['profile_picture']) ?>" alt="Profile Picture" class="profile-picture rounded-circle">
                <?php else: ?>
                    <div class="default-picture"></div>
                <?php endif; ?>
                <h2><?= htmlspecialchars($profile_row['username']) ?></h2>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 mb-3">
            <form action="search.php" method="GET" class="form-inline my-2 my-lg-0">
                <input class="form-control mr-sm-2 search-bar" type="search" name="username" placeholder="Search users" aria-label="Search" required>
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit">Search</button>
            </form>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card bg-dark text-light">
                <div class="card-body">
                    <form action="upload.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="image" class="text-muted">Upload a post</label>
                            <input type="file" class="form-control-file" name="image" id="image" accept="image/*" required>
                        </div>
                        <div class="form-group">
                            <label for="caption" class="text-muted">Add caption</label>
                            <input type="text" class="form-control" name="caption" id="caption">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Post</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <h2 class="text-center">Posts</h2>
    <?php
$sql = "SELECT posts.id, posts.image_path, posts.caption, posts.user_id, posts.created_at, users.username, users.profile_picture,
        (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count,
        (SELECT COUNT(*) FROM likes WHERE post_id = posts.id AND user_id = $user_id) AS user_liked
        FROM posts
        JOIN users ON posts.user_id = users.id
        ORDER BY posts.created_at DESC";



?>
<?php
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='card mb-3'>";


        
        echo "<div class='card-header d-flex align-items-center bg-dark text-light'>";
        if (!empty($row['profile_picture']) && file_exists($row['profile_picture'])) {
            echo "<img src='" . htmlspecialchars($row['profile_picture']) . "' alt='Profile Picture' class='profile-picture rounded-circle'>";
        } else {
            echo "<div class='default-picture'></div>";
        }
        echo "<strong><a href='profile.php?user_id=" . $row['user_id'] . "' class='link-light'>" . htmlspecialchars($row['username']). "</a></strong>";
        echo "</div>";
        echo "<div class='card-body'>";
        if (!empty($row['image_path']) && file_exists($row['image_path'])) {
            echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='Post image' class='img-fluid'>";
        } else {
            echo "<p>Image not found.</p>";
        }
        echo "<p class='mt-3'>" . htmlspecialchars($row['caption']) . "</p>";
        echo "</div>";
        echo "<div class='card-footer bg-dark text-light'>";
        echo "<p>Posted on: " . date("F j, Y, g:i a", strtotime($row['created_at'])) . "</p>";
        echo "<div class='d-flex justify-content-between align-items-center'>";
        echo "<span class='like-button " . ($row['user_liked'] ? 'liked' : '') . "' data-post-id='" . $row['id'] . "'>";
        echo "<i class='fa fa-heart'></i> <span class='like-count'>" . $row['like_count'] . "</span>";
        echo "</span>";

        // Heart button for liking
        echo "<span class='heart-button " . ($row['user_liked'] ? 'liked' : '') . "' data-post-id='" . $row['id'] . "'>";
        echo "<i class='fa fa-heart'></i>";
        echo "</span>";

        // Like button
        echo "<button class='btn btn-primary like-button' data-post-id='" . $row['id'] . "'><i class='far fa-thumbs-up'></i> Like</button>";

        echo "</div>"; // Close the d-flex container


        // Output comments and comment form
        $post_id = $row['id'];
        $comment_sql = "SELECT comments.comment, users.username
                        FROM comments
                        JOIN users ON comments.user_id = users.id
                        WHERE comments.post_id = $post_id";
        $comment_result = $conn->query($comment_sql);
        if ($comment_result && $comment_result->num_rows > 0) {
            echo "<div class='comments'>";
            echo "<h5>Comments</h5>";
            while ($comment_row = $comment_result->fetch_assoc()) {
                echo "<div class='comment mb-2'>";
                echo "<p><strong>" . htmlspecialchars($comment_row['username']) . ":</strong> " . htmlspecialchars($comment_row['comment']) . "</p>";
                echo "</div>";
            }
            echo "</div>";
        }

        echo "<form action='comment.php' method='POST'>";
        echo "<input type='hidden' name='post_id' value='" . $row['id'] . "'>";
        echo "<div class='form-group'>";
        echo "<input type='text' name='comment' placeholder='Add a comment' class='form-control comment-input'>";
        echo "</div>";
        echo "<button type='submit' class='btn btn-primary btn-block'>Comment</button>";
        echo "</form>";
        echo "</div>"; // Close card-footer
        echo "</div>"; // Close card
    }
}
?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        // Function to handle like button click
        $('.like-button').click(function() {
            var button = $(this);
            var postId = button.data('post-id');
            var liked = button.hasClass('liked');

            // Disable the button to prevent multiple clicks
            button.prop('disabled', true);

            // Send AJAX request to update like status
            $.ajax({
                url: 'like.php',
                method: 'POST',
                data: { postId: postId, liked: !liked },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update like count and button appearance
                        $('.like-count[data-post-id="' + postId + '"]').text(response.like_count);
                        button.toggleClass('liked');
                    } else {
                        console.error(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error(error);
                },
                complete: function() {
                    // Re-enable the button after AJAX request is complete
                    button.prop('disabled', false);
                }
            });
        });
    });
</script>


</body>
</html>
<?php
$conn->close();
?>
