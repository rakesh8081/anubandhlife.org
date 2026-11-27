<?php
require 'assessments.php';
$assessments = mindlab_all_assessments();
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mind Lab 2.0 | Anubandh Life</title>
    <link rel="stylesheet" href="style.css?v=2.0"> 
</head>
<body>

<div class="container">
    <header class="header-nav">
        <h2 class="brand-name">Anubandh Life</h2>
        <span style="color: #666; font-size: 0.9rem;">Mind Lab - Explore & Reflect</span>
    </header>

    <main>
        <div class="intro-text" style="margin-bottom: 40px;">
            <p>Welcome to Mind Lab 2.0 — your custom space for self-inquiry, awareness, and collective insight. Participate in psychological assessments to explore dimensions of your personality.</p>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-column">
                <h2 class="column-title">Join Us in Self-Discovery</h2>
                
                <div class="item-group">
                    <h3>1. Interest Inventory Test</h3>
                    <p>Your interests reflect your preference for doing some activities instead of others and can help you choose an appropriate career or additional path.</p>
                    <a href="consent.php?test=interest" class="btn">Take Test</a>
                </div>
            </div>

            <div class="dashboard-column">
                <h2 class="column-title">Wellbeing Screening</h2>
                <p class="disclaimer-note" style="margin-bottom: 20px;">Short, widely used screening questionnaires. These are not a diagnosis.</p>
                <?php $n = 0; foreach ($assessments as $a): $n++; ?>
                <div class="item-group">
                    <h3><?= $n ?>. <?= $h($a['title']) ?></h3>
                    <p><?= $h($a['summary']) ?> About <?= (int)$a['minutes'] ?> minutes.</p>
                    <a href="consent.php?test=<?= $h($a['id']) ?>" class="btn">Start</a>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="dashboard-column">
                <h2 class="column-title">Survey Collection</h2>
                
                <div class="item-group">
                    <h3>1. Psychological Awareness Survey</h3>
                    <p>A research initiative exploring student perceptions, knowledge gaps, and social attitudes regarding psychological disorders.</p>
                    <a href="consent.php?test=awareness" class="btn">Take Survey</a>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
