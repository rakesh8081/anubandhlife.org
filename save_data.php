<?php
// save_data.php
require 'db_config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

if ($action === 'register') {
    // 1. Save Participant
    try {
        $stmt = $pdo->prepare("INSERT INTO participants (full_name, email, age, sex, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['email'],
            $data['age'],
            $data['sex'],
            $data['phone']
        ]);
        $lastId = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'participant_id' => $lastId]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} 

elseif ($action === 'save_results') {
    // 2. Save Test Scores
    try {
        $stmt = $pdo->prepare("INSERT INTO interest_results (participant_id, raw_responses, calculated_scores) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['participant_id'],
            json_encode($data['responses']),
            json_encode($data['scores'])
        ]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// ... existing code ...
} elseif ($action === 'save_awareness_results') {
        try {
            $stmt = $pdo->prepare("INSERT INTO awareness_survey_results (participant_id, demographics, responses) VALUES (?, ?, ?)");
            $stmt->execute([
                $data['participant_id'],
                json_encode($data['demographics']),
                json_encode($data['responses'])
            ]);
            echo json_encode(['success' => true]);
        } catch (\Throwable $e) { 
            // \Throwable catches EVERYTHING, preventing a 500 error crash
            error_log("Survey Save Error: " . $e->getMessage());
            
            // This sends the exact database error back to your browser console
            echo json_encode(['success' => false, 'message' => 'DB Error: ' . $e->getMessage()]);
        }
}
// ... existing code ...
?>
