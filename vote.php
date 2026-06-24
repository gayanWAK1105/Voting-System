<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';



// Form එකකින් POST ක්‍රමයට ආවේ නැත්නම් ආපහු හරවා යවන්න
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$poll_id = isset($_POST['poll_id']) ? (int) $_POST['poll_id'] : 0;

if ($poll_id <= 0) {
    redirect('index.php');
}

// 1. ආරක්ෂිතව දත්ත සකසා ගැනීම සහ Poll එකේ තොරතුරු ලබා ගැනීම
$c_poll_id = mysqli_real_escape_string($conn, $poll_id);
$query = "SELECT * FROM polls WHERE id = '$c_poll_id'";
$result = mysqli_query($conn, $query);
$poll = mysqli_fetch_assoc($result);

if (!$poll) {
    redirect('index.php');
}

$user_id = get_current_user_id();

// ගිණුමක් අවශ්‍ය නම් (Account required) පරිශීලකයා ලොග් වී ඇත්දැයි බැලීම
if ($poll['access_type'] === 'account' && !is_logged_in()) {
    redirect('login.php');
}

// පරිශීලකයා දැනටමත් ඡන්දය ප්‍රකාශ කර ඇත්දැයි බැලීම (අලුත් $conn එක සමඟ)
if ($user_id !== null && has_user_voted($conn, $poll_id, $user_id)) {
    $_SESSION['vote_success'] = 'You have already voted on this poll.';
    redirect('poll.php?id=' . $poll_id);
}

// ඡන්ද සීමාව ඉක්මවා ඇත්දැයි බැලීම
$vote_count = get_vote_count($conn, $poll_id);
if ($poll['vote_limit'] !== null && $vote_count >= $poll['vote_limit']) {
    $_SESSION['vote_success'] = 'This poll has reached its maximum number of responses.';
    redirect('poll.php?id=' . $poll_id);
}

// 2. ප්‍රධාන ඡන්ද වාර්තාව ඇතුළත් කිරීම (Insert vote record)
$c_user_id = $user_id !== null ? (int)$user_id : "NULL";
$insert_vote = "INSERT INTO votes (poll_id, user_id) VALUES ('$c_poll_id', " . ($user_id !== null ? "'$c_user_id'" : "NULL") . ")";
mysqli_query($conn, $insert_query ?? $insert_vote);

// අලුතින්ම ඇතුළත් වූ Vote ID එක ලබා ගැනීම (lastInsertId වෙනුවට සරල ක්‍රමය)
$vote_id = mysqli_insert_id($conn);

// 3. ඡන්දයේ වර්ගය අනුව පිළිතුරු ඇතුළත් කිරීම (Insert vote answers)
if ($poll['poll_type'] === 'single') {
    $option_id = isset($_POST['option_id']) ? (int) $_POST['option_id'] : 0;
    if ($option_id > 0) {
        $c_option_id = mysqli_real_escape_string($conn, $option_id);
        $insert_ans = "INSERT INTO vote_answers (vote_id, option_id) VALUES ('$vote_id', '$c_option_id')";
        mysqli_query($conn, $insert_ans);
    }

} elseif ($poll['poll_type'] === 'multiple') {
    $option_ids = isset($_POST['option_ids']) ? $_POST['option_ids'] : [];
    foreach ($option_ids as $option_id) {
        $option_id = (int) $option_id;
        if ($option_id > 0) {
            $c_option_id = mysqli_real_escape_string($conn, $option_id);
            $insert_ans = "INSERT INTO vote_answers (vote_id, option_id) VALUES ('$vote_id', '$c_option_id')";
            mysqli_query($conn, $insert_ans);
        }
    }

} elseif ($poll['poll_type'] === 'text') {
    $text_answer = trim($_POST['text_answer'] ?? '');
    if ($text_answer !== '') {
        $c_text_answer = mysqli_real_escape_string($conn, $text_answer);
        $insert_ans = "INSERT INTO vote_answers (vote_id, text_answer) VALUES ('$vote_id', '$c_text_answer')";
        mysqli_query($conn, $insert_ans);
    }
}

$_SESSION['vote_success'] = 'Your vote has been recorded. Thank you!';
redirect('poll.php?id=' . $poll_id);
?>