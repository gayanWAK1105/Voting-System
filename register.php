<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';



// if user is already logged in, redirect to index.php
if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$username = '';
$email = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
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
    } elseif (!preg_match('/[a-zA-Z]/', $password)) {
        $errors[] = 'Password must contain at least one letter.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[^a-zA-Z0-9\s]/', $password)) {
        $errors[] = 'Password must contain at least one special character.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    
    if (empty($errors)) {
        // protect against SQL injection
        $c_username = mysqli_real_escape_string($conn, $username);
        $c_email = mysqli_real_escape_string($conn, $email);

        // check username availability
        $query_user = "SELECT id FROM users WHERE username = '$c_username'";
        $result_user = mysqli_query($conn, $query_user);
        if (mysqli_num_rows($result_user) > 0) {
            $errors[] = 'Username is already taken.';
        }

        // check whether Email is already registered
        $query_email = "SELECT id FROM users WHERE email = '$c_email'";
        $result_email = mysqli_query($conn, $query_email);
        if (mysqli_num_rows($result_email) > 0) {
            $errors[] = 'Email is already registered.';
        }
    }

    // if there are no errors, insert the new user into the database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_query = "INSERT INTO users (username, email, password) VALUES ('$c_username', '$c_email', '$hashed_password')";
        mysqli_query($conn, $insert_query);

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
        <p class="text-muted">Join Votify to create and vote on polls.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo sanitize($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div id="js-errors" class="alert alert-error" style="display:none;"></div>

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
                <span class="help-text">Minimum 6 chars, 1 uppercase letter, and 1 special character.</span>
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

<script src="assets/js/register-validation.js"></script>

<?php require_once 'includes/footer.php'; ?>