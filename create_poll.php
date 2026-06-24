<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

require_login();

$errors = [];
$title = '';
$description = '';
$poll_type = 'single';
$access_type = 'public';
$access_code = '';
$show_in_feed = 0;
$anonymous_mode = 0;
$results_visibility = 'immediate';
$vote_limit = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $poll_type = $_POST['poll_type'] ?? 'single';
    $access_type = $_POST['access_type'] ?? 'public';
    $access_code = trim($_POST['access_code'] ?? '');
    $show_in_feed = isset($_POST['show_in_feed']) ? 1 : 0;
    $anonymous_mode = isset($_POST['anonymous_mode']) ? 1 : 0;
    $results_visibility = $_POST['results_visibility'] ?? 'immediate';
    $vote_limit_input = trim($_POST['vote_limit'] ?? '');
    $vote_limit = ($vote_limit_input !== '' && is_numeric($vote_limit_input)) ? (int) $vote_limit_input : null;
    $options = isset($_POST['options']) ? $_POST['options'] : [];

    
    if (empty($title)) {
        $errors[] = 'Poll title is required.';
    } elseif (strlen($title) > 255) {
        $errors[] = 'Poll title must be under 255 characters.';
    }

    if (!in_array($poll_type, ['single', 'multiple', 'text'])) {
        $errors[] = 'Invalid poll type.';
    }

    if (!in_array($access_type, ['public', 'account', 'code'])) {
        $errors[] = 'Invalid access type.';
    }

    if ($access_type === 'code' && empty($access_code)) {
        $errors[] = 'Access code is required for code-protected polls.';
    }

    
    if ($poll_type !== 'text') {
        $clean_options = [];
        foreach ($options as $opt) {
            $opt = trim($opt);
            if ($opt !== '') {
                $clean_options[] = $opt;
            }
        }
        if (count($clean_options) < 2) {
            $errors[] = 'Please provide at least 2 options.';
        }
    }

    
    $image_path = null;
    if (isset($_FILES['poll_image']) && $_FILES['poll_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['poll_image'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowed)) {
            $errors[] = 'Image must be JPG, PNG, or GIF.';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'Image must be under 2MB.';
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'poll_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $upload_dir = __DIR__ . '/assets/uploads/';

            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $image_path = 'assets/uploads/' . $filename;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    }

    
    if (empty($errors)) {
        $c_user_id = mysqli_real_escape_string($conn, get_current_user_id());
        $c_title = mysqli_real_escape_string($conn, $title);
        $c_description = mysqli_real_escape_string($conn, $description);
        $c_image_path = $image_path ? "'" . mysqli_real_escape_string($conn, $image_path) . "'" : "NULL";
        $c_poll_type = mysqli_real_escape_string($conn, $poll_type);
        $c_access_type = mysqli_real_escape_string($conn, $access_type);
        $c_access_code = ($access_type === 'code') ? "'" . mysqli_real_escape_string($conn, $access_code) . "'" : "NULL";
        $c_show_in_feed = (int)$show_in_feed;
        $c_anonymous_mode = (int)$anonymous_mode;
        $c_results_visibility = mysqli_real_escape_string($conn, $results_visibility);
        $c_vote_limit = $vote_limit !== null ? (int)$vote_limit : "NULL";

        
        $query = "INSERT INTO polls (creator_id, title, description, image_path, poll_type, access_type, access_code, show_in_feed, anonymous_mode, results_visibility, vote_limit)
                  VALUES ('$c_user_id', '$c_title', '$c_description', $c_image_path, '$c_poll_type', '$c_access_type', $c_access_code, '$c_show_in_feed', '$c_anonymous_mode', '$c_results_visibility', $c_vote_limit)";
        
        if (mysqli_query($conn, $query)) {
            $poll_id = mysqli_insert_id($conn);

            
            if ($poll_type !== 'text') {
                foreach ($clean_options as $option_text) {
                    $c_option_text = mysqli_real_escape_string($conn, $option_text);
                    $opt_query = "INSERT INTO poll_options (poll_id, option_text) VALUES ('$poll_id', '$c_option_text')";
                    mysqli_query($conn, $opt_query);
                }
            }

            redirect('poll.php?id=' . $poll_id);
        } else {
            $errors[] = 'Database error: Failed to create poll.';
        }
    }
}

$page_title = 'Create Poll';
$page_css = 'poll.css';
require_once 'includes/header.php';
?>

<div class="poll-form-container">
    <div class="card">
        <h1>Create a New Poll</h1>
        <p class="text-muted mb-20">Fill in the details below to create your poll.</p>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
            <p><?php echo sanitize($error); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="create_poll.php" enctype="multipart/form-data" id="createPollForm">

            <div class="form-group">
                <label for="title">Poll Title</label>
                <input type="text" id="title" name="title" value="<?php echo sanitize($title); ?>" required>
            </div>

            <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description"><?php echo sanitize($description); ?></textarea>
            </div>

            <div class="form-group">
                <label for="poll_image">Poll Image (optional)</label>
                <input type="file" id="poll_image" name="poll_image" accept="image/jpeg,image/png,image/gif">
                <span class="help-text">Max 2MB. JPG, PNG, or GIF.</span>
            </div>

            <div class="form-group">
                <label for="poll_type">Poll Type</label>
                <select id="poll_type" name="poll_type">
                    <option value="single" <?php if ($poll_type === 'single') echo 'selected'; ?>>Single Choice</option>
                    <option value="multiple" <?php if ($poll_type === 'multiple') echo 'selected'; ?>>Multiple Choice</option>
                    <option value="text" <?php if ($poll_type === 'text') echo 'selected'; ?>>Text Response</option>
                </select>
            </div>

            <div id="optionsSection">
                <div class="form-group">
                    <label>Poll Options</label>
                    <div id="optionsContainer">
                        <?php if (!empty($options)): ?>
                            <?php foreach ($options as $i => $opt): ?>
                            <div class="option-row">
                                <input type="text" name="options[]" value="<?php echo sanitize($opt); ?>" placeholder="Option <?php echo $i + 1; ?>">
                                <?php if ($i >= 2): ?>
                                <button type="button" class="btn btn-danger btn-small remove-option-btn" onclick="removeOption(this)">X</button>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="option-row">
                                <input type="text" name="options[]" placeholder="Option 1">
                            </div>
                            <div class="option-row">
                                <input type="text" name="options[]" placeholder="Option 2">
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-secondary btn-small mt-10" id="addOptionBtn" onclick="addOption()">+ Add Option</button>
                </div>
            </div>

            <div class="form-group">
                <label for="access_type">Access Type</label>
                <select id="access_type" name="access_type">
                    <option value="public" <?php if ($access_type === 'public') echo 'selected'; ?>>Public Link</option>
                    <option value="account" <?php if ($access_type === 'account') echo 'selected'; ?>>Account Required</option>
                    <option value="code" <?php if ($access_type === 'code') echo 'selected'; ?>>Code Protected</option>
                </select>
            </div>

            <div class="form-group" id="accessCodeGroup" style="display:none;">
                <label for="access_code">Access Code</label>
                <input type="text" id="access_code" name="access_code" value="<?php echo sanitize($access_code); ?>" placeholder="e.g. TECH2026">
            </div>

            <div class="form-check">
                <input type="checkbox" id="show_in_feed" name="show_in_feed" <?php if ($show_in_feed) echo 'checked'; ?>>
                <label for="show_in_feed">Show in Community Feed</label>
            </div>

            <div class="form-check">
                <input type="checkbox" id="anonymous_mode" name="anonymous_mode" <?php if ($anonymous_mode) echo 'checked'; ?>>
                <label for="anonymous_mode">Anonymous Voting</label>
            </div>

            <div class="form-group">
                <label for="results_visibility">Results Visibility</label>
                <select id="results_visibility" name="results_visibility">
                    <option value="immediate" <?php if ($results_visibility === 'immediate') echo 'selected'; ?>>Visible immediately</option>
                    <option value="after_vote" <?php if ($results_visibility === 'after_vote') echo 'selected'; ?>>Visible after voting</option>
                </select>
            </div>

            <div class="form-group">
                <label for="vote_limit">Vote Limit (optional)</label>
                <input type="number" id="vote_limit" name="vote_limit" min="1" value="<?php echo sanitize($vote_limit); ?>" placeholder="Leave empty for unlimited">
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Create Poll</button>

        </form>
    </div>
</div>

<script>
var pollTypeSelect = document.getElementById('poll_type');
var optionsSection = document.getElementById('optionsSection');

pollTypeSelect.addEventListener('change', function() {
    if (this.value === 'text') {
        optionsSection.style.display = 'none';
    } else {
        optionsSection.style.display = 'block';
    }
});

if (pollTypeSelect.value === 'text') {
    optionsSection.style.display = 'none';
}

var accessTypeSelect = document.getElementById('access_type');
var accessCodeGroup = document.getElementById('accessCodeGroup');

accessTypeSelect.addEventListener('change', function() {
    if (this.value === 'code') {
        accessCodeGroup.style.display = 'block';
    } else {
        accessCodeGroup.style.display = 'none';
    }
});

if (accessTypeSelect.value === 'code') {
    accessCodeGroup.style.display = 'block';
}

var optionCount = document.querySelectorAll('#optionsContainer .option-row').length;

function addOption() {
    optionCount++;
    var container = document.getElementById('optionsContainer');
    var div = document.createElement('div');
    div.className = 'option-row';
    div.innerHTML = '<input type="text" name="options[]" placeholder="Option ' + optionCount + '">' +
        '<button type="button" class="btn btn-danger btn-small remove-option-btn" onclick="removeOption(this)">X</button>';
    container.appendChild(div);
}

function removeOption(btn) {
    var row = btn.parentElement;
    row.parentElement.removeChild(row);
}

document.getElementById('createPollForm').addEventListener('submit', function(e) {
    var title = document.getElementById('title').value.trim();
    var pollType = document.getElementById('poll_type').value;
    var accessType = document.getElementById('access_type').value;
    var accessCode = document.getElementById('access_code').value.trim();
    var errors = [];

    if (title === '') {
        errors.push('Poll title is required.');
    }

    if (accessType === 'code' && accessCode === '') {
        errors.push('Access code is required for code-protected polls.');
    }

    if (pollType !== 'text') {
        var options = document.querySelectorAll('input[name="options[]"]');
        var filled = 0;
        for (var i = 0; i < options.length; i++) {
            if (options[i].value.trim() !== '') {
                filled++;
            }
        }
        if (filled < 2) {
            errors.push('Please provide at least 2 options.');
        }
    }

    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join('\n'));
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>