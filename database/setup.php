<?php
/**
 * Database Setup Script
 * Run once: http://localhost/Voting-System/database/setup.php
 * Creates the votehub database and all tables.
 */

$host = 'localhost';
$user = 'root';
$pass = '';

echo "<h2>Voting System — Database Setup</h2>";
echo "<hr>";

// Connect to MySQL server (no database selected yet)
try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>Connected to MySQL server.</p>";
} catch (PDOException $e) {
    die("<p style='color:red;'>Connection failed: " . $e->getMessage() . "</p>");
}

// Create database
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS votehub CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "<p>Database 'votehub' created or already exists.</p>";
} catch (PDOException $e) {
    die("<p style='color:red;'>Failed to create database: " . $e->getMessage() . "</p>");
}

// Switch to votehub database
$pdo->exec("USE votehub");

// Create users table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
    ");
    echo "<p>Table 'users' created.</p>";
} catch (PDOException $e) {
    die("<p style='color:red;'>Failed to create users table: " . $e->getMessage() . "</p>");
}

// Create polls table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS polls (
            id INT AUTO_INCREMENT PRIMARY KEY,
            creator_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            image_path VARCHAR(255),
            poll_type ENUM('single','multiple','text') NOT NULL DEFAULT 'single',
            access_type ENUM('public','account','code') NOT NULL DEFAULT 'public',
            access_code VARCHAR(50),
            show_in_feed TINYINT(1) NOT NULL DEFAULT 0,
            anonymous_mode TINYINT(1) NOT NULL DEFAULT 0,
            results_visibility ENUM('immediate','after_vote') NOT NULL DEFAULT 'immediate',
            vote_limit INT DEFAULT NULL,
            total_views INT NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "<p>Table 'polls' created.</p>";
} catch (PDOException $e) {
    die("<p style='color:red;'>Failed to create polls table: " . $e->getMessage() . "</p>");
}

// Create poll_options table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS poll_options (
            id INT AUTO_INCREMENT PRIMARY KEY,
            poll_id INT NOT NULL,
            option_text VARCHAR(255) NOT NULL,
            image_path VARCHAR(255),
            FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "<p>Table 'poll_options' created.</p>";
} catch (PDOException $e) {
    die("<p style='color:red;'>Failed to create poll_options table: " . $e->getMessage() . "</p>");
}

// Create votes table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS votes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            poll_id INT NOT NULL,
            user_id INT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB
    ");
    echo "<p>Table 'votes' created.</p>";
} catch (PDOException $e) {
    die("<p style='color:red;'>Failed to create votes table: " . $e->getMessage() . "</p>");
}

// Create vote_answers table
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS vote_answers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            vote_id INT NOT NULL,
            option_id INT DEFAULT NULL,
            text_answer TEXT,
            FOREIGN KEY (vote_id) REFERENCES votes(id) ON DELETE CASCADE,
            FOREIGN KEY (option_id) REFERENCES poll_options(id) ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");
    echo "<p>Table 'vote_answers' created.</p>";
} catch (PDOException $e) {
    die("<p style='color:red;'>Failed to create vote_answers table: " . $e->getMessage() . "</p>");
}

echo "<hr>";
echo "<p style='color:green;'><strong>Setup complete!</strong> All tables created successfully.</p>";
echo "<p><a href='/Voting-System/index.php'>Go to Homepage</a></p>";
?>
