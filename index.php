<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';



// choose a random poll for "Today's Poll" section
$today = date('Y-m-d');
$query_today = "SELECT * FROM polls 
                WHERE show_in_feed = 1 
                AND DATE(created_at) = '$today' 
                ORDER BY RAND() 
                LIMIT 1";
$result_today = mysqli_query($conn, $query_today);
$todays_poll = mysqli_fetch_assoc($result_today);


$query_feed = "SELECT * FROM polls 
               WHERE show_in_feed = 1 
               ORDER BY created_at DESC 
               LIMIT 20";
$result_feed = mysqli_query($conn, $query_feed);

// collecting feed polls into an array
$feed_polls = [];
while ($row = mysqli_fetch_assoc($result_feed)) {
    $feed_polls[] = $row;
}

$page_title = 'Home';
$page_css = 'home.css';
require_once 'includes/header.php';
?>

<section class="hero">
    <h1>Welcome to VoteHub</h1>
    <p>Create polls, share them with the community, and make your voice heard.</p>
    <?php if (is_logged_in()): ?>
        <a href="create_poll.php" class="btn btn-primary">Create a Poll</a>
    <?php else: ?>
        <a href="register.php" class="btn btn-primary">Get Started</a>
    <?php endif; ?>
</section>

<?php if ($todays_poll): ?>
<section class="section">
    <h2>Today's Poll</h2>
    <div class="card">
        <div class="poll-card-meta">
            <span class="badge badge-<?php echo $todays_poll['poll_type']; ?>">
                <?php echo ucfirst($todays_poll['poll_type']); ?>
            </span>
            <span class="text-muted text-small">
                by <?php echo sanitize(get_poll_creator($conn, $todays_poll['creator_id'])); ?>
            </span>
        </div>
        <h3><a href="poll.php?id=<?php echo $todays_poll['id']; ?>"><?php echo sanitize($todays_poll['title']); ?></a></h3>
        
        <?php if (!empty($todays_poll['description'])): ?>
            <p class="text-muted">
                <?php echo sanitize(substr($todays_poll['description'], 0, 150)); ?>
                <?php echo strlen($todays_poll['description']) > 150 ? '...' : ''; ?>
            </p>
        <?php endif; ?>
        
        <div class="poll-card-footer">
            <span class="text-small text-muted"><?php echo get_vote_count($conn, $todays_poll['id']); ?> votes</span>
            <a href="poll.php?id=<?php echo $todays_poll['id']; ?>" class="btn btn-primary btn-small">Vote Now</a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <h2>Community Feed</h2>

    <?php if (empty($feed_polls)): ?>
        <div class="card">
            <p class="text-muted">No community polls yet. Be the first to create one!</p>
        </div>
    <?php else: ?>

        <div class="poll-feed">
            <?php foreach ($feed_polls as $fp): ?>
            <div class="card poll-feed-card">
                <div class="poll-card-meta">
                    <span class="badge badge-<?php echo $fp['poll_type']; ?>">
                        <?php echo ucfirst($fp['poll_type']); ?>
                    </span>
                    <span class="text-muted text-small">
                        by <?php echo sanitize(get_poll_creator($conn, $fp['creator_id'])); ?>
                        &middot; <?php echo get_time_ago($fp['created_at']); ?>
                    </span>
                </div>
                <h3><a href="poll.php?id=<?php echo $fp['id']; ?>"><?php echo sanitize($fp['title']); ?></a></h3>
                
                <?php if (!empty($fp['description'])): ?>
                    <p class="text-muted">
                        <?php echo sanitize(substr($fp['description'], 0, 120)); ?>
                        <?php echo strlen($fp['description']) > 120 ? '...' : ''; ?>
                    </p>
                <?php endif; ?>
                
                <div class="poll-card-footer">
                    <span class="text-small text-muted">
                        <?php echo get_vote_count($conn, $fp['id']); ?> votes &middot;
                        <?php echo $fp['total_views']; ?> views
                    </span>
                    <a href="poll.php?id=<?php echo $fp['id']; ?>" class="btn btn-primary btn-small">Vote</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>