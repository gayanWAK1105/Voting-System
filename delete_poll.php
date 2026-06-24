<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';


require_login();


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('my_polls.php');
}

$poll_id = isset($_POST['poll_id']) ? (int) $_POST['poll_id'] : 0;
$user_id = get_current_user_id();

if ($poll_id <= 0) {
    redirect('my_polls.php');
}


$c_poll_id = mysqli_real_escape_string($conn, $poll_id);
$c_user_id = mysqli_real_escape_string($conn, $user_id);


$query = "SELECT creator_id FROM polls WHERE id = '$c_poll_id'";
$result = mysqli_query($conn, $query);
$poll = mysqli_fetch_assoc($result);

if (!$poll || $poll['creator_id'] != $c_user_id) {
    $_SESSION['dashboard_message'] = 'You do not have permission to delete this poll.';
    redirect('my_polls.php');
}

// delete the poll and its associated votes
$delete_query = "DELETE FROM polls WHERE id = '$c_poll_id'";
mysqli_query($conn, $delete_query);

$_SESSION['dashboard_message'] = 'Poll deleted successfully.';
redirect('my_polls.php');
?>