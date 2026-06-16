<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

require_login();

$user_id = get_current_user_id();

// Get all polls created by current user
$stmt = $pdo->prepare("SELECT * FROM polls WHERE creator_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$my_polls = $stmt->fetchAll();

// Success/error messages
$message = '';
if (isset($_SESSION['dashboard_message'])) {
    $message = $_SESSION['dashboard_message'];
    unset($_SESSION['dashboard_message']);
}

$page_title = 'My Polls';
$page_css = 'dashboard.css';
require_once 'includes/header.php';
?>

<div class="dashboard">

    <div class="dashboard-header">
        <h1>My Polls</h1>
        <a href="create_poll.php" class="btn btn-primary">+ Create New Poll</a>
    </div>

    <?php if (!empty($message)): ?>
    <div class="alert alert-success"><p><?php echo sanitize($message); ?></p></div>
    <?php endif; ?>

    <?php if (empty($my_polls)): ?>
    <div class="card">
        <p class="text-muted">You haven't created any polls yet.</p>
        <p class="mt-10"><a href="create_poll.php">Create your first poll</a></p>
    </div>
    <?php else: ?>

    <table class="polls-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Type</th>
                <th>Votes</th>
                <th>Views</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($my_polls as $mp): ?>
            <tr>
                <td>
                    <a href="poll.php?id=<?php echo $mp['id']; ?>"><?php echo sanitize($mp['title']); ?></a>
                    <?php if ($mp['show_in_feed']): ?>
                    <span class="badge badge-single" style="font-size:9px;">FEED</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="badge badge-<?php echo $mp['poll_type']; ?>">
                        <?php echo ucfirst($mp['poll_type']); ?>
                    </span>
                </td>
                <td><?php echo get_vote_count($pdo, $mp['id']); ?></td>
                <td><?php echo $mp['total_views']; ?></td>
                <td class="text-small text-muted"><?php echo date('M j, Y', strtotime($mp['created_at'])); ?></td>
                <td>
                    <button type="button" class="btn btn-secondary btn-small" onclick="copyLink(<?php echo $mp['id']; ?>)">Share</button>
                    <form method="POST" action="delete_poll.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this poll?');">
                        <input type="hidden" name="poll_id" value="<?php echo $mp['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-small">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php endif; ?>

</div>

<script>
function copyLink(pollId) {
    var link = 'http://localhost/Voting-System/poll.php?id=' + pollId;
    var temp = document.createElement('input');
    document.body.appendChild(temp);
    temp.value = link;
    temp.select();
    document.execCommand('copy');
    document.body.removeChild(temp);
    alert('Poll link copied!');
}
</script>

<?php require_once 'includes/footer.php'; ?>
