<?php
/**
 * Authentication Helpers
 * Session management and login checks.
 */

function start_session_if_needed() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function is_logged_in() {
    start_session_if_needed();
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit;
    }
}

function get_current_user_id() {
    start_session_if_needed();
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function get_current_username() {
    start_session_if_needed();
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}
?>
