<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

start_session_if_needed();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$poll_id = isset($_POST['poll_id']) ? (int) $_POST['poll_id'] : 0;

if ($poll_id <= 0) {
    redirect('index.php');
}

// Fetch poll
$stmt = $pdo->prepare("SELECT * FROM polls WHERE id = ?");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch();

if (!$poll) {
    redirect('index.php');
}

$user_id = get_current_user_id();

// Check if login required
if ($poll['access_type'] === 'account' && !is_logged_in()) {
    redirect('login.php');
}

// Check if user already voted (for logged in users)
if ($user_id !== null && has_user_voted($pdo, $poll_id, $user_id)) {
    $_SESSION['vote_success'] = 'You have already voted on this poll.';
    redirect('poll.php?id=' . $poll_id);
}

// Check vote limit
$vote_count = get_vote_count($pdo, $poll_id);
if ($poll['vote_limit'] !== null && $vote_count >= $poll['vote_limit']) {
    $_SESSION['vote_success'] = 'This poll has reached its maximum number of responses.';
    redirect('poll.php?id=' . $poll_id);
}

// Insert vote record
$stmt = $pdo->prepare("INSERT INTO votes (poll_id, user_id) VALUES (?, ?)");
$stmt->execute([$poll_id, $user_id]);
$vote_id = $pdo->lastInsertId();

// Insert vote answers
if ($poll['poll_type'] === 'single') {
    $option_id = isset($_POST['option_id']) ? (int) $_POST['option_id'] : 0;
    if ($option_id > 0) {
        $stmt = $pdo->prepare("INSERT INTO vote_answers (vote_id, option_id) VALUES (?, ?)");
        $stmt->execute([$vote_id, $option_id]);
    }

} elseif ($poll['poll_type'] === 'multiple') {
    $option_ids = isset($_POST['option_ids']) ? $_POST['option_ids'] : [];
    $stmt = $pdo->prepare("INSERT INTO vote_answers (vote_id, option_id) VALUES (?, ?)");
    foreach ($option_ids as $option_id) {
        $option_id = (int) $option_id;
        if ($option_id > 0) {
            $stmt->execute([$vote_id, $option_id]);
        }
    }

} elseif ($poll['poll_type'] === 'text') {
    $text_answer = trim($_POST['text_answer'] ?? '');
    if ($text_answer !== '') {
        $stmt = $pdo->prepare("INSERT INTO vote_answers (vote_id, text_answer) VALUES (?, ?)");
        $stmt->execute([$vote_id, $text_answer]);
    }
}

$_SESSION['vote_success'] = 'Your vote has been recorded. Thank you!';
redirect('poll.php?id=' . $poll_id);
?>
