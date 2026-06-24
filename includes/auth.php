<?php
// check if the session is already started, if not, start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// check if the user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// if the user is not logged in, redirect to login page
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

// take recent session user id
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// take recent session username
function get_current_username() {
    return $_SESSION['username'] ?? null;
}
?>