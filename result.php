<?php
// result.php?test=<assessment id> — shows the latest result of this test taken in this browser session
require 'db_config.php';
require 'auth.php';
require 'assessments.php';

$def = mindlab_get_assessment($_GET['test'] ?? '');
if (!$def) {
    header('Location: index.php');
    exit;
}

$row = null;
try {
    $row = mindlab_latest_assessment_result($pdo, $def['id'], mindlab_owned_participants());
} catch (\Throwable $e) {
    error_log("Assessment result load error: " . $e->getMessage());
}
if (!$row) {
    // Nothing taken in this session yet (or the session expired)
    header('Location: consent.php?test=' . urlencode($def['id']));
    exit;
}

$scored = mindlab_score_assessment($def, $row['responses']);
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');

$stmt = $pdo->prepare("SELECT full_name, age, sex FROM participants WHERE id = ?");
$stmt->execute([$row['participant_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['full_name' => '', 'age' => '', 'sex' => ''];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $h($def['title']) ?> Result | Mind Lab</title>
    <link rel="stylesheet" href="style.css?v=3.0">
</head>
<body>

<div class="container">
    <header class="header-nav">
        <h2 class="brand-name">Anubandh Life</h2>
        <a href="index.php" style="text-decoration: none; color: #666; font-size: 0.9rem;">Back to Mind Lab</a>
    </header>

    <main style="max-width: 800px; margin: 0 auto;">
        <div class="report-header" style="text-align: center; margin-bottom: 30px;">
            <h1>Your <?= $h($def['title']) ?> Result</h1>
            <p style="color: var(--text-muted); margin-top: 5px;">
                <strong>Participant:</strong> <?= $h($user['full_name']) ?> &nbsp;|&nbsp;
                <strong>Age:</strong> <?= $h($user['age']) ?> &nbsp;|&nbsp;
                <strong>Date:</strong> <?= $h(date('j M Y', strtotime($row['created_at']))) ?>
            </p>
        </div>

        <?php if ($scored['crisis']) echo mindlab_crisis_html(); ?>

        <div class="score-card">
            <div class="score-number"><?= (int)$scored['total'] ?><span> / <?= (int)$def['max_score'] ?></span></div>
            <div class="score-band"><?= $h($scored['severity']) ?></div>
            <p><?= $h($scored['summary']) ?></p>
        </div>

        <h3 style="margin: 30px 0 10px;">Score ranges</h3>
        <table class="band-table">
            <?php foreach ($def['bands'] as [$min, $max, $label]): ?>
            <tr class="<?= $label === $scored['severity'] ? 'current' : '' ?>">
                <td><?= (int)$min ?>–<?= (int)$max ?></td>
                <td><?= $h($label) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p class="disclaimer-note" style="margin-top: 30px;"><?= $h(mindlab_disclaimer_text()) ?></p>

        <div class="action-row" style="display: flex; gap: 15px; margin-top: 30px; justify-content: center; flex-wrap: wrap;">
            <a class="btn btn-full" style="display: inline-block; text-align: center; text-decoration: none;" target="_blank"
               href="export_pdf.php?type=assessment&amp;test=<?= $h($def['id']) ?>&amp;id=<?= (int)$row['participant_id'] ?>">Download PDF</a>
            <button id="emailBtn" class="btn btn-full" type="button">Email Results</button>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" class="btn" style="background: transparent; color: var(--text-muted); border: 1px solid #ccc;">Take another assessment</a>
        </div>

        <p class="source-note"><?= $h($def['source']) ?></p>
    </main>
</div>

<script>
document.getElementById('emailBtn').addEventListener('click', async function () {
    const btn = this;
    btn.textContent = 'Sending...';
    btn.disabled = true;
    try {
        const res = await fetch('mailer.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ participant_id: <?= (int)$row['participant_id'] ?>, type: 'assessment', test: <?= json_encode($def['id']) ?> })
        });
        const data = await res.json();
        if (data.success) {
            btn.textContent = 'Sent Successfully!';
            return;
        }
        alert('Delivery failed: ' + (data.message || 'Please try again.'));
    } catch (err) {
        alert('Network error. Please try again.');
    }
    btn.textContent = 'Email Results';
    btn.disabled = false;
});
</script>

</body>
</html>
