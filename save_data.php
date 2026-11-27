<?php
// save_data.php
require 'db_config.php';
require 'auth.php';
require 'assessments.php';
header('Content-Type: application/json');

function respond($payload, $status = 200) {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    respond(['success' => false, 'message' => 'Invalid request.'], 400);
}
$action = $data['action'] ?? '';

if ($action === 'register') {
    // 1. Validate and save participant
    $name  = trim((string)($data['name'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $age   = filter_var($data['age'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 120]]);
    $sex   = trim((string)($data['sex'] ?? ''));
    $phone = trim((string)($data['phone'] ?? ''));

    if ($name === '' || mb_strlen($name) > 100) {
        respond(['success' => false, 'message' => 'Please enter your name.'], 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(['success' => false, 'message' => 'Please enter a valid email address.'], 400);
    }
    if ($age === false) {
        respond(['success' => false, 'message' => 'Please enter a valid age.'], 400);
    }
    if ($sex === '' || mb_strlen($sex) > 20 || mb_strlen($phone) > 20) {
        respond(['success' => false, 'message' => 'Please check your details and try again.'], 400);
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO participants (full_name, email, age, sex, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $age, $sex, $phone]);
        $lastId = $pdo->lastInsertId();
        mindlab_remember_participant($lastId);
        respond(['success' => true, 'participant_id' => $lastId]);
    } catch (\Throwable $e) {
        error_log("Register Error: " . $e->getMessage());
        respond(['success' => false, 'message' => 'Could not save your details. Please try again.'], 500);
    }
}

// Results can only be attached to a participant registered in this browser session
if (in_array($action, ['save_results', 'save_awareness_results', 'save_assessment'], true)
    && !mindlab_owns_participant($data['participant_id'] ?? 0)) {
    respond(['success' => false, 'message' => 'Your session has expired. Please start again from the home page.'], 403);
}

if ($action === 'save_results') {
    // 2. Save Test Scores
    try {
        $stmt = $pdo->prepare("INSERT INTO interest_results (participant_id, raw_responses, calculated_scores) VALUES (?, ?, ?)");
        $stmt->execute([
            (int)$data['participant_id'],
            json_encode($data['responses'] ?? []),
            json_encode($data['scores'] ?? [])
        ]);
        respond(['success' => true]);
    } catch (\Throwable $e) {
        error_log("Results Save Error: " . $e->getMessage());
        respond(['success' => false, 'message' => 'Could not save your results. Please try again.'], 500);
    }
}
elseif ($action === 'save_awareness_results') {
    // 3. Save Awareness Survey
    try {
        $stmt = $pdo->prepare("INSERT INTO awareness_survey_results (participant_id, demographics, responses) VALUES (?, ?, ?)");
        $stmt->execute([
            (int)$data['participant_id'],
            json_encode($data['demographics'] ?? []),
            json_encode($data['responses'] ?? [])
        ]);
        respond(['success' => true]);
    } catch (\Throwable $e) {
        error_log("Survey Save Error: " . $e->getMessage());
        respond(['success' => false, 'message' => 'Could not save your responses. Please try again.'], 500);
    }
}

elseif ($action === 'save_assessment') {
    // 4. Save a screening test (PHQ-9, GAD-7, ...). Scored on the server.
    $def = mindlab_get_assessment($data['test_id'] ?? '');
    if (!$def) {
        respond(['success' => false, 'message' => 'Unknown assessment.'], 400);
    }
    $scored = mindlab_score_assessment($def, $data['responses'] ?? null);
    if (!$scored) {
        respond(['success' => false, 'message' => 'Please answer every statement before continuing.'], 400);
    }
    try {
        mindlab_ensure_assessment_table($pdo);
        $stmt = $pdo->prepare("INSERT INTO assessment_results (participant_id, test_id, responses, total_score, severity, crisis_flag) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            (int)$data['participant_id'],
            $def['id'],
            json_encode($scored['answers'] + $scored['extra']),
            $scored['total'],
            $scored['severity'],
            $scored['crisis'] ? 1 : 0,
        ]);
        respond(['success' => true]);
    } catch (\Throwable $e) {
        error_log("Assessment Save Error: " . $e->getMessage());
        respond(['success' => false, 'message' => 'Could not save your answers. Please try again.'], 500);
    }
}

respond(['success' => false, 'message' => 'Unknown action.'], 400);
