<?php
// test.php?id=<assessment id> — renders any screening test defined in assessments/
require 'assessments.php';

$def = mindlab_get_assessment($_GET['id'] ?? '');
if (!$def) {
    header('Location: index.php');
    exit;
}
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $h($def['title']) ?> | Mind Lab</title>
    <link rel="stylesheet" href="style.css?v=3.0">
</head>
<body>

<div class="container">
    <header class="header-nav">
        <h2 class="brand-name">Anubandh Life</h2>
        <div style="text-align: right;">
            <span style="font-size: 0.85rem; color: var(--text-muted);">Participant:</span><br>
            <strong id="displayUserCard">Loading...</strong>
        </div>
    </header>

    <main style="max-width: 800px; margin: 0 auto;">
        <h1 style="margin-bottom: 10px;"><?= $h($def['title']) ?></h1>
        <p class="disclaimer-note"><?= $h(mindlab_disclaimer_text()) ?></p>
        <p style="margin: 20px 0 30px; font-weight: 600;"><?= $h($def['instructions']) ?></p>

        <form id="screeningForm">
            <?php $n = 0; foreach (mindlab_items($def) as $key => $item): $n++;
                $isCrisis = in_array($key, $def['crisis_items'] ?? [], true); ?>
            <fieldset class="screen-item" data-key="<?= $h($key) ?>"
                <?php if ($item['skip_if']): ?>data-skip-key="<?= $h($item['skip_if'][0]) ?>" data-skip-value="<?= (int)$item['skip_if'][1] ?>"<?php endif; ?>
                <?= $item['optional'] ? 'data-optional="1"' : '' ?>>
                <legend><?= $n ?>. <?= $h($item['text']) ?></legend>
                <?php if ($item['hint'] !== ''): ?><p class="item-hint"><?= $h($item['hint']) ?></p><?php endif; ?>
                <div class="option-list">
                    <?php foreach ($item['options'] as $value => $label): ?>
                    <label>
                        <input type="radio" name="<?= $h($key) ?>" value="<?= (int)$value ?>"
                            <?= $isCrisis ? 'data-crisis="1"' : '' ?>>
                        <?= $h($label) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <?php if ($isCrisis): ?>
            <div id="crisis-<?= $h($key) ?>" class="crisis-inline" hidden><?= mindlab_crisis_html() ?></div>
            <?php endif; ?>
            <?php endforeach; ?>

            <?php foreach ($def['extra'] ?? [] as $key => $q): ?>
            <fieldset class="screen-item">
                <legend><?= $h($q['text']) ?></legend>
                <div class="option-list">
                    <?php foreach ($q['options'] as $value => $label): ?>
                    <label>
                        <input type="radio" name="<?= $h($key) ?>" value="<?= (int)$value ?>">
                        <?= $h($label) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <?php endforeach; ?>

            <div style="margin-top: 40px; text-align: right;">
                <p id="errorMsg" style="color: #c62828; display: none; margin-bottom: 15px;"></p>
                <button type="submit" class="btn btn-full" style="padding: 15px; font-size: 1.1rem;">See My Results</button>
            </div>
        </form>

        <p class="source-note"><?= $h($def['source']) ?></p>
    </main>
</div>

<script>
    const TEST_ID = <?= json_encode($def['id']) ?>;
    const ITEM_KEYS = <?= json_encode(array_keys(mindlab_items($def))) ?>;
    const EXTRA_KEYS = <?= json_encode(array_keys($def['extra'] ?? [])) ?>;

    // Must have registered through the consent page first
    let userData = null;
    try { userData = JSON.parse(localStorage.getItem('mindLabUser')); } catch (e) {}
    const participantId = localStorage.getItem('participant_id');
    if (!userData || !participantId) {
        window.location.href = 'consent.php?test=' + encodeURIComponent(TEST_ID);
    } else {
        document.getElementById('displayUserCard').textContent = userData.name;
    }

    // Hide questions that an earlier answer makes irrelevant (e.g. AUDIT for non-drinkers)
    function isSkipped(fs) {
        if (!fs.dataset.skipKey) return false;
        const chosen = document.querySelector('input[name="' + fs.dataset.skipKey + '"]:checked');
        return chosen !== null && Number(chosen.value) === Number(fs.dataset.skipValue);
    }
    function applySkips() {
        document.querySelectorAll('fieldset[data-skip-key]').forEach(fs => {
            const skip = isSkipped(fs);
            fs.hidden = skip;
            if (skip) fs.querySelectorAll('input').forEach(i => { i.checked = false; });
        });
    }
    document.querySelectorAll('#screeningForm input[type="radio"]').forEach(i => i.addEventListener('change', applySkips));

    // Show support information as soon as a crisis item is answered above 0
    document.querySelectorAll('input[data-crisis]').forEach(input => {
        input.addEventListener('change', () => {
            const box = document.getElementById('crisis-' + input.name);
            if (box) box.hidden = Number(input.value) === 0;
        });
    });

    document.getElementById('screeningForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const errorMsg = document.getElementById('errorMsg');
        const form = new FormData(this);
        const responses = {};
        for (const key of ITEM_KEYS.concat(EXTRA_KEYS)) {
            if (form.get(key) !== null) responses[key] = Number(form.get(key));
        }
        const missing = ITEM_KEYS.filter(k => {
            const fs = document.querySelector('fieldset[data-key="' + k + '"]');
            return !(k in responses) && !fs.dataset.optional && !isSkipped(fs);
        });
        if (missing.length) {
            document.querySelector('fieldset[data-key="' + missing[0] + '"]').scrollIntoView({ behavior: 'smooth', block: 'center' });
            errorMsg.textContent = 'Please answer every question before continuing (' + missing.length + ' left).';
            errorMsg.style.display = 'block';
            return;
        }

        const btn = this.querySelector('button[type="submit"]');
        btn.textContent = 'Saving...';
        btn.disabled = true;
        try {
            const res = await fetch('save_data.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'save_assessment', test_id: TEST_ID, participant_id: participantId, responses })
            });
            const data = await res.json();
            if (data.success) {
                window.location.href = 'result.php?test=' + encodeURIComponent(TEST_ID);
                return;
            }
            errorMsg.textContent = data.message || 'Could not save your answers. Please try again.';
        } catch (err) {
            errorMsg.textContent = 'Could not reach the server. Please check your connection and try again.';
        }
        errorMsg.style.display = 'block';
        btn.textContent = 'See My Results';
        btn.disabled = false;
    });
</script>

</body>
</html>
