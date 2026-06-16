<?php
/**
 * Shared Helper Functions
 */

// Sanitize output to prevent XSS
function sanitize($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Redirect to a URL
function redirect($url) {
    header("Location: $url");
    exit;
}

// Get a human-readable time difference
function get_time_ago($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    }
    if ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    }
    if ($diff->d > 0) {
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    }
    if ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    }
    if ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    }
    return 'Just now';
}

// Count total votes for a poll
function get_vote_count($pdo, $poll_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE poll_id = ?");
    $stmt->execute([$poll_id]);
    return (int) $stmt->fetchColumn();
}

// Check if a user has already voted on a poll
function has_user_voted($pdo, $poll_id, $user_id) {
    if ($user_id === null) {
        return false;
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE poll_id = ? AND user_id = ?");
    $stmt->execute([$poll_id, $user_id]);
    return (int) $stmt->fetchColumn() > 0;
}

// Get all options for a poll
function get_poll_options($pdo, $poll_id) {
    $stmt = $pdo->prepare("SELECT * FROM poll_options WHERE poll_id = ? ORDER BY id ASC");
    $stmt->execute([$poll_id]);
    return $stmt->fetchAll();
}

// Count votes for a specific option
function get_option_vote_count($pdo, $option_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vote_answers WHERE option_id = ?");
    $stmt->execute([$option_id]);
    return (int) $stmt->fetchColumn();
}

// Get text answers for a text poll
function get_text_answers($pdo, $poll_id) {
    $stmt = $pdo->prepare("
        SELECT va.text_answer, v.created_at
        FROM vote_answers va
        JOIN votes v ON va.vote_id = v.id
        WHERE v.poll_id = ? AND va.text_answer IS NOT NULL
        ORDER BY v.created_at DESC
    ");
    $stmt->execute([$poll_id]);
    return $stmt->fetchAll();
}

// Get poll creator username
function get_poll_creator($pdo, $creator_id) {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$creator_id]);
    $row = $stmt->fetch();
    return $row ? $row['username'] : 'Unknown';
}
?>
