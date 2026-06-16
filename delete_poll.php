<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

require_login();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('my_polls.php');
}

$poll_id = isset($_POST['poll_id']) ? (int) $_POST['poll_id'] : 0;
$user_id = get_current_user_id();

if ($poll_id <= 0) {
    redirect('my_polls.php');
}

// Verify ownership
$stmt = $pdo->prepare("SELECT creator_id FROM polls WHERE id = ?");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch();

if (!$poll || $poll['creator_id'] != $user_id) {
    $_SESSION['dashboard_message'] = 'You do not have permission to delete this poll.';
    redirect('my_polls.php');
}

// Delete poll (cascading deletes handle options, votes, vote_answers)
$stmt = $pdo->prepare("DELETE FROM polls WHERE id = ?");
$stmt->execute([$poll_id]);

$_SESSION['dashboard_message'] = 'Poll deleted successfully.';
redirect('my_polls.php');
?>
