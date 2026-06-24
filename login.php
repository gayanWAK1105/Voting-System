<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';


if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$email = '';
$success_message = '';


if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        
        $c_email = mysqli_real_escape_string($conn, $email);

    
        $query = "SELECT * FROM users WHERE email = '$c_email'";
        $result = mysqli_query($conn, $query);
        $user = mysqli_fetch_assoc($result);

        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            redirect('index.php');
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

$page_title = 'Login';
$page_css = 'auth.css';
require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-box card">
        <h1>Login</h1>
        <p class="text-muted">Sign in to your VoteHub account.</p>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <p><?php echo sanitize($success_message); ?></p>
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
            <p><?php echo sanitize($error); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" id="loginForm">

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo sanitize($email); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>

        </form>

        <p class="text-center mt-20">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>

<script>

document.getElementById('loginForm').addEventListener('submit', function(e) {
    var email = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value;
    var errors = [];

    if (email === '') {
        errors.push('Email is required.');
    }
    if (password === '') {
        errors.push('Password is required.');
    }

    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join('\n'));
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>