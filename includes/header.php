<?php
/**
 * Shared Header (Simple Method)
 */


require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?php echo isset($page_title) ? sanitize($page_title) . ' - ' : ''; ?>VoteHub</title>
    
    <link rel="stylesheet" href="/Voting-System/assets/css/global.css">
    
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="/Voting-System/assets/css/<?php echo $page_css; ?>">
    <?php endif; ?>
</head>
<body>

<header>
    <div class="header-container">
        <div class="logo">
            <a href="/Voting-System/index.php">VoteHub</a>
        </div>
        <nav>
            <ul>
                <li><a href="/Voting-System/index.php">Home</a></li>
                
                <?php if (is_logged_in()): ?>
                    <li><a href="/Voting-System/create_poll.php">Create Poll</a></li>
                    <li><a href="/Voting-System/my_polls.php">My Polls</a></li>
                    <li class="nav-user">
                        <span>Hello, <?php echo sanitize(get_current_username()); ?></span>
                    </li>
                    <li><a href="/Voting-System/logout.php" class="nav-logout">Logout</a></li>
                
                <?php else: ?>
                    <li><a href="/Voting-System/login.php">Login</a></li>
                    <li><a href="/Voting-System/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main>