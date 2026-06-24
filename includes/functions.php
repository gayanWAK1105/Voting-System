<?php
/**
 * Shared Helper Functions (Simple MySQLi Method)
 */

function sanitize($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function get_time_ago($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

function get_vote_count($conn, $poll_id) {
    $poll_id = mysqli_real_escape_string($conn, $poll_id);
    $query = "SELECT COUNT(*) AS total FROM votes WHERE poll_id = '$poll_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return (int) $row['total'];
}

function has_user_voted($conn, $poll_id, $user_id) {
    if ($user_id === null) {
        return false;
    }
    $poll_id = mysqli_real_escape_string($conn, $poll_id);
    $user_id = mysqli_real_escape_string($conn, $user_id);
    
    $query = "SELECT COUNT(*) AS total FROM votes WHERE poll_id = '$poll_id' AND user_id = '$user_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return (int) $row['total'] > 0;
}

function get_poll_options($conn, $poll_id) {
    $poll_id = mysqli_real_escape_string($conn, $poll_id);
    $query = "SELECT * FROM poll_options WHERE poll_id = '$poll_id' ORDER BY id ASC";
    $result = mysqli_query($conn, $query);
    
    $options = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $options[] = $row;
    }
    return $options;
}

function get_option_vote_count($conn, $option_id) {
    $option_id = mysqli_real_escape_string($conn, $option_id);
    $query = "SELECT COUNT(*) AS total FROM vote_answers WHERE option_id = '$option_id'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return (int) $row['total'];
}

function get_text_answers($conn, $poll_id) {
    $poll_id = mysqli_real_escape_string($conn, $poll_id);
    $query = "SELECT va.text_answer, v.created_at 
              FROM vote_answers va 
              JOIN votes v ON va.vote_id = v.id 
              WHERE v.poll_id = '$poll_id' AND va.text_answer IS NOT NULL 
              ORDER BY v.created_at DESC";
    $result = mysqli_query($conn, $query);
    
    $answers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $answers[] = $row;
    }
    return $answers;
}

function get_poll_creator($conn, $creator_id) {
    $creator_id = mysqli_real_escape_string($conn, $creator_id);
    $query = "SELECT username FROM users WHERE id = '$creator_id'";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        return $row['username'];
    }
    return 'Unknown';
}
?>