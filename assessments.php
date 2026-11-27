<?php
// assessments.php — loads screening tests from assessments/<id>.php and scores them.
// To add a test: copy assessments/phq9.php, change the questions, options and bands.

function mindlab_assessment_ids() {
    $ids = [];
    foreach (glob(__DIR__ . '/assessments/*.php') as $file) {
        $ids[] = basename($file, '.php');
    }
    sort($ids);
    return $ids;
}

function mindlab_get_assessment($id) {
    if (!is_string($id) || !preg_match('/^[a-z0-9_-]{1,32}$/', $id)) {
        return null;
    }
    $file = __DIR__ . '/assessments/' . $id . '.php';
    if (!is_file($file)) {
        return null;
    }
    $def = require $file;
    return (is_array($def) && ($def['id'] ?? '') === $id) ? $def : null;
}

function mindlab_all_assessments() {
    $all = [];
    foreach (mindlab_assessment_ids() as $id) {
        if ($def = mindlab_get_assessment($id)) {
            $all[$id] = $def;
        }
    }
    uasort($all, fn($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));
    return $all;
}

// Normalises items: each may be a plain string, or an array with
//   'text'     => question text
//   'options'  => [score => label]   (defaults to the test's shared 'options')
//   'optional' => true               (may be left blank; counts as 0)
//   'skip_if'  => ['q1', 0]          (skipped, scoring 0, when q1 is answered 0)
//   'hint'     => short note shown under the question
function mindlab_items(array $def) {
    $items = [];
    foreach ($def['items'] as $key => $item) {
        if (is_string($item)) {
            $item = ['text' => $item];
        }
        $items[$key] = $item + [
            'options'  => $def['options'] ?? [],
            'optional' => false,
            'skip_if'  => null,
            'hint'     => '',
        ];
    }
    return $items;
}

// Scores answers on the server, so the stored score can't be faked by the browser.
// Returns null if any scored item is missing or has a value outside the scale.
function mindlab_score_assessment(array $def, $responses) {
    if (!is_array($responses)) {
        return null;
    }
    $answers = [];
    $total = 0;
    foreach (mindlab_items($def) as $key => $item) {
        if ($item['skip_if'] && ($answers[$item['skip_if'][0]] ?? null) === $item['skip_if'][1]) {
            continue; // skipped by an earlier answer: scores 0
        }
        $raw = $responses[$key] ?? null;
        if (($raw === null || $raw === '') && $item['optional']) {
            continue; // left blank on purpose: scores 0
        }
        if (!is_numeric($raw) || !array_key_exists((int)$raw, $item['options'])) {
            return null;
        }
        $answers[$key] = (int)$raw;
        $total += (int)$raw;
    }

    $extra = [];
    foreach ($def['extra'] ?? [] as $key => $q) {
        $raw = $responses[$key] ?? null;
        if (is_numeric($raw) && isset($q['options'][(int)$raw])) {
            $extra[$key] = (int)$raw;
        }
    }

    $band = ['', ''];
    foreach ($def['bands'] as [$min, $max, $label, $text]) {
        if ($total >= $min && $total <= $max) {
            $band = [$label, $text];
            break;
        }
    }

    $crisis = false;
    foreach ($def['crisis_items'] ?? [] as $key) {
        if (($answers[$key] ?? 0) > 0) {
            $crisis = true;
        }
    }

    return [
        'answers'  => $answers,
        'extra'    => $extra,
        'total'    => $total,
        'severity' => $band[0],
        'summary'  => $band[1],
        'crisis'   => $crisis,
    ];
}

function mindlab_ensure_assessment_table(PDO $pdo) {
    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
        $pdo->exec("CREATE TABLE IF NOT EXISTS assessment_results (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            participant_id INTEGER NOT NULL,
            test_id TEXT NOT NULL,
            responses TEXT NOT NULL,
            total_score INTEGER NOT NULL,
            severity TEXT NOT NULL,
            crisis_flag INTEGER NOT NULL DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP)");
        return;
    }
    $pdo->exec("CREATE TABLE IF NOT EXISTS assessment_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        participant_id INT NOT NULL,
        test_id VARCHAR(32) NOT NULL,
        responses TEXT NOT NULL,
        total_score INT NOT NULL,
        severity VARCHAR(64) NOT NULL,
        crisis_flag TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_participant_test (participant_id, test_id)
    ) DEFAULT CHARSET=utf8mb4");
}

// Latest stored result for this test from the participants registered in this browser session.
function mindlab_latest_assessment_result(PDO $pdo, $testId, array $participantIds) {
    $participantIds = array_values(array_filter(array_map('intval', $participantIds)));
    if (!$participantIds) {
        return null;
    }
    mindlab_ensure_assessment_table($pdo);
    $in = implode(',', array_fill(0, count($participantIds), '?'));
    $stmt = $pdo->prepare("SELECT * FROM assessment_results WHERE test_id = ? AND participant_id IN ($in) ORDER BY id DESC LIMIT 1");
    $stmt->execute(array_merge([$testId], $participantIds));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $row['responses'] = json_decode($row['responses'], true) ?: [];
    }
    return $row ?: null;
}

function mindlab_crisis_html() {
    return '
    <div class="crisis-box">
        <strong>If you are having thoughts of harming yourself, please reach out now. You are not alone.</strong>
        <ul>
            <li><strong>Tele-MANAS (Govt. of India, free, 24×7):</strong> 14416 or 1-800-891-4416</li>
            <li><strong>Emergency:</strong> 112</li>
            <li><strong>iCall (TISS):</strong> 9152987821 (Mon–Sat, 10 am–8 pm)</li>
        </ul>
        If you are in immediate danger, go to the nearest hospital emergency department.
    </div>';
}

function mindlab_disclaimer_text() {
    return 'This is a screening tool, not a diagnosis. Only a qualified mental health professional can make a diagnosis. If you are worried about how you feel, please consult a professional.';
}
