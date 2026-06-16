<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

start_session_if_needed();

// Get poll ID
$poll_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($poll_id <= 0) {
    redirect('index.php');
}

// Fetch poll
$stmt = $pdo->prepare("SELECT * FROM polls WHERE id = ?");
$stmt->execute([$poll_id]);
$poll = $stmt->fetch();

if (!$poll) {
    $page_title = 'Poll Not Found';
    $page_css = 'poll.css';
    require_once 'includes/header.php';
    echo '<div class="alert alert-error">Poll not found.</div>';
    echo '<p><a href="index.php">Back to Home</a></p>';
    require_once 'includes/footer.php';
    exit;
}

// Increment view count
$stmt = $pdo->prepare("UPDATE polls SET total_views = total_views + 1 WHERE id = ?");
$stmt->execute([$poll_id]);

// Check access: account required
if ($poll['access_type'] === 'account' && !is_logged_in()) {
    $page_title = 'Login Required';
    $page_css = 'poll.css';
    require_once 'includes/header.php';
    echo '<div class="card">';
    echo '<h2>Login Required</h2>';
    echo '<p>You must be logged in to view this poll.</p>';
    echo '<p class="mt-10"><a href="login.php" class="btn btn-primary">Login</a></p>';
    echo '</div>';
    require_once 'includes/footer.php';
    exit;
}

// Check access: code protected
$code_verified = false;
if ($poll['access_type'] === 'code') {
    // Check if code was submitted
    if (isset($_POST['access_code_submit'])) {
        $entered_code = trim($_POST['entered_code'] ?? '');
        if ($entered_code === $poll['access_code']) {
            $_SESSION['poll_code_' . $poll_id] = true;
            $code_verified = true;
        } else {
            $code_error = 'Incorrect code. Please try again.';
        }
    }

    // Check if already verified in session
    if (isset($_SESSION['poll_code_' . $poll_id])) {
        $code_verified = true;
    }

    if (!$code_verified) {
        $page_title = 'Enter Code';
        $page_css = 'poll.css';
        require_once 'includes/header.php';
        ?>
        <div class="card" style="max-width:400px; margin:0 auto;">
            <h2>Code Required</h2>
            <p class="text-muted mb-20">This poll requires an access code.</p>

            <?php if (isset($code_error)): ?>
            <div class="alert alert-error"><p><?php echo sanitize($code_error); ?></p></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="entered_code">Access Code</label>
                    <input type="text" id="entered_code" name="entered_code" required>
                </div>
                <input type="hidden" name="access_code_submit" value="1">
                <button type="submit" class="btn btn-primary">Enter</button>
            </form>
        </div>
        <?php
        require_once 'includes/footer.php';
        exit;
    }
}

// Get poll data
$creator_name = get_poll_creator($pdo, $poll['creator_id']);
$vote_count = get_vote_count($pdo, $poll_id);
$options = get_poll_options($pdo, $poll_id);
$user_id = get_current_user_id();
$user_voted = has_user_voted($pdo, $poll_id, $user_id);

// Check vote limit
$vote_limit_reached = false;
if ($poll['vote_limit'] !== null && $vote_count >= $poll['vote_limit']) {
    $vote_limit_reached = true;
}

// Determine if results should be shown
$show_results = false;
if ($poll['results_visibility'] === 'immediate') {
    $show_results = true;
} elseif ($poll['results_visibility'] === 'after_vote' && $user_voted) {
    $show_results = true;
}

// Check if current user is the poll creator
$is_creator = ($user_id !== null && $user_id == $poll['creator_id']);

// Success message from voting
$vote_success = '';
if (isset($_SESSION['vote_success'])) {
    $vote_success = $_SESSION['vote_success'];
    unset($_SESSION['vote_success']);
}

$page_title = $poll['title'];
$page_css = 'poll.css';
require_once 'includes/header.php';
?>

<div class="poll-view">

    <?php if (!empty($vote_success)): ?>
    <div class="alert alert-success"><p><?php echo sanitize($vote_success); ?></p></div>
    <?php endif; ?>

    <!-- Poll Header -->
    <div class="card poll-header-card">
        <div class="poll-meta">
            <span class="badge badge-<?php echo $poll['poll_type']; ?>">
                <?php echo ucfirst($poll['poll_type']); ?> Choice
            </span>
            <span class="text-muted text-small">
                by <?php echo sanitize($creator_name); ?> &middot; <?php echo get_time_ago($poll['created_at']); ?>
            </span>
        </div>

        <h1><?php echo sanitize($poll['title']); ?></h1>

        <?php if (!empty($poll['description'])): ?>
        <p><?php echo nl2br(sanitize($poll['description'])); ?></p>
        <?php endif; ?>

        <?php if (!empty($poll['image_path'])): ?>
        <div class="poll-image">
            <img src="/Voting-System/<?php echo sanitize($poll['image_path']); ?>" alt="Poll image">
        </div>
        <?php endif; ?>

        <?php if ($poll['anonymous_mode']): ?>
        <div class="alert alert-info"><p>This poll collects votes anonymously.</p></div>
        <?php endif; ?>

        <div class="poll-stats-bar">
            <span><?php echo $vote_count; ?> vote<?php echo $vote_count !== 1 ? 's' : ''; ?></span>
            <span><?php echo $poll['total_views']; ?> view<?php echo $poll['total_views'] !== 1 ? 's' : ''; ?></span>
            <?php if ($poll['vote_limit']): ?>
            <span>Limit: <?php echo $poll['vote_limit']; ?></span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Vote Limit Reached -->
    <?php if ($vote_limit_reached): ?>
    <div class="alert alert-error"><p>This poll has reached its maximum number of responses.</p></div>
    <?php endif; ?>

    <!-- Voting Form (show if user hasn't voted and limit not reached) -->
    <?php if (!$user_voted && !$vote_limit_reached): ?>
    <div class="card">
        <h2>Cast Your Vote</h2>

        <?php if (!is_logged_in() && $poll['access_type'] !== 'public'): ?>
        <p><a href="login.php">Login</a> to vote on this poll.</p>
        <?php else: ?>

        <form method="POST" action="vote.php" id="voteForm">
            <input type="hidden" name="poll_id" value="<?php echo $poll_id; ?>">

            <?php if ($poll['poll_type'] === 'single'): ?>
                <?php foreach ($options as $option): ?>
                <div class="form-check">
                    <input type="radio" name="option_id" id="option_<?php echo $option['id']; ?>" value="<?php echo $option['id']; ?>" required>
                    <label for="option_<?php echo $option['id']; ?>"><?php echo sanitize($option['option_text']); ?></label>
                </div>
                <?php endforeach; ?>

            <?php elseif ($poll['poll_type'] === 'multiple'): ?>
                <?php foreach ($options as $option): ?>
                <div class="form-check">
                    <input type="checkbox" name="option_ids[]" id="option_<?php echo $option['id']; ?>" value="<?php echo $option['id']; ?>">
                    <label for="option_<?php echo $option['id']; ?>"><?php echo sanitize($option['option_text']); ?></label>
                </div>
                <?php endforeach; ?>

            <?php elseif ($poll['poll_type'] === 'text'): ?>
                <div class="form-group">
                    <label for="text_answer">Your Response</label>
                    <textarea id="text_answer" name="text_answer" required placeholder="Type your answer here..."></textarea>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary mt-10">Submit Vote</button>
        </form>

        <?php endif; ?>
    </div>
    <?php elseif ($user_voted): ?>
    <div class="alert alert-info"><p>You have already voted on this poll.</p></div>
    <?php endif; ?>

    <!-- Results Section -->
    <?php if ($show_results): ?>
    <div class="card">
        <h2>Results</h2>

        <?php if ($poll['poll_type'] === 'text'): ?>
            <!-- Text responses -->
            <?php $text_answers = get_text_answers($pdo, $poll_id); ?>
            <?php if (empty($text_answers)): ?>
            <p class="text-muted">No responses yet.</p>
            <?php else: ?>
                <?php foreach ($text_answers as $answer): ?>
                <div class="text-response">
                    <p><?php echo nl2br(sanitize($answer['text_answer'])); ?></p>
                    <span class="text-muted text-small"><?php echo get_time_ago($answer['created_at']); ?></span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php else: ?>
            <!-- Single/Multiple choice results -->
            <?php foreach ($options as $option): ?>
                <?php
                $opt_votes = get_option_vote_count($pdo, $option['id']);
                $percentage = ($vote_count > 0) ? round(($opt_votes / $vote_count) * 100) : 0;
                ?>
                <div class="result-row">
                    <div class="result-label">
                        <span><?php echo sanitize($option['option_text']); ?></span>
                        <span class="text-muted"><?php echo $opt_votes; ?> vote<?php echo $opt_votes !== 1 ? 's' : ''; ?> (<?php echo $percentage; ?>%)</span>
                    </div>
                    <div class="result-bar-bg">
                        <div class="result-bar-fill" style="width: <?php echo $percentage; ?>%;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php elseif (!$show_results && !$user_voted): ?>
    <div class="card">
        <p class="text-muted">Results will be visible after you vote.</p>
    </div>
    <?php endif; ?>

    <!-- Share Link -->
    <div class="card">
        <h3>Share This Poll</h3>
        <div class="share-link-box">
            <input type="text" id="shareLink" value="<?php echo 'http://localhost/Voting-System/poll.php?id=' . $poll_id; ?>" readonly>
            <button type="button" class="btn btn-secondary btn-small" onclick="copyShareLink()">Copy</button>
        </div>
    </div>

</div>

<script>
function copyShareLink() {
    var input = document.getElementById('shareLink');
    input.select();
    document.execCommand('copy');
    alert('Link copied!');
}

// Validate vote form
var voteForm = document.getElementById('voteForm');
if (voteForm) {
    voteForm.addEventListener('submit', function(e) {
        var pollType = '<?php echo $poll['poll_type']; ?>';

        if (pollType === 'single') {
            var selected = document.querySelector('input[name="option_id"]:checked');
            if (!selected) {
                e.preventDefault();
                alert('Please select an option.');
            }
        } else if (pollType === 'multiple') {
            var checked = document.querySelectorAll('input[name="option_ids[]"]:checked');
            if (checked.length === 0) {
                e.preventDefault();
                alert('Please select at least one option.');
            }
        } else if (pollType === 'text') {
            var text = document.getElementById('text_answer').value.trim();
            if (text === '') {
                e.preventDefault();
                alert('Please enter your response.');
            }
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
