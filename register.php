<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

start_session_if_needed();

// If already logged in, go to homepage
if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$username = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'Username must be between 3 and 50 characters.';
    }

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check for duplicate username or email
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Username is already taken.';
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Email is already registered.';
        }
    }

    // Insert user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password]);

        $_SESSION['success_message'] = 'Account created successfully. Please login.';
        redirect('login.php');
    }
}

$page_title = 'Register';
$page_css = 'auth.css';
require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box card">
        <h1>Create Account</h1>
        <p class="text-muted">Join VoteHub to create and vote on polls.</p>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
            <p><?php echo sanitize($error); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="register.php" id="registerForm">

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo sanitize($username); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo sanitize($email); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <span class="help-text">Minimum 6 characters.</span>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Register</button>

        </form>

        <p class="text-center mt-20">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    var username = document.getElementById('username').value.trim();
    var email = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value;
    var confirm = document.getElementById('confirm_password').value;
    var errors = [];

    if (username.length < 3) {
        errors.push('Username must be at least 3 characters.');
    }
    if (email.indexOf('@') === -1) {
        errors.push('Please enter a valid email.');
    }
    if (password.length < 6) {
        errors.push('Password must be at least 6 characters.');
    }
    if (password !== confirm) {
        errors.push('Passwords do not match.');
    }

    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join('\n'));
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
