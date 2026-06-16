<?php
require_once 'includes/auth.php';

start_session_if_needed();
session_destroy();
header("Location: login.php");
exit;
?>
