<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Enable detailed error reporting
$conn = new mysqli('localhost', 'root', '', 'instagram_clone');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Search logic
$search_result = [];
if (isset($_GET['username'])) {
    $search_username = $conn->real_escape_string($_GET['username']);
    $search_sql = "SELECT id, username FROM users WHERE username LIKE ?";
    $stmt = $conn->prepare($search_sql);
    $search_username_like = '%' . $search_username . '%';
    $stmt->bind_param("s", $search_username_like);
    $stmt->execute();
    $search_result = $stmt->get_result();
}

function isFollowing($follower_id, $followed_id) {
    global $conn;
    $check_follow_sql = "SELECT * FROM follows WHERE follower_id = ? AND followed_id = ?";
    $stmt = $conn->prepare($check_follow_sql);
    $stmt->bind_param("ii", $follower_id, $followed_id);
    $stmt->execute();
    $check_follow_result = $stmt->get_result();
    return $check_follow_result->num_rows > 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Users</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #fff;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; /* Fallback font */
        }
        .container {
            margin-top: 50px;
        }
        .btn-primary {
            background-color: #6200ea;
            border-color: #6200ea;
        }
        .btn-primary:hover {
            background-color: #3700b3;
            border-color: #3700b3;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        .list-group-item {
            background-color: #1e1e1e;
            border-color: #333;
            color: #fff;
        }
        .list-group-item a {
            color: #bb86fc;
        }
        .list-group-item a:hover {
            color: #fff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Search Users</h1>
        <form action="search.php" method="GET">
            <div class="form-group">
                <input type="text" name="username" class="form-control" placeholder="Search for a user">
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>

        <?php if ($search_result && $search_result->num_rows > 0): ?>
            <h2 class="mt-5">Search Results</h2>
            <ul class="list-group" id="search-results">
                <?php while ($row = $search_result->fetch_assoc()): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="profile.php?user_id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['username']); ?></a>
                        <button class="btn <?php echo isFollowing($_SESSION['user_id'], $row['id']) ? 'btn-danger' : 'btn-success'; ?>" data-followed-id="<?php echo $row['id']; ?>">
                            <?php echo isFollowing($_SESSION['user_id'], $row['id']) ? 'Unfollow' : 'Follow'; ?>
                        </button>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php elseif (isset($_GET['username'])): ?>
            <p class="mt-5">No users found.</p>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        function updateFollowButton(button, action) {
            if (action === 'follow') {
                button.text('Unfollow');
                button.removeClass('btn-success').addClass('btn-danger');
            } else {
                button.text('Follow');
                button.removeClass('btn-danger').addClass('btn-success');
            }
        }

        $(document).ready(function() {
            $('#search-results').on('click', 'button', function() {
                var button = $(this);
                var followedId = button.data('followed-id');
                var action = button.hasClass('btn-success') ? 'follow' : 'unfollow';

                $.ajax({
                    url: 'follow_unfollow.php',
                    method: 'POST',
                    data: { followed_id: followedId, action: action },
                    success: function(response) {
                        if (response === 'success') {
                            updateFollowButton(button, action);
                        } else {
                            alert('An error occurred. Please try again.');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>